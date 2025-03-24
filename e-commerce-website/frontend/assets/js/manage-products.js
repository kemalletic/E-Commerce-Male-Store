document.addEventListener("DOMContentLoaded", function () {
    // Load products when the page loads
    loadProducts();
  
    // Add event listener for the "Add New Product" button
    const addProductButton = document.getElementById("add-product-btn");
    addProductButton.addEventListener("click", openAddProductModal);
  
    // Add event listener for the cancel button in the modal
    const cancelButton = document.getElementById("cancel-btn");
    cancelButton.addEventListener("click", closeProductModal);
  
    // Add event listener for the product form submission
    const productForm = document.getElementById("product-form");
    productForm.addEventListener("submit", handleProductFormSubmit);
  });
  
  // Function to load products and display them
  function loadProducts() {
    const products = JSON.parse(localStorage.getItem("products")) || [];
    const productsList = document.getElementById("products-list");
  
    productsList.innerHTML = ""; // Clear the list before loading
  
    products.forEach(product => {
      const productItem = document.createElement("div");
      productItem.classList.add("product-item");

      const productImage = document.createElement("img");
      productImage.classList.add("product-image");
      productImage.src = `../assets/images/${product.image}`;
      
  
      const productTitle = document.createElement("h4");
      productTitle.textContent = product.name;
  
      const productPrice = document.createElement("p");
      productPrice.textContent = `$${product.price}`;
  
      const productCategory = document.createElement("p");
      productCategory.textContent = `Category: ${product.category}`;
  
      const editButton = document.createElement("button");
      editButton.textContent = "Edit";
      editButton.onclick = () => openEditProductModal(product);
  
      const deleteButton = document.createElement("button");
      deleteButton.textContent = "Delete";
      deleteButton.onclick = () => deleteProduct(product.id);

      productItem.appendChild(productImage);
      productItem.appendChild(productTitle);
      productItem.appendChild(productPrice);
      productItem.appendChild(productCategory);
      productItem.appendChild(editButton);
      productItem.appendChild(deleteButton);
  
      productsList.appendChild(productItem);
    });
  }
  
  // Open the modal to add a new product
  function openAddProductModal() {
    document.getElementById("product-modal").style.display = "block";
    document.getElementById("modal-title").textContent = "Add New Product";
    document.getElementById("product-form").reset();
    document.getElementById("product-form").dataset.productId = "";
  }
  
  // Open the modal to edit an existing product
  function openEditProductModal(product) {
    document.getElementById("product-modal").style.display = "block";
    document.getElementById("modal-title").textContent = "Edit Product";
    
    document.getElementById("product-name").value = product.name;
    document.getElementById("product-price").value = product.price;
    document.getElementById("product-category").value = product.category;
    document.getElementById("product-form").dataset.productId = product.id;
  }
  
  // Close the modal
  function closeProductModal() {
    document.getElementById("product-modal").style.display = "none";
  }
  
  // Handle form submission for adding or editing a product
  function handleProductFormSubmit(event) {
    event.preventDefault();
  
    
    const name = document.getElementById("product-name").value;
    const price = parseFloat(document.getElementById("product-price").value);
    const image = document.getElementById("product-image").files[0].name; // Assuming images are uploaded and saved
    const category = document.getElementById("product-category").value;
    const productId = document.getElementById("product-form").dataset.productId;
  
    const newProduct = { id: productId || Date.now(), name, price, image, category };
  
    let products = JSON.parse(localStorage.getItem("products")) || [];
    
    // Edit existing product
    if (productId) {
      const productIndex = products.findIndex(product => product.id == productId);
      if (productIndex !== -1) {
        products[productIndex] = newProduct;
      }
    } 
    // Add new product
    else {
      products.push(newProduct);
    }
  
    localStorage.setItem("products", JSON.stringify(products));
    closeProductModal();
    loadProducts();
  }
  
  // Delete product from the list
  function deleteProduct(productId) {
    let products = JSON.parse(localStorage.getItem("products")) || [];
    products = products.filter(product => product.id !== productId);
    
    localStorage.setItem("products", JSON.stringify(products));
    loadProducts();
  }
  