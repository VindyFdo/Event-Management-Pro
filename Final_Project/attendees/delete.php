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

// Get attendee details for logging
$attendee_query = "SELECT name FROM attendees WHERE id = $attendee_id";
$attendee_result = mysqli_query($conn, $attendee_query);

if (mysqli_num_rows($attendee_result) == 0) {
    header('Location: index.php');
    exit();
}

$attendee = mysqli_fetch_assoc($attendee_result);

// Delete attendee
$delete_query = "DELETE FROM attendees WHERE id = $attendee_id";

if (mysqli_query($conn, $delete_query)) {
    log_activity($_SESSION['user_id'], 'DELETE_ATTENDEE', "Deleted attendee: " . $attendee['name']);
    header('Location: index.php?deleted=1');
} else {
    log_error('Attendee deletion failed: ' . mysqli_error($conn));
    header('Location: view.php?id=' . $attendee_id . '&error=delete_failed');
}
exit();
?>