# Event Management System

A comprehensive event management system built with PHP, HTML, CSS, and MySQL for managing events, attendees, vendors, payments, and more.

## Features

### 🎯 Core Modules
- **Event Management**: Create, edit, delete, and manage events with full details
- **Attendee Management**: Register attendees, track attendance, and manage communications
- **Vendor Management**: Maintain vendor database with contact information and services
- **Payment Processing**: Handle payments, track transactions, and manage revenue
- **Calendar System**: Interactive calendar view with drag-and-drop functionality
- **Reports & Analytics**: Comprehensive reporting with charts and statistics

### 🎨 Design Features
- **Modern UI**: Clean, professional design with custom color palette
- **Responsive Design**: Optimized for desktop, tablet, and mobile devices
- **Dark Theme**: Professional color scheme using navy and burgundy tones
- **Interactive Elements**: Hover effects, animations, and micro-interactions

### 🔐 Security Features
- **User Authentication**: Secure login/logout system
- **Session Management**: Proper session handling and security
- **Input Validation**: Comprehensive form validation and sanitization
- **SQL Injection Protection**: Parameterized queries and input sanitization

## Color Palette

- **Primary**: #03071E (Dark Navy)
- **Secondary**: #370617 (Deep Burgundy)  
- **Accent**: #6A040F (Red)
- **Supporting Colors**: #9D0208, #D00000, #DC2F02, #E85D04, #F48C06, #FAA307, #FFBA08

## Installation Instructions

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

### Setup Steps

1. **Download and Extract**
   - Download the project files
   - Extract to your web server directory (htdocs for XAMPP, www for WAMP)

2. **Database Setup**
   ```sql
   -- Import the database schema
   mysql -u root -p < database/schema.sql
   ```

3. **Configuration**
   - Edit `includes/db_connection.php` with your database credentials:
   ```php
   $host = 'localhost';
   $username = 'your_username';
   $password = 'your_password';
   $database = 'event_management';
   ```

4. **Directory Permissions**
   - Ensure the `logs` directory is writable
   ```bash
   chmod 755 logs/
   ```

5. **Access the System**
   - Open your web browser
   - Navigate to `http://localhost/your-project-folder/`
   - Default admin login: `admin` / `admin123`

## File Structure

```
event-management-system/
├── index.php                 # Dashboard/Home page
├── login.php                 # User authentication
├── register.php              # User registration
├── logout.php                # Logout functionality
├── css/
│   └── style.css             # Main stylesheet
├── includes/
│   ├── db_connection.php     # Database connection
│   └── navigation.php        # Navigation menu
├── events/
│   ├── index.php             # Events listing
│   ├── create.php            # Create new event
│   ├── edit.php              # Edit event
│   ├── view.php              # View event details
│   └── delete.php            # Delete event
├── attendees/
│   ├── index.php             # Attendees listing
│   ├── register.php          # Register attendee
│   ├── edit.php              # Edit attendee
│   ├── view.php              # View attendee details
│   ├── bulk_email.php        # Send bulk emails
│   └── mark_attended.php     # Mark attendance
├── vendors/
│   ├── index.php             # Vendors listing
│   ├── add.php               # Add new vendor
│   ├── edit.php              # Edit vendor
│   ├── view.php              # View vendor details
│   └── delete.php            # Delete vendor
├── calendar/
│   └── index.php             # Calendar view
├── payments/
│   ├── index.php             # Payments listing
│   ├── process.php           # Process payment
│   ├── view.php              # View payment details
│   └── update_status.php     # Update payment status
├── reports/
│   ├── index.php             # Reports dashboard
│   └── export.php            # Export functionality
├── database/
│   └── schema.sql            # Database schema
└── logs/                     # Error and activity logs
```

## Database Schema

### Main Tables
- **users**: User authentication and profiles
- **events**: Event information and details
- **attendees**: Event registrations and attendee data
- **vendors**: Vendor database with contact information
- **payments**: Payment transactions and records
- **tickets**: Ticket types and pricing
- **resources**: Equipment and resource management
- **activity_logs**: System activity tracking

### Relationships
- Events → Attendees (1:many)
- Events → Tickets (1:many)
- Attendees → Payments (1:many)
- Events → Vendors (many:many through event_vendors)
- Events → Resources (many:many through event_resources)

## User Guide

### Getting Started
1. **Login**: Use admin credentials or create a new account
2. **Dashboard**: Overview of key metrics and recent activities
3. **Navigation**: Use the top menu to access different modules

### Event Management
1. **Create Event**: Fill in event details, date, location, and capacity
2. **Manage Events**: Edit, view, or delete existing events
3. **Event Status**: Track upcoming, ongoing, and completed events

### Attendee Management
1. **Register Attendees**: Add attendees to events
2. **Track Attendance**: Mark attendees as present
3. **Communication**: Send bulk emails to attendees

### Vendor Management
1. **Add Vendors**: Maintain vendor database
2. **Services**: Track vendor services and ratings
3. **Event Assignment**: Assign vendors to specific events

### Payment Management
1. **Process Payments**: Handle payment transactions
2. **Track Status**: Monitor pending, completed, and failed payments
3. **Financial Reports**: View revenue and payment analytics

### Calendar & Scheduling
1. **Calendar View**: Visual representation of events
2. **Event Details**: Click events for quick information
3. **Navigation**: Browse different months and years

### Reports & Analytics
1. **Key Metrics**: View important performance indicators
2. **Charts**: Visual representation of data trends
3. **Export**: Download reports for external analysis

## Technical Details

### Security Measures
- Input sanitization for all form data
- SQL injection prevention
- Session-based authentication
- Password validation (simplified for practice)
- Activity logging for audit trails

### Browser Compatibility
- Modern browsers (Chrome, Firefox, Safari, Edge)
- Responsive design for mobile devices
- Touch-friendly interface elements

### Performance Optimization
- Efficient database queries
- Minimal external dependencies
- Optimized CSS and JavaScript
- Image optimization guidelines

## Troubleshooting

### Common Issues
1. **Database Connection Error**
   - Check database credentials in `includes/db_connection.php`
   - Ensure MySQL service is running
   - Verify database exists and is accessible

2. **Login Issues**
   - Default admin credentials: admin/admin123
   - Check users table in database
   - Verify session configuration

3. **Permission Errors**
   - Ensure web server has read/write permissions
   - Check file ownership and permissions
   - Verify logs directory is writable

### Error Logging
- Check `logs/error.log` for system errors
- Activity logs stored in database
- Enable PHP error reporting for development

## Customization

### Styling
- Modify `css/style.css` for design changes
- CSS variables for easy color scheme updates
- Responsive breakpoints for mobile optimization

### Functionality
- Add new modules following existing structure
- Extend database schema as needed
- Implement additional validation rules

### Reporting
- Add custom report types
- Integrate with external analytics tools
- Export to different formats (CSV, PDF)

## Support

For issues or questions:
1. Check this documentation first
2. Review error logs for specific issues
3. Verify database and file permissions
4. Test with default data and configuration

## License

This project is for educational and practice purposes. Feel free to modify and use as needed for learning PHP and web development concepts.