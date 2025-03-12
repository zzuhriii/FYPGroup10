<?php
// Start the session to handle login state
session_start();

// Include database connection
require_once __DIR__ . '/includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Politeknik Brunei Marketing Day</title>
    <link rel="stylesheet" href="/Website/assets/css/index.css">
</head>
<body>
    <!-- Decorative elements -->
    <div class="pb-decoration"></div>
    <div class="pb-decoration"></div>
    <div class="pb-decoration"></div>

    <header class="header-container">
        <img src="/Website/assets/images/pblogo.png" alt="Politeknik Brunei Logo">
        <h1>Welcome to Politeknik Brunei Marketing Day</h1>
    </header>

    <section class="intro">
        <h2>Find Your Perfect Career Match</h2>
        <p>Join our platform where opportunities meet talent. Whether you're a recent graduate looking to kickstart your career or a company seeking fresh talent, we've got you covered.</p>
    </section>

    <div class="main-container">
        <div class="tabs">
            <button id="graduate-tab" onclick="showTab('graduate')">Graduate</button>
            <button id="company-tab" onclick="showTab('company')">Company</button>
        </div>

        <div class="form-container">
            <!-- Graduate Form -->
            <form id="graduate-form" action="/Website/authentication/login.php" method="POST">
                <input type="hidden" name="login_type" value="graduate">
                
                <div class="form-group">
                    <label for="graduate-ic">IC Number</label>
                    <input type="text" id="graduate-ic" name="identifier" placeholder="Enter your IC Number" required>
                </div>
                
                <div class="form-group">
                    <label for="graduate-password">Password</label>
                    <input type="password" id="graduate-password" name="password" placeholder="Enter your password" required>
                </div>
                
                <div class="checkbox-container">
                    <input type="checkbox" id="graduate-remember" name="remember_username">
                    <label for="graduate-remember">Remember Username</label>
                </div>
                
                <button type="submit">Login</button>
            </form>

            <!-- Company Form -->
            <form id="company-form" action="/Website/authentication/login.php" method="POST" style="display: none;">
                <input type="hidden" name="login_type" value="company">
                
                <div class="form-group">
                    <label for="company-email">Email</label>
                    <input type="text" id="company-email" name="identifier" placeholder="Enter your Email" required>
                </div>
                
                <div class="form-group">
                    <label for="company-password">Password</label>
                    <input type="password" id="company-password" name="password" placeholder="Enter your password" required>
                </div>
                
                <div class="checkbox-container">
                    <input type="checkbox" id="company-remember" name="remember_username">
                    <label for="company-remember">Remember Username</label>
                </div>
                
                <button type="submit">Login</button>
            </form>
            <p class="register-link">Don't have an account? <a href="/Website/authentication/register.php">Register here</a></p>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Politeknik Brunei.</p>
    </footer>

    <script>
        function showTab(tab) {
            // Update form display
            document.getElementById('graduate-form').style.display = tab === 'graduate' ? 'block' : 'none';
            document.getElementById('company-form').style.display = tab === 'company' ? 'block' : 'none';
            
            // Update tab styling
            document.getElementById('graduate-tab').classList.toggle('active', tab === 'graduate');
            document.getElementById('company-tab').classList.toggle('active', tab === 'company');
        }

        // Initialize on load
        window.onload = () => {
            // Default to graduate tab
            const selectedTab = 'graduate'; 
            showTab(selectedTab);
        }
    </script>
</body>
</html>
