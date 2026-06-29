<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'SATTA RECORD CHART 2026 - A1 satta live';

require_once 'config.php';
require_once 'header.php';

// Get current month/year from URL or default to current
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$month_name = date('F Y', strtotime($month . '-01'));
$year = date('Y', strtotime($month . '-01'));
$month_num = date('m', strtotime($month . '-01'));
$days_in_month = date('t', strtotime($month . '-01'));

// Previous and Next month links
$prev_month = date('Y-m', strtotime($month . '-01 -1 month'));
$next_month = date('Y-m', strtotime($month . '-01 +1 month'));

// Fetch all games dynamically from database
try {
    $stmt = $pdo->query("SELECT * FROM game_results WHERE status = 1 ORDER BY table_type, id");
    $all_games_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $table1_games = [];
    $table2_games = [];

    foreach ($all_games_data as $game) {
        if ($game['table_type'] == 'table2') {
            $table2_games[] = $game;
        } else {
            $table1_games[] = $game;
        }
    }

    // Fallback if empty
    if (empty($table1_games)) {
        $table1_games = [
            ['game_name' => 'disawar', 'display_name' => 'DISAWAR'],
            ['game_name' => 'sadar bazar', 'display_name' => 'SADAR BAZAR'],
            ['game_name' => 'gwalior', 'display_name' => 'GWALIOR'],
            ['game_name' => 'delhi bazar', 'display_name' => 'DELHI BAZAR'],
            ['game_name' => 'shri ganesh', 'display_name' => 'SHRI GANESH'],
            ['game_name' => 'faridabad', 'display_name' => 'FARIDABAD'],
            ['game_name' => 'gaziabad', 'display_name' => 'GAZIABAD'],
            ['game_name' => 'gali', 'display_name' => 'GALI']
        ];
    }

    if (empty($table2_games)) {
        $table2_games = [
            ['game_name' => 'mandi bazar', 'display_name' => 'MANDI BAZAR'],
            ['game_name' => 'bhadra bazar', 'display_name' => 'BHADRA BAZAR'],
            ['game_name' => 'sialkot', 'display_name' => 'SIALKOT'],
            ['game_name' => 'lion bazar', 'display_name' => 'LION BAZAR'],
            ['game_name' => 'gaziabad king', 'display_name' => 'GAZIABAD KING'],
            ['game_name' => 'dehradun city', 'display_name' => 'DEHRADUN CITY'],
            ['game_name' => 'daman', 'display_name' => 'DAMAN'],
            ['game_name' => 'pushkar', 'display_name' => 'PUSHKAR']
        ];
    }
} catch (PDOException $e) {
    $table1_games = [
        ['game_name' => 'disawar', 'display_name' => 'DISAWAR'],
        ['game_name' => 'sadar bazar', 'display_name' => 'SADAR BAZAR'],
        ['game_name' => 'gwalior', 'display_name' => 'GWALIOR'],
        ['game_name' => 'delhi bazar', 'display_name' => 'DELHI BAZAR'],
        ['game_name' => 'shri ganesh', 'display_name' => 'SHRI GANESH'],
        ['game_name' => 'faridabad', 'display_name' => 'FARIDABAD'],
        ['game_name' => 'gaziabad', 'display_name' => 'GAZIABAD'],
        ['game_name' => 'gali', 'display_name' => 'GALI']
    ];
    $table2_games = [
        ['game_name' => 'mandi bazar', 'display_name' => 'MANDI BAZAR'],
        ['game_name' => 'bhadra bazar', 'display_name' => 'BHADRA BAZAR'],
        ['game_name' => 'sialkot', 'display_name' => 'SIALKOT'],
        ['game_name' => 'lion bazar', 'display_name' => 'LION BAZAR'],
        ['game_name' => 'gaziabad king', 'display_name' => 'GAZIABAD KING'],
        ['game_name' => 'dehradun city', 'display_name' => 'DEHRADUN CITY'],
        ['game_name' => 'daman', 'display_name' => 'DAMAN'],
        ['game_name' => 'pushkar', 'display_name' => 'PUSHKAR']
    ];
}

// Generate all dates for the month (YYYY-MM-DD format)
$all_dates = [];
for ($day = 1; $day <= $days_in_month; $day++) {
    $date_str = $year . '-' . str_pad($month_num, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
    $all_dates[] = $date_str;
}

// Fetch all chart data for this month using new schema
try {
    // Get all data for this month
    $start_date = $year . '-' . str_pad($month_num, 2, '0', STR_PAD_LEFT) . '-01';
    $end_date = $year . '-' . str_pad($month_num, 2, '0', STR_PAD_LEFT) . '-' . $days_in_month;

    $stmt = $pdo->prepare("SELECT * FROM chart_data WHERE chart_date BETWEEN ? AND ? ORDER BY chart_date ASC");
    $stmt->execute([$start_date, $end_date]);
    $chart_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get unique dates that have data
    $dates_with_data = [];
    foreach ($chart_data as $row) {
        $dates_with_data[$row['chart_date']] = true;
    }
    $dates_with_data = array_keys($dates_with_data);
    sort($dates_with_data);

} catch (PDOException $e) {
    $chart_data = [];
    $dates_with_data = [];
    $db_error = $e->getMessage();
}

// Build lookup array: [date][game_name] = result
$data_lookup = [];
foreach ($chart_data as $row) {
    $data_lookup[$row['chart_date']][$row['game_name']] = $row['result'];
}

// Get disawer display info
try {
    $stmt = $pdo->prepare("SELECT * FROM game_results WHERE LOWER(game_name) = 'disawar'");
    $stmt->execute();
    $disawer_info = $stmt->fetch(PDO::FETCH_ASSOC);
    $disawer_display = $disawer_info['display_name'] ?? 'DISAWAR';
    $disawer_today = $disawer_info['today_result'] ?? '--';
    $disawer_yesterday = $disawer_info['yesterday_result'] ?? '--';
    $disawer_time = $disawer_info['result_time'] ?? '5:15 AM';
} catch (PDOException $e) {
    $disawer_display = 'DISAWAR';
    $disawer_today = '--';
    $disawer_yesterday = '--';
    $disawer_time = '5:15 AM';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #1a1a1a, #2d2d2d);
            color: #fff;
            min-height: 100vh;
        }

        .chart-main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 15px;
            background: #fef9e6;
        }

        .chart-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .chart-header h1 {
            color: #c49a00;
            font-size: clamp(22px, 4vw, 32px);
            margin-bottom: 5px;
        }

        .chart-header h5 {
            font-size: clamp(16px, 3vw, 20px);
            color: #333;
            margin: 8px 0;
        }

        .month-nav {
            text-align: center;
            margin: 20px 0;
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .month-nav a {
            background: #ffd700;
            padding: 8px 20px;
            text-decoration: none;
            color: #000;
            border-radius: 30px;
            font-weight: bold;
            font-size: 13px;
            transition: 0.3s;
            border: 1px solid #333;
            white-space: nowrap;
        }

        .month-nav a:hover {
            background: #e6c200;
            transform: scale(1.02);
        }

        .chart-table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin: 15px 0;
            background: #fff;
            border-radius: 12px;
            padding: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .chart-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            min-width: 700px;
        }

        .chart-table th {
            background: #1e1e2a;
            color: #ffd700;
            padding: 10px 6px;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            border: 1px solid #333;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .chart-table th:first-child {
            position: sticky;
            left: 0;
            z-index: 2;
            background: #1e1e2a;
        }

        .chart-table td {
            border: 1px solid #ddd;
            padding: 8px 5px;
            text-align: center;
            font-size: 12px;
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
            background: #fff8e7;
            font-weight: bold;
            color: #c49a00;
            white-space: nowrap;
            position: sticky;
            left: 0;
            z-index: 1;
        }

        .chart-number-box {
            background: #1e1e2a;
            display: inline-block;
            padding: 3px 10px;
            border-radius: 15px;
            color: #ffd966;
            font-weight: bold;
            font-size: 12px;
            min-width: 40px;
        }

        .chart-number-box.empty {
            background: #f0f0f0;
            color: #ccc;
        }

        .chart-disawer-section {
            background: linear-gradient(135deg, #ffd700, #ffcc00);
            text-align: center;
            padding: 15px;
            margin: 15px 0;
            border-radius: 12px;
        }

        .chart-disawer-section .disawer-title {
            font-size: clamp(24px, 5vw, 36px);
            font-weight: bold;
            color: #000;
            letter-spacing: 3px;
        }

        .chart-disawer-section .disawer-time {
            font-size: 14px;
            color: #333;
            margin: 3px 0;
        }

        .chart-disawer-section .disawer-arrow {
            font-size: clamp(18px, 3vw, 24px);
            font-weight: bold;
            letter-spacing: 6px;
            color: #000;
        }

        .section-title {
            color: #333;
            margin: 15px 0 10px;
            text-align: center;
            font-size: clamp(16px, 3vw, 20px);
            font-weight: bold;
        }

        .back-home-btn {
            display: inline-block;
            margin-top: 15px;
            background: #6c757d;
            color: white;
            padding: 10px 25px;
            text-decoration: none;
            border-radius: 30px;
            font-weight: bold;
            transition: 0.3s;
            font-size: 14px;
        }

        .back-home-btn:hover {
            background: #5a6268;
            color: white;
        }

        .no-data {
            text-align: center;
            padding: 30px;
            color: #999;
            font-size: 14px;
        }

        .db-error {
            background: #fff3cd;
            color: #856404;
            padding: 12px;
            margin: 15px;
            border-radius: 8px;
            border: 1px solid #ffeeba;
            text-align: center;
            font-size: 13px;
        }

        .footer {
            background: #000;
            text-align: center;
            padding: 20px;
            border-top: 1px solid #333;
            margin-top: 30px;
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
        }

        @media (max-width: 768px) {
            .chart-main-container {
                padding: 10px;
            }

            .chart-table-wrapper {
                padding: 4px;
                border-radius: 8px;
            }

            .chart-table {
                min-width: 500px;
            }

            .chart-table th,
            .chart-table td {
                font-size: 10px;
                padding: 5px 3px;
            }

            .chart-number-box {
                padding: 2px 6px;
                font-size: 10px;
                min-width: 30px;
                border-radius: 10px;
            }

            .month-nav a {
                padding: 6px 12px;
                font-size: 11px;
            }

            .footer a {
                margin: 0 8px;
                font-size: 11px;
            }
        }

        @media (max-width: 480px) {
            .chart-table {
                min-width: 400px;
            }

            .chart-table th,
            .chart-table td {
                font-size: 9px;
                padding: 4px 2px;
            }

            .chart-number-box {
                padding: 1px 5px;
                font-size: 9px;
                min-width: 25px;
            }

            .month-nav {
                gap: 5px;
            }

            .month-nav a {
                padding: 5px 10px;
                font-size: 10px;
            }
        }
    </style>
</head>

<body>

    <div class="chart-main-container">
        <div class="chart-header">
            <h1>SATTA RECORD CHART <?php echo $year; ?></h1>
            <h5><?php echo $month_name; ?> RESULT CHART</h5>
        </div>

        <div class="month-nav">
            <a href="?month=<?php echo $prev_month; ?>">◀ PREVIOUS MONTH</a>
            <a href="?month=<?php echo date('Y-m'); ?>">📅 CURRENT MONTH</a>
            <a href="?month=<?php echo $next_month; ?>">NEXT MONTH ▶</a>
        </div>

        <?php if (isset($db_error)): ?>
            <div class="db-error">
                ⚠️ Database Notice: <?php echo htmlspecialchars($db_error); ?>
            </div>
        <?php endif; ?>

        <!-- TABLE 1 - Main Games Chart -->
        <div class="section-title">📋 MAIN GAMES CHART</div>
        <div class="chart-table-wrapper">
            <table class="chart-table">
                <thead>
                    <tr>
                        <th>DATE</th>
                        <?php foreach ($table1_games as $game): ?>
                            <th><?php echo htmlspecialchars(strtoupper($game['display_name'] ?? $game['game_name'])); ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($all_dates)): ?>
                        <?php foreach ($all_dates as $date_str):
                            // Format date for display (DD-MM)
                            $date_parts = explode('-', $date_str);
                            $display_date = $date_parts[2] . '-' . $date_parts[1];
                            ?>
                            <tr>
                                <td class="date-col"><strong><?php echo $display_date; ?></strong></td>
                                <?php foreach ($table1_games as $game):
                                    $result = $data_lookup[$date_str][$game['game_name']] ?? null;
                                    ?>
                                    <td>
                                        <span class="chart-number-box <?php echo !$result ? 'empty' : ''; ?>">
                                            <?php echo $result ? htmlspecialchars($result) : '--'; ?>
                                        </span>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo count($table1_games) + 1; ?>" class="no-data">
                                📭 No chart data available for <?php echo $month_name; ?>.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- TABLE 2 - Extra Games Chart -->
        <div class="section-title">📋 EXTRA GAMES CHART</div>
        <div class="chart-table-wrapper">
            <table class="chart-table">
                <thead>
                    <tr>
                        <th>DATE</th>
                        <?php foreach ($table2_games as $game): ?>
                            <th><?php echo htmlspecialchars(strtoupper($game['display_name'] ?? $game['game_name'])); ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($all_dates)): ?>
                        <?php foreach ($all_dates as $date_str):
                            // Format date for display (DD-MM)
                            $date_parts = explode('-', $date_str);
                            $display_date = $date_parts[2] . '-' . $date_parts[1];
                            ?>
                            <tr>
                                <td class="date-col"><strong><?php echo $display_date; ?></strong></td>
                                <?php foreach ($table2_games as $game):
                                    $result = $data_lookup[$date_str][$game['game_name']] ?? null;
                                    ?>
                                    <td>
                                        <span class="chart-number-box <?php echo !$result ? 'empty' : ''; ?>">
                                            <?php echo $result ? htmlspecialchars($result) : '--'; ?>
                                        </span>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo count($table2_games) + 1; ?>" class="no-data">
                                📭 No chart data available for <?php echo $month_name; ?>.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="text-align: center; margin: 25px 0 15px;">
            <a href="index.php" class="back-home-btn">🏠 BACK TO HOME</a>
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
        !! DISCLAIMER - A1 satta live is a non-commercial informational website. Please view this site at your own risk,
        All The Information Shown On Website Is Sponsored And We Warn You That satta matka Gambling/Satta May Be Banned
        Or Illegal In Your Country. We Are Not Responsible For Any Issues Or Scam..., We Respect All Country
        Rules/Laws... If You Not Agree With Our Site disclaimer Please Quit Our Site Right Now. Thank You.
    </div>

</body>

</html>