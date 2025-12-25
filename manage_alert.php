<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    exit(json_encode(['error' => 'Unauthorized']));
}

include("db.php");

$action = $_POST['action'] ?? '';

if ($action == 'create') {
    $title = $_POST['title'] ?? '';
    $message = $_POST['message'] ?? '';
    $level = $_POST['level'] ?? 'info';
    
    $stmt = $conn->prepare("INSERT INTO alerts (title, message, level) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $message, $level);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => $conn->error]);
    }
    
    $stmt->close();
} elseif ($action == 'delete') {
    $id = $_POST['id'] ?? 0;
    
    $stmt = $conn->prepare("DELETE FROM alerts WHERE id = ?");
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