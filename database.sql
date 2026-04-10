-- Blood Donation Management System Database
-- University/Campus Project

CREATE DATABASE IF NOT EXISTS blood_donation_db;
USE blood_donation_db;

-- Users Table (Donors & Admins)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    blood_group ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
    age INT,
    gender ENUM('Male','Female','Other'),
    department VARCHAR(100),
    student_id VARCHAR(50),
    address TEXT,
    is_available TINYINT(1) DEFAULT 1,
    role ENUM('donor','admin') DEFAULT 'donor',
    last_donation DATE,
    profile_pic VARCHAR(255) DEFAULT 'default.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Blood Requests Table
CREATE TABLE IF NOT EXISTS blood_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requester_name VARCHAR(100) NOT NULL,
    requester_email VARCHAR(100),
    requester_phone VARCHAR(15) NOT NULL,
    patient_name VARCHAR(100) NOT NULL,
    blood_group ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
    units_needed INT DEFAULT 1,
    hospital_name VARCHAR(200),
    urgency ENUM('Normal','Urgent','Critical') DEFAULT 'Normal',
    required_date DATE,
    message TEXT,
    status ENUM('Pending','Fulfilled','Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Donations Log Table
CREATE TABLE IF NOT EXISTS donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT,
    request_id INT,
    donation_date DATE NOT NULL,
    units INT DEFAULT 1,
    hospital VARCHAR(200),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (request_id) REFERENCES blood_requests(id) ON DELETE SET NULL
);

-- Blood Inventory Table
CREATE TABLE IF NOT EXISTS blood_inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blood_group ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL UNIQUE,
    units_available INT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Events/Drives Table
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time TIME,
    venue VARCHAR(200),
    organizer VARCHAR(100),
    status ENUM('Upcoming','Ongoing','Completed','Cancelled') DEFAULT 'Upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Event Registrations
CREATE TABLE IF NOT EXISTS event_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT,
    user_id INT,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_reg (event_id, user_id)
);

-- Initialize blood inventory
INSERT IGNORE INTO blood_inventory (blood_group, units_available) VALUES
('A+', 0), ('A-', 0), ('B+', 0), ('B-', 0),
('AB+', 0), ('AB-', 0), ('O+', 0), ('O-', 0);

-- Default admin account (password: admin123)
INSERT IGNORE INTO users (full_name, email, password, blood_group, role, department) VALUES
('Admin User', 'admin@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'O+', 'admin', 'Administration');

-- Sample donors
INSERT IGNORE INTO users (full_name, email, password, phone, blood_group, age, gender, department, student_id, is_available) VALUES
('Rahul Sharma', 'rahul@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543210', 'B+', 21, 'Male', 'Computer Science', 'CS2021001', 1),
('Priya Singh', 'priya@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543211', 'A+', 20, 'Female', 'Electronics', 'EC2021002', 1),
('Amit Kumar', 'amit@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543212', 'O+', 22, 'Male', 'Mechanical', 'ME2020003', 1),
('Sneha Patel', 'sneha@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543213', 'AB+', 21, 'Female', 'Civil', 'CE2021004', 1);

-- Sample inventory
UPDATE blood_inventory SET units_available = 12 WHERE blood_group = 'A+';
UPDATE blood_inventory SET units_available = 5 WHERE blood_group = 'A-';
UPDATE blood_inventory SET units_available = 18 WHERE blood_group = 'B+';
UPDATE blood_inventory SET units_available = 3 WHERE blood_group = 'B-';
UPDATE blood_inventory SET units_available = 8 WHERE blood_group = 'AB+';
UPDATE blood_inventory SET units_available = 2 WHERE blood_group = 'AB-';
UPDATE blood_inventory SET units_available = 22 WHERE blood_group = 'O+';
UPDATE blood_inventory SET units_available = 7 WHERE blood_group = 'O-';

-- Sample events
INSERT INTO events (title, description, event_date, event_time, venue, organizer, status) VALUES
('Annual Blood Donation Drive 2025', 'Join us for our annual campus blood donation drive. Every drop counts!', '2025-05-15', '09:00:00', 'University Auditorium', 'NSS Club', 'Upcoming'),
('Emergency Blood Camp', 'Special blood donation camp organized by Medical Committee', '2025-04-20', '10:00:00', 'Health Centre, Block C', 'Medical Committee', 'Upcoming');
