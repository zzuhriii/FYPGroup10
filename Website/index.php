<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Politeknik Marketing Day</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/animations.css">
</head>
<body>
    <header class="animate-slideDown">
        <img src="pblogo.png" alt="Politeknik Logo" class="top-left-image animate-slideInLeft">
        <h1 class="animate-fadeIn">Welcome to Politeknik Marketing Day</h1>
    </header>

    <main>
        <section class="intro animate-slideUp">
            <h2>Find Your Perfect Career Match</h2>
            <p>Join our platform where opportunities meet talent. Whether you're a recent graduate looking to kickstart your career or a company seeking fresh talent, we've got you covered.</p>
            
            <div class="button-container">
                <button class="register-btn animate-fadeIn" onclick="window.location.href='authentication/register_graduate.php'">
                    Register as Graduate
                </button>
                <button class="company-btn animate-fadeIn-delay" onclick="window.location.href='authentication/register_company.php'">
                    Register as Company
                </button>
            </div>
        </section>
    </main>
</body>
</html>