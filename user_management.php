<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit();
}
include("db.php");

// Fetch all users
$users = [];
$result = $conn->query("SELECT id, username, role, created_at FROM users ORDER BY username");
while($row = $result->fetch_assoc()) {
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management - Smart Waste Bin</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="sidebar">
        <h2>♻️ Smart Bin</h2>
        <p>Welcome, <?php echo $_SESSION['username']; ?> (<?php echo $_SESSION['role']; ?>)</p>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="bin_management.php">🗑️ Bin Management</a>
        <a href="user_management.php" class="active">👥 User Management</a>
        <a href="alert_settings.php">⚠️ Alert Settings</a>
        <a href="logout.php">🚪 Logout</a>
    </div>

    <div class="main-content">
        <h2>User Management</h2>
        
        <div class="admin-section">
            <h3>Add New User</h3>
            <form id="userForm">
                <input type="hidden" id="userId" name="id" value="0">
                <div>
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div>
                    <label for="role">Role:</label>
                    <select id="role" name="role" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit">Save User</button>
            </form>
        </div>

        <div class="admin-section">
            <h3>Existing Users</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo ucfirst($user['role']); ?></td>
                        <td><?php echo $user['created_at']; ?></td>
                        <td>
                            <button onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">Edit</button>
                            <?php if ($user['username'] != $_SESSION['username']): ?>
                                <button onclick="deleteUser(<?php echo $user['id']; ?>)">Delete</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function editUser(user) {
            document.getElementById('userId').value = user.id;
            document.getElementById('username').value = user.username;
            document.getElementById('password').value = ''; // Clear password for security
            document.getElementById('password').required = false; // Make password optional for edits
            document.getElementById('role').value = user.role;
        }

        function deleteUser(id) {
            if (confirm('Are you sure you want to delete this user?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);

                fetch('manage_user.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('User deleted successfully');
                        location.reload();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
            }
        }

        document.getElementById('userForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'save');

            fetch('manage_user.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('User saved successfully');
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            });
        });
    </script>
</body>
</html>