<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Products</title>
  <link rel="stylesheet" href="<?php echo $baseUrl; ?>/frontend/assets/css/style.css">
</head>
<body>
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
    document.addEventListener('DOMContentLoaded', function() {
      const user = localStorage.getItem('user');
      if (!user || JSON.parse(user).role !== 'admin') {
        window.location.href = '/';
      }
    });
  </script>
  <main class="admin-container">
    <h2>Manage Products</h2>
    <button id="add-product-btn" class="btn">Add New Product</button>
    <section id="products-list">
      <!-- Existing products will be displayed here -->
    </section>
  </main>
  <div id="product-modal" class="modal">
    <div class="modal-content">
      <h3 id="modal-title">Add New Product</h3>
      <form id="product-form">
        <input type="text" id="product-name" placeholder="Product Name" required>
        <input type="number" id="product-price" placeholder="Price" required>
        <input type="file" id="product-image" required>
        <select id="product-category" required>
          <option value="shirts">Shirts</option>
          <option value="jackets">Jackets</option>
          <option value="perfumes">Perfumes</option>
          <option value="sneakers">Sneakers</option>
          <option value="tracksuits">Tracksuits</option>
        </select>
        <button type="submit" class="btn">Save Product</button>
        <button type="button" class="btn cancel" id="cancel-btn">Cancel</button>
      </form>
    </div>
  </div>
  <footer>
    <p>&copy; 2025 E-Commerce Website. All rights reserved.</p>
  </footer>
  <script src="<?php echo $baseUrl; ?>/frontend/assets/js/navbar.js"></script>
  <script src="<?php echo $baseUrl; ?>/frontend/assets/js/manage-products.js"></script>
</body>
</html> 