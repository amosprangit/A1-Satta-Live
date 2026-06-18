<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'A1 SATTA LIVE | Contact Us - Get Support';

// Try to include config and header, but don't break if they fail
try {
    if (file_exists('config.php')) {
        require_once 'config.php';
    }
    if (file_exists('header.php')) {
        require_once 'header.php';
    }
} catch (Exception $e) {
    // Continue even if includes fail
}

// Hardcoded values - guaranteed to work
$contact_email = 'dostmera643@gmail.com';
$contact_message = 'We aim to reply within 24 hours. For business, advertising, or chart corrections, feel free to reach out.';
$contact_notice = 'A1 Satta Live is dedicated to providing accurate satta king results and charts. Your feedback matters.';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #1a1a1a, #2d2d2d);
            color: #fff;
            min-height: 100vh;
        }

        .contact-main-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 10px;
        }

        .contact-card {
            background: rgba(255, 252, 240, 0.95);
            border-radius: 8px;
            max-width: 780px;
            margin: 40px auto;
            padding: 48px 28px;
            box-shadow: 0 25px 45px -12px rgba(0, 0, 0, 0.2);
            border: 1px solid #ffdfaa;
            text-align: center;
            animation: fadeUp 0.5s ease;
        }

        .contact-card h2 {
            margin-bottom: 16px;
            color: #2a2416;
            font-size: clamp(1.5rem, 4vw, 2rem);
        }

        .contact-email {
            font-size: clamp(1.2rem, 5vw, 2rem);
            background: linear-gradient(125deg, #2a2416, #3c2e18);
            display: inline-block;
            padding: 14px 32px;
            border-radius: 80px;
            word-break: break-all;
        }

        .contact-email a {
            color: #ffeaac;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }

        .contact-email a:hover {
            color: #ffd700;
            letter-spacing: 1px;
        }

        .contact-card p {
            margin-top: 28px;
            color: #4a3a1a;
            line-height: 1.6;
        }

        .contact-notice {
            background: #fffaf0;
            border-radius: 8px;
            margin: 30px auto;
            max-width: 960px;
            padding: 20px 14px;
            text-align: center;
            border: 1px solid #ffe1aa;
        }

        .contact-notice p {
            font-size: 16px;
            color: #5a4a2a;
        }

        .contact-notice strong {
            color: #c49a00;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .contact-social-cards {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            margin: 30px auto;
            max-width: 780px;
        }

        .social-card {
            background: #ffd800;
            margin: 0;
            padding: 20px;
            border-radius: 20px;
            text-align: center;
            border: 2px dashed red;
            font-weight: bold;
            flex: 1;
            min-width: 200px;
            color: #000;
        }

        .social-card img {
            width: 48px;
            margin-top: 10px;
        }

        .social-card a {
            text-decoration: none;
            color: #000;
        }

        .footer {
            background: #000;
            text-align: center;
            padding: 30px;
            border-top: 1px solid #333;
            margin-top: 40px;
        }

        .footer a {
            color: #ffd700;
            text-decoration: none;
            margin: 0 20px;
            font-size: 16px;
        }

        .footer p {
            color: #666;
            margin-top: 20px;
            font-size: 14px;
        }

        .disclaimer {
            background: #111;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #888;
        }

        @media (max-width: 640px) {
            .contact-card {
                padding: 32px 18px;
                margin: 24px 16px;
            }

            .contact-email {
                font-size: 1rem;
                padding: 10px 20px;
            }

            .social-card {
                min-width: 150px;
                padding: 15px;
            }

            .footer a {
                margin: 0 10px;
                font-size: 12px;
            }
        }
    </style>
</head>

<body>

    <div class="contact-main-container">
        <!-- Contact Card -->
        <div class="contact-card">
            <h2>📧 For any enquiry, message us at</h2>
            <div class="contact-email">
                <a href="mailto:<?php echo htmlspecialchars($contact_email); ?>">
                    <?php echo htmlspecialchars($contact_email); ?>
                </a>
            </div>
            <p>📌 <?php echo htmlspecialchars($contact_message); ?></p>
        </div>

        <!-- Notice Card -->
        <div class="contact-notice">
            <p>⭐ <strong>A1 Satta Live</strong> — <?php echo htmlspecialchars($contact_notice); ?></p>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <a href="/privacy-policy">Privacy Policy</a>
        <a href="/terms-and-conditions">Terms & Conditions</a>
        <a href="/disclaimer">Disclaimer</a>
        <p>© 2026 A1 satta live | All Rights Reserved</p>
    </div>

    <div class="disclaimer">
        !! DISCLAIMER - A1 satta live is a non-commercial informational website. Please view this site at your own risk,
        All The Information Shown On Website Is Sponsored And We Warn You That satta matka Gambling/Satta May Be Banned
        Or Illegal In Your Country. We Are Not Responsible For Any Issues Or Scam..., We Respect All Country
        Rules/Laws... If You Not Agree With Our Site disclaimer Please Quit Our Site Right Now. Thank You.
    </div>

</body>

</html>