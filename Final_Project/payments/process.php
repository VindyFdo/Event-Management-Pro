<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$attendee_id = isset($_GET['attendee_id']) ? (int)$_GET['attendee_id'] : 0;
$error = '';
$success = '';

// Get attendees for dropdown
$attendees_query = "SELECT a.id, a.name, a.email, e.title as event_title 
                   FROM attendees a 
                   JOIN events e ON a.event_id = e.id 
                   ORDER BY a.name ASC";
$attendees_result = mysqli_query($conn, $attendees_query);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $attendee_id = (int)$_POST['attendee_id'];
    $amount = (float)$_POST['amount'];
    $payment_method = sanitize_input($_POST['payment_method']);
    $transaction_id = sanitize_input($_POST['transaction_id']);
    $notes = sanitize_input($_POST['notes']);
    
    // Validation
    if ($attendee_id <= 0 || $amount <= 0 || empty($payment_method)) {
        $error = 'Please fill in all required fields';
    } else {
        $payment_date = date('Y-m-d H:i:s');
        $status = 'completed'; // For practice, we'll mark as completed immediately
        
        $insert_query = "INSERT INTO payments (attendee_id, amount, payment_method, transaction_id, payment_date, status, notes) 
                        VALUES ($attendee_id, $amount, '$payment_method', '$transaction_id', '$payment_date', '$status', '$notes')";
        
        if (mysqli_query($conn, $insert_query)) {
            $payment_id = mysqli_insert_id($conn);
            log_activity($_SESSION['user_id'], 'PROCESS_PAYMENT', "Processed payment of $$amount for attendee ID: $attendee_id");
            header('Location: view.php?id=' . $payment_id);
            exit();
        } else {
            $error = 'Error processing payment. Please try again.';
            log_error('Payment processing failed: ' . mysqli_error($conn));
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Payment - Event Management System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/payments.css">
</head>
<body>
    <?php include '../includes/navigation.php'; ?>
    
    <div class="container">
        <div class="dashboard-header">
            <h1>Process Payment</h1>
            <p>Record a new payment transaction</p>
        </div>

        <div class="form-container">
            <?php if ($error): ?>
                <div class="error-message" style="background: var(--error); color: white; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="attendee_id">Select Attendee *</label>
                    <select id="attendee_id" name="attendee_id" class="form-control" required>
                        <option value="">Choose an attendee...</option>
                        <?php while ($attendee = mysqli_fetch_assoc($attendees_result)): ?>
                            <option value="<?php echo $attendee['id']; ?>" <?php echo $attendee_id == $attendee['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($attendee['name']); ?> - <?php echo htmlspecialchars($attendee['event_title']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="amount">Amount *</label>
                        <input type="number" id="amount" name="amount" class="form-control" min="0.01" step="0.01" value="<?php echo isset($_POST['amount']) ? $_POST['amount'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_method">Payment Method *</label>
                        <select id="payment_method" name="payment_method" class="form-control" required>
                            <option value="">Select method...</option>
                            <option value="credit_card" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'credit_card') ? 'selected' : ''; ?>>Credit Card</option>
                            <option value="debit_card" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'debit_card') ? 'selected' : ''; ?>>Debit Card</option>
                            <option value="paypal" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'paypal') ? 'selected' : ''; ?>>PayPal</option>
                            <option value="bank_transfer" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'bank_transfer') ? 'selected' : ''; ?>>Bank Transfer</option>
                            <option value="cash" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'cash') ? 'selected' : ''; ?>>Cash</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="transaction_id">Transaction ID</label>
                    <input type="text" id="transaction_id" name="transaction_id" class="form-control" value="<?php echo isset($_POST['transaction_id']) ? htmlspecialchars($_POST['transaction_id']) : ''; ?>">
                    <small style="color: var(--dark-gray);">Optional - for tracking purposes</small>
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3"><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">Process Payment</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>