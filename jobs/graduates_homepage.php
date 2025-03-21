<?php
    include 'header.php';
    
    // Function to get full programme name from code
    function getProgrammeName($code) {
        if (empty($code)) return 'Any Programme';
        
        $programmes = [
            // School of Business
            'DAFA' => 'DIPLOMA IN ACCOUNTING AND FINANCE',
            'DBSM' => 'DIPLOMA IN BUSINESS STUDIES (MANAGEMENT)',
            'DBSMK' => 'DIPLOMA IN BUSINESS STUDIES (MARKETING)',
            'DHCM' => 'DIPLOMA IN HUMAN CAPITAL MANAGEMENT',
            'DAHMO' => 'DIPLOMA APPRENTICESHIP IN HOSPITALITY MANAGEMENT AND OPERATIONS',
            
            // School of Information and Communication Technology
            'DAD' => 'DIPLOMA IN APPLICATIONS DEVELOPMENT',
            'DCN' => 'DIPLOMA IN CLOUD AND NETWORKING',
            'DDA' => 'DIPLOMA IN DATA ANALYTICS',
            'DDAM' => 'DIGITAL ARTS AND MEDIA',
            'DWT' => 'DIPLOMA IN WEB TECHNOLOGY',
            
            // School of Health Sciences
            'DHSN' => 'DIPLOMA IN HEALTH SCIENCE (NURSING)',
            'DHSM' => 'DIPLOMA IN HEALTH SCIENCE (MIDWIFERY)',
            'DHSP' => 'DIPLOMA IN HEALTH SCIENCE (PARAMEDIC)',
            'DHSCT' => 'DIPLOMA IN HEALTH SCIENCE (CARDIOVASCULAR TECHNOLOGY)',
            'DHSPH' => 'DIPLOMA IN HEALTH SCIENCE (PUBLIC HEALTH)',
            
            // School of Architecture and Engineering
            'DA' => 'DIPLOMA IN ARCHITECTURE',
            'DID' => 'DIPLOMA IN INTERIOR DESIGN',
            'DCE' => 'DIPLOMA IN CIVIL ENGINEERING',
            'DEE' => 'DIPLOMA IN ELECTRICAL ENGINEERING',
            'DECE' => 'DIPLOMA IN ELECTRONIC AND COMMUNICATION ENGINEERING',
            'DME' => 'DIPLOMA IN MECHANICAL ENGINEERING',
            'DPE' => 'DIPLOMA IN PETROLEUM ENGINEERING'
        ];
        
        return isset($programmes[$code]) ? $programmes[$code] : $code;
    }
    
    // Check if user is logged in as a graduate
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'graduate') {
        header("Location: /Website/index.php");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Get user's programme for recommendations
    $user_sql = "SELECT programme FROM users WHERE id = '$user_id'";
    $user_result = mysqli_query($conn, $user_sql);
    $user = mysqli_fetch_assoc($user_result);
    $user_programme = $user['programme'] ?? '';
    
    // Get recommended jobs based on user's programme
    $recommended_jobs = [];
    if (!empty($user_programme)) {
        $rec_sql = "SELECT * FROM jobs WHERE programme = '$user_programme' ORDER BY job_Offered DESC LIMIT 10";
        $rec_result = mysqli_query($conn, $rec_sql);
        if ($rec_result && mysqli_num_rows($rec_result) > 0) {
            while ($job = mysqli_fetch_assoc($rec_result)) {
                $recommended_jobs[] = $job;
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Listings - Politeknik Brunei</title>
    <link rel="stylesheet" href="/Website/assets/css/index.css">
    <link rel="stylesheet" href="/Website/assets/css/graduates_homepage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .job-company {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .view-company-profile {
            display: inline-flex;
            align-items: center;
            background-color: #f0f8ff;
            color: #0066cc;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: 1px solid #cce5ff;
        }
        
        .view-company-profile:hover {
            background-color: #0066cc;
            color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .view-company-profile i {
            margin-right: 5px;
        }
        
        /* Make sure company name doesn't overflow */
        .job-company {
            overflow: hidden;
        }
        
        /* Floating return to dashboard button */
        .floating-dashboard-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: #3498db;
            color: white;
            padding: 15px 20px;
            border-radius: 50px;
            text-decoration: none;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .floating-dashboard-btn:hover {
            background-color: #2980b9;
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.25);
        }
        
        .floating-dashboard-btn i {
            margin-right: 8px;
        }
        
        /* Fix for tab content display */
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Politeknik Logo at top left -->
    <div style="position: fixed; top: 10px; left: 10px; z-index: 1000;">
        <a href="/Website/index.php">
            <img src="/Website/assets/images/pblogo.png" alt="Politeknik Brunei Logo" style="max-height: 60px;">
        </a>
    </div>

    <div class="container">
        <h1>Job Opportunities for Graduates</h1>
        
        <!-- Tabs for switching between all jobs and recommended jobs -->
        <div class="tabs">
            <button class="tab-button active" data-tab="all-jobs">All Jobs</button>
            <button class="tab-button" data-tab="recommended-jobs">Recommended for You</button>
        </div>
        
        <!-- Recommended Jobs Tab Content -->
        <div id="recommended-jobs" class="tab-content">
            <h2>Jobs Recommended for Your Programme: <?php echo htmlspecialchars($user_programme); ?></h2>
            
            <?php if (empty($recommended_jobs)): ?>
                <div class="no-jobs">
                    <p>No recommended jobs found for your programme.</p>
                    <p>Please check the "All Jobs" tab to explore all available opportunities.</p>
                </div>
            <?php else: ?>
                <div class="job-listings">
                    <?php foreach ($recommended_jobs as $job): 
                        // Get company name
                        $company_name = "Company";
                        if (isset($job['company_id'])) {
                            $company_id = $job['company_id'];
                            $company_sql = "SELECT name FROM users WHERE id = '$company_id' AND user_type = 'company'";
                            $company_result = mysqli_query($conn, $company_sql);
                            if ($company_result && mysqli_num_rows($company_result) > 0) {
                                $company_data = mysqli_fetch_assoc($company_result);
                                $company_name = $company_data['name'];
                            }
                        }
                    ?>
                        <div class="job-card">
                            <h2 class="job-title"><?php echo htmlspecialchars($job['job_Title']); ?></h2>
                            <div class="job-company">
                                <?php echo htmlspecialchars($company_name); ?>
                                <a href="/Website/company_profile/view_profile.php?company_id=<?php echo $job['company_id']; ?>" class="view-company-profile">
                                    <i class="fas fa-building"></i> View Company Profile
                                </a>
                            </div>
                            <div class="job-details">
                                <span>Programme: <?php echo getProgrammeName($job['programme']); ?></span>
                                <span>Vacancies: <?php echo htmlspecialchars($job['job_Vacancy']); ?></span>
                                <strong>Salary:</strong> &nbsp;<?php echo !empty($job['salary_estimation']) ? htmlspecialchars($job['salary_estimation']) . ' per month' : 'Not specified'; ?>
                            </div>
                            <div class="job-description">
                                <?php echo (strlen($job['job_Description']) > 200 ? 
                                    substr($job['job_Description'], 0, 200)."..." : 
                                    $job['job_Description']); ?>
                            </div>
                            <div class="job-actions">
                                <a href="view_job.php?id=<?php echo $job['job_ID']; ?>" class="apply-btn">View Details</a>
                                <span class="job-date">Posted on: 
                                <?php
                                    $date = $job['job_Offered'];
                                    if (!empty($date) && $date != '0000-00-00' && $date != '0000-00-00 00:00:00') {
                                        $timestamp = strtotime($date);
                                        if ($timestamp && $timestamp > 0 && date('Y', $timestamp) > 1970) {
                                            echo date('F d, Y', $timestamp);
                                        } else {
                                            echo date('F d, Y');
                                        }
                                    } else {
                                        echo date('F d, Y');
                                    }
                                ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- All Jobs Tab Content -->
        <div id="all-jobs" class="tab-content active">
         <!-- Replace your existing search form with this structure -->
            <form method="GET" action="" class="search-bar">
              <div class="search-container">
                <input type="text" name="search" placeholder="Search for jobs..." class="search-input" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="search-btn">Search</button>
              </div>
              
              <div class="filter-options">
                <select name="programme" class="filter-select" onchange="this.form.submit()">
                  <option value="">Filter by Programme</option>
                  <!-- School of Business -->
                  <optgroup label="School of Business">
                      <option value="DAFA" <?php echo (isset($_GET['programme']) && $_GET['programme'] == 'DAFA') ? 'selected' : ''; ?>>DIPLOMA IN ACCOUNTING AND FINANCE</option>
                      <option value="DBSM" <?php echo (isset($_GET['programme']) && $_GET['programme'] == 'DBSM') ? 'selected' : ''; ?>>DIPLOMA IN BUSINESS STUDIES (MANAGEMENT)</option>
                      <option value="DBSM" <?php echo (isset($_GET['programme']) && $_GET['programme'] == 'DBSM') ? 'selected' : ''; ?>>DIPLOMA IN BUSINESS STUDIES (MARKETING)</option>
                      <option value="DHCM" <?php echo (isset($_GET['programme']) && $_GET['programme'] == 'DHCM') ? 'selected' : ''; ?>>DIPLOMA IN HUMAN CAPITAL MANAGEMENT</option>
                      <option value="DAHMO" <?php echo (isset($_GET['programme']) && $_GET['programme'] == 'DAHMO') ? 'selected' : ''; ?>>DIPLOMA APPRENTICESHIP IN HOSPITALITY MANAGEMENT AND OPERATIONS</option>
                  </optgroup>
                  <!-- School of Information and Communication Technology -->
                  <optgroup label="School of Information and Communication Technology">
                      <option value="DAD" <?php echo (isset($_GET['programme']) && $_GET['programme'] == 'DAD') ? 'selected' : ''; ?>>DIPLOMA IN APPLICATIONS DEVELOPMENT</option>
                      <option value="DCN" <?php echo (isset($_GET['programme']) && $_GET['programme'] == 'DCN') ? 'selected' : ''; ?>>DIPLOMA IN CLOUD AND NETWORKING</option>
                      <option value="DDA" <?php echo (isset($_GET['programme']) && $_GET['programme'] == 'DDA') ? 'selected' : ''; ?>>DIPLOMA IN DATA ANALYTICS</option>
                      <option value="DDAM" <?php echo (isset($_GET['programme']) && $_GET['programme'] == 'DDAM') ? 'selected' : ''; ?>>DIGITAL ARTS AND MEDIA</option>
                      <option value="DWT" <?php echo (isset($_GET['programme']) && $_GET['programme'] == 'DWT') ? 'selected' : ''; ?>>DIPLOMA IN WEB TECHNOLOGY</option>
                  </optgroup>
                  <!-- School of Health Sciences -->
                  <optgroup label="School of Health Sciences">
                      <option value="DHSN" <?php echo (isset($_GET['programme']) && $_GET['programme'] == 'DHSN') ? 'selected' : ''; ?>>DIPLOMA IN HEALTH SCIENCE (NURSING)</option>
                      <option value="DHSM" <?php echo (isset($_GET['programme']) && $_GET['programme'] == 'DHSM') ? 'selected' : ''; ?>>DIPLOMA IN HEALTH SCIENCE (MIDWIFERY)</option>
                      <option value="DHSP" <?php echo (isset($_GET['programme']) && $_GET['programme'] == 'DHSP') ? 'selected' : ''; ?>>DIPLOMA IN HEALTH SCIENCE (PARAMEDIC)</option>
                      <option value="DHSCT" <?php echo (isset($_GET['programme']) && $_GET['programme'] == 'DHSCT') ? 'selected' : ''; ?>>DIPLOMA IN HEALTH SCIENCE (CARDIOVASCULAR TECHNOLOGY)</option>
                      <option value="DHSPH" <?php echo (isset($_GET['programme']) && $_GET['programme'] == 'DHSPH') ? 'selected' : ''; ?>>DIPLOMA IN HEALTH SCIENCE (PUBLIC HEALTH)</option>
                  </optgroup>
                  <!-- School of Architecture and Engineering -->
                  <optgroup label="School of Architecture and Engineering">
                      <option value="DA" <?php echo (isset($_GET['programme']) && $_GET['programme'] == 'DA') ? 'selected' : ''; ?>>DIPLOMA IN ARCHITECTURE</option>
                      <option value="DID" <?php echo (isset($_GET['programme']) && $_GET['programme'] == 'DID') ? 'selected' : ''; ?>>DIPLOMA IN INTERIOR DESIGN</option>
                      <option value="DCE" <?php echo (isset($_GET['programme']) && $_GET['programme'] == 'DCE') ? 'selected' : ''; ?>>DIPLOMA IN CIVIL ENGINEERING</option>
                      <option value="DEE" <?php echo (isset($_GET['programme']) && $_GET['programme'] == 'DEE') ? 'selected' : ''; ?>>DIPLOMA IN ELECTRICAL ENGINEERING</option>
                      <option value="DECE" <?php echo (isset($_GET['programme']) && $_GET['programme'] == 'DECE') ? 'selected' : ''; ?>>DIPLOMA IN ELECTRONIC AND COMMUNICATION ENGINEERING</option>
                      <option value="DME" <?php echo (isset($_GET['programme']) && $_GET['programme'] == 'DME') ? 'selected' : ''; ?>>DIPLOMA IN MECHANICAL ENGINEERING</option>
                      <option value="DPE" <?php echo (isset($_GET['programme']) && $_GET['programme'] == 'DPE') ? 'selected' : ''; ?>>DIPLOMA IN PETROLEUM ENGINEERING</option>
                  </optgroup>
                </select>
                
                <select name="sort" class="filter-select" onchange="this.form.submit()">
                  <option value="">Sort By</option>
                  <option value="newest" <?php echo (!isset($_GET['sort']) || $_GET['sort'] == 'newest') ? 'selected' : ''; ?>>Newest First</option>
                  <option value="oldest" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'oldest') ? 'selected' : ''; ?>>Oldest First</option>
                  <option value="deadline" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'deadline') ? 'selected' : ''; ?>>Deadline</option>
                </select>
                
                <?php if(isset($_GET['search'])): ?>
                  <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                <?php endif; ?>
              </div>
            </form>
            
            <?php
                // Build the SQL query with filters
                $sql = "SELECT * FROM jobs WHERE 1=1";
                
                // Add programme filter if selected
                if (isset($_GET['programme']) && !empty($_GET['programme'])) {
                    $programme = mysqli_real_escape_string($conn, $_GET['programme']);
                    $sql .= " AND (programme = '$programme' OR programme = '')";
                }
                
                // Add search filter if provided
                if (isset($_GET['search']) && !empty($_GET['search'])) {
                    $search = mysqli_real_escape_string($conn, $_GET['search']);
                    $sql .= " AND (job_Title LIKE '%$search%' OR job_Description LIKE '%$search%')";
                }
                
                // Add sorting
                if (isset($_GET['sort']) && $_GET['sort'] == 'oldest') {
                    $sql .= " ORDER BY job_Offered ASC";
                } else {
                    $sql .= " ORDER BY job_Offered DESC";
                }
                
                $result = mysqli_query($conn, $sql);
                
                if ($result && mysqli_num_rows($result) > 0) {
                    echo '<div class="job-listings">';
                    while ($job = mysqli_fetch_assoc($result)) {
                        // Get company name
                        $company_name = "Company";
                        if (isset($job['company_id'])) {
                            $company_id = $job['company_id'];
                            $company_sql = "SELECT name FROM users WHERE id = '$company_id' AND user_type = 'company'";
                            $company_result = mysqli_query($conn, $company_sql);
                            if ($company_result && mysqli_num_rows($company_result) > 0) {
                                $company_data = mysqli_fetch_assoc($company_result);
                                $company_name = $company_data['name'];
                            }
                        }
                        
                        echo "<div class='job-card'>
                            <h2 class='job-title'>".$job['job_Title']."</h2>
                            <div class='job-company'>
                                ".$company_name."
                                <a href='/Website/company_profile/view_profile.php?company_id=".$job['company_id']."' class='view-company-profile'>
                                    <i class='fas fa-building'></i> View Company Profile
                                </a>
                            </div>
                            <div class='job-details'>
                                <span>Programme: ".getProgrammeName($job['programme'])."</span>
                                <span>Vacancies: ".$job['job_Vacancy']."</span>
                                <span>Salary: ".(!empty($job['salary_estimation']) ? htmlspecialchars($job['salary_estimation']) : 'Not specified')."</span>
                            </div>
                            <div class='job-description'>".
                                (strlen($job['job_Description']) > 200 ? 
                                    substr($job['job_Description'], 0, 200)."..." : 
                                    $job['job_Description'])
                            ."</div>
                            <div class='job-actions'>
                                <a href='view_job.php?id=".$job['job_ID']."' class='apply-btn'>View Details</a>
                                <span class='job-date'>Posted on: ";
                                
                                // Fix date display
                                $date = $job['job_Offered'];
                                if (!empty($date) && $date != '0000-00-00' && $date != '0000-00-00 00:00:00') {
                                    $timestamp = strtotime($date);
                                    if ($timestamp && $timestamp > 0 && date('Y', $timestamp) > 1970) {
                                        echo date('F d, Y', $timestamp);
                                    } else {
                                        echo date('F d, Y'); // Current date as fallback
                                    }
                                } else {
                                    echo date('F d, Y'); // Current date as fallback
                                }
                                
                                // Add live vacancy count
                            echo "</span>";
                            
                            // Check if job_applications table exists
                            $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'job_applications'");
                            if (mysqli_num_rows($table_check) > 0) {
                                // Count accepted applications for this job
                                $job_id = $job['job_ID'];
                                $count_sql = "SELECT COUNT(*) as accepted_count FROM job_applications WHERE job_id = '$job_id' AND status = 'accepted'";
                                $count_result = mysqli_query($conn, $count_sql);
                                
                                if ($count_result && $row = mysqli_fetch_assoc($count_result)) {
                                    $accepted_count = $row['accepted_count'];
                                    $remaining = max(0, intval($job['job_Vacancy']) - $accepted_count);
                                    echo "<span class='vacancy-count'>" . $remaining . " positions remaining</span>";
                                }
                            }
                            
                            echo "
                        </div>
                    </div>";
                }
            } else {
                echo "<div class='no-jobs'>
                    <p>No job listings are currently available.</p>
                    <p>Please check back later for new opportunities.</p>
                </div>";
            }
        ?>
    </div>
    
  
    
    <!-- Floating Return to Dashboard Button -->
    <a href="/Website/main/graduate_dashboard.php" class="floating-dashboard-btn">
        <i class="fas fa-home"></i> Dashboard
    </a>
    
    <script>
        // Tab functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons and contents
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Add active class to clicked button and corresponding content
                    this.classList.add('active');
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                    
                    // Ensure the floating button remains visible
                    document.querySelector('.floating-dashboard-btn').style.display = 'flex';
                });
            });
        });
    </script>
</body>
</html>
