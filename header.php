<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Politeknik Brunei Marketing Day</title>
    <link rel="stylesheet" href="/Website/assets/css/index.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="/Website/index.php">Politeknik Brunei Marketing Day</a>
            </div>
            <nav>
                <ul>
                    <li><a href="/Website/index.php">Home</a></li>
                    <li><a href="/Website/jobs/index.php">Jobs</a></li>
                    <li><a href="/Website/about.php">About</a></li>
                    <li><a href="/Website/contact.php">Contact</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php if($_SESSION['user_type'] == 'company'): ?>
                            <li><a href="/Website/company_profile/company_dashboard.php">Dashboard</a></li>
                        <?php else: ?>
                            <li><a href="/Website/profile.php">Profile</a></li>
                        <?php endif; ?>
                        <li><a href="/Website/logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="/Website/login.php">Login</a></li>
                        <li><a href="/Website/register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>