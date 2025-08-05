<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get current month and year
$current_month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$current_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Validate month and year
if ($current_month < 1 || $current_month > 12) {
    $current_month = date('n');
}
if ($current_year < 2020 || $current_year > 2030) {
    $current_year = date('Y');
}

// Calculate previous and next month
$prev_month = $current_month - 1;
$prev_year = $current_year;
if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year--;
}

$next_month = $current_month + 1;
$next_year = $current_year;
if ($next_month > 12) {
    $next_month = 1;
    $next_year++;
}

// Get events for the current month
$start_date = date('Y-m-01', mktime(0, 0, 0, $current_month, 1, $current_year));
$end_date = date('Y-m-t', mktime(0, 0, 0, $current_month, 1, $current_year));

$events_query = "SELECT * FROM events 
                WHERE (DATE(start_date) BETWEEN '$start_date' AND '$end_date') 
                   OR (DATE(end_date) BETWEEN '$start_date' AND '$end_date')
                   OR (DATE(start_date) < '$start_date' AND DATE(end_date) > '$end_date')
                ORDER BY start_date ASC";
$events_result = mysqli_query($conn, $events_query);

// Group events by date
$events_by_date = [];
while ($event = mysqli_fetch_assoc($events_result)) {
    $event_start = date('Y-m-d', strtotime($event['start_date']));
    $event_end = date('Y-m-d', strtotime($event['end_date']));
    
    // Add event to each date it spans
    $current_date = $event_start;
    while ($current_date <= $event_end) {
        if (!isset($events_by_date[$current_date])) {
            $events_by_date[$current_date] = [];
        }
        $events_by_date[$current_date][] = $event;
        $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
    }
}

// Calendar helper functions
function getDaysInMonth($month, $year) {
    return date('t', mktime(0, 0, 0, $month, 1, $year));
}

function getFirstDayOfMonth($month, $year) {
    return date('w', mktime(0, 0, 0, $month, 1, $year));
}

$days_in_month = getDaysInMonth($current_month, $current_year);
$first_day = getFirstDayOfMonth($current_month, $current_year);
$month_name = date('F Y', mktime(0, 0, 0, $current_month, 1, $current_year));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar - Event Management System</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .calendar-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .calendar-nav h2 {
            color: var(--primary);
            margin: 0;
        }
        
        .calendar-nav-buttons {
            display: flex;
            gap: 1rem;
        }
        
        .calendar-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 1px;
            background: var(--gray);
            border-radius: 10px;
            overflow: hidden;
        }
        
        .calendar-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--white);
            text-align: center;
            padding: 1rem;
            font-weight: bold;
        }
        
        .calendar-cell {
            background: var(--white);
            padding: 0.5rem;
            height: 120px;
            vertical-align: top;
            position: relative;
        }
        
        .calendar-cell.today {
            background: var(--bright);
            font-weight: bold;
        }
        
        .calendar-cell.other-month {
            background: var(--light-gray);
            color: var(--dark-gray);
        }
        
        .calendar-date {
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: var(--primary);
        }
        
        .calendar-event {
            background: var(--success);
            color: var(--white);
            padding: 0.25rem;
            margin-bottom: 0.25rem;
            border-radius: 3px;
            font-size: 0.7rem;
            cursor: pointer;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .calendar-event:hover {
            background: var(--info);
        }
        
        .event-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .event-modal-content {
            background-color: var(--white);
            margin: 15% auto;
            padding: 2rem;
            border-radius: 15px;
            width: 80%;
            max-width: 500px;
            position: relative;
        }
        
        .close-modal {
            position: absolute;
            right: 1rem;
            top: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--dark-gray);
        }
        
        .close-modal:hover {
            color: var(--error);
        }
    </style>
</head>
<body>
    <?php include '../includes/navigation.php'; ?>
    
    <div class="container">
        <div class="dashboard-header">
            <h1>Event Calendar</h1>
            <p>View and manage your events in calendar format</p>
        </div>

        <div class="calendar-container">
            <div class="calendar-nav">
                <div class="calendar-nav-buttons">
                    <a href="?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>" class="btn btn-secondary">← Previous</a>
                    <a href="?month=<?php echo date('n'); ?>&year=<?php echo date('Y'); ?>" class="btn btn-accent">Today</a>
                </div>
                <h2><?php echo $month_name; ?></h2>
                <div class="calendar-nav-buttons">
                    <a href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>" class="btn btn-secondary">Next →</a>
                    <a href="../events/create.php" class="btn btn-primary">New Event</a>
                </div>
            </div>

            <table class="calendar-table">
                <thead>
                    <tr>
                        <th class="calendar-header">Sunday</th>
                        <th class="calendar-header">Monday</th>
                        <th class="calendar-header">Tuesday</th>
                        <th class="calendar-header">Wednesday</th>
                        <th class="calendar-header">Thursday</th>
                        <th class="calendar-header">Friday</th>
                        <th class="calendar-header">Saturday</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $day = 1;
                    $week = 0;
                    
                    while ($week < 6 && $day <= $days_in_month) {
                        echo "<tr>";
                        
                        for ($dow = 0; $dow < 7; $dow++) {
                            if ($week == 0 && $dow < $first_day) {
                                // Previous month days
                                $prev_month_days = getDaysInMonth($prev_month, $prev_year);
                                $prev_day = $prev_month_days - ($first_day - $dow - 1);
                                echo "<td class='calendar-cell other-month'>";
                                echo "<div class='calendar-date'>$prev_day</div>";
                                echo "</td>";
                            } elseif ($day <= $days_in_month) {
                                // Current month days
                                $current_date = sprintf('%04d-%02d-%02d', $current_year, $current_month, $day);
                                $is_today = ($current_date == date('Y-m-d'));
                                $cell_class = $is_today ? 'calendar-cell today' : 'calendar-cell';
                                
                                echo "<td class='$cell_class'>";
                                echo "<div class='calendar-date'>$day</div>";
                                
                                // Show events for this day
                                if (isset($events_by_date[$current_date])) {
                                    foreach ($events_by_date[$current_date] as $event) {
                                        echo "<div class='calendar-event' onclick='showEventModal(" . json_encode($event) . ")'>";
                                        echo htmlspecialchars(substr($event['title'], 0, 15));
                                        echo "</div>";
                                    }
                                }
                                
                                echo "</td>";
                                $day++;
                            } else {
                                // Next month days
                                $next_day = $day - $days_in_month;
                                echo "<td class='calendar-cell other-month'>";
                                echo "<div class='calendar-date'>$next_day</div>";
                                echo "</td>";
                                $day++;
                            }
                        }
                        
                        echo "</tr>";
                        $week++;
                        
                        if ($day > $days_in_month) break;
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Event Modal -->
    <div id="eventModal" class="event-modal">
        <div class="event-modal-content">
            <span class="close-modal" onclick="closeEventModal()">&times;</span>
            <div id="modalContent"></div>
        </div>
    </div>

    <script>
        function showEventModal(event) {
            const modal = document.getElementById('eventModal');
            const modalContent = document.getElementById('modalContent');
            
            const startDate = new Date(event.start_date);
            const endDate = new Date(event.end_date);
            
            modalContent.innerHTML = `
                <h2 style="color: var(--primary); margin-bottom: 1rem;">${event.title}</h2>
                <p style="margin-bottom: 1rem;">${event.description}</p>
                <div style="margin-bottom: 1rem;">
                    <strong>📅 Date:</strong> ${startDate.toLocaleDateString()} ${startDate.toLocaleTimeString()} - ${endDate.toLocaleTimeString()}
                </div>
                <div style="margin-bottom: 1rem;">
                    <strong>📍 Location:</strong> ${event.location}
                </div>
                <div style="margin-bottom: 1rem;">
                    <strong>👥 Capacity:</strong> ${event.capacity}
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <a href="../events/view.php?id=${event.id}" class="btn btn-primary">View Details</a>
                    <a href="../events/edit.php?id=${event.id}" class="btn btn-secondary">Edit Event</a>
                </div>
            `;
            
            modal.style.display = 'block';
        }
        
        function closeEventModal() {
            document.getElementById('eventModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('eventModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>