<?php
require_once './admin-functions.php';

// Get game name from URL
$game_name = isset($_GET['game']) ? trim($_GET['game']) : '';

if (empty($game_name)) {
    echo json_encode(['success' => false, 'message' => 'No game specified']);
    exit;
}

$game_data = null;
$source = '';

// FIRST: Check game_results
try {
    $stmt = $pdo->prepare("SELECT display_name, today_result, yesterday_result, result_time FROM game_results WHERE LOWER(game_name) = LOWER(?) AND status = 1");
    $stmt->execute([$game_name]);
    $game_data = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($game_data) {
        $source = 'Main Table';
    }
} catch (PDOException $e) {
    error_log("Error fetching from game_results: " . $e->getMessage());
}

// SECOND: If not found, check custom_table_games
if (!$game_data) {
    try {
        $stmt = $pdo->prepare("SELECT display_name, today_result, yesterday_result, result_time, table_id FROM custom_table_games WHERE LOWER(game_name) = LOWER(?)");
        $stmt->execute([$game_name]);
        $game_data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($game_data) {
            // Get the table name
            $stmt2 = $pdo->prepare("SELECT table_name FROM custom_tables WHERE id = ?");
            $stmt2->execute([$game_data['table_id']]);
            $table_info = $stmt2->fetch(PDO::FETCH_ASSOC);
            $source = $table_info['table_name'] ?? 'Custom Table';
        }
    } catch (PDOException $e) {
        error_log("Error fetching from custom_table_games: " . $e->getMessage());
    }
}

if ($game_data) {
    echo json_encode([
        'success' => true,
        'display_name' => $game_data['display_name'] ?? '',
        'today_result' => $game_data['today_result'] ?? 'WAIT',
        'yesterday_result' => $game_data['yesterday_result'] ?? '--',
        'result_time' => $game_data['result_time'] ?? '',
        'source' => $source
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Game not found']);
}
?>