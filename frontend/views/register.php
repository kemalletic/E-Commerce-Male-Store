<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug output
echo "<!-- This is the dynamic PHP version -->\n";
echo "<!-- Debug: Template started -->\n";
echo "<!-- Debug: baseUrl = " . (isset($baseUrl) ? $baseUrl : 'not set') . " -->\n";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <link rel="stylesheet" href="<?php echo $baseUrl; ?>/frontend/assets/css/style.css">
</head>
<body>
  <!-- Navigation -->
  <header id="navbar">
    <nav>
      <button id="menu-toggle" class="hamburger">&#9776;</button>
      <a href="<?php echo $baseUrl; ?>/">Home</a>
      <div class="dropdown" id="categories-dropdown">
        <button class="dropbtn" onclick="disableCategories()">Categories</button>
        <div class="dropdown-content">
          <a href="<?php echo $baseUrl; ?>/shirts">Shirts</a>
          <a href="<?php echo $baseUrl; ?>/jackets">Jackets</a>
          <a href="<?php echo $baseUrl; ?>/perfumes">Perfumes</a>
          <a href="<?php echo $baseUrl; ?>/sneakers">Sneakers</a>
          <a href="<?php echo $baseUrl; ?>/tracksuits">Tracksuits</a>
        </div>
      </div>
      <a href="<?php echo $baseUrl; ?>/cart">Cart</a>
      <a href="<?php echo $baseUrl; ?>/user-profile">Profile</a>
      <a href="<?php echo $baseUrl; ?>/admin/dashboard" id="admin-dashboard-btn" style="display: none;">Admin Dashboard</a>

      <div class="auth-links">
        <a href="<?php echo $baseUrl; ?>/login" id="login-btn">Login</a>
        <a href="<?php echo $baseUrl; ?>/register" id="register-btn">Register</a>
        <a href="#" id="logout-btn" class="hidden">Logout</a>
      </div>
    </nav>
  </header>

  <main class="register-container">
    <div class="register-box">
      <h2>Create an Account</h2>
      
      <?php if(isset($error)): ?>
        <div class="error-message"><?php echo $error; ?></div>
      <?php endif; ?>

      <form id="registerForm" method="POST" action="<?php echo $baseUrl; ?>/register">
        <div class="input-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
        </div>

        <div class="input-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
        </div>

        <div class="input-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required>
        </div>

        <div class="input-group">
          <label for="confirmPassword">Confirm Password</label>
          <input type="password" id="confirmPassword" name="confirmPassword" required>
          <p id="passwordError" class="error-message"></p>
        </div>

        <button type="submit" class="btn-register">Register</button>
      </form>

      <p class="login-link">Already have an account? <a href="<?php echo $baseUrl; ?>/login">Login here</a></p>
    </div>
  </main>

  <footer>
    <p>&copy; 2025 E-Commerce Website. All rights reserved.</p>
  </footer>

  <script src="<?php echo $baseUrl; ?>/frontend/assets/js/auth.js"></script>
  <script>
    // Password confirmation validation
    document.getElementById("registerForm").addEventListener("submit", function(event) {
      let password = document.getElementById("password").value;
      let confirmPassword = document.getElementById("confirmPassword").value;
      let passwordError = document.getElementById("passwordError");

      if (password !== confirmPassword) {
        event.preventDefault();
        passwordError.textContent = "Passwords do not match!";
        passwordError.style.color = "red";
      } else {
        passwordError.textContent = "";
      }
    });
  </script>
  <script src="<?php echo $baseUrl; ?>/frontend/assets/js/register.js"></script>
  <script src="<?php echo $baseUrl; ?>/frontend/assets/js/navbar.js"></script>
</body>
</html> 