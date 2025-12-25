<?php
session_start();
if (!isset($_SESSION['username'])) {
    exit(json_encode(['error' => 'Not authenticated']));
}

include("db.php");

// Get time range from request
$range = isset($_GET['range']) ? $_GET['range'] : 10;

// Adjust range based on user role
if ($_SESSION['role'] != 'admin' && $range == '50') {
    $range = '10'; // Users can't view 50 readings
}

// Build query based on range
if ($range == 'day') {
    $sql = "SELECT * FROM bin_data WHERE timestamp >= NOW() - INTERVAL 1 DAY ORDER BY timestamp DESC";
} else {
    $limit = intval($range);
    $sql = "SELECT * FROM bin_data ORDER BY timestamp DESC LIMIT $limit";
}

$result = $conn->query($sql);

$labels = [];
$fillLevels = [];
while($row = $result->fetch_assoc()) {
    $labels[] = $row['timestamp'];
    $fillLevels[] = $row['fill_level'];
}

// Get recent alerts based on role
if ($_SESSION['role'] == 'admin') {
    $alertResult = $conn->query("SELECT * FROM alerts ORDER BY created_at DESC LIMIT 10");
} else {
    $alertResult = $conn->query("SELECT * FROM alerts WHERE created_at >= NOW() - INTERVAL 1 DAY ORDER BY created_at DESC LIMIT 5");
}

$alerts = [];
while($row = $alertResult->fetch_assoc()) {
    $alerts[] = $row;
}

// Get bin status
$binStatus = [];
$binStatusResult = $conn->query("
    SELECT b.bin_id, b.location, d.fill_level, d.gas_status, d.timestamp 
    FROM bins b 
    LEFT JOIN (SELECT bin_id, MAX(timestamp) as latest FROM bin_data GROUP BY bin_id) latest_data 
      ON b.bin_id = latest_data.bin_id
    LEFT JOIN bin_data d ON latest_data.bin_id = d.bin_id AND latest_data.latest = d.timestamp
    ORDER BY b.location
");

while($bin = $binStatusResult->fetch_assoc()) {
    $binStatus[] = $bin;
}

echo json_encode([
    'labels' => $labels,
    'fillLevels' => $fillLevels,
    'alerts' => $alerts,
    'binStatus' => $binStatus
]);
?>