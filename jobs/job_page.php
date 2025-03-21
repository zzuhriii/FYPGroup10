<?php
    include 'header.php';
    
    // Get company information for the job
    $company_name = "";
    if (isset($_GET['id'])) {
        $job_id = mysqli_real_escape_string($conn, $_GET['id']);
        $company_query = "SELECT c.name FROM jobs j JOIN companies c ON j.company_id = c.id WHERE j.job_ID = '$job_id'";
        $company_result = mysqli_query($conn, $company_query);
        if ($company_result && mysqli_num_rows($company_result) > 0) {
            $company_data = mysqli_fetch_assoc($company_result);
            $company_name = $company_data['name'];
        }
    }
?>
<body>
    <header>
    <h1>Job Page</h1>
    </header>

    <nav>
    <a href="index.php" title="Go to homepage">Home</a>
    <a href="#">About Us</a>
    <a href="#">Services</a>
    <a href="#">Contact</a>
    </nav>

    <div class="jobs-container">
        <?php
            $title = mysqli_real_escape_string($conn, $_GET['title']);
            $id = mysqli_real_escape_string($conn, $_GET['id']);

            $sql = "SELECT * FROM jobs WHERE job_Title='$title' AND job_ID='$id'";
            $result = mysqli_query($conn, $sql);
            $queryResults = mysqli_num_rows($result);

            if ($queryResults > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<div class='jobs-box'>
                        <h3>".$row['job_Title']."</h3>";
                    
                    // Display company name if available
                    if (!empty($company_name)) {
                        echo "<p>Company: ".$company_name."</p>";
                    }
                    
                    echo "<p>Job Industry: ".$row['job_Category']."</p>
                        <p>Description: <br>".$row['job_Description']."</p>
                        <p>Vacancy: ".$row['job_Vacancy']."</p>
                        <p>Date/Time Posted:<br>".$row['job_Offered']."</p>";
                    
                    // Add apply button for graduates
                    if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'graduate') {
                        echo "<form action='apply_job.php' method='POST'>
                            <input type='hidden' name='job_id' value='".$row['job_ID']."'>
                            <button type='submit' class='apply-btn'>Apply for this Job</button>
                        </form>";
                    } elseif (!isset($_SESSION['user_id'])) {
                        echo "<p><a href='/Website/index.php'>Login as a graduate</a> to apply for this job.</p>";
                    }
                    
                    echo "</div>";
                }
            }
        ?>
    </div>
    <footer>
        <p>&copy; 2025 TheSpinningCat Enterprise. All rights reserved.</p>
    </footer>
</body>
</html>
