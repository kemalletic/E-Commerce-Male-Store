document.addEventListener("DOMContentLoaded", function () {
    console.log("Cart page loaded");
    
    // Only check for login if we're on the cart page
    if (window.location.pathname === "/cart") {
        const token = localStorage.getItem("token");
        if (!token) {
            const cartContainer = document.getElementById("cart-items");
            if (cartContainer) {
                cartContainer.innerHTML = `
                    <div class="login-prompt">
                        <p>Please log in to view your cart.</p>
                        <button onclick="window.location.href='/login'">Login</button>
                    </div>
                `;
            }
            return;
        }
        // Load cart items only if we're on the cart page and user is logged in
        loadCartItems();
    }
});

async function loadCartItems() {
    const token = localStorage.getItem("token");
    if (!token) {
        const cartContainer = document.getElementById("cart-items");
        if (cartContainer) {
            cartContainer.innerHTML = `
                <div class="login-prompt">
                    <p>Please log in to view your cart.</p>
                    <button onclick="window.location.href='/login'">Login</button>
                </div>
            `;
        }
        return;
    }

    try {
        const response = await fetch("/cart", {
            headers: {
                "Authorization": `Bearer ${token}`,
                "Accept": "application/json"
            }
        });

        if (!response.ok) {
            throw new Error("Failed to load cart");
        }

        const data = await response.json();
        displayCartItems(data);
    } catch (error) {
        console.error("Error loading cart:", error);
        showError("Failed to load cart items. Please try again later.");
    }
}

function displayCartItems(data) {
    const cartContainer = document.getElementById("cart-items");
    const cartSummary = document.getElementById("cart-summary");
    if (!cartContainer || !cartSummary) return;

    if (!data.items || data.items.length === 0) {
        cartContainer.innerHTML = `
            <p class="empty-cart">Your cart is empty.</p>
        `;
        cartSummary.style.display = "none";
        return;
    }

    cartSummary.style.display = "block";
    cartContainer.innerHTML = data.items.map(item => `
        <div class="cart-item" data-id="${item.product_id}">
            <img src="/frontend/assets/images/${item.image_url || 'placeholder.jpg'}" alt="${item.name}">
            <div class="item-details">
                <h3>${item.name}</h3>
                <p class="price">$${item.price}</p>
                <div class="quantity-controls">
                    <button class="quantity-btn" onclick="updateQuantity(${item.product_id}, -1)">-</button>
                    <span class="quantity">${item.quantity}</span>
                    <button class="quantity-btn" onclick="updateQuantity(${item.product_id}, 1)">+</button>
                </div>
            </div>
            <button class="remove-btn" onclick="removeItem(${item.product_id})">Remove</button>
        </div>
    `).join('');

    document.getElementById("cart-total").textContent = `$${data.total.toFixed(2)}`;
}

async function updateQuantity(productId, change) {
    try {
        const quantityElement = event.target.parentElement.querySelector('.quantity');
        const currentQuantity = parseInt(quantityElement.textContent);
        const newQuantity = currentQuantity + change;
        
        if (newQuantity < 1) return;
        
        const response = await fetch("http://localhost:8080/cart/update", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${localStorage.getItem("token")}`
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: newQuantity
            })
        });

        if (!response.ok) {
            throw new Error("Failed to update quantity");
        }

        const data = await response.json();
        if (data.error) {
            throw new Error(data.error);
        }

        // Reload cart items to update all totals
        loadCartItems();
    } catch (error) {
        console.error("Error updating quantity:", error);
        showError("Failed to update quantity. Please try again.");
    }
}

async function removeItem(productId) {
    if (!confirm("Are you sure you want to remove this item?")) return;
    
    try {
        const response = await fetch(`http://localhost:8080/cart/${productId}`, {
            method: "DELETE",
            headers: {
                "Authorization": `Bearer ${localStorage.getItem("token")}`
            }
        });

        if (!response.ok) {
            throw new Error("Failed to remove item");
        }

        const data = await response.json();
        if (data.error) {
            throw new Error(data.error);
        }

        // Reload cart items
        loadCartItems();
    } catch (error) {
        console.error("Error removing item:", error);
        showError("Failed to remove item. Please try again.");
    }
}

// Function to add item to cart
async function addToCart(productId) {
    const token = localStorage.getItem("token");
    if (!token) {
        if (confirm("Please log in to add items to your cart. Would you like to log in now?")) {
            window.location.href = "/login";
        }
        return;
    }

    try {
        const response = await fetch("/cart/add", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${token}`
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: 1
            })
        });

        if (!response.ok) {
            throw new Error("Failed to add item to cart");
        }

        alert("Item added to cart!");
        // Optionally reload cart items if on cart page
        if (window.location.pathname === "/cart") {
            loadCartItems();
        }
    } catch (error) {
        console.error("Error adding to cart:", error);
        alert("Failed to add item to cart. Please try again.");
    }
}

function showError(message) {
    const cartContainer = document.querySelector('.cart-container');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    cartContainer.insertBefore(errorDiv, cartContainer.firstChild);
    
    // Remove error message after 3 seconds
    setTimeout(() => {
        errorDiv.remove();
    }, 3000);
}


