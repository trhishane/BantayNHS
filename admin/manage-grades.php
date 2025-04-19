<link rel="icon" type="image/x-icon" href="../assets/logo.png">
<title>Grades | Student Portal</title> 

<?php
include('includes/links.php');
include('includes/sidebar.php');
include('includes/dbconn.php');

$query = "
    SELECT DISTINCT 
        sec.strand, 
        sec.gradeLevel, 
        sec.sectionName 
    FROM tblsection sec
    ORDER BY sec.gradeLevel, sec.strand, sec.sectionName
";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$allSections = [];

while ($row = $result->fetch_assoc()) {
    $allSections[] = [
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
        <h1>Grades</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item active">Grades</li>
            </ol>
        </nav>
    </div>

    <div class="card mb-5">
        <div class="card-body">
            <div class="d-flex justify-content-center align-items-center">
                <div class="w-50">
                    <form id="viewStudentsForm" action="view-grades.php" method="POST">
                        <div class="mb-3 text-center">
                            <label for="sectionSelect" class="form-label">Choose a section:</label>
                            <select id="sectionSelect" class="form-select w-100" name="section" required onchange="this.form.submit()">
                                <option value="" disabled selected>Select a section</option>
                                <?php foreach ($allSections as $section): ?>
                                    <option value="<?= htmlspecialchars($section['sectionName']) ?>">
                                        <?= "Grade " . htmlspecialchars($section['gradeLevel']) . " - " . htmlspecialchars($section['strand']) . " (" . htmlspecialchars($section['sectionName']) . ")" ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-body text-center p-lg-4">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="50" height="50" fill="<?= $_SESSION['modalType'] === 'success' ? '#198754' : '#dc3545'; ?>">
                            <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"/>
                        </svg>
                        <h4 class="text-<?= $_SESSION['modalType'] === 'success' ? 'success' : 'danger'; ?> mt-3">
                            <?= $_SESSION['modalType'] === 'success' ? 'Success' : 'Error'; ?>
                        </h4>
                        <p class="mt-3 fs-5"><?= $_SESSION['modalMessage']; ?></p>
                        <button type="button" class="btn btn-<?= $_SESSION['modalType'] === 'success' ? 'success' : 'danger'; ?> btn-lg mt-3" data-bs-dismiss="modal" onclick="window.location.href='<?= $_SESSION['redirectUrl'] ?? 'input-grades.php'; ?>'">OK</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (isset($_SESSION['modalMessage'])): ?>
            new bootstrap.Modal(document.getElementById('resultModal')).show();
            <?php unset($_SESSION['modalMessage'], $_SESSION['modalType'], $_SESSION['redirectUrl']); ?>
        <?php endif; ?>
    });
</script>
