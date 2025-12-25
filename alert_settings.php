<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit();
}
include("db.php");

// Fetch current alerts
$alerts = [];
$result = $conn->query("SELECT * FROM alerts ORDER BY created_at DESC LIMIT 10");
while($row = $result->fetch_assoc()) {
    $alerts[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Alert Settings - Smart Waste Bin</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="sidebar">
        <h2>♻️ Smart Bin</h2>
        <p>Welcome, <?php echo $_SESSION['username']; ?> (<?php echo $_SESSION['role']; ?>)</p>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="bin_management.php">🗑️ Bin Management</a>
        <a href="user_management.php">👥 User Management</a>
        <a href="alert_settings.php" class="active">⚠️ Alert Settings</a>
        <a href="logout.php">🚪 Logout</a>
    </div>

    <div class="main-content">
        <h2>Alert Settings</h2>
        
        <div class="admin-section">
            <h3>Create New Alert</h3>
            <form id="alertForm">
                <div>
                    <label for="title">Alert Title:</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div>
                    <label for="message">Alert Message:</label>
                    <textarea id="message" name="message" rows="3" required></textarea>
                </div>
                <div>
                    <label for="level">Alert Level:</label>
                    <select id="level" name="level" required>
                        <option value="info">Info</option>
                        <option value="warning">Warning</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
                <button type="submit">Create Alert</button>
            </form>
        </div>

        <div class="admin-section">
            <h3>Recent Alerts</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Level</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($alerts as $alert): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($alert['title']); ?></td>
                        <td><?php echo htmlspecialchars($alert['message']); ?></td>
                        <td>
                            <span class="alert-badge <?php echo $alert['level']; ?>">
                                <?php echo ucfirst($alert['level']); ?>
                            </span>
                        </td>
                        <td><?php echo $alert['created_at']; ?></td>
                        <td>
                            <button onclick="deleteAlert(<?php echo $alert['id']; ?>)">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.getElementById('alertForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'create');

            fetch('manage_alert.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Alert created successfully');
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            });
        });

        function deleteAlert(id) {
            if (confirm('Are you sure you want to delete this alert?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);

                fetch('manage_alert.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Alert deleted successfully');
                        location.reload();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
            }
        }
    </script>
</body>
</html>