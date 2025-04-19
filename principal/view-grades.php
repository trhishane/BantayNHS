<?php
session_start();
include '../includes/dbconn.php';

if (!isset($_POST['section']) || empty($_POST['section'])) {
    $_SESSION['modalMessage'] = "Please select a section.";
    $_SESSION['modalType'] = "danger";
    $_SESSION['redirectUrl'] = "grades.php";
    header("Location: grades.php");
    exit();
}

$selectedSection = $_POST['section'];

// Get section details based on section name
$query = "SELECT sectionId, gradeLevel, strand FROM tblsection WHERE sectionName = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $selectedSection);
$stmt->execute();
$result = $stmt->get_result();
$sectionData = $result->fetch_assoc();
$stmt->close();

if (!$sectionData) {
    $_SESSION['modalMessage'] = "Invalid section.";
    $_SESSION['modalType'] = "danger";
    $_SESSION['redirectUrl'] = "grades.php";
    header("Location: grades.php");
    exit();
}

$sectionId = $sectionData['sectionId'];
$gradeLevel = $sectionData['gradeLevel'];
$strand = $sectionData['strand'];

// Fetch subjects for the selected section
$query = "SELECT subjectId, subjectName FROM tblsubject WHERE sectionId = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $sectionId);
$stmt->execute();
$result = $stmt->get_result();
$subjects = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$gradesBySubject = [];

foreach ($subjects as $subject) {
    $subjectId = $subject['subjectId'];
    $subjectName = $subject['subjectName'];

    // Fetch student grades for each subject
    $query = "
        SELECT u.firstname, u.lastname, g.quarter1_grade, g.quarter2_grade, g.status,
               ROUND((g.quarter1_grade + g.quarter2_grade) / 2, 2) AS final_grade
        FROM tblgrades g
        JOIN tblusersaccount u ON g.userId = u.userId
        WHERE g.subjectId = ?
        ORDER BY u.lastname, u.firstname
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $subjectId);
    $stmt->execute();
    $result = $stmt->get_result();
    $grades = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $gradesBySubject[$subjectId] = ['subjectName' => $subjectName, 'grades' => $grades];
}
// Function to log actions
function logAction($name, $role, $action, $conn) {
    $timestamp = date("Y-m-d H:i:s");
    $query = "INSERT INTO tblaudit_trail (name, role, action, timestamp) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("ssss", $name, $role, $action, $timestamp);
        if ($stmt->execute()) {
            return true;
        } else {
            error_log("Error executing logAction: " . $stmt->error);
            echo "SQL Error: " . $stmt->error; // Debugging output
        }
        $stmt->close();
    } else {
        error_log("Error preparing logAction statement: " . $conn->error);
        echo "SQL Prepare Error: " . $conn->error; // Debugging output
    }
    
    return false;
}

$name = "Mary Jane";
$role = "Principal";
$action = "Viewed grades of section " . htmlspecialchars($selectedSection);

logAction($name, $role, $action, $conn);
?>

<?php include('includes/sidebar.php'); include('includes/links.php'); ?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>View Grades</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item"><a href="grades.php">Grades</a></li>
                <li class="breadcrumb-item active">View Grades</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 class="text-center mt-3">Grades for <strong><?= htmlspecialchars($selectedSection . " " . $gradeLevel . " " . $strand) ?></strong></h3>

            <?php if (!empty($gradesBySubject)): ?>
                <?php foreach ($gradesBySubject as $subjectId => $subjectData): ?>
                    <h5 class="mt-4">Subject: <strong><?= htmlspecialchars($subjectData['subjectName']) ?></strong></h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-primary">
                                <tr>
                                    <th>Name</th>
                                    <th>Quarter 1</th>
                                    <th>Quarter 2</th>
                                    <th>Final Grade</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($subjectData['grades'])): ?>
                                    <?php foreach ($subjectData['grades'] as $grade): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($grade['firstname'] . " " . $grade['lastname']) ?></td>
                                            <td><?= htmlspecialchars($grade['quarter1_grade']) ?></td>
                                            <td><?= htmlspecialchars($grade['quarter2_grade']) ?></td>
                                            <td><?= htmlspecialchars($grade['final_grade']) ?></td>
                                            <td><?= $grade['status'] == 1 ? '<span class="badge bg-success">Approved</span>' : '<span class="badge bg-warning">Pending</span>' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No grades found for this subject.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Approve Button -->
                    <?php if (!empty($subjectData['grades']) && $grade['status'] == 0): ?>
                        <form action="approve_grades.php" method="post">
                            <input type="hidden" name="sectionId" value="<?= htmlspecialchars($sectionId) ?>">
                            <input type="hidden" name="subjectId" value="<?= htmlspecialchars($subjectId) ?>">
                            <div class="d-flex justify-content-end">
                                <button type="submit" name="approve" class="btn btn-success mt-2">Approve Grades</button>
                            </div>

                        </form>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">No grades found for this section.</p>
            <?php endif; ?>

            <div class="text-left mt-4">
                <a href="grades.php" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
