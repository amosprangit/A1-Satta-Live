<?php
// /admin/includes/admin-functions.php

function writeAdminLog($message)
{
    $log_file = __DIR__ . '/../admin_update_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

function getCurrentTab()
{
    return $_GET['tab'] ?? 'games';
}

// /admin/includes/admin-functions.php

function getAllGameNamesForAdmin($pdo)
{
    $allGames = [];

    // Get regular games from game_results
    try {
        $stmt = $pdo->query("SELECT game_name, display_name FROM game_results WHERE status = 1 ORDER BY game_name");
        $regularGames = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($regularGames as $game) {
            $allGames[] = [
                'game_name' => $game['game_name'],
                'display_name' => $game['display_name'] ?: strtoupper($game['game_name']),
                'source' => 'Main Table'
            ];
        }
    } catch (PDOException $e) {
        error_log("Error fetching regular games: " . $e->getMessage());
    }

    // Get custom table games
    try {
        $stmt = $pdo->query("
            SELECT ctg.game_name, ctg.display_name, ct.table_name 
            FROM custom_table_games ctg
            INNER JOIN custom_tables ct ON ctg.table_id = ct.id
            WHERE ct.status = 1
            ORDER BY ctg.game_name
        ");
        $customGames = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($customGames as $game) {
            $allGames[] = [
                'game_name' => $game['game_name'],
                'display_name' => $game['display_name'] ?: strtoupper($game['game_name']),
                'source' => $game['table_name']
            ];
        }
    } catch (PDOException $e) {
        error_log("Error fetching custom games: " . $e->getMessage());
    }

    return $allGames;
}

function getDisawarData($pdo)
{
    try {
        $stmt = $pdo->prepare("SELECT * FROM game_results WHERE LOWER(game_name) = 'disawar' AND status = 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return false;
    }
}

function getAllGameData($pdo)
{
    try {
        $stmt = $pdo->query("SELECT * FROM game_results WHERE status = 1 ORDER BY FIELD(game_name, 'pushkar', 'sadar bazar', 'gwalior', 'delhi bazar', 'shri ganesh', 'faridabad', 'gaziabad', 'gali', 'disawar')");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $all_games = [];
        $table1_games = [];
        $table2_games = [];

        foreach ($data as $game) {
            $all_games[$game['game_name']] = $game;
            if ($game['table_type'] == 'table2') {
                $table2_games[] = $game['game_name'];
            } else {
                $table1_games[] = $game['game_name'];
            }
        }

        if (empty($table1_games) && empty($table2_games)) {
            $table1_games = ['pushkar', 'sadar bazar', 'gwalior', 'delhi bazar', 'shri ganesh', 'gaziabad', 'gali'];
            $table2_games = ['disawar', 'faridabad'];
        }

        return [
            'all' => $all_games,
            'table1' => $table1_games,
            'table2' => $table2_games
        ];

    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return [
            'all' => [],
            'table1' => ['pushkar', 'sadar bazar', 'gwalior', 'delhi bazar', 'shri ganesh', 'gaziabad', 'gali'],
            'table2' => ['disawar', 'faridabad']
        ];
    }
}

function getChartData($pdo)
{
    try {
        $stmt = $pdo->query("SELECT * FROM chart_data ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function getGameTimings($pdo)
{
    try {
        $stmt = $pdo->query("SELECT * FROM game_timings ORDER BY display_order, id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function getGameRates($pdo)
{
    try {
        $stmt = $pdo->query("SELECT * FROM game_rates ORDER BY display_order, id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function getCustomTablesList($pdo)
{
    try {
        $stmt = $pdo->query("SELECT * FROM custom_tables ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function getCustomTableGamesList($pdo, $table_id)
{
    try {
        $stmt = $pdo->prepare("SELECT * FROM custom_table_games WHERE table_id = ? ORDER BY id");
        $stmt->execute([$table_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function getGameNamesList($pdo)
{
    try {
        $stmt = $pdo->query("SELECT DISTINCT game_name FROM game_results WHERE status = 1 ORDER BY game_name");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        return [];
    }
}

function getStats($pdo)
{
    $all_games = getAllGameData($pdo);
    $timings = getGameTimings($pdo);
    $rates = getGameRates($pdo);
    $charts = getChartData($pdo);
    $custom_tables = getCustomTablesList($pdo);

    return [
        'total_games' => count($all_games['all']),
        'total_timings' => count($timings),
        'total_rates' => count($rates),
        'total_charts' => count($charts),
        'total_tables' => count($custom_tables)
    ];
}

function getGameDisplayName($pdo, $game_name)
{
    try {
        $stmt = $pdo->prepare("SELECT display_name FROM game_results WHERE LOWER(game_name) = LOWER(?) AND status = 1");
        $stmt->execute([$game_name]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['display_name'] : ucfirst($game_name);
    } catch (PDOException $e) {
        return ucfirst($game_name);
    }
}

function getWebsiteContentValue($pdo, $key, $default = '')
{
    try {
        $stmt = $pdo->prepare("SELECT content_value FROM website_content WHERE content_key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['content_value'] : $default;
    } catch (PDOException $e) {
        return $default;
    }
}
function updateWebsiteContent($pdo, $key, $value) {
    try {
        $stmt = $pdo->prepare("INSERT INTO website_content (content_key, content_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE content_value = ?");
        return $stmt->execute([$key, $value, $value]);
    } catch (PDOException $e) {
        return false;
    }
}

function logoutAdmin() {
    session_destroy();
    header('Location: admin-login.php');
    exit();
}
?>