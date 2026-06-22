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

    /*
     * Database date format:
     * 01-06
     * 02-06
     * 03-06
     * etc.
     *
     * Therefore we search by month only:
     * %-06
     */

    $stmt = $pdo->prepare("
        SELECT *
        FROM chart_data
        WHERE TRIM(game_name) = TRIM(?)
        AND date LIKE ?
        ORDER BY CAST(SUBSTRING_INDEX(date,'-',1) AS UNSIGNED) ASC
    ");

    $stmt->execute([
        $game,
        '%-' . $month
    ]);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log("Chart Results Found: " . count($results));

    if (empty($results)) {

        // Debug check if game exists at all
        $checkStmt = $pdo->prepare("
            SELECT COUNT(*) as total
            FROM chart_data
            WHERE TRIM(game_name) = TRIM(?)
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
        'message' => 'Database Error',
        'error' => $e->getMessage()
    ]);
}
?>