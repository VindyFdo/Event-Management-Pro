<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$attendee_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($attendee_id <= 0) {
    header('Location: index.php');
    exit();
}

// Get attendee details
$attendee_query = "SELECT name FROM attendees WHERE id = $attendee_id";
$attendee_result = mysqli_query($conn, $attendee_query);

if (mysqli_num_rows($attendee_result) == 0) {
    header('Location: index.php');
    exit();
}

$attendee = mysqli_fetch_assoc($attendee_result);

// Update status to attended
$update_query = "UPDATE attendees SET status = 'attended' WHERE id = $attendee_id";

if (mysqli_query($conn, $update_query)) {
    log_activity($_SESSION['user_id'], 'MARK_ATTENDED', "Marked attendee as attended: " . $attendee['name']);
    header('Location: view.php?id=' . $attendee_id . '&success=marked_attended');
} else {
    log_error('Mark attended failed: ' . mysqli_error($conn));
    header('Location: view.php?id=' . $attendee_id . '&error=mark_failed');
}
exit();
?>