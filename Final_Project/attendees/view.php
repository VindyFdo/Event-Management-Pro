<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$attendee_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($attendee_id <= 0) {
    header('Location: index.php');
    exit();
}

// Get attendee details with event information
$attendee_query = "SELECT a.*, e.title as event_title, e.start_date, e.end_date, e.location 
                   FROM attendees a 
                   JOIN events e ON a.event_id = e.id 
                   WHERE a.id = $attendee_id";
$attendee_result = mysqli_query($conn, $attendee_query);

if (mysqli_num_rows($attendee_result) == 0) {
    header('Location: index.php');
    exit();
}

$attendee = mysqli_fetch_assoc($attendee_result);

// Get payment information
$payment_query = "SELECT * FROM payments WHERE attendee_id = $attendee_id ORDER BY payment_date DESC";
$payments = mysqli_query($conn, $payment_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($attendee['name']); ?> - Attendee Details</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/attendees.css">
</head>
<body>
    <?php include '../includes/navigation.php'; ?>
    
    <div class="container">
        <div class="attendee-header">
            <div class="attendee-title-section">
                <h1><?php echo htmlspecialchars($attendee['name']); ?></h1>
                <span class="attendee-status status-<?php echo $attendee['status']; ?>">
                    <?php echo ucfirst($attendee['status']); ?>
                </span>
            </div>
            <div class="attendee-actions">
                <a href="edit.php?id=<?php echo $attendee['id']; ?>" class="btn btn-warning">Edit</a>
                <a href="index.php" class="btn btn-secondary">Back to List</a>
            </div>
        </div>

        <div class="attendee-details-grid">
            <div class="attendee-main-info">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Attendee Information</h2>
                    </div>
                    <div class="attendee-info">
                        <div class="info-item">
                            <strong>👤 Full Name:</strong>
                            <p><?php echo htmlspecialchars($attendee['name']); ?></p>
                        </div>
                        
                        <div class="info-item">
                            <strong>📧 Email:</strong>
                            <p><a href="mailto:<?php echo htmlspecialchars($attendee['email']); ?>"><?php echo htmlspecialchars($attendee['email']); ?></a></p>
                        </div>
                        
                        <?php if ($attendee['phone']): ?>
                        <div class="info-item">
                            <strong>📞 Phone:</strong>
                            <p><a href="tel:<?php echo htmlspecialchars($attendee['phone']); ?>"><?php echo htmlspecialchars($attendee['phone']); ?></a></p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="info-item">
                            <strong>📅 Registration Date:</strong>
                            <p><?php echo date('F d, Y g:i A', strtotime($attendee['registration_date'])); ?></p>
                        </div>
                        
                        <div class="info-item">
                            <strong>📊 Status:</strong>
                            <p><span class="status-badge status-<?php echo $attendee['status']; ?>"><?php echo ucfirst($attendee['status']); ?></span></p>
                        </div>
                        
                        <?php if ($attendee['notes']): ?>
                        <div class="info-item">
                            <strong>📝 Notes:</strong>
                            <p><?php echo nl2br(htmlspecialchars($attendee['notes'])); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Event Information</h2>
                    </div>
                    <div class="event-info">
                        <div class="info-item">
                            <strong>🎯 Event:</strong>
                            <p><a href="../events/view.php?id=<?php echo $attendee['event_id']; ?>"><?php echo htmlspecialchars($attendee['event_title']); ?></a></p>
                        </div>
                        
                        <div class="info-item">
                            <strong>📅 Event Date:</strong>
                            <p><?php echo date('F d, Y g:i A', strtotime($attendee['start_date'])); ?></p>
                        </div>
                        
                        <div class="info-item">
                            <strong>📍 Location:</strong>
                            <p><?php echo htmlspecialchars($attendee['location']); ?></p>
                        </div>
                    </div>
                </div>

                <?php if (mysqli_num_rows($payments) > 0): ?>
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Payment History</h2>
                    </div>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Transaction ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($payment = mysqli_fetch_assoc($payments)): ?>
                                    <tr>
                                        <td><strong>$<?php echo number_format($payment['amount'], 2); ?></strong></td>
                                        <td><?php echo ucwords(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                                        <td><?php echo date('M d, Y g:i A', strtotime($payment['payment_date'])); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $payment['status']; ?>">
                                                <?php echo ucfirst($payment['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $payment['transaction_id'] ? htmlspecialchars($payment['transaction_id']) : 'N/A'; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="attendee-sidebar">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="action-buttons">
                        <a href="edit.php?id=<?php echo $attendee['id']; ?>" class="btn btn-warning">Edit Details</a>
                        <?php if ($attendee['status'] == 'registered'): ?>
                            <a href="mark_attended.php?id=<?php echo $attendee['id']; ?>" class="btn btn-success">Mark as Attended</a>
                        <?php endif; ?>
                        <a href="../payments/process.php?attendee_id=<?php echo $attendee['id']; ?>" class="btn btn-primary">Process Payment</a>
                        <a href="mailto:<?php echo htmlspecialchars($attendee['email']); ?>" class="btn btn-accent">Send Email</a>
                        <a href="delete.php?id=<?php echo $attendee['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this attendee?')">Delete</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>