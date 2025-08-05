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

// Get event details
$event_query = "SELECT e.*, u.full_name as created_by_name 
                FROM events e 
                JOIN users u ON e.created_by = u.id 
                WHERE e.id = $event_id";
$event_result = mysqli_query($conn, $event_query);

if (mysqli_num_rows($event_result) == 0) {
    header('Location: index.php');
    exit();
}

$event = mysqli_fetch_assoc($event_result);

// Get attendee count
$attendee_count_query = "SELECT COUNT(*) as count FROM attendees WHERE event_id = $event_id";
$attendee_count_result = mysqli_query($conn, $attendee_count_query);
$attendee_count = mysqli_fetch_assoc($attendee_count_result)['count'];

// Get recent attendees
$recent_attendees_query = "SELECT * FROM attendees WHERE event_id = $event_id ORDER BY registration_date DESC LIMIT 10";
$recent_attendees = mysqli_query($conn, $recent_attendees_query);

// Get event status
$now = new DateTime();
$start_date = new DateTime($event['start_date']);
$end_date = new DateTime($event['end_date']);

$status = 'upcoming';
$status_color = 'var(--info)';

if ($now > $end_date) {
    $status = 'completed';
    $status_color = 'var(--dark-gray)';
} elseif ($now >= $start_date && $now <= $end_date) {
    $status = 'ongoing';
    $status_color = 'var(--success)';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['title']); ?> - Event Management System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/events.css">
</head>
<body>
    <?php include '../includes/navigation.php'; ?>
    
    <div class="container">
        <div class="event-header">
            <div class="event-title-section">
                <h1><?php echo htmlspecialchars($event['title']); ?></h1>
                <span class="event-status" style="background: <?php echo $status_color; ?>;">
                    <?php echo ucfirst($status); ?>
                </span>
            </div>
            <div class="event-actions">
                <a href="edit.php?id=<?php echo $event['id']; ?>" class="btn btn-warning">Edit Event</a>
                <a href="index.php" class="btn btn-secondary">Back to Events</a>
            </div>
        </div>

        <div class="event-details-grid">
            <div class="event-main-info">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Event Details</h2>
                    </div>
                    <div class="event-info">
                        <div class="info-item">
                            <strong>📝 Description:</strong>
                            <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                        </div>
                        
                        <div class="info-item">
                            <strong>📅 Start Date & Time:</strong>
                            <p><?php echo date('F d, Y g:i A', strtotime($event['start_date'])); ?></p>
                        </div>
                        
                        <div class="info-item">
                            <strong>🏁 End Date & Time:</strong>
                            <p><?php echo date('F d, Y g:i A', strtotime($event['end_date'])); ?></p>
                        </div>
                        
                        <div class="info-item">
                            <strong>📍 Location:</strong>
                            <p><?php echo htmlspecialchars($event['location']); ?></p>
                        </div>
                        
                        <div class="info-item">
                            <strong>👥 Capacity:</strong>
                            <p><?php echo $event['capacity']; ?> attendees</p>
                        </div>
                        
                        <div class="info-item">
                            <strong>👤 Created By:</strong>
                            <p><?php echo htmlspecialchars($event['created_by_name']); ?></p>
                        </div>
                        
                        <div class="info-item">
                            <strong>📅 Created Date:</strong>
                            <p><?php echo date('F d, Y g:i A', strtotime($event['created_date'])); ?></p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Recent Attendees</h2>
                        <a href="../attendees/index.php?event_id=<?php echo $event['id']; ?>" class="btn btn-primary">View All</a>
                    </div>
                    <div class="attendees-list">
                        <?php if (mysqli_num_rows($recent_attendees) > 0): ?>
                            <?php while ($attendee = mysqli_fetch_assoc($recent_attendees)): ?>
                                <div class="attendee-item">
                                    <div class="attendee-info">
                                        <strong><?php echo htmlspecialchars($attendee['name']); ?></strong>
                                        <small><?php echo htmlspecialchars($attendee['email']); ?></small>
                                    </div>
                                    <div class="attendee-status">
                                        <span class="status-badge status-<?php echo $attendee['status']; ?>">
                                            <?php echo ucfirst($attendee['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="text-align: center; color: var(--dark-gray); padding: 2rem;">
                                No attendees registered yet
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="event-sidebar">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Stats</h3>
                    </div>
                    <div class="stats-list">
                        <div class="stat-item">
                            <span class="stat-label">Registered:</span>
                            <span class="stat-value"><?php echo $attendee_count; ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Capacity:</span>
                            <span class="stat-value"><?php echo $event['capacity']; ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Available:</span>
                            <span class="stat-value"><?php echo max(0, $event['capacity'] - $attendee_count); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Fill Rate:</span>
                            <span class="stat-value">
                                <?php echo $event['capacity'] > 0 ? round(($attendee_count / $event['capacity']) * 100, 1) : 0; ?>%
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="action-buttons">
                        <a href="../attendees/register.php?event_id=<?php echo $event['id']; ?>" class="btn btn-primary">Register Attendee</a>
                        <a href="../attendees/bulk_email.php?event_id=<?php echo $event['id']; ?>" class="btn btn-accent">Send Email</a>
                        <a href="edit.php?id=<?php echo $event['id']; ?>" class="btn btn-warning">Edit Event</a>
                        <a href="delete.php?id=<?php echo $event['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this event?')">Delete Event</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>