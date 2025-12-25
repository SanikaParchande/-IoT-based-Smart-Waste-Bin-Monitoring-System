<?php
session_start();
include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = strtolower($_POST['role']); // convert to lowercase

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND role = ?");
    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['id'] = $user['id'];
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        header("Location: dashboard.php"); // redirect both admin and user
        exit();
    } else {
        $message = "❌ Invalid username, password, or role!";
    }
}
?>

<?php
$message = ''; // initialize to empty string
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Smart Waste Bin | Login</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="login-container">
  <div class="login-card">
    <div class="logo-section">
      <h2 class="logo-text">Smart Waste Bin</h2>
      <p class="subtitle">Access your account to manage bins and track reports</p>
    </div>

    <form method="POST" class="login-form">
      <div class="role-toggle">
        <button type="button" class="role-btn active" data-role="User">User</button>
        <button type="button" class="role-btn" data-role="Staff">Staff</button>
        <button type="button" class="role-btn" data-role="Admin">Admin</button>
      </div>

      <input type="hidden" name="role" id="role" value="User">

      <div class="input-group">
        <label>Username</label>
        <input type="text" name="username" placeholder="Enter your username" required>
      </div>

      <div class="input-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Enter your password" required>
      </div>
      

      <button type="submit" id="submitBtn">Sign In as User</button>
    </form>

    <p class="extra-text">
      Don’t have an account? <a href="signup.php">Sign up here</a><br>
      <a href="home.php">← Back to Home</a>
    </p>

    <p class="error-msg"><?= $message ?></p>
  </div>
</div>

<script>
const roleButtons = document.querySelectorAll('.role-btn');
const roleInput = document.getElementById('role');
const submitBtn = document.getElementById('submitBtn');

roleButtons.forEach(btn => {
  btn.addEventListener('click', () => {
    roleButtons.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const selectedRole = btn.dataset.role;
    roleInput.value = selectedRole;
    submitBtn.textContent = `Sign In as ${selectedRole}`;
  });
});
</script>

</body>
</html>