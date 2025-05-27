document.getElementById("registerForm").addEventListener("submit", async function(event) {
    event.preventDefault();

    let username = document.getElementById("username").value.trim();
    let email = document.getElementById("email").value.trim();
    let password = document.getElementById("password").value;
    let confirmPassword = document.getElementById("confirmPassword").value;
    let passwordError = document.getElementById("passwordError");

    if (password !== confirmPassword) {
        passwordError.textContent = "Passwords do not match!";
        passwordError.style.color = "red";
        return;
    } else {
        passwordError.textContent = "";
    }

    // Prepare data as JSON
    let data = {
        name: username,
        email: email,
        password: password
        // role: "user" // Optionally allow user to select role, or default to user
    };

    try {
        let response = await fetch("/register", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify(data)
        });

        let result = await response.json();

        if (response.ok) {
            alert("Registration successful! You can now log in.");
            window.location.href = "/login";
        } else {
            alert(result.error || "Registration failed.");
        }
    } catch (err) {
        alert("An error occurred. Please try again.");
    }
});
