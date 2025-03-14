<?php
    include 'header.php';
?>
<header class="header-container">
    <img src="media/pblogo.png" alt="Politeknik Brunei Logo">
    <h1>Search Page</h1>
</header>

<nav>
    <a href="homepage_student.php" title="Go to homepage">Home</a>
    <a href="#">About Us</a>
    <a href="#">Services</a>
    <a href="#">Contact</a>
</nav>

<div class="jobs-container">
    <?php
        if (isset($_POST['submit-search'])) {
            // Sanitize and prepare the search term
            $search = trim($_POST['search']);
            $search = mysqli_real_escape_string($conn, $search);

            // Prepare the SQL query with a JOIN to search by category name
            $sql = "SELECT j.*, c.category_name 
                    FROM jobs j
                    JOIN job_categories c ON j.job_Category_id = c.category_id
                    WHERE j.job_Title LIKE ? 
                    OR j.job_Description LIKE ? 
                    OR c.category_name LIKE ? 
                    AND j.is_expired = FALSE
                    ORDER BY j.job_Created DESC";

            // Prepare the statement
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                die("Database query preparation failed: " . mysqli_error($conn));
            }

            // Bind parameters
            $searchTerm = "%$search%";
            mysqli_stmt_bind_param($stmt, "sss", $searchTerm, $searchTerm, $searchTerm);

            // Execute the query
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $queryResult = mysqli_num_rows($result);

            // Display results
            if ($queryResult > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    // Truncate description for display
                    $truncatedDescription = strlen($row['job_Description']) > 50 ? mb_substr($row['job_Description'], 0, 50, 'UTF-8') . '...' : $row['job_Description'];

                    // Output job details (sanitize output)
                    echo "<a href='job_page.php?title=".urlencode($row['job_Title'])."&id=".intval($row['job_ID'])."' class='job-link'>
                        <div class='jobs-box'>
                            <h3>".htmlspecialchars($row['job_Title'])."</h3>
                            <p>".htmlspecialchars($truncatedDescription)."</p>
                            <div class='details-row'>
                                <p class='deadline'><br>Application Deadline:<br> ".htmlspecialchars($row['application_deadline'])."</p>
                                <p class='salary'><br>Salary:<br> BND".htmlspecialchars($row['minimum_salary'])." - BND".htmlspecialchars($row['maximum_salary'])."</p>
                            </div>
                        </div>
                    </a>";
                }
            } else {
                echo "<p class='no-results'>Sorry, there are no results matching your search!</p>";
            }

            // Display the number of results
            echo "<p class='results-count'><center>".$queryResult." results found!</center></p>";

            // Close the statement
            mysqli_stmt_close($stmt);
        }
    ?>
</div>

<footer>
    <p>&copy; 2025 Politeknik Brunei. All Rights Reserved.</p>
</footer>
