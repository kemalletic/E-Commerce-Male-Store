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
  <title>Perfumes Collection</title>
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

  <main class="products-container">
    <h2>Perfumes Collection</h2>
    
    <?php if(isset($error)): ?>
      <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="filters">
      <select id="sort-by" onchange="sortProducts(this.value)">
        <option value="default">Sort By</option>
        <option value="price-low">Price: Low to High</option>
        <option value="price-high">Price: High to Low</option>
        <option value="name-asc">Name: A to Z</option>
        <option value="name-desc">Name: Z to A</option>
      </select>
    </div>

    <div class="products-grid">
      <?php if(isset($products) && !empty($products)): ?>
        <?php foreach($products as $product): ?>
          <div class="product-card">
            <img src="<?php echo $baseUrl; ?>/frontend/assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
            <p class="price">$<?php echo htmlspecialchars($product['price']); ?></p>
            <p class="description"><?php echo htmlspecialchars($product['description']); ?></p>
            <button class="btn-add-to-cart" onclick="addToCart(<?php echo $product['id']; ?>)">Add to Cart</button>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No products found.</p>
      <?php endif; ?>
    </div>
  </main>

  <footer>
    <p>&copy; 2025 E-Commerce Website. All rights reserved.</p>
  </footer>

  <script src="<?php echo $baseUrl; ?>/frontend/assets/js/auth.js"></script>
  <script src="<?php echo $baseUrl; ?>/frontend/assets/js/products.js"></script>
  <script src="<?php echo $baseUrl; ?>/frontend/assets/js/navbar.js"></script>
</body>
</html> 