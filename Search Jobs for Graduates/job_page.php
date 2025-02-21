<?php
    include 'header.php';
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
                        <h3>".$row['job_Title']."</h3>
                        <p>Job Industry: ".$row['job_Category']."</p>
                        <p>Description: <br>".$row['job_Description']."</p>
                        <p>Vacancy: ".$row['job_Vacancy']."</p>
                        <p>Date/Time Posted:<br>".$row['job_Offered']."</p>
                    </div>";
                }
            }
        ?>
    </div>
    <footer>
        <p>&copy; 2025 TheSpinningCat Enterprise. All rights reserved.</p>
    </footer>
</body>
</html>
