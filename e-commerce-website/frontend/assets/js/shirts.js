document.addEventListener("DOMContentLoaded", function () {
    // Load all shirts products when the page loads
    loadShirts();
});

// Helper function to load shirts and display them in the category section
function loadShirts() {
    const products = JSON.parse(localStorage.getItem("products")) || [];
    const shirtsSection = document.getElementById("shirts-list");
    shirtsSection.innerHTML = ""; // Clear the section before reloading

    // Filter the products to display only shirts
    const shirts = products.filter(product => product.category === "shirts");

    shirts.forEach(product => {
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

        const sizeSelection = document.createElement("div");
        sizeSelection.classList.add("product-size-selection");

        const sizeLabel = document.createElement("label");
        sizeLabel.setAttribute("for", `${product.name}-size`);
        sizeLabel.textContent = "Select Size:";

        const sizeSelect = document.createElement("select");
        sizeSelect.id = `${product.name}-size`;
        sizeSelect.name = "size";

        // Example sizes, you may want to enhance this part depending on your product data
        const sizes = ["S", "M", "L", "XL", "XXL"];
        sizes.forEach(size => {
            const option = document.createElement("option");
            option.value = size.toLowerCase().split(" ")[0];
            option.textContent = size;
            sizeSelect.appendChild(option);
        });

        const addToCartButton = document.createElement("button");
        addToCartButton.classList.add("product-btn");
        addToCartButton.textContent = "Add to Cart";

        addToCartButton.onclick = function () {
            const selectedSize = sizeSelect.value;
            addToCart(product.id, product.name, selectedSize, product.price, product.image);
        };

        // Append all elements
        sizeSelection.appendChild(sizeLabel);
        sizeSelection.appendChild(sizeSelect);
        productItem.appendChild(productImage);
        productItem.appendChild(productTitle);
        productItem.appendChild(productPrice);
        productItem.appendChild(sizeSelection);
        productItem.appendChild(addToCartButton);

        shirtsSection.appendChild(productItem);
    });
}
