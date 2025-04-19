<?php
include('includes/dbconn.php');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage-schoolyear.php?warning=Invalid School Year ID");
    exit();
}

$syId = $_GET['id'];
$query = "SELECT * FROM tblschoolyear WHERE syId = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $syId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$schoolYear = mysqli_fetch_assoc($result);

if (!$schoolYear) {
    header("Location: manage-schoolyear.php?warning=School Year not found");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $school_year = trim($_POST['school_year']);
    $status = $_POST['status'];

    // Check if another school year is already active
    if ($status == 'Yes') {
        $checkQuery = "SELECT * FROM tblschoolyear WHERE status = 'Yes' AND syId != ?";
        $checkStmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($checkStmt, "i", $syId);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);

        if (mysqli_num_rows($checkResult) > 0) {
            $error = "Another school year is already set as current.";
        }
    }

    if (!isset($error)) {
        $updateQuery = "UPDATE tblschoolyear SET school_year = ?, status = ? WHERE syId = ?";
        $updateStmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($updateStmt, "ssi", $school_year, $status, $syId);
        if (mysqli_stmt_execute($updateStmt)) {
            header("Location: manage-schoolyear.php?message=School Year updated successfully");
            exit();
        } else {
            $error = "Failed to update school year.";
        }
    }
}
?>

<?php

include('includes/links.php');
include('includes/sidebar.php');
?>
<link rel="icon" type="image/x-icon" href="../assets/logo.png">
<title>Edit School Year | Student Portal</title>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Edit School Year</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="admin_dashboard.php" style="text-decoration: none;">Home</a></li>
                <li class="breadcrumb-item active">Edit School Year</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">School Year</h5>
            </div>

            <?php if (isset($error)) : ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">School Year</label>
                    <input type="text" name="school_year" class="form-control" value="<?= htmlspecialchars($schoolYear['school_year']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Current School Year</label>
                    <select name="status" class="form-select" required>
                        <option value="Yes" <?= ($schoolYear['status'] == 'Yes') ? 'selected' : ''; ?>>Yes</option>
                        <option value="No" <?= ($schoolYear['status'] == 'No') ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>
                <div>
                <button type="submit" class="btn btn-primary">Update</button>
                    <a href="manage-schoolyear.php" class="btn btn-secondary">Back</a>
                </div>

                
            </form>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
