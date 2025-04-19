<?php
include('includes/dbconn.php');

if (isset($_GET['subjectId'])) {
    $subjectId = $_GET['subjectId'];

    $query = "SELECT * FROM tblsubject WHERE subjectId='$subjectId'";
    $query_run = mysqli_query($conn, $query);

    if (mysqli_num_rows($query_run) > 0) {
        $row = mysqli_fetch_assoc($query_run);

        $subjectName = $row['subjectName'];
        $semester = $row['semester'];
        $subjectType = $row['subjectType']; 
        $sectionId = $row['sectionId'];
        $userId = $row['userId'];
    }
}

if (isset($_POST['update_subject'])) {
    $subjectName = $_POST['subjectName'];
    $semester = $_POST['semester'];
    $subjectType = $_POST['subjectType']; 
    $sectionId = $_POST['sectionId']; 
    $userId = $_POST['userId'];

    $update_query = "UPDATE tblsubject SET subjectName='$subjectName', semester='$semester', subjectType='$subjectType', sectionId='$sectionId', userId='$userId' WHERE subjectId='$subjectId'";
    $update_run = mysqli_query($conn, $update_query);

    if ($update_run) {
        $modalMessage = "Updated successfully!";
        $modalType = "success";
    } else {
        $modalMessage = "Error: " . mysqli_error($conn);
        $modalType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Subject</title>
    <?php include('includes/links.php'); ?>
</head>
<body>
    <?php include('includes/sidebar.php'); ?>
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Edit Subject</h1>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="" method="POST">
                    <div class="form-group mb-3">
                        <label for="subjectName">Subject Name</label>
                        <input type="text" name="subjectName" class="form-control" value="<?php echo htmlspecialchars($subjectName); ?>" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="semester">Semester</label>
                        <select class="form-select" name="semester" required>
                            <option value="1st Semester" <?php echo $semester == '1st Semester' ? 'selected' : ''; ?>>1st Semester</option>
                            <option value="2nd Semester" <?php echo $semester == '2nd Semester' ? 'selected' : ''; ?>>2nd Semester</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="subjectType">Subject Type</label>
                        <select class="form-select" name="subjectType" required>
                            <option value="Core" <?php echo $subjectType == 'Core' ? 'selected' : ''; ?>>Core Subject</option>
                            <option value="Applied" <?php echo $subjectType == 'Applied' ? 'selected' : ''; ?>>Applied and Specialized Subject</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                    <label class="form-label">Section, Grade & Strand</label>
                        <select class="form-select" name="sectionId" id="sectionId" required>
                            <option value="" disabled selected>Select Section</option>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM tblsection");
                            $stmt->execute();
                            $sections = $stmt->get_result();

                            while ($section = $sections->fetch_assoc()) {
                                $selected = ($section['sectionId'] == $sectionId) ? 'selected' : '';
                                echo "<option value='{$section['sectionId']}' $selected>
                                    {$section['sectionName']} - Grade {$section['gradeLevel']} ({$section['strand']})
                                    </option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="userId" class="form-label">Subject Teacher</label>
                        <select class="form-select" name="userId" required>
                            <?php
                            $sql = "SELECT * FROM tblusersaccount WHERE role = 'Teacher'";
                            $sql_run = mysqli_query($conn, $sql);
                            while ($row = mysqli_fetch_assoc($sql_run)) {
                                $teacherId = $row['userId'];
                                $firstName = $row['firstName'];
                                $middleName = $row['middleName'];
                                $lastName = $row['lastName'];
                                $suffixName = $row['suffixName'];
                                ?>
                                <option value="<?= $teacherId; ?>" <?php echo $teacherId == $userId ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($firstName . ' ' . $middleName . ' ' . $lastName); ?>
                                </option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <a href="manage-subject.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Back</a>
                    <button type="submit" name="update_subject" class="btn btn-primary">Update</button>
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
            <?php if (isset($modalType) && $modalType === 'success') { ?>
                window.location.href = "manage-subject.php";
            <?php } ?>
        }

        <?php if (isset($modalMessage)) { ?>
            var resultModal = new bootstrap.Modal(document.getElementById('resultModal'));
            resultModal.show();
        <?php } ?>
        </script>
    </main>
</body>
</html>
