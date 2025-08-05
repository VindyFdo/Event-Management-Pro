-- Event Management System Database Schema
-- Created: 2025

-- Create database
CREATE DATABASE IF NOT EXISTS event_management;
USE event_management;

-- Users table for authentication
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

-- Events table
CREATE TABLE events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    location VARCHAR(255) NOT NULL,
    capacity INT NOT NULL DEFAULT 0,
    created_by INT NOT NULL,
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('draft', 'published', 'cancelled') DEFAULT 'published',
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_start_date (start_date),
    INDEX idx_status (status)
);

-- Ticket types table
CREATE TABLE tickets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) DEFAULT 0.00,
    quantity_available INT NOT NULL DEFAULT 0,
    quantity_sold INT DEFAULT 0,
    description TEXT,
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    INDEX idx_event_id (event_id)
);

-- Attendees table
CREATE TABLE attendees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    ticket_id INT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('registered', 'attended', 'cancelled') DEFAULT 'registered',
    notes TEXT,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE SET NULL,
    INDEX idx_event_id (event_id),
    INDEX idx_email (email),
    INDEX idx_status (status)
);

-- Vendors table
CREATE TABLE vendors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    services TEXT NOT NULL,
    website VARCHAR(255),
    rating DECIMAL(3,2) DEFAULT 0.00,
    notes TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_services (services(100))
);

-- Event vendors (many-to-many relationship)
CREATE TABLE event_vendors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    vendor_id INT NOT NULL,
    service_type VARCHAR(100) NOT NULL,
    cost DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE,
    UNIQUE KEY unique_event_vendor_service (event_id, vendor_id, service_type)
);

-- Payments table
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    attendee_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('credit_card', 'debit_card', 'paypal', 'bank_transfer', 'cash') DEFAULT 'credit_card',
    transaction_id VARCHAR(100),
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    notes TEXT,
    FOREIGN KEY (attendee_id) REFERENCES attendees(id) ON DELETE CASCADE,
    INDEX idx_attendee_id (attendee_id),
    INDEX idx_status (status),
    INDEX idx_payment_date (payment_date)
);

-- Resources table
CREATE TABLE resources (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type ENUM('equipment', 'venue', 'catering', 'transportation', 'other') NOT NULL,
    description TEXT,
    cost_per_unit DECIMAL(10,2) DEFAULT 0.00,
    availability_status ENUM('available', 'booked', 'maintenance') DEFAULT 'available',
    contact_info TEXT,
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Event resources (many-to-many relationship)
CREATE TABLE event_resources (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    resource_id INT NOT NULL,
    quantity_needed INT DEFAULT 1,
    cost DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('requested', 'confirmed', 'delivered', 'returned') DEFAULT 'requested',
    notes TEXT,
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE
);

-- Activity logs table
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_timestamp (timestamp)
);

-- Email templates table
CREATE TABLE email_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    body TEXT NOT NULL,
    type ENUM('registration', 'reminder', 'cancellation', 'update') NOT NULL,
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Email queue table
CREATE TABLE email_queue (
    id INT PRIMARY KEY AUTO_INCREMENT,
    recipient_email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    body TEXT NOT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    attempts INT DEFAULT 0,
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sent_date TIMESTAMP NULL,
    INDEX idx_status (status),
    INDEX idx_created_date (created_date)
);

-- Insert default admin user
INSERT INTO users (username, email, password, full_name, role) VALUES 
('admin', 'admin@eventmanager.com', 'admin123', 'System Administrator', 'admin');

-- Insert sample email templates
INSERT INTO email_templates (name, subject, body, type) VALUES 
('Registration Confirmation', 'Event Registration Confirmed', 
'Dear {name},\n\nThank you for registering for {event_title}.\n\nEvent Details:\nDate: {event_date}\nLocation: {event_location}\n\nWe look forward to seeing you there!\n\nBest regards,\nEvent Management Team', 
'registration'),

('Event Reminder', 'Reminder: {event_title} Tomorrow', 
'Dear {name},\n\nThis is a friendly reminder that {event_title} is scheduled for tomorrow.\n\nEvent Details:\nDate: {event_date}\nTime: {event_time}\nLocation: {event_location}\n\nSee you there!\n\nBest regards,\nEvent Management Team', 
'reminder');

-- Insert sample resources
INSERT INTO resources (name, type, description, cost_per_unit, availability_status) VALUES 
('Projector', 'equipment', 'HD projector with wireless connectivity', 50.00, 'available'),
('Sound System', 'equipment', 'Professional PA system with microphones', 100.00, 'available'),
('Conference Table', 'equipment', 'Large conference table for 12 people', 25.00, 'available'),
('Catering Package A', 'catering', 'Continental breakfast for 50 people', 15.00, 'available'),
('Catering Package B', 'catering', 'Lunch buffet for 50 people', 25.00, 'available');