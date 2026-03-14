-- Digital Document Verification and Tracking System
-- Database: dvts_db

CREATE DATABASE IF NOT EXISTS dvts_db;
USE dvts_db;

-- Students table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    department VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Faculty (Admin) table
CREATE TABLE IF NOT EXISTS faculty (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    department VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Recruiters table
CREATE TABLE IF NOT EXISTS recruiters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Documents table
CREATE TABLE IF NOT EXISTS documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL,
    document_name VARCHAR(200) NOT NULL,
    document_type VARCHAR(50) NOT NULL,
    file_path VARCHAR(300) NOT NULL,
    status ENUM('Pending','Verified','Rejected') DEFAULT 'Pending',
    verified_by VARCHAR(100) DEFAULT NULL,
    verified_at TIMESTAMP NULL DEFAULT NULL,
    rejection_reason TEXT DEFAULT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id)
);

-- Recruiter visits table
CREATE TABLE IF NOT EXISTS recruiter_visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL,
    company_name VARCHAR(100) NOT NULL,
    visited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id)
);

-- Sample data
INSERT INTO faculty (name, email, password, department) VALUES
('Dr. Ramesh Kumar', 'faculty@college.edu', MD5('faculty123'), 'Computer Science'),
('Prof. Anitha Raj', 'admin@college.edu', MD5('admin123'), 'BCA Department');

INSERT INTO students (student_id, name, email, department) VALUES
('23IABCA120', 'Priya T', 'priya@student.edu', 'BCA');

INSERT INTO recruiters (company_name, email) VALUES
('TCS', 'hr@tcs.com'),
('Infosys', 'hr@infosys.com');
