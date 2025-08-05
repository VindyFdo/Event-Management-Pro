<?php
require_once '../includes/db_connection.php';

$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($event_id <= 0) {
    header('Location: events.php');
    exit();
}

// Get event details
$event_query = "SELECT * FROM events WHERE id = $event_id AND start_date > NOW()";
$event_result = mysqli_query($conn, $event_query);

if (mysqli_num_rows($event_result) == 0) {
    header('Location: events.php');
    exit();
}

$event = mysqli_fetch_assoc($event_result);

// Get registered attendees count
$attendee_count_query = "SELECT COUNT(*) as count FROM attendees WHERE event_id = $event_id";
$attendee_count = mysqli_fetch_assoc(mysqli_query($conn, $attendee_count_query))['count'];

$available_spots = $event['capacity'] - $attendee_count;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['title']); ?> - EventManager Pro</title>
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
                <li><a href="events.php" class="nav-link">Events</a></li>
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

    <!-- Event Details -->
    <section class="event-details">
        <div class="container">
            <div class="event-header">
                <div class="event-title-section">
                    <h1><?php echo htmlspecialchars($event['title']); ?></h1>
                    <div class="event-status">
                        <?php if ($available_spots > 0): ?>
                            <span class="status-available">Available</span>
                        <?php else: ?>
                            <span class="status-full">Fully Booked</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="event-actions">
                    <?php if ($available_spots > 0): ?>
                        <a href="register.php?event_id=<?php echo $event['id']; ?>" class="btn btn-primary">Register Now</a>
                    <?php endif; ?>
                    <a href="events.php" class="btn btn-secondary">Back to Events</a>
                </div>
            </div>

            <div class="event-content">
                <div class="event-main">
                    <div class="event-description">
                        <h2>About This Event</h2>
                        <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                    </div>

                    <div class="event-info-grid">
                        <div class="info-card">
                            <div class="info-icon">📅</div>
                            <div class="info-content">
                                <h3>Date & Time</h3>
                                <p><strong><?php echo date('F d, Y', strtotime($event['start_date'])); ?></strong></p>
                                <p><?php echo date('g:i A', strtotime($event['start_date'])); ?> - <?php echo date('g:i A', strtotime($event['end_date'])); ?></p>
                            </div>
                        </div>

                        <div class="info-card">
                            <div class="info-icon">📍</div>
                            <div class="info-content">
                                <h3>Location</h3>
                                <p><?php echo htmlspecialchars($event['location']); ?></p>
                            </div>
                        </div>

                        <div class="info-card">
                            <div class="info-icon">👥</div>
                            <div class="info-content">
                                <h3>Capacity</h3>
                                <p><strong><?php echo $attendee_count; ?></strong> registered</p>
                                <p><?php echo $available_spots; ?> spots remaining</p>
                            </div>
                        </div>

                        <div class="info-card">
                            <div class="info-icon">🎫</div>
                            <div class="info-content">
                                <h3>Registration</h3>
                                <?php if ($available_spots > 0): ?>
                                    <p><strong>Open</strong></p>
                                    <p>Register now to secure your spot</p>
                                <?php else: ?>
                                    <p><strong>Closed</strong></p>
                                    <p>Event is fully booked</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="event-sidebar">
                    <div class="registration-card">
                        <h3>Event Registration</h3>
                        <?php if ($available_spots > 0): ?>
                            <div class="availability">
                                <p><strong><?php echo $available_spots; ?></strong> spots remaining</p>
                                <div class="progress-bar">
                                    <div class="progress" style="width: <?php echo ($attendee_count / $event['capacity']) * 100; ?>%"></div>
                                </div>
                            </div>
                            <a href="register.php?event_id=<?php echo $event['id']; ?>" class="btn btn-primary btn-full">Register Now</a>
                            <p class="registration-note">Registration is free and takes less than 2 minutes</p>
                        <?php else: ?>
                            <div class="fully-booked">
                                <p><strong>This event is fully booked</strong></p>
                                <p>Check out our other upcoming events</p>
                                <a href="events.php" class="btn btn-secondary btn-full">Browse Events</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="share-card">
                        <h3>Share This Event</h3>
                        <div class="share-buttons">
                            <a href="#" class="share-btn facebook" onclick="shareEvent('facebook')">📘 Facebook</a>
                            <a href="#" class="share-btn twitter" onclick="shareEvent('twitter')">🐦 Twitter</a>
                            <a href="#" class="share-btn linkedin" onclick="shareEvent('linkedin')">💼 LinkedIn</a>
                            <a href="#" class="share-btn email" onclick="shareEvent('email')">📧 Email</a>
                        </div>
                    </div>
                </div>
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

        function shareEvent(platform) {
            const url = window.location.href;
            const title = "<?php echo addslashes($event['title']); ?>";
            const text = "Check out this event: " + title;

            switch(platform) {
                case 'facebook':
                    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank');
                    break;
                case 'twitter':
                    window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`, '_blank');
                    break;
                case 'linkedin':
                    window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`, '_blank');
                    break;
                case 'email':
                    window.location.href = `mailto:?subject=${encodeURIComponent(title)}&body=${encodeURIComponent(text + ' ' + url)}`;
                    break;
            }
        }
    </script>
</body>
</html>