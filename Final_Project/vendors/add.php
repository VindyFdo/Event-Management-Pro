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
    $name = sanitize_input($_POST['name']);
    $contact_person = sanitize_input($_POST['contact_person']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);
    $services = sanitize_input($_POST['services']);
    $website = sanitize_input($_POST['website']);
    $notes = sanitize_input($_POST['notes']);
    
    // Validation
    if (empty($name) || empty($contact_person) || empty($email) || empty($phone) || empty($services)) {
        $error = 'Please fill in all required fields';
    } elseif (!validate_email($email)) {
        $error = 'Please enter a valid email address';
    } else {
        $created_date = date('Y-m-d H:i:s');
        
        $insert_query = "INSERT INTO vendors (name, contact_person, email, phone, address, services, website, notes, created_date) 
                        VALUES ('$name', '$contact_person', '$email', '$phone', '$address', '$services', '$website', '$notes', '$created_date')";
        
        if (mysqli_query($conn, $insert_query)) {
            $vendor_id = mysqli_insert_id($conn);
            log_activity($_SESSION['user_id'], 'CREATE_VENDOR', "Created vendor: $name");
            header('Location: view.php?id=' . $vendor_id);
            exit();
        } else {
            $error = 'Error creating vendor. Please try again.';
            log_error('Vendor creation failed: ' . mysqli_error($conn));
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Vendor - Event Management System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/vendors.css">
</head>
<body>
    <?php include '../includes/navigation.php'; ?>
    
    <div class="container">
        <div class="dashboard-header">
            <h1>Add New Vendor</h1>
            <p>Add a new vendor to your database</p>
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
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="contact_person">Contact Person *</label>
                    <input type="text" id="contact_person" name="contact_person" class="form-control" value="<?php echo isset($_POST['contact_person']) ? htmlspecialchars($_POST['contact_person']) : ''; ?>" required>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" class="form-control" rows="3"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="services">Services Provided *</label>
                    <textarea id="services" name="services" class="form-control" rows="4" placeholder="e.g., Catering, Photography, Sound Equipment, Decorations" required><?php echo isset($_POST['services']) ? htmlspecialchars($_POST['services']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="website">Website</label>
                    <input type="url" id="website" name="website" class="form-control" placeholder="https://" value="<?php echo isset($_POST['website']) ? htmlspecialchars($_POST['website']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3"><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">Add Vendor</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>