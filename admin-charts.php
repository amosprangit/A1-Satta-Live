<!-- <?php
require_once 'config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit();
}

// Handle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_chart'])) {
        $stmt = $pdo->prepare("UPDATE chart_data SET result_number = ? WHERE game_name = ? AND result_date = ?");
        $stmt->execute([$_POST['result_number'], $_POST['game_name'], $_POST['result_date']]);
        $success = "✅ Chart updated successfully!";
    }

    if (isset($_POST['add_entry'])) {
        $stmt = $pdo->prepare("INSERT INTO chart_data (game_name, result_date, result_number) VALUES (?, ?, ?) 
                               ON DUPLICATE KEY UPDATE result_number = VALUES(result_number)");
        $stmt->execute([$_POST['game_name'], $_POST['result_date'], $_POST['result_number']]);
        $success = "✅ New entry added!";
    }

    if (isset($_POST['delete_entry'])) {
        $stmt = $pdo->prepare("DELETE FROM chart_data WHERE game_name = ? AND result_date = ?");
        $stmt->execute([$_POST['game_name'], $_POST['result_date']]);
        $success = "✅ Entry deleted!";
    }
}

// Get current month
$current_month = $_GET['month'] ?? date('Y-m');
$month_name = date('F Y', strtotime($current_month . '-01'));

// Get all games
$games = $pdo->query("SELECT DISTINCT game_name FROM chart_data ORDER BY game_name")->fetchAll();

// Get all dates in this month
$dates = $pdo->prepare("SELECT DISTINCT result_date FROM chart_data WHERE DATE_FORMAT(result_date, '%Y-%m') = ? ORDER BY result_date");
$dates->execute([$current_month]);
$date_list = $dates->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Chart Editor - A1 Satta Live Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5f5f5;
        }

        .header {
            background: #1e1e2a;
            color: #ffd800;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .container {
            max-width: 1600px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .month-nav {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-bottom: 20px;
        }

        .month-nav a,
        .month-nav button {
            background: #ffd800;
            padding: 10px 20px;
            text-decoration: none;
            color: #1e1e2a;
            border-radius: 30px;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }

        .table-responsive {
            overflow-x: auto;
            max-height: 600px;
            overflow-y: auto;
        }

        .chart-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .chart-table th,
        .chart-table td {
            border: 1px solid #ddd;
            padding: 8px 5px;
            text-align: center;
        }

        .chart-table th {
            background: #ffd800;
            position: sticky;
            top: 0;
        }

        .chart-table input {
            width: 70px;
            padding: 5px;
            text-align: center;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .update-btn {
            background: #28a745;
            color: white;
            padding: 4px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
            padding: 4px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .add-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin-top: 20px;
        }

        .add-form select,
        .add-form input,
        .add-form button {
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .add-form button {
            background: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #c49a00;
            text-decoration: none;
        }

        .game-col {
            background: #fff8e7;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>📊 Chart Data Editor - A1 Satta Live</h2>
        <div>
            <a href="admin-dashboard.php" style="color:#ffd800; margin-right:20px;">← Dashboard</a>
            <a href="logout.php"
                style="background:#ffd800; color:#1e1e2a; padding:8px 20px; border-radius:30px; text-decoration:none;">Logout</a>
        </div>
    </div>

    <div class="container">
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="month-nav">
                <?php
                $prev = date('Y-m', strtotime($current_month . '-01 -1 month'));
                $next = date('Y-m', strtotime($current_month . '-01 +1 month'));
                ?>
                <a href="?month=<?php echo $prev; ?>">◀ Previous Month</a>
                <a href="?month=<?php echo date('Y-m'); ?>">Current Month</a>
                <a href="?month=<?php echo $next; ?>">Next Month ▶</a>
            </div>

            <h3 style="margin: 20px 0; text-align:center;">Editing Chart for: <?php echo $month_name; ?></h3>

            <div class="table-responsive">
                <form method="POST" id="chartForm">
                    <table class="chart-table">
                        <thead>
                            <tr>
                                <th style="width:120px">Game Name</th>
                                <?php foreach ($date_list as $date_row): ?>
                                    <th><?php echo date('d-m', strtotime($date_row['result_date'])); ?></th>
                                <?php endforeach; ?>
                                <th style="width:100px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($games as $game):
                                $game_name = $game['game_name'];
                                ?>
                                <tr>
                                    <td class="game-col"><?php echo htmlspecialchars($game_name); ?></td>
                                    <?php foreach ($date_list as $date_row):
                                        $result_date = $date_row['result_date'];
                                        $stmt = $pdo->prepare("SELECT result_number, id FROM chart_data WHERE game_name = ? AND result_date = ?");
                                        $stmt->execute([$game_name, $result_date]);
                                        $data = $stmt->fetch();
                                        $result_number = $data ? $data['result_number'] : '';
                                        ?>
                                        <td>
                                            <input type="text"
                                                name="chart[<?php echo $game_name; ?>][<?php echo $result_date; ?>]"
                                                value="<?php echo htmlspecialchars($result_number); ?>"
                                                style="width:70px; text-align:center;">
                                        </td>
                                    <?php endforeach; ?>
                                    <td>
                                        <button type="submit" name="update_row"
                                            value="<?php echo htmlspecialchars($game_name); ?>" class="update-btn">Save
                                            Row</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>

        <!-- Add New Entry Form -->
<div class="card">
    <h3>➕ Add New Chart Entry</h3>
    <form method="POST" class="add-form">
        <select name="game_name" required>
            <option value="">Select Game</option>
            <?php foreach ($games as $game): ?>
                <option value="<?php echo htmlspecialchars($game['game_name']); ?>">
                    <?php echo htmlspecialchars($game['game_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="result_date" required>
        <input type="text" name="result_number" placeholder="Result Number" style="width:100px" required>
        <button type="submit" name="add_entry">Add Entry</button>
    </form>
</div>

<div class="card">
    <h3>💡 Instructions</h3>
    <ul style="margin-left:20px; line-height:1.8">
        <li>✅ Edit any number directly in the table above</li>
        <li>✅ Click "Save Row" to save changes for that game</li>
        <li>✅ Use month navigation to edit different months</li>
        <li>✅ View the live chart page at <a href="chart.php" target="_blank">chart.php</a></li>
    </ul>
</div>
</div>

<script>
    // Handle individual row updates via separate forms
    <?php
    // Process bulk update if update_row is set
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_row'])) {
        $game = $_POST['update_row'];
        $updates = $_POST['chart'][$game] ?? [];
        foreach ($updates as $date => $number) {
            $stmt = $pdo->prepare("INSERT INTO chart_data (game_name, result_date, result_number) VALUES (?, ?, ?) 
                                   ON DUPLICATE KEY UPDATE result_number = VALUES(result_number)");
            $stmt->execute([$game, $date, $number]);
        }
        echo "<script>alert('Saved! Refresh to see changes.');</script>";
    }
    ?>
</script>
</body>

</html> -->

<?php
require_once 'config.php';

if (!isAdminLoggedIn()) {
    header('Location: admin-login.php');
    exit();
}

// Handle chart updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_chart'])) {
        $stmt = $pdo->prepare("UPDATE chart_data SET result_number = ? WHERE game_name = ? AND result_date = ? AND chart_type = ?");
        $stmt->execute([$_POST['result_number'], $_POST['game_name'], $_POST['result_date'], $_POST['chart_type']]);
        $success = "Chart updated successfully!";
    }

    if (isset($_POST['add_chart'])) {
        $stmt = $pdo->prepare("INSERT INTO chart_data (game_name, result_date, result_number, chart_type) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['game_name'], $_POST['result_date'], $_POST['result_number'], $_POST['chart_type']]);
        $success = "Chart entry added!";
    }
}

// Get current month
$current_month = $_GET['month'] ?? date('Y-m');
$games = $pdo->query("SELECT DISTINCT game_name FROM chart_data ORDER BY game_name")->fetchAll();
$dates = $pdo->prepare("SELECT DISTINCT result_date FROM chart_data WHERE DATE_FORMAT(result_date, '%Y-%m') = ? ORDER BY result_date");
$dates->execute([$current_month]);
$date_list = $dates->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Charts - A1 Satta Live</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
        }

        .success {
            background: #d4edda;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        th {
            background: #ffd800;
        }

        input {
            width: 60px;
            padding: 5px;
            text-align: center;
        }

        .month-nav {
            margin-bottom: 20px;
        }

        .month-nav a {
            background: #ffd800;
            padding: 10px 20px;
            text-decoration: none;
            color: #000;
            border-radius: 30px;
            margin: 0 5px;
        }

        button {
            background: #28a745;
            color: white;
            padding: 5px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .back-btn {
            background: #6c757d;
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="admin-dashboard.php" class="back-btn">← Back to Dashboard</a>
        <div class="card">
            <h2>📈 Chart Data Editor</h2>
            <?php if (isset($success)): ?>
                <div class="success">✅
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <div class="month-nav">
                <?php
                $prev = date('Y-m', strtotime($current_month . '-01 -1 month'));
                $next = date('Y-m', strtotime($current_month . '-01 +1 month'));
                ?>
                <a href="?month=<?php echo $prev; ?>">◀ Previous</a>
                <a href="?month=<?php echo date('Y-m'); ?>">Current Month</a>
                <a href="?month=<?php echo $next; ?>">Next ▶</a>
            </div>

            <form method="POST">
                <table>
                    <thead>
                        <tr>
                            <th>Game Name</th>
                            <?php foreach ($date_list as $date): ?>
                                <th>
                                    <?php echo date('d-m', strtotime($date['result_date'])); ?>
                                </th>
                            <?php endforeach; ?>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($games as $game): ?>
                            <tr>
                                <td><strong>
                                        <?php echo htmlspecialchars($game['game_name']); ?>
                                    </strong></td>
                                <?php foreach ($date_list as $date):
                                    $stmt = $pdo->prepare("SELECT result_number FROM chart_data WHERE game_name = ? AND result_date = ?");
                                    $stmt->execute([$game['game_name'], $date['result_date']]);
                                    $result = $stmt->fetch();
                                    ?>
                                    <td>
                                        <input type="text"
                                            name="chart[<?php echo $game['game_name']; ?>][<?php echo $date['result_date']; ?>]"
                                            value="<?php echo htmlspecialchars($result['result_number'] ?? '-'); ?>">
                                    </td>
                                <?php endforeach; ?>
                                <td>
                                    <button type="submit" name="update_row"
                                        value="<?php echo htmlspecialchars($game['game_name']); ?>">Save Row</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
        </div>
    </div>
</body>

</html>