<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    exit(json_encode(['error' => 'Unauthorized']));
}

include("db.php");

$action = $_POST['action'] ?? '';

if ($action == 'save') {
    $id = $_POST['id'] ?? 0;
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    
    if ($id) {
        // Update existing user
        if (!empty($password)) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username=?, role=?, password_hash=? WHERE id=?");
            $stmt->bind_param("sssi", $username, $role, $passwordHash, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username=?, role=? WHERE id=?");
            $stmt->bind_param("ssi", $username, $role, $id);
        }
    } else {
        // Insert new user
        if (empty($password)) {
            echo json_encode(['error' => 'Password is required for new users']);
            exit();
        }
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, role, password_hash) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $role, $passwordHash);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => $conn->error]);
    }
    
    $stmt->close();
} elseif ($action == 'delete') {
    $id = $_POST['id'] ?? 0;
    
    // Prevent self-deletion
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if ($user && $user['username'] == $_SESSION['username']) {
        echo json_encode(['error' => 'Cannot delete your own account']);
        exit();
    }
    
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => $conn->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['error' => 'Invalid action']);
}
?>