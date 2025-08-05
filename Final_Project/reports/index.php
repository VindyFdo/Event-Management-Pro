<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get report data
$current_year = date('Y');
$current_month = date('m');

// Event statistics
$event_stats_query = "SELECT 
    COUNT(*) as total_events,
    COUNT(CASE WHEN YEAR(start_date) = $current_year THEN 1 END) as events_this_year,
    COUNT(CASE WHEN YEAR(start_date) = $current_year AND MONTH(start_date) = $current_month THEN 1 END) as events_this_month,
    COUNT(CASE WHEN start_date > NOW() THEN 1 END) as upcoming_events
    FROM events";
$event_stats = mysqli_fetch_assoc(mysqli_query($conn, $event_stats_query));

// Attendee statistics
$attendee_stats_query = "SELECT 
    COUNT(*) as total_attendees,
    COUNT(CASE WHEN YEAR(registration_date) = $current_year THEN 1 END) as attendees_this_year,
    COUNT(CASE WHEN status = 'attended' THEN 1 END) as attended_count,
    COUNT(CASE WHEN status = 'registered' THEN 1 END) as registered_count
    FROM attendees";
$attendee_stats = mysqli_fetch_assoc(mysqli_query($conn, $attendee_stats_query));

// Revenue statistics
$revenue_stats_query = "SELECT 
    SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_revenue,
    SUM(CASE WHEN status = 'completed' AND YEAR(payment_date) = $current_year THEN amount ELSE 0 END) as revenue_this_year,
    SUM(CASE WHEN status = 'completed' AND YEAR(payment_date) = $current_year AND MONTH(payment_date) = $current_month THEN amount ELSE 0 END) as revenue_this_month,
    AVG(CASE WHEN status = 'completed' THEN amount ELSE NULL END) as avg_payment
    FROM payments";
$revenue_stats = mysqli_fetch_assoc(mysqli_query($conn, $revenue_stats_query));

// Top events by attendance
$top_events_query = "SELECT e.title, e.start_date, COUNT(a.id) as attendee_count 
                    FROM events e 
                    LEFT JOIN attendees a ON e.id = a.event_id 
                    GROUP BY e.id 
                    ORDER BY attendee_count DESC 
                    LIMIT 5";
$top_events = mysqli_query($conn, $top_events_query);

// Monthly revenue data for chart
$monthly_revenue_query = "SELECT 
    MONTH(payment_date) as month,
    YEAR(payment_date) as year,
    SUM(amount) as revenue
    FROM payments 
    WHERE status = 'completed' AND YEAR(payment_date) = $current_year
    GROUP BY MONTH(payment_date), YEAR(payment_date)
    ORDER BY month";
$monthly_revenue = mysqli_query($conn, $monthly_revenue_query);

$revenue_data = array_fill(1, 12, 0);
while ($row = mysqli_fetch_assoc($monthly_revenue)) {
    $revenue_data[$row['month']] = (float)$row['revenue'];
}

// Recent activities
$recent_activities_query = "SELECT * FROM activity_logs ORDER BY timestamp DESC LIMIT 10";
$recent_activities = mysqli_query($conn, $recent_activities_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - Event Management System</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .report-section {
            margin-bottom: 3rem;
        }
        
        .chart-container {
            background: var(--white);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .report-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .activity-item {
            padding: 1rem;
            border-left: 4px solid var(--success);
            background: var(--light-gray);
            margin-bottom: 0.5rem;
            border-radius: 0 8px 8px 0;
        }
        
        .activity-time {
            color: var(--dark-gray);
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <?php include '../includes/navigation.php'; ?>
    
    <div class="container">
        <div class="dashboard-header">
            <h1>Reports & Analytics</h1>
            <p>Comprehensive insights into your event management performance</p>
        </div>

        <!-- Key Performance Indicators -->
        <div class="report-section">
            <h2 style="color: var(--primary); margin-bottom: 1.5rem;">Key Performance Indicators</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--success), var(--info));">📅</div>
                    <div class="stat-info">
                        <h3><?php echo $event_stats['total_events']; ?></h3>
                        <p>Total Events</p>
                        <small style="color: var(--dark-gray);"><?php echo $event_stats['events_this_year']; ?> this year</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--info), var(--light));">👥</div>
                    <div class="stat-info">
                        <h3><?php echo $attendee_stats['total_attendees']; ?></h3>
                        <p>Total Attendees</p>
                        <small style="color: var(--dark-gray);"><?php echo $attendee_stats['attended_count']; ?> attended</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--light), var(--bright));">💰</div>
                    <div class="stat-info">
                        <h3>$<?php echo number_format($revenue_stats['total_revenue'] ?? 0, 2); ?></h3>
                        <p>Total Revenue</p>
                        <small style="color: var(--dark-gray);">$<?php echo number_format($revenue_stats['revenue_this_year'] ?? 0, 2); ?> this year</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--warning), var(--error));">📈</div>
                    <div class="stat-info">
                        <h3><?php echo $event_stats['upcoming_events']; ?></h3>
                        <p>Upcoming Events</p>
                        <small style="color: var(--dark-gray);"><?php echo $event_stats['events_this_month']; ?> this month</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Data -->
        <div class="report-grid">
            <div>
                <!-- Revenue Chart -->
                <div class="chart-container">
                    <h3 style="color: var(--primary); margin-bottom: 1rem;">Monthly Revenue Trend (<?php echo $current_year; ?>)</h3>
                    <canvas id="revenueChart" width="800" height="400"></canvas>
                </div>

                <!-- Top Events -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Top Events by Attendance</h3>
                    </div>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Event Title</th>
                                    <th>Date</th>
                                    <th>Attendees</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($top_events) > 0): ?>
                                    <?php while ($event = mysqli_fetch_assoc($top_events)): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($event['title']); ?></strong></td>
                                            <td><?php echo date('M d, Y', strtotime($event['start_date'])); ?></td>
                                            <td>
                                                <span style="background: var(--success); color: white; padding: 0.25rem 0.5rem; border-radius: 4px;">
                                                    <?php echo $event['attendee_count']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" style="text-align: center; color: var(--dark-gray);">No events found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div>
                <!-- Recent Activities -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Activities</h3>
                    </div>
                    <div style="max-height: 500px; overflow-y: auto;">
                        <?php if (mysqli_num_rows($recent_activities) > 0): ?>
                            <?php while ($activity = mysqli_fetch_assoc($recent_activities)): ?>
                                <div class="activity-item">
                                    <strong><?php echo htmlspecialchars($activity['action']); ?></strong>
                                    <?php if ($activity['details']): ?>
                                        <br><small><?php echo htmlspecialchars($activity['details']); ?></small>
                                    <?php endif; ?>
                                    <div class="activity-time"><?php echo date('M d, Y g:i A', strtotime($activity['timestamp'])); ?></div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="text-align: center; color: var(--dark-gray); padding: 2rem;">No recent activities</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card" style="margin-top: 2rem;">
                    <div class="card-header">
                        <h3 class="card-title">Quick Statistics</h3>
                    </div>
                    <div style="padding: 1rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                            <span>Attendance Rate:</span>
                            <strong style="color: var(--success);">
                                <?php 
                                $attendance_rate = $attendee_stats['total_attendees'] > 0 ? 
                                    round(($attendee_stats['attended_count'] / $attendee_stats['total_attendees']) * 100, 1) : 0;
                                echo $attendance_rate . '%';
                                ?>
                            </strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                            <span>Average Payment:</span>
                            <strong style="color: var(--info);">$<?php echo number_format($revenue_stats['avg_payment'] ?? 0, 2); ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                            <span>This Month Revenue:</span>
                            <strong style="color: var(--light);">$<?php echo number_format($revenue_stats['revenue_this_month'] ?? 0, 2); ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Active Registrations:</span>
                            <strong style="color: var(--warning);"><?php echo $attendee_stats['registered_count']; ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Options -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Export Reports</h3>
            </div>
            <div style="padding: 1rem;">
                <p style="margin-bottom: 1rem; color: var(--dark-gray);">Download detailed reports for further analysis</p>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="export.php?type=events" class="btn btn-primary">Export Events</a>
                    <a href="export.php?type=attendees" class="btn btn-secondary">Export Attendees</a>
                    <a href="export.php?type=payments" class="btn btn-accent">Export Payments</a>
                    <a href="export.php?type=vendors" class="btn btn-warning">Export Vendors</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Revenue Chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueData = <?php echo json_encode(array_values($revenue_data)); ?>;
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Revenue ($)',
                    data: revenueData,
                    borderColor: '#E85D04',
                    backgroundColor: 'rgba(232, 93, 4, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#E85D04',
                    pointBorderColor: '#FFFFFF',
                    pointBorderWidth: 2,
                    pointRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    }
                },
                elements: {
                    point: {
                        hoverRadius: 8
                    }
                }
            }
        });
    </script>
</body>
</html>