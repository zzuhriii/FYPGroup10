<?php
    include 'header.php';
?>
<header>
    <h1>Search Page</h1>
</header>

<nav>
    <a href="index.php" title="Go to homepage">Home</a>
    <a href="#">About Us</a>
    <a href="#">Services</a>
    <a href="#">Contact</a>
</nav>


<div class="jobs-container">
    <?php
        if (isset($_POST['submit-search'])) {
            $search = mysqli_real_escape_string($conn, $_POST['search']);
            $sql = "SELECT * FROM jobs WHERE job_Title LIKE '%$search%' 
                OR job_Description LIKE '%$search%' 
                OR job_Category LIKE '%$search%'";
            
            $result = mysqli_query($conn, $sql);
            $queryResult = mysqli_num_rows($result);


            if ($queryResult > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<a href='job_page.php?title=".$row['job_Title']."&id=".$row['job_ID']."'><div class='jobs-box'>
                        <h3>".$row['job_Title']."</h3>
                        <p>".$row['job_Description']."</p>
                    </div></a>";
                }
            }
            else {
                echo "Sorry.. There are no results matching your search!";
            }
        }
        echo "<p><center>".$queryResult." results matches!</center></p>";
    ?>
</div>
<footer>
    <p>&copy; 2025 TheSpinningCat Enterprise. All rights reserved.</p>
</footer>