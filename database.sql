CREATE DATABASE IF NOT EXISTS fitishh;
USE fitishh;
-- Fitish Pro Database Schema
-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user','admin','superadmin') DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME,
    is_active TINYINT(1) DEFAULT 1
);

-- Workouts Table
CREATE TABLE workouts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    duration INT NOT NULL, -- in minutes
    distance FLOAT, -- in km
    calories FLOAT,
    met FLOAT,
    date DATE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Body Stats Table
CREATE TABLE stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    weight FLOAT NOT NULL, -- kg
    height FLOAT NOT NULL, -- cm
    age INT NOT NULL,
    gender ENUM('male','female','other') NOT NULL,
    bmi FLOAT,
    bmr FLOAT,
    tdee FLOAT,
    date DATE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Goals Table
CREATE TABLE goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    goal_type VARCHAR(50) NOT NULL,
    target_value FLOAT NOT NULL,
    current_value FLOAT,
    deadline DATE,
    is_achieved TINYINT(1) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Badges Table
CREATE TABLE badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description VARCHAR(255),
    criteria VARCHAR(255)
);

-- User Badges Table
CREATE TABLE user_badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    badge_id INT NOT NULL,
    awarded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE
);

-- Mood Tracker Table
CREATE TABLE moods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    workout_id INT,
    mood ENUM('Happy','Tired','Motivated','Sad','Neutral') NOT NULL,
    note VARCHAR(255),
    date DATE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (workout_id) REFERENCES workouts(id) ON DELETE SET NULL
);

-- System Settings Table
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value VARCHAR(255) NOT NULL
);

-- Admin Activity Logs Table
CREATE TABLE admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    target_user_id INT,
    details TEXT,
    ip_address VARCHAR(45),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Sample Dummy Data
INSERT INTO users (username, email, password, role) VALUES
('user1', 'user1@example.com', '$2y$10$examplehashuser', 'user');

INSERT INTO badges (name, description, criteria) VALUES
('Streak Starter', 'Logged workouts 7 days in a row', 'streak:7'),
('Distance Champ', 'Ran 50km total', 'distance:50'),
('Calorie Crusher', 'Burned 5000 calories', 'calories:5000');

-- Example system setting
INSERT INTO system_settings (setting_key, setting_value) VALUES
('allow_registration', '1'),
('superadmin_auth_key', 'EXAMPLEKEY123');
