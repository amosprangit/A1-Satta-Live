<?php
// First, include config.php which has the isAdminLoggedIn() function
if (!isset($pdo) && file_exists('config.php')) {
    require_once 'config.php';
}

// Make sure the function exists, if not define it here
if (!function_exists('isAdminLoggedIn')) {
    function isAdminLoggedIn()
    {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }
}
?>
<!DOCTYPE html>
<html lang="en-IN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="description"
        content="A1 satta live - Trusted platform for Delhi Bazar Satta King results, Shree Ganesh Satta King 2026 charts">
    <meta name="keywords" content="a1 satta live, a1sattalive, delhi bazar satta king, shri ganesh satta">
    <meta name="author" content="A1 satta live">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#ffd700">
    <title><?php echo isset($page_title) ? $page_title : 'A1 satta live | Delhi Bazar Satta King 2026 Results'; ?>
    </title>
    <link rel="stylesheet" href="./css/header.css">
</head>
<body>
    <!-- Fixed Refresh Button -->
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="refresh-btn">🔄 Refresh</a>
    <!-- Navigation Bar -->
    <div class="navbar">
        <a href="index.php"
            class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">HOME</a>
        <a href="chart.php"
            class="<?php echo basename($_SERVER['PHP_SELF']) == 'chart.php' ? 'active' : ''; ?>">CHART</a>
        <a href="contact.php"
            class="<?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">CONTACT</a>
        <a href="admin-login.php">LOGIN</a>
    </div>
    <!-- Scrolling Marquee -->
    <div class="marquee">
        <span>A1 satta live is an informational satta matka website which publishes all A1 satta live games results
            which includes Delhi bazar, sadar bazar, a1 satta charts, dwarka satta, gali satta king 2026 and faridabad
            satta king 2026 and also ghaziabad 2026 satta charts. Official A1 satta live url is a1sattalive.com</span>
    </div>
    <!-- Logo -->
    <div class="logo">
        <h1>A1 SATTA LIVE</h1>
    </div>

    <!-- Live Clock Script (global) -->
    <script>
        function updateClock() {
            const now = new Date();
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            const date = now.toLocaleDateString('en-IN', options);
            let hours = now.getHours();
            let minutes = now.getMinutes();
            let seconds = now.getSeconds();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12 || 12;
            const time = hours.toString().padStart(2, '0') + ':' +
                minutes.toString().padStart(2, '0') + ':' +
                seconds.toString().padStart(2, '0') + ' ' + ampm;
            const clockElem = document.getElementById('clock');
            if (clockElem) clockElem.innerHTML = date + " | " + time;
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>