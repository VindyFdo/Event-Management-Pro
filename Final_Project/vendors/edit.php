<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$vendor_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';

if ($vendor_id <= 0) {
    header('Location: index.php');
    exit();
}

// Get vendor details
$vendor_query = "SELECT * FROM vendors WHERE id = $vendor_id";
$vendor_result = mysqli_query($conn, $vendor_query);

if (mysqli_num_rows($vendor_result) == 0) {
    header('Location: index.php');
    exit();
}

$vendor = mysqli_fetch_assoc($vendor_result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize_input($_POST['name']);
    $contact_person = sanitize_input($_POST['contact_person']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);
    $services = sanitize_input($_POST['services']);
    $website = sanitize_input($_POST['website']);
    $rating = (float)$_POST['rating'];
    $status = sanitize_input($_POST['status']);
    $notes = sanitize_input($_POST['notes']);
    
    // Validation
    if (empty($name) || empty($contact_person) || empty($email) || empty($phone) || empty($services)) {
        $error = 'Please fill in all required fields';
    } elseif (!validate_email($email)) {
        $error = 'Please enter a valid email address';
    } elseif ($rating < 0 || $rating > 5) {
        $error = 'Rating must be between 0 and 5';
    } else {
        $update_query = "UPDATE vendors SET 
                        name = '$name', 
                        contact_person = '$contact_person', 
                        email = '$email', 
                        phone = '$phone', 
                        address = '$address', 
                        services = '$services', 
                        website = '$website', 
                        rating = $rating, 
                        status = '$status', 
                        notes = '$notes' 
                        WHERE id = $vendor_id";
        
        if (mysqli_query($conn, $update_query)) {
            log_activity($_SESSION['user_id'], 'UPDATE_VENDOR', "Updated vendor: $name");
            header('Location: view.php?id=' . $vendor_id);
            exit();
        } else {
            $error = 'Error updating vendor. Please try again.';
            log_error('Vendor update failed: ' . mysqli_error($conn));
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Vendor - Event Management System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/vendors.css">
</head>
<body>
    <?php include '../includes/navigation.php'; ?>
    
    <div class="container">
        <div class="dashboard-header">
            <h1>Edit Vendor</h1>
            <p>Update vendor information</p>
        </div>

        <div class="form-container">
            <?php if ($error): ?>
                <div class="error-message" style="background: var(--error); color: white; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="name">Company Name *</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($vendor['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="contact_person">Contact Person *</label>
                    <input type="text" id="contact_person" name="contact_person" class="form-control" value="<?php echo htmlspecialchars($vendor['contact_person']); ?>" required>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($vendor['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($vendor['phone']); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($vendor['address']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="services">Services Provided *</label>
                    <textarea id="services" name="services" class="form-control" rows="4" required><?php echo htmlspecialchars($vendor['services']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="website">Website</label>
                    <input type="url" id="website" name="website" class="form-control" value="<?php echo htmlspecialchars($vendor['website']); ?>">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="rating">Rating (0-5)</label>
                        <input type="number" id="rating" name="rating" class="form-control" min="0" max="5" step="0.1" value="<?php echo $vendor['rating']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="active" <?php echo $vendor['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $vendor['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3"><?php echo htmlspecialchars($vendor['notes']); ?></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">Update Vendor</button>
                    <a href="view.php?id=<?php echo $vendor_id; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>