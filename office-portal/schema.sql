-- Office Portal database schema
-- Run this in phpMyAdmin (or via mysql client) on your `login_system` database.
-- Existing `users` table is extended with department_id + designation.

CREATE DATABASE IF NOT EXISTS login_system;
USE login_system;

-- ---------- Departments ----------
CREATE TABLE IF NOT EXISTS departments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------- Users (extend existing table) ----------
-- If your `users` table already exists from before, run these ALTERs.
-- If it does not exist yet, the CREATE TABLE below will make it fresh.
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('employee','admin') NOT NULL DEFAULT 'employee',
  department_id INT DEFAULT NULL,
  designation VARCHAR(100) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  last_login DATETIME DEFAULT NULL,
  FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

-- If `users` already existed without these columns, uncomment and run:
-- ALTER TABLE users ADD COLUMN department_id INT DEFAULT NULL AFTER role;
-- ALTER TABLE users ADD COLUMN designation VARCHAR(100) DEFAULT NULL AFTER department_id;
-- ALTER TABLE users ADD CONSTRAINT fk_users_dept FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL;

-- ---------- Attendance ----------
CREATE TABLE IF NOT EXISTS attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  work_date DATE NOT NULL,
  check_in TIME DEFAULT NULL,
  check_out TIME DEFAULT NULL,
  status ENUM('present','late','half_day') NOT NULL DEFAULT 'present',
  UNIQUE KEY uniq_user_date (user_id, work_date),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ---------- Leaves ----------
CREATE TABLE IF NOT EXISTS leaves (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  leave_type ENUM('sick','casual','annual','other') NOT NULL DEFAULT 'casual',
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  reason TEXT,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  reviewed_by INT DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ---------- Tasks ----------
CREATE TABLE IF NOT EXISTS tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  description TEXT,
  assigned_to INT NOT NULL,
  assigned_by INT NOT NULL,
  status ENUM('pending','in_progress','completed') NOT NULL DEFAULT 'pending',
  due_date DATE DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE CASCADE
);

-- ---------- Announcements ----------
CREATE TABLE IF NOT EXISTS announcements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  content TEXT NOT NULL,
  posted_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE CASCADE
);

-- ---------- Documents ----------
CREATE TABLE IF NOT EXISTS documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  filename VARCHAR(255) NOT NULL,
  filepath VARCHAR(255) NOT NULL,
  uploaded_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
);

-- ---------- Events ----------
CREATE TABLE IF NOT EXISTS events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  description TEXT,
  event_date DATE NOT NULL,
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- ---------- Settings (single row) ----------
CREATE TABLE IF NOT EXISTS settings (
  id INT PRIMARY KEY DEFAULT 1,
  company_name VARCHAR(150) DEFAULT 'My Company',
  office_start_time TIME DEFAULT '09:00:00',
  office_end_time TIME DEFAULT '18:00:00'
);
INSERT IGNORE INTO settings (id, company_name) VALUES (1, 'My Company');

-- ---------- Make yourself an admin ----------
-- 1. Register a normal account first through register.php
-- 2. Then run this (replace with your email):
-- UPDATE users SET role='admin' WHERE email='you@example.com';
