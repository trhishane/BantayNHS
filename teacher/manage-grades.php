<?php
session_start(); 
include '../includes/dbconn.php'; 

if (!isset($_SESSION['userId'])) {
    header('Location: ../index.php'); 
    exit();
}

$querySchoolYear = "SELECT syId, school_year FROM tblschoolyear WHERE status = 'Yes' LIMIT 1";
$schoolYearResult = $conn->query($querySchoolYear);

if ($schoolYearResult && $schoolYearResult->num_rows > 0) {
    $schoolYearRow = $schoolYearResult->fetch_assoc();
    $currentSchoolYear = $schoolYearRow['school_year'];
    $syId = $schoolYearRow['syId']; 
} else {
    $currentSchoolYear = "Not Available"; 
    $syId = null; 
}

// Fetch the grading status from tblgrading
$query = "SELECT quarter1, quarter2 FROM tblgradingstatus LIMIT 1";  
$result = mysqli_query($conn, $query);  
$row = mysqli_fetch_assoc($result);  

// Enable/disable quarters based on tblgrading status
$enableQuarter1 = ($row['quarter1'] == 1);  
$enableQuarter2 = ($row['quarter2'] == 1);   

if (isset($_POST['subject'])) {
    list($subjectName, $sectionName) = explode('_', $_POST['subject']);

    $querySubjectId = "SELECT subjectId FROM tblsubject WHERE subjectName = ? LIMIT 1";
    $stmtSubjectId = $conn->prepare($querySubjectId);
    $stmtSubjectId->bind_param("s", $subjectName);
    $stmtSubjectId->execute();
    $stmtSubjectId->bind_result($subjectId);
    $stmtSubjectId->fetch();
    $stmtSubjectId->close();

    $queryStudents = "
        SELECT 
            u.userId,
            u.firstName, 
            u.middleName, 
            u.lastName, 
            g.quarter1_grade,
            g.quarter2_grade
        FROM tblstudentinfo s
        INNER JOIN tblusersaccount u ON s.userId = u.userId
        INNER JOIN tblsection sec ON s.sectionId = sec.sectionId
        INNER JOIN tblsubject subj ON subj.sectionId = sec.sectionId
        LEFT JOIN tblgrades g ON s.userId = g.userId AND g.subjectId = ? AND g.syId = ?
        WHERE subj.subjectName = ? AND sec.sectionName = ?
ORDER BY u.lastName ASC, u.firstName ASC, u.middleName ASC;
    ";
    $stmtStudents = $conn->prepare($queryStudents);
    $stmtStudents->bind_param("iiss", $subjectId, $syId, $subjectName, $sectionName);
    $stmtStudents->execute();
    $studentsResult = $stmtStudents->get_result();

    $querySemester = "SELECT semester FROM tblsubject WHERE subjectName = ? LIMIT 1";
    $stmtSemester = $conn->prepare($querySemester);
    $stmtSemester->bind_param("s", $subjectName);
    $stmtSemester->execute();
    $stmtSemester->bind_result($semester);
    $stmtSemester->fetch();
    $stmtSemester->close();

    $query = "SELECT gradeLevel, strand FROM tblsection WHERE sectionName = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $sectionName);
    $stmt->execute();
    $stmt->bind_result($gradeLevel, $strand);
    $stmt->fetch();
    $stmt->close();
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
            logAction($name, $role, "Input grades", $conn);
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

<?php include('includes/sidebar.php'); include('includes/links.php'); ?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Manage Grades</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item"><a href="input-grades.php">Grades</a></li>
                <li class="breadcrumb-item active">Manage Grades</li>
            </ol>
        </nav>
    </div>

    <div class="card mb-5">
        <div class="card-body">
            <div class="text-center mb-4">
                <h4 class="mt-2"><?php echo htmlspecialchars($subjectName); ?></h4>
                <p>Grade: <strong><?php echo htmlspecialchars($gradeLevel); ?></strong> | Strand: <strong><?php echo htmlspecialchars($strand); ?></strong> | Section: <strong><?php echo htmlspecialchars($sectionName); ?></strong></p>
                <p>School Year: <strong><?php echo htmlspecialchars($currentSchoolYear); ?></strong></p>
            </div>
            <form method="POST" action="save-grades.php" id="gradesForm">
                <input type="hidden" name="subjectId" value="<?php echo htmlspecialchars($subjectId); ?>">
                <input type="hidden" name="syId" value="<?php echo htmlspecialchars($syId); ?>">
                <input type="hidden" name="semester" value="<?php echo htmlspecialchars($semester); ?>">

                <table id="grades-table" class="table table-striped">
                    <thead style="background-color: transparent;">
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Quarter 1 Grade</th>
                            <th>Quarter 2 Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $studentCount = 0; ?>
                        <?php while ($row = $studentsResult->fetch_assoc()): ?>
                            <?php $studentCount++; ?>
                            <tr>
                                <td><?php echo $studentCount; ?></td>
                                <td><?php echo htmlspecialchars($row['lastName'] . ',  ' . $row['firstName']); ?></td>
                                <!-- Quarter 1 Grade -->
                               
                                <td>
                                    <input type="number" class="form-control" 
                                        name="quarter1_grade[<?php echo htmlspecialchars($row['userId']); ?>]" 
                                        value="<?php echo htmlspecialchars($row['quarter1_grade']); ?>" 
                                        <?php echo $enableQuarter1 ? '' : 'readonly'; ?>>
                                </td>
                                <!-- Quarter 2 Grade -->
                                <td>
                                    <input type="number" class="form-control" 
                                        name="quarter2_grade[<?php echo htmlspecialchars($row['userId']); ?>]" 
                                        value="<?php echo ($row['quarter2_grade'] == 0) ? '' : htmlspecialchars($row['quarter2_grade']); ?>" 
                                        <?php echo $enableQuarter2 ? '' : 'readonly'; ?>>
                                </td>


                            </tr>
                        <?php endwhile; ?>
                    </tbody>

                </table>

                <div class="d-flex justify-content-end mt-3">
                    <button type="reset" class="btn btn-secondary me-1">Reset</button>
                    <button type="button" class="btn btn-primary" id="reviewButton" disabled>Review Grades</button>
                </div>
            </form>
        </div>
    </div>
</main>

<!-- Review Grades Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reviewModalLabel">Review Grades</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5>Grading Date: <span id="modalClassDate"></span></h5>
                <h5>School Year: <?php echo htmlspecialchars($currentSchoolYear); ?></h5>
                <h5>Semester: <span id="modalSemester"><?php echo htmlspecialchars($semester); ?></span></h5> <!-- Display Semester in Modal -->
                <h5>Grades Summary:</h5>
                <table class="table table-striped">
                <thead style="background-color: transparent;">
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Quarter 1 Grade</th>
                            <th>Quarter 2 Grade</th>
                        </tr>
                    </thead>
                    <tbody id="gradesSummaryBody">
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="gradesForm">Submit Grades</button>
            </div>
        </div>
    </div>
</div>

<script>
    const reviewButton = document.getElementById('reviewButton');
    const modalClassDate = document.getElementById('modalClassDate');
    const modalSemester = document.getElementById('modalSemester');
    const gradesSummaryTableBody = document.getElementById('gradesSummaryBody');

    function checkGradeInputs() {
        const gradeInputs = document.querySelectorAll('input[type="number"]');
        let hasValidValue = false;

        gradeInputs.forEach(input => {
            if (input.value.trim() !== '' && !isNaN(input.value)) {
                hasValidValue = true; 
            }
        });

        reviewButton.disabled = !hasValidValue; 
    }

    document.addEventListener('input', (event) => {
        if (event.target.matches('input[type="number"]')) {
            checkGradeInputs(); 
        }
    });

    reviewButton.addEventListener('click', function () {
      
        modalClassDate.textContent = new Date().toLocaleDateString(); // Use the current date
        modalSemester.textContent = '<?php echo htmlspecialchars($semester); ?>';

        gradesSummaryTableBody.innerHTML = '';

        const rows = document.querySelectorAll('#grades-table tbody tr');
        rows.forEach((row, index) => {
            const studentName = row.cells[1].textContent.trim();
            const quarter1Grade = row.querySelector('input[name^="quarter1_grade"]').value.trim();
            const quarter2Grade = row.querySelector('input[name^="quarter2_grade"]').value.trim();

            const summaryRow = document.createElement('tr');
            summaryRow.innerHTML = `
                <td>${index + 1}</td>
                <td>${studentName}</td>
                <td>${quarter1Grade || 'N/A'}</td>
                <td>${quarter2Grade || 'N/A'}</td>
            `;

            gradesSummaryTableBody.appendChild(summaryRow);
        });

        const reviewModal = new bootstrap.Modal(document.getElementById('reviewModal'));
        reviewModal.show();
    });

    document.addEventListener('DOMContentLoaded', checkGradeInputs);
</script>