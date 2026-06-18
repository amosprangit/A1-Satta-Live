<?php
require_once 'config.php';

header('Content-Type: application/json');

$game = $_POST['game'] ?? '';
$year = $_POST['year'] ?? date('Y');
$month = $_POST['month'] ?? date('m');

if (empty($game)) {
    echo json_encode(['success' => false, 'message' => 'Game name is required']);
    exit();
}

try {
    // Debug: Log the search parameters
    error_log("Searching for: game=$game, month=$month, year=$year");

    // Get chart data for the selected game and month
    // The date format in DB is like "01-06" (DD-MM)
    $search_pattern = $month . '-' . $year;

    $stmt = $pdo->prepare("SELECT * FROM chart_data 
                           WHERE game_name = ? 
                           AND date LIKE ? 
                           ORDER BY date ASC");
    $stmt->execute([$game, $search_pattern . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug: Log results count
    error_log("Found " . count($results) . " results for game=$game, pattern=$search_pattern%");

    if (empty($results)) {
        // Try without the year to see if data exists with different year
        $stmt2 = $pdo->prepare("SELECT * FROM chart_data WHERE game_name = ? ORDER BY date ASC");
        $stmt2->execute([$game]);
        $all_results = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($all_results)) {
            // Data exists but not for this month/year
            echo json_encode([
                'success' => false,
                'message' => 'No chart data found for ' . strtoupper($game) . ' in ' . $month . '-' . $year . '. Data exists for other dates.'
            ]);
            exit();
        }

        echo json_encode([
            'success' => false,
            'message' => 'No chart data found for ' . strtoupper($game)
        ]);
        exit();
    }

    echo json_encode([
        'success' => true,
        'data' => $results,
        'game' => $game,
        'year' => $year,
        'month' => $month
    ]);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>