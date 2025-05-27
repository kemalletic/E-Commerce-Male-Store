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
  <title>Shopping Cart</title>
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

  <main class="cart-container">
    <h2>Shopping Cart</h2>
    <?php if(isset($error)): ?>
      <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if(isset($success)): ?>
      <div class="success-message"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <section id="cart-items">
      <!-- Cart items will be dynamically loaded here -->
      <div class="cart-empty-message" style="display: none;">
        <p>Your cart is empty.</p>
        <a href="<?php echo $baseUrl; ?>/" class="btn-continue-shopping">Continue Shopping</a>
      </div>
      <div class="cart-items-list">
        <!-- Cart items will be inserted here by JavaScript -->
      </div>
    </section>

    <div id="cart-summary" class="cart-summary">
      <div class="summary-row">
        <span>Subtotal:</span>
        <span id="cart-subtotal">$0.00</span>
      </div>
      <div class="summary-row">
        <span>Shipping:</span>
        <span id="cart-shipping">$0.00</span>
      </div>
      <div class="summary-row total">
        <span>Total:</span>
        <span id="cart-total">$0.00</span>
      </div>
      <button id="checkout-btn" class="btn-checkout" disabled>Proceed to Checkout</button>
    </div>
  </main>

  <footer>
    <p>&copy; 2025 E-Commerce Website. All rights reserved.</p>
  </footer>

  <script src="<?php echo $baseUrl; ?>/frontend/assets/js/auth.js"></script>
  <script src="<?php echo $baseUrl; ?>/frontend/assets/js/cart.js"></script>
  <script src="<?php echo $baseUrl; ?>/frontend/assets/js/navbar.js"></script>
</body>
</html> 