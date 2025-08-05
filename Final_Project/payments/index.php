<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Handle search and filters
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';

$search_condition = '';
if (!empty($search)) {
    $search_condition .= "AND (a.name LIKE '%$search%' OR a.email LIKE '%$search%' OR p.transaction_id LIKE '%$search%')";
}
if (!empty($status_filter)) {
    $search_condition .= " AND p.status = '$status_filter'";
}

// Get payments with attendee and event information
$payments_query = "SELECT p.*, a.name as attendee_name, a.email as attendee_email, 
                          e.title as event_title, e.start_date 
                   FROM payments p 
                   JOIN attendees a ON p.attendee_id = a.id 
                   JOIN events e ON a.event_id = e.id 
                   WHERE 1=1 $search_condition 
                   ORDER BY p.payment_date DESC";
$payments_result = mysqli_query($conn, $payments_query);

// Get payment statistics
$stats_query = "SELECT 
    SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_revenue,
    SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount,
    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
    COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_count
    FROM payments";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - Event Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/navigation.php'; ?>
    
    <div class="container">
        <div class="dashboard-header">
            <h1>Payment Management</h1>
            <p>Track payments, transactions, and financial data</p>
        </div>

        <!-- Payment Statistics -->
        <div class="stats-grid" style="margin-bottom: 2rem;">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, var(--success), var(--info));">💰</div>
                <div class="stat-info">
                    <h3>$<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, var(--warning), var(--light));">⏳</div>
                <div class="stat-info">
                    <h3>$<?php echo number_format($stats['pending_amount'] ?? 0, 2); ?></h3>
                    <p>Pending Payments</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, var(--success), var(--info));">✅</div>
                <div class="stat-info">
                    <h3><?php echo $stats['completed_count'] ?? 0; ?></h3>
                    <p>Completed</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, var(--error), var(--danger));">❌</div>
                <div class="stat-info">
                    <h3><?php echo $stats['failed_count'] ?? 0; ?></h3>
                    <p>Failed</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <h2 class="card-title">Payment Transactions</h2>
                    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <form method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <input type="text" name="search" placeholder="Search payments..." class="form-control" style="width: auto;" value="<?php echo htmlspecialchars($search); ?>">
                            <select name="status" class="form-control" style="width: auto;">
                                <option value="">All Status</option>
                                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="failed" <?php echo $status_filter == 'failed' ? 'selected' : ''; ?>>Failed</option>
                                <option value="refunded" <?php echo $status_filter == 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                            </select>
                            <button type="submit" class="btn btn-secondary">Filter</button>
                        </form>
                        <a href="process.php" class="btn btn-primary">Process Payment</a>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Attendee</th>
                            <th>Event</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($payments_result) > 0): ?>
                            <?php while ($payment = mysqli_fetch_assoc($payments_result)): ?>
                                <?php
                                $status_colors = [
                                    'pending' => 'var(--warning)',
                                    'completed' => 'var(--success)',
                                    'failed' => 'var(--error)',
                                    'refunded' => 'var(--dark-gray)'
                                ];
                                $status_color = $status_colors[$payment['status']] ?? 'var(--dark-gray)';
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo $payment['transaction_id'] ? htmlspecialchars($payment['transaction_id']) : 'N/A'; ?></strong>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($payment['attendee_name']); ?></strong>
                                        <br><small style="color: var(--dark-gray);"><?php echo htmlspecialchars($payment['attendee_email']); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($payment['event_title']); ?></strong>
                                        <br><small style="color: var(--dark-gray);"><?php echo date('M d, Y', strtotime($payment['start_date'])); ?></small>
                                    </td>
                                    <td>
                                        <strong style="color: var(--success); font-size: 1.1rem;">$<?php echo number_format($payment['amount'], 2); ?></strong>
                                    </td>
                                    <td><?php echo ucwords(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                                    <td><?php echo date('M d, Y g:i A', strtotime($payment['payment_date'])); ?></td>
                                    <td>
                                        <span style="background: <?php echo $status_color; ?>; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
                                            <?php echo ucfirst($payment['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="view.php?id=<?php echo $payment['id']; ?>" class="btn" style="background: var(--info); color: white; padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-right: 0.25rem;">View</a>
                                        <?php if ($payment['status'] == 'pending'): ?>
                                            <a href="update_status.php?id=<?php echo $payment['id']; ?>&status=completed" class="btn" style="background: var(--success); color: white; padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-right: 0.25rem;" onclick="return confirm('Mark this payment as completed?')">Complete</a>
                                            <a href="update_status.php?id=<?php echo $payment['id']; ?>&status=failed" class="btn" style="background: var(--error); color: white; padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="return confirm('Mark this payment as failed?')">Fail</a>
                                        <?php elseif ($payment['status'] == 'completed'): ?>
                                            <a href="update_status.php?id=<?php echo $payment['id']; ?>&status=refunded" class="btn" style="background: var(--dark-gray); color: white; padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="return confirm('Process refund for this payment?')">Refund</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 3rem; color: var(--dark-gray);">
                                    <?php if (!empty($search) || !empty($status_filter)): ?>
                                        No payments found matching your criteria
                                    <?php else: ?>
                                        No payments found
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>