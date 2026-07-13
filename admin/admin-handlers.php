<?php
// /admin/includes/admin-handlers.php

require_once __DIR__ . '/admin-functions.php';

// Make sure $pdo is available
global $pdo;

// ============================================
// UPDATE RESULT
// ============================================
if (isset($_POST['update_result'])) {
    writeAdminLog("=== UPDATE ATTEMPT ===");
    writeAdminLog("POST Data: " . print_r($_POST, true));

    try {
        $game_name = trim($_POST['game_name']);
        $new_result = trim($_POST['today_result']);
        $display_name = trim($_POST['display_name']);
        $result_time = trim($_POST['result_time']);
        $yesterday_result = trim($_POST['yesterday_result'] ?? '');

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT * FROM game_results WHERE LOWER(game_name) = LOWER(?) AND status = 1");
        $stmt->execute([$game_name]);
        $game = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($game) {
            $current_today = $game['today_result'];
            $game_id = $game['id'];

            // ===== CRITICAL FIX: RESET ALL is_latest FIRST =====
            $pdo->query("UPDATE game_results SET is_latest = 0 WHERE status = 1");
            writeAdminLog("Reset all is_latest to 0");

            // Now update the game with is_latest = 1
            if (!empty($yesterday_result) && $yesterday_result !== '--') {
                $stmt = $pdo->prepare("UPDATE game_results SET today_result = ?, yesterday_result = ?, result_time = ?, display_name = ?, is_latest = 1 WHERE id = ?");
                $stmt->execute([$new_result, $yesterday_result, $result_time, $display_name, $game_id]);
                writeAdminLog("Updated with custom yesterday result: " . $yesterday_result);
            } else if ($current_today === 'WAIT' || $current_today === '-1' || empty($current_today)) {
                $stmt = $pdo->prepare("UPDATE game_results SET today_result = ?, result_time = ?, display_name = ?, is_latest = 1 WHERE id = ?");
                $stmt->execute([$new_result, $result_time, $display_name, $game_id]);
                writeAdminLog("WAIT replaced with new result: " . $new_result);
            } else {
                $stmt = $pdo->prepare("UPDATE game_results SET today_result = ?, yesterday_result = ?, result_time = ?, display_name = ?, is_latest = 1 WHERE id = ?");
                $stmt->execute([$new_result, $current_today, $result_time, $display_name, $game_id]);
                writeAdminLog("Result moved from today to yesterday: " . $current_today);
            }

            writeAdminLog("Game updated with is_latest = 1");

            // Save to chart data if result is not WAIT
            if ($new_result !== 'WAIT' && !empty($new_result) && $new_result !== '-1') {
                $chart_date = date('Y-m-d');
                $stmt = $pdo->prepare("SELECT id FROM chart_data WHERE LOWER(game_name) = LOWER(?) AND chart_date = ?");
                $stmt->execute([$game_name, $chart_date]);

                if ($stmt->rowCount() > 0) {
                    $stmt = $pdo->prepare("UPDATE chart_data SET result = ? WHERE LOWER(game_name) = LOWER(?) AND chart_date = ?");
                    $stmt->execute([$new_result, $game_name, $chart_date]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO chart_data (game_name, chart_date, result) VALUES (?, ?, ?)");
                    $stmt->execute([$game_name, $chart_date, $new_result]);
                }
                writeAdminLog("Saved to chart_data");
            }

            $pdo->commit();

            // Verify the update
            $verify = $pdo->query("SELECT game_name, today_result, is_latest FROM game_results WHERE id = $game_id")->fetch();
            writeAdminLog("VERIFY: " . $verify['game_name'] . " - Result: " . $verify['today_result'] . " - is_latest: " . $verify['is_latest']);

            $_SESSION['success'] = "✅ Result updated for " . htmlspecialchars(ucfirst($game_name)) . " (Now showing in Live Box)";
            writeAdminLog("✅ UPDATE SUCCESSFUL");

        } else {
            // Check if game is in custom_table_games
            $stmt = $pdo->prepare("SELECT * FROM custom_table_games WHERE LOWER(game_name) = LOWER(?)");
            $stmt->execute([$game_name]);
            $custom_game = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($custom_game) {
                // For custom tables, update normally (they don't have is_latest)
                $stmt = $pdo->prepare("UPDATE custom_table_games SET 
                    today_result = ?, 
                    yesterday_result = ?, 
                    result_time = ?, 
                    display_name = ? 
                    WHERE id = ?");
                $stmt->execute([$new_result, $yesterday_result, $result_time, $display_name, $custom_game['id']]);

                $pdo->commit();
                $_SESSION['success'] = "✅ Result updated for " . htmlspecialchars(ucfirst($game_name)) . " (Custom Table)";
                writeAdminLog("Updated custom table game: " . $game_name);
            } else {
                $_SESSION['error'] = "❌ Game '" . htmlspecialchars(ucfirst($game_name)) . "' not found!";
                writeAdminLog("Game not found: " . $game_name);
            }
        }

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "❌ Failed to update result: " . $e->getMessage();
        writeAdminLog("❌ EXCEPTION: " . $e->getMessage());
    }
    header('Location: admin-dashboard.php');
    exit();
}

// ============================================
// ADD NEW GAME
// ============================================
if (isset($_POST['add_game'])) {
    $game_name = strtolower(trim($_POST['new_game_name']));
    $display_name = $_POST['new_display_name'] ?: ucfirst(str_replace('-', ' ', $game_name));
    $today_result = $_POST['new_today_result'] ?: 'WAIT';
    $yesterday_result = $_POST['new_yesterday_result'] ?: '--';
    $result_time = $_POST['new_result_time'] ?: '--';
    $table_type = $_POST['new_table_type'];

    try {
        $stmt = $pdo->prepare("SELECT id FROM game_results WHERE LOWER(game_name) = LOWER(?)");
        $stmt->execute([$game_name]);

        if ($stmt->rowCount() > 0) {
            // Reset all is_latest first
            $pdo->query("UPDATE game_results SET is_latest = 0 WHERE status = 1");

            $stmt = $pdo->prepare("UPDATE game_results SET display_name = ?, today_result = ?, yesterday_result = ?, result_time = ?, table_type = ?, status = 1, is_latest = 1 WHERE LOWER(game_name) = LOWER(?)");
            $stmt->execute([$display_name, $today_result, $yesterday_result, $result_time, $table_type, $game_name]);
            $_SESSION['success'] = "✅ Game '" . htmlspecialchars(ucfirst($game_name)) . "' updated successfully!";
        } else {
            // New game - reset all is_latest first
            $pdo->query("UPDATE game_results SET is_latest = 0 WHERE status = 1");

            $stmt = $pdo->prepare("INSERT INTO game_results (game_name, display_name, today_result, yesterday_result, result_time, table_type, status, is_latest) VALUES (?, ?, ?, ?, ?, ?, 1, 1)");
            $stmt->execute([$game_name, $display_name, $today_result, $yesterday_result, $result_time, $table_type]);
            $_SESSION['success'] = "✅ Game '" . htmlspecialchars(ucfirst($game_name)) . "' added successfully!";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Failed to add/update game: " . $e->getMessage();
    }
    header('Location: admin-dashboard.php');
    exit();
}

// ============================================
// TOGGLE GAME STATUS
// ============================================
if (isset($_GET['toggle_game'])) {
    try {
        $stmt = $pdo->prepare("UPDATE game_results SET status = CASE WHEN status = 1 THEN 0 ELSE 1 END WHERE LOWER(game_name) = LOWER(?)");
        $stmt->execute([$_GET['toggle_game']]);
        $_SESSION['success'] = "✅ Game status toggled!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Failed to toggle game status!";
    }
    header('Location: admin-dashboard.php');
    exit();
}

// ============================================
// DELETE GAME - UPDATED to also check custom tables
// ============================================
if (isset($_GET['delete_game'])) {
    $game_to_delete = $_GET['delete_game'];
    try {
        // Delete from chart_data
        $stmt = $pdo->prepare("DELETE FROM chart_data WHERE LOWER(game_name) = LOWER(?)");
        $stmt->execute([$game_to_delete]);

        // Delete from game_results
        $stmt = $pdo->prepare("DELETE FROM game_results WHERE LOWER(game_name) = LOWER(?)");
        $stmt->execute([$game_to_delete]);

        // Also delete from custom_table_games if exists
        $stmt = $pdo->prepare("DELETE FROM custom_table_games WHERE LOWER(game_name) = LOWER(?)");
        $stmt->execute([$game_to_delete]);

        $_SESSION['success'] = "🗑️ Game deleted successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Error deleting game: " . $e->getMessage();
    }
    header('Location: admin-dashboard.php');
    exit();
}

// ============================================
// CHART HANDLERS
// ============================================
if (isset($_POST['update_chart'])) {
    try {
        $chart_date = $_POST['chart_date'];
        $game_name = $_POST['chart_game'];
        $result = $_POST['chart_result'];

        $stmt = $pdo->prepare("SELECT id FROM chart_data WHERE LOWER(game_name) = LOWER(?) AND chart_date = ?");
        $stmt->execute([$game_name, $chart_date]);

        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("UPDATE chart_data SET result = ? WHERE LOWER(game_name) = LOWER(?) AND chart_date = ?");
            $stmt->execute([$result, $game_name, $chart_date]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO chart_data (game_name, chart_date, result) VALUES (?, ?, ?)");
            $stmt->execute([$game_name, $chart_date, $result]);
        }
        $_SESSION['success'] = "✅ Chart updated for " . htmlspecialchars(ucfirst($game_name));
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Failed to update chart!";
    }
    header('Location: admin-dashboard.php?tab=chart');
    exit();
}

if (isset($_POST['generate_month_chart'])) {
    $game = $_POST['gen_chart_game'];
    $month = $_POST['gen_chart_month'];
    $year = $_POST['gen_chart_year'];

    if (empty($game) || empty($month) || empty($year)) {
        $_SESSION['error'] = "❌ Please fill all fields!";
        header('Location: admin-dashboard.php?tab=chart');
        exit();
    }

    $days_in_month = cal_days_in_month(CAL_GREGORIAN, (int) $month, (int) $year);
    $sample_results = ['12', '45', '78', '23', '56', '89', '34', '67', '90', '15', '48', '71', '29', '53', '86', '41', '74', '18', '62', '95', '37', '50', '83', '26', '59', '92', '35', '68', '10', '43', '76'];

    $count = 0;
    for ($day = 1; $day <= $days_in_month; $day++) {
        $date_str = date('Y-m-d', strtotime("$year-$month-$day"));
        $result = $sample_results[array_rand($sample_results)];

        try {
            $stmt = $pdo->prepare("INSERT INTO chart_data (game_name, chart_date, result) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE result = ?");
            $stmt->execute([$game, $date_str, $result, $result]);
            if ($stmt->rowCount() > 0)
                $count++;
        } catch (PDOException $e) {
            continue;
        }
    }

    if ($count > 0) {
        $_SESSION['success'] = "✅ Generated/Updated $count entries for " . htmlspecialchars(ucfirst($game));
    } else {
        $_SESSION['error'] = "❌ Failed to generate chart data!";
    }
    header('Location: admin-dashboard.php?tab=chart');
    exit();
}

if (isset($_GET['delete_chart'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM chart_data WHERE LOWER(game_name) = LOWER(?) AND chart_date = ?");
        $stmt->execute([$_GET['delete_chart'], $_GET['chart_date']]);
        $_SESSION['success'] = "🗑️ Chart data deleted successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Failed to delete chart data!";
    }
    header('Location: admin-dashboard.php?tab=chart');
    exit();
}

// ============================================
// TIMINGS HANDLERS
// ============================================
if (isset($_POST['add_timing'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO game_timings (game_name, timing, emoji, is_active) VALUES (?, ?, ?, 1)");
        $stmt->execute([$_POST['timing_game_name'], $_POST['timing_time'], $_POST['timing_emoji']]);
        $_SESSION['success'] = "✅ Game timing added successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Failed to add timing!";
    }
    header('Location: admin-dashboard.php?tab=timings');
    exit();
}

if (isset($_POST['update_timing'])) {
    try {
        $is_active = isset($_POST['timing_is_active']) ? $_POST['timing_is_active'] : 1;
        $stmt = $pdo->prepare("UPDATE game_timings SET game_name = ?, timing = ?, emoji = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$_POST['timing_game_name'], $_POST['timing_time'], $_POST['timing_emoji'], $is_active, $_POST['timing_id']]);
        $_SESSION['success'] = "✅ Game timing updated!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Failed to update timing!";
    }
    header('Location: admin-dashboard.php?tab=timings');
    exit();
}

if (isset($_GET['toggle_timing'])) {
    try {
        $stmt = $pdo->prepare("UPDATE game_timings SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END WHERE id = ?");
        $stmt->execute([$_GET['toggle_timing']]);
        $_SESSION['success'] = "✅ Timing status toggled!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Failed to toggle timing!";
    }
    header('Location: admin-dashboard.php?tab=timings');
    exit();
}

if (isset($_GET['delete_timing'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM game_timings WHERE id = ?");
        $stmt->execute([$_GET['delete_timing']]);
        $_SESSION['success'] = "🗑️ Game timing deleted!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Failed to delete timing!";
    }
    header('Location: admin-dashboard.php?tab=timings');
    exit();
}

// ============================================
// RATES HANDLERS
// ============================================
if (isset($_POST['add_rate'])) {
    try {
        $display_order = isset($_POST['rate_display_order']) ? (int) $_POST['rate_display_order'] : 0;
        $stmt = $pdo->prepare("INSERT INTO game_rates (rate_type, rate_value, display_order, is_active) VALUES (?, ?, ?, 1)");
        $stmt->execute([$_POST['rate_type'], $_POST['rate_value'], $display_order]);
        $_SESSION['success'] = "✅ Game rate added successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Failed to add rate!";
    }
    header('Location: admin-dashboard.php?tab=rates');
    exit();
}

if (isset($_POST['update_rate'])) {
    try {
        $is_active = isset($_POST['rate_is_active']) ? $_POST['rate_is_active'] : 1;
        $stmt = $pdo->prepare("UPDATE game_rates SET rate_type = ?, rate_value = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$_POST['rate_type'], $_POST['rate_value'], $is_active, $_POST['rate_id']]);
        $_SESSION['success'] = "✅ Game rate updated!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Failed to update rate!";
    }
    header('Location: admin-dashboard.php?tab=rates');
    exit();
}

if (isset($_GET['toggle_rate'])) {
    try {
        $stmt = $pdo->prepare("UPDATE game_rates SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END WHERE id = ?");
        $stmt->execute([$_GET['toggle_rate']]);
        $_SESSION['success'] = "✅ Rate status toggled!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Failed to toggle rate!";
    }
    header('Location: admin-dashboard.php?tab=rates');
    exit();
}

if (isset($_GET['delete_rate'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM game_rates WHERE id = ?");
        $stmt->execute([$_GET['delete_rate']]);
        $_SESSION['success'] = "🗑️ Game rate deleted!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Failed to delete rate!";
    }
    header('Location: admin-dashboard.php?tab=rates');
    exit();
}

// ============================================
// CUSTOM TABLES HANDLERS
// ============================================
if (isset($_POST['add_custom_table'])) {
    $table_name = trim($_POST['table_name']);
    $table_description = trim($_POST['table_description']);

    if (empty($table_name)) {
        $_SESSION['error'] = "❌ Table name is required!";
        header('Location: admin-dashboard.php?tab=tables');
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO custom_tables (table_name, table_description, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$table_name, $table_description]);
        $_SESSION['success'] = "✅ Table '" . htmlspecialchars($table_name) . "' created successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Failed to create table: " . $e->getMessage();
    }
    header('Location: admin-dashboard.php?tab=tables');
    exit();
}

if (isset($_POST['update_custom_table'])) {
    $table_id = $_POST['table_id'];
    $table_name = trim($_POST['table_name']);
    $table_description = trim($_POST['table_description']);

    if (empty($table_name)) {
        $_SESSION['error'] = "❌ Table name is required!";
        header('Location: admin-dashboard.php?tab=tables');
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE custom_tables SET table_name = ?, table_description = ? WHERE id = ?");
        $stmt->execute([$table_name, $table_description, $table_id]);
        $_SESSION['success'] = "✅ Table updated successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Failed to update table: " . $e->getMessage();
    }
    header('Location: admin-dashboard.php?tab=tables');
    exit();
}

if (isset($_GET['delete_custom_table'])) {
    $table_id = $_GET['delete_custom_table'];

    try {
        $stmt = $pdo->prepare("DELETE FROM custom_table_games WHERE table_id = ?");
        $stmt->execute([$table_id]);
        $stmt = $pdo->prepare("DELETE FROM custom_tables WHERE id = ?");
        $stmt->execute([$table_id]);
        $_SESSION['success'] = "🗑️ Table deleted successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Failed to delete table: " . $e->getMessage();
    }
    header('Location: admin-dashboard.php?tab=tables');
    exit();
}

// ============================================
// CUSTOM TABLE GAMES HANDLERS
// ============================================
if (isset($_POST['add_table_game'])) {
    $table_id = $_POST['table_id'];
    $game_name = strtolower(trim($_POST['game_name']));
    $display_name = $_POST['display_name'] ?: ucfirst(str_replace('-', ' ', $game_name));
    $today_result = $_POST['today_result'] ?: 'WAIT';
    $yesterday_result = $_POST['yesterday_result'] ?: '--';
    $result_time = $_POST['result_time'] ?: '--';

    try {
        $stmt = $pdo->prepare("INSERT INTO custom_table_games (table_id, game_name, display_name, today_result, yesterday_result, result_time) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$table_id, $game_name, $display_name, $today_result, $yesterday_result, $result_time]);
        $_SESSION['success'] = "✅ Game added to table successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Failed to add game: " . $e->getMessage();
    }
    header('Location: admin-dashboard.php?tab=tables');
    exit();
}

if (isset($_POST['update_table_game'])) {
    $game_id = $_POST['game_id'];
    $table_id = $_POST['table_id'];
    $game_name = strtolower(trim($_POST['game_name']));
    $display_name = $_POST['display_name'] ?: ucfirst(str_replace('-', ' ', $game_name));
    $today_result = $_POST['today_result'] ?: 'WAIT';
    $yesterday_result = $_POST['yesterday_result'] ?: '--';
    $result_time = $_POST['result_time'] ?: '--';

    try {
        $stmt = $pdo->prepare("UPDATE custom_table_games SET game_name = ?, display_name = ?, today_result = ?, yesterday_result = ?, result_time = ? WHERE id = ? AND table_id = ?");
        $stmt->execute([$game_name, $display_name, $today_result, $yesterday_result, $result_time, $game_id, $table_id]);
        $_SESSION['success'] = "✅ Game updated successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Failed to update game: " . $e->getMessage();
    }
    header('Location: admin-dashboard.php?tab=tables');
    exit();
}

if (isset($_GET['delete_table_game'])) {
    $game_id = $_GET['delete_table_game'];
    $table_id = $_GET['table_id'] ?? 0;

    try {
        $stmt = $pdo->prepare("DELETE FROM custom_table_games WHERE id = ?");
        $stmt->execute([$game_id]);
        $_SESSION['success'] = "🗑️ Game deleted successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Failed to delete game: " . $e->getMessage();
    }
    header('Location: admin-dashboard.php?tab=tables');
    exit();
}

// ============================================
// SETTINGS HANDLERS
// ============================================
if (isset($_POST['update_featured_game'])) {
    updateWebsiteContent($pdo, 'featured_game', $_POST['featured_game']);
    $_SESSION['success'] = "✅ Featured game updated to " . htmlspecialchars(ucfirst($_POST['featured_game']));
    header('Location: admin-dashboard.php?tab=settings');
    exit();
}

if (isset($_POST['update_khaiwal'])) {
    updateWebsiteContent($pdo, 'khaiwal_line1', $_POST['khaiwal_line1']);
    updateWebsiteContent($pdo, 'khaiwal_line2', $_POST['khaiwal_line2']);
    $_SESSION['success'] = "✅ Khaiwal info updated!";
    header('Location: admin-dashboard.php?tab=settings');
    exit();
}

if (isset($_POST['update_whatsapp'])) {
    updateWebsiteContent($pdo, 'whatsapp_number', $_POST['whatsapp_number']);
    updateWebsiteContent($pdo, 'whatsapp_text', $_POST['whatsapp_text']);
    updateWebsiteContent($pdo, 'whatsapp_subtext', $_POST['whatsapp_subtext']);
    $_SESSION['success'] = "✅ WhatsApp settings updated!";
    header('Location: admin-dashboard.php?tab=settings');
    exit();
}

if (isset($_POST['update_top_whatsapp'])) {
    updateWebsiteContent($pdo, 'top_whatsapp_number', $_POST['top_whatsapp_number']);
    updateWebsiteContent($pdo, 'top_whatsapp_text', $_POST['top_whatsapp_text']);
    updateWebsiteContent($pdo, 'top_whatsapp_btn', $_POST['top_whatsapp_btn']);
    $_SESSION['success'] = "✅ WhatsApp card updated!";
    header('Location: admin-dashboard.php?tab=settings');
    exit();
}

if (isset($_POST['update_telegram'])) {
    updateWebsiteContent($pdo, 'telegram_link', $_POST['telegram_link']);
    updateWebsiteContent($pdo, 'telegram_text', $_POST['telegram_text']);
    updateWebsiteContent($pdo, 'telegram_btn', $_POST['telegram_btn']);
    $_SESSION['success'] = "✅ Telegram card updated!";
    header('Location: admin-dashboard.php?tab=settings');
    exit();
}

// ============================================
// LOGOUT
// ============================================
if (isset($_GET['logout'])) {
    logoutAdmin();
}
?>