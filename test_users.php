<?php
include("db.php");

echo "<h2>Checking Users in Database</h2>";

// Check if users table exists and has data
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows == 0) {
    echo "❌ Users table doesn't exist!<br>";
} else {
    echo "✅ Users table exists<br>";
}

// List all users
$users = $conn->query("SELECT username, role, password_hash FROM users");
echo "<h3>Current Users:</h3>";
if ($users->num_rows > 0) {
    while($user = $users->fetch_assoc()) {
        echo "Username: <strong>" . $user['username'] . "</strong><br>";
        echo "Role: " . $user['role'] . "<br>";
        echo "Password Hash: " . ($user['password_hash'] ? 'SET' : 'MISSING') . "<br><br>";
    }
} else {
    echo "❌ No users found in database!<br>";
}

// Test password verification
echo "<h3>Password Test:</h3>";
$test_user = $conn->query("SELECT password_hash FROM users WHERE username = 'user'");
if ($test_user->num_rows > 0) {
    $user_data = $test_user->fetch_assoc();
    $is_valid = password_verify('user123', $user_data['password_hash']);
    echo "Password 'user123' verification: " . ($is_valid ? "✅ VALID" : "❌ INVALID") . "<br>";
    
    if (!$is_valid) {
        echo "Let's reset the password...<br>";
        $new_hash = password_hash('user123', PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET password_hash = '$new_hash' WHERE username = 'user'");
        echo "Password reset complete!<br>";
    }
} else {
    echo "❌ User 'user' not found!<br>";
}
?>