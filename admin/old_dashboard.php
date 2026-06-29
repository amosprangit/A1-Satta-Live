<?php
require_once '../config.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    header('Location: admin-login.php');
    exit();
}

$page_title = 'Admin Dashboard - A1 Satta Live';

// ===== CUSTOM LOG FUNCTION =====
function writeAdminLog($message)
{
    $log_file = __DIR__ . '/admin_update_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

// ============================================
// UPDATE RESULT - THE FIX
// ============================================
if (isset($_POST['update_result'])) {
    writeAdminLog("=== UPDATE ATTEMPT ===");
    writeAdminLog("POST Data: " . print_r($_POST, true));

    try {
        $game_name = trim($_POST['game_name']);
        $new_result = trim($_POST['today_result']);
        $display_name = trim($_POST['display_name']);
        $result_time = trim($_POST['result_time']);

        writeAdminLog("Game Name: " . $game_name);
        writeAdminLog("New Result: " . $new_result);

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT * FROM game_results WHERE LOWER(game_name) = LOWER(?)");
        $stmt->execute([$game_name]);
        $game = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($game) {
            $current_today = $game['today_result'];
            $game_id = $game['id'];

            writeAdminLog("✅ Found game ID: " . $game_id);
            writeAdminLog("Current Today: " . $current_today);

            if ($current_today === 'WAIT' || $current_today === '-1' || empty($current_today)) {
                $stmt = $pdo->prepare("UPDATE game_results SET 
                    today_result = ?, 
                    result_time = ?, 
                    display_name = ?
                    WHERE id = ?");
                $stmt->execute([$new_result, $result_time, $display_name, $game_id]);
                writeAdminLog("Action: WAIT replaced with new result");
            } else {
                $stmt = $pdo->prepare("UPDATE game_results SET 
                    today_result = ?, 
                    yesterday_result = ?, 
                    result_time = ?, 
                    display_name = ?
                    WHERE id = ?");
                $stmt->execute([$new_result, $current_today, $result_time, $display_name, $game_id]);
                writeAdminLog("Action: Result moved from today to yesterday");
            }

            writeAdminLog("Rows affected: " . $stmt->rowCount());

            if ($new_result !== 'WAIT' && !empty($new_result) && $new_result !== '-1') {
                $date = date('d-m');
                $table_type = $game['table_type'] ?? 'table1';

                $stmt = $pdo->prepare("SELECT id FROM chart_data WHERE LOWER(game_name) = LOWER(?) AND date = ?");
                $stmt->execute([$game_name, $date]);

                if ($stmt->rowCount() > 0) {
                    $stmt = $pdo->prepare("UPDATE chart_data SET result_number = ? WHERE LOWER(game_name) = LOWER(?) AND date = ?");
                    $stmt->execute([$new_result, $game_name, $date]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO chart_data (game_name, date, result_number, table_type) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$game_name, $date, $new_result, $table_type]);
                }
            }

            $pdo->commit();
            $_SESSION['success'] = "✅ Result updated for " . htmlspecialchars(ucfirst($game_name));
            writeAdminLog("✅ UPDATE SUCCESSFUL");

        } else {
            writeAdminLog("❌ ERROR: Game not found: " . $game_name);
            $_SESSION['error'] = "❌ Game '" . htmlspecialchars(ucfirst($game_name)) . "' not found!";
        }
        writeAdminLog("==========================");

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "❌ Failed to update result: " . $e->getMessage();
        writeAdminLog("❌ EXCEPTION: " . $e->getMessage());
        writeAdminLog("==========================");
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
            $stmt = $pdo->prepare("UPDATE game_results SET 
                display_name = ?, 
                today_result = ?, 
                yesterday_result = ?, 
                result_time = ?, 
                table_type = ?, 
                status = 'active'
                WHERE LOWER(game_name) = LOWER(?)");
            $stmt->execute([$display_name, $today_result, $yesterday_result, $result_time, $table_type, $game_name]);
            $_SESSION['success'] = "✅ Game '" . htmlspecialchars(ucfirst($game_name)) . "' updated successfully!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO game_results (game_name, display_name, today_result, yesterday_result, result_time, table_type, status) 
                                   VALUES (?, ?, ?, ?, ?, ?, 'active')");
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
// UPDATE DISAWER
// ============================================
if (isset($_POST['update_disawer'])) {
    try {
        $display_name = $_POST['disawer_display_name'] ?? 'DISAWAR';
        $time = $_POST['disawer_time'] ?? '5:15 AM';
        $today = $_POST['disawer_today'] ?? '86';
        $yesterday = $_POST['disawer_yesterday'] ?? '05';

        $stmt = $pdo->prepare("UPDATE game_results SET 
            today_result = ?, 
            yesterday_result = ?, 
            result_time = ?, 
            display_name = ?
            WHERE LOWER(game_name) = 'disawar'");
        $stmt->execute([$today, $yesterday, $time, $display_name]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = "✅ Disawar updated successfully!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO game_results (game_name, display_name, today_result, yesterday_result, result_time, status, table_type) 
                                   VALUES ('disawar', ?, ?, ?, ?, 'active', 'table1')");
            $stmt->execute([$display_name, $today, $yesterday, $time]);
            $_SESSION['success'] = "✅ Disawar created successfully!";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Failed to update Disawar: " . $e->getMessage();
    }
    header('Location: admin-dashboard.php');
    exit();
}

// ============================================
// UPDATE CHART DATA
// ============================================
if (isset($_POST['update_chart'])) {
    try {
        $stmt = $pdo->prepare("SELECT id FROM chart_data WHERE game_name = ? AND date = ? AND table_type = ?");
        $stmt->execute([$_POST['chart_game'], $_POST['chart_date'], $_POST['chart_table_type']]);

        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("UPDATE chart_data SET result_number = ? WHERE game_name = ? AND date = ? AND table_type = ?");
            $stmt->execute([$_POST['chart_result'], $_POST['chart_game'], $_POST['chart_date'], $_POST['chart_table_type']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO chart_data (game_name, date, result_number, table_type) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_POST['chart_game'], $_POST['chart_date'], $_POST['chart_result'], $_POST['chart_table_type']]);
        }
        $_SESSION['success'] = "✅ Chart updated for " . htmlspecialchars(ucfirst($_POST['chart_game'])) . " on " . htmlspecialchars($_POST['chart_date']);
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Failed to update chart!";
    }
    header('Location: admin-dashboard.php?tab=chart');
    exit();
}

// ============================================
// GENERATE MONTH CHART
// ============================================
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

    try {
        $stmt = $pdo->prepare("SELECT table_type FROM game_results WHERE LOWER(game_name) = LOWER(?)");
        $stmt->execute([$game]);
        $game_info = $stmt->fetch(PDO::FETCH_ASSOC);
        $table_type = $game_info ? $game_info['table_type'] : 'table1';
    } catch (PDOException $e) {
        $table_type = 'table1';
    }

    $count = 0;
    for ($day = 1; $day <= $days_in_month; $day++) {
        $date_str = str_pad($day, 2, '0', STR_PAD_LEFT) . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
        $result = $sample_results[array_rand($sample_results)];

        try {
            $stmt = $pdo->prepare("INSERT INTO chart_data (game_name, date, result_number, table_type) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE result_number = ?");
            $stmt->execute([$game, $date_str, $result, $table_type, $result]);
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

// ============================================
// GAME TIMINGS CRUD
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

// ============================================
// GAME RATES CRUD
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

// ============================================
// MULTIPLE RESULTS CRUD
// ============================================
if (isset($_POST['add_multiple_result'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO game_multiple_results (game_name, result_date, result_number, result_time) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['mr_game_name'], $_POST['mr_result_date'], $_POST['mr_result_number'], $_POST['mr_result_time']]);
        $_SESSION['success'] = "✅ Result added successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Failed to add result!";
    }
    header('Location: admin-dashboard.php?tab=multiple');
    exit();
}

if (isset($_POST['update_multiple_result'])) {
    try {
        $stmt = $pdo->prepare("UPDATE game_multiple_results SET game_name = ?, result_date = ?, result_number = ?, result_time = ? WHERE id = ?");
        $stmt->execute([$_POST['mr_game_name'], $_POST['mr_result_date'], $_POST['mr_result_number'], $_POST['mr_result_time'], $_POST['mr_id']]);
        $_SESSION['success'] = "✅ Result updated!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Failed to update result!";
    }
    header('Location: admin-dashboard.php?tab=multiple');
    exit();
}

// ============================================
// UPDATE FEATURED GAME
// ============================================
if (isset($_POST['update_featured_game'])) {
    updateWebsiteContent($pdo, 'featured_game', $_POST['featured_game']);
    $_SESSION['success'] = "✅ Featured game updated to " . htmlspecialchars(ucfirst($_POST['featured_game']));
    header('Location: admin-dashboard.php?tab=settings');
    exit();
}

// ============================================
// DELETE OPERATIONS
// ============================================
if (isset($_GET['delete_game'])) {
    $game_to_delete = $_GET['delete_game'];
    try {
        $stmt = $pdo->prepare("DELETE FROM chart_data WHERE LOWER(game_name) = LOWER(?)");
        $stmt->execute([$game_to_delete]);
        $stmt = $pdo->prepare("DELETE FROM game_results WHERE LOWER(game_name) = LOWER(?)");
        $stmt->execute([$game_to_delete]);
        $_SESSION['success'] = "🗑️ Game deleted successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Error deleting game!";
    }
    header('Location: admin-dashboard.php');
    exit();
}

if (isset($_GET['delete_chart'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM chart_data WHERE game_name = ? AND date = ? AND table_type = ?");
        $stmt->execute([$_GET['delete_chart'], $_GET['chart_date'], $_GET['chart_table_type']]);
        $_SESSION['success'] = "🗑️ Chart data deleted successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Failed to delete chart data!";
    }
    header('Location: admin-dashboard.php?tab=chart');
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

if (isset($_GET['delete_multiple_result'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM game_multiple_results WHERE id = ?");
        $stmt->execute([$_GET['delete_multiple_result']]);
        $_SESSION['success'] = "🗑️ Result deleted!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "❌ Failed to delete result!";
    }
    header('Location: admin-dashboard.php?tab=multiple');
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

// ============================================
// SETTINGS UPDATES
// ============================================
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

// ============================================
// GET CURRENT TAB
// ============================================
$current_tab = $_GET['tab'] ?? 'games';
$edit_game = $_GET['edit_game'] ?? '';
$edit_date = $_GET['edit_date'] ?? '';
$edit_result = $_GET['edit_result'] ?? '';
$edit_table = $_GET['edit_table'] ?? 'table1';
$selected_game = $_GET['game'] ?? '';
$selected_month = $_GET['month'] ?? date('m');
$selected_year = $_GET['year'] ?? date('Y');

// ============================================
// FETCH DATA
// ============================================
try {
    $stmt = $pdo->prepare("SELECT * FROM game_results WHERE LOWER(game_name) = 'disawar'");
    $stmt->execute();
    $disawer = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $disawer = false;
}
$disawer_result = $disawer['today_result'] ?? '86';
$disawer_yesterday = $disawer['yesterday_result'] ?? '05';

try {
    $stmt = $pdo->query("SELECT SQL_NO_CACHE * FROM game_results WHERE LOWER(game_name) != 'disawar' AND status = 'active' ORDER BY table_type, game_name");
    $all_games_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $table1_games = [];
    $table2_games = [];
    $all_games = [];

    foreach ($all_games_data as $game) {
        $all_games[$game['game_name']] = $game;
        if ($game['table_type'] == 'table2') {
            $table2_games[] = $game['game_name'];
        } else {
            $table1_games[] = $game['game_name'];
        }
    }

    if (empty($table1_games)) {
        $table1_games = ['sadar bazar', 'gwalior', 'delhi bazar', 'shri ganesh', 'faridabad', 'gaziabad', 'gali'];
    }
    if (empty($table2_games)) {
        $table2_games = ['mandi bazar', 'bhadra bazar', 'sialkot', 'lion bazar', 'gaziabad king', 'dehradun city', 'daman', 'pushkar'];
    }

} catch (PDOException $e) {
    $table1_games = ['sadar bazar', 'gwalior', 'delhi bazar', 'shri ganesh', 'faridabad', 'gaziabad', 'gali'];
    $table2_games = ['mandi bazar', 'bhadra bazar', 'sialkot', 'lion bazar', 'gaziabad king', 'dehradun city', 'daman', 'pushkar'];
    $all_games = [];
}

try {
    $stmt = $pdo->query("SELECT * FROM chart_data ORDER BY id DESC");
    $all_chart_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $all_chart_data = [];
}

try {
    $stmt = $pdo->query("SELECT * FROM game_timings ORDER BY display_order, id");
    $game_timings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $game_timings = [];
}

try {
    $stmt = $pdo->query("SELECT * FROM game_rates ORDER BY display_order, id");
    $game_rates = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $game_rates = [];
}

try {
    $stmt = $pdo->query("SELECT * FROM game_multiple_results ORDER BY result_date DESC, id DESC");
    $multiple_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $multiple_results = [];
}

try {
    $stmt = $pdo->query("SELECT DISTINCT game_name FROM game_results WHERE status = 'active' ORDER BY game_name");
    $game_names_list = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $game_names_list = array_merge($table1_games, $table2_games);
}

$chart1_games = array_merge($table1_games, ['disawar']);
$chart2_games = $table2_games;
$chart_data_display = $all_chart_data;

// ============================================
// INCLUDE HEADER
// ============================================
require_once '../header.php';
?>

<!-- ============================================ -->
<!-- INCLUDE CSS -->
<!-- ============================================ -->
<link rel="stylesheet" href="../css/admin-dashboard.css">

<!-- ============================================ -->
<!-- ADMIN DASHBOARD HTML -->
<!-- ============================================ -->
<div class="admin-container">
    <!-- Admin Header -->
    <div class="admin-header">
        <h1>🏆 Admin Dashboard</h1>
        <div class="header-actions">
            <a href="index.php" target="_blank">🌐 View Website</a>
            <a href="?logout=1" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">🚪
                Logout</a>
        </div>
    </div>

    <!-- Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success-msg"><?php echo $_SESSION['success'];
        unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error-msg"><?php echo $_SESSION['error'];
        unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="stat-cards">
        <div class="stat-card">
            <div class="number"><?php echo count($all_games); ?></div>
            <div class="label">Total Games</div>
        </div>
        <div class="stat-card">
            <div class="number"><?php echo count($game_timings); ?></div>
            <div class="label">Game Timings</div>
        </div>
        <div class="stat-card">
            <div class="number"><?php echo count($game_rates); ?></div>
            <div class="label">Game Rates</div>
        </div>
        <div class="stat-card">
            <div class="number"><?php echo count($all_chart_data); ?></div>
            <div class="label">Chart Entries</div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <a href="admin-dashboard.php?tab=games"
            class="tab-btn <?php echo $current_tab === 'games' ? 'active' : ''; ?>">🎮 Games</a>
        <a href="admin-dashboard.php?tab=chart"
            class="tab-btn <?php echo $current_tab === 'chart' ? 'active' : ''; ?>">📊 Charts</a>
        <a href="admin-dashboard.php?tab=timings"
            class="tab-btn <?php echo $current_tab === 'timings' ? 'active' : ''; ?>">⏰ Timings</a>
        <a href="admin-dashboard.php?tab=rates"
            class="tab-btn <?php echo $current_tab === 'rates' ? 'active' : ''; ?>">💰 Rates</a>
        <a href="admin-dashboard.php?tab=multiple"
            class="tab-btn <?php echo $current_tab === 'multiple' ? 'active' : ''; ?>">📝 Multiple Results</a>
        <a href="admin-dashboard.php?tab=settings"
            class="tab-btn <?php echo $current_tab === 'settings' ? 'active' : ''; ?>">⚙️ Settings</a>
    </div>

    <!-- ==================== TAB 1: GAMES ==================== -->
    <div id="tab-games" class="tab-content <?php echo $current_tab === 'games' ? 'active' : ''; ?>">
        <!-- Edit Disawer -->
        <div class="admin-section">
            <h2>✏️ Edit Disawar Result</h2>
            <form method="POST" class="admin-form">
                <input type="hidden" name="update_disawer" value="1">
                <div class="form-group"><label>Display Name:</label><input type="text" name="disawer_display_name"
                        value="<?php echo htmlspecialchars($disawer['display_name'] ?? 'DISAWAR'); ?>"></div>
                <div class="form-group"><label>Time:</label><input type="text" name="disawer_time"
                        value="<?php echo htmlspecialchars($disawer['result_time'] ?? '5:15 AM'); ?>"></div>
                <div class="form-group"><label>Yesterday Result:</label><input type="text" name="disawer_yesterday"
                        value="<?php echo htmlspecialchars($disawer_yesterday); ?>"></div>
                <div class="form-group"><label>Today Result:</label><input type="text" name="disawer_today"
                        value="<?php echo htmlspecialchars($disawer_result); ?>"></div>
                <button type="submit" class="btn-primary">💾 Update Result</button>
            </form>
        </div>

        <!-- Add New Game -->
        <div class="admin-section">
            <h2>➕ Add New Game</h2>
            <form method="POST" class="admin-form">
                <input type="hidden" name="add_game" value="1">
                <div class="form-group"><label>Game Name (slug):</label><input type="text" name="new_game_name"
                        placeholder="e.g. new-game" required></div>
                <div class="form-group"><label>Display Name:</label><input type="text" name="new_display_name"
                        placeholder="सट्टा का नाम"></div>
                <div class="form-group"><label>Yesterday:</label><input type="text" name="new_yesterday_result"
                        placeholder="--"></div>
                <div class="form-group"><label>Today:</label><input type="text" name="new_today_result"
                        placeholder="WAIT"></div>
                <div class="form-group"><label>Time:</label><input type="text" name="new_result_time"
                        placeholder="5:15 PM"></div>
                <div class="form-group"><label>Table:</label>
                    <select name="new_table_type">
                        <option value="table1">Table 1</option>
                        <option value="table2">Table 2</option>
                    </select>
                </div>
                <button type="submit" class="btn-success">➕ Add Game</button>
            </form>
        </div>

        <!-- Manage Games - Table 1 -->
        <div class="admin-section">
            <h2>📊 Manage Games - Table 1</h2>
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Game Name</th>
                            <th>Display Name</th>
                            <th>Yesterday</th>
                            <th>Today</th>
                            <th>Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($table1_games as $game):
                            $data = isset($all_games[$game]) ? $all_games[$game] : ['yesterday_result' => '--', 'today_result' => 'WAIT', 'result_time' => '--', 'display_name' => $game];
                            ?>
                            <tr>
                                <td class="game-name-cell"><?php echo ucfirst($game); ?></td>
                                <td><?php echo $data['display_name'] ?: ucfirst($game); ?></td>
                                <td><?php echo $data['yesterday_result']; ?></td>
                                <td><?php echo $data['today_result']; ?></td>
                                <td><?php echo $data['result_time']; ?></td>
                                <td>
                                    <div class="actions-cell">
                                        <button class="btn-edit"
                                            onclick="openEditModal('<?php echo $game; ?>', '<?php echo $data['yesterday_result']; ?>', '<?php echo $data['today_result']; ?>', '<?php echo $data['result_time']; ?>', '<?php echo $data['display_name']; ?>')">✏️
                                            Edit</button>
                                        <a href="?delete_game=<?php echo urlencode($game); ?>" class="btn-delete"
                                            onclick="return confirm('Delete game \'<?php echo ucfirst($game); ?>\'?')">🗑️
                                            Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Manage Games - Table 2 -->
        <div class="admin-section">
            <h2>📊 Manage Games - Table 2</h2>
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Game Name</th>
                            <th>Display Name</th>
                            <th>Yesterday</th>
                            <th>Today</th>
                            <th>Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($table2_games as $game):
                            $data = isset($all_games[$game]) ? $all_games[$game] : ['yesterday_result' => '--', 'today_result' => 'WAIT', 'result_time' => '--', 'display_name' => $game];
                            ?>
                            <tr>
                                <td class="game-name-cell"><?php echo ucfirst($game); ?></td>
                                <td><?php echo $data['display_name'] ?: ucfirst($game); ?></td>
                                <td><?php echo $data['yesterday_result']; ?></td>
                                <td><?php echo $data['today_result']; ?></td>
                                <td><?php echo $data['result_time']; ?></td>
                                <td>
                                    <div class="actions-cell">
                                        <button class="btn-edit"
                                            onclick="openEditModal('<?php echo $game; ?>', '<?php echo $data['yesterday_result']; ?>', '<?php echo $data['today_result']; ?>', '<?php echo $data['result_time']; ?>', '<?php echo $data['display_name']; ?>')">✏️
                                            Edit</button>
                                        <a href="?delete_game=<?php echo urlencode($game); ?>" class="btn-delete"
                                            onclick="return confirm('Delete game \'<?php echo ucfirst($game); ?>\'?')">🗑️
                                            Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ==================== TAB 2: CHARTS ==================== -->
    <div id="tab-chart" class="tab-content <?php echo $current_tab === 'chart' ? 'active' : ''; ?>">
        <?php if ($edit_game && $edit_date): ?>
            <div class="admin-section" style="border-left-color: #17a2b8; background: #f0f8ff;">
                <h2>✏️ Edit Chart Data</h2>
                <form method="POST" class="admin-form">
                    <input type="hidden" name="update_chart" value="1">
                    <input type="hidden" name="chart_game" value="<?php echo $edit_game; ?>">
                    <input type="hidden" name="chart_date" value="<?php echo $edit_date; ?>">
                    <input type="hidden" name="chart_table_type" value="<?php echo $edit_table; ?>">
                    <div class="form-group"><label>Game:</label><input type="text"
                            value="<?php echo ucfirst($edit_game); ?>" disabled></div>
                    <div class="form-group"><label>Date:</label><input type="text" value="<?php echo $edit_date; ?>"
                            disabled></div>
                    <div class="form-group"><label>Result Number:</label><input type="text" name="chart_result"
                            value="<?php echo $edit_result; ?>" required></div>
                    <button type="submit" class="btn-info">💾 Update Chart</button>
                    <a href="admin-dashboard.php?tab=chart" class="btn-danger">❌ Cancel</a>
                </form>
            </div>
        <?php endif; ?>

        <div class="admin-section">
            <h2>➕ Add New Chart Data</h2>
            <form method="POST" class="admin-form">
                <input type="hidden" name="update_chart" value="1">
                <div class="form-group"><label>Game Name:</label>
                    <select name="chart_game" required>
                        <option value="">-- Select Game --</option>
                        <?php $all_games_list = getGameNames($pdo);
                        foreach ($all_games_list as $game): ?>
                            <option value="<?php echo $game; ?>"><?php echo ucfirst($game); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Date (DD-MM):</label><input type="text" name="chart_date"
                        placeholder="e.g. 01-06" required></div>
                <div class="form-group"><label>Result Number:</label><input type="text" name="chart_result"
                        placeholder="Enter result number" required></div>
                <div class="form-group"><label>Table Type:</label>
                    <select name="chart_table_type" required>
                        <option value="table1">Table 1</option>
                        <option value="table2">Table 2</option>
                    </select>
                </div>
                <button type="submit" class="btn-success">💾 Save Chart Data</button>
            </form>
        </div>

        <div class="admin-section" style="border-left-color: #28a745;">
            <h2>📅 Generate Full Month Chart Data</h2>
            <form method="POST" class="admin-form">
                <input type="hidden" name="generate_month_chart" value="1">
                <div class="form-group"><label>Game Name:</label>
                    <select name="gen_chart_game" required>
                        <option value="">-- Select Game --</option>
                        <?php $all_games_list = getGameNames($pdo);
                        foreach ($all_games_list as $game): ?>
                            <option value="<?php echo $game; ?>"><?php echo ucfirst($game); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Month:</label>
                    <select name="gen_chart_month" required>
                        <option value="01">January</option>
                        <option value="02">February</option>
                        <option value="03">March</option>
                        <option value="04">April</option>
                        <option value="05">May</option>
                        <option value="06" selected>June</option>
                        <option value="07">July</option>
                        <option value="08">August</option>
                        <option value="09">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                </div>
                <div class="form-group"><label>Year:</label>
                    <select name="gen_chart_year" required>
                        <option value="2026" selected>2026</option>
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                    </select>
                </div>
                <button type="submit" class="btn-info">📊 Generate Full Month</button>
            </form>
        </div>

        <div class="admin-section">
            <h2>📋 All Chart Data</h2>
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Game Name</th>
                            <th>Date</th>
                            <th>Result</th>
                            <th>Table</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($chart_data_display)): ?>
                            <tr>
                                <td colspan="6" style="padding:30px; text-align:center; color:#999;">No chart data found.
                                </td>
                            </tr>
                        <?php else:
                            foreach ($chart_data_display as $row): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><strong><?php echo ucfirst($row['game_name']); ?></strong></td>
                                    <td><?php echo $row['date']; ?></td>
                                    <td style="font-weight:bold; color:#c49a00; font-size:18px;">
                                        <?php echo $row['result_number'] ?: '--'; ?></td>
                                    <td><span
                                            style="background: <?php echo $row['table_type'] === 'table1' ? '#ffd700' : '#17a2b8'; ?>; color:#000; padding:2px 10px; border-radius:10px; font-size:11px;"><?php echo $row['table_type']; ?></span>
                                    </td>
                                    <td>
                                        <div class="actions-cell">
                                            <a href="admin-dashboard.php?tab=chart&edit_game=<?php echo urlencode($row['game_name']); ?>&edit_date=<?php echo urlencode($row['date']); ?>&edit_result=<?php echo urlencode($row['result_number']); ?>&edit_table=<?php echo urlencode($row['table_type']); ?>"
                                                class="btn-edit">✏️ Edit</a>
                                            <a href="?delete_chart=<?php echo urlencode($row['game_name']); ?>&chart_date=<?php echo urlencode($row['date']); ?>&chart_table_type=<?php echo urlencode($row['table_type']); ?>"
                                                class="btn-delete" onclick="return confirm('Delete this entry?')">🗑️ Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ==================== TAB 3: TIMINGS ==================== -->
    <div id="tab-timings" class="tab-content <?php echo $current_tab === 'timings' ? 'active' : ''; ?>">
        <div class="admin-section">
            <h2>⏰ Manage Game Timings</h2>
            <form method="POST" class="admin-form"
                style="margin-bottom:20px; padding:20px; background:#f9f9f9; border-radius:15px;">
                <input type="hidden" name="add_timing" value="1">
                <h3 style="width:100%; margin-bottom:15px; font-size:16px;">Add New Timing</h3>
                <div class="form-group"><label>Game Name:</label><input type="text" name="timing_game_name"
                        placeholder="e.g. disawer" required></div>
                <div class="form-group"><label>Time:</label><input type="text" name="timing_time"
                        placeholder="e.g. 5:15 AM" required></div>
                <div class="form-group"><label>Emoji:</label><input type="text" name="timing_emoji" placeholder="😇"
                        value="😇"></div>
                <button type="submit" class="btn-success">➕ Add Timing</button>
            </form>

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Game Name</th>
                            <th>Timing</th>
                            <th>Emoji</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($game_timings)): ?>
                            <tr>
                                <td colspan="6" style="padding:30px; color:#999;">No timings added yet.</td>
                            </tr>
                        <?php else:
                            foreach ($game_timings as $timing): ?>
                                <tr>
                                    <td><?php echo $timing['id']; ?></td>
                                    <td><strong><?php echo ucfirst($timing['game_name']); ?></strong></td>
                                    <td><?php echo $timing['timing']; ?></td>
                                    <td style="font-size:24px;"><?php echo $timing['emoji']; ?></td>
                                    <td><span
                                            style="color: <?php echo $timing['is_active'] ? '#28a745' : '#dc3545'; ?>;"><?php echo $timing['is_active'] ? '✅ Active' : '❌ Inactive'; ?></span>
                                    </td>
                                    <td>
                                        <div class="actions-cell">
                                            <button class="btn-edit"
                                                onclick="openTimingEditModal(<?php echo $timing['id']; ?>, '<?php echo $timing['game_name']; ?>', '<?php echo $timing['timing']; ?>', '<?php echo $timing['emoji']; ?>', <?php echo $timing['is_active']; ?>)">✏️
                                                Edit</button>
                                            <a href="?toggle_timing=<?php echo $timing['id']; ?>"
                                                class="btn-toggle <?php echo $timing['is_active'] ? 'active' : ''; ?>">🔄
                                                Toggle</a>
                                            <a href="?delete_timing=<?php echo $timing['id']; ?>" class="btn-delete"
                                                onclick="return confirm('Delete this timing?')">🗑️ Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ==================== TAB 4: RATES ==================== -->
    <div id="tab-rates" class="tab-content <?php echo $current_tab === 'rates' ? 'active' : ''; ?>">
        <div class="admin-section">
            <h2>💰 Manage Game Rates</h2>
            <form method="POST" class="admin-form"
                style="margin-bottom:20px; padding:20px; background:#f9f9f9; border-radius:15px;">
                <input type="hidden" name="add_rate" value="1">
                <h3 style="width:100%; margin-bottom:15px; font-size:16px;">Add New Rate</h3>
                <div class="form-group"><label>Rate Type:</label><input type="text" name="rate_type"
                        placeholder="e.g. जोड़ी रेट" required></div>
                <div class="form-group"><label>Rate Value:</label><input type="text" name="rate_value"
                        placeholder="e.g. 10 ke..960" required></div>
                <div class="form-group"><label>Display Order:</label><input type="number" name="rate_display_order"
                        placeholder="0" value="0"></div>
                <button type="submit" class="btn-success">➕ Add Rate</button>
            </form>

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Rate Type</th>
                            <th>Rate Value</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($game_rates)): ?>
                            <tr>
                                <td colspan="6" style="padding:30px; color:#999;">No rates added yet.</td>
                            </tr>
                        <?php else:
                            foreach ($game_rates as $rate): ?>
                                <tr>
                                    <td><?php echo $rate['id']; ?></td>
                                    <td><strong><?php echo $rate['rate_type']; ?></strong></td>
                                    <td><?php echo $rate['rate_value']; ?></td>
                                    <td><?php echo $rate['display_order']; ?></td>
                                    <td><span
                                            style="color: <?php echo $rate['is_active'] ? '#28a745' : '#dc3545'; ?>;"><?php echo $rate['is_active'] ? '✅ Active' : '❌ Inactive'; ?></span>
                                    </td>
                                    <td>
                                        <div class="actions-cell">
                                            <button class="btn-edit"
                                                onclick="openRateEditModal(<?php echo $rate['id']; ?>, '<?php echo $rate['rate_type']; ?>', '<?php echo $rate['rate_value']; ?>', <?php echo $rate['is_active']; ?>)">✏️
                                                Edit</button>
                                            <a href="?toggle_rate=<?php echo $rate['id']; ?>"
                                                class="btn-toggle <?php echo $rate['is_active'] ? 'active' : ''; ?>">🔄
                                                Toggle</a>
                                            <a href="?delete_rate=<?php echo $rate['id']; ?>" class="btn-delete"
                                                onclick="return confirm('Delete this rate?')">🗑️ Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ==================== TAB 5: MULTIPLE RESULTS ==================== -->
    <div id="tab-multiple" class="tab-content <?php echo $current_tab === 'multiple' ? 'active' : ''; ?>">
        <div class="admin-section">
            <h2>📝 Manage Multiple Results</h2>
            <form method="POST" class="admin-form"
                style="margin-bottom:20px; padding:20px; background:#f9f9f9; border-radius:15px;">
                <input type="hidden" name="add_multiple_result" value="1">
                <h3 style="width:100%; margin-bottom:15px; font-size:16px;">Add New Result</h3>
                <div class="form-group"><label>Game Name:</label><input type="text" name="mr_game_name"
                        placeholder="e.g. gwalior" required></div>
                <div class="form-group"><label>Date:</label><input type="date" name="mr_result_date" required></div>
                <div class="form-group"><label>Result Number:</label><input type="text" name="mr_result_number"
                        placeholder="e.g. 86" required></div>
                <div class="form-group"><label>Time:</label><input type="text" name="mr_result_time"
                        placeholder="e.g. 2:25 PM"></div>
                <button type="submit" class="btn-success">➕ Add Result</button>
            </form>

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Game Name</th>
                            <th>Date</th>
                            <th>Result</th>
                            <th>Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($multiple_results)): ?>
                            <tr>
                                <td colspan="6" style="padding:30px; color:#999;">No results added yet.</td>
                            </tr>
                        <?php else:
                            foreach ($multiple_results as $result): ?>
                                <tr>
                                    <td><?php echo $result['id']; ?></td>
                                    <td><strong><?php echo ucfirst($result['game_name']); ?></strong></td>
                                    <td><?php echo $result['result_date']; ?></td>
                                    <td style="font-weight:bold; color:#c49a00; font-size:18px;">
                                        <?php echo $result['result_number']; ?></td>
                                    <td><?php echo $result['result_time'] ?: '--'; ?></td>
                                    <td>
                                        <div class="actions-cell">
                                            <button class="btn-edit"
                                                onclick="openMultipleResultEditModal(<?php echo $result['id']; ?>, '<?php echo $result['game_name']; ?>', '<?php echo $result['result_date']; ?>', '<?php echo $result['result_number']; ?>', '<?php echo $result['result_time']; ?>')">✏️
                                                Edit</button>
                                            <a href="?delete_multiple_result=<?php echo $result['id']; ?>" class="btn-delete"
                                                onclick="return confirm('Delete this result?')">🗑️ Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ==================== TAB 6: SETTINGS ==================== -->
    <div id="tab-settings" class="tab-content <?php echo $current_tab === 'settings' ? 'active' : ''; ?>">
        <!-- Featured Game -->
        <div class="admin-section">
            <h2>⭐ Featured Game for Live Box</h2>
            <form method="POST" class="admin-form">
                <input type="hidden" name="update_featured_game" value="1">
                <div class="form-group"><label>Select Game to Show in Live Box:</label>
                    <select name="featured_game" required>
                        <option value="disawar" <?php echo (getWebsiteContent($pdo, 'featured_game') == 'disawar') ? 'selected' : ''; ?>>DISAWAR</option>
                        <?php
                        $stmt = $pdo->query("SELECT game_name, display_name FROM game_results WHERE LOWER(game_name) != 'disawar' AND status = 'active' ORDER BY game_name");
                        $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($games as $game):
                            $display_name = !empty($game['display_name']) ? $game['display_name'] : strtoupper($game['game_name']);
                            $selected = (getWebsiteContent($pdo, 'featured_game') == $game['game_name']) ? 'selected' : '';
                            ?>
                            <option value="<?php echo htmlspecialchars($game['game_name']); ?>" <?php echo $selected; ?>>
                                <?php echo htmlspecialchars($display_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <small style="color:#555; display:block; margin:10px 0;">💡 The selected game will appear in the Live
                    Box on the homepage.</small>
                <button type="submit" class="btn-primary">💾 Update Featured Game</button>
            </form>
        </div>

        <!-- Khaiwal Info -->
        <div class="admin-section">
            <h2>🔰 Edit Name Info</h2>
            <form method="POST" class="admin-form">
                <input type="hidden" name="update_khaiwal" value="1">
                <div class="form-group"><label>Line 1:</label><input type="text" name="khaiwal_line1"
                        value="<?php echo htmlspecialchars(getWebsiteContent($pdo, 'khaiwal_line1') ?? '🔰 *Online khaiwal* 🔰'); ?>">
                </div>
                <div class="form-group"><label>Line 2:</label><input type="text" name="khaiwal_line2"
                        value="<?php echo htmlspecialchars(getWebsiteContent($pdo, 'khaiwal_line2') ?? '*( Raj Bhai Khaiwal )*'); ?>">
                </div>
                <button type="submit" class="btn-primary">💾 Update Khaiwal Info</button>
            </form>
        </div>

        <!-- WhatsApp Settings -->
        <div class="admin-section">
            <h2>📱 Edit WhatsApp Settings</h2>
            <form method="POST" class="admin-form">
                <input type="hidden" name="update_whatsapp" value="1">
                <div class="form-group"><label>WhatsApp Number:</label><input type="text" name="whatsapp_number"
                        value="<?php echo htmlspecialchars(getWebsiteContent($pdo, 'whatsapp_number') ?? '919812287328'); ?>">
                </div>
                <div class="form-group"><label>Button Text:</label><input type="text" name="whatsapp_text"
                        value="<?php echo htmlspecialchars(getWebsiteContent($pdo, 'whatsapp_text') ?? 'WhatsApp'); ?>">
                </div>
                <div class="form-group"><label>Subtext:</label><input type="text" name="whatsapp_subtext"
                        value="<?php echo htmlspecialchars(getWebsiteContent($pdo, 'whatsapp_subtext') ?? 'Click to Chat'); ?>">
                </div>
                <button type="submit" class="btn-success">💾 Update WhatsApp</button>
            </form>
        </div>

        <!-- Top WhatsApp Card -->
        <div class="admin-section">
            <h2>📞 Edit Top WhatsApp Card</h2>
            <form method="POST" class="admin-form">
                <input type="hidden" name="update_top_whatsapp" value="1">
                <div class="form-group"><label>WhatsApp Number:</label><input type="text" name="top_whatsapp_number"
                        value="<?php echo htmlspecialchars(getWebsiteContent($pdo, 'top_whatsapp_number') ?? '919812287328'); ?>">
                </div>
                <div class="form-group"><label>Card Text:</label><textarea name="top_whatsapp_text"
                        rows="2"><?php echo htmlspecialchars(getWebsiteContent($pdo, 'top_whatsapp_text') ?? '"NOW WHATSAPP PLAYERS CAN ALSO JOIN OUR WHATSAPP CHANNEL TO GET RESULTS QUICKLY AND RECEIVE SUPERFAST RESULTS."'); ?></textarea>
                </div>
                <div class="form-group"><label>Button Text:</label><input type="text" name="top_whatsapp_btn"
                        value="<?php echo htmlspecialchars(getWebsiteContent($pdo, 'top_whatsapp_btn') ?? 'Click to chat'); ?>">
                </div>
                <button type="submit" class="btn-primary">💾 Update WhatsApp Card</button>
            </form>
        </div>

        <!-- Telegram Card -->
        <div class="admin-section">
            <h2>📢 Edit Telegram Card</h2>
            <form method="POST" class="admin-form">
                <input type="hidden" name="update_telegram" value="1">
                <div class="form-group"><label>Telegram Link:</label><input type="text" name="telegram_link"
                        value="<?php echo htmlspecialchars(getWebsiteContent($pdo, 'telegram_link') ?? 'https://t.me/a7Resultupdates'); ?>">
                </div>
                <div class="form-group"><label>Card Text:</label><textarea name="telegram_text"
                        rows="2"><?php echo htmlspecialchars(getWebsiteContent($pdo, 'telegram_text') ?? '"NOW TELEGRAM PLAYERS CAN ALSO JOIN OUR TELEGRAM CHANNEL TO GET RESULTS QUICKLY AND RECEIVE SUPERFAST RESULTS."'); ?></textarea>
                </div>
                <div class="form-group"><label>Button Text:</label><input type="text" name="telegram_btn"
                        value="<?php echo htmlspecialchars(getWebsiteContent($pdo, 'telegram_btn') ?? 'Click to Connect'); ?>">
                </div>
                <button type="submit" class="btn-info">💾 Update Telegram Card</button>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MODALS -->
<!-- ============================================ -->

<!-- Edit Game Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>✏️ Update Game Result</h3>
        <form method="POST">
            <input type="hidden" name="update_result" value="1">
            <input type="hidden" name="game_name" id="editGameName">
            <div class="form-group"><label>Game Name:</label><input type="text" id="editGameNameDisplay" disabled></div>
            <div class="form-group"><label>Display Name:</label><input type="text" name="display_name"
                    id="editDisplayName" required></div>
            <div class="form-group current-status">
                <label>Current Status:</label>
                <div class="status-display">
                    <div><small>Yesterday</small>
                        <div id="editYesterdayDisplay">--</div>
                    </div>
                    <div><small>Today</small>
                        <div id="editTodayDisplay">WAIT</div>
                    </div>
                </div>
            </div>
            <div class="form-group"><label>New Result:</label><input type="text" name="today_result" id="editToday"
                    placeholder="Enter new result number" required>
                <small>💡 This will replace 'WAIT' with the new result.</small>
            </div>
            <div class="form-group"><label>Result Time:</label><input type="text" name="result_time" id="editTime"
                    placeholder="e.g. 5:15 PM"></div>
            <div class="modal-actions">
                <button type="submit" class="btn-save">💾 Update Result</button>
                <button type="button" class="btn-cancel" onclick="closeEditModal()">❌ Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Timing Modal -->
<div id="timingEditModal" class="modal">
    <div class="modal-content">
        <h3>✏️ Edit Game Timing</h3>
        <form method="POST">
            <input type="hidden" name="update_timing" value="1">
            <input type="hidden" name="timing_id" id="timingEditId">
            <div class="form-group"><label>Game Name:</label><input type="text" name="timing_game_name"
                    id="timingEditGameName" required></div>
            <div class="form-group"><label>Time:</label><input type="text" name="timing_time" id="timingEditTime"
                    required></div>
            <div class="form-group"><label>Emoji:</label><input type="text" name="timing_emoji" id="timingEditEmoji">
            </div>
            <div class="form-group"><label>Active:</label>
                <select name="timing_is_active" id="timingEditActive">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn-save">💾 Save</button>
                <button type="button" class="btn-cancel" onclick="closeTimingEditModal()">❌ Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Rate Modal -->
<div id="rateEditModal" class="modal">
    <div class="modal-content">
        <h3>✏️ Edit Game Rate</h3>
        <form method="POST">
            <input type="hidden" name="update_rate" value="1">
            <input type="hidden" name="rate_id" id="rateEditId">
            <div class="form-group"><label>Rate Type:</label><input type="text" name="rate_type" id="rateEditType"
                    required></div>
            <div class="form-group"><label>Rate Value:</label><input type="text" name="rate_value" id="rateEditValue"
                    required></div>
            <div class="form-group"><label>Active:</label>
                <select name="rate_is_active" id="rateEditActive">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn-save">💾 Save</button>
                <button type="button" class="btn-cancel" onclick="closeRateEditModal()">❌ Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Multiple Result Modal -->
<div id="multipleResultEditModal" class="modal">
    <div class="modal-content">
        <h3>✏️ Edit Multiple Result</h3>
        <form method="POST">
            <input type="hidden" name="update_multiple_result" value="1">
            <input type="hidden" name="mr_id" id="mrEditId">
            <div class="form-group"><label>Game Name:</label><input type="text" name="mr_game_name" id="mrEditGameName"
                    required></div>
            <div class="form-group"><label>Date:</label><input type="date" name="mr_result_date" id="mrEditDate"
                    required></div>
            <div class="form-group"><label>Result Number:</label><input type="text" name="mr_result_number"
                    id="mrEditNumber" required></div>
            <div class="form-group"><label>Time:</label><input type="text" name="mr_result_time" id="mrEditTime"></div>
            <div class="modal-actions">
                <button type="submit" class="btn-save">💾 Save</button>
                <button type="button" class="btn-cancel" onclick="closeMultipleResultEditModal()">❌ Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================ -->
<!-- INCLUDE JAVASCRIPT -->
<!-- ============================================ -->
<script src="./js/admin-dashboard.js"></script>

<?php require_once 'footer.php'; ?>