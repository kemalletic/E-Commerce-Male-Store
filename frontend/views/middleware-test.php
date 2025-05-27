<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Middleware Test Page</title>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/frontend/assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Middleware Test Page</h1>
        
        <!-- Test Validation -->
        <div class="test-section">
            <h2>Test Validation Middleware</h2>
            <form id="validationForm" class="test-form">
                <div class="form-group">
                    <label for="required_field">Required Field:</label>
                    <input type="text" id="required_field" name="required_field">
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email">
                </div>
                
                <div class="form-group">
                    <label for="min_length">Min Length (3 chars):</label>
                    <input type="text" id="min_length" name="min_length">
                </div>
                
                <div class="form-group">
                    <label for="max_length">Max Length (10 chars):</label>
                    <input type="text" id="max_length" name="max_length">
                </div>
                
                <div class="form-group">
                    <label for="numeric">Numeric:</label>
                    <input type="text" id="numeric" name="numeric">
                </div>
                
                <button type="submit">Test Validation</button>
            </form>
        </div>

        <!-- Test Error Handling -->
        <div class="test-section">
            <h2>Test Error Handling Middleware</h2>
            <button id="testError" class="test-button">Trigger Error</button>
        </div>

        <!-- Test Success Response -->
        <div class="test-section">
            <h2>Test Success Response</h2>
            <button id="testSuccess" class="test-button">Test Success</button>
        </div>

        <!-- Response Display -->
        <div class="response-section">
            <h2>Response</h2>
            <pre id="response"></pre>
        </div>
    </div>

    <script>
        // Test Validation
        document.getElementById('validationForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            data.test_validation = true;

            try {
                const response = await fetch('<?php echo $baseUrl; ?>/test-middleware', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                document.getElementById('response').textContent = JSON.stringify(result, null, 2);
            } catch (error) {
                document.getElementById('response').textContent = 'Error: ' + error.message;
            }
        });

        // Test Error
        document.getElementById('testError').addEventListener('click', async () => {
            try {
                const response = await fetch('<?php echo $baseUrl; ?>/test-middleware', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ test_error: true })
                });
                const result = await response.json();
                document.getElementById('response').textContent = JSON.stringify(result, null, 2);
            } catch (error) {
                document.getElementById('response').textContent = 'Error: ' + error.message;
            }
        });

        // Test Success
        document.getElementById('testSuccess').addEventListener('click', async () => {
            try {
                const response = await fetch('<?php echo $baseUrl; ?>/test-middleware', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        required_field: 'test',
                        email: 'test@example.com',
                        min_length: 'abc',
                        max_length: 'short',
                        numeric: '123'
                    })
                });
                const result = await response.json();
                document.getElementById('response').textContent = JSON.stringify(result, null, 2);
            } catch (error) {
                document.getElementById('response').textContent = 'Error: ' + error.message;
            }
        });
    </script>

    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .test-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .test-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .test-button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .test-button:hover {
            background-color: #0056b3;
        }
        .response-section {
            margin-top: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        #response {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</body>
</html> 