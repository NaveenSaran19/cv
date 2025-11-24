<?php
// config.php

// SMTP Configuration
// 1. Login to Gmail -> Manage Google Account -> Security -> 2-Step Verification (Enable it)
// 2. Search "App passwords" -> Create new -> Copy the 16-character code
// 3. Paste your EMAIL and that 16-CHAR CODE below.

define('SMTP_HOST', 'smtp.gmail.com');

// REPLACE 'your_email@gmail.com' WITH YOUR ACTUAL GMAIL ADDRESS
define('SMTP_USERNAME', 'naaveensaaran.01319@gmail.com'); 

// REPLACE 'your_app_password' WITH THE 16-CHARACTER APP PASSWORD (e.g., "abcd efgh ijkl mnop")
define('SMTP_PASSWORD', '');    

define('SMTP_PORT', 587);
define('SMTP_FROM_EMAIL', 'naaveensaaran.01319@gmail.com');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'resume_db');
