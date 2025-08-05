<?php
require_once '../includes/db_connection.php';

// Handle search and filters
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$location_filter = isset($_GET['location']) ? sanitize_input($_GET['location']) : '';

$search_condition = "WHERE start_date > NOW()";
if (!empty($search)) {
    $search_condition .= " AND (title LIKE '%$search%' OR description LIKE '%$search%')";
}
if (!empty($location_filter)) {
    $search_condition .= " AND location LIKE '%$location_filter%'";
}

// Get all upcoming events
$events_query = "SELECT * FROM events $search_condition ORDER BY start_date ASC";
$events_result = mysqli_query($conn, $events_query);

// Get unique locations for filter
$locations_query = "SELECT DISTINCT location FROM events WHERE start_date > NOW() ORDER BY location";
$locations_result = mysqli_query($conn, $locations_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - EventManager Pro</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/homepage.css">
    <link rel="stylesheet" href="../css/public.css">
</head>
<body>
    <!-- Public Navigation -->
    <nav class="public-navbar">
        <div class="nav-container">
            <a href="../home.php" class="logo">EventManager Pro</a>
            <ul class="nav-menu" id="nav-menu">
                <li><a href="../home.php" class="nav-link">Home</a></li>
                <li><a href="events.php" class="nav-link active">Events</a></li>
                <li><a href="about.php" class="nav-link">About</a></li>
                <li><a href="contact.php" class="nav-link">Contact</a></li>
                <li><a href="../login.php" class="nav-link login-btn">Admin Login</a></li>
            </ul>
            <div class="nav-toggle" id="nav-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Upcoming Events</h1>
            <p>Discover amazing events happening near you</p>
        </div>
    </section>

    <!-- Events Content -->
    <section class="events-content">
        <div class="container">
            <!-- Search and Filters -->
            <div class="events-filters">
                <form method="GET" class="filter-form">
                    <div class="filter-group">
                        <input type="text" name="search" placeholder="Search events..." class="form-control" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="filter-group">
                        <select name="location" class="form-control">
                            <option value="">All Locations</option>
                            <?php while ($location = mysqli_fetch_assoc($locations_result)): ?>
                                <option value="<?php echo htmlspecialchars($location['location']); ?>" <?php echo $location_filter == $location['location'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($location['location']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="btn btn-primary">Filter Events</button>
                    </div>
                </form>
            </div>

            <!-- Events Grid -->
            <div class="events-grid">
                <?php if (mysqli_num_rows($events_result) > 0): ?>
                    <?php while ($event = mysqli_fetch_assoc($events_result)): ?>
                        <div class="event-card">
                            <div class="event-date">
                                <span class="day"><?php echo date('d', strtotime($event['start_date'])); ?></span>
                                <span class="month"><?php echo date('M', strtotime($event['start_date'])); ?></span>
                            </div>
                            <div class="event-info">
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p class="event-description"><?php echo htmlspecialchars(substr($event['description'], 0, 150)); ?>...</p>
                                <div class="event-meta">
                                    <span class="location">📍 <?php echo htmlspecialchars($event['location']); ?></span>
                                    <span class="time">🕒 <?php echo date('g:i A', strtotime($event['start_date'])); ?></span>
                                    <span class="capacity">👥 <?php echo $event['capacity']; ?> spots</span>
                                </div>
                                <div class="event-actions">
                                    <a href="event_details.php?id=<?php echo $event['id']; ?>" class="btn btn-primary">View Details</a>
                                    <a href="register.php?event_id=<?php echo $event['id']; ?>" class="btn btn-accent">Register Now</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-events">
                        <h3>No Events Found</h3>
                        <p>Sorry, no events match your search criteria. Try adjusting your filters or check back later for new events.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>EventManager Pro</h3>
                    <p>Professional event management solutions for every occasion.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="../home.php">Home</a></li>
                        <li><a href="events.php">Events</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <ul>
                        <li>📧 info@eventmanagerpro.com</li>
                        <li>📞 +1 (555) 123-4567</li>
                        <li>📍 123 Event Street, City, State</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 EventManager Pro. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navToggle = document.getElementById('nav-toggle');
            const navMenu = document.getElementById('nav-menu');

            navToggle.addEventListener('click', function() {
                navMenu.classList.toggle('active');
            });

            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', () => {
                    navMenu.classList.remove('active');
                });
            });
        });
    </script>
</body>
</html>