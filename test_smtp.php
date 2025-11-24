<?php
// test_smtp.php
require_once 'config.php';
require __DIR__ . '/vendor/PHPMailer/src/Exception.php';
require __DIR__ . '/vendor/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/vendor/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "<h1>SMTP Connection Test</h1>";
echo "<p>Testing connection to <strong>" . SMTP_HOST . "</strong> on port <strong>" . SMTP_PORT . "</strong>...</p>";

$mail = new PHPMailer(true);
$debug_output = '';
$mail->Debugoutput = function($str, $level) use (&$debug_output) {
    $debug_output .= "$str\n";
};

try {
    $mail->isSMTP();
    $mail->SMTPDebug = 2;
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = SMTP_PORT;

    $mail->setFrom(SMTP_USERNAME, 'Test Script');
    $mail->addAddress(SMTP_FROM_EMAIL);
    
    $mail->Subject = 'SMTP Test';
    $mail->Body    = 'This is a test email to verify your SMTP settings.';

    $mail->send();
    echo "<h2 style='color: green;'>Success! Email sent.</h2>";
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Error: " . $e->getMessage() . "</h2>";
    echo "<h3>Debug Output:</h3>";
    echo "<pre>" . htmlspecialchars($debug_output) . "</pre>";
    
    if (strpos($debug_output, 'Username and Password not accepted') !== false) {
        echo "<div style='background: #ffebee; padding: 10px; border: 1px solid red;'>";
        echo "<strong>POSSIBLE CAUSE:</strong> You might be using your regular Gmail password.<br>";
        echo "Google requires an <strong>App Password</strong> for this to work.<br>";
        echo "1. Go to <a href='https://myaccount.google.com/security'>Google Security</a>.<br>";
        echo "2. Enable 2-Step Verification.<br>";
        echo "3. Search for 'App passwords' and generate one.<br>";
        echo "4. Update <code>config.php</code> with that 16-character code.";
        echo "</div>";
    }
}
?>
