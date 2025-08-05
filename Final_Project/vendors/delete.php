<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$vendor_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($vendor_id <= 0) {
    header('Location: index.php');
    exit();
}

// Get vendor details for logging
$vendor_query = "SELECT name FROM vendors WHERE id = $vendor_id";
$vendor_result = mysqli_query($conn, $vendor_query);

if (mysqli_num_rows($vendor_result) == 0) {
    header('Location: index.php');
    exit();
}

$vendor = mysqli_fetch_assoc($vendor_result);

// Delete vendor
$delete_query = "DELETE FROM vendors WHERE id = $vendor_id";

if (mysqli_query($conn, $delete_query)) {
    log_activity($_SESSION['user_id'], 'DELETE_VENDOR', "Deleted vendor: " . $vendor['name']);
    header('Location: index.php?deleted=1');
} else {
    log_error('Vendor deletion failed: ' . mysqli_error($conn));
    header('Location: view.php?id=' . $vendor_id . '&error=delete_failed');
}
exit();
?>