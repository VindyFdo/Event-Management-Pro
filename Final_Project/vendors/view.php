<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$vendor_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

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

// Get events this vendor is associated with
$events_query = "SELECT e.id, e.title, e.start_date, ev.service_type, ev.cost, ev.status 
                FROM events e 
                JOIN event_vendors ev ON e.id = ev.event_id 
                WHERE ev.vendor_id = $vendor_id 
                ORDER BY e.start_date DESC";
$events = mysqli_query($conn, $events_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($vendor['name']); ?> - Vendor Details</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/vendors.css">
</head>
<body>
    <?php include '../includes/navigation.php'; ?>
    
    <div class="container">
        <div class="vendor-header">
            <div class="vendor-title-section">
                <h1><?php echo htmlspecialchars($vendor['name']); ?></h1>
                <span class="vendor-status status-<?php echo $vendor['status']; ?>">
                    <?php echo ucfirst($vendor['status']); ?>
                </span>
            </div>
            <div class="vendor-actions">
                <a href="edit.php?id=<?php echo $vendor['id']; ?>" class="btn btn-warning">Edit</a>
                <a href="index.php" class="btn btn-secondary">Back to List</a>
            </div>
        </div>

        <div class="vendor-details-grid">
            <div class="vendor-main-info">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Vendor Information</h2>
                    </div>
                    <div class="vendor-info">
                        <div class="info-item">
                            <strong>🏢 Company Name:</strong>
                            <p><?php echo htmlspecialchars($vendor['name']); ?></p>
                        </div>
                        
                        <div class="info-item">
                            <strong>👤 Contact Person:</strong>
                            <p><?php echo htmlspecialchars($vendor['contact_person']); ?></p>
                        </div>
                        
                        <div class="info-item">
                            <strong>📧 Email:</strong>
                            <p><a href="mailto:<?php echo htmlspecialchars($vendor['email']); ?>"><?php echo htmlspecialchars($vendor['email']); ?></a></p>
                        </div>
                        
                        <div class="info-item">
                            <strong>📞 Phone:</strong>
                            <p><a href="tel:<?php echo htmlspecialchars($vendor['phone']); ?>"><?php echo htmlspecialchars($vendor['phone']); ?></a></p>
                        </div>
                        
                        <?php if ($vendor['address']): ?>
                        <div class="info-item">
                            <strong>📍 Address:</strong>
                            <p><?php echo nl2br(htmlspecialchars($vendor['address'])); ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="info-item">
                            <strong>🛠️ Services:</strong>
                            <p><?php echo nl2br(htmlspecialchars($vendor['services'])); ?></p>
                        </div>
                        
                        <?php if ($vendor['website']): ?>
                        <div class="info-item">
                            <strong>🌐 Website:</strong>
                            <p><a href="<?php echo htmlspecialchars($vendor['website']); ?>" target="_blank"><?php echo htmlspecialchars($vendor['website']); ?></a></p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="info-item">
                            <strong>⭐ Rating:</strong>
                            <p>
                                <?php if ($vendor['rating'] > 0): ?>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php echo ($i <= $vendor['rating']) ? '⭐' : '☆'; ?>
                                    <?php endfor; ?>
                                    (<?php echo number_format($vendor['rating'], 1); ?>/5)
                                <?php else: ?>
                                    Not rated yet
                                <?php endif; ?>
                            </p>
                        </div>
                        
                        <?php if ($vendor['notes']): ?>
                        <div class="info-item">
                            <strong>📝 Notes:</strong>
                            <p><?php echo nl2br(htmlspecialchars($vendor['notes'])); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (mysqli_num_rows($events) > 0): ?>
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Associated Events</h2>
                    </div>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Date</th>
                                    <th>Service</th>
                                    <th>Cost</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($event = mysqli_fetch_assoc($events)): ?>
                                    <tr>
                                        <td>
                                            <a href="../events/view.php?id=<?php echo $event['id']; ?>">
                                                <?php echo htmlspecialchars($event['title']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($event['start_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($event['service_type']); ?></td>
                                        <td>$<?php echo number_format($event['cost'], 2); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $event['status']; ?>">
                                                <?php echo ucfirst($event['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="vendor-sidebar">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="action-buttons">
                        <a href="edit.php?id=<?php echo $vendor['id']; ?>" class="btn btn-warning">Edit Details</a>
                        <a href="mailto:<?php echo htmlspecialchars($vendor['email']); ?>" class="btn btn-accent">Send Email</a>
                        <a href="tel:<?php echo htmlspecialchars($vendor['phone']); ?>" class="btn btn-primary">Call Vendor</a>
                        <?php if ($vendor['website']): ?>
                            <a href="<?php echo htmlspecialchars($vendor['website']); ?>" target="_blank" class="btn btn-info">Visit Website</a>
                        <?php endif; ?>
                        <a href="delete.php?id=<?php echo $vendor['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this vendor?')">Delete</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>