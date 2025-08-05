<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($event_id <= 0) {
    header('Location: index.php');
    exit();
}

// Get event details for logging
$event_query = "SELECT title FROM events WHERE id = $event_id";
$event_result = mysqli_query($conn, $event_query);

if (mysqli_num_rows($event_result) == 0) {
    header('Location: index.php');
    exit();
}

$event = mysqli_fetch_assoc($event_result);

// Delete event (this will cascade delete attendees and payments due to foreign key constraints)
$delete_query = "DELETE FROM events WHERE id = $event_id";

if (mysqli_query($conn, $delete_query)) {
    log_activity($_SESSION['user_id'], 'DELETE_EVENT', "Deleted event: " . $event['title']);
    header('Location: index.php?deleted=1');
} else {
    log_error('Event deletion failed: ' . mysqli_error($conn));
    header('Location: view.php?id=' . $event_id . '&error=delete_failed');
}
exit();
?>