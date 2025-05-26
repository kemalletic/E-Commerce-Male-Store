document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.getElementById('login-form');
    const errorMessage = document.querySelector('.error-message');
  
    if (loginForm) {
        loginForm.addEventListener('submit', async function (event) {
            event.preventDefault();
  
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
  
            if (!email || !password) {
                if (errorMessage) {
                    errorMessage.textContent = 'Please enter both email and password.';
                } else {
                    alert('Please enter both email and password.');
                }
                return;
            }
  
            try {
                const response = await fetch("/login", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({ email, password })
                });
  
                const result = await response.json();
  
                if (response.ok && result.token) {
                    // Store JWT and user info in localStorage
                    localStorage.setItem("token", result.token);
                    localStorage.setItem("user", JSON.stringify(result.user));
                    localStorage.setItem("loggedInUser", JSON.stringify(result.user));
                    
                    console.log("Login successful. User role:", result.user.role);
                    
                    // Show success message
                    if (errorMessage) {
                        errorMessage.textContent = 'Login successful! Redirecting...';
                        errorMessage.className = 'success-message';
                    }
                    
                    // Redirect after a short delay
                    setTimeout(() => {
                        window.location.href = "/";
                    }, 1000);
                } else {
                    if (errorMessage) {
                        errorMessage.textContent = result.error || "Invalid email or password.";
                        errorMessage.className = 'error-message';
                    } else {
                        alert(result.error || "Invalid email or password.");
                    }
                }
            } catch (err) {
                console.error("Login error:", err);
                if (errorMessage) {
                    errorMessage.textContent = "An error occurred. Please try again.";
                    errorMessage.className = 'error-message';
                } else {
                    alert("An error occurred. Please try again.");
                }
            }
        });
    }
});
  