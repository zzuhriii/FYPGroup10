<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "marketing_day";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle form submission to add a new student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $student_id = $_POST['student_id'];
    $name = $_POST['name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $cgpa = $_POST['cgpa'];
    $school = $_POST['school'];

    if (empty($student_id) || empty($name) || empty($age) || empty($gender) || empty($cgpa) || empty($school)) {
        echo "<p>All fields are required!</p>";
    } else {
        // Insert into students table
        $stmt = $conn->prepare("INSERT INTO students (student_id, name, age, gender, cgpa, school) VALUES (:student_id, :name, :age, :gender, :cgpa, :school)");
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':age', $age);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':cgpa', $cgpa);
        $stmt->bindParam(':school', $school);

        if ($stmt->execute()) {
            echo "<script>alert('Student added successfully!'); window.location.href='FindingApplicants.php';</script>";
        } else {
            echo "<p>Error adding student.</p>";
        }
    }
}

// Handle form submission to update student details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_student'])) {
    $student_id = $_POST['student_id'];
    $name = $_POST['name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $cgpa = $_POST['cgpa'];
    $school = $_POST['school'];

    if (empty($name) || empty($age) || empty($gender) || empty($cgpa) || empty($school)) {
        echo "<p>All fields are required!</p>";
    } else {
        // Update students table
        $stmt = $conn->prepare("UPDATE students SET name = :name, age = :age, gender = :gender, cgpa = :cgpa, school = :school WHERE student_id = :student_id");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':age', $age);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':cgpa', $cgpa);
        $stmt->bindParam(':school', $school);
        $stmt->bindParam(':student_id', $student_id);

        if ($stmt->execute()) {
            echo "<script>alert('Student updated successfully!'); window.location.href='FindingApplicants.php';</script>";
        } else {
            echo "<p>Error updating student.</p>";
        }
    }
}

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Fetch students with pagination (MySQL uses LIMIT offset, count)
$stmt = $conn->prepare("SELECT * FROM students LIMIT :offset, :limit");
$stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', (int) $records_per_page, PDO::PARAM_INT);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total records for pagination
$total_records = $conn->query("SELECT COUNT(*) FROM students")->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .edit-form, .add-form { display: none; margin-top: 20px; padding: 10px; border: 1px solid #ccc; width: 300px; background-color: #f9f9f9; }
        .edit-form input, .add-form input, .edit-form select, .add-form select { width: 100%; padding: 5px; margin: 5px 0; }
        .edit-form button, .add-form button { background-color: blue; color: white; padding: 7px; border: none; cursor: pointer; }
        .delete-button { margin-top: 20px; }
        .toggle-button { margin-bottom: 20px; }
        .pagination { margin-top: 20px; }
        .pagination a { padding: 8px 16px; text-decoration: none; border: 1px solid #ddd; color: #333; }
        .pagination a.active { background-color: #4CAF50; color: white; border: 1px solid #4CAF50; }
        .pagination a:hover:not(.active) { background-color: #ddd; }
    </style>
</head>
<body>

<h2>Student List</h2>

<!-- Toggle Button for Add Student Form -->
<button class="toggle-button" onclick="toggleAddForm('addStudentForm')">Add New Student</button>

<!-- Add Student Form -->
<div id="addStudentForm" class="add-form">
    <h2>Add New Student</h2>
    <form method="POST" action="">
        <label>Student ID:</label>
        <input type="text" name="student_id" placeholder="Student ID" required>
        
        <label>Name:</label>
        <input type="text" name="name" placeholder="Name" required>

        <label>Age:</label>
        <input type="number" name="age" placeholder="Age" required>

        <label>Gender:</label>
        <select name="gender">
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select>

        <label>CGPA:</label>
        <input type="number" step="0.01" name="cgpa" placeholder="CGPA" required>

        <label>School:</label>
        <input type="text" name="school" placeholder="School" required>

        <button type="submit" name="add_student">Add Student</button>
    </form>
</div>

<!-- Toggle Button for Add Contact Form -->
<button class="toggle-button" onclick="toggleAddForm('addContactForm')">Add New Contact</button>

<!-- Add Contact Form -->
<div id="addContactForm" class="add-form">
    <h2>Add New Contact</h2>
    <form method="POST" action="">
        <label>Student ID:</label>
        <input type="text" name="student_id" placeholder="Student ID" required>

        <label>Phone Number:</label>
        <input type="text" name="phone_number" placeholder="Phone Number" required>

        <label>Email:</label>
        <input type="email" name="email" placeholder="Email" required>

        <button type="submit" name="add_contact">Add Contact</button>
    </form>
</div>

<!-- Delete Students Form -->
<form method="POST" action="">
    <button type="submit" name="delete_students" class="delete-button">Delete Selected Students</button>

    <!-- Display Students -->
    <table>
        <tr>
            <th>Select</th>
            <th>ID</th>
            <th>Name</th>
            <th>Age</th>
            <th>Gender</th>
            <th>CGPA</th>
            <th>School</th>
            <th>Action</th>
        </tr>
        <?php foreach ($students as $student): ?>
        <tr>
            <td>
                <input type="checkbox" name="delete[]" value="<?php echo $student['student_id']; ?>">
            </td>
            <td><?php echo $student['student_id']; ?></td>
            <td><?php echo $student['name']; ?></td>
            <td><?php echo $student['age']; ?></td>
            <td><?php echo $student['gender']; ?></td>
            <td><?php echo $student['cgpa']; ?></td>
            <td><?php echo $student['school']; ?></td>
            <td>
                <button type="button" onclick="openEditForm(
                    '<?php echo $student['student_id']; ?>', 
                    '<?php echo htmlspecialchars($student['name'], ENT_QUOTES); ?>', 
                    <?php echo $student['age']; ?>, 
                    '<?php echo htmlspecialchars($student['gender'], ENT_QUOTES); ?>', 
                    <?php echo $student['cgpa']; ?>, 
                    '<?php echo htmlspecialchars($student['school'], ENT_QUOTES); ?>'
                )">Edit</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</form>

<!-- Pagination Links -->
<div class="pagination">
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="FindingApplicants.php?page=<?php echo $i; ?>" <?php echo ($page == $i) ? 'class="active"' : ''; ?>>
            <?php echo $i; ?>
        </a>
    <?php endfor; ?>
</div>

<!-- Edit Form -->
<div id="editForm" class="edit-form">
    <h2>Edit Student</h2>
    <form method="POST" action="">
        <input type="hidden" id="editStudentId" name="student_id">
        
        <label>Name:</label>
        <input type="text" id="editName" name="name">

        <label>Age:</label>
        <input type="number" id="editAge" name="age">

        <label>Gender:</label>
        <select id="editGender" name="gender">
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select>

        <label>CGPA:</label>
        <input type="number" step="0.01" id="editCgpa" name="cgpa">

        <label>School:</label>
        <input type="text" id="editSchool" name="school">

        <button type="submit" name="edit_student">Update Student</button>
    </form>
</div>

<!-- Edit Contact Form -->
<div id="editContactForm" class="edit-form">
    <h2>Edit Contact</h2>
    <form method="POST" action="">
        <input type="hidden" id="editContactStudentId" name="student_id">
        
        <label>Phone Number:</label>
        <input type="text" id="editPhoneNumber" name="phone_number">

        <label>Email:</label>
        <input type="email" id="editEmail" name="email">

        <button type="submit" name="edit_contact">Update Contact</button>
    </form>
</div>

<script>
// JavaScript to open the edit form and populate it with student data
function openEditForm(id, name, age, gender, cgpa, school) {
    document.getElementById("editStudentId").value = id;
    document.getElementById("editName").value = name;
    document.getElementById("editAge").value = age;
    document.getElementById("editGender").value = gender;
    document.getElementById("editCgpa").value = cgpa;
    document.getElementById("editSchool").value = school;

    document.getElementById("editForm").style.display = "block";
    window.scrollTo(0, document.getElementById("editForm").offsetTop);
}

// JavaScript to open the edit form and populate it with contact data
function openEditContactForm(student_id, phone_number, email) {
    document.getElementById("editContactStudentId").value = student_id;
    document.getElementById("editPhoneNumber").value = phone_number;
    document.getElementById("editEmail").value = email;

    document.getElementById("editContactForm").style.display = "block";
    window.scrollTo(0, document.getElementById("editContactForm").offsetTop);
}

// JavaScript to toggle the visibility of the Add Student/Contact Form
function toggleAddForm(formId) {
    const addForm = document.getElementById(formId);
    if (addForm.style.display === "none") {
        addForm.style.display = "block";
    } else {
        addForm.style.display = "none";
    }
}
</script>

</body>
</html>