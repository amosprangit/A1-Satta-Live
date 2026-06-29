<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

// Get game name from URL
$game_slug = isset($_GET['game']) ? trim($_GET['game']) : '';

// If no game specified, redirect to home
if (empty($game_slug)) {
    header('Location: /');
    exit;
}

// Convert slug back to game name
$game_name = str_replace('-', ' ', $game_slug);

// Fetch game data directly from database
try {
    $stmt = $pdo->prepare("SELECT * FROM game_results WHERE LOWER(game_name) = LOWER(?) AND status = 1");
    $stmt->execute([$game_name]);
    $game_result = $stmt->fetch(PDO::FETCH_ASSOC);

    // If not found, try case-insensitive without status filter
    if (!$game_result) {
        $stmt = $pdo->query("SELECT game_name FROM game_results");
        $all_games = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $found = false;
        foreach ($all_games as $db_game) {
            if (strtolower($db_game) === strtolower($game_name)) {
                $game_name = $db_game;
                $stmt = $pdo->prepare("SELECT * FROM game_results WHERE LOWER(game_name) = LOWER(?) AND status = 1");
                $stmt->execute([$game_name]);
                $game_result = $stmt->fetch(PDO::FETCH_ASSOC);
                $found = true;
                break;
            }
        }
        if (!$found) {
            header('Location: /');
            exit;
        }
    }
} catch (PDOException $e) {
    header('Location: /');
    exit;
}

// Get game data
$current_result = $game_result['today_result'] ?? 'WAIT';
$yesterday_result = $game_result['yesterday_result'] ?? '--';
$result_time = $game_result['result_time'] ?? '';
$display_name = !empty($game_result['display_name']) ? $game_result['display_name'] : strtoupper($game_name);
$table_type = $game_result['table_type'] ?? 'table1';

// Get game timing
try {
    $stmt = $pdo->prepare("SELECT timing FROM game_timings WHERE LOWER(game_name) = LOWER(?) AND is_active = 1 LIMIT 1");
    $stmt->execute([$game_name]);
    $timing_row = $stmt->fetch(PDO::FETCH_ASSOC);
    $game_timing = $timing_row ? $timing_row['timing'] : $result_time;
} catch (PDOException $e) {
    $game_timing = $result_time;
}

// ============ TIMER LOGIC ============
function shouldShowTodayResult($game_timing)
{
    // Get current time
    $current_time = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
    $current_hour = (int) $current_time->format('H');
    $current_minute = (int) $current_time->format('i');
    $current_minutes = ($current_hour * 60) + $current_minute;

    // Parse game timing
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
    $today_result_value = $current_result;
    $current_result = 'WAIT';
}

$current_time = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
$current_hour = (int) $current_time->format('H');
$current_minute = (int) $current_time->format('i');

if ($current_hour == 0 && $current_minute <= 5) {
    $current_result = 'WAIT';
}

// Get all chart data for this game using new schema
try {
    $stmt = $pdo->prepare("SELECT chart_date, result FROM chart_data WHERE LOWER(game_name) = LOWER(?) ORDER BY chart_date DESC");
    $stmt->execute([$game_name]);
    $chart_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $chart_data = [];
}

// Set page title
$page_title = strtoupper($display_name) . ' Satta King Results Chart | A1 Satta Live';

require_once 'header.php';
?>

<style>
    .game-page-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 15px;
    }

    .game-header-box {
        background: linear-gradient(135deg, #1a1a2e, #16213e);
        color: #ffd700;
        padding: 25px 20px;
        border-radius: 15px;
        margin-bottom: 20px;
        text-align: center;
    }

    .game-header-box h1 {
        font-size: clamp(24px, 5vw, 36px);
        margin: 0 0 5px 0;
        letter-spacing: 2px;
    }

    .game-header-box .game-time {
        font-size: 16px;
        color: #ddd;
        margin-top: 5px;
    }

    .game-header-box .countdown-timer {
        font-size: 14px;
        color: #ff6b6b;
        margin-top: 8px;
        padding: 5px 15px;
        background: rgba(255, 0, 0, 0.1);
        border-radius: 20px;
        display: inline-block;
    }

    .game-header-box .countdown-timer .time-remaining {
        font-weight: bold;
        color: #fff;
    }

    .game-info-card {
        background: #fff;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        display: flex;
        justify-content: space-around;
        flex-wrap: wrap;
        gap: 15px;
        text-align: center;
    }

    .game-info-item {
        flex: 1;
        min-width: 120px;
    }

    .game-info-item .label {
        color: #666;
        font-size: 13px;
        margin-bottom: 5px;
    }

    .game-info-item .value {
        font-size: clamp(18px, 4vw, 28px);
        font-weight: bold;
        color: #1a1a2e;
    }

    .game-info-item .value.result {
        color: #c49a00;
        font-size: clamp(24px, 5vw, 36px);
    }

    .game-info-item .value.wait {
        color: #d32f2f;
        font-size: clamp(18px, 3vw, 24px);
        animation: pulse 1.5s ease-in-out infinite;
    }

    @keyframes pulse {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0.4;
        }

        100% {
            opacity: 1;
        }
    }

    .chart-section {
        background: #fff;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
    }

    .chart-section h2 {
        text-align: center;
        color: #1a1a2e;
        margin: 0 0 20px 0;
        font-size: clamp(18px, 3vw, 24px);
        border-bottom: 2px solid #ffd700;
        padding-bottom: 10px;
    }

    .chart-table-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .chart-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 500px;
    }

    .chart-table th {
        background: #1e1e2a;
        color: #ffd700;
        padding: 12px 15px;
        font-size: 14px;
        font-weight: bold;
        text-align: center;
        border: 1px solid #333;
        white-space: nowrap;
    }

    .chart-table td {
        border: 1px solid #ddd;
        padding: 10px 15px;
        text-align: center;
        font-size: 14px;
        color: #333;
    }

    .chart-table tbody tr:hover {
        background: #fff8e0;
    }

    .chart-table tbody tr:nth-child(even) {
        background: #fafafa;
    }

    .chart-table tbody tr:nth-child(even):hover {
        background: #fff8e0;
    }

    .date-col {
        font-weight: bold;
        color: #1a1a2e;
        white-space: nowrap;
    }

    .day-col {
        color: #666;
    }

    .result-box {
        background: #1e1e2a;
        display: inline-block;
        padding: 5px 18px;
        border-radius: 20px;
        color: #ffd966;
        font-weight: bold;
        font-size: 16px;
        min-width: 50px;
    }

    .no-data {
        text-align: center;
        padding: 40px;
        color: #999;
    }

    .no-data .icon {
        font-size: 48px;
        margin-bottom: 10px;
    }

    .no-data .text {
        font-size: 18px;
        margin-bottom: 5px;
    }

    .no-data .subtext {
        font-size: 14px;
    }

    .back-btn {
        display: inline-block;
        padding: 10px 25px;
        background: #6c757d;
        color: #fff;
        text-decoration: none;
        border-radius: 30px;
        font-weight: bold;
        font-size: 14px;
        transition: 0.3s;
    }

    .back-btn:hover {
        background: #5a6268;
        color: #fff;
    }

    .footer {
        background: #000;
        text-align: center;
        padding: 20px;
        border-top: 1px solid #333;
        margin-top: 20px;
    }

    .footer a {
        color: #ffd700;
        text-decoration: none;
        margin: 0 15px;
        font-size: 14px;
    }

    .footer p {
        color: #666;
        margin-top: 15px;
        font-size: 12px;
    }

    .disclaimer {
        background: #111;
        padding: 15px;
        text-align: center;
        font-size: 11px;
        color: #888;
        line-height: 1.5;
    }

    @media (max-width: 768px) {
        .game-page-container {
            padding: 10px;
        }

        .chart-table th,
        .chart-table td {
            padding: 8px 10px;
            font-size: 12px;
        }

        .result-box {
            padding: 4px 12px;
            font-size: 14px;
        }

        .game-info-card {
            gap: 10px;
        }

        .footer a {
            margin: 0 8px;
            font-size: 12px;
        }
    }

    @media (max-width: 480px) {

        .chart-table th,
        .chart-table td {
            padding: 6px 8px;
            font-size: 11px;
        }

        .result-box {
            padding: 3px 10px;
            font-size: 12px;
        }

        .game-header-box h1 {
            font-size: 20px;
        }
    }
</style>

<script>
    // JavaScript countdown timer for real-time updates
    document.addEventListener('DOMContentLoaded', function () {
        const gameTiming = '<?php echo htmlspecialchars($game_timing); ?>';
        const gameName = '<?php echo htmlspecialchars($display_name); ?>';

        function parseTime(timingStr) {
            if (!timingStr) return null;

            let hour, minute, ampm;
            const parts = timingStr.trim().split(' ');

            if (parts.length === 2) {
                // Format: "10:30 PM"
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
                // Format: "22:30"
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

            // Set target date to today with the specified time
            let target = new Date(now);
            target.setHours(targetTime.hour, targetTime.minute, 0, 0);

            // If target time has passed today, set to tomorrow
            if (now > target) {
                target.setDate(target.getDate() + 1);
            }

            const diff = target - now;

            if (diff <= 0) {
                // Result should be available
                const timerElement = document.getElementById('countdownTimer');
                if (timerElement) {
                    timerElement.innerHTML = '<span style="color: #4caf50;">✓ Result Available</span>';
                }
                // Refresh page to update results
                setTimeout(function () {
                    location.reload();
                }, 60000); // Refresh every minute to check for updates
                return;
            }

            // Calculate hours, minutes, seconds
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            // Update countdown display
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

        // Add countdown timer to the header
        const headerBox = document.querySelector('.game-header-box');
        if (headerBox) {
            const countdownDiv = document.createElement('div');
            countdownDiv.className = 'countdown-timer';
            countdownDiv.id = 'countdownTimer';
            headerBox.appendChild(countdownDiv);

            // Update countdown every second
            updateCountdown();
            setInterval(updateCountdown, 1000);
        }

        // Auto-refresh the page at midnight to update results
        function scheduleMidnightRefresh() {
            const now = new Date();
            const tomorrow = new Date(now);
            tomorrow.setDate(tomorrow.getDate() + 1);
            tomorrow.setHours(0, 0, 1, 0); // 12:00:01 AM

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
                            // Parse date from YYYY-MM-DD format
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
    <a href="/terms-and-conditions">Terms & Conditions</a>
    <a href="/disclaimer">Disclaimer</a>
    <p>© 2026 A1 satta live | All Rights Reserved</p>
</div>

<div class="disclaimer">
    !! DISCLAIMER - A1 satta live is a non-commercial informational website. Please view this site at your own risk, All
    The Information Shown On Website Is Sponsored And We Warn You That satta matka Gambling/Satta May Be Banned Or
    Illegal In Your Country. We Are Not Responsible For Any Issues Or Scam..., We Respect All Country Rules/Laws... If
    You Not Agree With Our Site disclaimer Please Quit Our Site Right Now. Thank You.
</div>

<?php require_once 'footer.php'; ?>