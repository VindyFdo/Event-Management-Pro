<?php
// Get the current directory to determine the correct path prefix
$current_dir = dirname($_SERVER['SCRIPT_NAME']);
$path_prefix = '';

// Determine how many levels deep we are
if (strpos($current_dir, '/') !== false) {
    $levels = substr_count($current_dir, '/');
    if ($levels > 0) {
        $path_prefix = str_repeat('../', $levels);
    }
}

// If we're in the root directory, no prefix needed
if ($current_dir === '/' || $current_dir === '') {
    $path_prefix = '';
}

// Special handling for admin directory
$is_in_admin = strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false;
$is_in_subdirectory = strpos($_SERVER['SCRIPT_NAME'], '/') !== false && !$is_in_admin;

// Adjust path prefix based on location
if ($is_in_admin) {
    $admin_prefix = '';
    $root_prefix = '../';
} else if ($is_in_subdirectory) {
    $admin_prefix = '../admin/';
    $root_prefix = '../';
} else {
    $admin_prefix = 'admin/';
    $root_prefix = '';
}
?>
<nav class="navbar">
    <div class="nav-container">
        <a href="<?php echo $admin_prefix; ?>index.php" class="logo">EventManager Pro - Admin</a>
        <ul class="nav-menu" id="nav-menu">
            <li><a href="<?php echo $admin_prefix; ?>index.php" class="nav-link">Dashboard</a></li>
            <li><a href="<?php echo $is_in_admin ? '../events/' : ($is_in_subdirectory ? '../events/' : 'events/'); ?>index.php" class="nav-link">Events</a></li>
            <li><a href="<?php echo $is_in_admin ? '../attendees/' : ($is_in_subdirectory ? '../attendees/' : 'attendees/'); ?>index.php" class="nav-link">Attendees</a></li>
            <li><a href="<?php echo $is_in_admin ? '../vendors/' : ($is_in_subdirectory ? '../vendors/' : 'vendors/'); ?>index.php" class="nav-link">Vendors</a></li>
            <li><a href="<?php echo $is_in_admin ? '../calendar/' : ($is_in_subdirectory ? '../calendar/' : 'calendar/'); ?>index.php" class="nav-link">Calendar</a></li>
            <li><a href="<?php echo $is_in_admin ? '../payments/' : ($is_in_subdirectory ? '../payments/' : 'payments/'); ?>index.php" class="nav-link">Payments</a></li>
            <li><a href="<?php echo $is_in_admin ? '../reports/' : ($is_in_subdirectory ? '../reports/' : 'reports/'); ?>index.php" class="nav-link">Reports</a></li>
            <li><a href="<?php echo $root_prefix; ?>home.php" class="nav-link">View Site</a></li>
            <li><a href="<?php echo $root_prefix; ?>logout.php" class="nav-link">Logout</a></li>
        </ul>
        <div class="nav-toggle" id="nav-toggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
</nav>

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