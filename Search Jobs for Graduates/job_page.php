<?php
    include 'header.php';
?>
<body>
    <header class="header-container">
        <img src="media/pblogo.png" alt="Politeknik Brunei Logo">
        <h1>Job Page:</h1>
    </header>

    <nav>
        <a href="homepage_student.php" title="Go to homepage">Home</a>
        <a href="#">About Us</a>
        <a href="#">Services</a>
        <a href="#">Contact</a>
    </nav>

    <div class="jobs-containerold">
        <?php
            // Sanitize and validate input
            $title = mysqli_real_escape_string($conn, $_GET['title']);
            $id = intval($_GET['id']); // Ensure ID is an integer

            // Prepare the SQL query with a JOIN to fetch the category name
            $sql = "SELECT j.*, c.category_name 
                    FROM jobs j
                    JOIN job_categories c ON j.job_Category_id = c.category_id
                    WHERE j.job_Title = ? AND j.job_ID = ?";
            
            // Prepare the statement
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                die("Database query preparation failed: " . mysqli_error($conn));
            }

            // Bind parameters
            mysqli_stmt_bind_param($stmt, "si", $title, $id);

            // Execute the query
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $queryResults = mysqli_num_rows($result);

            // Display results
            if ($queryResults > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<div class='jobs-boxold'>
                        <h3>".htmlspecialchars($row['job_Title'])."</h3>
                        <p><strong>Job Category:</strong> ".htmlspecialchars($row['category_name'])."</p>
                        <p><strong>Description:</strong><br>".htmlspecialchars($row['job_Description'])."</p>
                        <p><strong>Requirements:</strong><br>".htmlspecialchars($row['job_Requirements'])."</p>
                        <p><strong>Vacancy:</strong> ".htmlspecialchars($row['job_Vacancy'])."</p>
                        <p><strong>Date/Time Posted:</strong><br>".htmlspecialchars($row['job_Created'])."</p>
                        <p><strong>Application Deadline:</strong><br>".htmlspecialchars($row['application_deadline'])."</p>
                        <p><strong>Location:</strong> ".htmlspecialchars($row['job_location'])."</p>
                        <p><strong>Salary Range:</strong> BND".htmlspecialchars($row['minimum_salary'])." - BND".htmlspecialchars($row['maximum_salary'])."</p>

                        <!-- Apply Button -->
                        <a href='#' class='apply-button'>Apply Now</a>
                    </div>";
                }
            } else {
                echo "<p class='no-results'>Sorry, the job you are looking for does not exist.</p>";
            }

            // Close the statement
            mysqli_stmt_close($stmt);
        ?>
    </div>

    <footer>
        <p>&copy; 2025 Politeknik Brunei. All Rights Reserved.</p>
    </footer>
</body>
</html>
