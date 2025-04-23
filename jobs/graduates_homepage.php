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
    $user_sql = "SELECT programme FROM users WHERE id = ?";
    $stmt = $conn->prepare($user_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $user_programme = $user['programme'] ?? '';
    
    // Get recommended jobs based on user's programme
    $recommended_jobs = [];
    if (!empty($user_programme)) {
        $rec_sql = "SELECT * FROM jobs WHERE programme = ? AND is_active = 1 ORDER BY job_Offered DESC LIMIT 10";
        $rec_stmt = $conn->prepare($rec_sql);
        $rec_stmt->bind_param("s", $user_programme);
        $rec_stmt->execute();
        $rec_result = $rec_stmt->get_result();
        
        if ($rec_result && $rec_result->num_rows > 0) {
            while ($job = $rec_result->fetch_assoc()) {
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
</head>
<body>
    <!-- Politeknik Logo at top left with proper left alignment -->
    <div style="position: absolute; top: 15px; left: 15px; z-index: 1000; text-align: left;">
        <a href="/Website/index.php">
            <img src="/Website/assets/images/pblogo.png" alt="Politeknik Brunei Logo" style="height: 60px;">
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
            <h2>Jobs Recommended for Your Programme: <?php echo htmlspecialchars(getProgrammeName($user_programme)); ?></h2>
            
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
                            $company_sql = "SELECT name FROM users WHERE id = ? AND user_type = 'company'";
                            $company_stmt = $conn->prepare($company_sql);
                            $company_stmt->bind_param("i", $company_id);
                            $company_stmt->execute();
                            $company_result = $company_stmt->get_result();
                            
                            if ($company_result && $company_result->num_rows > 0) {
                                $company_data = $company_result->fetch_assoc();
                                $company_name = $company_data['name'];
                            }
                        }
                    ?>
                        <div class="job-card">
                            <h2 class="job-title"><?php echo htmlspecialchars($job['job_Title']); ?></h2>
                            <div class="job-company">
                                <div style="display: flex; flex-direction: column;">
                                    <span style="font-weight: bold; margin-bottom: 8px;"><?php echo htmlspecialchars($company_name); ?></span>
                                    <div>
                                        <a href="/Website/company_profile/companyprofile.php?id=<?php echo $job['company_id']; ?>" class="view-company-profile">
                                            <i class="fas fa-building"></i> View Company Profile
                                        </a>
                                        <a href="/Website/company_profile/location_map.php?company_id=<?php echo $job['company_id']; ?>" class="view-company-location">
                                            <i class="fas fa-map-marker-alt"></i> View Location
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="job-details">
                                <span>Programme: <?php echo htmlspecialchars(getProgrammeName($job['programme'])); ?></span>
                                <span>Vacancies: <?php echo htmlspecialchars($job['job_Vacancy']); ?></span>
                                <span>Salary: <?php echo ($job['min_salary'] && $job['max_salary']) ? 'BND ' . number_format($job['min_salary']) . ' - BND ' . number_format($job['max_salary']) . ' per month' : 'Not specified'; ?></span>
                            </div>
                            <div class="job-description">
                                <?php echo htmlspecialchars(strlen($job['job_Description']) > 200 ? 
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
            <!-- Search form with filters -->
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
                            <option value="DBSMK" <?php echo (isset($_GET['programme']) && $_GET['programme'] == 'DBSMK') ? 'selected' : ''; ?>>DIPLOMA IN BUSINESS STUDIES (MARKETING)</option>
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

                    <!-- <label for="salary_min">Minimum Salary (BND):</label> -->
                    <input type="number" id="salary_min" placeholder="Minimum Salary (BND)" name="salary_min" step="100" value="<?php echo isset($_GET['salary_min']) ? htmlspecialchars($_GET['salary_min']) : ''; ?>">
    
                    <!-- <label for="salary_max">Maximum Salary (BND):</label> -->
                    <input type="number" id="salary_max" placeholder="Minimum Salary (BND)" name="salary_max" step="100" value="<?php echo isset($_GET['salary_max']) ? htmlspecialchars($_GET['salary_max']) : ''; ?>">
                </div>
            </form>
            
            <?php
                // Build the SQL query with filters
                $sql = "SELECT * FROM jobs WHERE is_active = 1";
                $params = [];
                $types = "";
                
                // Add programme filter if selected
                if (isset($_GET['programme']) && !empty($_GET['programme'])) {
                    $sql .= " AND (programme = ? OR programme = '')";
                    $params[] = $_GET['programme'];
                    $types .= "s";
                }
                
                // Add search filter if provided
                if (isset($_GET['search']) && !empty($_GET['search'])) {
                    $search_term = "%" . $_GET['search'] . "%";
                    $sql .= " AND (job_Title LIKE ? OR job_Description LIKE ?)";
                    $params[] = $search_term;
                    $params[] = $search_term;
                    $types .= "ss";
                }

                // Add salary filter if provided
                if (isset($_GET['salary_min']) && isset($_GET['salary_max']) && 
                is_numeric($_GET['salary_min']) && is_numeric($_GET['salary_max'])) {

                $salary_min = intval($_GET['salary_min']);
                $salary_max = intval($_GET['salary_max']);

                // Only add to SQL if min is less than or equal to max
                if ($salary_min <= $salary_max) {
                    $sql .= " AND (min_salary >= $salary_min AND max_salary <= $salary_max)";
                }
                
                // Add sorting
                if (isset($_GET['sort']) && $_GET['sort'] == 'oldest') {
                    $sql .= " ORDER BY job_Offered ASC";
                } else if (isset($_GET['sort']) && $_GET['sort'] == 'deadline') {
                    $sql .= " ORDER BY application_deadline ASC";
                } else {
                    $sql .= " ORDER BY job_Offered DESC";
                }
                
                // Prepare and execute the query
                $stmt = $conn->prepare($sql);
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result && $result->num_rows > 0) {
                    echo '<div class="job-listings">';
                    while ($job = $result->fetch_assoc()) {
                        // Get company name
                        $company_name = "Company";
                        if (isset($job['company_id'])) {
                            $company_id = $job['company_id'];
                            $company_sql = "SELECT name FROM users WHERE id = ? AND user_type = 'company'";
                            $company_stmt = $conn->prepare($company_sql);
                            $company_stmt->bind_param("i", $company_id);
                            $company_stmt->execute();
                            $company_result = $company_stmt->get_result();
                            
                            if ($company_result && $company_result->num_rows > 0) {
                                $company_data = $company_result->fetch_assoc();
                                $company_name = $company_data['name'];
                            }
                        }
                        
                        echo "<div class='job-card'>
                            <h2 class='job-title'>".htmlspecialchars($job['job_Title'])."</h2>
                            <div class='job-company'>
                                <div style='display: flex; flex-direction: column;'>
                                    <span style='font-weight: bold; margin-bottom: 8px;'>".htmlspecialchars($company_name)."</span>
                                    <div>
                                        <a href='/Website/company_profile/companyprofile.php?id=".$job['company_id']."' class='view-company-profile'>
                                            <i class='fas fa-building'></i> View Company Profile
                                        </a>
                                        <a href='/Website/company_profile/location_map.php?company_id=".$job['company_id']."' class='view-company-location'>
                                            <i class='fas fa-map-marker-alt'></i> View Location
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class='job-details'>
                                <span>Programme: ".htmlspecialchars(getProgrammeName($job['programme']))."</span>
                                <span>Vacancies: ".htmlspecialchars($job['job_Vacancy'])."</span>
                                <span>Salary: ".(($job['min_salary'] && $job['max_salary']) ? "BND " . number_format($job['min_salary']) . " - BND " . number_format($job['max_salary']) . " per month" : "Not specified")."</span>
                            </div>
                            <div class='job-description'>".
                                htmlspecialchars(strlen($job['job_Description']) > 200 ? 
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
                                        echo date('F d, Y h:i A', $timestamp);
                                    } else {
                                        echo date('F d, Y h:i A'); // Current date as fallback
                                    }
                                } else {
                                    echo date('F d, Y h:i A'); // Current date as fallback
                                }
                                
                            echo "</span>";
                            
                            // Check if job_applications table exists
                            $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'job_applications'");
                            if (mysqli_num_rows($table_check) > 0) {
                                // Count accepted applications for this job
                                $job_id = isset($job['job_ID']) ? $job['job_ID'] : (isset($job['job_id']) ? $job['job_id'] : null);
                                if ($job_id) {
                                    $count_sql = "SELECT COUNT(*) as accepted_count FROM job_applications WHERE job_id = ? AND status = 'accepted'";
                                    $count_stmt = $conn->prepare($count_sql);
                                    $count_stmt->bind_param("i", $job_id);
                                    $count_stmt->execute();
                                    $count_result = $count_stmt->get_result();
                                    
                                    if ($count_result && $row = $count_result->fetch_assoc()) {
                                        $accepted_count = $row['accepted_count'];
                                        $remaining = max(0, intval($job['job_Vacancy']) - $accepted_count);
                                        echo "<span class='vacancy-count'>" . $remaining . " positions remaining</span>";
                                    }
                                }
                            }
                            
                            echo "
                            </div>
                        </div>";
                    }
                    echo "</div>";
                } else {
                    echo "<div class='no-jobs'>
                        <p>No job listings are currently available.</p>
                        <p>Please check back later for new opportunities.</p>
                    </div>";
            }
        ?>
        </div>
    </div>
    
    <!-- Include the universal floating dashboard button -->
    <?php include_once '../includes/floating-button.php'; ?>
    
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
                });
            });
        });
    </script>
</body>
</html>
