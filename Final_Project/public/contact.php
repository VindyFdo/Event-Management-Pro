<?php
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $subject = htmlspecialchars(trim($_POST['subject']));
    $message = htmlspecialchars(trim($_POST['message']));
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // In a real application, you would send the email here
        $success = 'Thank you for your message! We will get back to you soon.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - EventManager Pro</title>
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
                <li><a href="contact.php" class="nav-link active">Contact</a></li>
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
            <h1>Contact Us</h1>
            <p>Get in touch with our team</p>
        </div>
    </section>

    <!-- Contact Content -->
    <section class="contact-content">
        <div class="container">
            <div class="contact-grid">
                <div class="contact-info">
                    <h2>Get In Touch</h2>
                    <p>Have questions about our event management platform? Want to discuss your event needs? We're here to help!</p>
                    
                    <div class="contact-methods">
                        <div class="contact-method">
                            <div class="method-icon">📧</div>
                            <div class="method-info">
                                <h3>Email</h3>
                                <p>info@eventmanagerpro.com</p>
                                <p>support@eventmanagerpro.com</p>
                            </div>
                        </div>
                        
                        <div class="contact-method">
                            <div class="method-icon">📞</div>
                            <div class="method-info">
                                <h3>Phone</h3>
                                <p>+1 (555) 123-4567</p>
                                <p>Mon-Fri: 9AM-6PM EST</p>
                            </div>
                        </div>
                        
                        <div class="contact-method">
                            <div class="method-icon">📍</div>
                            <div class="method-info">
                                <h3>Address</h3>
                                <p>123 Event Street</p>
                                <p>City, State 12345</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="business-hours">
                        <h3>Business Hours</h3>
                        <ul>
                            <li>Monday - Friday: 9:00 AM - 6:00 PM</li>
                            <li>Saturday: 10:00 AM - 4:00 PM</li>
                            <li>Sunday: Closed</li>
                        </ul>
                    </div>
                </div>

                <div class="contact-form">
                    <h2>Send Us a Message</h2>
                    
                    <?php if ($error): ?>
                        <div class="error-message">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="success-message">
                            <?php echo $success; ?>
                        </div>
                    <?php else: ?>
                        <form method="POST">
                            <div class="form-group">
                                <label for="name">Full Name *</label>
                                <input type="text" id="name" name="name" class="form-control" value="<?php echo isset($_POST['name']) ? $_POST['name'] : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="subject">Subject *</label>
                                <select id="subject" name="subject" class="form-control" required>
                                    <option value="">Select a subject...</option>
                                    <option value="General Inquiry" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'General Inquiry') ? 'selected' : ''; ?>>General Inquiry</option>
                                    <option value="Event Planning" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Event Planning') ? 'selected' : ''; ?>>Event Planning</option>
                                    <option value="Technical Support" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Technical Support') ? 'selected' : ''; ?>>Technical Support</option>
                                    <option value="Partnership" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Partnership') ? 'selected' : ''; ?>>Partnership Opportunities</option>
                                    <option value="Feedback" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Feedback') ? 'selected' : ''; ?>>Feedback</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="message">Message *</label>
                                <textarea id="message" name="message" class="form-control" rows="6" required><?php echo isset($_POST['message']) ? $_POST['message'] : ''; ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-full">Send Message</button>
                        </form>
                    <?php endif; ?>
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
                navToggle.classList.toggle('active');
                navMenu.classList.toggle('active');
            });

            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', () => {
                    navMenu.classList.remove('active');
                    navToggle.classList.remove('active');
                });
            });
        });
    </script>
</body>
</html>