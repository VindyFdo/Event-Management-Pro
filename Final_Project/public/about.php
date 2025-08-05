<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - EventManager Pro</title>
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
                <li><a href="about.php" class="nav-link active">About</a></li>
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
            <h1>About EventManager Pro</h1>
            <p>Your trusted partner in creating memorable events</p>
        </div>
    </section>

    <!-- About Content -->
    <section class="about-content">
        <div class="container">
            <div class="about-intro">
                <h2>Who We Are</h2>
                <p>EventManager Pro is a comprehensive event management platform designed to simplify the complex process of organizing, managing, and executing successful events. Whether you're planning a corporate conference, social gathering, or community event, our platform provides all the tools you need to create memorable experiences.</p>
            </div>

            <div class="about-features">
                <div class="feature-grid">
                    <div class="feature-item">
                        <div class="feature-icon">🎯</div>
                        <h3>Our Mission</h3>
                        <p>To empower event organizers with intuitive, powerful tools that transform event planning from a stressful task into an enjoyable creative process.</p>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">👁️</div>
                        <h3>Our Vision</h3>
                        <p>To become the leading event management platform that connects communities and creates lasting memories through exceptional events.</p>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">⭐</div>
                        <h3>Our Values</h3>
                        <p>Innovation, reliability, user-centric design, and commitment to helping our users create extraordinary events that bring people together.</p>
                    </div>
                </div>
            </div>

            <div class="about-stats">
                <h2>Our Impact</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>1000+</h3>
                        <p>Events Managed</p>
                    </div>
                    <div class="stat-card">
                        <h3>50,000+</h3>
                        <p>Attendees Served</p>
                    </div>
                    <div class="stat-card">
                        <h3>500+</h3>
                        <p>Happy Organizers</p>
                    </div>
                    <div class="stat-card">
                        <h3>99%</h3>
                        <p>Success Rate</p>
                    </div>
                </div>
            </div>

            <div class="about-team">
                <h2>Why Choose Us?</h2>
                <div class="team-grid">
                    <div class="team-item">
                        <h3>🚀 Easy to Use</h3>
                        <p>Intuitive interface designed for both beginners and experienced event planners.</p>
                    </div>
                    <div class="team-item">
                        <h3>🔒 Secure & Reliable</h3>
                        <p>Enterprise-grade security to protect your data and your attendees' information.</p>
                    </div>
                    <div class="team-item">
                        <h3>📱 Mobile Friendly</h3>
                        <p>Manage your events on the go with our fully responsive mobile interface.</p>
                    </div>
                    <div class="team-item">
                        <h3>🎨 Customizable</h3>
                        <p>Tailor the platform to match your brand and event requirements.</p>
                    </div>
                    <div class="team-item">
                        <h3>📊 Analytics</h3>
                        <p>Comprehensive reporting and analytics to measure your event success.</p>
                    </div>
                    <div class="team-item">
                        <h3>🤝 Support</h3>
                        <p>Dedicated customer support to help you every step of the way.</p>
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