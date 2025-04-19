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

<?php include('includes/sidebar.php'); include('includes/links.php');  ?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Attendance</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item active">Input Attendance</li>
            </ol>
        </nav>
    </div>

    <div class="card mb-5">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title">Take Attendance</h5>
            </div>
            <div class="d-flex justify-content-center align-items-center">
                <div class="w-50">
                    <form id="takeAttendanceForm" action="manage-attendance.php" method="POST">
                        <div class="mb-3 text-center">
                            <label for="subjectSelect" class="form-label">Choose a subject handle: </label>
                            <select id="subjectSelect" class="form-select form-select-m w-100 mx-auto d-block" name="subject" required>
                            <option value="" disabled selected>-- Select a Subject --</option>    
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
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title">View Attendance</h5>
            </div>
            <div class="d-flex justify-content-center align-items-center">
                <div class="w-50">
                    <form id="viewAttendanceForm" action="view-attendance.php" method="GET" class="w-100">
                    <div class="mb-3 text-center">
                                <label for="attendanceSubjectSelect" class="form-label" style="font-size: 15px;">Choose a subject handle: </label>
                                <select id="attendanceSubjectSelect" class="form-select form-select-s w-100" name="attendanceSubject" required>
                                <option value="" disabled selected>-- Select a Subject --</option>
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
                            <div class="mb-3 mt-2 text-center">
                                <label for="attendanceDate" class="form-label" style="font-size: 15px;">Choose a date:</label>
                                <input type="date" id="attendanceDate" name="date" class="form-control w-100 mx-auto" required>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center p-lg-4">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="50" height="50" fill="<?php echo $_SESSION['modalType'] === 'success' ? '#198754' : '#dc3545'; ?>">
                        <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"/>
                    </svg>
                    <h4 class="text-<?php echo $_SESSION['modalType'] === 'success' ? 'success' : 'danger'; ?> mt-3">
                        <?php echo $_SESSION['modalType'] === 'success' ? 'Success' : 'Error'; ?>
                    </h4>
                    <p class="mt-3 fs-5"><?php echo $_SESSION['modalMessage']; ?></p>
                    <button type="button" class="btn btn-<?php echo $_SESSION['modalType'] === 'success' ? 'success' : 'danger'; ?> btn-lg mt-3" data-bs-dismiss="modal" onclick="window.location.href='<?php echo $_SESSION['redirectUrl'] ?? 'take-attendance.php'; ?>'">OK</button>
                </div>
            </div>
        </div>
    </div>

</main>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (isset($_SESSION['modalMessage'])): ?>
            var myModal = new bootstrap.Modal(document.getElementById('resultModal'));
            myModal.show();
            <?php unset($_SESSION['modalMessage']); unset($_SESSION['modalType']); unset($_SESSION['redirectUrl']); ?>
        <?php endif; ?>

        document.getElementById('subjectSelect').addEventListener('change', function() {
            document.getElementById('takeAttendanceForm').submit();
        });
        document.getElementById('attendanceDate').addEventListener('change', function() {
            document.getElementById('viewAttendanceForm').submit();
        });
    });
</script>