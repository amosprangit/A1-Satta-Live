<?php
// /admin/admin-dashboard.php - Main Admin Dashboard

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// FIXED: config.php is one level up
require_once 'config.php';

// FIXED: These files are in the same directory
require_once './admin/admin-functions.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    header('Location: admin-login.php');
    exit();
}

// Process all POST/GET handlers
require_once './admin/admin-handlers.php';

$page_title = 'Admin Dashboard - A1 Satta Live';
$current_tab = getCurrentTab();

// Get edit parameters
$edit_game = $_GET['edit_game'] ?? '';
$edit_date = $_GET['edit_date'] ?? '';
$edit_result = $_GET['edit_result'] ?? '';

// ============================================
// FETCH DATA WITH PROPER ERROR HANDLING
// ============================================

// Fetch Disawar data
// $disawer = getDisawarData($pdo);

// Fetch all games data
$gameData = getAllGameData($pdo);

// Extract game data with fallbacks
$all_games = $gameData['all'] ?? [];
$table1_games = $gameData['table1'] ?? [];
$table2_games = $gameData['table2'] ?? [];

// Fetch chart data
$chart_data_display = getChartData($pdo);

// Fetch game timings
$game_timings = getGameTimings($pdo);

// Fetch game rates
$game_rates = getGameRates($pdo);

// Fetch custom tables
$custom_tables = getCustomTablesList($pdo);

// Fetch stats
$stats = getStats($pdo);

// Get all games for dropdown (includes custom tables)
$all_games_list = getAllGameNamesForAdmin($pdo);

// FIXED: header.php is one level up
require_once 'header.php';
?>

<link rel="stylesheet" href="./css/admin-dashboard.css">

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
            <div class="number"><?php echo $stats['total_games'] ?? 0; ?></div>
            <div class="label">Total Games</div>
        </div>
        <div class="stat-card">
            <div class="number"><?php echo $stats['total_timings'] ?? 0; ?></div>
            <div class="label">Game Timings</div>
        </div>
        <div class="stat-card">
            <div class="number"><?php echo $stats['total_rates'] ?? 0; ?></div>
            <div class="label">Game Rates</div>
        </div>
        <div class="stat-card">
            <div class="number"><?php echo $stats['total_charts'] ?? 0; ?></div>
            <div class="label">Chart Entries</div>
        </div>
        <div class="stat-card">
            <div class="number"><?php echo $stats['total_tables'] ?? 0; ?></div>
            <div class="label">Custom Tables</div>
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
        <a href="admin-dashboard.php?tab=tables"
            class="tab-btn <?php echo $current_tab === 'tables' ? 'active' : ''; ?>">📋 Add Table</a>
        <a href="admin-dashboard.php?tab=settings"
            class="tab-btn <?php echo $current_tab === 'settings' ? 'active' : ''; ?>">⚙️ Settings</a>
    </div>

    <!-- Tab Contents -->
    <div id="tab-games" class="tab-content <?php echo $current_tab === 'games' ? 'active' : ''; ?>">
        <?php require_once './admin/admin-tabs.php'; ?>
        <?php renderGamesTab($pdo, $all_games, $table1_games, $table2_games, $all_games_list); ?>
    </div>

    <div id="tab-chart" class="tab-content <?php echo $current_tab === 'chart' ? 'active' : ''; ?>">
        <?php renderChartTab($pdo, $edit_game, $edit_date, $edit_result, $chart_data_display); ?>
    </div>

    <div id="tab-timings" class="tab-content <?php echo $current_tab === 'timings' ? 'active' : ''; ?>">
        <?php renderTimingsTab($pdo, $game_timings); ?>
    </div>

    <div id="tab-rates" class="tab-content <?php echo $current_tab === 'rates' ? 'active' : ''; ?>">
        <?php renderRatesTab($pdo, $game_rates); ?>
    </div>

    <div id="tab-tables" class="tab-content <?php echo $current_tab === 'tables' ? 'active' : ''; ?>">
        <?php renderTablesTab($pdo, $custom_tables); ?>
    </div>

    <div id="tab-settings" class="tab-content <?php echo $current_tab === 'settings' ? 'active' : ''; ?>">
        <?php renderSettingsTab($pdo); ?>
    </div>
</div>

<!-- Modals -->
<?php require_once './admin/admin-models.php';
renderModals(); ?>

<!-- JavaScript - Include the external JS file -->
<script src="./js/admin-dashboard.js"></script>

<style>
    .btn-secondary {
        background: #6c757d;
        color: #fff;
        padding: 10px 30px;
        border: none;
        border-radius: 30px;
        font-weight: bold;
        cursor: pointer;
        transition: transform 0.2s, opacity 0.2s;
        font-size: 14px;
        white-space: nowrap;
    }

    .btn-secondary:hover {
        background: #5a6268;
        transform: scale(1.03);
    }
</style>

<?php require_once 'footer.php'; ?>