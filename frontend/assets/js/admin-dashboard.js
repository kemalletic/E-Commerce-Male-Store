document.addEventListener("DOMContentLoaded", function () {
    // Try to get user data from both possible storage keys
    const userData = localStorage.getItem("loggedInUser") || localStorage.getItem("user");
    let loggedInUser = null;

    try {
        loggedInUser = userData ? JSON.parse(userData) : null;
    } catch (error) {
        console.error("Error parsing user data:", error);
        loggedInUser = null;
    }

    // Check if user is logged in and is an admin
    if (!loggedInUser || loggedInUser.role !== "admin") {
        console.log("Access denied. User data:", loggedInUser);
        alert("Access Denied! Admins Only.");
        window.location.href = "/";
        return;
    }

    console.log("Admin dashboard loaded for:", loggedInUser.name);

    // Initialize admin panel functionality
    function manageProducts() { 
        window.location.href = "manage-products.html";
    }
    
    function manageUsers() { 
        window.location.href = "manage-users.html";
    }
    
    function manageOrders() { 
        window.location.href = "manage-orders.html";
    }

    // Add click event listeners to admin cards
    document.querySelector("#manage-products")?.addEventListener("click", manageProducts);
    document.querySelector("#manage-users")?.addEventListener("click", manageUsers);
    document.querySelector("#manage-orders")?.addEventListener("click", manageOrders);
});
