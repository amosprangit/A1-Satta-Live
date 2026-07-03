<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'SATTA RECORD CHART 2026 - A1 satta live';

require_once 'config.php';
require_once 'includes/game-functions.php';
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

// ============================================
// FETCH ALL GAMES (Regular + Custom Tables)
// ============================================
$table1_games = [];  // Regular games
$table2_games = [];  // Custom table games

// Get regular games from game_results
try {
    $stmt = $pdo->query("SELECT * FROM game_results WHERE status = 1 ORDER BY FIELD(game_name, 'pushkar', 'sadar bazar', 'gwalior', 'delhi bazar', 'shri ganesh', 'faridabad', 'gaziabad', 'gali', 'disawar')");
    $regular_games = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($regular_games as $game) {
        if ($game['table_type'] == 'table2') {
            $table2_games[] = $game;
        } else {
            $table1_games[] = $game;
        }
    }
} catch (PDOException $e) {
    error_log("Error fetching regular games: " . $e->getMessage());
}

// Get custom table games
try {
    $customTables = getCustomTables($pdo);
    foreach ($customTables as $customTable) {
        $customGames = getCustomTableGames($pdo, $customTable['id']);
        foreach ($customGames as $game) {
            // Add custom table name to identify source
            $game['custom_table_name'] = $customTable['table_name'];
            $table2_games[] = $game;
        }
    }
} catch (PDOException $e) {
    error_log("Error fetching custom games: " . $e->getMessage());
}

// Fallback if empty
if (empty($table1_games)) {
    $table1_games = [
        ['game_name' => 'pushkar', 'display_name' => 'PUSHKAR'],
        ['game_name' => 'sadar bazar', 'display_name' => 'SADAR BAZAR'],
        ['game_name' => 'gwalior', 'display_name' => 'GWALIOR'],
        ['game_name' => 'delhi bazar', 'display_name' => 'DELHI BAZAR'],
        ['game_name' => 'shri ganesh', 'display_name' => 'SHRI GANESH'],
        ['game_name' => 'faridabad', 'display_name' => 'FARIDABAD'],
        ['game_name' => 'gaziabad', 'display_name' => 'GAZIABAD'],
        ['game_name' => 'gali', 'display_name' => 'GALI'],
        ['game_name' => 'disawar', 'display_name' => 'DISAWAR']
    ];
}

// Generate all dates for the month (YYYY-MM-DD format)
$all_dates = [];
for ($day = 1; $day <= $days_in_month; $day++) {
    $date_str = $year . '-' . str_pad($month_num, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
    $all_dates[] = $date_str;
}

// Fetch all chart data for this month
try {
    $start_date = $year . '-' . str_pad($month_num, 2, '0', STR_PAD_LEFT) . '-01';
    $end_date = $year . '-' . str_pad($month_num, 2, '0', STR_PAD_LEFT) . '-' . $days_in_month;

    $stmt = $pdo->prepare("SELECT * FROM chart_data WHERE chart_date BETWEEN ? AND ? ORDER BY chart_date ASC");
    $stmt->execute([$start_date, $end_date]);
    $chart_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Build lookup array: [date][game_name] = result
    $data_lookup = [];
    foreach ($chart_data as $row) {
        $data_lookup[$row['chart_date']][$row['game_name']] = $row['result'];
    }

} catch (PDOException $e) {
    $chart_data = [];
    $data_lookup = [];
    $db_error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="./css/chart.css">
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
        <?php if (!empty($table1_games)): ?>
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
        <?php endif; ?>

        <!-- TABLE 2 - Extra Games & Custom Tables -->
        <?php if (!empty($table2_games)): ?>
            <div class="section-title">📋 EXTRA GAMES & CUSTOM TABLES CHART</div>
            <div class="chart-table-wrapper">
                <table class="chart-table">
                    <thead>
                        <tr>
                            <th>DATE</th>
                            <?php foreach ($table2_games as $game): ?>
                                <th>
                                    <?php
                                    $display_name = strtoupper($game['display_name'] ?? $game['game_name']);
                                    // Add custom table indicator
                                    if (isset($game['custom_table_name'])) {
                                        $display_name .= '';
                                    }
                                    echo htmlspecialchars($display_name);
                                    ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($all_dates)): ?>
                            <?php foreach ($all_dates as $date_str):
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
        <?php endif; ?>

        <!-- Legend for Custom Tables -->
        <?php
        $has_custom = false;
        foreach ($table2_games as $game) {
            if (isset($game['custom_table_name'])) {
                $has_custom = true;
                break;
            }
        }
        if ($has_custom):
            ?>
            <div style="text-align: center; margin: 10px 0; font-size: 12px; color: #888;">
                <span>* Games marked with * are from Custom Tables</span>
            </div>
        <?php endif; ?>

        <div style="text-align: center; margin: 25px 0 15px;">
            <a href="index.php" class="back-home-btn">🏠 BACK TO HOME</a>
            <a href="game.php?game=disawar" class="back-home-btn" style="margin-left: 10px;">📊 VIEW GAME CHARTS</a>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <a href="/privacy-policy">Privacy Policy</a>
        <a href="/terms-and-conditions">Terms & Conditions</a>
    </div>
</body>

</html>
<?php include 'footer.php'; ?>