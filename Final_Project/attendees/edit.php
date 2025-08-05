<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$attendee_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';

if ($attendee_id <= 0) {
    header('Location: index.php');
    exit();
}

// Get attendee details
$attendee_query = "SELECT * FROM attendees WHERE id = $attendee_id";
$attendee_result = mysqli_query($conn, $attendee_query);

if (mysqli_num_rows($attendee_result) == 0) {
    header('Location: index.php');
    exit();
}

$attendee = mysqli_fetch_assoc($attendee_result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $status = sanitize_input($_POST['status']);
    $notes = sanitize_input($_POST['notes']);
    
    // Validation
    if (empty($name) || empty($email)) {
        $error = 'Please fill in all required fields';
    } elseif (!validate_email($email)) {
        $error = 'Please enter a valid email address';
    } else {
        $update_query = "UPDATE attendees SET 
                        name = '$name', 
                        email = '$email', 
                        phone = '$phone', 
                        status = '$status', 
                        notes = '$notes' 
                        WHERE id = $attendee_id";
        
        if (mysqli_query($conn, $update_query)) {
            log_activity($_SESSION['user_id'], 'UPDATE_ATTENDEE', "Updated attendee: $name");
            header('Location: view.php?id=' . $attendee_id);
            exit();
        } else {
            $error = 'Error updating attendee. Please try again.';
            log_error('Attendee update failed: ' . mysqli_error($conn));
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Attendee - Event Management System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/attendees.css">
</head>
<body>
    <?php include '../includes/navigation.php'; ?>
    
    <div class="container">
        <div class="dashboard-header">
            <h1>Edit Attendee</h1>
            <p>Update attendee information</p>
        </div>

        <div class="form-container">
            <?php if ($error): ?>
                <div class="error-message" style="background: var(--error); color: white; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($attendee['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($attendee['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($attendee['phone']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="registered" <?php echo $attendee['status'] == 'registered' ? 'selected' : ''; ?>>Registered</option>
                        <option value="attended" <?php echo $attendee['status'] == 'attended' ? 'selected' : ''; ?>>Attended</option>
                        <option value="cancelled" <?php echo $attendee['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3"><?php echo htmlspecialchars($attendee['notes']); ?></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">Update Attendee</button>
                    <a href="view.php?id=<?php echo $attendee_id; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>