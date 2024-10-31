<?php
$host = 'localhost';
$dbname = 'noc_tasks';
$username = 'root';
$password = '!kolakgrendul#';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$pdo->exec($sql);

// Tabel `tasks`
$sql = "CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    status VARCHAR(50),
    task_description TEXT,
    location_site VARCHAR(100),
    assignee VARCHAR(100),
    due_date DATE,
    notes TEXT,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    attachment_path VARCHAR(255),
    created_by INT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
)";
$pdo->exec($sql);
