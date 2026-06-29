<?php
// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$dbname = 'a1satta_admin';
$username = 'root';
$password = 'password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// ============ AUTHENTICATION ============
function isAdminLoggedIn()
{
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function loginAdmin($pdo, $username, $password)
{
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_id'] = $user['id'];
        return true;
    }
    return false;
}

function logoutAdmin()
{
    session_destroy();
    header('Location: admin-login.php');
    exit();
}

// ============ GAME RESULTS CRUD ============
function getAllGames($pdo)
{
    $stmt = $pdo->query("SELECT * FROM game_results WHERE status = 1 ORDER BY 
        FIELD(game_name, 'disawar', 'sadar bazar', 'gwalior', 'delhi bazar', 'delhi matka', 
        'shri ganesh', 'agra', 'faridabad', 'alwar', 'gaziabad', 'dwarka', 'gali',
        'hr satta', 'kkr city', 'madhupuri', 'ujjala super', 'karol bagh', 
        'delhi darbar', 'new ganga', 'fatehabad', 'raj shree', 'mandi bazar', 
        'lion bazar', 'dehradun city', 'daman')");
    $results = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $results[$row['game_name']] = $row;
    }
    return $results;
}

function getWebsiteContent($pdo, $key)
{
    try {
        $stmt = $pdo->prepare("SELECT content_value FROM website_content WHERE content_key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['content_value'] : null;
    } catch (PDOException $e) {
        return null;
    }
}

function updateWebsiteContent($pdo, $key, $value)
{
    try {
        $stmt = $pdo->prepare("INSERT INTO website_content (content_key, content_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE content_value = ?");
        return $stmt->execute([$key, $value, $value]);
    } catch (PDOException $e) {
        return false;
    }
}

function getGameResults($pdo, $game_name)
{
    $stmt = $pdo->prepare("SELECT * FROM game_results WHERE LOWER(game_name) = LOWER(?) AND status = 1");
    $stmt->execute([$game_name]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function addGame($pdo, $data)
{
    $stmt = $pdo->prepare("INSERT INTO game_results (game_name, display_name, today_result, yesterday_result, result_time, table_type, status, is_latest) 
                           VALUES (?, ?, ?, ?, ?, ?, 1, 1)");
    return $stmt->execute([
        $data['game_name'], 
        $data['display_name'], 
        $data['today_result'], 
        $data['yesterday_result'], 
        $data['result_time'], 
        $data['table_type']
    ]);
}

function updateGame($pdo, $data)
{
    $stmt = $pdo->prepare("UPDATE game_results SET 
        today_result = ?, 
        yesterday_result = ?, 
        result_time = ?, 
        display_name = ?,
        is_latest = 1
        WHERE LOWER(game_name) = LOWER(?)");
    return $stmt->execute([
        $data['today_result'], 
        $data['yesterday_result'], 
        $data['result_time'], 
        $data['display_name'], 
        $data['game_name']
    ]);
}

function deleteGame($pdo, $game_name)
{
    try {
        // Delete from chart_data first
        $stmt = $pdo->prepare("DELETE FROM chart_data WHERE LOWER(game_name) = LOWER(?)");
        $stmt->execute([$game_name]);
        
        // Delete from game_results
        $stmt = $pdo->prepare("DELETE FROM game_results WHERE LOWER(game_name) = LOWER(?)");
        return $stmt->execute([$game_name]);
    } catch (PDOException $e) {
        error_log("Delete game error: " . $e->getMessage());
        return false;
    }
}

function toggleGameStatus($pdo, $game_name)
{
    $stmt = $pdo->prepare("UPDATE game_results SET status = CASE WHEN status = 1 THEN 0 ELSE 1 END WHERE LOWER(game_name) = LOWER(?)");
    return $stmt->execute([$game_name]);
}

function updateDisawer($pdo, $today_result, $yesterday_result, $display_name = 'DISAWAR', $result_time = '5:15 AM')
{
    $stmt = $pdo->prepare("UPDATE game_results SET 
        today_result = ?, 
        yesterday_result = ?, 
        display_name = ?,
        result_time = ?,
        is_latest = 1
        WHERE LOWER(game_name) = 'disawar'");
    return $stmt->execute([$today_result, $yesterday_result, $display_name, $result_time]);
}

// ============ CHART DATA CRUD (Updated for new schema) ============
function getChartData($pdo, $game_name, $date)
{
    $stmt = $pdo->prepare("SELECT result FROM chart_data WHERE LOWER(game_name) = LOWER(?) AND chart_date = ?");
    $stmt->execute([$game_name, $date]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['result'] : '--';
}

function getAllChartData($pdo)
{
    try {
        $sql = "SELECT * FROM chart_data ORDER BY chart_date DESC, game_name ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error in getAllChartData: " . $e->getMessage());
        return [];
    }
}

function updateChartData($pdo, $game_name, $date, $result)
{
    try {
        $stmt = $pdo->prepare("INSERT INTO chart_data (game_name, chart_date, result) 
                               VALUES (?, ?, ?) 
                               ON DUPLICATE KEY UPDATE result = VALUES(result)");
        return $stmt->execute([$game_name, $date, $result]);
    } catch (PDOException $e) {
        error_log("Error in updateChartData: " . $e->getMessage());
        return false;
    }
}

function deleteChartData($pdo, $game_name, $date)
{
    $stmt = $pdo->prepare("DELETE FROM chart_data WHERE LOWER(game_name) = LOWER(?) AND chart_date = ?");
    return $stmt->execute([$game_name, $date]);
}

function getChartDates($pdo)
{
    $sql = "SELECT DISTINCT chart_date FROM chart_data ORDER BY chart_date DESC";
    $stmt = $pdo->query($sql);
    $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
    return $dates;
}

function getChartDataByMonth($pdo, $game_name, $year, $month)
{
    try {
        $start_date = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
        $end_date = date('Y-m-t', strtotime($start_date));
        
        $stmt = $pdo->prepare("SELECT chart_date, result FROM chart_data 
                               WHERE LOWER(game_name) = LOWER(?) 
                               AND chart_date BETWEEN ? AND ?
                               ORDER BY chart_date DESC");
        $stmt->execute([$game_name, $start_date, $end_date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching chart data by month: " . $e->getMessage());
        return [];
    }
}

// ============ GAME CHART DATA FOR DEDICATED PAGES ============
function getGameChartData($pdo, $game_name, $year, $month)
{
    return getChartDataByMonth($pdo, $game_name, $year, $month);
}

function getGameTiming($pdo, $game_name)
{
    try {
        $stmt = $pdo->prepare("SELECT timing FROM game_timings WHERE LOWER(game_name) = LOWER(?) AND is_active = 1 LIMIT 1");
        $stmt->execute([$game_name]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['timing'] : null;
    } catch (PDOException $e) {
        return null;
    }
}

function getGameInfo($pdo, $game_name)
{
    try {
        $stmt = $pdo->prepare("
            SELECT gr.*, gt.timing 
            FROM game_results gr 
            LEFT JOIN game_timings gt ON LOWER(gr.game_name) = LOWER(gt.game_name) AND gt.is_active = 1 
            WHERE LOWER(gr.game_name) = LOWER(?) AND gr.status = 1
            LIMIT 1
        ");
        $stmt->execute([$game_name]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching game info: " . $e->getMessage());
        return null;
    }
}

function getAllChartDataForGame($pdo, $game_name)
{
    try {
        $stmt = $pdo->prepare("SELECT chart_date, result 
                               FROM chart_data 
                               WHERE LOWER(game_name) = LOWER(?)
                               ORDER BY chart_date DESC");
        $stmt->execute([$game_name]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching chart data for game: " . $e->getMessage());
        return [];
    }
}

// ============ GAME TIMINGS CRUD ============
function getGameTimings($pdo)
{
    $stmt = $pdo->query("SELECT * FROM game_timings WHERE is_active = 1 ORDER BY display_order");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllGameTimings($pdo)
{
    $stmt = $pdo->query("SELECT * FROM game_timings ORDER BY display_order");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addGameTiming($pdo, $game_name, $timing, $emoji = '😇')
{
    $stmt = $pdo->prepare("INSERT INTO game_timings (game_name, timing, emoji) VALUES (?, ?, ?)");
    return $stmt->execute([$game_name, $timing, $emoji]);
}

function updateGameTiming($pdo, $id, $game_name, $timing, $emoji, $is_active = 1)
{
    $stmt = $pdo->prepare("UPDATE game_timings SET game_name = ?, timing = ?, emoji = ?, is_active = ? WHERE id = ?");
    return $stmt->execute([$game_name, $timing, $emoji, $is_active, $id]);
}

function deleteGameTiming($pdo, $id)
{
    $stmt = $pdo->prepare("DELETE FROM game_timings WHERE id = ?");
    return $stmt->execute([$id]);
}

function toggleGameTiming($pdo, $id, $is_active)
{
    $stmt = $pdo->prepare("UPDATE game_timings SET is_active = ? WHERE id = ?");
    return $stmt->execute([$is_active, $id]);
}

// ============ MULTIPLE RESULTS CRUD ============
function getGameMultipleResults($pdo, $game_name = null, $limit = null)
{
    $sql = "SELECT * FROM game_multiple_results";
    $params = [];

    if ($game_name) {
        $sql .= " WHERE LOWER(game_name) = LOWER(?)";
        $params[] = $game_name;
    }

    $sql .= " ORDER BY result_date DESC, id DESC";

    if ($limit) {
        $sql .= " LIMIT ?";
        $params[] = (int) $limit;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addGameResult($pdo, $game_name, $result_date, $result_number, $result_time = null)
{
    $stmt = $pdo->prepare("INSERT INTO game_multiple_results (game_name, result_date, result_number, result_time) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$game_name, $result_date, $result_number, $result_time]);
}

function updateGameResult($pdo, $id, $game_name, $result_date, $result_number, $result_time = null)
{
    $stmt = $pdo->prepare("UPDATE game_multiple_results SET game_name = ?, result_date = ?, result_number = ?, result_time = ? WHERE id = ?");
    return $stmt->execute([$game_name, $result_date, $result_number, $result_time, $id]);
}

function deleteGameResult($pdo, $id)
{
    $stmt = $pdo->prepare("DELETE FROM game_multiple_results WHERE id = ?");
    return $stmt->execute([$id]);
}

// ============ GAME RATES CRUD ============
function getGameRates($pdo)
{
    $stmt = $pdo->query("SELECT * FROM game_rates WHERE is_active = 1 ORDER BY display_order");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllGameRates($pdo)
{
    $stmt = $pdo->query("SELECT * FROM game_rates ORDER BY display_order");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addGameRate($pdo, $rate_type, $rate_value, $display_order = 0)
{
    $stmt = $pdo->prepare("INSERT INTO game_rates (rate_type, rate_value, display_order) VALUES (?, ?, ?)");
    return $stmt->execute([$rate_type, $rate_value, $display_order]);
}

function updateGameRate($pdo, $id, $rate_type, $rate_value, $is_active = 1)
{
    $stmt = $pdo->prepare("UPDATE game_rates SET rate_type = ?, rate_value = ?, is_active = ? WHERE id = ?");
    return $stmt->execute([$rate_type, $rate_value, $is_active, $id]);
}

function deleteGameRate($pdo, $id)
{
    $stmt = $pdo->prepare("DELETE FROM game_rates WHERE id = ?");
    return $stmt->execute([$id]);
}

function toggleGameRate($pdo, $id, $is_active)
{
    $stmt = $pdo->prepare("UPDATE game_rates SET is_active = ? WHERE id = ?");
    return $stmt->execute([$is_active, $id]);
}

// ============ ADMIN USER MANAGEMENT ============
function getAdminUsers($pdo)
{
    $stmt = $pdo->query("SELECT id, username, email, created_at FROM admin_users ORDER BY id");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addAdminUser($pdo, $username, $password, $email = null)
{
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, email) VALUES (?, ?, ?)");
    return $stmt->execute([$username, $hashed_password, $email]);
}

function deleteAdminUser($pdo, $id)
{
    $stmt = $pdo->prepare("DELETE FROM admin_users WHERE id = ?");
    return $stmt->execute([$id]);
}

// ============ NOTIFICATIONS CRUD ============
function getNotifications($pdo, $is_active = true)
{
    $sql = "SELECT * FROM notifications";
    if ($is_active) {
        $sql .= " WHERE is_active = 1";
    }
    $sql .= " ORDER BY created_at DESC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addNotification($pdo, $title, $message, $type = 'info')
{
    $stmt = $pdo->prepare("INSERT INTO notifications (title, message, type) VALUES (?, ?, ?)");
    return $stmt->execute([$title, $message, $type]);
}

function updateNotification($pdo, $id, $title, $message, $type, $is_active)
{
    $stmt = $pdo->prepare("UPDATE notifications SET title = ?, message = ?, type = ?, is_active = ? WHERE id = ?");
    return $stmt->execute([$title, $message, $type, $is_active, $id]);
}

function deleteNotification($pdo, $id)
{
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ?");
    return $stmt->execute([$id]);
}

// ============ HELPER FUNCTIONS ============
function getGamesByTable($pdo, $table_type)
{
    $stmt = $pdo->prepare("SELECT game_name FROM game_results WHERE table_type = ? AND status = 1 ORDER BY game_name");
    $stmt->execute([$table_type]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getGameNames($pdo)
{
    $stmt = $pdo->query("SELECT game_name FROM game_results WHERE status = 1 ORDER BY game_name");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getGameDisplayName($pdo, $game_name)
{
    $stmt = $pdo->prepare("SELECT display_name FROM game_results WHERE LOWER(game_name) = LOWER(?) AND status = 1");
    $stmt->execute([$game_name]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['display_name'] : ucfirst($game_name);
}

function getLatestGameResult($pdo)
{
    $stmt = $pdo->query("SELECT * FROM game_results WHERE is_latest = 1 AND status = 1 ORDER BY id DESC LIMIT 1");
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateIsLatest($pdo)
{
    // Reset all is_latest to 0
    $pdo->query("UPDATE game_results SET is_latest = 0");
    // Set is_latest = 1 for games with today_result != WAIT
    $pdo->query("UPDATE game_results SET is_latest = 1 WHERE today_result != 'WAIT' AND today_result != '-1' AND today_result != '' AND status = 1");
}
?>