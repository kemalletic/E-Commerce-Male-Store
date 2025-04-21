document.addEventListener("DOMContentLoaded", function () {
    checkLoginStatus();
    document.getElementById("logout-btn").addEventListener("click", logout);
});

// Function to check if the user is logged in
function checkLoginStatus() {
    const user = getLoggedInUser();
    
    if (!user) {
        redirectToLogin();
        return;
    }

    displayUserInfo(user);
    loadOrderHistory(user.username);
}

// Function to get the logged-in user from localStorage
function getLoggedInUser() {
    const storedUser = localStorage.getItem("loggedInUser");
    return storedUser ? JSON.parse(storedUser) : null;
}

// Function to redirect to the login page
function redirectToLogin() {
    alert("You must be logged in to access the profile page.");
    window.location.href = "login.html"; // Redirect to login
}

// Function to display user information on the profile page
function displayUserInfo(user) {
    document.getElementById("username").textContent = user.username;
    document.getElementById("email").textContent = user.email;
}

// Function to load and display the user's order history
function loadOrderHistory(username) {
    const orders = getUserOrders(username);
    const orderList = document.getElementById("order-list");

    orderList.innerHTML = ""; // Clear previous orders

    if (orders.length === 0) {
        orderList.innerHTML = "<p>No orders found.</p>";
        return;
    }

    orders.forEach(order => {
        orderList.appendChild(createOrderCard(order));
    });
}

// Function to get orders for a specific user
function getUserOrders(username) {
    const allOrders = JSON.parse(localStorage.getItem("orders")) || [];
    return allOrders.filter(order => order.user === username);
}

// Function to create an HTML card for an order
function createOrderCard(order) {
    const orderCard = document.createElement("div");
    orderCard.classList.add("order-card");

    // Create order header
    const orderHeader = document.createElement("div");
    orderHeader.classList.add("order-header");
    orderHeader.innerHTML = `
        <h4>Order #${order.id}</h4>
        <div class="status ${order.status.toLowerCase()}">${order.status}</div>
        <p><strong>Total Amount:</strong> $${order.totalAmount.toFixed(2)}</p>
    `;
    orderCard.appendChild(orderHeader);

    // Create shipping info section
    const shippingInfo = document.createElement("div");
    shippingInfo.classList.add("shipping-info");
    shippingInfo.innerHTML = `
        <p><strong>Shipping to:</strong> ${order.shippingInfo.fullName}</p>
        <p>${order.shippingInfo.address}, ${order.shippingInfo.city}, ${order.shippingInfo.zipCode}</p>
        <p><strong>Phone:</strong> ${order.shippingInfo.phoneNumber}</p>
    `;
    orderCard.appendChild(shippingInfo);

    // Create order items section
    const orderItems = document.createElement("div");
    orderItems.classList.add("order-items");
    
    if (order.cartItems && order.cartItems.length > 0) {
        order.cartItems.forEach(item => {
            const itemDiv = document.createElement("div");
            itemDiv.classList.add("order-item");
            
            // Create and handle product image
            const itemImage = document.createElement("img");
            itemImage.classList.add("order-item-image");
            if (!item.image.startsWith('data:')) {
                if (!item.image.startsWith('../assets/images/')) {
                    item.image = '../assets/images/' + item.image;
                }
            }
            itemImage.src = item.image;
            itemImage.alt = item.name;
            itemImage.onerror = function() {
                // Create a simple placeholder image using data URL
                const canvas = document.createElement('canvas');
                canvas.width = 100;
                canvas.height = 100;
                const ctx = canvas.getContext('2d');
                
                // Fill background
                ctx.fillStyle = '#f0f0f0';
                ctx.fillRect(0, 0, 100, 100);
                
                // Add text
                ctx.fillStyle = '#999';
                ctx.font = '12px Arial';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText('No Image', 50, 50);
                
                // Set the placeholder image
                this.src = canvas.toDataURL();
            };
            
            // Create item details
            const itemDetails = document.createElement("div");
            itemDetails.classList.add("order-item-details");
            itemDetails.innerHTML = `
                <p><strong>${item.name}</strong></p>
                <p>Size: ${item.size}</p>
                <p>Quantity: ${item.quantity}</p>
                <p>Price: $${item.price.toFixed(2)}</p>
                <p>Subtotal: $${(item.price * item.quantity).toFixed(2)}</p>
            `;
            
            itemDiv.appendChild(itemImage);
            itemDiv.appendChild(itemDetails);
            orderItems.appendChild(itemDiv);
        });
    } else {
        orderItems.innerHTML = "<p>No items in this order.</p>";
    }
    
    orderCard.appendChild(orderItems);
    return orderCard;
}

// Function to log out the user
function logout() {
    localStorage.removeItem("loggedInUser");
    window.location.href = "login.html"; // Redirect to login page
}
