<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "smartbin_db";

/* STEP 1: Connect to MySQL server ONLY */
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("MySQL connection failed: " . $conn->connect_error);
}

/* STEP 2: Create database */
$conn->query(
    "CREATE DATABASE IF NOT EXISTS `$dbname`
     CHARACTER SET utf8mb4
     COLLATE utf8mb4_unicode_ci"
);

/* STEP 3: Select database */
$conn->select_db($dbname);

// ---------------------------
// CREATE TABLES
// ---------------------------
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        email VARCHAR(255) NULL,
        mobile VARCHAR(20) NULL,
        location VARCHAR(255) NULL,
        role ENUM('admin','staff','user') NOT NULL DEFAULT 'user',
        password_hash VARCHAR(255) NULL,
        points INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    "CREATE TABLE IF NOT EXISTS bins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        bin_id VARCHAR(50) NOT NULL UNIQUE,
        location VARCHAR(255) NOT NULL,
        capacity INT NOT NULL DEFAULT 0,
        latitude DECIMAL(10,7) NULL,
        longitude DECIMAL(10,7) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    "CREATE TABLE IF NOT EXISTS bin_data (
        id INT AUTO_INCREMENT PRIMARY KEY,
        bin_id VARCHAR(50) NOT NULL,
        user_id INT NULL,
        fill_level INT NOT NULL,
        gas_status VARCHAR(50) NULL,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (bin_id),
        FOREIGN KEY (bin_id) REFERENCES bins(bin_id) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    "CREATE TABLE IF NOT EXISTS alerts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        level ENUM('critical','warning','info') NOT NULL DEFAULT 'info',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    "CREATE TABLE IF NOT EXISTS reward_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        action VARCHAR(255) NOT NULL,
        points INT NOT NULL,
        date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    "CREATE TABLE IF NOT EXISTS staff_bin_assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        staff_id INT NOT NULL,
        bin_id VARCHAR(50) NOT NULL,
        assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_active BOOLEAN DEFAULT TRUE,
        last_collection_date TIMESTAMP NULL,
        FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (bin_id) REFERENCES bins(bin_id) ON DELETE CASCADE,
        UNIQUE KEY unique_assignment (staff_id, bin_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    "CREATE TABLE IF NOT EXISTS collection_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        staff_id INT NOT NULL,
        bin_id VARCHAR(50) NOT NULL,
        collected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fill_level_before INT NULL,
        fill_level_after INT NULL,
        notes TEXT NULL,
        FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (bin_id) REFERENCES bins(bin_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

foreach ($tables as $sql) {
    $conn->query($sql);
}

// ---------------------------
// DEFAULT USERS
// ---------------------------
$users = [
    ['admin', 'admin@smartbin.com', '9876543210', 'Admin Office', 'admin123', 'admin'],
    ['staff', 'staff@smartbin.com', '9123456780', 'Park', 'staff123', 'staff'],
    ['user', 'user@smartbin.com', '9988776655', 'Main Street', 'user123', 'user']
];

foreach ($users as $u) {
    list($username, $email, $mobile, $location, $password, $role) = $u;
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $conn->query("DELETE FROM users WHERE username='$username'");
    $stmt = $conn->prepare("INSERT INTO users (username, email, mobile, location, role, password_hash, points) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $points = ($role == 'user') ? 100 : 0;
    $stmt->bind_param("ssssssi", $username, $email, $mobile, $location, $role, $passwordHash, $points);
    $stmt->execute();
    $stmt->close();
}

// ---------------------------
// SAMPLE BINS
// ---------------------------
$bins = [
    ['BIN001','Main Gate',100,40.7128,-74.0060],
    ['BIN002','Park Area',100,40.7589,-73.9851],
    ['BIN003','Cafeteria',100,40.7505,-73.9934]
];
foreach ($bins as $b) {
    list($bin_id, $location, $capacity, $lat, $lng) = $b;
    $stmt = $conn->prepare("INSERT IGNORE INTO bins (bin_id, location, capacity, latitude, longitude) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssidd", $bin_id, $location, $capacity, $lat, $lng);
    $stmt->execute();
    $stmt->close();
}

// ---------------------------
// SAMPLE BIN DATA
// ---------------------------
$result = $conn->query("SELECT id FROM users WHERE username='user' ORDER BY id DESC LIMIT 1");
$userId = $result->fetch_assoc()['id'];
$bin_data = [
    ['BIN001', 80, 'normal'],
    ['BIN002', 90, 'normal'],
    ['BIN003', 60, 'normal']
];
foreach ($bin_data as $bd) {
    list($bin_id, $fill, $gas) = $bd;
    $stmt = $conn->prepare("INSERT INTO bin_data (bin_id, user_id, fill_level, gas_status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siis", $bin_id, $userId, $fill, $gas);
    $stmt->execute();
    $stmt->close();
}

// ---------------------------
// SAMPLE ALERTS
// ---------------------------
$alerts = [
    ['Bin Almost Full','BIN002 is 90% full. Needs attention.','warning'],
    ['Gas Level Alert','BIN001 detected high gas concentration','critical']
];
foreach ($alerts as $a) {
    list($title, $message, $level) = $a;
    $stmt = $conn->prepare("INSERT INTO alerts (title, message, level) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $message, $level);
    $stmt->execute();
    $stmt->close();
}

// ---------------------------
// SAMPLE REWARD HISTORY
// ---------------------------
$reward_history = [
    ['First Login', 1],
    ['Used Dustbin BIN001', 5],
    ['Used Dustbin BIN002', 5]
];
foreach ($reward_history as $r) {
    list($action, $points) = $r;
    $stmt = $conn->prepare("INSERT INTO reward_history (user_id, action, points) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $userId, $action, $points);
    $stmt->execute();
    $stmt->close();
}

// ---------------------------
// FUNCTION: ADD REWARD
// ---------------------------
function addReward($conn, $userId, $action, $points) {
    $stmt = $conn->prepare("INSERT INTO reward_history (user_id, action, points) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $userId, $action, $points);
    $stmt->execute();
    $stmt->close();
    $conn->query("UPDATE users SET points = points + $points WHERE id = $userId");
}
?>
