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
        // ============ TIMING OPERATIONS ============
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

        // ============ RATE OPERATIONS ============
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

        // Add these cases to the switch statement in admin-ajax.php

        case 'update_chart':
            $game = $_POST['game'] ?? '';
            $date = $_POST['date'] ?? '';
            $result = $_POST['result'] ?? '';
            $table_type = $_POST['table_type'] ?? 'table1';

            if (empty($game) || empty($date) || empty($result)) {
                throw new Exception('All fields are required');
            }

            $result_bool = updateChartData($pdo, $game, $date, $result, $table_type);
            echo json_encode(['success' => $result_bool]);
            break;

        case 'delete_chart':
            $game = $_POST['game'] ?? '';
            $date = $_POST['date'] ?? '';
            $table_type = $_POST['table_type'] ?? 'table1';

            if (empty($game) || empty($date)) {
                throw new Exception('Game name and date are required');
            }

            $result_bool = deleteChartData($pdo, $game, $date, $table_type);
            echo json_encode(['success' => $result_bool]);
            break;

        // Add this case to the switch statement in admin-ajax.php

        case 'generate_month_data':
            $game = $_POST['game'] ?? '';
            $month = $_POST['month'] ?? date('m');
            $year = $_POST['year'] ?? date('Y');

            if (empty($game)) {
                throw new Exception('Game name is required');
            }

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

            echo json_encode(['success' => true, 'message' => "Generated $count entries for $game"]);
            break;
        
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>