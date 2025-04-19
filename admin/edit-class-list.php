<?php
include('includes/dbconn.php');
include('includes/sidebar.php');
include('includes/links.php');

if (isset($_GET['classId'])) {
    $classId = $_GET['classId'];

    $query = "SELECT * FROM tblclasslist WHERE classId='$classId'";
    $query_run = mysqli_query($conn, $query);
    $class = mysqli_fetch_assoc($query_run);

    if (isset($_POST['update'])) {
        $subjectId = $_POST['subjectId'];
        $userId = $_POST['userId'];

        $update_query = "UPDATE tblclasslist SET subjectId='$subjectId', userId='$userId' WHERE classId='$classId'";
        $update_run = mysqli_query($conn, $update_query);

        if ($update_run) {
            $modalMessage = "Updated successfully!";
            $modalType = "success";
        } else {
            $modalMessage = "Error: " . mysqli_error($conn);
            $modalType = "error";
        }
    }
}
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Edit Class</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="admin_dashboard.php">Home</a></li>
                <li class="breadcrumb-item"><a href="manage-class-list.php">Manage Class List</a></li>
                <li class="breadcrumb-item active">Edit Class</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Class Details</h5>

            <!-- Edit Class Form -->
            <form method="POST">
                <div class="mb-3">
                    <label for="subjectId" class="form-label">Class Subject</label>
                    <select id="subjectId" name="subjectId" class="form-select" required>
                        <?php
                        $subject_query = "SELECT subjectId, subjectName FROM tblsubject";
                        $subject_run = mysqli_query($conn, $subject_query);

                        while ($subject = mysqli_fetch_assoc($subject_run)) {
                            $selected = ($subject['subjectId'] == $class['subjectId']) ? 'selected' : '';
                            echo "<option value='{$subject['subjectId']}' $selected>{$subject['subjectName']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="gradeLevel" class="form-label">Grade Level</label>
                    <input type="text" id="gradeLevel" name="gradeLevel" class="form-control" value="<?php echo $class['gradeLevel']; ?>" readonly>
                </div>

                <div class="mb-3">
                    <label for="strand" class="form-label">Strand</label>
                    <input type="text" id="strand" name="strand" class="form-control" value="<?php echo $class['strand']; ?>" readonly>
                </div>

                <div class="mb-3">
                    <label for="userId" class="form-label">Subject Teacher</label>
                    <select id="userId" name="userId" class="form-select" required>
                        <?php
                        $teacher_query = "SELECT userId, firstName, middleName, lastName FROM tblusersaccount WHERE role = 'Teacher'";
                        $teacher_run = mysqli_query($conn, $teacher_query);

                        while ($teacher = mysqli_fetch_assoc($teacher_run)) {
                            $selected = ($teacher['userId'] == $class['userId']) ? 'selected' : '';
                            echo "<option value='{$teacher['userId']}' $selected>{$teacher['firstName']} {$teacher['middleName']} {$teacher['lastName']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <a href="manage-class-list.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Back</a>
                <button type="submit" name="update" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>

    <!-- Custom Bootstrap Modal -->
    <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-body text-center p-lg-4">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="50" height="50" fill="<?php echo $modalType === 'success' ? '#198754' : '#dc3545'; ?>">
                        <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"/>
                    </svg>
                    <h4 class="text-<?php echo $modalType === 'success' ? 'success' : 'danger'; ?> mt-3">
                        <?php echo $modalType === 'success' ? 'Success' : 'Error'; ?>
                    </h4>
                    <p class="mt-3 fs-5"><?php echo $modalMessage; ?></p>
                    <button type="button" class="btn btn-<?php echo $modalType === 'success' ? 'success' : 'danger'; ?> btn-lg mt-3" data-bs-dismiss="modal" onclick="resetForm()">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function resetForm() {
        <?php if ($modalType === 'success') { ?>
            window.location.href = "manage-class-list.php";
        <?php } ?>
    }

    <?php if ($modalMessage) { ?>
    var resultModal = new bootstrap.Modal(document.getElementById('resultModal'));
    resultModal.show();
    <?php } ?>
    </script>
</main>
