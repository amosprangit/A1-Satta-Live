<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'SATTA RECORD CHART 2026 - A1 satta live';

// Try to include config, die with message if fails
if (!file_exists('config.php')) {
    die("Error: config.php file not found. Please check file path.");
}
require_once 'config.php';

if (!file_exists('header.php')) {
    die("Error: header.php file not found. Please check file path.");
}
require_once 'header.php';

// Check database connection
if (!isset($pdo) || !($pdo instanceof PDO)) {
    die("Error: Database connection failed. Please check config.php settings.");
}

// Get current month/year from URL or default to current
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$month_name = date('F Y', strtotime($month . '-01'));

// Define the games in order
$table1_games = ['sadar bazar', 'gwalior', 'delhi bazar', 'delhi matka', 'shri ganesh', 'agra', 'faridabad', 'alwar', 'gaziabad', 'dwarka', 'gali', 'disawer'];
$table2_games = ['hr satta', 'ujjala super', 'kkr city', 'madhupuri', 'karol bagh', 'delhi darbar', 'new ganga', 'fatehabad', 'raj shree', 'mandi bazar', 'dehradun city', 'daman'];

// Get all dates in this month that have data
try {
    $stmt = $pdo->prepare("SELECT DISTINCT result_number FROM chart_data WHERE DATE_FORMAT(result_number, '%Y-%m') = ? ORDER BY result_number");
    $stmt->execute([$month]);
    $date_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If table doesn't exist or query fails
    $date_list = [];
    $db_error = $e->getMessage();
}

// Get disawer result for display
try {
    $disawer_result_data = $pdo->query("SELECT today_result, yesterday_result FROM game_results WHERE game_name = 'disawer'")->fetch(PDO::FETCH_ASSOC);
    $disawer_today = $disawer_result_data ? $disawer_result_data['today_result'] : '71';
    $disawer_yesterday = $disawer_result_data ? $disawer_result_data['yesterday_result'] : '52';
} catch (PDOException $e) {
    $disawer_today = '71';
    $disawer_yesterday = '52';
}

// Generate dates for current month
$year = date('Y', strtotime($month . '-01'));
$month_num = date('m', strtotime($month . '-01'));
$days_in_month = date('t', strtotime($month . '-01'));

// Previous and Next month links
$prev_month = date('Y-m', strtotime($month . '-01 -1 month'));
$next_month = date('Y-m', strtotime($month . '-01 +1 month'));

// Function to get result for a specific game and date
function getGameResult($pdo, $game_name, $result_number, $chart_type = null)
{
    try {
        if ($chart_type) {
            $stmt = $pdo->prepare("SELECT result_number FROM chart_data WHERE game_name = ? AND result_number = ? AND chart_type = ?");
            $stmt->execute([$game_name, $result_number, $chart_type]);
        } else {
            $stmt = $pdo->prepare("SELECT result_number FROM chart_data WHERE game_name = ? AND result_number = ? AND (chart_type IS NULL OR chart_type = '')");
            $stmt->execute([$game_name, $result_number]);
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['result_number'] : '--';
    } catch (PDOException $e) {
        return '--';
    }
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
            padding: 20px;
            background: #fef9e6;
        }

        .chart-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .chart-header h1 {
            color: #c49a00;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .chart-header h5 {
            font-size: 20px;
            color: #333;
            margin: 10px 0;
        }

        .month-nav {
            text-align: center;
            margin: 25px 0;
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .month-nav a {
            background: #ffd700;
            padding: 10px 25px;
            text-decoration: none;
            color: #000;
            border-radius: 40px;
            font-weight: bold;
            font-size: 14px;
            transition: 0.3s;
            border: 1px solid #333;
        }

        .month-nav a:hover {
            background: #e6c200;
            transform: scale(1.02);
        }

        .chart-table-wrapper {
            overflow-x: auto;
            margin: 20px 0;
            background: #fff;
            border-radius: 16px;
            padding: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .chart-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            min-width: 800px;
        }

        .chart-table th {
            background: #1e1e2a;
            color: #ffd700;
            padding: 12px 8px;
            font-size: 13px;
            font-weight: bold;
            text-align: center;
            border: 1px solid #333;
            white-space: nowrap;
        }

        .chart-table td {
            border: 1px solid #ddd;
            padding: 10px 6px;
            text-align: center;
            font-size: 13px;
            color: #333;
        }

        .chart-table tbody tr:hover {
            background: #fff8e0;
        }

        .date-col {
            background: #fff8e7;
            font-weight: bold;
            color: #c49a00;
        }

        .chart-number-box {
            background: #1e1e2a;
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            color: #ffd966;
            font-weight: bold;
            font-size: 13px;
            min-width: 50px;
        }

        .chart-disawer-section {
            background: linear-gradient(135deg, #ffd700, #ffcc00);
            text-align: center;
            padding: 20px;
            margin: 20px 0;
            border-radius: 16px;
        }

        .chart-disawer-section .disawer-title {
            font-size: 36px;
            font-weight: bold;
            color: #000;
            letter-spacing: 4px;
        }

        .chart-disawer-section .disawer-time {
            font-size: 16px;
            color: #333;
            margin: 5px 0;
        }

        .chart-disawer-section .disawer-arrow {
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 8px;
            color: #000;
        }

        .back-home-btn {
            display: inline-block;
            margin-top: 20px;
            background: #6c757d;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 40px;
            font-weight: bold;
            transition: 0.3s;
        }

        .back-home-btn:hover {
            background: #5a6268;
            color: white;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 16px;
        }

        .db-error {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            margin: 20px;
            border-radius: 8px;
            border: 1px solid #ffeeba;
            text-align: center;
        }

        .footer {
            background: #000;
            text-align: center;
            padding: 30px;
            border-top: 1px solid #333;
            margin-top: 40px;
        }

        .footer a {
            color: #ffd700;
            text-decoration: none;
            margin: 0 20px;
            font-size: 16px;
        }

        .footer p {
            color: #666;
            margin-top: 20px;
            font-size: 14px;
        }

        .disclaimer {
            background: #111;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #888;
        }

        @media (max-width: 768px) {

            .chart-table th,
            .chart-table td {
                font-size: 11px;
                padding: 6px 3px;
            }

            .chart-number-box {
                padding: 2px 8px;
                font-size: 11px;
                min-width: 35px;
            }

            .month-nav a {
                padding: 6px 15px;
                font-size: 12px;
            }

            .chart-header h1 {
                font-size: 24px;
            }

            .chart-disawer-section .disawer-title {
                font-size: 28px;
            }

            .footer a {
                margin: 0 10px;
                font-size: 12px;
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
                ⚠️ Database Notice: <?php echo htmlspecialchars($db_error); ?><br>
                <small>Check if the 'chart_data' table exists in your database.</small>
            </div>
        <?php endif; ?>

        <!-- TABLE 1 - Main Games Chart -->
        <h3 style="color: #333; margin: 20px 0; text-align: center;">📋 MAIN GAMES CHART</h3>
        <div class="chart-table-wrapper">
            <table class="chart-table">
                <thead>
                    <tr>
                        <th>DATE</th>
                        <?php foreach ($table1_games as $game): ?>
                            <th><?php echo strtoupper($game); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($date_list)): ?>
                        <?php foreach ($date_list as $date_row):
                            $date_formatted = date('d-m', strtotime($date_row['result_number']));
                            ?>
                            <tr>
                                <td class="date-col"><strong><?php echo $date_formatted; ?></strong></td>
                                <?php foreach ($table1_games as $game): ?>
                                    <td>
                                        <span class="chart-number-box">
                                            <?php echo htmlspecialchars(getGameResult($pdo, $game, $date_row['result_number'], 'table1')); ?>
                                        </span>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo count($table1_games) + 1; ?>" class="no-data">
                                📭 No chart data available for <?php echo $month_name; ?>.
                                <?php if (isset($db_error)): ?>
                                    <br>Please add data through the admin panel.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- TABLE 2 - Extra Games Chart -->
        <h3 style="color: #333; margin: 20px 0; text-align: center;">📋 EXTRA GAMES CHART</h3>
        <div class="chart-table-wrapper">
            <table class="chart-table">
                <thead>
                    <tr>
                        <th>DATE</th>
                        <?php foreach ($table2_games as $game): ?>
                            <th><?php echo strtoupper($game); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($date_list)): ?>
                        <?php foreach ($date_list as $date_row):
                            $date_formatted = date('d-m', strtotime($date_row['result_number']));
                            ?>
                            <tr>
                                <td class="date-col"><strong><?php echo $date_formatted; ?></strong></td>
                                <?php foreach ($table2_games as $game): ?>
                                    <td>
                                        <span class="chart-number-box">
                                            <?php echo htmlspecialchars(getGameResult($pdo, $game, $date_row['result_number'], 'table2')); ?>
                                        </span>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo count($table2_games) + 1; ?>" class="no-data">
                                📭 No chart data available for <?php echo $month_name; ?>.
                                <?php if (isset($db_error)): ?>
                                    <br>Please add data through the admin panel.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="text-align: center; margin: 30px 0 20px;">
            <a href="index.php" class="back-home-btn">🏠 BACK TO HOME</a>
        </div>
    </div>

    <!-- Footer -->
    <?php if (file_exists('footer.php')): ?>
        <?php require_once 'footer.php'; ?>
    <?php else: ?>
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
    <?php endif; ?>

</body>

</html>