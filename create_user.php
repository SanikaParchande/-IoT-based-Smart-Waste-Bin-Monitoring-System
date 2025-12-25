<?php
include("db.php");

// Create a regular user account
$username = 'user';
$password = 'user123';
$role = 'user';

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT IGNORE INTO users (username, role, password_hash) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $role, $passwordHash);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo "User account created successfully!<br>";
        echo "Username: $username<br>";
        echo "Password: $password<br>";
        echo "Role: $role<br>";
    } else {
        echo "User account already exists.<br>";
        
        // Check current password
        $check = $conn->prepare("SELECT password_hash FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $result = $check->get_result();
        $user = $result->fetch_assoc();
        
        if ($user) {
            $isValid = password_verify($password, $user['password_hash']);
            echo "Password verification: " . ($isValid ? "VALID" : "INVALID");
            
            if (!$isValid) {
                // Reset password
                $update = $conn->prepare("UPDATE users SET password_hash = ? WHERE username = ?");
                $update->bind_param("ss", $passwordHash, $username);
                $update->execute();
                echo "<br>Password has been reset!";
            }
        }
    }
} else {
    echo "Error creating user: " . $conn->error;
}

$stmt->close();
?>