<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting debug...\n";

$files = [
    __DIR__ . '/vendor/PHPMailer/src/Exception.php',
    __DIR__ . '/vendor/PHPMailer/src/PHPMailer.php',
    __DIR__ . '/vendor/PHPMailer/src/SMTP.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "Loading $file... ";
        require_once $file;
        echo "Done.\n";
    } else {
        echo "ERROR: File not found: $file\n";
    }
}

echo "Checking classes...\n";
if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    echo "PHPMailer class exists.\n";
} else {
    echo "PHPMailer class NOT found.\n";
    echo "Declared classes:\n";
    print_r(get_declared_classes());
}

try {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    echo "PHPMailer instantiated successfully.\n";
} catch (Exception $e) {
    echo "Error instantiating: " . $e->getMessage() . "\n";
}
?>
