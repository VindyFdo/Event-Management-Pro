<?php
session_start();
require_once 'includes/db_connection.php';

if (isset($_SESSION['user_id'])) {
    log_activity($_SESSION['user_id'], 'LOGOUT', 'User logged out');
}

session_destroy();
header('Location: login.php');
exit();
?>