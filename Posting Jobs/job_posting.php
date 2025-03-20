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


// Fetch courses for checkboxes
$courses = [];
try {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    $result_courses = $conn->query("SELECT course_id, course_name FROM courses");
    if ($result_courses === false) {
        throw new Exception("Error fetching courses: " . $conn->error);
    }
    while ($row = $result_courses->fetch_assoc()) {
        $courses[] = $row;
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

        // Handle new job posting
        elseif (isset($_POST['submit_job'])) {
            // Validate required fields
            $requiredFields = [
                'job_Title' => 'Job Title',
                'job_Description' => 'Job Description',
                'job_Requirements' => 'Job Requirements',
                'job_Category_id' => 'Job Category',
                'job_location' => 'Job Location',
                'job_Vacancy' => 'Job Vacancy',
                'minimum_salary'  => 'Minimum Salary',
                'maximum_salary'  => 'Maximum Salary',
                'expiration_date' => 'Expiration Date'
            ];

            foreach ($requiredFields as $field => $name) {
                if (!isset($_POST[$field]) || empty($_POST[$field])) {  // Now it checks if the key exists before accessing it
                    $errors[] = "$name is required";
                }
            }


            // Validate that at least one course is selected
            if (!isset($_POST['eligible_courses']) || empty($_POST['eligible_courses'])) {
                $errors[] = "Please select at least one eligible course.";
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

                

                $minimum_salary = (int)$_POST['minimum_salary'];
                $maximum_salary = (int)$_POST['maximum_salary'];

                // Validate salaries
                if ($minimum_salary !== null && $maximum_salary !== null && $maximum_salary < $minimum_salary) {
                    $errors[] = "Maximum salary must be greater than or equal to minimum salary.";
                }

                // Insert job with category_id
                $stmt = $conn->prepare("INSERT INTO jobs (job_Title, job_Description, job_Requirements, job_Category_id, job_location, job_Vacancy, minimum_salary, maximum_salary, application_deadline) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssisiiis", $job_Title, $job_Description, $job_Requirements, $job_Category_id, $job_location, $job_Vacancy, $minimum_salary, $maximum_salary, $expiration_date);

                if ($stmt->execute()) {
                    $job_id = $conn->insert_id; // Retrieve the newly inserted job's ID

                    // --- Insert eligible courses ---
                    if (!empty($_POST['eligible_courses']) && is_array($_POST['eligible_courses'])) {
                        $stmt_course = $conn->prepare("INSERT INTO job_courses (job_ID, course_id) VALUES (?, ?)");
                        foreach ($_POST['eligible_courses'] as $course_id) {
                            $course_id = (int)$course_id;
                            $stmt_course->bind_param("ii", $job_id, $course_id);
                            $stmt_course->execute();
                        }
                        $stmt_course->close();
                    }

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
                <input type="text" id="job_Title" name="job_Title" required
                value="<?php echo isset($_POST['job_Title']) ? htmlspecialchars($_POST['job_Title']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="job_Description">Job Description:</label>
                <textarea id="job_Description" name="job_Description" required><?php echo isset($_POST['job_Description']) ? htmlspecialchars($_POST['job_Description']) : ''; ?></textarea>
                
            </div>

            <div class="form-group">
                <label for="job_Requirements">Job Requirements:</label>
                <textarea id="job_Requirements" name="job_Requirements" required><?php echo isset($_POST['job_Requirements']) ? htmlspecialchars($_POST['job_Requirements']) : ''; ?></textarea>
            </div>

            
            <div class="form-group">
                <label for="job_Category">Job Category:</label>
                <select id="job_Category" name="job_Category_id" required>
                    <option value="">Select a category</option>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category['category_id']); ?>"
                            <?php echo (isset($_POST['job_Category_id']) && $_POST['job_Category_id'] == $category['category_id']) ? 'selected' : ''; ?>>
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
                    <option value="Brunei Muara" <?php echo (isset($_POST['job_location']) && $_POST['job_location'] == "Brunei Muara") ? 'selected' : ''; ?>>
                        Brunei Muara
                    </option>
                    <option value="Kuala Belait" <?php echo (isset($_POST['job_location']) && $_POST['job_location'] == "Kuala Belait") ? 'selected' : ''; ?>>
                        Kuala Belait
                    </option>
                    <option value="Tutong" <?php echo (isset($_POST['job_location']) && $_POST['job_location'] == "Tutong") ? 'selected' : ''; ?>>
                        Tutong
                    </option>
                    <option value="Temburong" <?php echo (isset($_POST['job_location']) && $_POST['job_location'] == "Temburong") ? 'selected' : ''; ?>>
                        Temburong
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label for="job_Vacancy">Number of Vacancies:</label>
                <input type="number" id="job_Vacancy" name="job_Vacancy" required min="1"
                value="<?php echo isset($_POST['job_Vacancy']) ? htmlspecialchars($_POST['job_Vacancy']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>Eligible Courses (Select at least one):</label>
                <div class="checkbox-pills">
                    <?php if (!empty($courses)): ?>
                        <?php foreach ($courses as $course): ?>
                            <div class="checkbox-pill-item">
                                <input type="checkbox" name="eligible_courses[]" 
                                    value="<?php echo htmlspecialchars($course['course_id']); ?>" 
                                    id="course_<?php echo htmlspecialchars($course['course_id']); ?>"
                                    <?php 
                                        if (isset($_POST['eligible_courses']) && in_array($course['course_id'], $_POST['eligible_courses'])) {
                                            echo 'checked';
                                        }
                                    ?>>
                                <label for="course_<?php echo htmlspecialchars($course['course_id']); ?>">
                                    <?php echo htmlspecialchars($course['course_name']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No courses available.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="minimum_salary">Minimum Salary:</label>
                <input type="number" id="minimum_salary" name="minimum_salary" min="0" required
                value="<?php echo isset($_POST['minimum_salary']) ? htmlspecialchars($_POST['minimum_salary']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="maximum_salary">Maximum Salary:</label>
                <input type="number" id="maximum_salary" name="maximum_salary" min="0" required
                value="<?php echo isset($_POST['maximum_salary']) ? htmlspecialchars($_POST['maximum_salary']) : ''; ?>">
            </div>

            <!-- Error message for salary validation -->
            <div id="salary_error" class="error" style="display: none;"></div>

            <div class="form-group">
                <label for="expiration_date">Application Deadline:</label>
                <input type="datetime-local" id="expiration_date" name="expiration_date" required
                value="<?php echo isset($_POST['expiration_date']) ? htmlspecialchars($_POST['expiration_date']) : ''; ?>">
            </div>

            <button type="submit" name="submit_job">Post Job</button>
        </form>
    </div>
</body>
</html>
