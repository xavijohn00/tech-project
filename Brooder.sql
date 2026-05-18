CREATE DATABASE IF NOT EXISTS brooder_system;
USE brooder_system;

-- Users
CREATE TABLE IF NOT EXISTS users (
    user_id    INT NOT NULL AUTO_INCREMENT,
    full_name  VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('admin','lecturer','student') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id)
);

-- Brooders
CREATE TABLE IF NOT EXISTS brooders (
    brooder_id INT NOT NULL AUTO_INCREMENT,
    name       VARCHAR(100) NOT NULL,
    location   VARCHAR(150) DEFAULT NULL,
    api_key    VARCHAR(64) NOT NULL UNIQUE,
    status     ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (brooder_id)
);

-- Student–Brooder assignment
CREATE TABLE IF NOT EXISTS student_brooder (
    id         INT NOT NULL AUTO_INCREMENT,
    student_id INT NOT NULL,
    brooder_id INT NOT NULL,
    assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_student (student_id),
    UNIQUE KEY unique_brooder (brooder_id),
    FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (brooder_id) REFERENCES brooders(brooder_id) ON DELETE CASCADE
);

-- Lecturer–Student assignment
CREATE TABLE IF NOT EXISTS lecturer_student (
    id          INT NOT NULL AUTO_INCREMENT,
    lecturer_id INT NOT NULL,
    student_id  INT NOT NULL,
    assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_pair (lecturer_id, student_id),
    FOREIGN KEY (lecturer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id)  REFERENCES users(user_id) ON DELETE CASCADE
);

-- Brooder settings (target temp set by student)
CREATE TABLE IF NOT EXISTS brooder_settings (
    setting_id  INT NOT NULL AUTO_INCREMENT,
    brooder_id  INT NOT NULL,
    student_id  INT NOT NULL,
    target_temp DECIMAL(5,2) NOT NULL,
    set_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (setting_id),
    FOREIGN KEY (brooder_id) REFERENCES brooders(brooder_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- System logs (per brooder, per user action)
CREATE TABLE IF NOT EXISTS system_logs (
    log_id      INT NOT NULL AUTO_INCREMENT,
    brooder_id  INT DEFAULT NULL,
    user_id     INT DEFAULT NULL,
    event_type  VARCHAR(50) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    logged_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (log_id),
    FOREIGN KEY (brooder_id) REFERENCES brooders(brooder_id) ON DELETE SET NULL,
    FOREIGN KEY (user_id)    REFERENCES users(user_id)       ON DELETE SET NULL
);

-- Sample data (plain text passwords — replace with hashes later)
INSERT INTO users (full_name, email, password, role) VALUES
('Admin User',    'admin@salcc.edu.lc',    'SALCC1234', 'admin'),
('Dr. Jane Smith','jsmith@salcc.edu.lc',   'SALCC1234', 'lecturer'),
('John Doe',      'jdoe@salcc.edu.lc',     'SALCC1234', 'student'),
('Mary Charles',  'mcharles@salcc.edu.lc', 'SALCC1234', 'student');

INSERT INTO brooders (name, location, api_key, status) VALUES
('Brooder A', 'Lab Room 1', 'brooder-a-key', 'active'),
('Brooder B', 'Lab Room 1', 'brooder-b-key', 'active');

INSERT INTO student_brooder (student_id, brooder_id) VALUES (3,1),(4,2);
INSERT INTO lecturer_student (lecturer_id, student_id) VALUES (2,3),(2,4);
