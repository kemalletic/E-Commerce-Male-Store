const API_BASE_URL = 'http://localhost/e-commerce-website/backend/rest';

class ApiService {
    static async request(endpoint, method = 'GET', data = null) {
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json'
            }
        };

        if (data && (method === 'POST' || method === 'PUT')) {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(`${API_BASE_URL}/${endpoint}`, options);
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    // User endpoints
    static async login(email, password) {
        return this.request('users.php', 'POST', { email, password });
    }

    static async register(userData) {
        return this.request('users.php', 'POST', userData);
    }

    static async updateUser(id, userData) {
        return this.request(`users.php?id=${id}`, 'PUT', userData);
    }

    // Product endpoints
    static async getProducts() {
        return this.request('products.php');
    }

    static async getProduct(id) {
        return this.request(`products.php?id=${id}`);
    }

    static async createProduct(productData) {
        return this.request('products.php', 'POST', productData);
    }

    static async updateProduct(id, productData) {
        return this.request(`products.php?id=${id}`, 'PUT', productData);
    }

    static async deleteProduct(id) {
        return this.request(`products.php?id=${id}`, 'DELETE');
    }

    // Cart endpoints
    static async getCart() {
        return this.request('cart.php');
    }

    static async addToCart(productId, quantity) {
        return this.request('cart.php', 'POST', { product_id: productId, quantity });
    }

    static async updateCartItem(id, quantity) {
        return this.request(`cart.php?id=${id}`, 'PUT', { quantity });
    }

    static async removeFromCart(id) {
        return this.request(`cart.php?id=${id}`, 'DELETE');
    }

    // Order endpoints
    static async getOrders() {
        return this.request('orders.php');
    }

    static async createOrder(orderData) {
        return this.request('orders.php', 'POST', orderData);
    }

    static async updateOrder(id, status) {
        return this.request(`orders.php?id=${id}`, 'PUT', { status });
    }

    // Category endpoints
    static async getCategories() {
        return this.request('categories.php');
    }

    static async getCategory(id) {
        return this.request(`categories.php?id=${id}`);
    }
}

export default ApiService;
