<?php
require_once 'config.php';

// Only allow admins to view this
if (!isAdminLoggedIn()) {
    die('Access denied');
}

echo "<h1>Chart Data Debug</h1>";

// Get all chart data
$stmt = $pdo->query("SELECT * FROM chart_data ORDER BY game_name, date");
$all_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>All Chart Data (" . count($all_data) . " entries)</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Game Name</th><th>Date</th><th>Result</th><th>Table Type</th><th>Created</th></tr>";

foreach ($all_data as $row) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td><strong>" . $row['game_name'] . "</strong></td>";
    echo "<td>" . $row['date'] . "</td>";
    echo "<td style='font-weight:bold;color:#c49a00;'>" . $row['result_number'] . "</td>";
    echo "<td>" . $row['table_type'] . "</td>";
    echo "<td>" . $row['created_at'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>Games List</h2>";
$games = getGameNames($pdo);
echo "<ul>";
foreach ($games as $game) {
    echo "<li>" . $game . "</li>";
}
echo "</ul>";

echo "<h2>Sample Query Test</h2>";

// Test search with a specific game and month
$test_game = 'gwalior';
$test_month = '06';
$test_year = '2026';

$stmt = $pdo->prepare("SELECT * FROM chart_data WHERE game_name = ? AND date LIKE ?");
$stmt->execute([$test_game, $test_month . '-' . $test_year . '%']);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<p>Searching for: game=$test_game, pattern=" . $test_month . '-' . $test_year . "%</p>";
echo "<p>Found: " . count($results) . " results</p>";

if (!empty($results)) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Game</th><th>Date</th><th>Result</th></tr>";
    foreach ($results as $row) {
        echo "<tr>";
        echo "<td>" . $row['game_name'] . "</td>";
        echo "<td>" . $row['date'] . "</td>";
        echo "<td>" . $row['result_number'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<p><a href='admin-dashboard.php?tab=chart'>Back to Admin Dashboard</a></p>";
echo "<p><a href='index.php'>Back to Homepage</a></p>";
?>