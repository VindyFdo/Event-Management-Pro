<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '2001';
$database = 'event_management';

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8
mysqli_set_charset($conn, "utf8");

// Function to sanitize input
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

// Function to validate email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to validate date
function validate_date($date, $format = 'Y-m-d H:i:s') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Function to generate random string
function generate_random_string($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

// Function to log errors
function log_error($message) {
    $log = date('Y-m-d H:i:s') . " - " . $message . PHP_EOL;
    file_put_contents('logs/error.log', $log, FILE_APPEND | LOCK_EX);
}

// Function to log activities
function log_activity($user_id, $action, $details = '') {
    global $conn;
    $user_id = (int)$user_id;
    $action = sanitize_input($action);
    $details = sanitize_input($details);
    $timestamp = date('Y-m-d H:i:s');
    
    $query = "INSERT INTO activity_logs (user_id, action, details, timestamp) VALUES ('$user_id', '$action', '$details', '$timestamp')";
    mysqli_query($conn, $query);
}
?>