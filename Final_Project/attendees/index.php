<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Handle search
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$event_filter = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

$search_condition = '';
$params = [];

if (!empty($search)) {
    $search_condition .= "AND (a.name LIKE '%$search%' OR a.email LIKE '%$search%')";
}

if ($event_filter > 0) {
    $search_condition .= " AND a.event_id = $event_filter";
}

// Get all attendees with event information
$attendees_query = "SELECT a.*, e.title as event_title, e.start_date 
                   FROM attendees a 
                   JOIN events e ON a.event_id = e.id 
                   WHERE 1=1 $search_condition 
                   ORDER BY a.registration_date DESC";
$attendees_result = mysqli_query($conn, $attendees_query);

// Get events for filter dropdown
$events_query = "SELECT id, title FROM events ORDER BY start_date DESC";
$events_result = mysqli_query($conn, $events_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendees - Event Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/navigation.php'; ?>
    
    <div class="container">
        <div class="dashboard-header">
            <h1>Attendee Management</h1>
            <p>Manage event registrations and attendee information</p>
        </div>

        <div class="card">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <h2 class="card-title">All Attendees</h2>
                    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <form method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <input type="text" name="search" placeholder="Search attendees..." class="form-control" style="width: auto;" value="<?php echo htmlspecialchars($search); ?>">
                            <select name="event_id" class="form-control" style="width: auto;">
                                <option value="">All Events</option>
                                <?php 
                                mysqli_data_seek($events_result, 0);
                                while ($event = mysqli_fetch_assoc($events_result)): 
                                ?>
                                    <option value="<?php echo $event['id']; ?>" <?php echo $event_filter == $event['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($event['title']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <button type="submit" class="btn btn-secondary">Filter</button>
                        </form>
                        <a href="register.php" class="btn btn-primary">Register New Attendee</a>
                        <a href="bulk_email.php" class="btn btn-accent">Send Bulk Email</a>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Event</th>
                            <th>Registration Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($attendees_result) > 0): ?>
                            <?php while ($attendee = mysqli_fetch_assoc($attendees_result)): ?>
                                <?php
                                $status_colors = [
                                    'registered' => 'var(--info)',
                                    'attended' => 'var(--success)',
                                    'cancelled' => 'var(--error)'
                                ];
                                $status_color = $status_colors[$attendee['status']] ?? 'var(--dark-gray)';
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($attendee['name']); ?></strong>
                                        <?php if ($attendee['phone']): ?>
                                            <br><small style="color: var(--dark-gray);"><?php echo htmlspecialchars($attendee['phone']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($attendee['email']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($attendee['event_title']); ?></strong>
                                        <br><small style="color: var(--dark-gray);"><?php echo date('M d, Y', strtotime($attendee['start_date'])); ?></small>
                                    </td>
                                    <td><?php echo date('M d, Y g:i A', strtotime($attendee['registration_date'])); ?></td>
                                    <td>
                                        <span style="background: <?php echo $status_color; ?>; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
                                            <?php echo ucfirst($attendee['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="view.php?id=<?php echo $attendee['id']; ?>" class="btn" style="background: var(--info); color: white; padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-right: 0.25rem;">View</a>
                                        <a href="edit.php?id=<?php echo $attendee['id']; ?>" class="btn" style="background: var(--warning); color: var(--primary); padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-right: 0.25rem;">Edit</a>
                                        <?php if ($attendee['status'] == 'registered'): ?>
                                            <a href="mark_attended.php?id=<?php echo $attendee['id']; ?>" class="btn" style="background: var(--success); color: white; padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-right: 0.25rem;">Mark Attended</a>
                                        <?php endif; ?>
                                        <a href="delete.php?id=<?php echo $attendee['id']; ?>" class="btn" style="background: var(--error); color: white; padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="return confirm('Are you sure you want to delete this attendee?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 3rem; color: var(--dark-gray);">
                                    <?php if (!empty($search) || $event_filter > 0): ?>
                                        No attendees found matching your criteria
                                    <?php else: ?>
                                        No attendees found. <a href="register.php">Register the first attendee</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>