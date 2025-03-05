<?php
// Start the session to handle login state
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Politeknik Brunei Marketing Day</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            /* Politeknik Brunei Color Scheme */
            --pb-yellow: #FFD700; /* Yellow */
            --pb-blue: #006F9C; /* Blue - Politeknik Brunei primary color */
            --pb-dark-blue: #0A1C4B; /* Dark Blue - Politeknik Brunei dark shade */
            --pb-light-yellow: #FFF3B0; /* Light yellow */
            --pb-accent: #1A2D5A; /* Accent color - Dark Blue */
            --pb-light: #F5F5F5;
            --pb-dark: #333333;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, var(--pb-blue), var(--pb-dark-blue));
            color: #fff;
            min-height: 100vh;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
        }
        
        .header-container {
            width: 100%;
            max-width: 1200px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            padding: 15px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .header-container img {
            max-width: 120px;
            margin-right: 20px;
        }
        
        .header-container h1 {
            font-size: 1.8rem;
            text-align: center;
            color: var(--pb-blue);
            font-weight: bold;
        }
        
        .intro {
            text-align: center;
            margin-bottom: 30px;
            max-width: 800px;
            background-color: rgba(0, 0, 0, 0.15);
            padding: 20px;
            border-radius: 10px;
        }
        
        .intro h2 {
            font-size: 2.2rem;
            margin-bottom: 1rem;
            color: var(--pb-yellow);
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
        }
        
        .intro p {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        
        .main-container {
            background: #fff;
            color: var(--pb-dark);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 1000px;
            border-top: 5px solid var(--pb-yellow);
        }
        
        .tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        
        .tabs button {
            background: var(--pb-blue);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 6px 6px 0 0;
            cursor: pointer;
            margin: 0 5px;
            font-size: 1rem;
            min-width: 120px;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
        }
        
        .tabs button:hover {
            background: var(--pb-dark-blue);
        }
        
        .tabs button.active {
            background: var(--pb-accent);
            border-bottom: 3px solid var(--pb-yellow);
        }
        
        .form-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }
        
        .form-container h2 {
            margin-bottom: 1.5rem;
            text-align: center;
            color: var(--pb-blue);
        }
        
        form {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            background-color: var(--pb-light);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .form-group {
            margin-bottom: 1.2rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: var(--pb-accent);
        }
        
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: border 0.3s;
        }
        
        input[type="text"]:focus, input[type="password"]:focus {
            border-color: var(--pb-yellow);
            outline: none;
            box-shadow: 0 0 0 2px rgba(255, 215, 0, 0.3);
        }
        
        .checkbox-container {
            display: flex;
            align-items: center;
            margin-bottom: 1.2rem;
        }
        
        .checkbox-container input {
            margin-right: 10px;
            accent-color: var(--pb-blue);
        }
        
        button[type="submit"] {
            width: 100%;
            padding: 0.75rem;
            background: var(--pb-blue);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background 0.3s;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        button[type="submit"]:hover {
            background: var(--pb-dark-blue);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 0.9rem;
            color: var(--pb-light-yellow);
        }
        
        /* Decorative elements */
        .pb-decoration {
            position: absolute;
            width: 50px;
            height: 50px;
            background-color: var(--pb-yellow);
            opacity: 0.1;
            border-radius: 50%;
            z-index: -1;
        }
        
        .pb-decoration:nth-child(1) {
            top: 10%;
            left: 10%;
            width: 100px;
            height: 100px;
        }
        
        .pb-decoration:nth-child(2) {
            bottom: 15%;
            right: 5%;
            width: 120px;
            height: 120px;
        }
        
        .pb-decoration:nth-child(3) {
            top: 40%;
            right: 20%;
            width: 80px;
            height: 80px;
        }
        
        @media screen and (max-width: 768px) {
            .header-container {
                flex-direction: column;
                padding: 10px;
            }
            
            .header-container img {
                margin-right: 0;
                margin-bottom: 10px;
            }
            
            .intro h2 {
                font-size: 1.8rem;
            }
            
            .intro p {
                font-size: 1rem;
            }
            
            .main-container {
                padding: 1.5rem;
            }
        }
        
        @media screen and (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .header-container h1 {
                font-size: 1.5rem;
            }
            
            .intro h2 {
                font-size: 1.5rem;
            }
            
            .main-container {
                padding: 1rem;
            }
            
            .tabs button {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
                min-width: 100px;
            }
            
            form {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Decorative elements -->
    <div class="pb-decoration"></div>
    <div class="pb-decoration"></div>
    <div class="pb-decoration"></div>

    <header class="header-container">
        <img src="media/pblogo.png" alt="Politeknik Brunei Logo">
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
        <p>&copy; 2025 Politeknik Brunei. All Rights Reserved.</p>
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
