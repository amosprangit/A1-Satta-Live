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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        body {
            background: #000;
        }

        /* Navigation Bar */
        .navbar {
            background: #000;
            padding: 25px 20px;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .navbar a {
            background: #ffd700;
            color: #000;
            text-decoration: none;
            width: 200px;
            text-align: center;
            padding: 15px;
            border-radius: 40px;
            border: 2px solid #fff;
            font-size: 16px;
            font-weight: bold;
            transition: 0.3s;
        }

        .navbar a:hover,
        .navbar a.active {
            background: #e6c200;
            transform: scale(1.02);
        }

        /* Marquee */
        .marquee {
            background: #000;
            color: #ffd700;
            padding: 12px;
            font-size: 16px;
            font-weight: bold;
            overflow: hidden;
            white-space: nowrap;
            border-top: 1px solid #333;
            border-bottom: 1px solid #333;
        }

        .marquee span {
            display: inline-block;
            animation: scroll 20s linear infinite;
        }

        @keyframes scroll {
            from {
                transform: translateX(100%);
            }

            to {
                transform: translateX(-100%);
            }
        }

        /* Logo */
        .logo {
            background: #ffd700;
            text-align: center;
            padding: 20px;
        }

        .logo h1 {
            font-size: 40px;
            letter-spacing: 2px;
        }

        /* Live Box */
        .live-box {
            background: #000;
            color: #fff;
            text-align: center;
            padding: 25px 20px;
        }

        .clock {
            color: #ffd700;
            font-size: 22px;
            margin-bottom: 30px;
            font-family: monospace;
        }

        .live-box h2 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #ffd700;
        }

        .live-box h1 {
            font-size: 30px;
            margin-bottom: 20px;
            letter-spacing: 4px;
        }

        .result-number {
            font-size: 40px;
            font-weight: bold;
            background: #222;
            display: inline-block;
            padding: 20px 50px;
            border-radius: 20px;
            color: #ffd700;
            letter-spacing: 8px;
        }

        /* Tables */
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            background: #111;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        th {
            background: #000;
            color: #ffd700;
            padding: 20px;
            font-size: 24px;
            border: 1px solid #333;
        }

        td {
            border: 1px solid #ccc;
            text-align: center;
            padding: 20px;
            font-size: 24px;
            font-weight: bold;
        }

        .game {
            background: #ffd700;
            color: #000;
            width: 35%;
            text-align: left;
            padding-left: 30px;
        }

        .game a {
            color: #000;
            text-decoration: none;
        }

        .game small {
            font-size: 14px;
            font-weight: normal;
            display: block;
            color: #444;
        }

        .yesterday,
        .today {
            background: #f0f0f0;
        }

        .wait {
            color: #d32f2f;
            font-weight: bold;
            display: inline-block;
            background: #fff;
            padding: 5px 15px;
            border-radius: 20px;
        }

        /* Footer */
        .footer {
            background: #000;
            text-align: center;
            padding: 30px;
            border-top: 1px solid #333;
        }

        .footer a {
            color: #ffd700;
            text-decoration: none;
            margin: 0 15px;
            font-size: 14px;
        }

        .footer p {
            color: #666;
            margin-top: 20px;
            font-size: 12px;
        }

        .disclaimer {
            background: #111;
            padding: 20px;
            text-align: center;
            font-size: 11px;
            color: #888;
        }

        .refresh-btn {
            position: fixed;
            right: 20px;
            bottom: 20px;
            background: #ffd700;
            color: #000;
            border-radius: 50px;
            padding: 12px 24px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            border: none;
            z-index: 99;
            text-decoration: none;
            display: inline-block;
        }

        .admin-bar {
            background: #333;
            color: #ffd700;
            padding: 10px;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 100;
            font-size: 14px;
        }

        .admin-bar a {
            color: #ffd700;
            margin: 0 10px;
            text-decoration: none;
        }

        .admin-bar a:hover {
            text-decoration: underline;
        }

        @media(max-width:768px) {
            .logo h1 {
                font-size: 40px;
            }

            .result-number {
                font-size: 55px;
                padding: 15px 30px;
            }

            th {
                font-size: 16px;
                padding: 12px;
            }

            td {
                font-size: 18px;
                padding: 12px;
            }

            .navbar a {
                width: 160px;
                padding: 12px;
                font-size: 14px;
            }
        }
    </style>
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