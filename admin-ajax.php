<?php
require_once 'config.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? '';

header('Content-Type: application/json');

try {
    switch ($action) {
        // ============================================
        // GET GAME INFO - NEW FOR DYNAMIC EDITOR
        // ============================================
        case 'get_game_info':
            $game = $_GET['game'] ?? $_POST['game'] ?? '';

            if (empty($game)) {
                throw new Exception('Game name is required');
            }

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
            break;

        // ============================================
        // TIMING OPERATIONS
        // ============================================
        case 'add_timing':
            $game = $_POST['game'] ?? '';
            $time = $_POST['time'] ?? '';
            $emoji = $_POST['emoji'] ?? '😇';

            if (empty($game) || empty($time)) {
                throw new Exception('Game name and time are required');
            }

            $result = addGameTiming($pdo, $game, $time, $emoji);
            echo json_encode(['success' => $result]);
            break;

        case 'update_timing':
            $id = $_POST['id'] ?? 0;
            $game = $_POST['game'] ?? '';
            $time = $_POST['time'] ?? '';
            $emoji = $_POST['emoji'] ?? '😇';

            if (!$id || empty($game) || empty($time)) {
                throw new Exception('All fields are required');
            }

            $result = updateGameTiming($pdo, $id, $game, $time, $emoji);
            echo json_encode(['success' => $result]);
            break;

        case 'delete_timing':
            $id = $_POST['id'] ?? 0;

            if (!$id) {
                throw new Exception('ID is required');
            }

            $result = deleteGameTiming($pdo, $id);
            echo json_encode(['success' => $result]);
            break;

        case 'toggle_timing':
            $id = $_POST['id'] ?? 0;
            $status = $_POST['status'] ?? 1;

            if (!$id) {
                throw new Exception('ID is required');
            }

            $result = toggleGameTiming($pdo, $id, $status);
            echo json_encode(['success' => $result]);
            break;

        // ============================================
        // RATE OPERATIONS
        // ============================================
        case 'add_rate':
            $type = $_POST['type'] ?? '';
            $value = $_POST['value'] ?? '';

            if (empty($type) || empty($value)) {
                throw new Exception('Rate type and value are required');
            }

            $result = addGameRate($pdo, $type, $value);
            echo json_encode(['success' => $result]);
            break;

        case 'update_rate':
            $id = $_POST['id'] ?? 0;
            $type = $_POST['type'] ?? '';
            $value = $_POST['value'] ?? '';

            if (!$id || empty($type) || empty($value)) {
                throw new Exception('All fields are required');
            }

            $result = updateGameRate($pdo, $id, $type, $value);
            echo json_encode(['success' => $result]);
            break;

        case 'delete_rate':
            $id = $_POST['id'] ?? 0;

            if (!$id) {
                throw new Exception('ID is required');
            }

            $result = deleteGameRate($pdo, $id);
            echo json_encode(['success' => $result]);
            break;

        case 'toggle_rate':
            $id = $_POST['id'] ?? 0;
            $status = $_POST['status'] ?? 1;

            if (!$id) {
                throw new Exception('ID is required');
            }

            $result = toggleGameRate($pdo, $id, $status);
            echo json_encode(['success' => $result]);
            break;

        // ============================================
        // CHART OPERATIONS - UPDATED FOR NEW SCHEMA
        // ============================================
        case 'update_chart':
            $game = $_POST['game'] ?? '';
            $date = $_POST['date'] ?? '';
            $result = $_POST['result'] ?? '';

            if (empty($game) || empty($date) || empty($result)) {
                throw new Exception('All fields are required');
            }

            // Use the updated function with new schema
            $result_bool = updateChartData($pdo, $game, $date, $result);
            echo json_encode(['success' => $result_bool]);
            break;

        case 'delete_chart':
            $game = $_POST['game'] ?? '';
            $date = $_POST['date'] ?? '';

            if (empty($game) || empty($date)) {
                throw new Exception('Game name and date are required');
            }

            $result_bool = deleteChartData($pdo, $game, $date);
            echo json_encode(['success' => $result_bool]);
            break;

        // ============================================
        // GENERATE MONTH DATA - UPDATED FOR NEW SCHEMA
        // ============================================
        case 'generate_month_data':
            $game = $_POST['game'] ?? '';
            $month = $_POST['month'] ?? date('m');
            $year = $_POST['year'] ?? date('Y');

            if (empty($game)) {
                throw new Exception('Game name is required');
            }

            $days_in_month = cal_days_in_month(CAL_GREGORIAN, (int) $month, (int) $year);
            $sample_results = ['12', '45', '78', '23', '56', '89', '34', '67', '90', '15', '48', '71', '29', '53', '86', '41', '74', '18', '62', '95', '37', '50', '83', '26', '59', '92', '35', '68', '10', '43', '76'];

            // Determine table type
            $table1_games = ['sadar bazar', 'gwalior', 'delhi bazar', 'shri ganesh', 'faridabad', 'gaziabad', 'gali', 'disawar'];
            $table_type = in_array($game, $table1_games) ? 'table1' : 'table2';

            $count = 0;
            for ($day = 1; $day <= $days_in_month; $day++) {
                $date_str = date('Y-m-d', strtotime("$year-$month-$day"));
                $result = $sample_results[($day - 1) % count($sample_results)];

                $stmt = $pdo->prepare("INSERT INTO chart_data (game_name, chart_date, result) 
                               VALUES (?, ?, ?) 
                               ON DUPLICATE KEY UPDATE result = VALUES(result)");
                if ($stmt->execute([$game, $date_str, $result])) {
                    $count++;
                }
            }

            echo json_encode(['success' => true, 'message' => "Generated $count entries for $game"]);
            break;

        // ============================================
        // UPDATE GAME STATUS - NEW
        // ============================================
        case 'toggle_game':
            $game = $_POST['game'] ?? '';

            if (empty($game)) {
                throw new Exception('Game name is required');
            }

            $stmt = $pdo->prepare("UPDATE game_results SET status = CASE WHEN status = 1 THEN 0 ELSE 1 END WHERE LOWER(game_name) = LOWER(?)");
            $result = $stmt->execute([$game]);
            echo json_encode(['success' => $result]);
            break;

        // ============================================
        // DELETE GAME - NEW
        // ============================================
        case 'delete_game':
            $game = $_POST['game'] ?? '';

            if (empty($game)) {
                throw new Exception('Game name is required');
            }

            // Delete from chart_data first
            $stmt = $pdo->prepare("DELETE FROM chart_data WHERE LOWER(game_name) = LOWER(?)");
            $stmt->execute([$game]);

            // Delete from game_results
            $stmt = $pdo->prepare("DELETE FROM game_results WHERE LOWER(game_name) = LOWER(?)");
            $result = $stmt->execute([$game]);
            echo json_encode(['success' => $result]);
            break;

        // ============================================
        // GET LATEST GAME - NEW
        // ============================================
        case 'get_latest_game':
            $stmt = $pdo->query("SELECT * FROM game_results WHERE status = 1 AND is_latest = 1 ORDER BY id DESC LIMIT 1");
            $game = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($game) {
                echo json_encode([
                    'success' => true,
                    'game_name' => $game['game_name'],
                    'display_name' => $game['display_name'],
                    'today_result' => $game['today_result'],
                    'yesterday_result' => $game['yesterday_result'],
                    'result_time' => $game['result_time']
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No latest game found']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>