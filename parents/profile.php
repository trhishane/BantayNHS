<?php 
include('includes/sidebar.php'); 
include('includes/links.php');

if (isset($_SESSION['auth_user'])) {
    $username = $_SESSION['auth_user']['username'];
    $sql = "SELECT * FROM tblusersaccount WHERE username = '$username'";
    $sql_run = mysqli_query($conn, $sql);

    if (mysqli_num_rows($sql_run)) {
        $row = mysqli_fetch_assoc($sql_run);
        $userId = $row['userId'];
        $role = $row['role'];
        $firstName = $row['firstName'];
        $middleName = ($row['middleName'] === NULL || empty($row['middleName'])) ? 'N/A' : $row['middleName'];
        $lastName = $row['lastName'];
        $suffixName = ($row['suffixName'] === NULL || empty($row['suffixName'])) ? 'N/A' : $row['suffixName'];
    

        if ($role == 'Parent') {
            
            $sql = "SELECT * FROM tblparentinfo WHERE userId = '$userId'";
            $sql_run = mysqli_query($conn, $sql);

            if (mysqli_num_rows($sql_run)) {
                $row = mysqli_fetch_assoc($sql_run);

                $parentId = $row['parentId'];
                $birthDate = $row['birthDate'];
                $contactNumber = $row['contactNumber'];
                $age = $row['age'];
                $sex = $row['sex'];
                $email = $row['email'];

                $sql = "
                SELECT s.studentId, u.firstName, u.middleName, u.lastName, u.suffixName
                FROM tblparent_student ps
                JOIN tblstudentinfo s ON ps.studentId = s.studentId
                JOIN tblusersaccount u ON s.userId = u.userId
                WHERE ps.parentId = '$parentId'
                ";
                $student_sql_run = mysqli_query($conn, $sql);
                
                $students = [];
                if (mysqli_num_rows($student_sql_run) > 0) {
                    while ($student_row = mysqli_fetch_assoc($student_sql_run)) {
                        $students[] = [
                            'studentId' => $student_row['studentId'],
                            'firstName' => $student_row['firstName'],
                            'middleName' => $student_row['middleName'],
                            'lastName' => $student_row['lastName'],
                            'suffixName' => $student_row['suffixName'],
                        ];
                    }
                }
            }
        }
    }
} else {
    echo "You are not logged in.";
}

if (isset($_SESSION['modalType'])) {
    $modalType = $_SESSION['modalType'];
    $modalMessage = $_SESSION['modalMessage'];
    unset($_SESSION['modalType']);
    unset($_SESSION['modalMessage']);
}
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Profile</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="admin_dashboard.php">Home</a></li>
                <li class="breadcrumb-item active">My Profile</li>
            </ol>
        </nav>
    </div>

    <div class="container-fluid">
        <div class="card mb-5">
            <div class="card-body">
                <h5 class="mt-2 mb-1 text-center">Profile Information</h5>
                <form action="edit-profile.php" method="post">
                    <hr>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Parent Id</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($parentId) ?>" disabled>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" value="<?= $firstName?>" disabled>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Middle Name</label>
                            <input type="text" class="form-control" value="<?= $middleName?>" disabled>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" value="<?= $lastName?>" disabled>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Suffix Name</label>
                            <input type="text" class="form-control" value="<?= $suffixName?>" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Birth Date</label>
                            <input type="date" name="birthDate" class="form-control" value="<?= htmlspecialchars($birthDate) ?>" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gender</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($sex) ?>" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Age</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($age) ?>" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact Number</label>
                            <input type="number" class="form-control" name="contactNumber" value="<?= htmlspecialchars($contactNumber) ?>" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email) ?>" disabled>
                        </div>

                        <hr>
                        <h5 class="mt-2 mb-2">Enrolled Children</h5>
                        <div class="row">
                            <?php if (!empty($students)): ?>
                                <?php foreach ($students as $student): ?>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Student ID</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($student['studentId']) ?>" disabled>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Student Name</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($student['firstName'] . ' ' . $student['middleName'] . ' ' . $student['lastName']) ?>" disabled>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-md-12 mb-3">
                                    <p>No enrolled children found.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <hr>
                        <h5 class="mt-2 mb-2">Account Information</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($username) ?>" disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" value="******" disabled>
                            </div>
                        </div>

                        <div class="d-flex justify-content-center">
                            <button type="button" class="btn btn-primary mb-2 mt-2 me-3" style="padding: 0.375rem 2rem; font-size: 20px;" onclick="window.location.href='edit-account.php'">Edit Account</button>
                            <button type="submit" class="btn btn-primary mb-2 mt-2" style="padding: 0.375rem 2rem; font-size: 20px;">Edit Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center p-lg-4">
                    <i class="fas <?= htmlspecialchars($modalType === 'success' ? 'fa-check-circle' : 'fa-times-circle'); ?>" style="font-size: 50px; color: <?= htmlspecialchars($modalType === 'success' ? '#198754' : '#dc3545'); ?>;"></i>
                    <h4 class="mt-3 mb-3 text-<?= htmlspecialchars($modalType === 'success' ? 'success' : 'danger'); ?>">
                        <?= htmlspecialchars($modalType === 'success' ? 'Success' : 'Error'); ?>
                    </h4>
                    <p class="fs-5"><?= htmlspecialchars($modalMessage); ?></p>
                    <button type="button" class="btn btn-<?= htmlspecialchars($modalType === 'success' ? 'success' : 'danger'); ?> btn-lg mt-3" data-bs-dismiss="modal" id="closeModal">OK</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Modal -->

    <script>
        <?php if (isset($modalType) && isset($modalMessage)): ?>
        var myModal = new bootstrap.Modal(document.getElementById('resultModal'), {
            keyboard: false
        });
        myModal.show();
        <?php endif; ?>
    </script>
</main>

