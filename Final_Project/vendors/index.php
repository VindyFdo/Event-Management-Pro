<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Handle search
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search_condition = "WHERE name LIKE '%$search%' OR services LIKE '%$search%' OR contact_person LIKE '%$search%'";
}

// Get all vendors
$vendors_query = "SELECT * FROM vendors $search_condition ORDER BY name ASC";
$vendors_result = mysqli_query($conn, $vendors_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendors - Event Management System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/navigation.php'; ?>
    
    <div class="container">
        <div class="dashboard-header">
            <h1>Vendor Management</h1>
            <p>Manage your vendor database and service providers</p>
        </div>

        <div class="card">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <h2 class="card-title">All Vendors</h2>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <form method="GET" style="display: flex; gap: 0.5rem;">
                            <input type="text" name="search" placeholder="Search vendors..." class="form-control" style="width: auto;" value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-secondary">Search</button>
                        </form>
                        <a href="add.php" class="btn btn-primary">Add New Vendor</a>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Company Name</th>
                            <th>Contact Person</th>
                            <th>Contact Info</th>
                            <th>Services</th>
                            <th>Rating</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($vendors_result) > 0): ?>
                            <?php while ($vendor = mysqli_fetch_assoc($vendors_result)): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($vendor['name']); ?></strong>
                                        <?php if ($vendor['website']): ?>
                                            <br><a href="<?php echo htmlspecialchars($vendor['website']); ?>" target="_blank" style="color: var(--success); font-size: 0.8rem;">Website</a>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($vendor['contact_person']); ?></td>
                                    <td>
                                        <div style="font-size: 0.9rem;">
                                            📧 <?php echo htmlspecialchars($vendor['email']); ?><br>
                                            📞 <?php echo htmlspecialchars($vendor['phone']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.9rem;">
                                            <?php echo htmlspecialchars(substr($vendor['services'], 0, 100)); ?>
                                            <?php if (strlen($vendor['services']) > 100): ?>...<?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($vendor['rating'] > 0): ?>
                                            <div style="color: var(--warning);">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <?php echo ($i <= $vendor['rating']) ? '⭐' : '☆'; ?>
                                                <?php endfor; ?>
                                                <br><small><?php echo number_format($vendor['rating'], 1); ?>/5</small>
                                            </div>
                                        <?php else: ?>
                                            <span style="color: var(--dark-gray);">Not rated</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="background: <?php echo $vendor['status'] == 'active' ? 'var(--success)' : 'var(--dark-gray)'; ?>; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
                                            <?php echo ucfirst($vendor['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="view.php?id=<?php echo $vendor['id']; ?>" class="btn" style="background: var(--info); color: white; padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-right: 0.25rem;">View</a>
                                        <a href="edit.php?id=<?php echo $vendor['id']; ?>" class="btn" style="background: var(--warning); color: var(--primary); padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-right: 0.25rem;">Edit</a>
                                        <a href="delete.php?id=<?php echo $vendor['id']; ?>" class="btn" style="background: var(--error); color: white; padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="return confirm('Are you sure you want to delete this vendor?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 3rem; color: var(--dark-gray);">
                                    <?php if (!empty($search)): ?>
                                        No vendors found matching "<?php echo htmlspecialchars($search); ?>"
                                    <?php else: ?>
                                        No vendors found. <a href="add.php">Add your first vendor</a>
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