<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit();
}
include("db.php");

// Fetch all bins
$bins = [];
$result = $conn->query("SELECT * FROM bins ORDER BY location");
while($row = $result->fetch_assoc()) {
    $bins[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bin Management - Smart Waste Bin</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="sidebar">
        <h2>♻️ Smart Bin</h2>
        <p>Welcome, <?php echo $_SESSION['username']; ?> (<?php echo $_SESSION['role']; ?>)</p>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="bin_management.php" class="active">🗑️ Bin Management</a>
        <a href="user_management.php">👥 User Management</a>
        <a href="alert_settings.php">⚠️ Alert Settings</a>
        <a href="logout.php">🚪 Logout</a>
    </div>

    <div class="main-content">
        <h2>Bin Management</h2>
        
        <div class="admin-section">
            <h3>Add/Edit Bin</h3>
            <form id="binForm">
                <input type="hidden" id="binId" name="id" value="0">
                <div>
                    <label for="bin_id">Bin ID:</label>
                    <input type="text" id="bin_id" name="bin_id" required>
                </div>
                <div>
                    <label for="location">Location:</label>
                    <input type="text" id="location" name="location" required>
                </div>
                <div>
                    <label for="capacity">Capacity (L):</label>
                    <input type="number" id="capacity" name="capacity" value="100" required>
                </div>
                <div>
                    <label for="latitude">Latitude:</label>
                    <input type="number" step="any" id="latitude" name="latitude">
                </div>
                <div>
                    <label for="longitude">Longitude:</label>
                    <input type="number" step="any" id="longitude" name="longitude">
                </div>
                <button type="submit">Save Bin</button>
            </form>
        </div>

        <div class="admin-section">
            <h3>Existing Bins</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Bin ID</th>
                        <th>Location</th>
                        <th>Capacity</th>
                        <th>Coordinates</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($bins as $bin): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($bin['bin_id']); ?></td>
                        <td><?php echo htmlspecialchars($bin['location']); ?></td>
                        <td><?php echo $bin['capacity']; ?>L</td>
                        <td>
                            <?php if ($bin['latitude'] && $bin['longitude']): ?>
                                <?php echo $bin['latitude']; ?>, <?php echo $bin['longitude']; ?>
                            <?php else: ?>
                                Not set
                            <?php endif; ?>
                        </td>
                        <td>
                            <button onclick="editBin(<?php echo htmlspecialchars(json_encode($bin)); ?>)">Edit</button>
                            <button onclick="deleteBin(<?php echo $bin['id']; ?>)">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function editBin(bin) {
            document.getElementById('binId').value = bin.id;
            document.getElementById('bin_id').value = bin.bin_id;
            document.getElementById('location').value = bin.location;
            document.getElementById('capacity').value = bin.capacity;
            document.getElementById('latitude').value = bin.latitude || '';
            document.getElementById('longitude').value = bin.longitude || '';
        }

        function deleteBin(id) {
            if (confirm('Are you sure you want to delete this bin?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);

                fetch('manage_bin.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Bin deleted successfully');
                        location.reload();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
            }
        }

        document.getElementById('binForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'save');

            fetch('manage_bin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Bin saved successfully');
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            });
        });
    </script>
</body>
</html>