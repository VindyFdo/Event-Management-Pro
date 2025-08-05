<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$type = isset($_GET['type']) ? sanitize_input($_GET['type']) : '';

if (empty($type)) {
    header('Location: index.php');
    exit();
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $type . '_export_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

switch ($type) {
    case 'events':
        // Export events
        fputcsv($output, ['ID', 'Title', 'Description', 'Start Date', 'End Date', 'Location', 'Capacity', 'Created Date']);
        
        $query = "SELECT * FROM events ORDER BY start_date DESC";
        $result = mysqli_query($conn, $query);
        
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, [
                $row['id'],
                $row['title'],
                $row['description'],
                $row['start_date'],
                $row['end_date'],
                $row['location'],
                $row['capacity'],
                $row['created_date']
            ]);
        }
        break;
        
    case 'attendees':
        // Export attendees
        fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'Event Title', 'Registration Date', 'Status']);
        
        $query = "SELECT a.*, e.title as event_title 
                  FROM attendees a 
                  JOIN events e ON a.event_id = e.id 
                  ORDER BY a.registration_date DESC";
        $result = mysqli_query($conn, $query);
        
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, [
                $row['id'],
                $row['name'],
                $row['email'],
                $row['phone'],
                $row['event_title'],
                $row['registration_date'],
                $row['status']
            ]);
        }
        break;
        
    case 'payments':
        // Export payments
        fputcsv($output, ['ID', 'Attendee Name', 'Event Title', 'Amount', 'Payment Method', 'Transaction ID', 'Payment Date', 'Status']);
        
        $query = "SELECT p.*, a.name as attendee_name, e.title as event_title 
                  FROM payments p 
                  JOIN attendees a ON p.attendee_id = a.id 
                  JOIN events e ON a.event_id = e.id 
                  ORDER BY p.payment_date DESC";
        $result = mysqli_query($conn, $query);
        
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, [
                $row['id'],
                $row['attendee_name'],
                $row['event_title'],
                $row['amount'],
                $row['payment_method'],
                $row['transaction_id'],
                $row['payment_date'],
                $row['status']
            ]);
        }
        break;
        
    case 'vendors':
        // Export vendors
        fputcsv($output, ['ID', 'Company Name', 'Contact Person', 'Email', 'Phone', 'Services', 'Rating', 'Status']);
        
        $query = "SELECT * FROM vendors ORDER BY name ASC";
        $result = mysqli_query($conn, $query);
        
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, [
                $row['id'],
                $row['name'],
                $row['contact_person'],
                $row['email'],
                $row['phone'],
                $row['services'],
                $row['rating'],
                $row['status']
            ]);
        }
        break;
        
    default:
        header('Location: index.php');
        exit();
}

fclose($output);
log_activity($_SESSION['user_id'], 'EXPORT_DATA', "Exported $type data");
exit();
?>