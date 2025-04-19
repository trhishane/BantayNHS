<?php
session_start();
include '../includes/dbconn.php'; 

if (!isset($_SESSION['userId'])) {
    header('Location: ../index.php');
    exit();
}

if (!isset($_POST['subject'])) {
    echo "Invalid selection.";
    exit();
}

list($subjectName, $sectionName) = explode('_', $_POST['subject']);

$query = "SELECT subjectId FROM tblsubject WHERE subjectName = ? AND sectionId = (SELECT sectionId FROM tblsection WHERE sectionName = ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $subjectName, $sectionName);
$stmt->execute();
$stmt->bind_result($subjectId);
$stmt->fetch();
$stmt->close();

$query = "
    SELECT 
        u.firstName, 
        u.middleName, 
        u.lastName, 
        s.studentId,
        sec.gradeLevel, 
        sec.strand, 
        sec.sectionName
    FROM tblstudentinfo s
    INNER JOIN tblusersaccount u ON s.userId = u.userId
    INNER JOIN tblsection sec ON s.sectionId = sec.sectionId
    INNER JOIN tblsubject subj ON subj.sectionId = sec.sectionId
    WHERE subj.subjectName = ? AND sec.sectionName = ?
    ORDER BY u.lastName ASC, u.firstName ASC, u.middleName ASC"; 
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $subjectName, $sectionName);
$stmt->execute();
$result = $stmt->get_result();

$studentCount = 0;

$query = "SELECT gradeLevel, strand FROM tblsection WHERE sectionName = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $sectionName);
$stmt->execute();
$stmt->bind_result($gradeLevel, $strand);
$stmt->fetch();
$stmt->close();

?>

<?php include('includes/sidebar.php'); include('includes/links.php'); ?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Manage Attendance</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item active">Manage Attendance</li>
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
                <p>Total Students: <strong><?php echo $result->num_rows; ?></strong></p> <!-- Display total student count -->
            </div>
            <form method="POST" action="save-attendance.php" id="attendanceForm">
                <input type="hidden" name="subjectId" value="<?php echo htmlspecialchars($subjectId); ?>"> <!-- Hidden subjectId input -->
                <input type="hidden" name="subject" value="<?php echo htmlspecialchars($gradeLevel . '_' . $strand); ?>"> <!-- Hidden subject input -->
                
                <div class="mb-3 text-end">
                    <label for="classDate" class="form-label float-end mb-0">Class Date:</label>
                    <p id="classDate" class="form-control-plaintext float-end mt-0 fw-bold">
                        <?php echo date('F j, Y'); ?>
                    </p>
                </div>

                <table id="attendance-table" class="table table-striped">
                    <thead style="background-color: transparent;">
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Attendance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $studentCount = 0; ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php $studentCount++; ?>
                            <tr>
                                <td><?php echo $studentCount; ?></td>
                                <td><?php echo htmlspecialchars($row['lastName'] . ',  ' . $row['firstName']); ?></td>
                                <td>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="attendance[<?php echo htmlspecialchars($row['studentId']); ?>]" id="present-<?php echo htmlspecialchars($row['studentId']); ?>" value="Present" required>
                                        <label class="form-check-label" for="present-<?php echo htmlspecialchars($row['studentId']); ?>">Present</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="attendance[<?php echo htmlspecialchars($row['studentId']); ?>]" id="absent-<?php echo htmlspecialchars($row['studentId']); ?>" value="Absent" required>
                                        <label class="form-check-label" for="absent-<?php echo htmlspecialchars($row['studentId']); ?>">Absent</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="attendance[<?php echo htmlspecialchars($row['studentId']); ?>]" id="late-<?php echo htmlspecialchars($row['studentId']); ?>" value="Late" required>
                                        <label class="form-check-label" for="late-<?php echo htmlspecialchars($row['studentId']); ?>">Late</label>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div class="d-flex justify-content-end mt-3">
                    <button type="reset" class="btn btn-secondary me-1">Reset</button> 
                    <button type="button" class="btn btn-primary" id="reviewButton" disabled>Review Attendance</button>
                </div>
            </form>
        </div>
    </div>
</main>

<!-- Review Attendance Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reviewModalLabel">Review Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h4>Class Date: <span id="modalClassDate"></span></h4>
                <h5>Attendance Summary:</h5>
                <table class="table table-striped">
                    <thead style="background-color: transparent;">
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Attendance</th>
                        </tr>
                    </thead>
                    <tbody id="attendanceSummaryTableBody">
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Go Back</button>
                <button type="submit" class="btn btn-success" form="attendanceForm">Save Attendance</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.getElementById('reviewButton').addEventListener('click', function() {
    const attendanceRows = document.querySelectorAll('#attendance-table tbody tr');
    const attendanceSummaryTableBody = document.getElementById('attendanceSummaryTableBody');
    attendanceSummaryTableBody.innerHTML = ''; // Clear previous summary

    let classDate = document.getElementById('classDate').innerText;

    attendanceRows.forEach((row, index) => {
        const studentName = row.cells[1].innerText;
        const attendanceStatus = row.querySelector('input[type="radio"]:checked');
        
        if (attendanceStatus) {
            const statusValue = attendanceStatus.value; 
            attendanceSummaryTableBody.innerHTML += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${studentName}</td>
                    <td>${statusValue}</td>
                </tr>
            `;
        }
    });

    document.getElementById('modalClassDate').innerText = classDate;

    var reviewModal = new bootstrap.Modal(document.getElementById('reviewModal'));
    reviewModal.show();
});

const attendanceForm = document.getElementById('attendanceForm');
attendanceForm.addEventListener('change', function() {
    const allRadios = document.querySelectorAll('input[type="radio"]');
    const anySelected = Array.from(allRadios).some(radio => radio.checked);
    document.getElementById('reviewButton').disabled = !anySelected;
});
</script>
