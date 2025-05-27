document.addEventListener('DOMContentLoaded', function() {
    // Get user data from localStorage
    const userStr = localStorage.getItem('user') || localStorage.getItem('loggedInUser');
    let user = null;
    
    if (userStr) {
        try {
            user = JSON.parse(userStr);
            console.log('User data:', user); // Debug log
            
            // Update profile information
            const usernameElement = document.getElementById('username');
            const emailElement = document.getElementById('email');
            
            if (usernameElement) {
                usernameElement.textContent = user.name || user.username || 'N/A';
            }
            if (emailElement) {
                emailElement.textContent = user.email || 'N/A';
            }
            
            // Fetch order history if user has an ID
            if (user.id) {
                fetchOrderHistory(user.id);
            } else {
                console.error('User ID not found');
                document.getElementById('order-list').innerHTML = '<p>Error loading order history.</p>';
            }
        } catch (e) {
            console.error('Error parsing user data:', e);
            showError('Error loading user data');
        }
    } else {
        // Redirect to login if no user data found
        window.location.href = '/login';
    }
});

async function fetchOrderHistory(userId) {
    try {
        const token = localStorage.getItem('token');
        if (!token) {
            throw new Error('No authentication token found');
        }

        const response = await fetch(`/orders/user/${userId}`, {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        if (!response.ok) {
            throw new Error('Failed to fetch order history');
        }

        const orders = await response.json();
        displayOrders(orders);
    } catch (error) {
        console.error('Error fetching order history:', error);
        document.getElementById('order-list').innerHTML = '<p>Error loading order history.</p>';
    }
}

function displayOrders(orders) {
    const orderList = document.getElementById('order-list');
    if (!orderList) return;

    if (!orders || orders.length === 0) {
        orderList.innerHTML = '<p>No orders found.</p>';
        return;
    }

    orderList.innerHTML = orders.map(order => `
        <div class="order-card">
            <div class="order-header">
                <h4>Order #${order.id}</h4>
                <span class="status ${order.status.toLowerCase()}">${order.status}</span>
            </div>
            <div class="order-details">
                <p><strong>Date:</strong> ${new Date(order.created_at).toLocaleDateString()}</p>
                <p><strong>Total:</strong> $${order.total.toFixed(2)}</p>
            </div>
            <div class="order-products">
                <h4>Products</h4>
                ${order.items.map(item => `
                    <div class="order-item">
                        <div class="order-item-image-container">
                            <img src="/frontend/assets/images/${item.image_url || 'placeholder.jpg'}" alt="${item.name}" class="order-item-image">
                        </div>
                        <div class="order-item-details">
                            <p><strong>${item.name}</strong></p>
                            <p>Quantity: ${item.quantity}</p>
                            <p>Price: $${item.price.toFixed(2)}</p>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `).join('');
}

function showError(message) {
    const profileInfo = document.querySelector('.profile-info');
    if (profileInfo) {
        profileInfo.innerHTML = `<p class="error-message">${message}</p>`;
    }
}

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
