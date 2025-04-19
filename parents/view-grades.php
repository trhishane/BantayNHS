<?php 
include('includes/sidebar.php'); 
include('includes/links.php');
include('includes/dbconn.php');

if (isset($_SESSION['auth_user'])) {
    $username = $_SESSION['auth_user']['username'];
    $sql = "SELECT * FROM tblusersaccount WHERE username = '$username'";
    $sql_run = mysqli_query($conn, $sql);

    if (mysqli_num_rows($sql_run)) {
        $row = mysqli_fetch_assoc($sql_run);
        $userId = $row['userId'];

        $sql = "
        SELECT s.studentId, u.firstName, u.middleName, u.lastName
        FROM tblparent_student ps
        JOIN tblstudentinfo s ON ps.studentId = s.studentId
        JOIN tblusersaccount u ON s.userId = u.userId
        WHERE ps.parentId = (SELECT parentId FROM tblparentinfo WHERE userId = '$userId')
        ";
        $student_sql_run = mysqli_query($conn, $sql);

        $students = [];
        if (mysqli_num_rows($student_sql_run) > 0) {
            while ($student_row = mysqli_fetch_assoc($student_sql_run)) {
                $students[] = [
                    'studentId' => $student_row['studentId'],
                    'firstName' => $student_row['firstName'],
                    'middleName' => isset($student_row['middleName']) ? $student_row['middleName'] : '', // Fix for missing middle name
                    'lastName' => $student_row['lastName'],
                ];
            }
        }
    }
} else {
    echo "You are not logged in.";
}

$combinationsQuery = "
SELECT DISTINCT g.semester, sy.school_year
FROM tblgrades g
JOIN tblschoolyear sy ON g.syId = sy.syId
";
$combinationsResult = mysqli_query($conn, $combinationsQuery);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['studentId'], $_POST['semester'], $_POST['schoolYear'])) {
        $studentId = $_POST['studentId'];
        $semester = $_POST['semester'];
        $schoolYear = $_POST['schoolYear'];

        $query = "
        SELECT g.subjectId, s.subjectName, g.quarter1_grade, g.quarter2_grade, g.semester
        FROM tblgrades g
        JOIN tblsubject s ON g.subjectId = s.subjectId
        JOIN tblschoolyear sy ON g.syId = sy.syId 
        WHERE g.userId = (SELECT userId FROM tblstudentinfo WHERE studentId = '$studentId')
        AND g.semester = '$semester' 
        AND sy.school_year = '$schoolYear'
        ";

        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            echo '<table class="table table-bordered">
                    <thead style="background-color: transparent;">
                        <tr>
                            <th>Subject</th>
                            <th>Semester</th>
                            <th>Quarter 1</th>
                            <th>Quarter 2</th>
                        </tr>
                    </thead>
                    <tbody>';
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<tr>
                        <td>' . htmlspecialchars($row['subjectName']) . '</td>
                        <td>' . htmlspecialchars($row['semester']) . '</td>
                        <td>' . ($row['quarter1_grade'] == 0 ? 'Not Available' : htmlspecialchars($row['quarter1_grade'])) . '</td>
                        <td>' . ($row['quarter2_grade'] == 0 ? 'Not Available' : htmlspecialchars($row['quarter2_grade'])) . '</td>
                      </tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<div class="alert alert-warning" role="alert">
                        No grades found for the selected semester and school year.
                    </div>';
        }
    } else {
        echo '<p class="text-center text-danger">Invalid request.</p>';
    }
    exit; 
}

if (isset($_SESSION['auth_user'])) {
    $username = $_SESSION['auth_user']['username'];

    // Fetch user details from tblusersaccount
    $sql = "SELECT firstName, middleName, lastName, role FROM tblusersaccount WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        $_SESSION['firstname'] = $user['firstName'];
        $_SESSION['middlename'] = isset($user['middleName']) ? $user['middleName'] : ''; // Fix missing middle name
        $_SESSION['lastname'] = $user['lastName'];
        $_SESSION['role'] = $user['role'];

        // Construct the full name properly
        $name = trim($user['firstName'] . " " . $_SESSION['middlename'] . " " . $user['lastName']);
     
        $role = $user['role'];
        
        // Log action
        if (function_exists('logAction')) {
            logAction($name, $role, "Viewed grades", $conn);
        }
    } else {
        echo "User not found in the database.";
        exit();
    }
} else {
    echo "You are not logged in.";
    exit();
}

// Function to log actions (ensure this exists in your includes file)
function logAction($name, $role, $action, $conn) {
    $timestamp = date("Y-m-d H:i:s");
    $query = "INSERT INTO tblaudit_trail ( name, role, action, timestamp) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssss", $name, $role, $action, $timestamp);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
?>


<main id="main" class="main">
    <div class="pagetitle">
        <h1>View Grades</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="admin_dashboard.php">Home</a></li>
                <li class="breadcrumb-item active">View Grades</li>
            </ol>
        </nav>
    </div>

    <div class="container-fluid">
        <div class="card mb-5">
            <div class="card-body">
                <h5 class="mt-4 mb-1 text-center">Select a Student, Semester, and School Year</h5>
                <div class="row mb-3 align-items-end"> 
                    <div class="col-md-6">
                        <label class="form-label">Student</label>
                        <select class="form-select" name="studentId" id="studentId" required>
                        <option value="" disabled <?= !isset($_POST['studentId']) ? 'selected' : ''; ?>>Select Student:</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?= htmlspecialchars($student['studentId']); ?>">
                                    <?= htmlspecialchars($student['firstName'] . ' ' . $student['middleName'] . ' ' . $student['lastName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Semester & School Year</label>
                        <select class="form-select" name="semester_school_year" id="semester_school_year" required>
                        <option value="" disabled <?= !isset($_POST['studentId']) ? 'selected' : ''; ?>>Select Semester and School Year:</option>    
                            <?php if ($combinationsResult): ?>
                                <?php while ($combinationRow = mysqli_fetch_assoc($combinationsResult)): ?>
                                    <option value="<?= htmlspecialchars($combinationRow['semester'] . '|' . $combinationRow['school_year']); ?>">
                                        <?= htmlspecialchars($combinationRow['semester'] . ' | ' . $combinationRow['school_year']); ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <hr>
                <div id="grades-container" class="mt-4">
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const studentSelect = document.getElementById('studentId');
        const semesterSelect = document.getElementById('semester_school_year');
        const gradesContainer = document.getElementById('grades-container');

        function fetchGrades() {
            const studentId = studentSelect.value;
            const semesterSchoolYear = semesterSelect.value;

            if (studentId && semesterSchoolYear) {
                const [semester, schoolYear] = semesterSchoolYear.split('|');
                fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `studentId=${studentId}&semester=${semester}&schoolYear=${schoolYear}`
                })
                .then(response => response.text())
                .then(data => {
                    gradesContainer.innerHTML = data;
                })
                .catch(error => {
                    gradesContainer.innerHTML = `<p class="text-center text-danger">Error fetching grades.</p>`;
                });
            }
        }

        studentSelect.addEventListener('change', fetchGrades);
        semesterSelect.addEventListener('change', fetchGrades);
    });
</script>
