document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.getElementById('login-form');
    const errorMessage = document.querySelector('.error-message');
  
    if (loginForm) {
        loginForm.addEventListener('submit', async function (event) {
            event.preventDefault();
  
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();
  
            if (!email || !password) {
                if (errorMessage) {
                    errorMessage.textContent = 'Please enter both email and password.';
                    errorMessage.className = 'error-message';
                } else {
                    alert('Please enter both email and password.');
                }
                return;
            }
  
            try {
                const response = await fetch('/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ email, password })
                });
  
                const result = await response.json();
  
                if (response.ok && result.token) {
                    // Clear any existing session data
                    localStorage.removeItem('token');
                    localStorage.removeItem('user');
                    localStorage.removeItem('loggedInUser');
                    
                    // Store new session data
                    localStorage.setItem('token', result.token);
                    localStorage.setItem('user', JSON.stringify(result.user));
                    localStorage.setItem('loggedInUser', JSON.stringify(result.user));
                    
                    // Show success message
                    if (errorMessage) {
                        errorMessage.textContent = 'Login successful! Redirecting...';
                        errorMessage.className = 'success-message';
                    }
                    
                    // Redirect based on user role
                    setTimeout(() => {
                        if (result.user.role === 'admin') {
                            window.location.href = "/admin/dashboard";
                        } else {
                            window.location.href = "/";
                        }
                    }, 1000);
                } else {
                    if (errorMessage) {
                        errorMessage.textContent = result.error || "Invalid email or password.";
                        errorMessage.className = 'error-message';
                    } else {
                        alert(result.error || "Invalid email or password.");
                    }
                }
            } catch (error) {
                console.error('Login error:', error);
                if (errorMessage) {
                    errorMessage.textContent = "An error occurred during login. Please try again.";
                    errorMessage.className = 'error-message';
                } else {
                    alert("An error occurred during login. Please try again.");
                }
            }
        });
    }
});
  