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
$events_query = "SELECT id, title, start_date FROM events WHERE start_date > NOW() ORDER BY start_date ASC";
$events_result = mysqli_query($conn, $events_query);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = (int)$_POST['event_id'];
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $notes = sanitize_input($_POST['notes']);
    
    // Validation
    if (empty($name) || empty($email) || $event_id <= 0) {
        $error = 'Please fill in all required fields';
    } elseif (!validate_email($email)) {
        $error = 'Please enter a valid email address';
    } else {
        // Check if already registered
        $check_query = "SELECT id FROM attendees WHERE event_id = $event_id AND email = '$email'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = 'This email is already registered for this event';
        } else {
            // Check event capacity
            $capacity_query = "SELECT e.capacity, COUNT(a.id) as registered 
                              FROM events e 
                              LEFT JOIN attendees a ON e.id = a.event_id 
                              WHERE e.id = $event_id 
                              GROUP BY e.id";
            $capacity_result = mysqli_query($conn, $capacity_query);
            $capacity_data = mysqli_fetch_assoc($capacity_result);
            
            if ($capacity_data && $capacity_data['registered'] >= $capacity_data['capacity']) {
                $error = 'This event is at full capacity';
            } else {
                $registration_date = date('Y-m-d H:i:s');
                
                $insert_query = "INSERT INTO attendees (event_id, name, email, phone, notes, registration_date, status) 
                                VALUES ($event_id, '$name', '$email', '$phone', '$notes', '$registration_date', 'registered')";
                
                if (mysqli_query($conn, $insert_query)) {
                    $attendee_id = mysqli_insert_id($conn);
                    log_activity($_SESSION['user_id'], 'REGISTER_ATTENDEE', "Registered attendee: $name for event ID: $event_id");
                    header('Location: view.php?id=' . $attendee_id);
                    exit();
                } else {
                    $error = 'Error registering attendee. Please try again.';
                    log_error('Attendee registration failed: ' . mysqli_error($conn));
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Attendee - Event Management System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/attendees.css">
</head>
<body>
    <?php include '../includes/navigation.php'; ?>
    
    <div class="container">
        <div class="dashboard-header">
            <h1>Register New Attendee</h1>
            <p>Add a new attendee to an event</p>
        </div>

        <div class="form-container">
            <?php if ($error): ?>
                <div class="error-message" style="background: var(--error); color: white; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="event_id">Select Event *</label>
                    <select id="event_id" name="event_id" class="form-control" required>
                        <option value="">Choose an event...</option>
                        <?php while ($event = mysqli_fetch_assoc($events_result)): ?>
                            <option value="<?php echo $event['id']; ?>" <?php echo $event_id == $event['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($event['title']); ?> - <?php echo date('M d, Y', strtotime($event['start_date'])); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3"><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">Register Attendee</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>