<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';
require_once 'includes/game-functions.php';

// Get game name from URL
$game_slug = isset($_GET['game']) ? trim($_GET['game']) : '';

// If no game specified, redirect to home
if (empty($game_slug)) {
    header('Location: index.php');
    exit;
}

// Convert slug back to game name
$game_name = str_replace('-', ' ', $game_slug);

error_log("===== GAME PAGE REQUEST =====");
error_log("Slug: " . $game_slug);
error_log("Game name: " . $game_name);

// ============================================
// TRY MULTIPLE APPROACHES TO FIND THE GAME
// ============================================
$game_result = null;
$is_custom_table_game = false;
$custom_table_id = null;
$found_by = '';

// APPROACH 1: Check game_results (regular tables)
try {
    $stmt = $pdo->prepare("SELECT * FROM game_results WHERE LOWER(game_name) = LOWER(?) AND status = 1");
    $stmt->execute([$game_name]);
    $game_result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($game_result) {
        $found_by = 'game_results';
        error_log("FOUND in game_results: " . $game_name);
    }
} catch (PDOException $e) {
    error_log("Error in game_results query: " . $e->getMessage());
}

// APPROACH 2: Check custom_table_games with JOIN and status=1
if (!$game_result) {
    try {
        $stmt = $pdo->prepare("
            SELECT ctg.*, ct.table_name as custom_table_name, ct.id as custom_table_id 
            FROM custom_table_games ctg
            INNER JOIN custom_tables ct ON ctg.table_id = ct.id
            WHERE LOWER(ctg.game_name) = LOWER(?) AND ct.status = 1
            LIMIT 1
        ");
        $stmt->execute([$game_name]);
        $custom_game = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($custom_game) {
            $game_result = $custom_game;
            $is_custom_table_game = true;
            $custom_table_id = $custom_game['custom_table_id'];
            $found_by = 'custom_table_games (with status=1)';
            error_log("FOUND in custom_table_games with status=1: " . $game_name);
        } else {
            error_log("NOT FOUND in custom_table_games with status=1");
        }
    } catch (PDOException $e) {
        error_log("Error in custom_table_games query: " . $e->getMessage());
    }
}

// APPROACH 3: Check custom_table_games without status check (bypass status)
if (!$game_result) {
    try {
        $stmt = $pdo->prepare("
            SELECT ctg.*, ct.table_name as custom_table_name, ct.id as custom_table_id 
            FROM custom_table_games ctg
            INNER JOIN custom_tables ct ON ctg.table_id = ct.id
            WHERE LOWER(ctg.game_name) = LOWER(?)
            LIMIT 1
        ");
        $stmt->execute([$game_name]);
        $custom_game = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($custom_game) {
            $game_result = $custom_game;
            $is_custom_table_game = true;
            $custom_table_id = $custom_game['custom_table_id'];
            $found_by = 'custom_table_games (bypass status)';
            error_log("FOUND in custom_table_games (bypass status): " . $game_name);
            error_log("Table status: " . ($custom_game['status'] ?? 'unknown'));
        }
    } catch (PDOException $e) {
        error_log("Error in custom_table_games bypass query: " . $e->getMessage());
    }
}

// APPROACH 4: Direct query on custom_table_games only
if (!$game_result) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM custom_table_games WHERE LOWER(game_name) = LOWER(?)");
        $stmt->execute([$game_name]);
        $custom_game = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($custom_game) {
            // Now get the custom table info
            $stmt2 = $pdo->prepare("SELECT * FROM custom_tables WHERE id = ?");
            $stmt2->execute([$custom_game['table_id']]);
            $table_info = $stmt2->fetch(PDO::FETCH_ASSOC);

            $game_result = $custom_game;
            $game_result['custom_table_name'] = $table_info['table_name'] ?? 'Unknown Table';
            $game_result['custom_table_id'] = $table_info['id'] ?? null;
            $is_custom_table_game = true;
            $custom_table_id = $custom_game['table_id'];
            $found_by = 'direct custom_table_games';
            error_log("FOUND via direct query: " . $game_name);
        }
    } catch (PDOException $e) {
        error_log("Error in direct custom_table_games query: " . $e->getMessage());
    }
}

// APPROACH 5: Try by display_name
if (!$game_result) {
    try {
        // Check in game_results by display_name
        $stmt = $pdo->prepare("SELECT * FROM game_results WHERE LOWER(display_name) = LOWER(?) AND status = 1");
        $stmt->execute([$game_name]);
        $game_result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($game_result) {
            $found_by = 'game_results (display_name)';
            error_log("FOUND by display_name in game_results: " . $game_name);
        }
    } catch (PDOException $e) {
        $game_result = null;
    }
}

// If still not found, show error
if (!$game_result) {
    // Get all custom tables info for debugging
    try {
        $stmt = $pdo->query("SELECT * FROM custom_tables");
        $all_tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("All custom tables: " . print_r($all_tables, true));
    } catch (PDOException $e) {
        error_log("Error getting custom tables: " . $e->getMessage());
    }
    ?>
    <!DOCTYPE html>
    <html>

    <head>
        <title>Game Not Found</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                padding: 40px;
                background: #f5f5f5;
            }

            .error-box {
                background: #fff;
                padding: 30px;
                border-radius: 10px;
                max-width: 600px;
                margin: 0 auto;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }

            h2 {
                color: #d32f2f;
            }

            .debug-info {
                background: #f0f0f0;
                padding: 15px;
                border-radius: 5px;
                margin: 15px 0;
                font-family: monospace;
            }

            .btn {
                display: inline-block;
                padding: 10px 20px;
                background: #1a1a2e;
                color: #ffd700;
                text-decoration: none;
                border-radius: 5px;
            }

            .success {
                color: green;
            }

            .error {
                color: red;
            }
        </style>
    </head>

    <body>
        <div class="error-box">
            <h2>❌ Game Not Found</h2>
            <p><strong>You requested:</strong> <?php echo htmlspecialchars($game_name); ?></p>
            <p><strong>Slug:</strong> <?php echo htmlspecialchars($game_slug); ?></p>

            <div class="debug-info">
                <strong>Debug Information:</strong><br>
                <?php
                // Show all custom tables
                echo "<br><strong>Custom Tables:</strong><br>";
                try {
                    $stmt = $pdo->query("SELECT * FROM custom_tables");
                    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($tables as $table) {
                        echo "• ID: " . $table['id'] . " | Name: " . $table['table_name'] . " | Status: " . ($table['status'] ? '✅ Active' : '❌ Inactive') . "<br>";
                    }
                } catch (PDOException $e) {
                    echo "• Error fetching tables<br>";
                }

                // Show all custom games
                echo "<br><strong>Custom Games:</strong><br>";
                try {
                    $stmt = $pdo->query("SELECT * FROM custom_table_games");
                    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($games as $g) {
                        echo "• " . $g['game_name'] . " (table_id: " . $g['table_id'] . ")<br>";
                    }
                } catch (PDOException $e) {
                    echo "• Error fetching games<br>";
                }
                ?>
            </div>

            <p><a href="index.php" class="btn">🏠 Back to Home</a></p>
        </div>
    </body>

    </html>
    <?php
    exit;
}

// ============================================
// EXTRACT GAME DATA
// ============================================
$current_result = $game_result['today_result'] ?? 'WAIT';
$yesterday_result = $game_result['yesterday_result'] ?? '--';
$result_time = $game_result['result_time'] ?? '';
$display_name = !empty($game_result['display_name']) ? $game_result['display_name'] : strtoupper($game_name);

// Get custom table info if applicable
$custom_table_name = $is_custom_table_game ? ($game_result['custom_table_name'] ?? '') : '';

// ============================================
// GET GAME TIMING
// ============================================
if (!$is_custom_table_game) {
    try {
        $stmt = $pdo->prepare("SELECT timing FROM game_timings WHERE LOWER(game_name) = LOWER(?) AND is_active = 1 LIMIT 1");
        $stmt->execute([$game_name]);
        $timing_row = $stmt->fetch(PDO::FETCH_ASSOC);
        $game_timing = $timing_row ? $timing_row['timing'] : $result_time;
    } catch (PDOException $e) {
        $game_timing = $result_time;
    }
} else {
    $game_timing = $result_time;
}

// ============================================
// TIMER LOGIC
// ============================================
function shouldShowTodayResult($game_timing)
{
    $current_time = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
    $current_hour = (int) $current_time->format('H');
    $current_minute = (int) $current_time->format('i');
    $current_minutes = ($current_hour * 60) + $current_minute;

    $timing_parts = explode(' ', $game_timing);
    if (count($timing_parts) == 2) {
        $time_parts = explode(':', $timing_parts[0]);
        $hour = (int) $time_parts[0];
        $minute = (int) $time_parts[1];
        $ampm = strtoupper($timing_parts[1]);

        if ($ampm == 'PM' && $hour != 12) {
            $hour += 12;
        } elseif ($ampm == 'AM' && $hour == 12) {
            $hour = 0;
        }
        $timing_minutes = ($hour * 60) + $minute;
    } else {
        $time_parts = explode(':', $game_timing);
        $hour = (int) $time_parts[0];
        $minute = (int) ($time_parts[1] ?? 0);
        $timing_minutes = ($hour * 60) + $minute;
    }

    if (empty($game_timing) || !isset($timing_minutes)) {
        return true;
    }

    return $current_minutes >= $timing_minutes;
}

$show_today_result = shouldShowTodayResult($game_timing);

if (!$show_today_result) {
    $current_result = 'WAIT';
}

$current_time = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
$current_hour = (int) $current_time->format('H');
$current_minute = (int) $current_time->format('i');

if ($current_hour == 0 && $current_minute <= 5) {
    $current_result = 'WAIT';
}

// ============================================
// FETCH CHART DATA
// ============================================
try {
    $stmt = $pdo->prepare("SELECT chart_date, result FROM chart_data WHERE LOWER(game_name) = LOWER(?) ORDER BY chart_date DESC");
    $stmt->execute([$game_name]);
    $chart_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($chart_data)) {
        $chart_data = [];
    }
} catch (PDOException $e) {
    error_log("Error fetching chart data: " . $e->getMessage());
    $chart_data = [];
}

// Set page title
$page_title = strtoupper($display_name) . ' Satta King Results Chart | A1 Satta Live';

require_once 'header.php';
?>
<link rel="stylesheet" href="./css/game-detail.css">

<div
    style="background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; font-size: 12px; color: #666; border-left: 3px solid #ffd700;">
    <strong>Debug:</strong> Found by: <?php echo $found_by; ?>
    <?php if ($is_custom_table_game): ?>
        | Custom Table: <?php echo htmlspecialchars($custom_table_name); ?>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const gameTiming = '<?php echo htmlspecialchars($game_timing); ?>';

        function parseTime(timingStr) {
            if (!timingStr) return null;

            let hour, minute, ampm;
            const parts = timingStr.trim().split(' ');

            if (parts.length === 2) {
                const timeParts = parts[0].split(':');
                hour = parseInt(timeParts[0]);
                minute = parseInt(timeParts[1]);
                ampm = parts[1].toUpperCase();

                if (ampm === 'PM' && hour !== 12) {
                    hour += 12;
                } else if (ampm === 'AM' && hour === 12) {
                    hour = 0;
                }
            } else if (parts.length === 1) {
                const timeParts = timingStr.split(':');
                hour = parseInt(timeParts[0]);
                minute = parseInt(timeParts[1] || 0);
            } else {
                return null;
            }

            return { hour, minute };
        }

        function updateCountdown() {
            const now = new Date();
            let targetTime = parseTime(gameTiming);

            if (!targetTime) {
                document.getElementById('countdownTimer')?.remove();
                return;
            }

            let target = new Date(now);
            target.setHours(targetTime.hour, targetTime.minute, 0, 0);

            if (now > target) {
                target.setDate(target.getDate() + 1);
            }

            const diff = target - now;

            if (diff <= 0) {
                const timerElement = document.getElementById('countdownTimer');
                if (timerElement) {
                    timerElement.innerHTML = '<span style="color: #4caf50;">✓ Result Available</span>';
                }
                setTimeout(function () {
                    location.reload();
                }, 60000);
                return;
            }

            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            const timerElement = document.getElementById('countdownTimer');
            if (timerElement) {
                timerElement.innerHTML = `
                    ⏰ Next Result in: 
                    <span class="time-remaining">
                        ${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}
                    </span>
                `;
            }
        }

        const headerBox = document.querySelector('.game-header-box');
        if (headerBox) {
            const countdownDiv = document.createElement('div');
            countdownDiv.className = 'countdown-timer';
            countdownDiv.id = 'countdownTimer';
            headerBox.appendChild(countdownDiv);
            updateCountdown();
            setInterval(updateCountdown, 1000);
        }

        function scheduleMidnightRefresh() {
            const now = new Date();
            const tomorrow = new Date(now);
            tomorrow.setDate(tomorrow.getDate() + 1);
            tomorrow.setHours(0, 0, 1, 0);
            const timeToMidnight = tomorrow - now;
            setTimeout(function () {
                location.reload();
            }, timeToMidnight);
        }

        scheduleMidnightRefresh();
    });
</script>

<div class="game-page-container">

    <!-- Game Header -->
    <div class="game-header-box">
        <h1><?php echo htmlspecialchars(strtoupper($display_name)); ?></h1>
        <div class="game-time">⏰ Result Time: <?php echo htmlspecialchars($game_timing ?: 'N/A'); ?></div>
        <?php if ($is_custom_table_game && $custom_table_name): ?>
            <div class="game-source">📋 From: <?php echo htmlspecialchars(strtoupper($custom_table_name)); ?></div>
        <?php endif; ?>
        <div id="countdownTimer" class="countdown-timer">Loading timer...</div>
    </div>

    <!-- Game Info Cards -->
    <div class="game-info-card">
        <div class="game-info-item">
            <div class="label">Yesterday Result</div>
            <div class="value"><?php echo htmlspecialchars($yesterday_result); ?></div>
        </div>
        <div class="game-info-item">
            <div class="label">Today Result</div>
            <div
                class="value <?php echo ($current_result == 'WAIT' || $current_result == '-1' || empty($current_result)) ? 'wait' : 'result'; ?>">
                <?php if ($current_result == 'WAIT' || $current_result == '-1' || empty($current_result)): ?>
                    ⏳ WAIT
                <?php else: ?>
                    <?php echo htmlspecialchars($current_result); ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="game-info-item">
            <div class="label">Total Results</div>
            <div class="value"><?php echo count($chart_data); ?></div>
        </div>
    </div>

    <!-- Chart Results Table -->
    <div class="chart-section">
        <h2>📊 <?php echo htmlspecialchars(strtoupper($display_name)); ?> - ALL RESULTS CHART</h2>

        <div class="chart-table-wrapper">
            <table class="chart-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Result</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($chart_data)): ?>
                        <?php foreach ($chart_data as $row):
                            $date_parts = explode('-', $row['chart_date']);
                            if (count($date_parts) == 3) {
                                $year_num = $date_parts[0];
                                $month_num = $date_parts[1];
                                $day_num = $date_parts[2];
                                $timestamp = mktime(0, 0, 0, $month_num, $day_num, $year_num);
                                $date_formatted = date('d M Y', $timestamp);
                                $day_name = date('l', $timestamp);
                            } else {
                                $date_formatted = $row['chart_date'];
                                $day_name = '--';
                            }
                            $result_number = $row['result'];
                            ?>
                            <tr>
                                <td class="date-col"><?php echo $date_formatted; ?></td>
                                <td class="day-col"><?php echo $day_name; ?></td>
                                <td>
                                    <span class="result-box">
                                        <?php echo htmlspecialchars($result_number ?: '--'); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">
                                <div class="no-data">
                                    <div class="icon">📊</div>
                                    <div class="text">No results data available yet</div>
                                    <div class="subtext">Check back later for updated results</div>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Back Button -->
    <div style="text-align: center; margin: 20px 0;">
        <a href="index.php" class="back-btn">🏠 BACK TO HOME</a>
        <a href="chart.php" class="back-btn" style="margin-left: 10px;">📊 VIEW ALL CHARTS</a>
    </div>

</div>

<!-- Footer -->
<div class="footer">
    <a href="/privacy-policy">Privacy Policy</a>
    <a href="/terms-conditions">Terms & Conditions</a>
</div>

<?php require_once 'footer.php'; ?>