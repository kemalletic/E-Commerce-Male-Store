<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
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
  <script>
    // Only allow admin users
    document.addEventListener('DOMContentLoaded', function() {
      const user = localStorage.getItem('user');
      if (!user || JSON.parse(user).role !== 'admin') {
        window.location.href = '/';
      }
    });
  </script>
  <main class="dashboard-container">
    <h2>Admin Dashboard</h2>
    <section id="admin-panel">
      <div class="admin-card" id="manage-products">
        <h3>Manage Products</h3>
        <p>Add, edit, or remove products.</p>
        <a href="<?php echo $baseUrl; ?>/admin/manage-products" class="btn">Go to Manage Products</a>
      </div>
      <div class="admin-card" id="manage-users">
        <h3>Manage Users</h3>
        <p>View and control user accounts.</p>
        <a href="<?php echo $baseUrl; ?>/admin/manage-users" class="btn">Go to Manage Users</a>
      </div>
      <div class="admin-card" id="manage-orders">
        <h3>Manage Orders</h3>
        <p>Track and update order statuses.</p>
        <a href="<?php echo $baseUrl; ?>/admin/manage-orders" class="btn">Go to Manage Orders</a>
      </div>
    </section>
  </main>
  <footer>
    <p>&copy; 2025 E-Commerce Website. All rights reserved.</p>
  </footer>
  <script src="<?php echo $baseUrl; ?>/frontend/assets/js/navbar.js"></script>
  <script src="<?php echo $baseUrl; ?>/frontend/assets/js/admin-dashboard.js"></script>
</body>
</html> 