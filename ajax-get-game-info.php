<?php
// ajax-get-game-info.php
header('Content-Type: application/json');
require_once 'config.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['game']) || empty($_GET['game'])) {
    echo json_encode(['success' => false, 'message' => 'No game specified']);
    exit;
}

$game = trim($_GET['game']);

try {
    $stmt = $pdo->prepare("SELECT display_name, result_time, yesterday_result, today_result, status, table_type FROM game_results WHERE LOWER(game_name) = LOWER(?) AND status = 1");
    $stmt->execute([$game]);
    $game_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($game_data) {
        echo json_encode([
            'success' => true,
            'display_name' => $game_data['display_name'] ?? '',
            'result_time' => $game_data['result_time'] ?? '',
            'yesterday_result' => $game_data['yesterday_result'] ?? '--',
            'today_result' => $game_data['today_result'] ?? 'WAIT',
            'status' => $game_data['status'],
            'table_type' => $game_data['table_type']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Game not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>