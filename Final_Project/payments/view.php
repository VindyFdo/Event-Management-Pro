<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$payment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($payment_id <= 0) {
    header('Location: index.php');
    exit();
}

// Get payment details with attendee and event information
$payment_query = "SELECT p.*, a.name as attendee_name, a.email as attendee_email, 
                         e.title as event_title, e.start_date 
                  FROM payments p 
                  JOIN attendees a ON p.attendee_id = a.id 
                  JOIN events e ON a.event_id = e.id 
                  WHERE p.id = $payment_id";
$payment_result = mysqli_query($conn, $payment_query);

if (mysqli_num_rows($payment_result) == 0) {
    header('Location: index.php');
    exit();
}

$payment = mysqli_fetch_assoc($payment_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Details - Event Management System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/payments.css">
</head>
<body>
    <?php include '../includes/navigation.php'; ?>
    
    <div class="container">
        <div class="payment-header">
            <div class="payment-title-section">
                <h1>Payment Details</h1>
                <span class="payment-status status-<?php echo $payment['status']; ?>">
                    <?php echo ucfirst($payment['status']); ?>
                </span>
            </div>
            <div class="payment-actions">
                <a href="index.php" class="btn btn-secondary">Back to Payments</a>
            </div>
        </div>

        <div class="payment-details-grid">
            <div class="payment-main-info">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Payment Information</h2>
                    </div>
                    <div class="payment-info">
                        <div class="info-item">
                            <strong>💰 Amount:</strong>
                            <p class="amount">$<?php echo number_format($payment['amount'], 2); ?></p>
                        </div>
                        
                        <div class="info-item">
                            <strong>💳 Payment Method:</strong>
                            <p><?php echo ucwords(str_replace('_', ' ', $payment['payment_method'])); ?></p>
                        </div>
                        
                        <div class="info-item">
                            <strong>📅 Payment Date:</strong>
                            <p><?php echo date('F d, Y g:i A', strtotime($payment['payment_date'])); ?></p>
                        </div>
                        
                        <div class="info-item">
                            <strong>📊 Status:</strong>
                            <p><span class="status-badge status-<?php echo $payment['status']; ?>"><?php echo ucfirst($payment['status']); ?></span></p>
                        </div>
                        
                        <?php if ($payment['transaction_id']): ?>
                        <div class="info-item">
                            <strong>🔢 Transaction ID:</strong>
                            <p><?php echo htmlspecialchars($payment['transaction_id']); ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($payment['notes']): ?>
                        <div class="info-item">
                            <strong>📝 Notes:</strong>
                            <p><?php echo nl2br(htmlspecialchars($payment['notes'])); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Attendee Information</h2>
                    </div>
                    <div class="attendee-info">
                        <div class="info-item">
                            <strong>👤 Name:</strong>
                            <p><a href="../attendees/view.php?id=<?php echo $payment['attendee_id']; ?>"><?php echo htmlspecialchars($payment['attendee_name']); ?></a></p>
                        </div>
                        
                        <div class="info-item">
                            <strong>📧 Email:</strong>
                            <p><a href="mailto:<?php echo htmlspecialchars($payment['attendee_email']); ?>"><?php echo htmlspecialchars($payment['attendee_email']); ?></a></p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Event Information</h2>
                    </div>
                    <div class="event-info">
                        <div class="info-item">
                            <strong>🎯 Event:</strong>
                            <p><?php echo htmlspecialchars($payment['event_title']); ?></p>
                        </div>
                        
                        <div class="info-item">
                            <strong>📅 Event Date:</strong>
                            <p><?php echo date('F d, Y g:i A', strtotime($payment['start_date'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="payment-sidebar">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="action-buttons">
                        <?php if ($payment['status'] == 'pending'): ?>
                            <a href="update_status.php?id=<?php echo $payment['id']; ?>&status=completed" class="btn btn-success" onclick="return confirm('Mark this payment as completed?')">Mark Completed</a>
                            <a href="update_status.php?id=<?php echo $payment['id']; ?>&status=failed" class="btn btn-danger" onclick="return confirm('Mark this payment as failed?')">Mark Failed</a>
                        <?php elseif ($payment['status'] == 'completed'): ?>
                            <a href="update_status.php?id=<?php echo $payment['id']; ?>&status=refunded" class="btn btn-warning" onclick="return confirm('Process refund for this payment?')">Process Refund</a>
                        <?php endif; ?>
                        <a href="../attendees/view.php?id=<?php echo $payment['attendee_id']; ?>" class="btn btn-primary">View Attendee</a>
                        <a href="mailto:<?php echo htmlspecialchars($payment['attendee_email']); ?>" class="btn btn-accent">Send Email</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>