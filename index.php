<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

// Set page title for header
$page_title = 'A1 satta live | Delhi Bazar Satta King 2026 Results';

// Fetch website content
$khaiwal_line1 = getWebsiteContent($pdo, 'khaiwal_line1') ?: '🔰 *Online khaiwal* 🔰';
$khaiwal_line2 = getWebsiteContent($pdo, 'khaiwal_line2') ?: '*( Raj Bhai Khaiwal )*';
$whatsapp_number = getWebsiteContent($pdo, 'whatsapp_number') ?: '919812287328';
$whatsapp_text = getWebsiteContent($pdo, 'whatsapp_text') ?: 'WhatsApp';
$whatsapp_subtext = getWebsiteContent($pdo, 'whatsapp_subtext') ?: 'Click to Chat';
$top_whatsapp_number = getWebsiteContent($pdo, 'top_whatsapp_number') ?: '919812287328';
$top_whatsapp_text = getWebsiteContent($pdo, 'top_whatsapp_text') ?: '"NOW WHATSAPP PLAYERS CAN ALSO JOIN OUR WHATSAPP CHANNEL TO GET RESULTS QUICKLY AND RECEIVE SUPERFAST RESULTS."';
$top_whatsapp_btn = getWebsiteContent($pdo, 'top_whatsapp_btn') ?: 'Click to chat';
$telegram_link = getWebsiteContent($pdo, 'telegram_link') ?: 'https://t.me/a7Resultupdates';
$telegram_text = getWebsiteContent($pdo, 'telegram_text') ?: '"NOW TELEGRAM PLAYERS CAN ALSO JOIN OUR TELEGRAM CHANNEL TO GET RESULTS QUICKLY AND RECEIVE SUPERFAST RESULTS."';
$telegram_btn = getWebsiteContent($pdo, 'telegram_btn') ?: 'Click to Connect';

// Fetch disawer result - Updated for new schema
try {
    $stmt = $pdo->prepare("SELECT * FROM game_results WHERE LOWER(game_name) = 'disawar' AND status = 1");
    $stmt->execute();
    $disawer = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $disawer = false;
}
$disawer_result = $disawer['today_result'] ?? '86';
$disawer_yesterday = $disawer['yesterday_result'] ?? '05';
$disawer_display_name = $disawer['display_name'] ?? 'DISAWAR';
$disawer_time = $disawer['result_time'] ?? '5:15 AM';

// ===== FORCE FRESH DATA FROM DATABASE =====
$pdo->query("SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED");

// Fetch all games dynamically from database - Updated for new schema
try {
    // Force fresh query with NO CACHE and status = 1 (active)
    $stmt = $pdo->query("SELECT SQL_NO_CACHE * FROM game_results WHERE LOWER(game_name) != 'disawar' AND status = 1 ORDER BY table_type, id");
    $all_games_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Create lookup array and separate by table type
    $all_results = [];
    $table1_game_names = [];
    $table2_game_names = [];

    foreach ($all_games_data as $game) {
        // Use game_name as key (lowercase for consistency)
        $key = strtolower($game['game_name']);
        $all_results[$key] = $game;
        if ($game['table_type'] == 'table2') {
            $table2_game_names[] = $game['game_name'];
        } else {
            $table1_game_names[] = $game['game_name'];
        }
    }

    // Fallback if database is empty
    if (empty($table1_game_names) && empty($table2_game_names)) {
        $table1_game_names = ['sadar bazar', 'gwalior', 'delhi bazar', 'shri ganesh', 'faridabad', 'gaziabad', 'gali'];
        $table2_game_names = ['mandi bazar', 'bhadra bazar', 'sialkot', 'lion bazar', 'gaziabad king', 'dehradun city', 'daman', 'pushkar'];
    }
} catch (PDOException $e) {
    // Fallback to default lists if database query fails
    $table1_game_names = ['sadar bazar', 'gwalior', 'delhi bazar', 'shri ganesh', 'faridabad', 'gaziabad', 'gali'];
    $table2_game_names = ['mandi bazar', 'bhadra bazar', 'sialkot', 'lion bazar', 'gaziabad king', 'dehradun city', 'daman', 'pushkar'];
    $all_results = [];
}

// Get table header display name
$table_header_display = 'सट्टा का नाम';

// Fetch game timings dynamically
try {
    $stmt = $pdo->query("SELECT * FROM game_timings WHERE is_active = 1 ORDER BY display_order, id");
    $game_timings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $game_timings = [];
}

// Fetch game rates dynamically
try {
    $stmt = $pdo->query("SELECT * FROM game_rates WHERE is_active = 1 ORDER BY display_order, id");
    $rates = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $rates = [];
}

// Fetch all game names for chart selector - Updated for new schema
try {
    $stmt = $pdo->query("SELECT DISTINCT game_name FROM game_results WHERE status = 1 ORDER BY game_name");
    $all_game_names = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $all_game_names = array_merge(['disawar'], $table1_game_names, $table2_game_names);
}

// Fetch all game timings for admin editor
if (isAdminLoggedIn()) {
    try {
        $stmt = $pdo->query("SELECT * FROM game_timings ORDER BY display_order, id");
        $all_game_timings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $all_game_timings = [];
    }

    try {
        $stmt = $pdo->query("SELECT * FROM game_rates ORDER BY display_order, id");
        $all_game_rates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $all_game_rates = [];
    }
}

require_once 'header.php';
?>

<link rel="stylesheet" href="./css/style.css">
<meta charset="UTF-8">

<!-- Success/Error Messages from admin actions -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="admin-message success-msg"
        style="background: #d4edda; color: #155724; padding: 15px; margin: 10px 20px; border-radius: 10px; border-left: 5px solid #28a745; text-align: center; font-weight: bold;">
        <?php echo $_SESSION['success'];
        unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="admin-message error-msg"
        style="background: #f8d7da; color: #721c24; padding: 15px; margin: 10px 20px; border-radius: 10px; border-left: 5px solid #dc3545; text-align: center; font-weight: bold;">
        <?php echo $_SESSION['error'];
        unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<!-- Live Box -->
<?php
// Get the most recently updated game (for live box) - Updated for new schema
try {
    $stmt = $pdo->query("SELECT SQL_NO_CACHE * FROM game_results WHERE LOWER(game_name) != 'disawar' AND status = 1 AND is_latest = 1 ORDER BY id DESC LIMIT 1");
    $latest_game = $stmt->fetch(PDO::FETCH_ASSOC);

    // If no other game found, use disawar
    if (!$latest_game) {
        $latest_game = $disawer;
    }
} catch (PDOException $e) {
    // If is_latest column doesn't exist, use disawar
    $latest_game = $disawer;
}

$live_game_name = !empty($latest_game['display_name']) ? $latest_game['display_name'] : strtoupper($latest_game['game_name']);
$live_result = $latest_game['today_result'] ?? 'WAIT';
$live_yesterday = $latest_game['yesterday_result'] ?? '--';
$live_time = $latest_game['result_time'] ?? '--';
?>

<div class="live-box">
    <div id="clock" class="clock"></div>
    <h2>हा भाई यही आती हे सबसे पहले खबर रूको और देखो</h2>
    <h1><?php echo htmlspecialchars(strtoupper($live_game_name)); ?></h1>
    <div class="result-number">
        <?php
        if ($live_result == 'WAIT' || $live_result == '-1' || empty($live_result)) {
            echo '<span style="color: #d32f2f; font-size: 24px;">⏳ WAIT</span>';
        } else {
            echo htmlspecialchars($live_result);
        }
        ?>
    </div>
    <div style="font-size: 14px; color: #aaa; margin-top: 10px;">
        ⏰ <?php echo htmlspecialchars($live_time); ?>
    </div>
</div>

<div class="highlight"><?php echo htmlspecialchars(strtolower($live_game_name)); ?></div>
<div class="disawer-timing"><?php echo htmlspecialchars($live_time); ?></div>
<div class="disawer-arrow">
    <?php echo htmlspecialchars($live_yesterday); ?> ➡️
    <?php
    if ($live_result == 'WAIT' || $live_result == '-1' || empty($live_result)) {
        echo 'WAIT';
    } else {
        echo htmlspecialchars($live_result);
    }
    ?>
</div>

<!-- WhatsApp & Telegram Cards -->
<div class="social-cards-wrapper">
    <div class="social-card whatsapp-card">
        <div class="social-card-content">
            <p class="social-text">
                <?php echo htmlspecialchars($top_whatsapp_text); ?>
            </p>
            <a href="https://wa.me/<?php echo htmlspecialchars($top_whatsapp_number); ?>" target="_blank"
                class="social-btn whatsapp-btn">
                <?php echo htmlspecialchars($top_whatsapp_btn); ?>
            </a>
        </div>
    </div>

    <div class="social-card telegram-card">
        <div class="social-card-content">
            <p class="social-text">
                <?php echo htmlspecialchars($telegram_text); ?>
            </p>
            <a href="<?php echo htmlspecialchars($telegram_link); ?>" target="_blank" class="social-btn telegram-btn">
                <?php echo htmlspecialchars($telegram_btn); ?>
            </a>
        </div>
    </div>
</div>

<!-- Game Timings -->
<div id="play-time-info" style="position: relative;">
    <?php if (isAdminLoggedIn()): ?>
        <button onclick="openPlayTimeEditor()" style="
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ffd700;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            transition: transform 0.2s;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
        " onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
            ✏️
        </button>
    <?php endif; ?>

    <p><?php echo htmlspecialchars($khaiwal_line1); ?></p>
    <p><?php echo htmlspecialchars($khaiwal_line2); ?></p>
    <p>🎊🎊🎊🎊🎊</p>
    <p>🔰 *All game timing* 🔰</p>

    <div id="timings-container">
        <?php foreach ($game_timings as $timing): ?>
            <p data-timing-id="<?php echo $timing['id']; ?>">
                <?php echo htmlspecialchars($timing['emoji']); ?>
                *<?php echo htmlspecialchars(ucfirst($timing['game_name'])); ?>...
                <?php echo htmlspecialchars($timing['timing']); ?>*
            </p>
        <?php endforeach; ?>
        <?php if (empty($game_timings)): ?>
            <p>😇 *Disawar... 5:15 AM*</p>
            <p>😇 *Gali... 11:15 PM*</p>
        <?php endif; ?>
    </div>

    <p>*फोन पे, गूगल पे=* *scanner*</p>
    <p>*Rate list* *राधे राधे*</p>

    <div id="rates-container">
        <?php foreach ($rates as $rate): ?>
            <p data-rate-id="<?php echo $rate['id']; ?>">
                *<?php echo htmlspecialchars($rate['rate_type']); ?>=<?php echo htmlspecialchars($rate['rate_value']); ?>*
            </p>
        <?php endforeach; ?>
        <?php if (empty($rates)): ?>
            <p>*जोड़ी रेट=10 ke..960*</p>
            <p>*हरूप रेट=10 ke..90*</p>
        <?php endif; ?>
    </div>

    <p>🙏🏻🙏🏻🙏🏻🙏🏻🙏🏻🙏🏻🙏🏻</p>
    <p>सीधे सट्टा कंपनी का No 1 खाईवाल *Game play करने के लिये नीचे क्लिक करे*</p>
    <div class="whatsapp-button">
        <a href="https://wa.me/<?php echo htmlspecialchars($whatsapp_number); ?>" target="_blank">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="24" height="24">
                <path fill="#25D366"
                    d="M35.5,12.5C31.9,8.9,27.1,7,22,7c-8.3,0-15,6.7-15,15c0,2.7,0.7,5.3,2,7.6L7,41l11.9-3.1c2.2,1.2,4.7,1.8,7.2,1.8h0c8.3,0,15-6.7,15-15C41,18.6,39.1,14.1,35.5,12.5z" />
                <path fill="#FFF"
                    d="M24.1,9.5c-7.2,0-13,5.8-13,13c0,2.3,0.6,4.5,1.7,6.4L11.7,36l7.4-1.9c1.9,1,4,1.6,6.2,1.6c7.2,0,13-5.8,13-13S31.3,9.5,24.1,9.5z M33.6,24.6c-0.5,1.5-2.6,2.8-4.2,3.1c-0.7,0.1-1.3,0.2-1.8,0.2c-0.9,0-1.9-0.3-2.9-0.9c-1.3-0.8-2.4-1.9-3.5-3c-0.9-0.9-1.8-2-2.5-3.1c-0.7-1.1-1.2-2.1-1.2-3c0-0.9,0.3-1.6,0.9-2.1c0.4-0.4,0.9-0.6,1.3-0.6c0.3,0,0.6,0,0.9,0c0.3,0,0.6,0,0.9,0.5c0.3,0.5,0.8,1.5,0.9,1.6c0.1,0.2,0.1,0.4,0,0.6c0,0.2-0.1,0.3-0.2,0.5c-0.1,0.2-0.3,0.4-0.4,0.6c-0.1,0.2-0.2,0.3-0.1,0.5c0.1,0.2,0.5,0.8,0.9,1.3c0.6,0.8,1.3,1.5,2.1,2c0.8,0.5,1.5,0.8,2.1,0.9c0.3,0.1,0.5,0.1,0.7,0c0.2-0.1,0.4-0.2,0.5-0.4c0.1-0.2,0.4-0.4,0.5-0.6c0.1-0.2,0.4-0.2,0.6-0.1c0.2,0.1,1.5,0.7,1.8,0.8c0.3,0.1,0.5,0.2,0.6,0.4c0.1,0.2,0.1,0.8-0.1,1.3C33.9,24.1,33.8,24.3,33.6,24.6z" />
            </svg>
            <?php echo htmlspecialchars($whatsapp_text); ?>
            <p>
                <?php echo htmlspecialchars($whatsapp_subtext); ?>
            </p>
        </a>
    </div>
</div>

<!-- TABLE 1 - Main Games (Dynamic) -->
<div class="table-wrapper">
    <table class="result-table">
        <thead>
            <tr>
                <th><?php echo $table_header_display; ?></th>
                <th>कल आया था</th>
                <th>आज का रिज़ल्ट</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($table1_game_names as $game):
                // Get data from all_results using lowercase key for consistency
                $key = strtolower($game);
                $data = isset($all_results[$key]) ? $all_results[$key] : ['yesterday_result' => '--', 'today_result' => 'WAIT', 'result_time' => '--', 'display_name' => $game];
                $display_name = !empty($data['display_name']) ? $data['display_name'] : strtoupper($game);
                $is_wait = ($data['today_result'] == 'WAIT' || $data['today_result'] == '-1' || empty($data['today_result']));
                ?>
                <tr>
                    <td class="game-name">
                        <a
                            href="game.php?game=<?php echo urlencode(strtolower(str_replace(' ', '-', $game))); ?>"><?php echo strtoupper(htmlspecialchars($display_name)); ?></a>
                        <span class="game-time"><?php echo htmlspecialchars($data['result_time'] ?? '--'); ?></span>
                    </td>
                    <td class="yesterday-result"><?php echo htmlspecialchars($data['yesterday_result'] ?? '--'); ?></td>
                    <td class="today-result">
                        <?php if ($is_wait): ?>
                            <span class="wait-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28"
                                    fill="#d32f2f">
                                    <path
                                        d="M12,2C6.48,2,2,6.48,2,12s4.48,10,10,10s10-4.48,10-10S17.52,2,12,2z M12,20c-4.41,0-8-3.59-8-8s3.59-8,8-8s8,3.59,8,8 S16.41,20,12,20z" />
                                    <path
                                        d="M12,6c-0.55,0-1,0.45-1,1v5c0,0.55,0.45,1,1,1h4c0.55,0,1-0.45,1-1s-0.45-1-1-1h-3V7C13,6.45,12.55,6,12,6z" />
                                </svg>
                                WAIT
                            </span>
                        <?php else: ?>
                            <span class="result-value"><?php echo htmlspecialchars($data['today_result']); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($table1_game_names)): ?>
                <tr>
                    <td colspan="3" style="text-align: center; padding: 20px; color: #999;">No games available</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- TABLE 2 - Extra Games (Dynamic) -->
<div class="table-wrapper">
    <table class="result-table">
        <thead>
            <tr>
                <th><?php echo $table_header_display; ?></th>
                <th>कल आया था</th>
                <th>आज का रिज़ल्ट</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($table2_game_names as $game):
                // Get data from all_results using lowercase key for consistency
                $key = strtolower($game);
                $data = isset($all_results[$key]) ? $all_results[$key] : ['yesterday_result' => '--', 'today_result' => 'WAIT', 'result_time' => '--', 'display_name' => $game];
                $display_name = !empty($data['display_name']) ? $data['display_name'] : strtoupper($game);
                $is_wait = ($data['today_result'] == 'WAIT' || $data['today_result'] == '-1' || empty($data['today_result']));
                ?>
                <tr>
                    <td class="game-name">
                        <a
                            href="game.php?game=<?php echo urlencode(strtolower(str_replace(' ', '-', $game))); ?>"><?php echo strtoupper(htmlspecialchars($display_name)); ?></a>
                        <span class="game-time"><?php echo htmlspecialchars($data['result_time'] ?? '--'); ?></span>
                    </td>
                    <td class="yesterday-result"><?php echo htmlspecialchars($data['yesterday_result'] ?? '--'); ?></td>
                    <td class="today-result">
                        <?php if ($is_wait): ?>
                            <span class="wait-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28"
                                    fill="#d32f2f">
                                    <path
                                        d="M12,2C6.48,2,2,6.48,2,12s4.48,10,10,10s10-4.48,10-10S17.52,2,12,2z M12,20c-4.41,0-8-3.59-8-8s3.59-8,8-8s8,3.59,8,8 S16.41,20,12,20z" />
                                    <path
                                        d="M12,6c-0.55,0-1,0.45-1,1v5c0,0.55,0.45,1,1,1h4c0.55,0,1-0.45,1-1s-0.45-1-1-1h-3V7C13,6.45,12.55,6,12,6z" />
                                </svg>
                                WAIT
                            </span>
                        <?php else: ?>
                            <span class="result-value"><?php echo htmlspecialchars($data['today_result']); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($table2_game_names)): ?>
                <tr>
                    <td colspan="3" style="text-align: center; padding: 20px; color: #999;">No games available</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Chart Selector - Dynamic -->
<div class="chart-selector">
    <select id="chartGameSelect">
        <option value="">-- Select Game --</option>
        <?php foreach ($all_game_names as $game): ?>
            <option value="<?php echo htmlspecialchars($game); ?>"><?php echo strtoupper(htmlspecialchars($game)); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <select id="chartYearSelect">
        <option value="2026">2026</option>
        <option value="2025">2025</option>
        <option value="2024">2024</option>
    </select>
    <select id="chartMonthSelect">
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
    <button onclick="loadChartData()">📊 Check</button>
</div>

<!-- Chart Results Container -->
<div id="chartResultsContainer" style="display: none;">
    <div style="text-align:center; margin:20px;">
        <h1 style="color:#ffd700; font-size: 28px;" id="chartTitle">RESULT CHART</h1>
    </div>
    <div id="chartDataDisplay" style="overflow-x: auto; margin: 20px;">
        <div style="text-align: center; padding: 60px 20px; color: #999;">
            <div style="font-size: 48px; margin-bottom: 20px;">🔍</div>
            <div style="font-size: 18px;">Select a game and click "Check" to view chart data</div>
        </div>
    </div>
</div>

<!-- Play Time Editor Modal - Admin Only -->
<?php if (isAdminLoggedIn()): ?>
    <div id="playTimeEditorModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; justify-content: center; align-items: center;">
        <div
            style="background: #fff; border-radius: 30px; padding: 30px; max-width: 800px; width: 95%; max-height: 90vh; overflow-y: auto; position: relative;">
            <button onclick="closePlayTimeEditor()"
                style="position: sticky; top: 0; float: right; background: #dc3545; color: #fff; border: none; border-radius: 50%; width: 40px; height: 40px; font-size: 24px; cursor: pointer; z-index: 10;">✕</button>

            <h2 style="color: #1a1a2e; text-align: center; margin-bottom: 30px;">✏️ Edit Play Time Info</h2>

            <div
                style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px;">
                <button onclick="showPlayTimeTab('timings')" id="tabTimingsBtn"
                    style="padding: 10px 25px; border: none; border-radius: 40px; background: #ffd700; font-weight: bold; cursor: pointer;">⏰
                    Timings</button>
                <button onclick="showPlayTimeTab('rates')" id="tabRatesBtn"
                    style="padding: 10px 25px; border: none; border-radius: 40px; background: #e0e0e0; font-weight: bold; cursor: pointer;">💰
                    Rates</button>
            </div>

            <div id="timingsTab">
                <div style="background: #f9f9f9; padding: 20px; border-radius: 15px; margin-bottom: 20px;">
                    <h3 style="margin-top: 0; color: #1a1a2e;">➕ Add New Timing</h3>
                    <form id="addTimingForm" style="display: flex; gap: 10px; flex-wrap: wrap;" onsubmit="return false;">
                        <input type="text" id="newTimingGame" placeholder="Game Name"
                            style="flex: 1; min-width: 120px; padding: 10px; border-radius: 10px; border: 2px solid #ddd;">
                        <input type="text" id="newTimingTime" placeholder="Time (e.g. 5:15 AM)"
                            style="flex: 1; min-width: 120px; padding: 10px; border-radius: 10px; border: 2px solid #ddd;">
                        <input type="text" id="newTimingEmoji" placeholder="Emoji (e.g. 😇)"
                            style="flex: 0 0 80px; padding: 10px; border-radius: 10px; border: 2px solid #ddd;">
                        <button type="button" onclick="addTiming()"
                            style="padding: 10px 25px; background: #28a745; color: #fff; border: none; border-radius: 40px; font-weight: bold; cursor: pointer;">➕
                            Add</button>
                    </form>
                </div>

                <div style="max-height: 400px; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #1a1a2e; color: #ffd700;">
                                <th style="padding: 10px; text-align: left;">Emoji</th>
                                <th style="padding: 10px; text-align: left;">Game</th>
                                <th style="padding: 10px; text-align: left;">Timing</th>
                                <th style="padding: 10px; text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="timingsList">
                            <?php foreach ($all_game_timings as $timing): ?>
                                <tr id="timing-row-<?php echo $timing['id']; ?>" style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 8px; font-size: 24px;"><?php echo htmlspecialchars($timing['emoji']); ?>
                                    </td>
                                    <td style="padding: 8px;">
                                        <strong><?php echo htmlspecialchars(ucfirst($timing['game_name'])); ?></strong>
                                    </td>
                                    <td style="padding: 8px;"><?php echo htmlspecialchars($timing['timing']); ?></td>
                                    <td style="padding: 8px; text-align: center;">
                                        <button onclick="editTiming(<?php echo $timing['id']; ?>)"
                                            style="padding: 5px 12px; background: #ffd700; border: none; border-radius: 5px; cursor: pointer; margin-right: 5px;">✏️</button>
                                        <button onclick="deleteTiming(<?php echo $timing['id']; ?>)"
                                            style="padding: 5px 12px; background: #dc3545; color: #fff; border: none; border-radius: 5px; cursor: pointer;">🗑️</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="ratesTab" style="display: none;">
                <div style="background: #f9f9f9; padding: 20px; border-radius: 15px; margin-bottom: 20px;">
                    <h3 style="margin-top: 0; color: #1a1a2e;">➕ Add New Rate</h3>
                    <form id="addRateForm" style="display: flex; gap: 10px; flex-wrap: wrap;" onsubmit="return false;">
                        <input type="text" id="newRateType" placeholder="Rate Type (e.g. जोड़ी रेट)"
                            style="flex: 1; min-width: 150px; padding: 10px; border-radius: 10px; border: 2px solid #ddd;">
                        <input type="text" id="newRateValue" placeholder="Rate Value (e.g. 10 ke..960)"
                            style="flex: 1; min-width: 150px; padding: 10px; border-radius: 10px; border: 2px solid #ddd;">
                        <button type="button" onclick="addRate()"
                            style="padding: 10px 25px; background: #28a745; color: #fff; border: none; border-radius: 40px; font-weight: bold; cursor: pointer;">➕
                            Add</button>
                    </form>
                </div>

                <div style="max-height: 400px; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #1a1a2e; color: #ffd700;">
                                <th style="padding: 10px; text-align: left;">Rate Type</th>
                                <th style="padding: 10px; text-align: left;">Rate Value</th>
                                <th style="padding: 10px; text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="ratesList">
                            <?php foreach ($all_game_rates as $rate): ?>
                                <tr id="rate-row-<?php echo $rate['id']; ?>" style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 8px;">
                                        <strong><?php echo htmlspecialchars($rate['rate_type']); ?></strong>
                                    </td>
                                    <td style="padding: 8px;"><?php echo htmlspecialchars($rate['rate_value']); ?></td>
                                    <td style="padding: 8px; text-align: center;">
                                        <button onclick="editRate(<?php echo $rate['id']; ?>)"
                                            style="padding: 5px 12px; background: #ffd700; border: none; border-radius: 5px; cursor: pointer; margin-right: 5px;">✏️</button>
                                        <button onclick="deleteRate(<?php echo $rate['id']; ?>)"
                                            style="padding: 5px 12px; background: #dc3545; color: #fff; border: none; border-radius: 5px; cursor: pointer;">🗑️</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="editTimingForm"
                style="display: none; background: #fff8e7; padding: 20px; border-radius: 15px; margin-top: 20px; border: 2px solid #ffd700;">
                <h3 style="margin-top: 0; color: #c49a00;">✏️ Edit Timing</h3>
                <input type="hidden" id="editTimingId">
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <input type="text" id="editTimingGame" placeholder="Game Name"
                        style="flex: 1; min-width: 120px; padding: 10px; border-radius: 10px; border: 2px solid #ffd700;">
                    <input type="text" id="editTimingTime" placeholder="Time"
                        style="flex: 1; min-width: 120px; padding: 10px; border-radius: 10px; border: 2px solid #ffd700;">
                    <input type="text" id="editTimingEmoji" placeholder="Emoji"
                        style="flex: 0 0 80px; padding: 10px; border-radius: 10px; border: 2px solid #ffd700;">
                    <button onclick="updateTiming()"
                        style="padding: 10px 25px; background: #ffd700; border: none; border-radius: 40px; font-weight: bold; cursor: pointer;">💾
                        Update</button>
                    <button onclick="cancelEditTiming()"
                        style="padding: 10px 25px; background: #6c757d; color: #fff; border: none; border-radius: 40px; font-weight: bold; cursor: pointer;">Cancel</button>
                </div>
            </div>

            <div id="editRateForm"
                style="display: none; background: #fff8e7; padding: 20px; border-radius: 15px; margin-top: 20px; border: 2px solid #ffd700;">
                <h3 style="margin-top: 0; color: #c49a00;">✏️ Edit Rate</h3>
                <input type="hidden" id="editRateId">
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <input type="text" id="editRateType" placeholder="Rate Type"
                        style="flex: 1; min-width: 150px; padding: 10px; border-radius: 10px; border: 2px solid #ffd700;">
                    <input type="text" id="editRateValue" placeholder="Rate Value"
                        style="flex: 1; min-width: 150px; padding: 10px; border-radius: 10px; border: 2px solid #ffd700;">
                    <button onclick="updateRate()"
                        style="padding: 10px 25px; background: #ffd700; border: none; border-radius: 40px; font-weight: bold; cursor: pointer;">💾
                        Update</button>
                    <button onclick="cancelEditRate()"
                        style="padding: 10px 25px; background: #6c757d; color: #fff; border: none; border-radius: 40px; font-weight: bold; cursor: pointer;">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ============ TIMINGS CRUD ============
        function openPlayTimeEditor() {
            document.getElementById('playTimeEditorModal').style.display = 'flex';
        }

        function closePlayTimeEditor() {
            document.getElementById('playTimeEditorModal').style.display = 'none';
        }

        function showPlayTimeTab(tab) {
            document.getElementById('timingsTab').style.display = tab === 'timings' ? 'block' : 'none';
            document.getElementById('ratesTab').style.display = tab === 'rates' ? 'block' : 'none';
            document.getElementById('tabTimingsBtn').style.background = tab === 'timings' ? '#ffd700' : '#e0e0e0';
            document.getElementById('tabRatesBtn').style.background = tab === 'rates' ? '#ffd700' : '#e0e0e0';
        }

        function addTiming() {
            const game = document.getElementById('newTimingGame').value;
            const time = document.getElementById('newTimingTime').value;
            const emoji = document.getElementById('newTimingEmoji').value || '😇';

            if (!game || !time) { alert('Please fill all fields'); return; }

            fetch('ajax-handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=add_timing&game_name=${encodeURIComponent(game)}&timing=${encodeURIComponent(time)}&emoji=${encodeURIComponent(emoji)}`
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        }

        function editTiming(id) {
            const row = document.getElementById('timing-row-' + id);
            const cells = row.querySelectorAll('td');
            document.getElementById('editTimingId').value = id;
            document.getElementById('editTimingGame').value = cells[1].innerText.trim();
            document.getElementById('editTimingTime').value = cells[2].innerText.trim();
            document.getElementById('editTimingEmoji').value = cells[0].innerText.trim();
            document.getElementById('editTimingForm').style.display = 'block';
            document.getElementById('editRateForm').style.display = 'none';
        }

        function updateTiming() {
            const id = document.getElementById('editTimingId').value;
            const game = document.getElementById('editTimingGame').value;
            const time = document.getElementById('editTimingTime').value;
            const emoji = document.getElementById('editTimingEmoji').value;

            fetch('ajax-handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=update_timing&id=${id}&game_name=${encodeURIComponent(game)}&timing=${encodeURIComponent(time)}&emoji=${encodeURIComponent(emoji)}`
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        }

        function deleteTiming(id) {
            if (!confirm('Delete this timing?')) return;

            fetch('ajax-handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=delete_timing&id=${id}`
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        }

        function cancelEditTiming() {
            document.getElementById('editTimingForm').style.display = 'none';
        }

        // ============ RATES CRUD ============
        function addRate() {
            const type = document.getElementById('newRateType').value;
            const value = document.getElementById('newRateValue').value;

            if (!type || !value) { alert('Please fill all fields'); return; }

            fetch('ajax-handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=add_rate&rate_type=${encodeURIComponent(type)}&rate_value=${encodeURIComponent(value)}`
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        }

        function editRate(id) {
            const row = document.getElementById('rate-row-' + id);
            const cells = row.querySelectorAll('td');
            document.getElementById('editRateId').value = id;
            document.getElementById('editRateType').value = cells[0].innerText.trim();
            document.getElementById('editRateValue').value = cells[1].innerText.trim();
            document.getElementById('editRateForm').style.display = 'block';
            document.getElementById('editTimingForm').style.display = 'none';
        }

        function updateRate() {
            const id = document.getElementById('editRateId').value;
            const type = document.getElementById('editRateType').value;
            const value = document.getElementById('editRateValue').value;

            fetch('ajax-handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=update_rate&id=${id}&rate_type=${encodeURIComponent(type)}&rate_value=${encodeURIComponent(value)}`
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        }

        function deleteRate(id) {
            if (!confirm('Delete this rate?')) return;

            fetch('ajax-handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=delete_rate&id=${id}`
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        }

        function cancelEditRate() {
            document.getElementById('editRateForm').style.display = 'none';
        }
    </script>
<?php endif; ?>

<!-- Content Section -->
<div class="content-section">
    <h5><strong>A1 Satta - Satta Bazar Website</strong></h5>
    <h5><strong>What is A1-Satta?</strong></h5>
    <p>You've probably heard the name A1 satta many times. In India, this name carries a lot of weight. A1 satta is a
        website which provides all satta king lottery game results that uses numbers ranging from 00 to 99. A1 Satta
        also known as A1Satta or A1-satta. This game is known as Satta Matka, where "Satta" refers to betting or
        gambling and "Matka" refers to a pot. In the Satta Matka game, money is wagered on numbers ranging from 00 to
        99. The pot is then drawn, followed by a number. Because whoever's number was drawn won the prize, he was known
        as the Satta king. The winner of Satta Matka was given the title Satta King, not the game itself.</p>
    <h5><strong>How do you play A1-Satta?</strong></h5>
    <p>In A1 Satta, a wager can be placed on any number between 0 and 99. The odds of winning depend on the number
        chosen. They can get assistance with this from a Khaiwal who lives in their region. Khaiwal acts as a go-between
        for players and game operators, facilitating communication between the two groups. Players in Khaiwal's region
        submit money and numbers to the corporation based on the money they gather and the number they provide. This is
        done under the game's rules. Once a victor has been determined, he will then go around and collect the winnings
        and hand them out to the victors. The Satta firm selects winners at random and then announces the winners at a
        set time. An individual who successfully wagers on a winning number will receive ninety times the amount of his
        original wager.</p>
    <h5><strong>How do you play Satta King online, and why?</strong></h5>
    <p>When you play satta online, you don't have to worry about the police getting in the way. It's hard to catch
        people who play games online. But betting is against the law in India and can lead to a big fine. There are many
        exciting online games you can play with apps from the Google Play store. Get the app and install it. Play satta
        games at home.</p>
    <h5><strong>How much money can you make with Delhi satta?</strong></h5>
    <p>If a person bets 10 rupees on a number and that number is opened, they will get 10 times 90, which is 900 rupees.
        The same goes for 20, 30, 40, and 50 rupees; users get 1800 rupees for each. The user can play as many numbers
        as he wants and put as much money into the game as he wants.</p>
    <h5><strong>What is the Reality of A1-Satta?</strong></h5>
    <p>It's worth noting that A1 Satta is just one of several lottery games out there. There will be 100 people taking
        part in it, all of their own. The whole collection of 100 digits is combined, and a single number, between 1 and
        100, is drawn at random. After that, a number from any slip is chosen at random. If your ticket number is drawn
        as the winner, congratulations! This game is exactly as it sounds, yet it doesn't have any of these elements.
        Rather than randomly selecting a number from a pool of one hundred, Satta Company plays a game where players
        choose a winning slip from a pot. Profit maximization is the primary focus of Satta's business operations. In
        other words, the numbers are not chosen at random. The Satta Company dictates all of the numbers. As a result,
        Satta makes a lot of money, and the winners in Satta are decided by the amount of money each player takes home.
    </p>
    <h5><strong>What is DELHI BAZAR SATTA KING?</strong></h5>
    <p>The term satta king delhi bazar came from the game delhi bazar Satta which is quite popular game which named upon
        capital of India which is Delhi. and the word "bazar" means Market so the collected word will mean as Delhi
        Market butin context to satta king , it means a game which have number from 0 to 99 where everyday at 3:15 one
        number is announced by game owner which is unknown and players who had bid for same number will be rewarded with
        100 times of the bid amount and those who bid the wrong number will be loser and will have to loose their money
        so playing satta king delhi bajar is not quite easy as it have financial burden to end up loosing. Also keep in
        mind that playing satta king delhi bazar or other games like this are illegal .</p>
    <h5><strong>What is the leak Number for A1-Satta?</strong></h5>
    <p>We'll give you a leak, and you'll see what happens right in front of your eyes. You can win lakhs of rupees by
        playing Satta with 100% Direct Speculative Leak in the Gali and Disawar. The only people we help are those who
        have lost a lot and are upset. You will have to put fees directly into a company's account, not just in the
        accounts of its employees. Gali and delhi satta will make someone wealthy. This is the only way to change your
        future. At the moment, we will pay for loans or players that we lose. We'll also give you millions and millions
        of crores, and the leaked Jodi will always be with you.</p>
    <h5><strong>What does it mean by SATTA KING?</strong></h5>
    <p>Satta king is a game which played in south east part of the world especially in India , To define it in true
        sense you can imagine a game which is prediction of number , infact there are numbers from 0 to 99 are playable
        numbers in satta king games , there are many games which are satta king games such as delhi bazar satta king ,
        shri ganesh satta king , diwaser and gali . The players usually bids on a number through a mediator who takes
        bids money from player and pours in networks of game owner where result of one number from 0 to 99 is chosen by
        owner and later announced by diffrent channels then those who have bid on exact number will be paid upto 90 to
        100 times of bid money. This is shortest description of SATTA KING game Play.</p>
    <h5><strong>Is Shri Ganesh result published on A1-satta?</strong></h5>
    <p>Yes , A1satta website is one of fast website to publish shri ganesh satta result which is published everyday
        around 4:40 PM where a1-satta updates on its chart section real time. Shri ganesh satta players likes <a
            href="#">A1satta website</a> for its interactive interface and straigh forward result section which gives
        information real time</p>
    <h5><strong>Frequently Asked Question About A1-Satta</strong></h5>
    <h5><strong>What are the types of A1-Satta Games?</strong></h5>
    <p>In India, the game of satta bazar is most commonly played in one of four variations: Desawar Satta kings, Gali
        Satta kings, Ghaziabad Satta kings, and satta bazar. In addition to these four games, Khaiwal participated in
        several other games on his own, including Rajkot, Taj, New Faridabad, Hindustan, and Peshawar.</p>
    <h5><strong>Which A1 satta number is the most likely to win?</strong></h5>
    <p>To win money playing Delhi Satta, all you need is a number that is drawn as a winner. There is no specific
        mathematical formula that can be used to derive this number. When it comes to Satta King games, players would
        often refer to older record charts to make their guesses on the next number. As a direct consequence of this, a
        large number of numbers are played all at once.</p>
    <h5><strong>How to predict satta king number?</strong></h5>
    <p>For next prediction of number there need analysis , some people calculate it as how many occurrences occur within
        a specific time frame , however this is ideally easier way people predict these game but there is also need to
        catch pattern to get what number are published in a specific time with a specific pattern . Some people use some
        complex mind algos to predict which works once in while which ends up loosing money for players.</p>
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

<script src="js/chart-functions.js"></script>

<?