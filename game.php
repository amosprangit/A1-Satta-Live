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

// Check if game exists in database directly
$game_result = getGameResults($pdo, $game_name);

// If game not found in database, redirect to home
if (!$game_result) {
    // Try case-insensitive search
    $all_game_names = getGameNames($pdo);
    $found = false;
    foreach ($all_game_names as $db_game) {
        if (strtolower($db_game) === strtolower($game_name)) {
            $game_name = $db_game;
            $game_result = getGameResults($pdo, $game_name);
            $found = true;
            break;
        }
    }

    if (!$found) {
        header('Location: /');
        exit;
    }
}

// Get game data
$current_result = $game_result['today_result'] ?? 'WAIT';
$yesterday_result = $game_result['yesterday_result'] ?? '--';
$result_time = $game_result['result_time'] ?? '';
$display_name = !empty($game_result['display_name']) ? $game_result['display_name'] : strtoupper($game_name);
$table_type = $game_result['table_type'] ?? '1';

// Get game timing from game_timings table
$game_timing = getGameTiming($pdo, $game_name);

// Get all chart data for this game from chart_data table
$chart_data = getAllChartDataForGame($pdo, $game_name, $table_type);

// Set page title
$page_title = strtoupper($display_name) . ' Satta King Results Chart | A1 Satta Live';

require_once 'header.php';
?>

<link rel="stylesheet" href="./css/style.css">

<!-- Game Header -->
<div class="live-box">
    <h1><?php echo strtoupper($display_name); ?></h1>
</div>

<!-- Chart Results Table -->
<div class="table-wrapper" style="margin: 30px 0;">
    <div style="text-align:center; margin:20px;">
        <h2 style="color:#ffd700; font-size: 24px;"><?php echo strtoupper($display_name); ?> - ALL RESULTS CHART</h2>
    </div>

    <table class="result-table">
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
                    $result_date = $row['date'];
                    $result_number = $row['result_number'];
                    $day_name = date('l', strtotime($result_date));
                    ?>
                    <tr>
                        <td class="game-name"><?php echo date('d M Y', strtotime($result_date)); ?></td>
                        <td class="yesterday-result"><?php echo $day_name; ?></td>
                        <td class="today-result">
                            <span class="result-value"><?php echo $result_number; ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" style="text-align: center; padding: 40px; color: #999;">
                        <div style="font-size: 48px; margin-bottom: 10px;">📊</div>
                        <div style="font-size: 18px;">No results data available yet</div>
                        <div style="font-size: 14px; margin-top: 5px;">Check back later for updated results</div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

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

<?