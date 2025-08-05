<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

if ($event_id <= 0) {
    header('Location: index.php');
    exit();
}

// Get event details
$event_query = "SELECT * FROM events WHERE id = $event_id";
$event_result = mysqli_query($conn, $event_query);

if (mysqli_num_rows($event_result) == 0) {
    header('Location: index.php');
    exit();
}

$event = mysqli_fetch_assoc($event_result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize_input($_POST['title']);
    $description = sanitize_input($_POST['description']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $location = sanitize_input($_POST['location']);
    $capacity = (int)$_POST['capacity'];
    
    // Validation
    if (empty($title) || empty($description) || empty($start_date) || empty($end_date) || empty($location) || $capacity <= 0) {
        $error = 'Please fill in all fields with valid data';
    } elseif (strtotime($start_date) >= strtotime($end_date)) {
        $error = 'End date must be after start date';
    } else {
        $updated_date = date('Y-m-d H:i:s');
        
        $update_query = "UPDATE events SET 
                        title = '$title', 
                        description = '$description', 
                        start_date = '$start_date', 
                        end_date = '$end_date', 
                        location = '$location', 
                        capacity = $capacity, 
                        updated_date = '$updated_date' 
                        WHERE id = $event_id";
        
        if (mysqli_query($conn, $update_query)) {
            log_activity($_SESSION['user_id'], 'UPDATE_EVENT', "Updated event: $title");
            header('Location: view.php?id=' . $event_id);
            exit();
        } else {
            $error = 'Error updating event. Please try again.';
            log_error('Event update failed: ' . mysqli_error($conn));
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - Event Management System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/events.css">
</head>
<body>
    <?php include '../includes/navigation.php'; ?>
    
    <div class="container">
        <div class="dashboard-header">
            <h1>Edit Event</h1>
            <p>Update event details and information</p>
        </div>

        <div class="form-container">
            <?php if ($error): ?>
                <div class="error-message" style="background: var(--error); color: white; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="title">Event Title *</label>
                    <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($event['title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($event['description']); ?></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="start_date">Start Date & Time *</label>
                        <input type="datetime-local" id="start_date" name="start_date" class="form-control" value="<?php echo date('Y-m-d\TH:i', strtotime($event['start_date'])); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="end_date">End Date & Time *</label>
                        <input type="datetime-local" id="end_date" name="end_date" class="form-control" value="<?php echo date('Y-m-d\TH:i', strtotime($event['end_date'])); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="location">Location *</label>
                    <input type="text" id="location" name="location" class="form-control" value="<?php echo htmlspecialchars($event['location']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="capacity">Maximum Capacity *</label>
                    <input type="number" id="capacity" name="capacity" class="form-control" min="1" value="<?php echo $event['capacity']; ?>" required>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">Update Event</button>
                    <a href="view.php?id=<?php echo $event_id; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>