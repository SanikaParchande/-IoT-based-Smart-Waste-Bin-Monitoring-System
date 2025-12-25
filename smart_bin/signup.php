<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include("db.php");
$message = "";


// Secret key for admin registration
$ADMIN_SECRET_KEY = "SMARTADMIN2025"; // change for security

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $role = strtolower(trim($_POST['role']));
    $username = trim($_POST['full_name']); // using same input field name for simplicity
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $message = "❌ Passwords do not match!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // --- USER SIGNUP ---
        if ($role === 'user') {
            $stmt = $conn->prepare("INSERT INTO users (username, email, role, password_hash, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssss", $username, $email, $role, $hashed_password);

            if ($stmt->execute()) {
                echo "<script>
                        alert('✅ Signup successful! You can now login.');
                        window.location.href = 'index.php';
                      </script>";
                exit;
            } else {
                $message = '⚠️ Error: ' . $conn->error;
            }
        }

        // --- ADMIN SIGNUP ---
        elseif ($role === 'admin') {
            $admin_key = trim($_POST['admin_key']);
            if ($admin_key !== $ADMIN_SECRET_KEY) {
                $message = "❌ Invalid Admin Secret Key!";
            } else {
                $stmt = $conn->prepare("INSERT INTO users (username, email, role, password_hash, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->bind_param("ssss", $username, $email, $role, $hashed_password);

                if ($stmt->execute()) {
                    echo "<h3 style='color:green;'>✅ Data inserted successfully!</h3>";
                    echo "<script>
                    alert('✅ Signup successful! You can now login.');
                    window.location.href = 'index.php';
                    </script>";
                    exit;
               } else {
    echo "<h3 style='color:red;'>⚠️ SQL Error: " . $conn->error . "</h3>";
}

            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Signup - Smart Waste Bin</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="signup-container">
    <div class="signup-card">
      <h2 class="logo-text">Smart Waste Bin Signup</h2>
      <p class="subtitle">Create your account</p>

      <?php if (!empty($message)): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>

      <form method="POST" action="signup.php">
        <div class="role-tabs">
          <button type="button" id="User-tab" class="active" data-role="User">User</button>
          <button type="button" id="Admin-tab" data-role="Admin">Admin</button>
        </div>

        <input type="hidden" name="role" id="role" value="User">

        <!-- USER FORM -->
        <div class="form-section" id="User-form">
          <div class="input-group"><label>Full Name</label>
            <input type="text" name="full_name" required>
          </div>
          <div class="input-group"><label>Email</label>
            <input type="email" name="email" required>
          </div>
          <div class="input-group"><label>Phone No.</label>
            <input type="text" name="phone" required>
          </div>
          <div class="input-group"><label>Password</label>
            <input type="password" name="password" required>
          </div>
          <div class="input-group"><label>Confirm Password</label>
            <input type="password" name="confirm_password" required>
          </div>
        </div>

        <!-- ADMIN FORM -->
        <div class="form-section" id="Admin-form" style="display:none;">
          <div class="input-group"><label>Full Name</label>
            <input type="text" name="full_name" required>
          </div>
          <div class="input-group"><label>Email</label>
            <input type="email" name="email" required>
          </div>
          <div class="input-group"><label>Phone No.</label>
            <input type="text" name="phone" required>
          </div>
          <div class="input-group"><label>Password</label>
            <input type="password" name="password" required>
          </div>
          <div class="input-group"><label>Confirm Password</label>
            <input type="password" name="confirm_password" required>
          </div>
          <div class="input-group"><label>Admin Secret Key</label>
            <input type="password" name="admin_key" required>
          </div>
        </div>

        <button type="submit" class="submit-btn">Sign Up</button>
      </form>

      <div class="links">
        <p>Already have an account? <a href="index.php">Sign in here</a></p>
        <p><a href="index.php">← Back to Home</a></p>
      </div>
    </div>
  </div>

  <script>
const roleButtons = document.querySelectorAll(".role-tabs button");
const forms = document.querySelectorAll(".form-section");
const roleInput = document.getElementById("role");

function showForm(role) {
  forms.forEach(f => {
    f.style.display = "none";
    // Remove 'required' from all hidden inputs
    f.querySelectorAll("input").forEach(inp => inp.required = false);
  });

  const activeForm = document.getElementById(role + "-form");
  activeForm.style.display = "block";

  // Add 'required' only to visible form inputs
  activeForm.querySelectorAll("input").forEach(inp => inp.required = true);

  roleButtons.forEach(b => b.classList.remove("active"));
  document.getElementById(role + "-tab").classList.add("active");

  roleInput.value = role.toLowerCase();
}

// Initialize form on page load
document.querySelector(".role-tabs button.active").click();

roleButtons.forEach(btn => {
  btn.addEventListener("click", () => {
    const role = btn.dataset.role;
    showForm(role);
  });
});
</script>

</body>
</html>
