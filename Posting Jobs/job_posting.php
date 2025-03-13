<?php
// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'company_database');

// Initialize variables
$errors = [];
$successMessage = '';
$categories = [];

// Fetch categories from the database
try {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Fetch categories for the dropdown
    $result = $conn->query("SELECT category_id, category_name FROM job_categories");

if ($result === false) {
    throw new Exception("Error fetching job categories: " . $conn->error);
}
$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

    $conn->close();
} catch (Exception $e) {
    $errors[] = "Database error: " . $e->getMessage();
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Handle job deletion
        if (isset($_POST['delete_job'])) {
            $job_id = (int)$_POST['job_id'];
            $stmt = $conn->prepare("DELETE FROM jobs WHERE job_ID = ?");
            $stmt->bind_param("i", $job_id);
            if ($stmt->execute()) {
                $successMessage = "Job deleted successfully!";
            } else {
                throw new Exception("Error deleting job: " . $stmt->error);
            }
            $stmt->close();
        }
        // Handle new job posting
        elseif (isset($_POST['submit_job'])) {
            // Validate required fields
            $requiredFields = [
                'job_Title' => 'Job Title',
                'job_Description' => 'Job Description',
                'job_Requirements' => 'Job Requirements',
                'job_Category_id' => 'Job Category', // Updated to use category_id
                'job_location' => 'Job Location',
                'job_Vacancy' => 'Job Vacancy',
                'expiration_date' => 'Expiration Date'
            ];

            foreach ($requiredFields as $field => $name) {
                if (!isset($_POST[$field]) || empty($_POST[$field])) {  // Now it checks if the key exists before accessing it
                    $errors[] = "$name is required";
                }
            }

            // Additional validation for job_location (ensure it's a valid ENUM value)
            if (isset($_POST['job_location']) && !in_array($_POST['job_location'], ['Brunei Muara', 'Kuala Belait', 'Tutong', 'Temburong'])) {
                $errors[] = "Invalid job location selected.";
            }
            

            if (empty($errors)) {
                $job_Title = $_POST['job_Title'];
                $job_Description = $_POST['job_Description'];
                $job_Requirements = $_POST['job_Requirements'];
                $job_Category_id = (int)$_POST['job_Category_id']; // Use category_id
                $job_location = $_POST['job_location'];
                $job_Vacancy = (int)$_POST['job_Vacancy'];
                $expiration_date = $_POST['expiration_date'];

                // Get minimum and maximum salary (optional fields)
                $minimum_salary = !empty($_POST['minimum_salary']) ? (int)$_POST['minimum_salary'] : null;
                $maximum_salary = !empty($_POST['maximum_salary']) ? (int)$_POST['maximum_salary'] : null;

                // Validate salaries
                if ($minimum_salary !== null && $maximum_salary !== null && $maximum_salary < $minimum_salary) {
                    $errors[] = "Maximum salary must be greater than or equal to minimum salary.";
                }

                // Insert job with category_id
                $stmt = $conn->prepare("INSERT INTO jobs (job_Title, job_Description, job_Requirements, job_Category_id, job_location, job_Vacancy, minimum_salary, maximum_salary, application_deadline) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssisiiis", $job_Title, $job_Description, $job_Requirements, $job_Category_id, $job_location, $job_Vacancy, $minimum_salary, $maximum_salary, $expiration_date);

                if ($stmt->execute()) {
                    $successMessage = "Job posted successfully!";
                } else {
                    throw new Exception("Error: " . $stmt->error);
                }

                $stmt->close();
            }
        }

        $conn->close();
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

// Fetch and clean up expired jobs
try {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    // Delete expired jobs
    $conn->query("DELETE FROM jobs WHERE application_deadline < NOW()");

    $conn->close();
} catch (Exception $e) {
    $errors[] = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Job Posting Form</title>
    <link rel="stylesheet" type="text/css" href="css/job_posting.css">
    <script>
        function validateForm() {
            // Get the minimum and maximum salary values
            const minimumSalary = document.getElementById('minimum_salary').value;
            const maximumSalary = document.getElementById('maximum_salary').value;

            // Convert the values to numbers
            const minSalary = parseFloat(minimumSalary);
            const maxSalary = parseFloat(maximumSalary);

            // Get the error message element
            const errorMessage = document.getElementById('salary_error');

            // Check if both fields are filled and maximum salary is less than minimum salary
            if (minimumSalary && maximumSalary && maxSalary < minSalary) {
                errorMessage.textContent = "Maximum salary must be greater than or equal to minimum salary.";
                errorMessage.style.display = 'block'; // Show the error message
                return false; // Prevent form submission
            } else {
                errorMessage.style.display = 'none'; // Hide the error message
            }

            return true; // Allow form submission
        }
    </script>
</head>
<body>
    <div class="form-container">
        <h2>Company Job Posting Form</h2>

        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($successMessage)): ?>
            <div class="success">
                <p><?php echo htmlspecialchars($successMessage); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="job_Title">Job Title:</label>
                <input type="text" id="job_Title" name="job_Title" required>
            </div>

            <div class="form-group">
                <label for="job_Description">Job Description:</label>
                <textarea id="job_Description" name="job_Description" required></textarea>
            </div>

            <div class="form-group">
                <label for="job_Requirements">Job Requirements:</label>
                <textarea id="job_Requirements" name="job_Requirements" required></textarea>
            </div>

            <!-- Updated dropdown to use category_id -->
            <div class="form-group">
                <label for="job_Category">Job Category:</label>
                <select id="job_Category" name="job_Category_id" required>
                    <option value="">Select a category</option>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category['category_id']); ?>">
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="">No categories available</option>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Job Location Field -->
            <div class="form-group">
                <label for="job_location">Job Location:</label>
                <select id="job_location" name="job_location" required>
                    <option value="">Select a location</option>
                    <option value="Brunei Muara">Brunei Muara</option>
                    <option value="Kuala Belait">Kuala Belait</option>
                    <option value="Tutong">Tutong</option>
                    <option value="Temburong">Temburong</option>
                </select>
            </div>

            <div class="form-group">
                <label for="job_Vacancy">Number of Vacancies:</label>
                <input type="number" id="job_Vacancy" name="job_Vacancy" required min="1">
            </div>

            <div class="form-group">
                <label for="minimum_salary">Minimum Salary (Optional):</label>
                <input type="number" id="minimum_salary" name="minimum_salary" min="0">
            </div>

            <div class="form-group">
                <label for="maximum_salary">Maximum Salary (Optional):</label>
                <input type="number" id="maximum_salary" name="maximum_salary" min="0">
            </div>

            <!-- Error message for salary validation -->
            <div id="salary_error" class="error" style="display: none;"></div>

            <div class="form-group">
                <label for="expiration_date">Application Deadline:</label>
                <input type="datetime-local" id="expiration_date" name="expiration_date" required>
            </div>

            <button type="submit" name="submit_job">Post Job</button>
        </form>
    </div>
</body>
</html>