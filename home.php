<?php
// index.php — Smart-bin Landing Page
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart-bin | Smart Waste Management</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navbar -->
    <header>
        <div class="logo">Smart-bin</div>
        <nav>
            <ul>
                <li><a href="#">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="#contact">Contact</a></li>
                <li><a href="login.php">Dashboard</a></li>
            </ul>
        </nav>
    </header>
     <!-- Hero Section -->
    <section class="hero">
        <div class="overlay"></div>
        <div class="hero-content">
            <h1>Smart Waste Management</h1>
            <p>Real-time monitoring solution</p>
            <div class="buttons">
                <a href="dashboard.php" class="btn primary">View Dashboard</a>
                <a href="#about" class="btn secondary">Learn More</a>
            </div>
        </div>
    </section>

<section class="cards-section">
  <h2>How Smart Waste Bin Works</h2>
  <div class="cards-container">
    
    <div class="card">
  <img src="images/dustbin.png" alt="Fill Level Detection">
  <h3>Fill Level Detection</h3>
  <p>Ultrasonic sensors measure how full the bin is and send real-time data to the dashboard.</p>
</div>

<div class="card">
  <img src="images/data.png" alt="Gas Detection">
  <h3>Gas Detection</h3>
  <p>MQ2 gas sensors detect harmful gases emitted from the waste and trigger alerts if unsafe levels are reached.</p>
</div>

<div class="card">
  <img src="images/alert.png" alt="SMS Notifications">
  <h3>SMS Notifications</h3>
  <p>If a bin is full or hazardous gas is detected, SMS alerts are sent to municipal staff for timely action.</p>
</div>

<div class="card">
  <img src="images/strategy.png" alt="Efficient Collection">
  <h3>Efficient Collection</h3>
  <p>Staff receive alerts and optimized routes to collect waste, reducing unnecessary trips and keeping the city clean.</p>
</div>


  </div>
</section>

<!-- Benefits Section -->
    <section id="benefits" class="benefits">
        <div class="benefits-container">
            <!-- Left side: text -->
            <div class="benefits-text">
                <h2>Benefits for Cities & Communities</h2>
                <p>
                    Smart-bin technology brings measurable improvements to urban waste management systems,
                    ensuring cleaner, more efficient, and sustainable cities.
                </p>
                <ul>
                    <li>✅ <strong>Cleaner Public Spaces:</strong> Smart bins help keep streets, parks, and public areas clean, improving hygiene and aesthetics.</li>
                    <li>✅ <strong>Efficient Waste Collection:</strong> Sensors enable municipalities to collect waste only when bins are full, optimizing routes and manpower.</li>
                    <li>✅ <strong>Reduced Overflow & Littering:</strong> Prevents trash from spilling over, especially in crowded areas or during festivals and peak seasons.</li>
                    <li>✅ <strong>Cost Savings:</strong> Optimized collection reduces fuel consumption and labor costs for waste management authorities.</li>
                    <li>✅ <strong>Supports Sustainability Goals:</strong> Encourages proper disposal and recycling, helping cities become more eco-friendly.</li>
                    <li>✅ <strong>Enhanced Citizen Experience:</strong> Cleaner surroundings improve quality of life and promote civic pride.</li>
                </ul>
            </div>

            <!-- Right side: image -->
            <div class="benefits-image">
                <img src="https://images.unsplash.com/photo-1526045612212-70caf35c14df?auto=format&fit=crop&w=1000&q=80" alt="Smart City">
            </div>
        </div>
    </section>

    <section class="cta">
    <h2>Ready to see Smart-Bin in action?</h2>
    <p>View our real-time dashboard to monitor garbage levels at key locations across Nashik.</p>
    <a href="dashboard.php" class="btn-cta">View Dashboard</a>
    </section>

 <footer class="footer">
    <div class="footer-container">

        <div class="footer-logo">
            <h2>Smart-Bin</h2>
            <p>Making cities cleaner and smarter through intelligent waste management.</p>
        </div>

        <div class="footer-links">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href=".footer-contact">Contact</a></li>
                <li><a href="login.php">Dashboard</a></li>
            </ul>
        </div>

        <div class="footer-contact">
            <h3>Contact Us</h3>
            <p>Email: info@smartbin.com</p>
            <p>Phone: +91 98765 43210</p>
        </div>

    </div>

    <div class="footer-bottom">
        <p>© 2025 Smart-Bin | All Rights Reserved</p>
    </div>
</footer>

</body>
</html>
