<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /Website/authentication/login.php");
    exit();
}

// Database connection
require_once '../includes/db.php';

// Get user data
$user_id = $_SESSION['user_id'];
$sql = "SELECT u.name, u.email, u.phone, u.ic_number, u.profile_pic, u.cv, 
               e.education_level, e.institution, e.field_of_study, e.graduation_year,
               a.title as achievement_title, a.description as achievement_description, a.year as achievement_year,
               w.company, w.position, w.start_date, w.end_date, w.description as work_description
        FROM users u
        LEFT JOIN education e ON u.id = e.user_id
        LEFT JOIN achievements a ON u.id = a.user_id
        LEFT JOIN work_experience w ON u.id = w.user_id
        WHERE u.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$user = null;
$education = [];
$achievements = [];
$work_experience = [];

while ($row = $result->fetch_assoc()) {
    if ($user === null) {
        $user = [
            'name' => $row['name'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'ic_number' => $row['ic_number'],
            'profile_pic' => $row['profile_pic'],
            'cv' => $row['cv']
        ];
    }
    
    // Collect education info
    if (!empty($row['education_level'])) {
        $education[] = [
            'education_level' => $row['education_level'],
            'institution' => $row['institution'],
            'field_of_study' => $row['field_of_study'],
            'graduation_year' => $row['graduation_year']
        ];
    }
    
    // Collect achievements
    if (!empty($row['achievement_title'])) {
        $achievements[] = [
            'title' => $row['achievement_title'],
            'description' => $row['achievement_description'],
            'year' => $row['achievement_year']
        ];
    }
    
    // Collect work experience
    if (!empty($row['company'])) {
        $work_experience[] = [
            'company' => $row['company'],
            'position' => $row['position'],
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date'],
            'description' => $row['work_description']
        ];
    }
}

$stmt->close();
$conn->close();

// Set profile picture or placeholder
$profile_pic = !empty($user['profile_pic']) ? "uploads/profile/" . $user['profile_pic'] : "/Website/media/placeholder.png";
$cv = !empty($user['cv']) ? "uploads/cv/" . $user['cv'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page - Politeknik Brunei</title>
    <link rel="stylesheet" href="/Website/assets/css/profile.css">
  
</head>
<body>
    <script src="/Website/assets/js/profile.js"></script>
    

    <header>
        <div class="header-content">
            <div class="logo-container">
                <img src="/Website/assets/images/pblogo.png" alt="Politeknik Logo" class="top-left-image">
            </div>
            <h1>Politeknik Brunei - Update Profile</h1>
            <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" class="profile-pic">
        </div>
    </header>

    <div class="container">
        <form id="profileForm" action="update_profile.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
            
            <!-- Tab navigation -->
            <div class="tabs">
                <button type="button" class="tab-button active" data-tab="personal">Personal Details</button>
                <button type="button" class="tab-button" data-tab="education">Education Background</button>
                <button type="button" class="tab-button" data-tab="achievements">Achievements</button>
                <button type="button" class="tab-button" data-tab="work">Work Experience</button>
                <button type="button" class="tab-button" data-tab="cv">Auto-Generated CV</button>
            </div>
            
            <!-- Personal Details Tab -->
            <div id="personal" class="tab-content active">
                <h2>Personal Details</h2>
                
                <div class="input-field">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>

                <div class="input-field">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="input-field">
                    <label for="phone">Phone:</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                </div>

                <div class="input-field">
                    <label for="ic_number">IC Number:</label>
                    <input type="text" id="ic_number" name="ic_number" value="<?php echo htmlspecialchars($user['ic_number']); ?>" required>
                </div>

                <div class="input-field">
                    <label for="profile_pic">Upload New Profile Picture:</label>
                    <input type="file" name="profile_pic" id="profile_pic">
                </div>
            </div>
            
           <!-- Education Background Tab -->
<div id="education" class="tab-content">
    <h2>Education Background</h2>
    <div id="education-container">
        <?php if (!empty($education)): ?>
            <?php foreach ($education as $index => $edu): ?>
                <div class="form-section education-entry">
                    <div class="input-field">
                        <label for="education_level_<?php echo $index; ?>">Education Level:</label>
                        <select name="education[<?php echo $index; ?>][education_level]" id="education_level_<?php echo $index; ?>" required>
                            <option value="">Select Level</option>
                            <option value="Secondary" <?php echo ($edu['education_level'] == 'Secondary') ? 'selected' : ''; ?>>Secondary</option>
                            <option value="Diploma" <?php echo ($edu['education_level'] == 'Diploma') ? 'selected' : ''; ?>>Diploma</option>
                            <option value="Bachelor" <?php echo ($edu['education_level'] == 'Bachelor') ? 'selected' : ''; ?>>Bachelor's Degree</option>
                            <option value="Master" <?php echo ($edu['education_level'] == 'Master') ? 'selected' : ''; ?>>Master's Degree</option>
                            <option value="PhD" <?php echo ($edu['education_level'] == 'PhD') ? 'selected' : ''; ?>>PhD</option>
                        </select>
                    </div>
                    <div class="input-field">
                        <label for="institution_<?php echo $index; ?>">Institution:</label>
                        <input type="text" id="institution_<?php echo $index; ?>" name="education[<?php echo $index; ?>][institution]" value="<?php echo htmlspecialchars($edu['institution']); ?>" required>
                    </div>
                    <div class="input-field">
                        <label for="field_of_study_<?php echo $index; ?>">Field of Study:</label>
                        <input type="text" id="field_of_study_<?php echo $index; ?>" name="education[<?php echo $index; ?>][field_of_study]" value="<?php echo htmlspecialchars($edu['field_of_study']); ?>" required>
                    </div>
                    <div class="input-field">
                        <label for="graduation_year_<?php echo $index; ?>">Graduation Year:</label>
                        <input type="number" id="graduation_year_<?php echo $index; ?>" name="education[<?php echo $index; ?>][graduation_year]" min="1950" max="2030" value="<?php echo htmlspecialchars($edu['graduation_year']); ?>" required>
                    </div>
                    <div class="input-field">
                        <label for="education_cert_<?php echo $index; ?>">Upload Certificate:</label>
                        <input type="file" id="education_cert_<?php echo $index; ?>" name="education[<?php echo $index; ?>][certificate]" accept=".pdf,.jpg,.jpeg,.png">
                        <?php if (!empty($edu['certificate'])): ?>
                            <p class="file-info">Current certificate: <?php echo htmlspecialchars($edu['certificate']); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if ($index > 0): ?>
                        <button type="button" class="remove-btn remove-education">Remove</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="form-section education-entry">
                <div class="input-field">
                    <label for="education_level_0">Education Level:</label>
                    <select name="education[0][education_level]" id="education_level_0" required>
                        <option value="">Select Level</option>
                        <option value="Secondary">Secondary</option>
                        <option value="Diploma">Diploma</option>
                        <option value="Bachelor">Bachelor's Degree</option>
                        <option value="Master">Master's Degree</option>
                        <option value="PhD">PhD</option>
                    </select>
                </div>
                <div class="input-field">
                    <label for="institution_0">Institution:</label>
                    <input type="text" id="institution_0" name="education[0][institution]" required>
                </div>
                <div class="input-field">
                    <label for="field_of_study_0">Field of Study:</label>
                    <input type="text" id="field_of_study_0" name="education[0][field_of_study]" required>
                </div>
                <div class="input-field">
                    <label for="graduation_year_0">Graduation Year:</label>
                    <input type="number" id="graduation_year_0" name="education[0][graduation_year]" min="1950" max="2030" required>
                </div>
                <div class="input-field">
                    <label for="education_cert_0">Upload Certificate:</label>
                    <input type="file" id="education_cert_0" name="education[0][certificate]" accept=".pdf,.jpg,.jpeg,.png">
                </div>
            </div>
        <?php endif; ?>
    </div>
    <button type="button" class="add-btn" id="add-education">Add Another Education</button>
</div>

            
       <!-- Achievements Tab -->
<div id="achievements" class="tab-content">
    <h2>Achievements</h2>
    <div id="achievements-container">
        <?php if (!empty($achievements)): ?>
            <?php foreach ($achievements as $index => $ach): ?>
                <div class="form-section achievement-entry">
                    <div class="input-field">
                        <label for="achievement_title_<?php echo $index; ?>">Title:</label>
                        <input type="text" id="achievement_title_<?php echo $index; ?>" name="achievements[<?php echo $index; ?>][title]" value="<?php echo htmlspecialchars($ach['title']); ?>" required>
                    </div>
                    <div class="input-field">
                        <label for="achievement_description_<?php echo $index; ?>">Description:</label>
                        <textarea id="achievement_description_<?php echo $index; ?>" name="achievements[<?php echo $index; ?>][description]" rows="3" required><?php echo htmlspecialchars($ach['description']); ?></textarea>
                    </div>
                    <div class="input-field">
                        <label for="achievement_year_<?php echo $index; ?>">Year:</label>
                        <input type="number" id="achievement_year_<?php echo $index; ?>" name="achievements[<?php echo $index; ?>][year]" min="1950" max="2030" value="<?php echo htmlspecialchars($ach['year']); ?>" required>
                    </div>
                    <div class="input-field">
                        <label for="achievement_cert_<?php echo $index; ?>">Upload Certificate/Evidence:</label>
                        <input type="file" id="achievement_cert_<?php echo $index; ?>" name="achievements[<?php echo $index; ?>][certificate]" accept=".pdf,.jpg,.jpeg,.png">
                        <?php if (!empty($ach['certificate'])): ?>
                            <p class="file-info">Current certificate: <?php echo htmlspecialchars($ach['certificate']); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if ($index > 0): ?>
                        <button type="button" class="remove-btn remove-achievement">Remove</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="form-section achievement-entry">
                <div class="input-field">
                    <label for="achievement_title_0">Title:</label>
                    <input type="text" id="achievement_title_0" name="achievements[0][title]" required>
                </div>
                <div class="input-field">
                    <label for="achievement_description_0">Description:</label>
                    <textarea id="achievement_description_0" name="achievements[0][description]" rows="3" required></textarea>
                </div>
                <div class="input-field">
                    <label for="achievement_year_0">Year:</label>
                    <input type="number" id="achievement_year_0" name="achievements[0][year]" min="1950" max="2030" required>
                </div>
                <div class="input-field">
                    <label for="achievement_cert_0">Upload Certificate/Evidence:</label>
                    <input type="file" id="achievement_cert_0" name="achievements[0][certificate]" accept=".pdf,.jpg,.jpeg,.png">
                </div>
            </div>
        <?php endif; 
        ?>
    </div>
    <button type="button" class="add-btn" id="add-achievement">Add Another Achievement</button>
</div>

            <!-- Work Experience Tab -->
            <div id="work" class="tab-content">
                <h2>Work Experience</h2>
                <div id="work-container">
                    <?php if (!empty($work_experience)): ?>
                        <?php foreach ($work_experience as $index => $work): ?>
                            <div class="form-section work-entry">
                                <div class="input-field">
                                    <label for="company_<?php echo $index; ?>">Company:</label>
                                    <input type="text" id="company_<?php echo $index; ?>" name="work[<?php echo $index; ?>][company]" value="<?php echo htmlspecialchars($work['company']); ?>" required>
                                </div>
                                <div class="input-field">
                                    <label for="position_<?php echo $index; ?>">Position:</label>
                                    <input type="text" id="position_<?php echo $index; ?>" name="work[<?php echo $index; ?>][position]" value="<?php echo htmlspecialchars($work['position']); ?>" required>
                                </div>
                                <div class="input-field">
                                    <label for="start_date_<?php echo $index; ?>">Start Date:</label>
                                    <input type="date" id="start_date_<?php echo $index; ?>" name="work[<?php echo $index; ?>][start_date]" value="<?php echo htmlspecialchars($work['start_date']); ?>" required>
                                </div>
                                <div class="input-field">
                                    <label for="end_date_<?php echo $index; ?>">End Date:</label>
                                    <input type="date" id="end_date_<?php echo $index; ?>" name="work[<?php echo $index; ?>][end_date]" value="<?php echo htmlspecialchars($work['end_date']); ?>">
                                    <small>(Leave blank if current position)</small>
                                </div>
                                <div class="input-field">
                                    <label for="work_description_<?php echo $index; ?>">Description:</label>
                                    <textarea id="work_description_<?php echo $index; ?>" name="work[<?php echo $index; ?>][description]" rows="3" required><?php echo htmlspecialchars($work['description']); ?></textarea>
                                </div>
                                <?php if ($index > 0): ?>
                                    <button type="button" class="remove-btn remove-work">Remove</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="form-section work-entry">
                            <div class="input-field">
                                <label for="company_0">Company:</label>
                                <input type="text" id="company_0" name="work[0][company]" required>
                            </div>
                            <div class="input-field">
                                <label for="position_0">Position:</label>
                                <input type="text" id="position_0" name="work[0][position]" required>
                            </div>
                            <div class="input-field">
                                <label for="start_date_0">Start Date:</label>
                                <input type="date" id="start_date_0" name="work[0][start_date]" required>
                            </div>
                            <div class="input-field">
                                <label for="end_date_0">End Date:</label>
                                <input type="date" id="end_date_0" name="work[0][end_date]">
                                <small>(Leave blank if current position)</small>
                            </div>
                            <div class="input-field">
                                <label for="work_description_0">Description:</label>
                                <textarea id="work_description_0" name="work[0][description]" rows="3" required></textarea>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <button type="button" class="add-btn" id="add-work">Add Another Work Experience</button>
            </div>
            
            <!-- Auto-Generated CV Tab -->
            <div id="cv" class="tab-content">
                <h2>Auto-Generated CV</h2>
                <p>This tab shows your auto-generated CV based on the information provided in other tabs.</p>
                
                <?php if (!empty($user['cv'])): ?>
                <div class="current-cv-container">
                    <h3>Current CV</h3>
                    <p>You already have a CV saved to your profile.</p>
                    <div class="input-field">
                        <a href="/Website/user_profile/uploads/cv/<?php echo htmlspecialchars($user['cv']); ?>" target="_blank" class="view-cv-btn">View Current CV</a>
                    </div>
                </div>
                <?php endif; ?>
                
                <p>Click the "Generate CV" button to preview your CV, then click "Save CV" to save it to your profile.</p>
                
                <button type="button" id="generate-cv-btn" class="add-btn">Generate CV Preview</button>
                
                <div id="cv-preview" class="cv-preview" style="display: none;">
                    <div class="cv-section cv-header">
                        <div class="cv-header-content">
                            <div class="cv-header-text">
                                <h2 id="cv-name"></h2>
                                <p id="cv-contact"></p>
                            </div>
                            <div class="cv-profile-pic">
                                <img id="cv-profile-image" src="" alt="Profile Picture">
                            </div>
                        </div>
                    </div>
                    
                    <div class="cv-section">
                        <h3>Education</h3>
                        <div id="cv-education"></div>
                    </div>
                    
                    <div class="cv-section">
                        <h3>Work Experience</h3>
                        <div id="cv-work"></div>
                    </div>
                    
                    <div class="cv-section">
                        <h3>Achievements</h3>
                        <div id="cv-achievements"></div>
                    </div>
                </div>
                
                <div class="input-field" style="margin-top: 20px; display: none;" id="save-cv-container">
                    <input type="hidden" name="auto_generate_cv" id="auto_generate_cv" value="0">
                    <button type="button" id="save-cv-btn" class="add-btn">Save Generated CV</button>
                </div>
                
                <!-- New button to open CV in new tab -->
                <div class="input-field" style="margin-top: 20px; display: none;" id="open-cv-container">
                    <button type="button" id="open-cv-tab-btn" class="add-btn">Open CV in New Tab</button>
                </div>
            </div>
        </form>
        
        <button class="back-button" onclick="window.location.href='/Website/main/graduate_dashboard.php'">Back to Dashboard</button>
        
        <!-- Floating Save Button -->
        <button class="floating-save-button" id="floating-save-btn" type="button">Save All Changes</button>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Politeknik Brunei.</p>
    </footer>

</body>
</html>
