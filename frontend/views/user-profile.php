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
  <title>User Profile</title>
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

  <script src="<?php echo $baseUrl; ?>/frontend/assets/js/navbar.js"></script>

  <main class="profile-container">
    <h2>User Profile</h2>

    <section class="profile-card">
      <div class="profile-info">
        <p><strong>Username:</strong> <span id="username">Loading...</span></p>
        <p><strong>Email:</strong> <span id="email">Loading...</span></p>
      </div>
    </section>

    <section class="order-history">
      <h3>Order History</h3>
      <div id="order-list">
        <!-- Orders will be dynamically added here -->
      </div>
    </section>
  </main>

  <footer>
    <p>&copy; 2025 E-Commerce Website. All rights reserved.</p>
  </footer>

  <script src="<?php echo $baseUrl; ?>/frontend/assets/js/user-profile.js"></script>
</body>
</html> 