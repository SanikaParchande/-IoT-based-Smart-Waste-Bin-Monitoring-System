<?php
include("db.php");

$accounts = [
    ['admin', 'admin123', 'admin'],
    ['user', 'user123', 'user']
];

foreach ($accounts as $account) {
    list($username, $password, $role) = $account;
    
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Delete if exists
    $conn->query("DELETE FROM users WHERE username = '$username'");
    
    // Insert fresh
    $stmt = $conn->prepare("INSERT INTO users (username, role, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $role, $passwordHash);
    
    if ($stmt->execute()) {
        echo "✅ $username account created successfully!<br>";
        echo "&nbsp;&nbsp;Username: <strong>$username</strong><br>";
        echo "&nbsp;&nbsp;Password: <strong>$password</strong><br>";
        echo "&nbsp;&nbsp;Role: <strong>$role</strong><br><br>";
    } else {
        echo "❌ Error creating $username: " . $conn->error . "<br>";
    }
    
    $stmt->close();
}

echo "<hr><h3>Login URLs:</h3>";
echo "Admin: <a href='index.php'>Login as Admin</a><br>";
echo "User: <a href='index.php'>Login as User</a><br>";
?>