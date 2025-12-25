<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    exit(json_encode(['error' => 'Unauthorized']));
}

include("db.php");

$action = $_POST['action'] ?? '';

if ($action == 'save') {
    $id = $_POST['id'] ?? 0;
    $binId = $_POST['bin_id'] ?? '';
    $location = $_POST['location'] ?? '';
    $capacity = $_POST['capacity'] ?? 0;
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;
    
    if ($id) {
        // Update existing bin
        $stmt = $conn->prepare("UPDATE bins SET bin_id=?, location=?, capacity=?, latitude=?, longitude=? WHERE id=?");
        $stmt->bind_param("ssiddi", $binId, $location, $capacity, $latitude, $longitude, $id);
    } else {
        // Insert new bin
        $stmt = $conn->prepare("INSERT INTO bins (bin_id, location, capacity, latitude, longitude) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssidd", $binId, $location, $capacity, $latitude, $longitude);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => $conn->error]);
    }
    
    $stmt->close();
} elseif ($action == 'delete') {
    $id = $_POST['id'] ?? 0;
    
    $stmt = $conn->prepare("DELETE FROM bins WHERE id = ?");
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