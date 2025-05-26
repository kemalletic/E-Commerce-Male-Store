document.addEventListener("DOMContentLoaded", function () {
    const menuToggle = document.getElementById("menu-toggle");
    const navbar = document.querySelector("nav");

    menuToggle.addEventListener("click", function () {
        navbar.classList.toggle("nav-active");
    });
});




document.addEventListener("DOMContentLoaded", function () {
    const loginBtn = document.getElementById("login-btn");
    const registerBtn = document.getElementById("register-btn");
    const logoutBtn = document.getElementById("logout-btn");
    const adminDashboardBtn = document.getElementById("admin-dashboard-btn");

    // Try both user and loggedInUser keys for compatibility
    const userStr = localStorage.getItem("user") || localStorage.getItem("loggedInUser");
    let user = null;
    if (userStr) {
        user = JSON.parse(userStr);
        console.log("Current user:", user); // Debug log
    }

    if (user) {
        // User is logged in
        if (loginBtn) loginBtn.style.display = "none";
        if (registerBtn) registerBtn.style.display = "none";
        if (logoutBtn) {
            logoutBtn.classList.remove("hidden");
            logoutBtn.style.display = "inline";
        }
        // Show admin dashboard button for admins
        if (user.role === "admin" && adminDashboardBtn) {
            console.log("Showing admin dashboard button"); // Debug log
            adminDashboardBtn.style.display = "inline";
        } else if (adminDashboardBtn) {
            adminDashboardBtn.style.display = "none";
        }
    } else {
        // User is not logged in
        if (loginBtn) loginBtn.style.display = "inline";
        if (registerBtn) registerBtn.style.display = "inline";
        if (logoutBtn) {
            logoutBtn.classList.add("hidden");
            logoutBtn.style.display = "none";
        }
        if (adminDashboardBtn) {
            adminDashboardBtn.style.display = "none";
        }
    }

    // Logout functionality
    if (logoutBtn) {
        logoutBtn.addEventListener("click", function (e) {
            e.preventDefault();
            localStorage.removeItem("token");
            localStorage.removeItem("user");
            localStorage.removeItem("loggedInUser"); // Add this line for consistency
            window.location.href = "/";
        });
    }
});




// Helper function to manage navbar visibility
function updateNavBar(loggedInUser) {
    const loginBtn = document.getElementById("login-btn");
    const registerBtn = document.getElementById("register-btn");
    const logoutBtn = document.getElementById("logout-btn");
    const adminDashboardBtn = document.getElementById("admin-dashboard-btn");

    if (loggedInUser) {
        // Hide login & register, show logout
        if (loginBtn) loginBtn.style.display = "none";
        if (registerBtn) registerBtn.style.display = "none";
        if (logoutBtn) logoutBtn.style.display = "inline-block";

        // Show admin dashboard button for admins
        if (loggedInUser.role === "admin" && adminDashboardBtn) {
            adminDashboardBtn.style.display = "inline-block";
        }
    } else {
        // Show login & register, hide logout and admin dashboard
        if (loginBtn) loginBtn.style.display = "inline-block";
        if (registerBtn) registerBtn.style.display = "inline-block";
        if (logoutBtn) logoutBtn.style.display = "none";
        if (adminDashboardBtn) adminDashboardBtn.style.display = "none";
    }
}
