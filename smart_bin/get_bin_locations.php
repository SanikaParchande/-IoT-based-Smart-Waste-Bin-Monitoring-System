<?php
session_start();
include('db.php');

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['username']) || !isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$role = $_SESSION['role'];
if ($role != 'admin' && $role != 'staff') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit();
}

// Fetch bin locations with latest fill level data
$query = "
    SELECT 
        b.bin_id,
        b.location,
        b.capacity,
        b.latitude,
        b.longitude,
        COALESCE(bd.fill_level, 0) as fill_level,
        bd.timestamp
    FROM bins b
    LEFT JOIN (
        SELECT bin_id, fill_level, timestamp,
               ROW_NUMBER() OVER (PARTITION BY bin_id ORDER BY timestamp DESC) as rn
        FROM bin_data
    ) bd ON b.bin_id = bd.bin_id AND bd.rn = 1
    ORDER BY b.bin_id
";

$result = $conn->query($query);
$bins = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $bins[] = [
            'bin_id' => $row['bin_id'],
            'location' => $row['location'],
            'capacity' => $row['capacity'],
            'latitude' => $row['latitude'],
            'longitude' => $row['longitude'],
            'fill_level' => $row['fill_level'],
            'timestamp' => $row['timestamp']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($bins);
?>

