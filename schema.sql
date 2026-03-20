-- RealityOS AI - Database Schema
-- Run this SQL in phpMyAdmin or MySQL CLI

CREATE DATABASE IF NOT EXISTS realityos_ai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE realityos_ai;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Simulations Table
CREATE TABLE IF NOT EXISTS simulations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    decision_title VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL,
    situation_description TEXT NOT NULL,
    option_a_title VARCHAR(255) NOT NULL,
    option_a_description TEXT NOT NULL,
    option_b_title VARCHAR(255) NOT NULL,
    option_b_description TEXT NOT NULL,
    user_goal TEXT,
    time_horizon VARCHAR(50) DEFAULT 'Mid-term',
    risk_tolerance VARCHAR(20) DEFAULT 'Medium',
    ai_response_json LONGTEXT,
    recommended_option VARCHAR(255),
    confidence_score TINYINT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity Logs Table
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes for performance
CREATE INDEX idx_simulations_user_id ON simulations(user_id);
CREATE INDEX idx_simulations_category ON simulations(category);
CREATE INDEX idx_activity_logs_user_id ON activity_logs(user_id);
