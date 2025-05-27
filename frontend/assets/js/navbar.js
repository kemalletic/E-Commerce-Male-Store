document.addEventListener("DOMContentLoaded", function () {
    // Hamburger menu functionality
    const menuToggle = document.getElementById("menu-toggle");
    const navbar = document.querySelector("nav");

    if (menuToggle && navbar) {
        menuToggle.addEventListener("click", function () {
            navbar.classList.toggle("nav-active");
        });
    }

    // Auth-related elements
    const loginBtn = document.getElementById("login-btn");
    const registerBtn = document.getElementById("register-btn");
    const logoutBtn = document.getElementById("logout-btn");
    const adminDashboardBtn = document.getElementById("admin-dashboard-btn");
    const profileBtn = document.querySelector('a[href*="user-profile"]');
    const cartBtn = document.querySelector('a[href*="cart"]');

    // Try both user and loggedInUser keys for compatibility
    const userStr = localStorage.getItem("user") || localStorage.getItem("loggedInUser");
    let user = null;
    if (userStr) {
        try {
            user = JSON.parse(userStr);
            console.log("Current user:", user); // Debug log
        } catch (e) {
            console.error("Error parsing user data:", e);
        }
    }

    // Update navbar based on user state
    if (user) {
        // User is logged in
        if (loginBtn) loginBtn.style.display = "none";
        if (registerBtn) registerBtn.style.display = "none";
        if (logoutBtn) {
            logoutBtn.classList.remove("hidden");
            logoutBtn.style.display = "inline-block";
            logoutBtn.addEventListener("click", function(e) {
                e.preventDefault();
                // Clear all auth data
                localStorage.removeItem("user");
                localStorage.removeItem("loggedInUser");
                localStorage.removeItem("token");
                // Redirect to home page
                window.location.href = "/";
            });
        }
        if (profileBtn) profileBtn.style.display = "inline-block";
        if (cartBtn) cartBtn.style.display = "inline-block";

        // Show admin dashboard button for admins
        if (user.role === "admin" && adminDashboardBtn) {
            adminDashboardBtn.style.display = "inline-block";
        }
    } else {
        // User is not logged in
        if (loginBtn) loginBtn.style.display = "inline-block";
        if (registerBtn) registerBtn.style.display = "inline-block";
        if (logoutBtn) {
            logoutBtn.classList.add("hidden");
            logoutBtn.style.display = "none";
        }
        if (profileBtn) profileBtn.style.display = "none";
        if (cartBtn) cartBtn.style.display = "none";
        if (adminDashboardBtn) adminDashboardBtn.style.display = "none";
    }
});
