<?php
include("db.php");
session_start();

if(!isset($_SESSION['id'])) {
  echo "User not logged in";
  exit();
}

$user_id = $_SESSION['id'];
$bin_id = $_POST['bin_id']; // or from QR data

// Record the bin usage
$stmt = $conn->prepare("INSERT INTO bin_usage (user_id, bin_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $bin_id);
$stmt->execute();

// Update user reward stats
$conn->query("UPDATE users SET bin_uses = bin_uses + 1, points = points + 10 WHERE id = $user_id");

// Optional: Insert into reward history
$conn->query("INSERT INTO reward_history (user_id, points_earned, description, date) 
              VALUES ($user_id, 10, 'Used Smart Bin #$bin_id', NOW())");

echo "✅ Bin use recorded! Reward +10 points";
?>
