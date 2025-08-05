<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$payment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$status = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';

if ($payment_id <= 0 || empty($status)) {
    header('Location: index.php');
    exit();
}

// Validate status
$valid_statuses = ['pending', 'completed', 'failed', 'refunded'];
if (!in_array($status, $valid_statuses)) {
    header('Location: index.php');
    exit();
}

// Get payment details for logging
$payment_query = "SELECT p.amount, a.name as attendee_name 
                  FROM payments p 
                  JOIN attendees a ON p.attendee_id = a.id 
                  WHERE p.id = $payment_id";
$payment_result = mysqli_query($conn, $payment_query);

if (mysqli_num_rows($payment_result) == 0) {
    header('Location: index.php');
    exit();
}

$payment = mysqli_fetch_assoc($payment_result);

// Update payment status
$update_query = "UPDATE payments SET status = '$status' WHERE id = $payment_id";

if (mysqli_query($conn, $update_query)) {
    log_activity($_SESSION['user_id'], 'UPDATE_PAYMENT_STATUS', "Updated payment status to $status for " . $payment['attendee_name'] . " (Amount: $" . $payment['amount'] . ")");
    header('Location: view.php?id=' . $payment_id . '&success=status_updated');
} else {
    log_error('Payment status update failed: ' . mysqli_error($conn));
    header('Location: view.php?id=' . $payment_id . '&error=update_failed');
}
exit();
?>