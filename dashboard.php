<?php 
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

include("db.php");

// Get user info from session
$userId = $_SESSION['id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Fetch user info securely
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();

// If user not found, try to find by username as fallback
if (!$userData) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $userResult = $stmt->get_result();
    $userData = $userResult->fetch_assoc();
    
    // Update session with correct user ID if found
    if ($userData) {
        $_SESSION['id'] = $userData['id'];
        $userId = $userData['id'];
    }
}

// If still no user data, create default values to prevent errors
if (!$userData) {
    $userData = [
        'username' => $username,
        'email' => $username . '@smartbin.com',
        'points' => 0
    ];
}

// Fetch reward history (user only)
$rewardHistory = [];
if ($role == 'user') {
    $stmt2 = $conn->prepare("SELECT * FROM reward_history WHERE user_id = ? ORDER BY date DESC LIMIT 10");
    $stmt2->bind_param("i", $userId);
    $stmt2->execute();
    $rewardHistory = $stmt2->get_result();
}

// Fetch bin data
if ($role == 'admin' || $role == 'staff') {
    $binQuery = "SELECT * FROM bin_data ORDER BY timestamp DESC LIMIT 10";
} else {
    $binQuery = "SELECT * FROM bin_data ORDER BY timestamp DESC LIMIT 5";
}
$result = $conn->query($binQuery);
$labels = [];
$fillLevels = [];
while($row = $result->fetch_assoc()) {
    $labels[] = $row['timestamp'];
    $fillLevels[] = $row['fill_level'];
}

// Fetch alerts
if ($role == 'admin' || $role == 'staff') {
    $alertResult = $conn->query("SELECT * FROM alerts ORDER BY created_at DESC LIMIT 10");
} else {
    $alertResult = $conn->query("SELECT * FROM alerts WHERE created_at >= NOW() - INTERVAL 1 DAY ORDER BY created_at DESC LIMIT 5");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
<title><?php echo ucfirst($role); ?> Dashboard - Smart Waste Bin</title>
  <link rel="stylesheet" href="css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>
<body>
  <div class="sidebar">
    <h2>♻️ Smart Bin</h2>
    <p>Welcome, <?php echo htmlspecialchars($username); ?> (<?php echo htmlspecialchars($role); ?>)</p>
    <a href="dashboard.php" class="active">📊 Dashboard</a>

    <?php if($role == 'admin' || $role == 'staff'): ?>
        <a href="bin_management.php">🗑️ Bin Management</a>
        <a href="alert_settings.php">⚠️ Alert Settings</a>
        <a href="#activity" data-tab="activity">📈 Activity Tracker</a>
    <?php endif; ?>

    <?php if($role == 'admin'): ?>
        <a href="user_management.php">👥 User/Staff Management</a>
    <?php endif; ?>

    <?php if($role == 'user' || $role == 'admin'): ?>
        <a href="#profile" data-tab="profile">🧍 Profile</a>
        <a href="#rewards" data-tab="rewards">🏆 Rewards</a>
    <?php endif; ?>

    <a href="#map" data-tab="map">🗺️ Map</a>
    <a href="#notifications" data-tab="notifications">🔔 Notifications</a>
    <a href="#settings" data-tab="settings">⚙️ Settings</a>

    <a href="logout.php">🚪 Logout</a>
</div>

  <div class="main-content">
  <div class="dashboard-header <?php echo $role; ?>-header">
    <h2><?php 
      if($role=='admin') echo '🏠 Admin Dashboard';
      elseif($role=='staff') echo '👷 Staff Dashboard';
      else echo '📊 User Dashboard';
    ?></h2>
    <p><?php 
      if($role=='admin') echo 'Complete system overview and management';
      elseif($role=='staff') echo 'Bin monitoring and maintenance management';
      else echo 'Bin monitoring and reward points';
    ?></p>
      </div>

 
  <?php if($role=='user'): ?>
  <div class="user-content">
    <!-- Profile Section -->
    <div class="tab-content" id="profile">
  <h2>🧍 My Profile</h2>
  <div class="profile-card">
    <div class="profile-header">
      <div class="profile-avatar">photo</div>
      <button class="change-photo">Change Photo</button>
    </div>

    <form method="post" action="update_profile.php">
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="fullname" value="<?php echo htmlspecialchars($userData['username'] ?? ''); ?>">
      </div>

      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>">
      </div>

      <div class="form-group">
        <label>Mobile</label>
        <input type="text" name="mobile" value="<?php echo htmlspecialchars($userData['mobile'] ?? ''); ?>">
      </div>

      <div class="form-group">
        <label>City/Area</label>
        <input type="text" name="city" value="<?php echo htmlspecialchars($userData['location'] ?? ''); ?>">
      </div>

      <div class="reward-summary">
        <h4>Reward Points Summary</h4>
        <div class="points">
          <span class="points-value"><?php echo htmlspecialchars($userData['points'] ?? 0); ?></span>
          <span class="points-label">Total Points Earned</span>
          <span class="level-badge">Gold Level</span>
        </div>
      </div>

      <div class="button-group">
        <button type="submit" class="btn save">Save Changes</button>
      </div>
    </form>
  </div>
</div>


    <!-- Map Section -->
    <div class="tab-content" id="map">
      <h2>🗺️ Nearby Smart Bins</h2>
      <div id="userMap" style="height: 400px; border-radius: 10px;"></div>
    </div>

    <!-- Activity Tracker -->
    <div class="tab-content" id="activity">
      <h2>📈 My Activity Tracker</h2>
      <div class="activity-stats">
        <div class="stat">🗑️ Bins Checked: <?php echo $userData['bins_checked'] ?? 0; ?></div>
        <div class="stat">📋 Reports Submitted: <?php echo $userData['reports_submitted'] ?? 0; ?></div>
        <div class="stat">🏅 Reward Points: <?php echo $userData['points'] ?? 0; ?></div>
      </div>
      <canvas id="activityChart"></canvas>
    </div>

    <!-- Rewards Section -->
    <div class="tab-content" id="rewards">
  <h2>🏆 Rewards & Achievements</h2>

  <div class="reward-summary">
  <h4>Reward Points Summary</h4>
  <div class="points">
    <span class="points-value">
      <?php echo htmlspecialchars($userData['points'] ?? 0); ?>
    </span>
    <span class="points-label">Total Points Earned</span>

    <?php
    // 🧩 Calculate reward level dynamically
    $points = $userData['points'] ?? 0;
    if ($points < 500) {
        $level = "🥉 Bronze";
    } elseif ($points < 1000) {
        $level = "🥈 Silver";
    } elseif ($points < 2000) {
        $level = "🥇 Gold";
    } else {
        $level = "🌟 Eco Hero";
    }
    ?>
    <span class="level-badge"><?php echo $level; ?></span>
  </div>
</div>


    <div class="achievement-levels">
      <div class="level achieved">
        <span>🥉</span>
        <h4>Bronze</h4>
        <p>Achieved</p>
      </div>
      <div class="level achieved">
        <span>🥈</span>
        <h4>Silver</h4>
        <p>Achieved</p>
      </div>
      <div class="level current">
        <span>🥇</span>
        <h4>Gold</h4>
        <p>Current Level</p>
      </div>
      <div class="level upcoming">
        <span>🌟</span>
        <h4>Eco Hero</h4>
        <p><?php echo 2000 - ($userData['points'] ?? 0); ?> points to go</p>
      </div>
    </div>
  </div>
</div>


    <!-- Notifications Section -->
    <div class="tab-content" id="notifications">
      <h2>🔔 Notifications</h2>
    </div>

    <!-- Settings Section -->
    <div class="tab-content" id="settings">
      <h2>⚙️ Settings</h2>
      <a href="change_password.php" class="btn">🔑 Change Password</a>
      <a href="notification_settings.php" class="btn">🔔 Notification Preferences</a>
      <a href="logout.php" class="btn logout">🚪 Logout</a>
    </div>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const sidebarLinks = document.querySelectorAll('.sidebar a[data-tab]');
  const tabContents = document.querySelectorAll('.tab-content');

  // Hide all tabs initially
  tabContents.forEach(tab => tab.style.display = 'none');

  // Show profile tab by default
  document.getElementById('profile').style.display = 'block';

  let mapInitialized = false;
  let userMap;

  sidebarLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();

      // Remove active state from all links
      sidebarLinks.forEach(l => l.classList.remove('active'));

      // Hide all tab contents
      tabContents.forEach(tab => tab.style.display = 'none');

      // Show the clicked tab
      const targetTab = this.getAttribute('data-tab');
      document.getElementById(targetTab).style.display = 'block';

      // Add active state
      this.classList.add('active');

      // Initialize map only once
      if(targetTab === 'map' && !mapInitialized){
          mapInitialized = true;

          const sknLat = 17.66670036315918;   // SKN College latitude
          const sknLng = 75.33329772949219;   // SKN College longitude

          userMap = L.map('userMap').setView([sknLat, sknLng], 20);

          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
          }).addTo(userMap);

          // Add bin markers
          const bins = <?php
            $binsResult = $conn->query("SELECT bin_id, location, latitude, longitude, capacity FROM bins");
            $bins = [];
            while($row = $binsResult->fetch_assoc()) { $bins[] = $row; }
            echo json_encode($bins);
          ?>;

          bins.forEach(function(bin){
              if(bin.latitude && bin.longitude){
                  L.marker([bin.latitude, bin.longitude]).addTo(userMap)
                    .bindPopup(`<b>${bin.bin_id} - ${bin.location}</b><br>Capacity: ${bin.capacity}L`);
              }
          });
      }
    });
  });
});
</script>



<style>
.user-dashboard { display: flex; gap: 20px; }
.user-sidebar { width: 220px; background: #2c3e50; color: #fff; padding: 20px; border-radius: 10px; }
.user-sidebar ul { list-style: none; padding: 0; }
.user-sidebar ul li { padding: 10px; cursor: pointer; border-radius: 6px; margin-bottom: 8px; }
.user-sidebar ul li.active, .user-sidebar ul li:hover { background: #27ae60; }
.user-content { flex: 1; }
.tab-content { display: none; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
.profile-pic { width: 80px; height: 80px; border-radius: 50%; margin-bottom: 10px; }
.btn { display: inline-block; padding: 6px 12px; background: #27ae60; color: #fff; border-radius: 6px; text-decoration: none; margin-top: 8px; }
.btn.danger { background: #e74c3c; }
</style>

<?php endif; ?>

    

  <?php if($role=='admin' || $role=='staff'): ?>
  <div class="card quick-stats">
    <h3>📊 Quick Statistics</h3>
    <div class="stats-grid">
      <?php
      $totalBins = $conn->query("SELECT COUNT(*) as count FROM bins")->fetch_assoc()['count'];
      $fullBins = $conn->query("SELECT COUNT(DISTINCT b.bin_id) as count FROM bins b JOIN bin_data d ON b.bin_id = d.bin_id WHERE d.fill_level > 80")->fetch_assoc()['count'];
      $totalAlerts = $conn->query("SELECT COUNT(*) as count FROM alerts WHERE created_at >= NOW() - INTERVAL 24 HOUR")->fetch_assoc()['count'];
      $totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
      ?>
      <div class="stat-item"><div class="stat-number"><?php echo $totalBins; ?></div><div class="stat-label">Total Bins</div></div>
      <div class="stat-item"><div class="stat-number"><?php echo $fullBins; ?></div><div class="stat-label">Full Bins</div></div>
      <div class="stat-item"><div class="stat-number"><?php echo $totalAlerts; ?></div><div class="stat-label">24h Alerts</div></div>
      <?php if($role=='admin'): ?>
      <div class="stat-item"><div class="stat-number"><?php echo $totalUsers; ?></div><div class="stat-label">Users</div></div>
      <?php else: ?>
      <div class="stat-item"><div class="stat-number"><?php echo $conn->query("SELECT COUNT(*) as count FROM bin_data WHERE timestamp >= NOW() - INTERVAL 24 HOUR")->fetch_assoc()['count']; ?></div><div class="stat-label">24h Readings</div></div>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

</body>
</html>
