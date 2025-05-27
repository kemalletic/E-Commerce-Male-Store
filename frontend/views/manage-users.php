<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Users</title>
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
    document.addEventListener('DOMContentLoaded', function() {
      const user = localStorage.getItem('user');
      if (!user || JSON.parse(user).role !== 'admin') {
        window.location.href = '/';
      }
    });
  </script>
    <main class="dashboard-container">
        <h2>Manage Users</h2>
        <section id="admin-panel">
            <div class="admin-card" id="manage-users">
                <h3>Users List</h3>
                <button id="addUserBtn" class="btn">Add New User</button>
                <table id="usersTable">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <!-- User rows will be added dynamically here -->
                    </tbody>
                </table>
            </div>
        </section>
        <div id="editUserModal" style="display: none;">
            <div class="modal-content">
                <h2>Edit User</h2>
                <form id="editUserForm">
                    <label for="editUsername">Username</label>
                    <input type="text" id="editUsername" disabled>
                    <label for="editEmail">Email</label>
                    <input type="email" id="editEmail">
                    <label for="editRole">Role</label>
                    <select id="editRole">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                    <label for="editStatus">Status</label>
                    <select id="editStatus">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <button type="submit" class="btn">Save Changes</button>
                    <button type="button" id="cancelEditBtn" class="btn">Cancel</button>
                </form>
            </div>
        </div>
    </main>
    <footer>
        <p>&copy; 2025 E-Commerce Website. All rights reserved.</p>
    </footer>
    <script src="<?php echo $baseUrl; ?>/frontend/assets/js/manage-users.js"></script>
    <script src="<?php echo $baseUrl; ?>/frontend/assets/js/navbar.js"></script>
</body>
</html> 