<?php
// contact.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer Manually (NO Composer)
require __DIR__ . '/vendor/PHPMailer/src/Exception.php';
require __DIR__ . '/vendor/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/vendor/PHPMailer/src/SMTP.php';

// Load Configuration
require_once __DIR__ . '/config.php';

// Initialize variables
$name = $company = $email = $message = '';
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize inputs
    $name = filter_input(INPUT_POST, 'Name', FILTER_SANITIZE_STRING);
    $company = filter_input(INPUT_POST, 'Company', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'Email', FILTER_SANITIZE_EMAIL);
    $message = filter_input(INPUT_POST, 'Message', FILTER_SANITIZE_STRING);

    // Validation
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email is required.';
    }
    if (empty($message)) {
        $errors[] = 'Message is required.';
    }

    // Save to Database
    $db_saved = false;
    if (empty($errors)) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            $errors[] = "Database connection failed: " . $conn->connect_error;
        } else {
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, company, email, message) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("ssss", $name, $company, $email, $message);
                if ($stmt->execute()) {
                    $db_saved = true;
                } else {
                    $errors[] = "Failed to save message: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $errors[] = "Database error: " . $conn->error;
            }
            $conn->close();
        }
    }

    // Send email
    if (empty($errors) || $db_saved) { // Attempt email if no errors OR if DB save was successful (even if we want to warn about email later, actually let's keep it simple: if DB failed, don't email. If DB success, try email.)
        // Actually, if DB saved, we want to try email.
        // If DB failed, we already have errors, so we stop.
    }
    
    if (empty($errors)) {
        $mail = new PHPMailer(true);
        $debug_output = '';
        $mail->Debugoutput = function($str, $level) use (&$debug_output) {
            $debug_output .= "$str\n";
        };

        try {
            // SMTP setup
            $mail->isSMTP();
            $mail->SMTPDebug = 2; 
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;

            // Email headers
            $mail->setFrom($email, $name);
            $mail->addAddress(SMTP_FROM_EMAIL);
            $mail->addReplyTo($email, $name);

            // Content
            $mail->isHTML(false);
            $mail->Subject = 'Contact Form Submission from ' . $name;
            $mail->Body  = "Name: $name\n";
            $mail->Body .= "Company: " . ($company ?: 'Not provided') . "\n";
            $mail->Body .= "Email: $email\n";
            $mail->Body .= "Message:\n$message";
            if ($db_saved) {
                $mail->Body .= "\n\n(Saved to Database: Yes)";
            }

            // Send
            $mail->send();
            $success = 'Thank you! Your message has been sent successfully.';
            $name = $company = $email = $message = '';

        } catch (\PHPMailer\PHPMailer\Exception $e) {
            if ($db_saved) {
                $success = 'Your message was saved to the database, but we could not send the email notification.';
                
                // Check for authentication error
                if (strpos($e->getMessage(), 'Username and Password not accepted') !== false) {
                     $errors[] = "<strong>Email Sending Failed: Authentication Error.</strong><br>
                     It looks like you are using your regular Gmail password.<br>
                     Google requires an <strong>App Password</strong> for security.<br>
                     Please generate one at <a href='https://myaccount.google.com/apppasswords' target='_blank'>myaccount.google.com/apppasswords</a> and update <code>config.php</code>.";
                } else {
                    $errors[] = 'Email Error: ' . $e->getMessage();
                    $errors[] = 'SMTP Debug: <pre>' . htmlspecialchars($debug_output) . '</pre>';
                }
            } else {
                if (strpos($e->getMessage(), 'Username and Password not accepted') !== false) {
                     $errors[] = "<strong>Authentication Error.</strong><br>
                     It looks like you are using your regular Gmail password.<br>
                     Google requires an <strong>App Password</strong> for security.<br>
                     Please generate one at <a href='https://myaccount.google.com/apppasswords' target='_blank'>myaccount.google.com/apppasswords</a> and update <code>config.php</code>.";
                } else {
                    $errors[] = 'Failed to send message: ' . $e->getMessage();
                    $errors[] = 'SMTP Debug: <pre>' . htmlspecialchars($debug_output) . '</pre>';
                }
            }
            error_log('PHPMailer error: ' . $e->getMessage(), 3, 'error.log');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form Submission</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <section id="contact" class="active">
        <h2>Contact</h2>
        <div class="contact-box">
            <p>If you are an HR or recruiter, feel free to reach out to me directly.</p>

            <?php if (!empty($errors)): ?>
                <div class="error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success">
                    <p><?php echo htmlspecialchars($success); ?></p>
                </div>
            <?php endif; ?>

            <form class="contact-form" action="contact.php" method="post">
                <input type="text" name="Name" placeholder="Your Name" value="<?php echo htmlspecialchars($name); ?>" required>
                <input type="text" name="Company" placeholder="Company Name (Optional)" value="<?php echo htmlspecialchars($company); ?>">
                <input type="email" name="Email" placeholder="Your Email" value="<?php echo htmlspecialchars($email); ?>" required>
                <textarea name="Message" placeholder="Your Message" required><?php echo htmlspecialchars($message); ?></textarea>
                <button type="submit">Send Message</button>
            </form>

            <div class="direct-contact">
                <p><strong>Email:</strong> <a href="mailto:naaveensaaran.01319@gmail.com">naaveensaaran.01319@gmail.com</a></p>
                <p><strong>Phone:</strong> +91 90875 90967</p>
                <p><strong>LinkedIn:</strong> <a href="https://www.linkedin.com/in/naveen-s-4813ab291/">linkedin.com/in/naveen-s-4813ab291</a></p>
            </div>
        </div>
    </section>
</body>
</html>
