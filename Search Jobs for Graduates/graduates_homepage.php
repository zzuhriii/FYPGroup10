<?php
    include 'header.php';
?>
<body>
    <form action="search_function.php" method="POST">
        <input type="text" name="search" placeholder="Search">
        <button type="submit" name="submit-search">Search</button>
    </form>

    <h1>Front Page</h1>
    <h2>Jobs available:</h2>

    <div class="jobs-container">
        <?php
            $sql = "SELECT * FROM jobs";
            $result = mysqli_query($conn, $sql);
            $queryResults = mysqli_num_rows($result);

            if ($queryResults > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<div class='jobs-box'>
                        <h3>".$row['job_Title']."</h3>
                        <p>".$row['job_Description']."</p>
                    </div>";
                }
            }
        ?>
    </div>
</body>
</html>
