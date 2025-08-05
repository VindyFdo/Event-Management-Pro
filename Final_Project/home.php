<?php
require_once 'includes/db_connection.php';

// Get upcoming public events
$upcoming_events_query = "SELECT * FROM events WHERE start_date > NOW() ORDER BY start_date ASC LIMIT 6";
$upcoming_events = mysqli_query($conn, $upcoming_events_query);

// Get event statistics for display
$total_events_query = "SELECT COUNT(*) as count FROM events WHERE start_date > NOW()";
$total_events = mysqli_fetch_assoc(mysqli_query($conn, $total_events_query))['count'];

$total_attendees_query = "SELECT COUNT(*) as count FROM attendees";
$total_attendees = mysqli_fetch_assoc(mysqli_query($conn, $total_attendees_query))['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventManager Pro - Professional Event Management</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/homepage.css">
</head>
<body>
    <!-- Public Navigation -->
    <nav class="public-navbar">
        <div class="nav-container">
            <a href="home.php" class="logo">EventManager Pro</a>
            <ul class="nav-menu" id="nav-menu">
                <li><a href="home.php" class="nav-link">Home</a></li>
                <li><a href="public/events.php" class="nav-link">Events</a></li>
                <li><a href="public/about.php" class="nav-link">About</a></li>
                <li><a href="public/contact.php" class="nav-link">Contact</a></li>
                <li><a href="login.php" class="nav-link login-btn">Admin Login</a></li>
            </ul>
            <div class="nav-toggle" id="nav-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-text">
                <h1>Professional Event Management Made Simple</h1>
                <p>Create, manage, and execute unforgettable events with our comprehensive event management platform. From corporate conferences to social gatherings, we've got you covered.</p>
                <div class="hero-buttons">
                    <a href="public/events.php" class="btn btn-primary">Browse Events</a>
                    <a href="public/contact.php" class="btn btn-secondary">Get Started</a>
                </div>
            </div>
            <div class="hero-stats">
                <div class="stat-item">
                    <h3><?php echo $total_events; ?>+</h3>
                    <p>Upcoming Events</p>
                </div>
                <div class="stat-item">
                    <h3><?php echo $total_attendees; ?>+</h3>
                    <p>Happy Attendees</p>
                </div>
                <div class="stat-item">
                    <h3>100%</h3>
                    <p>Success Rate</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="section-header">
                <h2>Why Choose EventManager Pro?</h2>
                <p>Comprehensive event management solutions for every occasion</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📅</div>
                    <h3>Event Planning</h3>
                    <p>Streamlined event creation and management with intuitive tools and comprehensive planning features.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <h3>Attendee Management</h3>
                    <p>Efficient registration, check-in, and communication systems to manage your attendees seamlessly.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💰</div>
                    <h3>Payment Processing</h3>
                    <p>Secure payment handling with multiple payment methods and automated transaction management.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Analytics & Reports</h3>
                    <p>Detailed insights and comprehensive reporting to track your event success and performance.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🏢</div>
                    <h3>Vendor Management</h3>
                    <p>Coordinate with vendors and suppliers efficiently with our integrated vendor management system.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3>Mobile Responsive</h3>
                    <p>Access your event management tools from anywhere with our fully responsive mobile interface.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Upcoming Events Section -->
    <section class="upcoming-events">
        <div class="container">
            <div class="section-header">
                <h2>Upcoming Events</h2>
                <p>Don't miss out on these exciting upcoming events</p>
            </div>
            <div class="events-grid">
                <?php if (mysqli_num_rows($upcoming_events) > 0): ?>
                    <?php while ($event = mysqli_fetch_assoc($upcoming_events)): ?>
                        <div class="event-card">
                            <div class="event-date">
                                <span class="day"><?php echo date('d', strtotime($event['start_date'])); ?></span>
                                <span class="month"><?php echo date('M', strtotime($event['start_date'])); ?></span>
                            </div>
                            <div class="event-info">
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p class="event-description"><?php echo htmlspecialchars(substr($event['description'], 0, 100)); ?>...</p>
                                <div class="event-meta">
                                    <span class="location">📍 <?php echo htmlspecialchars($event['location']); ?></span>
                                    <span class="time">🕒 <?php echo date('g:i A', strtotime($event['start_date'])); ?></span>
                                </div>
                                <a href="public/event_details.php?id=<?php echo $event['id']; ?>" class="btn btn-accent">View Details</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-events">
                        <p>No upcoming events at the moment. Check back soon!</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="view-all">
                <a href="public/events.php" class="btn btn-primary">View All Events</a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Create Amazing Events?</h2>
                <p>Join thousands of event organizers who trust EventManager Pro for their event management needs.</p>
                <a href="public/contact.php" class="btn btn-primary">Get Started Today</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>EventManager Pro</h3>
                    <p>Professional event management solutions for every occasion. Making your events memorable and successful.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="home.php">Home</a></li>
                        <li><a href="public/events.php">Events</a></li>
                        <li><a href="public/about.php">About</a></li>
                        <li><a href="public/contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Services</h4>
                    <ul>
                        <li><a href="#">Event Planning</a></li>
                        <li><a href="#">Attendee Management</a></li>
                        <li><a href="#">Payment Processing</a></li>
                        <li><a href="#">Analytics & Reports</a></li>
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

            // Close menu when clicking on a link (mobile)
            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', () => {
                    navMenu.classList.remove('active');
                });
            });
        });
    </script>
</body>
</html>