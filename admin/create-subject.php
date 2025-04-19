<link rel="icon" type="image/x-icon" href="../assets/logo.png">
<title>Create Subject | Student Portal</title> 

<?php
include('includes/links.php');
include('includes/sidebar.php');
include('includes/dbconn.php');

if (isset($_POST['create'])) {
    $subjectName = $_POST['subjectName'];
    $semester = $_POST['semester'];
    $userId = $_POST['userId'];
    $subjectType = $_POST['subjectType'];
    $sectionId = $_POST['sectionId'];

    $subjectName = mysqli_real_escape_string($conn, $subjectName);
    $semester = mysqli_real_escape_string($conn, $semester);
    $subjectType = mysqli_real_escape_string($conn, $subjectType);
    $sectionId = mysqli_real_escape_string($conn, $sectionId);
    $userId = mysqli_real_escape_string($conn, $userId); 

    $checkUser = "SELECT * FROM tblusersaccount WHERE userId = '$userId'";
    $result = mysqli_query($conn, $checkUser);
    if (mysqli_num_rows($result) > 0) {
        $sql = "INSERT INTO tblsubject (subjectName, semester, subjectType, sectionId, userId) 
                VALUES ('$subjectName', '$semester', '$subjectType', '$sectionId', '$userId')";
        $sql_run = mysqli_query($conn, $sql);

        if ($sql_run) {
            $modalMessage = "Added successfully!";
            $modalType = "success";
        } else {
            $modalMessage = "Error: " . mysqli_error($conn);
            $modalType = "error";
        }
    } else {
        $modalMessage = "Error: User ID does not exist.";
        $modalType = "error";
    }
}
?>


<main id="main" class="main">
    <div class="pagetitle">
        <h1>Create Subject</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active">Create Subject</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-body">
            <h5>Subject Details</h5>
            <div class="d-flex justify-content-center">
                <form action="create-subject.php" method="POST" class="w-75">
                    <div class="mb-3">
                        <label class="form-label">Subject Name</label>
                        <input type="text" name="subjectName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester</label>
                        <select class="form-select" name="semester" required>
                            <option value="1st Semester">1st Semester</option>
                            <option value="2nd Semester">2nd Semester</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject Type</label>
                        <select class="form-select" name="subjectType" required>
                            <option value="Core">Core Subject</option>
                            <option value="Applied">Applied and Specialized Subject</option>
                        </select>
                    </div>
                    <div class="mb-3">
                    <label class="form-label">Section, Grade & Strand</label>
                        <select class="form-select" name="sectionId" id="sectionId" required>
                            <option value="" disabled selected>Select Section</option>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM tblsection");
                            $stmt->execute();
                            $sections = $stmt->get_result();

                            while ($section = $sections->fetch_assoc()) {
                                echo "<option value='{$section['sectionId']}'>
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
                            // Select all teachers from tblusersaccount
                            $sql = "SELECT * FROM tblusersaccount WHERE role = 'Teacher'";
                            $sql_run = mysqli_query($conn, $sql);
                            while ($row = mysqli_fetch_assoc($sql_run)) {
                                $userId = $row['userId'];
                                $firstName = $row['firstName'];
                                $middleName = $row['middleName'];
                                $lastName = $row['lastName'];
                                ?>
                                <option value="<?= $userId; ?>">
                                    <?= $firstName . ' ' . $middleName . ' ' . $lastName; ?>
                                </option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" name="create" class="btn btn-primary w-50">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!--  Modal -->
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
                window.location.href = "manage-subject.php";
            <?php } ?>
        }

        <?php if (isset($modalMessage)) { ?>
            var resultModal = new bootstrap.Modal(document.getElementById('resultModal'));
            resultModal.show();
        <?php } ?>
    </script>
</main>
