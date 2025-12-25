<?php
session_start();
include("db.php");

// Check login (optional)
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
	header("Location: login.php");
	exit();
}

// Fetch bins from database
$query = "SELECT bin_id, location, capacity, latitude, longitude FROM bins";
$result = $conn->query($query);
$bins = [];
while ($row = $result->fetch_assoc()) {
    $bins[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Smart Bin - Live Map</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    #map { height: 500px; width: 100%; border-radius: 10px; }
  </style>
</head>
<body>
  <h2>📍 Smart Bin Live Map</h2>
  <p>Showing bins near your current location.</p>
  <div id="map"></div>

  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script>
    // Initialize map (temporary center, will update automatically)
    var map = L.map('map').setView([0, 0], 13);

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Show user's current location
    map.locate({ setView: true, maxZoom: 16 });

    // Add a marker for user's location
    function onLocationFound(e) {
      var userMarker = L.marker(e.latlng).addTo(map)
        .bindPopup("📍 You are here").openPopup();
    }

    function onLocationError(e) {
      alert("Unable to access your location: " + e.message);
      // Default to Nashik if location not found
      map.setView([19.9975, 73.7898], 13);
    }

    map.on('locationfound', onLocationFound);
    map.on('locationerror', onLocationError);

    // Add bin markers from PHP
    var bins = <?php echo json_encode($bins); ?>;

    bins.forEach(function(bin) {
      if (!bin.latitude || !bin.longitude) return;

      var marker = L.marker([bin.latitude, bin.longitude]).addTo(map);
      marker.bindPopup(
        `<b>${bin.bin_id} - ${bin.location}</b><br>
         Capacity: ${bin.capacity}L`
      );
    });
  </script>
</body>
</html>
