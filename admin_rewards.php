<?php
include("db.php");
session_start();

if($_SESSION['role'] != 'admin') {
  header("Location: dashboard.php");
  exit();
}

$result = $conn->query("SELECT username, bin_uses, points FROM users ORDER BY points DESC");
?>
<!DOCTYPE html>
<html>
<head>
  <title>User Rewards - Admin</title>
  <style>
    table { width: 80%; margin: 30px auto; border-collapse: collapse; }
    th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
    th { background: #2c3e50; color: white; }
  </style>
</head>
<body>
  <h2 align="center">🏆 User Reward Overview</h2>
  <table>
    <tr>
      <th>User</th>
      <th>Bin Uses</th>
      <th>Points</th>
      <th>Level</th>
    </tr>
    <?php while($row = $result->fetch_assoc()): 
      $p = $row['points'];
      if ($p < 500) $level = "Bronze";
      elseif ($p < 1000) $level = "Silver";
      elseif ($p < 2000) $level = "Gold";
      else $level = "Eco Hero";
    ?>
    <tr>
      <td><?php echo htmlspecialchars($row['username']); ?></td>
      <td><?php echo $row['bin_uses']; ?></td>
      <td><?php echo $row['points']; ?></td>
      <td><?php echo $level; ?></td>
    </tr>
    <?php endwhile; ?>
  </table>
</body>
</html>
