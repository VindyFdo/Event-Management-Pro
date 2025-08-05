<?php
require_once '../includes/db_connection.php';

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$error = '';
$success = '';

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

// Check availability
$attendee_count_query = "SELECT COUNT(*) as count FROM attendees WHERE event_id = $event_id";
$attendee_count = mysqli_fetch_assoc(mysqli_query($conn, $attendee_count_query))['count'];
$available_spots = $event['capacity'] - $attendee_count;

if ($available_spots <= 0) {
    header('Location: event_details.php?id=' . $event_id);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    
    // Validation
    if (empty($name) || empty($email)) {
        $error = 'Please fill in all required fields';
    } elseif (!validate_email($email)) {
        $error = 'Please enter a valid email address';
    } else {
        // Check if already registered
        $check_query = "SELECT id FROM attendees WHERE event_id = $event_id AND email = '$email'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = 'This email is already registered for this event';
        } else {
            // Re-check availability
            $current_count_query = "SELECT COUNT(*) as count FROM attendees WHERE event_id = $event_id";
            $current_count = mysqli_fetch_assoc(mysqli_query($conn, $current_count_query))['count'];
            
            if ($current_count >= $event['capacity']) {
                $error = 'Sorry, this event is now full';
            } else {
                $registration_date = date('Y-m-d H:i:s');
                
                $insert_query = "INSERT INTO attendees (event_id, name, email, phone, registration_date, status) 
                                VALUES ($event_id, '$name', '$email', '$phone', '$registration_date', 'registered')";
                
                if (mysqli_query($conn, $insert_query)) {
                    $success = 'Registration successful! You will receive a confirmation email shortly.';
                } else {
                    $error = 'Error processing registration. Please try again.';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for <?php echo htmlspecialchars($event['title']); ?> - EventManager Pro</title>
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

    <!-- Registration Form -->
    <section class="registration-section">
        <div class="container">
            <div class="registration-container">
                <div class="registration-header">
                    <h1>Event Registration</h1>
                    <p>Register for: <strong><?php echo htmlspecialchars($event['title']); ?></strong></p>
                </div>

                <div class="registration-content">
                    <div class="registration-form">
                        <?php if ($error): ?>
                            <div class="error-message">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="success-message">
                                <?php echo $success; ?>
                                <div style="margin-top: 1rem;">
                                    <a href="event_details.php?id=<?php echo $event_id; ?>" class="btn btn-primary">View Event Details</a>
                                    <a href="events.php" class="btn btn-secondary">Browse More Events</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <form method="POST">
                                <div class="form-group">
                                    <label for="name">Full Name *</label>
                                    <input type="text" id="name" name="name" class="form-control" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                    <small>We'll send your confirmation details to this email</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                    <small>Optional - for event updates and reminders</small>
                                </div>
                                
                                <div class="form-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" required>
                                        <span class="checkmark"></span>
                                        I agree to receive event updates and communications
                                    </label>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary btn-full">Complete Registration</button>
                                    <a href="event_details.php?id=<?php echo $event_id; ?>" class="btn btn-secondary btn-full">Cancel</a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>

                    <div class="event-summary">
                        <h3>Event Summary</h3>
                        <div class="summary-item">
                            <strong>Event:</strong>
                            <p><?php echo htmlspecialchars($event['title']); ?></p>
                        </div>
                        <div class="summary-item">
                            <strong>Date:</strong>
                            <p><?php echo date('F d, Y', strtotime($event['start_date'])); ?></p>
                        </div>
                        <div class="summary-item">
                            <strong>Time:</strong>
                            <p><?php echo date('g:i A', strtotime($event['start_date'])); ?> - <?php echo date('g:i A', strtotime($event['end_date'])); ?></p>
                        </div>
                        <div class="summary-item">
                            <strong>Location:</strong>
                            <p><?php echo htmlspecialchars($event['location']); ?></p>
                        </div>
                        <div class="summary-item">
                            <strong>Available Spots:</strong>
                            <p><?php echo $available_spots; ?> remaining</p>
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
    </script>
</body>
</html>