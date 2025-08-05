<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$error = '';
$success = '';

// Get events for dropdown
$events_query = "SELECT id, title FROM events ORDER BY start_date DESC";
$events_result = mysqli_query($conn, $events_query);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = (int)$_POST['event_id'];
    $subject = sanitize_input($_POST['subject']);
    $message = sanitize_input($_POST['message']);
    $status_filter = sanitize_input($_POST['status_filter']);
    
    if (empty($subject) || empty($message) || $event_id <= 0) {
        $error = 'Please fill in all required fields';
    } else {
        // Get attendees based on filter
        $status_condition = '';
        if (!empty($status_filter)) {
            $status_condition = "AND status = '$status_filter'";
        }
        
        $attendees_query = "SELECT name, email FROM attendees WHERE event_id = $event_id $status_condition";
        $attendees_result = mysqli_query($conn, $attendees_query);
        
        if (mysqli_num_rows($attendees_result) > 0) {
            $email_count = 0;
            while ($attendee = mysqli_fetch_assoc($attendees_result)) {
                // In a real application, you would use a proper email service
                // For this practice system, we'll just log the email
                $personalized_message = str_replace('{name}', $attendee['name'], $message);
                
                // Insert into email queue (simulated)
                $queue_query = "INSERT INTO email_queue (recipient_email, subject, body, status) 
                               VALUES ('" . $attendee['email'] . "', '$subject', '$personalized_message', 'sent')";
                mysqli_query($conn, $queue_query);
                $email_count++;
            }
            
            log_activity($_SESSION['user_id'], 'BULK_EMAIL', "Sent bulk email to $email_count attendees for event ID: $event_id");
            $success = "Email sent to $email_count attendees successfully!";
        } else {
            $error = 'No attendees found for the selected criteria';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Email - Event Management System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/attendees.css">
</head>
<body>
    <?php include '../includes/navigation.php'; ?>
    
    <div class="container">
        <div class="dashboard-header">
            <h1>Send Bulk Email</h1>
            <p>Send emails to multiple attendees at once</p>
        </div>

        <div class="form-container">
            <?php if ($error): ?>
                <div class="error-message" style="background: var(--error); color: white; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message" style="margin-bottom: 1rem;">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="event_id">Select Event *</label>
                    <select id="event_id" name="event_id" class="form-control" required>
                        <option value="">Choose an event...</option>
                        <?php while ($event = mysqli_fetch_assoc($events_result)): ?>
                            <option value="<?php echo $event['id']; ?>" <?php echo $event_id == $event['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($event['title']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status_filter">Attendee Status Filter</label>
                    <select id="status_filter" name="status_filter" class="form-control">
                        <option value="">All Attendees</option>
                        <option value="registered">Registered Only</option>
                        <option value="attended">Attended Only</option>
                        <option value="cancelled">Cancelled Only</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="subject">Email Subject *</label>
                    <input type="text" id="subject" name="subject" class="form-control" value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="message">Email Message *</label>
                    <textarea id="message" name="message" class="form-control" rows="8" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    <small style="color: var(--dark-gray);">Use {name} to personalize with attendee names</small>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">Send Emails</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>