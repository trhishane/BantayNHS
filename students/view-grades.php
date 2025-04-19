<?php
session_start();
ob_start(); 

include '../includes/dbconn.php';
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');  

if (!isset($_SESSION['userId'])) {
    header("Location: index.php"); 
    exit();
}

$userId = $_SESSION['userId'];
$selectedOption = '';
$gradesResult = null;
$gradesArray = []; 
$studentName = ''; 
$schoolYear = ''; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['semester_year'])) {
        $selectedOption = $_POST['semester_year'];
        list($selectedSemester, $selectedSchoolYear) = explode('|', $selectedOption);

        $query = "SELECT g.gradeId, g.semester, g.quarter1_grade, g.quarter2_grade, s.subjectName, sy.school_year 
                  FROM tblgrades g 
                  JOIN tblsubject s ON g.subjectId = s.subjectId 
                  JOIN tblschoolyear sy ON g.syId = sy.syId 
                  WHERE g.userId = ? AND g.semester = ? AND sy.school_year = ? AND g.status = 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $userId, $selectedSemester, $selectedSchoolYear);
        $stmt->execute();
        $gradesResult = $stmt->get_result();

        // Check if user is logged in
if (isset($_SESSION['auth_user'])) {
    $username = $_SESSION['auth_user']['username'];

    // Fetch user details from tblusersaccount
    $sql = "SELECT firstName, middleName, lastName, role FROM tblusersaccount WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        $_SESSION['firstname'] = $user['firstName'];
        $_SESSION['middlename'] = isset($user['middleName']) ? $user['middleName'] : ''; 
        $_SESSION['lastname'] = $user['lastName'];
        $_SESSION['role'] = $user['role'];

        $name = trim($user['firstName'] . " " . $_SESSION['middlename'] . " " . $user['lastName']);
        $role = $user['role'];
        $action = "Viewed grades";
        $timestamp = date('Y-m-d H:i:s');

        $checkQuery = "SELECT auditId FROM tblaudit_trail WHERE name = ? AND role = ? AND action = ? AND timestamp >= NOW() - INTERVAL 1 MINUTE";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("sss", $name, $role, $action);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows == 0) {
            $query = "INSERT INTO tblaudit_trail (name, role, action, timestamp) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssss", $name, $role, $action, $timestamp);
            $stmt->execute();
            $stmt->close();
        }

        $checkStmt->close();
    } else {
        echo "User not found in the database.";
        exit();
    }
} else {
    echo "You are not logged in.";
    exit();
}

        while ($row = $gradesResult->fetch_assoc()) {
            $gradesArray[] = $row;
            
        }

        $studentQuery = "SELECT CONCAT(firstname, ' ', lastname) AS fullname FROM tblusersaccount WHERE userId = ?";
        $studentStmt = $conn->prepare($studentQuery);
        $studentStmt->bind_param("s", $userId);
        $studentStmt->execute();
        $studentResult = $studentStmt->get_result();
        if ($studentResult->num_rows > 0) {
            $studentRow = $studentResult->fetch_assoc();
            $studentName = $studentRow['fullname'];
        }
    }
}

// Fetch available semesters and school years
$semestersQuery = "SELECT DISTINCT semester FROM tblgrades";
$semestersResult = $conn->query($semestersQuery);

$schoolYearsQuery = "SELECT school_year FROM tblschoolyear";
$schoolYearsResult = $conn->query($schoolYearsQuery);



include('includes/sidebar.php');
include('includes/links.php');
?>



<main id="main" class="main">
    <div class="pagetitle">
        <h1>View Grades</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item active">View Grades</li>
            </ol>
        </nav>
    </div>

    <div class="container-fluid">
        <div class="card mb-5">
            <div class="card-body">
                <h4 class="mt-2 mb-2 text-center">Grades</h4>
                <form method="POST" class="mb-4">
                    <div class="container">
                        <div class="row justify-content-center"> 
                            <div class="form-group col-md-4 text-center"> 
                                <label for="semester_year">Select Semester and School Year</label>
                                <select name="semester_year" id="semester_year" class="form-control" required>
                                    <?php if ($semestersResult->num_rows > 0 && $schoolYearsResult->num_rows > 0): ?>
                                        <?php while ($semesterRow = $semestersResult->fetch_assoc()): ?>
                                            <?php while ($schoolYearRow = $schoolYearsResult->fetch_assoc()): ?>
                                                <option value="<?php echo htmlspecialchars($semesterRow['semester'] . '|' . $schoolYearRow['school_year']); ?>" 
                                                    <?php echo ($selectedOption == $semesterRow['semester'] . '|' . $schoolYearRow['school_year']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($semesterRow['semester'] . ' | ' . $schoolYearRow['school_year']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                            <?php $schoolYearsResult->data_seek(0);  ?>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>

                <hr>

                <?php if (!empty($gradesArray)): ?>
                    <table  id="grades-table" class="table table-striped">
                        <thead style="background-color: transparent;">
                            <tr>
                                <th>Subjects</th>
                                <th>Quarter 1 Grade</th>
                                <th>Quarter 2 Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($gradesArray as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['subjectName']); ?></td>
                                    <td><?php echo ($row['quarter1_grade'] == 0) ? 'Not Available' : htmlspecialchars($row['quarter1_grade']); ?></td>
                                    <td><?php echo ($row['quarter2_grade'] == 0) ? 'Not Available' : htmlspecialchars($row['quarter2_grade']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    
                <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
                    <div class="alert alert-warning" role="alert">
                        No grades found for the selected semester and school year.
                    </div>
                <?php endif; ?>
                
                <?php
if (isset($_POST['download_pdf']) && !empty($gradesArray)) {
    ob_end_clean();

    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetMargins(10, 10, 10);
    $pdf->AddPage();

    // Page width and height
    $width = 297;  // A4 width in mm
    $height = 210; // A4 height in mm

    // Define two columns width (left and right) and the gap between them
    $leftColumnWidth = 140;
    $rightColumnWidth = 140;
    $gap = 10; // Gap between columns

    // Left column (First Column)
    $pdf->SetXY(10, 10);  // Position the content at the top of the page in the left column

    // Report Header
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell($leftColumnWidth, 10, 'Department of Education', 0, 1, 'C');
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell($leftColumnWidth, 10, 'Form 138 - REPORT ON LEARNING PROGRESS AND ACHIEVEMENT', 0, 1, 'C');
    $pdf->Ln(15); // Add more space after the title header

    

    // Right column (Second Column)
    $pdf->SetXY(10 + $leftColumnWidth + $gap, 10);  // Position the content at the top of the page in the right column

    // Report Header for the second column
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell($rightColumnWidth, 10, 'Performance Summary', 0, 1, 'C');
    $pdf->Ln(10); // Add space before the performance summary title

    // Ensure there’s enough space before the table starts
    $pdf->Ln(10);  // Adds extra space before the table

    // Generate Table for Grades (right column)
    function generateTable($pdf, $grades) {
        if (empty($grades)) return;

        // Table header
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(60, 8, 'Subject', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Quarter 1', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Quarter 2', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Final Grade', 1, 1, 'C');

        $totalGrades = 0;
        $subjectsCount = 0;

        foreach ($grades as $row) {
            $q1 = isset($row['quarter1_grade']) ? $row['quarter1_grade'] : 'N/A';
            $q2 = isset($row['quarter2_grade']) ? $row['quarter2_grade'] : 'N/A';
            $finalGrade = (is_numeric($q1) && is_numeric($q2)) ? round(($q1 + $q2) / 2, 2) : 'N/A';

            // Table body
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(60, 8, $row['subjectName'], 1, 0, 'L');
            $pdf->Cell(30, 8, $q1, 1, 0, 'C');
            $pdf->Cell(30, 8, $q2, 1, 0, 'C');
            $pdf->Cell(30, 8, $finalGrade, 1, 1, 'C');

            if ($finalGrade !== 'N/A') {
                $totalGrades += $finalGrade;
                $subjectsCount++;
            }
        }

        // Compute General Average
        $average = ($subjectsCount > 0) ? round($totalGrades / $subjectsCount, 2) : 0;
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(60, 8, 'General Average', 1, 0, 'C');
        $pdf->Cell(30, 8, $average, 1, 1, 'C');
    }

    generateTable($pdf, $gradesArray);

    // Footer with teacher's name or other notes
    $pdf->SetXY(10, $height - 30);
    $pdf->Cell($leftColumnWidth, 8, 'Date Printed: ' . date('F d, Y'), 0, 1, 'L'); // Add teacher's name or other footer notes

    // Output the PDF
    $pdf->Output('grades_report.pdf', 'I');
    exit();
}
?>



            </div>
        </div>
    </div>
</main>

<script>
    document.getElementById('semester_year').addEventListener('change', function() {
        this.form.submit();
    });
</script> 
