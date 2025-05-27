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
  <title>Checkout</title>
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

  <main class="checkout-container">
    <h2>Checkout</h2>
    <?php if(isset($error)): ?>
      <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if(isset($success)): ?>
      <div class="success-message"><?php echo $success; ?></div>
    <?php endif; ?>
    <div class="checkout-content">
      <div class="checkout-form">
        <form id="checkoutForm" method="POST" action="<?php echo $baseUrl; ?>/checkout">
          <h3>Shipping Information</h3>
          <div class="input-group">
            <label for="fullName">Full Name</label>
            <input type="text" id="fullName" name="fullName" value="<?php echo isset($shipping['fullName']) ? htmlspecialchars($shipping['fullName']) : ''; ?>" required>
          </div>
          <div class="input-group">
            <label for="address">Address</label>
            <input type="text" id="address" name="address" value="<?php echo isset($shipping['address']) ? htmlspecialchars($shipping['address']) : ''; ?>" required>
          </div>
          <div class="input-group">
            <label for="city">City</label>
            <input type="text" id="city" name="city" value="<?php echo isset($shipping['city']) ? htmlspecialchars($shipping['city']) : ''; ?>" required>
          </div>
          <div class="input-group">
            <label for="state">State</label>
            <input type="text" id="state" name="state" value="<?php echo isset($shipping['state']) ? htmlspecialchars($shipping['state']) : ''; ?>" required>
          </div>
          <div class="input-group">
            <label for="zipCode">ZIP Code</label>
            <input type="text" id="zipCode" name="zipCode" value="<?php echo isset($shipping['zipCode']) ? htmlspecialchars($shipping['zipCode']) : ''; ?>" required>
          </div>
          <h3>Payment Information</h3>
          <div class="input-group">
            <label for="cardNumber">Card Number</label>
            <input type="text" id="cardNumber" name="cardNumber" required>
          </div>
          <div class="input-group">
            <label for="expiryDate">Expiry Date</label>
            <input type="text" id="expiryDate" name="expiryDate" placeholder="MM/YY" required>
          </div>
          <div class="input-group">
            <label for="cvv">CVV</label>
            <input type="text" id="cvv" name="cvv" required>
          </div>
          <button type="submit" class="btn-place-order">Place Order</button>
        </form>
      </div>
      <div class="order-summary">
        <h3>Order Summary</h3>
        <?php if(isset($cart) && !empty($cart['items'])): ?>
          <div class="summary-items">
            <?php foreach($cart['items'] as $item): ?>
              <div class="summary-item">
                <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                <span class="item-quantity">x<?php echo htmlspecialchars($item['quantity']); ?></span>
                <span class="item-price">$<?php echo htmlspecialchars($item['price']); ?></span>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="summary-totals">
            <div class="summary-row">
              <span>Subtotal:</span>
              <span>$<?php echo htmlspecialchars($cart['subtotal']); ?></span>
            </div>
            <div class="summary-row">
              <span>Shipping:</span>
              <span>$<?php echo htmlspecialchars($cart['shipping']); ?></span>
            </div>
            <div class="summary-row total">
              <span>Total:</span>
              <span>$<?php echo htmlspecialchars($cart['total']); ?></span>
            </div>
          </div>
        <?php else: ?>
          <p>Your cart is empty</p>
          <a href="<?php echo $baseUrl; ?>/" class="btn-continue-shopping">Continue Shopping</a>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <footer>
    <p>&copy; 2025 E-Commerce Website. All rights reserved.</p>
  </footer>

  <script src="<?php echo $baseUrl; ?>/frontend/assets/js/auth.js"></script>
  <script src="<?php echo $baseUrl; ?>/frontend/assets/js/checkout.js"></script>
  <script src="<?php echo $baseUrl; ?>/frontend/assets/js/navbar.js"></script>
</body>
</html> 