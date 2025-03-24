document.addEventListener("DOMContentLoaded", function () {
    // Load all perfume products when the page loads
    loadPerfumes();
});

// Helper function to load perfumes and display them in the category section
function loadPerfumes() {
    const products = JSON.parse(localStorage.getItem("products")) || [];
    const perfumesSection = document.getElementById("perfumes-list");
    perfumesSection.innerHTML = ""; // Clear the section before reloading

    // Filter the products to display only perfumes
    const perfumes = products.filter(product => product.category === "perfumes");

    perfumes.forEach(product => {
        const productItem = document.createElement("div");
        productItem.classList.add("product-item");

        const productImage = document.createElement("img");
        productImage.classList.add("product-image");
        productImage.src = `../assets/images/${product.image}`;
        productImage.alt = product.name;

        const productTitle = document.createElement("h3");
        productTitle.classList.add("product-title");
        productTitle.textContent = product.name;

        const productPrice = document.createElement("p");
        productPrice.classList.add("product-price");
        productPrice.textContent = `$${product.price}`;

        const addToCartButton = document.createElement("button");
        addToCartButton.classList.add("product-btn");
        addToCartButton.textContent = "Add to Cart";

        addToCartButton.onclick = function () {
            addToCart(product.id, product.name, "", product.price, product.image); // No size for perfumes
        };

        // Append all elements
        productItem.appendChild(productImage);
        productItem.appendChild(productTitle);
        productItem.appendChild(productPrice);
        productItem.appendChild(addToCartButton);

        perfumesSection.appendChild(productItem);
    });
}
