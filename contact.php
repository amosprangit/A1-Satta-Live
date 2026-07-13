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
    <link rel="stylesheet" href="./css/contact.css">
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