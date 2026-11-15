CREATE DATABASE IF NOT EXISTS powerreport
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE powerreport;

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS reports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_code VARCHAR(40) NOT NULL UNIQUE,
    name VARCHAR(120) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    fault_type VARCHAR(80) NOT NULL,
    location VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    report_date DATE NOT NULL,
    status ENUM('Pending', 'In Progress', 'Resolved')
        NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);