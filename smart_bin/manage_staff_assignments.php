<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit();
}
include("db.php");

// Fetch all staff members
$staffMembers = [];
$result = $conn->query("SELECT id, username, email FROM users WHERE role = 'staff' ORDER BY username");
while($row = $result->fetch_assoc()) {
    $staffMembers[] = $row;
}

// Fetch all bins
$bins = [];
$result = $conn->query("SELECT * FROM bins ORDER BY location");
while($row = $result->fetch_assoc()) {
    $bins[] = $row;
}

// Fetch current assignments
$assignments = [];
$result = $conn->query("SELECT sba.*, u.username as staff_name, b.location 
                        FROM staff_bin_assignments sba
                        JOIN users u ON sba.staff_id = u.id
                        JOIN bins b ON sba.bin_id = b.bin_id
                        ORDER BY sba.assigned_at DESC");
while($row = $result->fetch_assoc()) {
    $assignments[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Bin Assignments - Smart Waste Bin</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="sidebar">
        <h2>♻️ Smart Bin</h2>
        <p>Welcome, <?php echo $_SESSION['username']; ?> (<?php echo $_SESSION['role']; ?>)</p>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="bin_management.php">🗑️ Bin Management</a>
        <a href="user_management.php">👥 User Management</a>
        <a href="manage_staff_assignments.php" class="active">👷 Staff Assignments</a>
        <a href="alert_settings.php">⚠️ Alert Settings</a>
        <a href="logout.php">🚪 Logout</a>
    </div>

    <div class="main-content">
        <h2>👷 Staff Bin Assignments</h2>
        
        <div class="admin-section">
            <h3>Assign Bin to Staff</h3>
            <form id="assignmentForm">
                <div style="margin-bottom: 15px;">
                    <label for="staff_id">Staff Member:</label>
                    <select id="staff_id" name="staff_id" required>
                        <option value="">Select Staff</option>
                        <?php foreach($staffMembers as $staff): ?>
                            <option value="<?php echo $staff['id']; ?>">
                                <?php echo htmlspecialchars($staff['username']); ?> (<?php echo htmlspecialchars($staff['email']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label for="bin_id">Bin:</label>
                    <select id="bin_id" name="bin_id" required>
                        <option value="">Select Bin</option>
                        <?php foreach($bins as $bin): ?>
                            <option value="<?php echo htmlspecialchars($bin['bin_id']); ?>">
                                <?php echo htmlspecialchars($bin['bin_id']); ?> - <?php echo htmlspecialchars($bin['location']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit">Assign Bin</button>
            </form>
        </div>

        <div class="admin-section">
            <h3>Current Assignments</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Staff</th>
                        <th>Bin ID</th>
                        <th>Location</th>
                        <th>Assigned At</th>
                        <th>Last Collection</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($assignments as $assignment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($assignment['staff_name']); ?></td>
                        <td><?php echo htmlspecialchars($assignment['bin_id']); ?></td>
                        <td><?php echo htmlspecialchars($assignment['location']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($assignment['assigned_at'])); ?></td>
                        <td><?php echo $assignment['last_collection_date'] ? date('M d, Y', strtotime($assignment['last_collection_date'])) : 'Never'; ?></td>
                        <td>
                            <span class="alert-badge <?php echo $assignment['is_active'] ? 'info' : 'warning'; ?>">
                                <?php echo $assignment['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td>
                            <?php if($assignment['is_active']): ?>
                                <button onclick="deactivateAssignment(<?php echo $assignment['id']; ?>)">Deactivate</button>
                            <?php else: ?>
                                <button onclick="reactivateAssignment(<?php echo $assignment['id']; ?>)">Reactivate</button>
                            <?php endif; ?>
                            <button onclick="deleteAssignment(<?php echo $assignment['id']; ?>)" style="background: #e74c3c;">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.getElementById('assignmentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'assign');

            fetch('handle_staff_assignments.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Bin assigned successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            });
        });

        function deactivateAssignment(id) {
            updateAssignmentStatus(id, false);
        }

        function reactivateAssignment(id) {
            updateAssignmentStatus(id, true);
        }

        function updateAssignmentStatus(id, status) {
            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('id', id);
            formData.append('is_active', status ? '1' : '0');

            fetch('handle_staff_assignments.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Assignment status updated!');
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            });
        }

        function deleteAssignment(id) {
            if (confirm('Are you sure you want to delete this assignment?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);

                fetch('handle_staff_assignments.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Assignment deleted successfully!');
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


