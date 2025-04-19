<?php
session_start();
include '../includes/dbconn.php'; 

if (!isset($_SESSION['userId'])) {
    header('Location: ../index.php');
    exit();
}

$teacherId = $_SESSION['userId'];

$query = "
    SELECT 
        s.subjectName, 
        sec.strand, 
        sec.gradeLevel, 
        sec.sectionName 
    FROM tblsubject s
    INNER JOIN tblsection sec 
        ON s.sectionId = sec.sectionId
    WHERE s.userId = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $teacherId);
$stmt->execute();
$result = $stmt->get_result();

$assignedSubjects = [];
while ($row = $result->fetch_assoc()) {
    $assignedSubjects[] = [
        'subjectName' => $row['subjectName'],
        'strand' => $row['strand'],
        'gradeLevel' => $row['gradeLevel'],
        'sectionName' => $row['sectionName']
    ];
}
$stmt->close();

?>

<?php include('includes/sidebar.php'); include('includes/links.php'); ?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Students List</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item active">Students List</li>
            </ol>
        </nav>
    </div>

    <div class="card mb-5">
        <div class="card-body">
            <form id="viewStudentsForm" action="parents-list.php" method="GET">
                <div class="mb-3 text-center">
                    <label for="subjectSelect" class="form-label">Choose a subject handle: </label>
                    <select id="subjectSelect" class="form-select form-select-m w-50 mx-auto" name="subject" required onchange="this.form.submit()">
                        <option value="" disabled selected>Select Subject:</option> 
                        <?php foreach ($assignedSubjects as $subject): ?>
                            <option value="<?php echo htmlspecialchars($subject['subjectName']) . '_' . htmlspecialchars($subject['sectionName']); ?>">
                                <?php 
                                    echo htmlspecialchars($subject['subjectName']) . ' (' . 
                                         htmlspecialchars($subject['gradeLevel']) . ' ' . 
                                         htmlspecialchars($subject['strand']) . ' - ' . 
                                         htmlspecialchars($subject['sectionName']) . ')'; 
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
