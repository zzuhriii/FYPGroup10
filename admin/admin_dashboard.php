<?php
session_start();
include '../includes/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Get statistics
// Total users
$users_sql = "SELECT COUNT(*) as total FROM users";
$users_result = $conn->query($users_sql);
$total_users = $users_result->fetch_assoc()['total'];

// Total companies
$companies_sql = "SELECT COUNT(*) as total FROM users WHERE user_type = 'company'";
$companies_result = $conn->query($companies_sql);
$total_companies = $companies_result->fetch_assoc()['total'];

// Total jobs
$jobs_sql = "SELECT COUNT(*) as total FROM jobs";
$jobs_result = $conn->query($jobs_sql);
$total_jobs = $jobs_result->fetch_assoc()['total'];

// Total applications
$applications_sql = "SELECT COUNT(*) as total FROM job_applications";
$applications_result = $conn->query($applications_sql);
$total_applications = $applications_result->fetch_assoc()['total'];

// Recent jobs
$recent_jobs_sql = "SELECT j.*, u.name as company_name 
                   FROM jobs j 
                   LEFT JOIN users u ON j.job_UserID = u.id 
                   ORDER BY j.job_Created DESC LIMIT 5";
$recent_jobs_result = $conn->query($recent_jobs_sql);

// Recent applications
$recent_applications_sql = "SELECT a.*, j.job_Title, u.name as applicant_name 
                           FROM job_applications a 
                           JOIN jobs j ON a.job_id = j.job_ID 
                           JOIN users u ON a.user_id = u.id 
                           ORDER BY a.application_date DESC LIMIT 5";
$recent_applications_result = $conn->query($recent_applications_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Politeknik Brunei Marketing Day</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        
        .wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding-top: 20px;
            position: fixed;
            height: 100%;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            text-align: center;
            border-bottom: 1px solid #3d5166;
        }
        
        .sidebar-header img {
            max-width: 120px;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            color: #ecf0f1;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .menu-item:hover, .menu-item.active {
            background-color: #34495e;
        }
        
        .menu-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            color: #2c3e50;
            font-size: 24px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-info span {
            margin-right: 15px;
            color: #6c757d;
        }
        
        .logout-btn {
            background-color: #f8f9fa;
            color: #6c757d;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            font-size: 14px;
        }
        
        .logout-btn:hover {
            background-color: #e9ecef;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .stat-card .icon {
            font-size: 36px;
            margin-bottom: 15px;
            width: 70px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: white;
        }
        
        .stat-card .icon.users {
            background-color: #3498db;
        }
        
        .stat-card .icon.companies {
            background-color: #2ecc71;
        }
        
        .stat-card .icon.jobs {
            background-color: #f39c12;
        }
        
        .stat-card .icon.applications {
            background-color: #9b59b6;
        }
        
        .stat-card .count {
            font-size: 28px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .stat-card .label {
            color: #6c757d;
            font-size: 14px;
        }
        
        .recent-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .recent-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .recent-card h2 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #2c3e50;
            font-size: 18px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eaeaea;
        }
        
        .recent-item {
            padding: 12px 0;
            border-bottom: 1px solid #eaeaea;
        }
        
        .recent-item:last-child {
            border-bottom: none;
        }
        
        .recent-item .title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .recent-item .meta {
            display: flex;
            justify-content: space-between;
            color: #6c757d;
            font-size: 14px;
        }
        
        .recent-item .status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .recent-item .status.pending {
            background-color: #ffeeba;
            color: #856404;
        }
        
        .recent-item .status.accepted {
            background-color: #d4edda;
            color: #155724;
        }
        
        .recent-item .status.declined {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .view-all {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #3498db;
            text-decoration: none;
            font-size: 14px;
        }
        
        .view-all:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }
            
            .sidebar-header {
                padding: 0 10px 10px;
            }
            
            .sidebar-header img {
                max-width: 50px;
            }
            
            .menu-item {
                padding: 12px;
                justify-content: center;
            }
            
            .menu-item i {
                margin-right: 0;
            }
            
            .menu-item span {
                display: none;
            }
            
            .content {
                margin-left: 70px;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="/Website/assets/images/pblogo.png" alt="Politeknik Brunei Logo">
            </div>
            
            <div class="sidebar-menu">
                <a href="admin_dashboard.php" class="menu-item active">
                    <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                </a>
                <a href="admin_graduates.php" class="menu-item">
                    <i class="fas fa-user-graduate"></i> <span>Manage Graduates</span>
                </a>
                <a href="admin_companies.php" class="menu-item">
                    <i class="fas fa-building"></i> <span>Manage Companies</span>
                </a>
                <a href="admin_jobs.php" class="menu-item">
                    <i class="fas fa-briefcase"></i> <span>Manage Jobs</span>
                </a>
                <a href="admin_applications.php" class="menu-item">
                    <i class="fas fa-file-alt"></i> <span>Applications</span>
                </a>
                <a href="admin_settings.php" class="menu-item">
                    <i class="fas fa-cog"></i> <span>Settings</span>
                </a>
            </div>
        </div>
        
        <div class="content">
            <div class="header">
                <h1>Admin Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="admin_logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
            
            <div class="stats-container">
                <div class="stat-card">
                    <div class="icon users">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="count"><?php echo $total_users; ?></div>
                    <div class="label">Total Users</div>
                </div>
                
                <div class="stat-card">
                    <div class="icon companies">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="count"><?php echo $total_companies; ?></div>
                    <div class="label">Companies</div>
                </div>
                
                <div class="stat-card">
                    <div class="icon jobs">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="count"><?php echo $total_jobs; ?></div>
                    <div class="label">Jobs Posted</div>
                </div>
                
                <div class="stat-card">
                    <div class="icon applications">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="count"><?php echo $total_applications; ?></div>
                    <div class="label">Applications</div>
                </div>
            </div>
            
            <div class="recent-container">
                <div class="recent-card">
                    <h2>Recent Jobs</h2>
                    <?php if ($recent_jobs_result && $recent_jobs_result->num_rows > 0): ?>
                        <?php while ($job = $recent_jobs_result->fetch_assoc()): ?>
                            <div class="recent-item">
                                <div class="title"><?php echo htmlspecialchars($job['job_Title']); ?></div>
                                <div class="meta">
                                    <span><?php echo htmlspecialchars($job['company_name']); ?></span>
                                    <span><?php echo date('M d, Y', strtotime($job['job_Created'])); ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        <a href="admin_jobs.php" class="view-all">View All Jobs</a>
                    <?php else: ?>
                        <p>No jobs found</p>
                    <?php endif; ?>
                </div>
                
                <div class="recent-card">
                    <h2>Recent Applications</h2>
                    <?php if ($recent_applications_result && $recent_applications_result->num_rows > 0): ?>
                        <?php while ($app = $recent_applications_result->fetch_assoc()): ?>
                            <div class="recent-item">
                                <div class="title"><?php echo htmlspecialchars($app['job_Title']); ?></div>
                                <div class="meta">
                                    <span><?php echo htmlspecialchars($app['applicant_name']); ?></span>
                                    <span>
                                        <span class="status <?php echo strtolower($app['status']); ?>">
                                            <?php echo ucfirst($app['status']); ?>
                                        </span>
                                    </span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        <a href="admin_applications.php" class="view-all">View All Applications</a>
                    <?php else: ?>
                        <p>No applications found</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>