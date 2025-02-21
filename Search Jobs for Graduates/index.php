<?php
    include 'header.php';
?>

<!-- <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Canvas Homepage Template</title>
  <style>
    body {
      font-family: 'Arial', sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f8f9fa;
      color: #333;
    }
    header {
      background-color: #343a40;
      color: white;
      padding: 1rem;
      text-align: center;
    }
    nav {
      background-color: #007bff;
      padding: 1rem;
      text-align: center;
    }
    nav a {
      color: white;
      text-decoration: none;
      margin: 0 1rem;
    }
    main {
      padding: 2rem;
      text-align: center;
    }
    footer {
      background-color: #343a40;
      color: white;
      padding: 1rem;
      position: relative;
      text-align: center;
    }
    .container {
      max-width: 1200px;
      margin: 0 auto;
    }
    .cta {
      background-color: #007bff;
      color: white;
      padding: 1rem 2rem;
      display: inline-block;
      text-decoration: none;
      border-radius: 4px;
    }
    .search-container {
      margin: 2rem 0;
      text-align: center;
    }
    .search-container input[type="text"] {
      padding: 0.5rem;
      width: 300px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    .search-container button {
      padding: 0.5rem 1rem;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
  </style>
</head> -->

<body>

<header>
  <h1>HomePage</h1>
</header>

<nav>
  <a href="#">Home</a>
  <a href="#">About Us</a>
  <a href="#">Services</a>
  <a href="#">Contact</a>
</nav>

<main class="container">
  <section>
    <div class="search-container">
        <form action="search_function.php" method="POST">
            <input type="text" name="search" id="search" placeholder="Search...">
      <!-- <select id="category">
        <option value="">Select Category</option>
        <option value="news">News</option>
        <option value="products">Products</option>
        <option value="articles">Articles</option>
      </select> -->
            <button type="submit" name="submit-search">Search</button>
        </form>
    </div>
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
    <a href="#" class="cta">Get Started</a>
  </section>
</main>

<footer>
  <p>&copy; 2025 TheSpinningCat Enterprise. All rights reserved.</p>
</footer>


</body>
</html>
