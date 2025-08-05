<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$error = '';
$success = '';

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
    } elseif (strtotime($start_date) <= time()) {
        $error = 'Start date must be in the future';
    } else {
        $created_by = $_SESSION['user_id'];
        $created_date = date('Y-m-d H:i:s');
        
        $insert_query = "INSERT INTO events (title, description, start_date, end_date, location, capacity, created_by, created_date) 
                        VALUES ('$title', '$description', '$start_date', '$end_date', '$location', $capacity, $created_by, '$created_date')";
        
        if (mysqli_query($conn, $insert_query)) {
            $event_id = mysqli_insert_id($conn);
            log_activity($_SESSION['user_id'], 'CREATE_EVENT', "Created event: $title");
            header('Location: view.php?id=' . $event_id);
            exit();
        } else {
            $error = 'Error creating event. Please try again.';
            log_error('Event creation failed: ' . mysqli_error($conn));
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - Event Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/navigation.php'; ?>
    
    <div class="container">
        <div class="dashboard-header">
            <h1>Create New Event</h1>
            <p>Fill in the details to create a new event</p>
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
                    <input type="text" id="title" name="title" class="form-control" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" class="form-control" rows="4" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="start_date">Start Date & Time *</label>
                        <input type="datetime-local" id="start_date" name="start_date" class="form-control" value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="end_date">End Date & Time *</label>
                        <input type="datetime-local" id="end_date" name="end_date" class="form-control" value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="location">Location *</label>
                    <input type="text" id="location" name="location" class="form-control" placeholder="e.g., Conference Room A, 123 Main St, New York" value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="capacity">Maximum Capacity *</label>
                    <input type="number" id="capacity" name="capacity" class="form-control" min="1" value="<?php echo isset($_POST['capacity']) ? $_POST['capacity'] : ''; ?>" required>
                    <small style="color: var(--dark-gray);">Maximum number of attendees allowed</small>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">Create Event</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>