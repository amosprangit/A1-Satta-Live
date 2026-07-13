<?php
require_once 'config.php';

header('Content-Type: application/json');

$game = trim($_POST['game'] ?? '');
$year = trim($_POST['year'] ?? date('Y'));
$month = trim($_POST['month'] ?? date('m'));

if (empty($game)) {
    echo json_encode([
        'success' => false,
        'message' => 'Game name is required'
    ]);
    exit();
}

try {
    error_log("Chart Search => Game: $game | Month: $month | Year: $year");

    
    $start_date = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
    $end_date = date('Y-m-t', strtotime($start_date));
    
    error_log("Date range: $start_date to $end_date");
    
    // Query using new schema (chart_date, result)
    $stmt = $pdo->prepare("
        SELECT chart_date, result
        FROM chart_data
        WHERE LOWER(game_name) = LOWER(?)
        AND chart_date BETWEEN ? AND ?
        ORDER BY chart_date DESC
    ");

    $stmt->execute([
        $game,
        $start_date,
        $end_date
    ]);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log("Chart Results Found: " . count($results));

    if (empty($results)) {
        // Debug check if game exists at all in chart_data
        $checkStmt = $pdo->prepare("
            SELECT COUNT(*) as total
            FROM chart_data
            WHERE LOWER(game_name) = LOWER(?)
        ");
        $checkStmt->execute([$game]);
        $gameExists = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($gameExists['total'] > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Data exists for this game but not for selected month.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No chart data found for ' . strtoupper($game)
            ]);
        }
        exit();
    }

    echo json_encode([
        'success' => true,
        'data' => $results,
        'game' => $game,
        'month' => $month,
        'year' => $year,
        'total_records' => count($results)
    ]);

} catch (PDOException $e) {
    error_log("Chart Data Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database Error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Chart Data Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>