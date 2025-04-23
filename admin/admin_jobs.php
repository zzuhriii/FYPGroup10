<?php
session_start();
include '../includes/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle job actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $job_id = $_GET['id'];
    
    if ($action == 'delete') {
        // Delete job
        $delete_sql = "DELETE FROM jobs WHERE job_ID = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $job_id);
        
        if ($delete_stmt->execute()) {
            $message = "Job deleted successfully";
            $messageType = "success";
        } else {
            $message = "Error deleting job: " . $conn->error;
            $messageType = "error";
        }
    } elseif ($action == 'toggle') {
        // Toggle job active status
        $status_sql = "UPDATE jobs SET is_active = NOT is_active WHERE job_ID = ?";
        $status_stmt = $conn->prepare($status_sql);
        $status_stmt->bind_param("i", $job_id);
        
        if ($status_stmt->execute()) {
            $message = "Job status updated successfully";
            $messageType = "success";
        } else {
            $message = "Error updating job status: " . $conn->error;
            $messageType = "error";
        }
    }
}

// Get jobs with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

// Build query
$query = "SELECT j.*, u.name as company_name 
          FROM jobs j 
          LEFT JOIN users u ON j.job_UserID = u.id";
$countQuery = "SELECT COUNT(*) as total FROM jobs j LEFT JOIN users u ON j.job_UserID = u.id";

$whereClause = [];
$params = [];
$types = "";

if (!empty($search)) {
    $whereClause[] = "(j.job_Title LIKE ? OR j.job_Description LIKE ? OR u.name LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "sss";
}

if ($filter == 'active') {
    $whereClause[] = "j.is_active = 1";
} elseif ($filter == 'inactive') {
    $whereClause[] = "j.is_active = 0";
}

if (!empty($whereClause)) {
    $query .= " WHERE " . implode(" AND ", $whereClause);
    $countQuery .= " WHERE " . implode(" AND ", $whereClause);
}

$query .= " ORDER BY j.job_Created DESC LIMIT ?, ?";
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

// Get jobs
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
    <title>Manage Jobs - Admin Dashboard</title>
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
        
        .filter-options {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .filter-options select {
            padding: 10px 15px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
            background-color: white;
        }
        
        .jobs-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .jobs-table th, .jobs-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eaeaea;
        }
        
        .jobs-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .jobs-table tr:last-child td {
            border-bottom: none;
        }
        
        .jobs-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status.active {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status.inactive {
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
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="admin_graduates.php" class="menu-item">
                    <i class="fas fa-user-graduate"></i> Manage Graduates
                </a>
                <a href="admin_companies.php" class="menu-item">
                    <i class="fas fa-building"></i> Manage Companies
                </a>
                <a href="admin_jobs.php" class="menu-item active">
                    <i class="fas fa-briefcase"></i> Manage Jobs
                </a>
                <a href="admin_applications.php" class="menu-item">
                    <i class="fas fa-file-alt"></i> Applications
                </a>
                <a href="admin_settings.php" class="menu-item">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </div>
        </div>
        
        <div class="content">
            <div class="header">
                <h1>Manage Jobs</h1>
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
                    <input type="text" name="search" placeholder="Search by title, description or company" value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
                
                <div class="filter-options">
                    <select name="filter" onchange="this.form.submit()">
                        <option value="">All Jobs</option>
                        <option value="active" <?php echo $filter == 'active' ? 'selected' : ''; ?>>Active Jobs</option>
                        <option value="inactive" <?php echo $filter == 'inactive' ? 'selected' : ''; ?>>Inactive Jobs</option>
                    </select>
                </div>
            </div>
            
            <table class="jobs-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Company</th>
                        <th>Category</th>
                        <th>Vacancies</th>
                        <th>Posted On</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($job = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $job['job_ID']; ?></td>
                                <td><?php echo htmlspecialchars($job['job_Title']); ?></td>
                                <td><?php echo htmlspecialchars($job['company_name']); ?></td>
                                <td><?php echo htmlspecialchars($job['job_Category']); ?></td>
                                <td><?php echo $job['job_Vacancy']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($job['job_Created'])); ?></td>
                                <td>
                                    <span class="status <?php echo $job['is_active'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $job['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="action-links">
                                    <a href="admin_view_job.php?id=<?php echo $job['job_ID']; ?>">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="admin_edit_job.php?id=<?php echo $job['job_ID']; ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="admin_jobs.php?action=toggle&id=<?php echo $job['job_ID']; ?>">
                                        <i class="fas fa-toggle-<?php echo $job['is_active'] ? 'on' : 'off'; ?>"></i> 
                                        <?php echo $job['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                    </a>
                                    <a href="admin_jobs.php?action=delete&id=<?php echo $job['job_ID']; ?>" class="delete" onclick="return confirm('Are you sure you want to delete this job?');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">No jobs found</td>
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