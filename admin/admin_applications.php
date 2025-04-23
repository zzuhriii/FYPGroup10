<?php
session_start();
include '../includes/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle application actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $application_id = $_GET['id'];
    
    if ($action == 'approve') {
        // Approve application
        $update_sql = "UPDATE job_applications SET status = 'accepted' WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $application_id);
        
        if ($update_stmt->execute()) {
            $message = "Application approved successfully";
            $messageType = "success";
        } else {
            $message = "Error approving application: " . $conn->error;
            $messageType = "error";
        }
    } elseif ($action == 'reject') {
        // Reject application
        $update_sql = "UPDATE job_applications SET status = 'declined' WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $application_id);
        
        if ($update_stmt->execute()) {
            $message = "Application rejected successfully";
            $messageType = "success";
        } else {
            $message = "Error rejecting application: " . $conn->error;
            $messageType = "error";
        }
    } elseif ($action == 'delete') {
        // Delete application
        $delete_sql = "DELETE FROM job_applications WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $application_id);
        
        if ($delete_stmt->execute()) {
            $message = "Application deleted successfully";
            $messageType = "success";
        } else {
            $message = "Error deleting application: " . $conn->error;
            $messageType = "error";
        }
    }
}

// Get applications with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

// Build query
$query = "SELECT a.*, j.job_Title, u.name as applicant_name, c.name as company_name 
          FROM job_applications a 
          JOIN jobs j ON a.job_id = j.job_ID 
          JOIN users u ON a.user_id = u.id 
          JOIN users c ON j.job_UserID = c.id";
          
$countQuery = "SELECT COUNT(*) as total FROM job_applications a 
               JOIN jobs j ON a.job_id = j.job_ID 
               JOIN users u ON a.user_id = u.id 
               JOIN users c ON j.job_UserID = c.id";

$whereClause = [];
$params = [];
$types = "";

if (!empty($search)) {
    $whereClause[] = "(j.job_Title LIKE ? OR u.name LIKE ? OR c.name LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "sss";
}

if (!empty($filter)) {
    $whereClause[] = "a.status = ?";
    $params[] = $filter;
    $types .= "s";
}

if (!empty($whereClause)) {
    $query .= " WHERE " . implode(" AND ", $whereClause);
    $countQuery .= " WHERE " . implode(" AND ", $whereClause);
}

$query .= " ORDER BY a.application_date DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $limit;
$types .= "ii";

// Get total records
$countStmt = $conn->prepare($countQuery);
if (!empty($params) && count($params) > 0 && !empty($types)) {
    // Remove the last two parameters (offset and limit) for the count query
    $countParams = array_slice($params, 0, -2);
    $countTypes = substr($types, 0, -2);
    
    if (!empty($countParams)) {
        $countStmt->bind_param($countTypes, ...$countParams);
    }
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRecords = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $limit);

// Get applications
$stmt = $conn->prepare($query);
if (!empty($params) && !empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Applications - Admin Dashboard</title>
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
        
        .filters {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            background-color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .search-box {
            display: flex;
            flex: 1;
            max-width: 400px;
        }
        
        .search-box input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ced4da;
            border-radius: 4px 0 0 4px;
            font-size: 14px;
        }
        
        .search-box button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 0 15px;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }
        
        .filter-options select {
            padding: 10px 15px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
            background-color: white;
        }
        
        .applications-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .applications-table th, .applications-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eaeaea;
        }
        
        .applications-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .applications-table tr:last-child td {
            border-bottom: none;
        }
        
        .applications-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status.pending {
            background-color: #ffeeba;
            color: #856404;
        }
        
        .status.accepted {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status.declined {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .action-links a {
            display: inline-block;
            margin-right: 10px;
            color: #3498db;
            text-decoration: none;
        }
        
        .action-links a:hover {
            text-decoration: underline;
        }
        
        .action-links a.delete {
            color: #dc3545;
        }
        
        .action-links a.approve {
            color: #28a745;
        }
        
        .action-links a.reject {
            color: #dc3545;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        
        .pagination a, .pagination span {
            display: inline-block;
            padding: 8px 12px;
            margin: 0 5px;
            border-radius: 4px;
            text-decoration: none;
            color: #3498db;
            background-color: white;
            border: 1px solid #dee2e6;
        }
        
        .pagination a:hover {
            background-color: #e9ecef;
        }
        
        .pagination span.current {
            background-color: #3498db;
            color: white;
            border-color: #3498db;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
                <a href="admin_dashboard.php" class="menu-item">
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
                <a href="admin_applications.php" class="menu-item active">
                    <i class="fas fa-file-alt"></i> <span>Applications</span>
                </a>
                <a href="admin_settings.php" class="menu-item">
                    <i class="fas fa-cog"></i> <span>Settings</span>
                </a>
            </div>
        </div>
        
        <div class="content">
            <div class="header">
                <h1>Manage Applications</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="admin_logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
            
            <?php if (isset($message)): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <div class="filters">
                <form class="search-box" method="GET" action="">
                    <input type="text" name="search" placeholder="Search by job title, applicant or company" value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
                
                <div class="filter-options">
                    <select name="filter" onchange="this.form.submit()">
                        <option value="">All Applications</option>
                        <option value="pending" <?php echo $filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="accepted" <?php echo $filter == 'accepted' ? 'selected' : ''; ?>>Accepted</option>
                        <option value="declined" <?php echo $filter == 'declined' ? 'selected' : ''; ?>>Declined</option>
                    </select>
                </div>
            </div>
            
            <table class="applications-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Job Title</th>
                        <th>Applicant</th>
                        <th>Company</th>
                        <th>Applied On</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($app = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $app['id']; ?></td>
                                <td><?php echo htmlspecialchars($app['job_Title']); ?></td>
                                <td><?php echo htmlspecialchars($app['applicant_name']); ?></td>
                                <td><?php echo htmlspecialchars($app['company_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($app['application_date'])); ?></td>
                                <td>
                                    <span class="status <?php echo strtolower($app['status']); ?>">
                                        <?php echo ucfirst($app['status']); ?>
                                    </span>
                                </td>
                                <td class="action-links">
                                    <a href="admin_view_application.php?id=<?php echo $app['id']; ?>">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <?php if ($app['status'] == 'pending'): ?>
                                        <a href="admin_applications.php?action=approve&id=<?php echo $app['id']; ?>" class="approve">
                                            <i class="fas fa-check"></i> Approve
                                        </a>
                                        <a href="admin_applications.php?action=reject&id=<?php echo $app['id']; ?>" class="reject">
                                            <i class="fas fa-times"></i> Reject
                                        </a>
                                    <?php endif; ?>
                                    <a href="admin_applications.php?action=delete&id=<?php echo $app['id']; ?>" class="delete" onclick="return confirm('Are you sure you want to delete this application?');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No applications found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo urlencode($filter); ?>">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo urlencode($filter); ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo urlencode($filter); ?>">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>