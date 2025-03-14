<?php
    include 'header.php';

    // Fetch non-expired jobs, ordered by latest first
    $sql = "SELECT * FROM jobs WHERE is_expired = FALSE ORDER BY job_Created DESC";
    $result = mysqli_query($conn, $sql);

    // Check for query errors
    if (!$result) {
        die("Database query failed: " . mysqli_error($conn));
    }

    // Get the number of rows
    $queryResults = mysqli_num_rows($result);
?>

<body>
<header class="header-container">
    <img src="media/pblogo.png" alt="Politeknik Brunei Logo">
    <h1>Homepage</h1>
</header>

<nav>
    <a href="homepage_student.php">Home</a>
    <a href="#">About Us</a>
    <a href="#">Services</a>
    <a href="#">Contact</a>
</nav>

<main class="container">
    <section>
        <div class="search-container">
            <form action="search_function.php" method="POST">
                <input type="text" name="search" id="search" placeholder="Search...">
                <button type="submit" name="submit-search">Search</button>
            </form>
        </div>
        <h2>Latest Jobs Posted:</h2>
        <div class="jobs-container">
            <?php
                if ($queryResults > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        // Truncate description for display
                        $truncatedDescription = strlen($row['job_Description']) > 50 ? mb_substr($row['job_Description'], 0, 50, 'UTF-8') . '...' : $row['job_Description'];

                        // Output job details
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
                    echo "<p>No jobs available at the moment.</p>";
                }
            ?>
        </div>
    </section>
</main>

<footer>
    <p>&copy; 2025 Politeknik Brunei. All Rights Reserved.</p>
</footer>
</body>
</html>
