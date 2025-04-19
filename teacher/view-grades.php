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
    s.studentId,
    sec.gradeLevel, 
    sec.strand, 
    sec.sectionName,
    g.quarter1_grade,
    g.quarter2_grade
FROM tblstudentinfo s
INNER JOIN tblusersaccount u ON s.userId = u.userId
INNER JOIN tblsection sec ON s.sectionId = sec.sectionId
INNER JOIN tblsubject subj ON subj.sectionId = sec.sectionId
LEFT JOIN tblgrades g ON s.userId = g.userId AND g.subjectId = ? AND g.syId = ?
WHERE subj.subjectName = ? 
AND sec.sectionName = ? 
ORDER BY u.lastName ASC, u.firstName ASC, u.middleName ASC;

    ";
    $stmtStudents = $conn->prepare($queryStudents);
    $stmtStudents->bind_param("ssss", $subjectId, $syId, $subjectName, $sectionName);
    $stmtStudents->execute();
    $studentsResult = $stmtStudents->get_result();

    $querySection = "SELECT gradeLevel, strand FROM tblsection WHERE sectionName = ?";
    $stmtSection = $conn->prepare($querySection);
    $stmtSection->bind_param("s", $sectionName);
    $stmtSection->execute();
    $stmtSection->bind_result($gradeLevel, $strand);
    $stmtSection->fetch();
    $stmtSection->close();
} else {
    header('Location: input-grades.php');
    exit();
}
?>

<?php include('includes/sidebar.php'); include('includes/links.php'); ?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>View Grades</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item"><a href="input-grades.php">Grades</a></li>
                <li class="breadcrumb-item active">View Grades</li>
            </ol>
        </nav>
    </div>

    <div class="card mb-5">
        <div class="card-body">
            <div class="text-center mb-4">
                <h4 class="mt-2"><?php echo htmlspecialchars($subjectName); ?></h4>
                <p class="text-center mb-1">
                    Grade: <strong><?php echo htmlspecialchars($gradeLevel); ?></strong> | Strand: <strong><?php echo htmlspecialchars($strand); ?></strong> | Section: <strong><?php echo htmlspecialchars($sectionName); ?></strong>
                </p>
                <p class="mb-1">School Year: <strong><?php echo htmlspecialchars($currentSchoolYear); ?></strong></p>
                <p>Total Students: <strong><?php echo $studentsResult->num_rows; ?></strong></p> <!-- Display total student count -->
            </div>

            <table id="grades-table" class="table table-striped">
                <thead style="background-color: transparent;">
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 40%;">Student Name</th>
                        <th style="width: 20%;">Quarter 1 Grade</th>
                        <th style="width: 20%;">Quarter 2 Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $studentCount = 0; ?>
                    <?php while ($row = $studentsResult->fetch_assoc()): ?>
                        <?php $studentCount++; ?>
                        <tr>
                            <td><?php echo $studentCount; ?></td>
                            <td><?php echo htmlspecialchars($row['firstName'] . ' ' . $row['middleName'] . ' ' . $row['lastName']); ?></td>
                            <td><?php echo $row['quarter1_grade'] !== 0 ? htmlspecialchars($row['quarter1_grade']) : 'N/A'; ?></td>
                            <td><?php echo $row['quarter2_grade'] !== 0 ? htmlspecialchars($row['quarter2_grade']) : 'N/A'; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

        </div>
    </div>
</main>

</body>
</html>
