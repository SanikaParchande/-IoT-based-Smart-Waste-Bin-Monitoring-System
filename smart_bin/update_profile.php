<?php
session_start();
include("db.php");

$userId = $_SESSION['id'];
$fullname = $_POST['fullname'];
$email = $_POST['email'];
$mobile = $_POST['mobile'];
$city = $_POST['city'];

$stmt = $conn->prepare("UPDATE users SET username=?, email=?, mobile=?, location=? WHERE id=?");
$stmt->bind_param("ssssi", $fullname, $email, $mobile, $city, $userId);
$stmt->execute();

header("Location: dashboard.php#profile");
exit();
?>
