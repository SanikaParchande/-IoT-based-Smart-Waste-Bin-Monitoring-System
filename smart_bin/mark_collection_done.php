<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'staff') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

include("db.php");

$staffId = $_SESSION['id'];
$data = json_decode(file_get_contents('php://input'), true);
$binId = $data['bin_id'] ?? '';

if (empty($binId)) {
    echo json_encode(['error' => 'Bin ID is required']);
    exit();
}

// Verify that this bin is assigned to this staff member
$checkQuery = "SELECT * FROM staff_bin_assignments WHERE staff_id = ? AND bin_id = ? AND is_active = TRUE";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("is", $staffId, $binId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['error' => 'Bin not assigned to you or inactive']);
    exit();
}

// Get current fill level and gas status
$binDataQuery = "SELECT fill_level, gas_status FROM bin_data WHERE bin_id = ? ORDER BY timestamp DESC LIMIT 1";
$stmt2 = $conn->prepare($binDataQuery);
$stmt2->bind_param("s", $binId);
$stmt2->execute();
$binDataResult = $stmt2->get_result();
$binData = $binDataResult->fetch_assoc();

$fillLevelBefore = $binData['fill_level'] ?? 0;

// Update last_collection_date in staff_bin_assignments
$updateQuery = "UPDATE staff_bin_assignments SET last_collection_date = NOW() WHERE staff_id = ? AND bin_id = ?";
$stmt3 = $conn->prepare($updateQuery);
$stmt3->bind_param("is", $staffId, $binId);

// Insert into collection history
$historyQuery = "INSERT INTO collection_history (staff_id, bin_id, fill_level_before, notes) VALUES (?, ?, ?, ?)";
$stmt4 = $conn->prepare($historyQuery);
$notes = "Collection completed";
$stmt4->bind_param("isis", $staffId, $binId, $fillLevelBefore, $notes);

// Reset bin fill level to 0
$resetQuery = "INSERT INTO bin_data (bin_id, fill_level, gas_status) VALUES (?, 0, 'normal')";
$stmt5 = $conn->prepare($resetQuery);
$stmt5->bind_param("s", $binId);

if ($stmt3->execute() && $stmt4->execute() && $stmt5->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Failed to update collection']);
}
?>


