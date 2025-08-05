<?php
session_start();
require_once '../includes/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get dashboard statistics
$events_query = "SELECT COUNT(*) as total_events FROM events";
$events_result = mysqli_query($conn, $events_query);
$total_events = mysqli_fetch_assoc($events_result)['total_events'];

$attendees_query = "SELECT COUNT(*) as total_attendees FROM attendees";
$attendees_result = mysqli_query($conn, $attendees_query);
$total_attendees = mysqli_fetch_assoc($attendees_result)['total_attendees'];

$vendors_query = "SELECT COUNT(*) as total_vendors FROM vendors";
$vendors_result = mysqli_query($conn, $vendors_query);
$total_vendors = mysqli_fetch_assoc($vendors_result)['total_vendors'];

$revenue_query = "SELECT SUM(amount) as total_revenue FROM payments WHERE status = 'completed'";
$revenue_result = mysqli_query($conn, $revenue_query);
$total_revenue = mysqli_fetch_assoc($revenue_result)['total_revenue'] ?? 0;

// Get recent events
$recent_events_query = "SELECT * FROM events ORDER BY created_date DESC LIMIT 5";
$recent_events = mysqli_query($conn, $recent_events_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Event Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/navigation.php'; ?>
    
    <div class="container">
        <div class="dashboard-header">
            <h1>Admin Dashboard</h1>
            <p>Welcome back! Here's an overview of your event management system.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-info">
                    <h3><?php echo $total_events; ?></h3>
                    <p>Total Events</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <h3><?php echo $total_attendees; ?></h3>
                    <p>Total Attendees</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🏢</div>
                <div class="stat-info">
                    <h3><?php echo $total_vendors; ?></h3>
                    <p>Total Vendors</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <h3>$<?php echo number_format($total_revenue, 2); ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>
        </div>

        <div class="dashboard-content">
            <div class="recent-events">
                <h2>Recent Events</h2>
                <div class="events-list">
                    <?php while ($event = mysqli_fetch_assoc($recent_events)): ?>
                    <div class="event-card">
                        <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                        <p><?php echo htmlspecialchars($event['description']); ?></p>
                        <div class="event-meta">
                            <span>📅 <?php echo date('M d, Y', strtotime($event['start_date'])); ?></span>
                            <span>📍 <?php echo htmlspecialchars($event['location']); ?></span>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-buttons">
                    <a href="../events/create.php" class="btn btn-primary">Create New Event</a>
                    <a href="../attendees/register.php" class="btn btn-secondary">Register Attendee</a>
                    <a href="../vendors/add.php" class="btn btn-accent">Add Vendor</a>
                    <a href="../reports/index.php" class="btn btn-warning">View Reports</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>