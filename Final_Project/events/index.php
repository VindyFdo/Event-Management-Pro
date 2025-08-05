<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Handle search
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search_condition = "WHERE title LIKE '%$search%' OR description LIKE '%$search%' OR location LIKE '%$search%'";
}

// Get all events
$events_query = "SELECT * FROM events $search_condition ORDER BY start_date ASC";
$events_result = mysqli_query($conn, $events_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - Event Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/navigation.php'; ?>
    
    <div class="container">
        <div class="dashboard-header">
            <h1>Events Management</h1>
            <p>Create, manage, and monitor all your events</p>
        </div>

        <div class="card">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <h2 class="card-title">All Events</h2>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <form method="GET" style="display: flex; gap: 0.5rem;">
                            <input type="text" name="search" placeholder="Search events..." class="form-control" style="width: auto;" value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-secondary">Search</button>
                        </form>
                        <a href="create.php" class="btn btn-primary">Create New Event</a>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Date & Time</th>
                            <th>Location</th>
                            <th>Capacity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($events_result) > 0): ?>
                            <?php while ($event = mysqli_fetch_assoc($events_result)): ?>
                                <?php
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
                                
                                // Get attendee count
                                $attendee_count_query = "SELECT COUNT(*) as count FROM attendees WHERE event_id = " . $event['id'];
                                $attendee_count_result = mysqli_query($conn, $attendee_count_query);
                                $attendee_count = mysqli_fetch_assoc($attendee_count_result)['count'];
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                        <br>
                                        <small style="color: var(--dark-gray);"><?php echo htmlspecialchars(substr($event['description'], 0, 100)); ?>...</small>
                                    </td>
                                    <td>
                                        <strong><?php echo date('M d, Y', strtotime($event['start_date'])); ?></strong>
                                        <br>
                                        <small><?php echo date('g:i A', strtotime($event['start_date'])); ?> - <?php echo date('g:i A', strtotime($event['end_date'])); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($event['location']); ?></td>
                                    <td>
                                        <span style="color: <?php echo $attendee_count >= $event['capacity'] ? 'var(--error)' : 'var(--success)'; ?>">
                                            <?php echo $attendee_count; ?> / <?php echo $event['capacity']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="background: <?php echo $status_color; ?>; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="view.php?id=<?php echo $event['id']; ?>" class="btn" style="background: var(--info); color: white; padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-right: 0.25rem;">View</a>
                                        <a href="edit.php?id=<?php echo $event['id']; ?>" class="btn" style="background: var(--warning); color: var(--primary); padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-right: 0.25rem;">Edit</a>
                                        <a href="delete.php?id=<?php echo $event['id']; ?>" class="btn" style="background: var(--error); color: white; padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="return confirm('Are you sure you want to delete this event?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 3rem; color: var(--dark-gray);">
                                    <?php if (!empty($search)): ?>
                                        No events found matching "<?php echo htmlspecialchars($search); ?>"
                                    <?php else: ?>
                                        No events found. <a href="create.php">Create your first event</a>
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