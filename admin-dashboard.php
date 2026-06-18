<?php
require_once 'config.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    header('Location: admin-login.php');
    exit();
}

$page_title = 'Admin Dashboard - A1 Satta Live';

// Handle all POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update Game Result
    if (isset($_POST['update_result'])) {
        $data = [
            'game_name' => $_POST['game_name'],
            'today_result' => $_POST['today_result'],
            'yesterday_result' => $_POST['yesterday_result'],
            'result_time' => $_POST['result_time'],
            'display_name' => $_POST['display_name']
        ];
        if (updateGame($pdo, $data)) {
            $_SESSION['success'] = "✅ Result updated for " . ucfirst($data['game_name']);
        } else {
            $_SESSION['error'] = "❌ Failed to update result!";
        }
        header('Location: admin-dashboard.php');
        exit();
    }

    // Add New Game
    if (isset($_POST['add_game'])) {
        $data = [
            'game_name' => strtolower(trim($_POST['new_game_name'])),
            'display_name' => $_POST['new_display_name'] ?: ucfirst($_POST['new_game_name']),
            'today_result' => $_POST['new_today_result'] ?: 'WAIT',
            'yesterday_result' => $_POST['new_yesterday_result'] ?: '--',
            'result_time' => $_POST['new_result_time'] ?: '--',
            'table_type' => $_POST['new_table_type']
        ];

        $existing = getGameResults($pdo, $data['game_name']);
        if ($existing) {
            $_SESSION['error'] = "❌ Game '" . ucfirst($data['game_name']) . "' already exists!";
        } else {
            if (addGame($pdo, $data)) {
                $_SESSION['success'] = "✅ Game '" . ucfirst($data['game_name']) . "' added successfully!";
            } else {
                $_SESSION['error'] = "❌ Failed to add game!";
            }
        }
        header('Location: admin-dashboard.php');
        exit();
    }

    // Update Disawer
    if (isset($_POST['update_disawer'])) {
        if (updateDisawer($pdo, $_POST['disawer_today'], $_POST['disawer_yesterday'])) {
            $_SESSION['success'] = "✅ Disawer result updated!";
        } else {
            $_SESSION['error'] = "❌ Failed to update Disawer!";
        }
        header('Location: admin-dashboard.php');
        exit();
    }

    // Update Chart Data
    if (isset($_POST['update_chart'])) {
        if (updateChartData($pdo, $_POST['chart_game'], $_POST['chart_date'], $_POST['chart_result'], $_POST['chart_table_type'])) {
            $_SESSION['success'] = "✅ Chart updated for " . ucfirst($_POST['chart_game']) . " on " . $_POST['chart_date'];
        } else {
            $_SESSION['error'] = "❌ Failed to update chart!";
        }
        header('Location: admin-dashboard.php');
        exit();
    }

    // Generate Full Month Chart Data
    if (isset($_POST['generate_month_chart'])) {
        $game = $_POST['gen_chart_game'];
        $month = $_POST['gen_chart_month'];
        $year = $_POST['gen_chart_year'];

        // Generate data for the entire month
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, (int) $month, (int) $year);
        $sample_results = ['39', '52', '18', '43', '86', '71', '47', '31', '65', '80', '95', '110', '125', '140', '155', '170', '185', '200', '215', '230', '245', '260', '275', '290', '305', '320', '335', '350', '365', '380', '395'];

        // Determine table type
        $table1_games = ['sadar bazar', 'gwalior', 'delhi bazar', 'delhi matka', 'shri ganesh', 'agra', 'faridabad', 'alwar', 'gaziabad', 'dwarka', 'gali', 'disawer'];
        $table_type = in_array($game, $table1_games) ? 'table1' : 'table2';

        $count = 0;
        for ($day = 1; $day <= $days_in_month; $day++) {
            $date_str = str_pad($day, 2, '0', STR_PAD_LEFT) . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
            $result = $sample_results[($day - 1) % count($sample_results)];

            $stmt = $pdo->prepare("INSERT INTO chart_data (game_name, date, result_number, table_type) 
                                   VALUES (?, ?, ?, ?) 
                                   ON DUPLICATE KEY UPDATE result_number = VALUES(result_number)");
            if ($stmt->execute([$game, $date_str, $result, $table_type])) {
                $count++;
            }
        }

        if ($count > 0) {
            $_SESSION['success'] = "✅ Generated $count entries for " . ucfirst($game) . " in " . $month . '-' . $year;
        } else {
            $_SESSION['error'] = "❌ Failed to generate chart data!";
        }
        header('Location: admin-dashboard.php');
        exit();
    }

    // Add Game Timing
    if (isset($_POST['add_timing'])) {
        if (addGameTiming($pdo, $_POST['timing_game_name'], $_POST['timing_time'], $_POST['timing_emoji'])) {
            $_SESSION['success'] = "✅ Game timing added successfully!";
        } else {
            $_SESSION['error'] = "❌ Failed to add timing!";
        }
        header('Location: admin-dashboard.php');
        exit();
    }

    // Update Game Timing
    if (isset($_POST['update_timing'])) {
        if (updateGameTiming($pdo, $_POST['timing_id'], $_POST['timing_game_name'], $_POST['timing_time'], $_POST['timing_emoji'], $_POST['timing_is_active'] ?? 1)) {
            $_SESSION['success'] = "✅ Game timing updated!";
        } else {
            $_SESSION['error'] = "❌ Failed to update timing!";
        }
        header('Location: admin-dashboard.php');
        exit();
    }

    // Add Game Rate
    if (isset($_POST['add_rate'])) {
        if (addGameRate($pdo, $_POST['rate_type'], $_POST['rate_value'], $_POST['rate_display_order'] ?? 0)) {
            $_SESSION['success'] = "✅ Game rate added successfully!";
        } else {
            $_SESSION['error'] = "❌ Failed to add rate!";
        }
        header('Location: admin-dashboard.php');
        exit();
    }

    // Update Game Rate
    if (isset($_POST['update_rate'])) {
        if (updateGameRate($pdo, $_POST['rate_id'], $_POST['rate_type'], $_POST['rate_value'], $_POST['rate_is_active'] ?? 1)) {
            $_SESSION['success'] = "✅ Game rate updated!";
        } else {
            $_SESSION['error'] = "❌ Failed to update rate!";
        }
        header('Location: admin-dashboard.php');
        exit();
    }

    // Add Multiple Result
    if (isset($_POST['add_multiple_result'])) {
        if (addGameResult($pdo, $_POST['mr_game_name'], $_POST['mr_result_date'], $_POST['mr_result_number'], $_POST['mr_result_time'])) {
            $_SESSION['success'] = "✅ Result added successfully!";
        } else {
            $_SESSION['error'] = "❌ Failed to add result!";
        }
        header('Location: admin-dashboard.php');
        exit();
    }

    // Update Multiple Result
    if (isset($_POST['update_multiple_result'])) {
        if (updateGameResult($pdo, $_POST['mr_id'], $_POST['mr_game_name'], $_POST['mr_result_date'], $_POST['mr_result_number'], $_POST['mr_result_time'])) {
            $_SESSION['success'] = "✅ Result updated!";
        } else {
            $_SESSION['error'] = "❌ Failed to update result!";
        }
        header('Location: admin-dashboard.php');
        exit();
    }
}

// Handle GET requests (Delete operations)
if (isset($_GET['delete_game'])) {
    if (deleteGame($pdo, $_GET['delete_game'])) {
        $_SESSION['success'] = "🗑️ Game deleted successfully!";
    } else {
        $_SESSION['error'] = "❌ Failed to delete game!";
    }
    header('Location: admin-dashboard.php');
    exit();
}

if (isset($_GET['delete_chart'])) {
    if (deleteChartData($pdo, $_GET['delete_chart'], $_GET['chart_date'], $_GET['chart_table_type'])) {
        $_SESSION['success'] = "🗑️ Chart data deleted successfully!";
    } else {
        $_SESSION['error'] = "❌ Failed to delete chart data!";
    }
    header('Location: admin-dashboard.php');
    exit();
}

if (isset($_GET['delete_timing'])) {
    if (deleteGameTiming($pdo, $_GET['delete_timing'])) {
        $_SESSION['success'] = "🗑️ Game timing deleted!";
    } else {
        $_SESSION['error'] = "❌ Failed to delete timing!";
    }
    header('Location: admin-dashboard.php');
    exit();
}

if (isset($_GET['delete_rate'])) {
    if (deleteGameRate($pdo, $_GET['delete_rate'])) {
        $_SESSION['success'] = "🗑️ Game rate deleted!";
    } else {
        $_SESSION['error'] = "❌ Failed to delete rate!";
    }
    header('Location: admin-dashboard.php');
    exit();
}

if (isset($_GET['delete_multiple_result'])) {
    if (deleteGameResult($pdo, $_GET['delete_multiple_result'])) {
        $_SESSION['success'] = "🗑️ Result deleted!";
    } else {
        $_SESSION['error'] = "❌ Failed to delete result!";
    }
    header('Location: admin-dashboard.php');
    exit();
}

if (isset($_GET['toggle_timing'])) {
    $stmt = $pdo->prepare("SELECT is_active FROM game_timings WHERE id = ?");
    $stmt->execute([$_GET['toggle_timing']]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($current) {
        toggleGameTiming($pdo, $_GET['toggle_timing'], $current['is_active'] ? 0 : 1);
        $_SESSION['success'] = "✅ Timing status toggled!";
    }
    header('Location: admin-dashboard.php');
    exit();
}

if (isset($_GET['toggle_rate'])) {
    $stmt = $pdo->prepare("SELECT is_active FROM game_rates WHERE id = ?");
    $stmt->execute([$_GET['toggle_rate']]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($current) {
        toggleGameRate($pdo, $_GET['toggle_rate'], $current['is_active'] ? 0 : 1);
        $_SESSION['success'] = "✅ Rate status toggled!";
    }
    header('Location: admin-dashboard.php');
    exit();
}

// Logout
if (isset($_GET['logout'])) {
    logoutAdmin();
}

// Check if we're editing from URL parameters
$edit_game = $_GET['edit_game'] ?? '';
$edit_date = $_GET['edit_date'] ?? '';
$edit_result = $_GET['edit_result'] ?? '';
$edit_table = $_GET['edit_table'] ?? 'table1';
$selected_game = $_GET['game'] ?? '';
$selected_month = $_GET['month'] ?? date('m');
$selected_year = $_GET['year'] ?? date('Y');

// Fetch all data
$disawer = getGameResults($pdo, 'disawer');
$disawer_result = $disawer ? $disawer['today_result'] : '86';
$disawer_yesterday = $disawer ? $disawer['yesterday_result'] : '05';

$all_games = getAllGames($pdo);
$all_chart_data = getAllChartData($pdo);
$chart_dates = getChartDates($pdo);
$game_timings = getAllGameTimings($pdo);
$game_rates = getAllGameRates($pdo);
$multiple_results = getGameMultipleResults($pdo);

// Define game lists
$table1_games = ['sadar bazar', 'gwalior', 'delhi bazar', 'delhi matka', 'shri ganesh', 'agra', 'faridabad', 'alwar', 'gaziabad', 'dwarka', 'gali'];
$table2_games = ['hr satta', 'kkr city', 'madhupuri', 'ujjala super', 'karol bagh', 'anmol bazar', 'sky king', 'delhi darbar', 'new ganga', 'fatehabad', 'raj shree', 'mandi bazar', 'bhadra bazar', 'sialkot', 'lion bazar', 'gaziabad king', 'dehradun city', 'daman'];
$chart1_games = array_merge($table1_games, ['disawer']);
$chart2_games = $table2_games;

require_once 'header.php';
?>

<style>
    /* Dashboard Styles */
    .admin-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    .admin-header {
        background: linear-gradient(135deg, #1a1a2e, #16213e);
        color: #ffd700;
        padding: 30px;
        border-radius: 20px;
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }

    .admin-header h1 {
        font-size: 28px;
        margin: 0;
    }

    .admin-header .header-actions {
        display: flex;
        gap: 15px;
        align-items: center;
        flex-wrap: wrap;
    }

    .admin-header .header-actions a {
        color: #fff;
        text-decoration: none;
        padding: 8px 20px;
        border-radius: 40px;
        background: rgba(255, 255, 255, 0.1);
        transition: background 0.3s;
    }

    .admin-header .header-actions a:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .admin-header .header-actions .logout-btn {
        background: #dc3545;
    }

    .admin-header .header-actions .logout-btn:hover {
        background: #c82333;
    }

    .admin-section {
        background: #fff;
        border-radius: 20px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        border-left: 5px solid #ffd700;
    }

    .admin-section h2 {
        color: #1a1a2e;
        margin-top: 0;
        margin-bottom: 20px;
        font-size: 22px;
        border-bottom: 2px solid #ffd700;
        padding-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .admin-form {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: flex-end;
    }

    .admin-form .form-group {
        flex: 1;
        min-width: 150px;
    }

    .admin-form label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
        font-size: 13px;
        color: #333;
    }

    .admin-form input,
    .admin-form select,
    .admin-form textarea {
        width: 100%;
        padding: 10px 15px;
        border-radius: 10px;
        border: 2px solid #e0e0e0;
        font-size: 14px;
        transition: border-color 0.3s;
        box-sizing: border-box;
    }

    .admin-form input:focus,
    .admin-form select:focus {
        border-color: #ffd700;
        outline: none;
    }

    .admin-form button {
        padding: 10px 30px;
        border: none;
        border-radius: 40px;
        font-weight: bold;
        cursor: pointer;
        transition: transform 0.2s;
        font-size: 14px;
    }

    .admin-form button:hover {
        transform: scale(1.05);
    }

    .btn-primary {
        background: #ffd700;
        color: #000;
    }

    .btn-success {
        background: #28a745;
        color: #fff;
    }

    .btn-danger {
        background: #dc3545;
        color: #fff;
    }

    .btn-warning {
        background: #ffc107;
        color: #000;
    }

    .btn-info {
        background: #17a2b8;
        color: #fff;
    }

    .admin-table-wrapper {
        overflow-x: auto;
    }

    .admin-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .admin-table th {
        background: #1a1a2e;
        color: #ffd700;
        padding: 12px 15px;
        text-align: center;
        font-weight: bold;
        border: 1px solid #333;
        white-space: nowrap;
    }

    .admin-table td {
        padding: 10px 15px;
        text-align: center;
        border: 1px solid #e0e0e0;
        vertical-align: middle;
    }

    .admin-table tr:nth-child(even) {
        background: #f9f9f9;
    }

    .admin-table tr:hover {
        background: #fff8e0;
    }

    .admin-table .game-name-cell {
        background: #ffd700;
        color: #000;
        font-weight: bold;
        text-align: left;
        padding-left: 20px;
    }

    .admin-table .actions-cell {
        display: flex;
        gap: 5px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .admin-table .actions-cell button,
    .admin-table .actions-cell a {
        padding: 5px 12px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
        font-size: 12px;
        font-weight: bold;
        display: inline-block;
        transition: opacity 0.2s;
    }

    .admin-table .actions-cell button:hover,
    .admin-table .actions-cell a:hover {
        opacity: 0.8;
    }

    .btn-edit {
        background: #ffd700;
        color: #000;
    }

    .btn-delete {
        background: #dc3545;
        color: #fff;
    }

    .btn-chart-edit {
        background: #17a2b8;
        color: #fff;
    }

    .btn-toggle {
        background: #6c757d;
        color: #fff;
    }

    .btn-toggle.active {
        background: #28a745;
    }

    .success-msg {
        background: #d4edda;
        color: #155724;
        padding: 15px;
        margin: 10px 0;
        border-radius: 10px;
        border-left: 5px solid #28a745;
        text-align: center;
        font-weight: bold;
    }

    .error-msg {
        background: #f8d7da;
        color: #721c24;
        padding: 15px;
        margin: 10px 0;
        border-radius: 10px;
        border-left: 5px solid #dc3545;
        text-align: center;
        font-weight: bold;
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background: #fff;
        padding: 30px;
        border-radius: 30px;
        max-width: 500px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
        position: relative;
    }

    .modal-content h3 {
        color: #c49a00;
        text-align: center;
        margin-bottom: 20px;
        font-size: 24px;
    }

    .modal-content .form-group {
        margin-bottom: 15px;
    }

    .modal-content label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
        color: #333;
        font-size: 13px;
    }

    .modal-content input,
    .modal-content select {
        width: 100%;
        padding: 12px;
        border-radius: 10px;
        border: 2px solid #ffd700;
        font-size: 14px;
        box-sizing: border-box;
    }

    .modal-actions {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin-top: 20px;
    }

    .modal-actions button {
        padding: 12px 30px;
        border: none;
        border-radius: 40px;
        font-weight: bold;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .modal-actions button:hover {
        transform: scale(1.05);
    }

    .btn-save {
        background: #ffd700;
        color: #000;
    }

    .btn-cancel {
        background: #dc3545;
        color: #fff;
    }

    .stat-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: #fff;
        padding: 20px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        border: 1px solid #e0e0e0;
    }

    .stat-card .number {
        font-size: 32px;
        font-weight: bold;
        color: #ffd700;
    }

    .stat-card .label {
        color: #666;
        font-size: 14px;
        margin-top: 5px;
    }

    .tabs {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .tab-btn {
        padding: 10px 25px;
        border: 2px solid #ffd700;
        background: transparent;
        border-radius: 40px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
    }

    .tab-btn.active {
        background: #ffd700;
        color: #000;
    }

    .tab-btn:hover {
        background: #ffd700;
        color: #000;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .chart-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 10px;
        margin-bottom: 20px;
    }

    .chart-stat {
        background: #f9f9f9;
        padding: 10px 15px;
        border-radius: 10px;
        text-align: center;
    }

    .chart-stat .number {
        font-size: 20px;
        font-weight: bold;
        color: #c49a00;
    }

    .chart-stat .label {
        font-size: 12px;
        color: #666;
    }

    @media(max-width: 768px) {
        .admin-header {
            flex-direction: column;
            text-align: center;
            gap: 15px;
        }

        .admin-form {
            flex-direction: column;
        }

        .admin-form .form-group {
            min-width: 100%;
        }

        .admin-table {
            font-size: 11px;
        }

        .admin-table th,
        .admin-table td {
            padding: 6px 8px;
        }

        .stat-cards {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

<div class="admin-container">
    <!-- Admin Header -->
    <div class="admin-header">
        <h1>🛡️ Admin Dashboard</h1>
        <div class="header-actions">
            <a href="index.php">🌐 View Website</a>
            <a href="?logout=1" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">🚪 Logout</a>
        </div>
    </div>

    <!-- Messages -->
    <?php if (isset($_SESSION['success'])): ?>
            <div class="success-msg">
                <?php echo $_SESSION['success'];
                unset($_SESSION['success']); ?>
            </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
            <div class="error-msg">
                <?php echo $_SESSION['error'];
                unset($_SESSION['error']); ?>
            </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="stat-cards">
        <div class="stat-card">
            <div class="number"><?php echo count($all_games); ?></div>
            <div class="label">Total Games</div>
        </div>
        <div class="stat-card">
            <div class="number"><?php echo count($chart_dates); ?></div>
            <div class="label">Chart Dates</div>
        </div>
        <div class="stat-card">
            <div class="number"><?php echo count($game_timings); ?></div>
            <div class="label">Game Timings</div>
        </div>
        <div class="stat-card">
            <div class="number"><?php echo count($game_rates); ?></div>
            <div class="label">Game Rates</div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="tabs">
        <a href="admin-dashboard.php?tab=games" class="tab-btn <?php echo (!isset($_GET['tab']) || $_GET['tab'] === 'games') ? 'active' : ''; ?>">🎮 Games</a>
        <a href="admin-dashboard.php?tab=chart" class="tab-btn <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'chart') ? 'active' : ''; ?>">📊 Charts</a>
        <a href="admin-dashboard.php?tab=timings" class="tab-btn <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'timings') ? 'active' : ''; ?>">⏰ Timings</a>
        <a href="admin-dashboard.php?tab=rates" class="tab-btn <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'rates') ? 'active' : ''; ?>">💰 Rates</a>
        <a href="admin-dashboard.php?tab=multiple" class="tab-btn <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'multiple') ? 'active' : ''; ?>">📝 Multiple Results</a>
    </div>

    <!-- ==================== TAB 1: GAMES ==================== -->
    <div id="tab-games" class="tab-content <?php echo (!isset($_GET['tab']) || $_GET['tab'] === 'games') ? 'active' : ''; ?>">
        <!-- Edit Disawer -->
        <div class="admin-section">
            <h2>✏️ Edit Disawer Result</h2>
            <form method="POST" class="admin-form">
                <input type="hidden" name="update_disawer" value="1">
                <div class="form-group">
                    <label>Yesterday Result:</label>
                    <input type="text" name="disawer_yesterday" value="<?php echo $disawer_yesterday; ?>" style="width:120px; text-align:center; font-size:18px;">
                </div>
                <div class="form-group">
                    <label>Today Result:</label>
                    <input type="text" name="disawer_today" value="<?php echo $disawer_result; ?>" style="width:120px; text-align:center; font-size:18px;">
                </div>
                <button type="submit" class="btn-primary">💾 Update Disawer</button>
            </form>
        </div>

        <!-- Add New Game -->
        <div class="admin-section">
            <h2>➕ Add New Game</h2>
            <form method="POST" class="admin-form">
                <input type="hidden" name="add_game" value="1">
                <div class="form-group">
                    <label>Game Name (slug):</label>
                    <input type="text" name="new_game_name" placeholder="e.g. new-game" required>
                </div>
                <div class="form-group">
                    <label>Display Name:</label>
                    <input type="text" name="new_display_name" placeholder="सट्टा का नाम">
                </div>
                <div class="form-group">
                    <label>Yesterday:</label>
                    <input type="text" name="new_yesterday_result" placeholder="--">
                </div>
                <div class="form-group">
                    <label>Today:</label>
                    <input type="text" name="new_today_result" placeholder="WAIT">
                </div>
                <div class="form-group">
                    <label>Time:</label>
                    <input type="text" name="new_result_time" placeholder="5:15 PM">
                </div>
                <div class="form-group">
                    <label>Table:</label>
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
                            $data = $all_games[$game] ?? ['yesterday_result' => '--', 'today_result' => 'WAIT', 'result_time' => '--', 'display_name' => $game];
                            ?>
                                <tr>
                                    <td class="game-name-cell"><?php echo ucfirst($game); ?></td>
                                    <td><?php echo $data['display_name'] ?: ucfirst($game); ?></td>
                                    <td><?php echo $data['yesterday_result']; ?></td>
                                    <td><?php echo $data['today_result']; ?></td>
                                    <td><?php echo $data['result_time']; ?></td>
                                    <td>
                                        <div class="actions-cell">
                                            <button class="btn-edit" onclick="openEditModal('<?php echo $game; ?>', '<?php echo $data['yesterday_result']; ?>', '<?php echo $data['today_result']; ?>', '<?php echo $data['result_time']; ?>', '<?php echo $data['display_name']; ?>')">✏️ Edit</button>
                                            <a href="?delete_game=<?php echo urlencode($game); ?>" class="btn-delete" onclick="return confirm('Delete game \'<?php echo ucfirst($game); ?>\'?')">🗑️ Delete</a>
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
                            $data = $all_games[$game] ?? ['yesterday_result' => '--', 'today_result' => 'WAIT', 'result_time' => '--', 'display_name' => $game];
                            ?>
                                <tr>
                                    <td class="game-name-cell"><?php echo ucfirst($game); ?></td>
                                    <td><?php echo $data['display_name'] ?: ucfirst($game); ?></td>
                                    <td><?php echo $data['yesterday_result']; ?></td>
                                    <td><?php echo $data['today_result']; ?></td>
                                    <td><?php echo $data['result_time']; ?></td>
                                    <td>
                                        <div class="actions-cell">
                                            <button class="btn-edit" onclick="openEditModal('<?php echo $game; ?>', '<?php echo $data['yesterday_result']; ?>', '<?php echo $data['today_result']; ?>', '<?php echo $data['result_time']; ?>', '<?php echo $data['display_name']; ?>')">✏️ Edit</button>
                                            <a href="?delete_game=<?php echo urlencode($game); ?>" class="btn-delete" onclick="return confirm('Delete game \'<?php echo ucfirst($game); ?>\'?')">🗑️ Delete</a>
                                        </div>
                                    </td>
                                </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ==================== TAB 2: CHARTS (UPDATED) ==================== -->
    <div id="tab-chart" class="tab-content <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'chart') ? 'active' : ''; ?>">
        
        <!-- Edit Chart Data Section (Shown when editing from URL) -->
        <?php if ($edit_game && $edit_date): ?>
                <div class="admin-section" style="border-left-color: #17a2b8; background: #f0f8ff;">
                    <h2>✏️ Edit Chart Data</h2>
                    <form method="POST" class="admin-form">
                        <input type="hidden" name="update_chart" value="1">
                        <input type="hidden" name="chart_game" value="<?php echo $edit_game; ?>">
                        <input type="hidden" name="chart_date" value="<?php echo $edit_date; ?>">
                        <input type="hidden" name="chart_table_type" value="<?php echo $edit_table; ?>">
                        <div class="form-group">
                            <label>Game:</label>
                            <input type="text" value="<?php echo ucfirst($edit_game); ?>" disabled style="background: #f0f0f0; opacity: 0.7;">
                        </div>
                        <div class="form-group">
                            <label>Date:</label>
                            <input type="text" value="<?php echo $edit_date; ?>" disabled style="background: #f0f0f0; opacity: 0.7;">
                        </div>
                        <div class="form-group">
                            <label>Result Number:</label>
                            <input type="text" name="chart_result" value="<?php echo $edit_result; ?>" required style="border-color: #17a2b8;">
                        </div>
                        <button type="submit" class="btn-info">💾 Update Chart</button>
                        <a href="admin-dashboard.php?tab=chart" class="btn-danger" style="padding: 10px 20px; border-radius: 40px; text-decoration: none; display: inline-block;">❌ Cancel</a>
                    </form>
                </div>
        <?php endif; ?>

        <!-- Add New Chart Data -->
        <div class="admin-section">
            <h2>➕ Add New Chart Data</h2>
            <form method="POST" class="admin-form">
                <input type="hidden" name="update_chart" value="1">
                <div class="form-group">
                    <label>Game Name:</label>
                    <select name="chart_game" required>
                        <option value="">-- Select Game --</option>
                        <?php
                        $all_games_list = getGameNames($pdo);
                        foreach ($all_games_list as $game):
                            $selected = ($selected_game && $game === $selected_game) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $game; ?>" <?php echo $selected; ?>><?php echo ucfirst($game); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date (DD-MM):</label>
                    <input type="text" name="chart_date" placeholder="e.g. 01-06" required value="<?php echo $edit_date ?: ''; ?>">
                </div>
                <div class="form-group">
                    <label>Result Number:</label>
                    <input type="text" name="chart_result" placeholder="Enter result number" required value="<?php echo $edit_result ?: ''; ?>">
                </div>
                <div class="form-group">
                    <label>Table Type:</label>
                    <select name="chart_table_type" required>
                        <option value="table1" <?php echo ($edit_table === 'table1') ? 'selected' : ''; ?>>Table 1</option>
                        <option value="table2" <?php echo ($edit_table === 'table2') ? 'selected' : ''; ?>>Table 2</option>
                    </select>
                </div>
                <button type="submit" class="btn-success">💾 Save Chart Data</button>
            </form>
        </div>

        <!-- Generate Full Month Data -->
        <div class="admin-section" style="border-left-color: #28a745;">
            <h2>📅 Generate Full Month Chart Data</h2>
            <form method="POST" class="admin-form">
                <input type="hidden" name="generate_month_chart" value="1">
                <div class="form-group">
                    <label>Game Name:</label>
                    <select name="gen_chart_game" required>
                        <option value="">-- Select Game --</option>
                        <?php
                        $all_games_list = getGameNames($pdo);
                        foreach ($all_games_list as $game):
                            ?>
                                <option value="<?php echo $game; ?>"><?php echo ucfirst($game); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Month:</label>
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
                <div class="form-group">
                    <label>Year:</label>
                    <select name="gen_chart_year" required>
                        <option value="2026" selected>2026</option>
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                    </select>
                </div>
                <button type="submit" class="btn-info">📊 Generate Full Month</button>
            </form>
        </div>

        <!-- Chart Data List -->
        <div class="admin-section">
            <h2>📋 All Chart Data</h2>
            
            <?php if (!empty($chart_data_display)): ?>
                    <div class="chart-stats">
                        <div class="chart-stat">
                            <div class="number"><?php echo count($chart_data_display); ?></div>
                            <div class="label">Total Entries</div>
                        </div>
                        <div class="chart-stat">
                            <div class="number"><?php echo count(array_unique(array_column($chart_data_display, 'game_name'))); ?></div>
                            <div class="label">Unique Games</div>
                        </div>
                        <div class="chart-stat">
                            <div class="number"><?php echo count(array_unique(array_column($chart_data_display, 'date'))); ?></div>
                            <div class="label">Unique Dates</div>
                        </div>
                    </div>
            <?php endif; ?>

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
                                    <td colspan="6" style="padding: 30px; text-align: center; color: #999;">
                                        No chart data found. Add some data using the forms above.
                                    </td>
                                </tr>
                        <?php else: ?>
                                <?php foreach ($chart_data_display as $row): ?>
                                        <tr>
                                            <td><?php echo $row['id']; ?></td>
                                            <td><strong><?php echo ucfirst($row['game_name']); ?></strong></td>
                                            <td><?php echo $row['date']; ?></td>
                                            <td style="font-weight: bold; color: #c49a00; font-size: 18px;"><?php echo $row['result_number'] ?: '--'; ?></td>
                                            <td><span style="background: <?php echo $row['table_type'] === 'table1' ? '#ffd700' : '#17a2b8'; ?>; color: #000; padding: 2px 10px; border-radius: 10px; font-size: 11px;"><?php echo $row['table_type']; ?></span></td>
                                            <td>
                                                <div class="actions-cell">
                                                    <a href="admin-dashboard.php?tab=chart&edit_game=<?php echo urlencode($row['game_name']); ?>&edit_date=<?php echo urlencode($row['date']); ?>&edit_result=<?php echo urlencode($row['result_number']); ?>&edit_table=<?php echo urlencode($row['table_type']); ?>" class="btn-edit" style="padding: 5px 12px; border-radius: 5px; text-decoration: none; display: inline-block;">✏️ Edit</a>
                                                    <a href="?delete_chart=<?php echo urlencode($row['game_name']); ?>&chart_date=<?php echo urlencode($row['date']); ?>&chart_table_type=<?php echo urlencode($row['table_type']); ?>" class="btn-delete" onclick="return confirm('Delete chart data for <?php echo ucfirst($row['game_name']); ?> on <?php echo $row['date']; ?>?')" style="padding: 5px 12px; border-radius: 5px; text-decoration: none; display: inline-block;">🗑️ Delete</a>
                                                </div>
                                            </td>
                                        </tr>
                                <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ==================== TAB 3: TIMINGS ==================== -->
    <div id="tab-timings" class="tab-content <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'timings') ? 'active' : ''; ?>">
        <div class="admin-section">
            <h2>⏰ Manage Game Timings</h2>

            <!-- Add Timing -->
            <form method="POST" class="admin-form" style="margin-bottom: 20px; padding: 20px; background: #f9f9f9; border-radius: 15px;">
                <input type="hidden" name="add_timing" value="1">
                <h3 style="width:100%; margin-bottom:15px; font-size:16px;">Add New Timing</h3>
                <div class="form-group">
                    <label>Game Name:</label>
                    <input type="text" name="timing_game_name" placeholder="e.g. disawer" required>
                </div>
                <div class="form-group">
                    <label>Time:</label>
                    <input type="text" name="timing_time" placeholder="e.g. 5:15 AM" required>
                </div>
                <div class="form-group">
                    <label>Emoji:</label>
                    <input type="text" name="timing_emoji" placeholder="😇" value="😇">
                </div>
                <button type="submit" class="btn-success">➕ Add Timing</button>
            </form>

            <!-- Timings List -->
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
                        <?php else: ?>
                                <?php foreach ($game_timings as $timing): ?>
                                        <tr>
                                            <td><?php echo $timing['id']; ?></td>
                                            <td><strong><?php echo ucfirst($timing['game_name']); ?></strong></td>
                                            <td><?php echo $timing['timing']; ?></td>
                                            <td style="font-size:24px;"><?php echo $timing['emoji']; ?></td>
                                            <td>
                                                <span style="color: <?php echo $timing['is_active'] ? '#28a745' : '#dc3545'; ?>;">
                                                    <?php echo $timing['is_active'] ? '✅ Active' : '❌ Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="actions-cell">
                                                    <button class="btn-edit" onclick="openTimingEditModal(<?php echo $timing['id']; ?>, '<?php echo $timing['game_name']; ?>', '<?php echo $timing['timing']; ?>', '<?php echo $timing['emoji']; ?>', <?php echo $timing['is_active']; ?>)">✏️ Edit</button>
                                                    <a href="?toggle_timing=<?php echo $timing['id']; ?>" class="btn-toggle <?php echo $timing['is_active'] ? 'active' : ''; ?>">🔄 Toggle</a>
                                                    <a href="?delete_timing=<?php echo $timing['id']; ?>" class="btn-delete" onclick="return confirm('Delete this timing?')">🗑️ Delete</a>
                                                </div>
                                            </td>
                                        </tr>
                                <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ==================== TAB 4: RATES ==================== -->
    <div id="tab-rates" class="tab-content <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'rates') ? 'active' : ''; ?>">
        <div class="admin-section">
            <h2>💰 Manage Game Rates</h2>

            <!-- Add Rate -->
            <form method="POST" class="admin-form" style="margin-bottom: 20px; padding: 20px; background: #f9f9f9; border-radius: 15px;">
                <input type="hidden" name="add_rate" value="1">
                <h3 style="width:100%; margin-bottom:15px; font-size:16px;">Add New Rate</h3>
                <div class="form-group">
                    <label>Rate Type:</label>
                    <input type="text" name="rate_type" placeholder="e.g. जोड़ी रेट" required>
                </div>
                <div class="form-group">
                    <label>Rate Value:</label>
                    <input type="text" name="rate_value" placeholder="e.g. 10 ke..960" required>
                </div>
                <div class="form-group">
                    <label>Display Order:</label>
                    <input type="number" name="rate_display_order" placeholder="0" value="0">
                </div>
                <button type="submit" class="btn-success">➕ Add Rate</button>
            </form>

            <!-- Rates List -->
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
                        <?php else: ?>
                                <?php foreach ($game_rates as $rate): ?>
                                        <tr>
                                            <td><?php echo $rate['id']; ?></td>
                                            <td><strong><?php echo $rate['rate_type']; ?></strong></td>
                                            <td><?php echo $rate['rate_value']; ?></td>
                                            <td><?php echo $rate['display_order']; ?></td>
                                            <td>
                                                <span style="color: <?php echo $rate['is_active'] ? '#28a745' : '#dc3545'; ?>;">
                                                    <?php echo $rate['is_active'] ? '✅ Active' : '❌ Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="actions-cell">
                                                    <button class="btn-edit" onclick="openRateEditModal(<?php echo $rate['id']; ?>, '<?php echo $rate['rate_type']; ?>', '<?php echo $rate['rate_value']; ?>', <?php echo $rate['is_active']; ?>)">✏️ Edit</button>
                                                    <a href="?toggle_rate=<?php echo $rate['id']; ?>" class="btn-toggle <?php echo $rate['is_active'] ? 'active' : ''; ?>">🔄 Toggle</a>
                                                    <a href="?delete_rate=<?php echo $rate['id']; ?>" class="btn-delete" onclick="return confirm('Delete this rate?')">🗑️ Delete</a>
                                                </div>
                                            </td>
                                        </tr>
                                <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ==================== TAB 5: MULTIPLE RESULTS ==================== -->
    <div id="tab-multiple" class="tab-content <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'multiple') ? 'active' : ''; ?>">
        <div class="admin-section">
            <h2>📝 Manage Multiple Results</h2>

            <!-- Add Result -->
            <form method="POST" class="admin-form" style="margin-bottom: 20px; padding: 20px; background: #f9f9f9; border-radius: 15px;">
                <input type="hidden" name="add_multiple_result" value="1">
                <h3 style="width:100%; margin-bottom:15px; font-size:16px;">Add New Result</h3>
                <div class="form-group">
                    <label>Game Name:</label>
                    <input type="text" name="mr_game_name" placeholder="e.g. gwalior" required>
                </div>
                <div class="form-group">
                    <label>Date:</label>
                    <input type="date" name="mr_result_date" required>
                </div>
                <div class="form-group">
                    <label>Result Number:</label>
                    <input type="text" name="mr_result_number" placeholder="e.g. 86" required>
                </div>
                <div class="form-group">
                    <label>Time:</label>
                    <input type="text" name="mr_result_time" placeholder="e.g. 2:25 PM">
                </div>
                <button type="submit" class="btn-success">➕ Add Result</button>
            </form>

            <!-- Results List -->
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
                        <?php else: ?>
                                <?php foreach ($multiple_results as $result): ?>
                                        <tr>
                                            <td><?php echo $result['id']; ?></td>
                                            <td><strong><?php echo ucfirst($result['game_name']); ?></strong></td>
                                            <td><?php echo $result['result_date']; ?></td>
                                            <td style="font-weight:bold; color:#c49a00; font-size:18px;"><?php echo $result['result_number']; ?></td>
                                            <td><?php echo $result['result_time'] ?: '--'; ?></td>
                                            <td>
                                                <div class="actions-cell">
                                                    <button class="btn-edit" onclick="openMultipleResultEditModal(<?php echo $result['id']; ?>, '<?php echo $result['game_name']; ?>', '<?php echo $result['result_date']; ?>', '<?php echo $result['result_number']; ?>', '<?php echo $result['result_time']; ?>')">✏️ Edit</button>
                                                    <a href="?delete_multiple_result=<?php echo $result['id']; ?>" class="btn-delete" onclick="return confirm('Delete this result?')">🗑️ Delete</a>
                                                </div>
                                            </td>
                                        </tr>
                                <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ==================== MODALS ==================== -->

<!-- Edit Game Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>✏️ Edit Game</h3>
        <form method="POST">
            <input type="hidden" name="update_result" value="1">
            <input type="hidden" name="game_name" id="editGameName">
            <div class="form-group">
                <label>Display Name (सट्टा का नाम):</label>
                <input type="text" name="display_name" id="editDisplayName">
            </div>
            <div class="form-group">
                <label>Yesterday Result:</label>
                <input type="text" name="yesterday_result" id="editYesterday">
            </div>
            <div class="form-group">
                <label>Today Result:</label>
                <input type="text" name="today_result" id="editToday">
            </div>
            <div class="form-group">
                <label>Result Time:</label>
                <input type="text" name="result_time" id="editTime">
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn-save">💾 Save</button>
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
            <div class="form-group">
                <label>Game Name:</label>
                <input type="text" name="timing_game_name" id="timingEditGameName" required>
            </div>
            <div class="form-group">
                <label>Time:</label>
                <input type="text" name="timing_time" id="timingEditTime" required>
            </div>
            <div class="form-group">
                <label>Emoji:</label>
                <input type="text" name="timing_emoji" id="timingEditEmoji">
            </div>
            <div class="form-group">
                <label>Active:</label>
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
            <div class="form-group">
                <label>Rate Type:</label>
                <input type="text" name="rate_type" id="rateEditType" required>
            </div>
            <div class="form-group">
                <label>Rate Value:</label>
                <input type="text" name="rate_value" id="rateEditValue" required>
            </div>
            <div class="form-group">
                <label>Active:</label>
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
            <div class="form-group">
                <label>Game Name:</label>
                <input type="text" name="mr_game_name" id="mrEditGameName" required>
            </div>
            <div class="form-group">
                <label>Date:</label>
                <input type="date" name="mr_result_date" id="mrEditDate" required>
            </div>
            <div class="form-group">
                <label>Result Number:</label>
                <input type="text" name="mr_result_number" id="mrEditNumber" required>
            </div>
            <div class="form-group">
                <label>Time:</label>
                <input type="text" name="mr_result_time" id="mrEditTime">
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn-save">💾 Save</button>
                <button type="button" class="btn-cancel" onclick="closeMultipleResultEditModal()">❌ Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript -->
<script>
    // Tab switching - Updated to work with URL
    function showTab(tabName) {
        window.location.href = 'admin-dashboard.php?tab=' + tabName;
    }

    // Edit Game Modal
    function openEditModal(game, yesterday, today, time, displayName) {
        document.getElementById('editGameName').value = game;
        document.getElementById('editDisplayName').value = displayName || '';
        document.getElementById('editYesterday').value = yesterday;
        document.getElementById('editToday').value = today;
        document.getElementById('editTime').value = time;
        document.getElementById('editModal').style.display = 'flex';
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    // Timing Edit Modal
    function openTimingEditModal(id, gameName, timing, emoji, isActive) {
        document.getElementById('timingEditId').value = id;
        document.getElementById('timingEditGameName').value = gameName;
        document.getElementById('timingEditTime').value = timing;
        document.getElementById('timingEditEmoji').value = emoji;
        document.getElementById('timingEditActive').value = isActive;
        document.getElementById('timingEditModal').style.display = 'flex';
    }

    function closeTimingEditModal() {
        document.getElementById('timingEditModal').style.display = 'none';
    }

    // Rate Edit Modal
    function openRateEditModal(id, rateType, rateValue, isActive) {
        document.getElementById('rateEditId').value = id;
        document.getElementById('rateEditType').value = rateType;
        document.getElementById('rateEditValue').value = rateValue;
        document.getElementById('rateEditActive').value = isActive;
        document.getElementById('rateEditModal').style.display = 'flex';
    }

    function closeRateEditModal() {
        document.getElementById('rateEditModal').style.display = 'none';
    }

    // Multiple Result Edit Modal
    function openMultipleResultEditModal(id, gameName, date, number, time) {
        document.getElementById('mrEditId').value = id;
        document.getElementById('mrEditGameName').value = gameName;
        document.getElementById('mrEditDate').value = date;
        document.getElementById('mrEditNumber').value = number;
        document.getElementById('mrEditTime').value = time || '';
        document.getElementById('multipleResultEditModal').style.display = 'flex';
    }

    function closeMultipleResultEditModal() {
        document.getElementById('multipleResultEditModal').style.display = 'none';
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    }

    // Close modals with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.modal').forEach(el => {
                el.style.display = 'none';
            });
        }
    });
</script>

<?php require_once 'footer.php'; ?>