
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


    if ($role == 'Teacher') {
      $sql = "SELECT * FROM tblteacherinfo WHERE userId = '$userId'";
      $sql_run = mysqli_query($conn, $sql);

      if (mysqli_num_rows($sql_run)) {
        $row = mysqli_fetch_assoc($sql_run);

        $teacherId = $row['teacherId'];
        $position = $row['position'];
        $birthDate = $row['birthDate'];
        $contactNumber = $row['contactNumber'];
        $age = $row['age'];
        $sex = $row['sex'];
        $civilStatus = $row['civilStatus'];
        $email = $row['email'];
        $barangay = $row['barangay'];
        $municipality = $row['municipality'];
        $province = $row['province'];
        $sectionId = $row['sectionId']; 

        if ($sectionId) {
          $sectionSql = "SELECT * FROM tblsection WHERE sectionId = '$sectionId'";
          $sectionSql_run = mysqli_query($conn, $sectionSql);
          if (mysqli_num_rows($sectionSql_run)) {
            $sectionRow = mysqli_fetch_assoc($sectionSql_run);
            $sectionName = $sectionRow['sectionName'];
            $gradeLevel = $sectionRow['gradeLevel'];
            $strand = $sectionRow['strand'];
          }
        } else {
          $sectionName = "No Advisory Class";  
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
      <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
      <li class="breadcrumb-item active">My Profile</li>
    </ol>
  </nav>
</div>

<div class="container-fluid">
  <div class="card mb-5">
    <div class="card-body">
      <h5 class="card-title text-center">Profile Information</h5>
      <form action="edit-profile.php" method="post">
        <hr>
        <h5 class="mt-2 mb-2">Personal Information</h5>
        <div class="row">
          <div class="col-md-12 mb-3">
            <label class="form-label">Employee No.</label>
            <input type="text" class="form-control" value="<?= $teacherId?>" disabled >
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
            <input type="date" name="birthDate" class="form-control" value="<?= $birthDate?>" disabled>
          </div>
          <div class="col-md-6 mb-3">
                <label class="form-label">Gender</label>
                <input type="text" class="form-control" value="<?= $sex?>" disabled>
          </div>
          <div class="col-md-6 mb-3">
                <label class="form-label">Age</label>
                <input type="text" class="form-control" value="<?= $age?>" disabled>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Contact Number</label>
            <input type="number" class="form-control" name="contactNumber" value="<?= $contactNumber?>" disabled>
              
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" class="form-control" name="email" value="<?= $email?>" disabled>
              
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Position</label>
            <input type="text" class="form-control" name="position" value="<?= $position?>" disabled>
              
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Civil Status</label>
            <input type="text" class="form-control" name="civilStatus" value="<?= $civilStatus?>" disabled>
              
          </div>
          <div class="col-md-6 mb-3">
    <label class="form-label">Advisory Class</label>
    <input type="text" class="form-control" name="sectionId" 
           value="<?= htmlspecialchars($sectionId ? "$sectionName (Grade $gradeLevel - $strand)" : 'No Advisory Class Assigned'); ?>" 
           disabled>
</div>

        </div>

        <hr>
        <h5 class="mt-2 mb-2">Address</h5>
        <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Province</label>
            <input type="text" class="form-control" name="province" value="<?= $province?>" disabled>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Municipality</label>
            <input type="text" class="form-control" name="municipality" value="<?= $municipality?>" disabled>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">Barangay</label>
            <input type="text" class="form-control" name="barangay" value="<?= $barangay?>" disabled>
          </div>
        </div>

        <hr>
        <h5 class="mt-2 mb-2">Account Information</h5>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Username</label>
            <input type="text" class="form-control" name="username" value="<?= $username ?>" disabled>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" name="password" value="******" disabled>
          </div>
        </div>

        <div class="d-flex justify-content-center">
          <button type="button" class="btn btn-primary mb-2 mt-2 me-3" style="padding: 0.375rem 2rem; font-size: 20px;" onclick="window.location.href='edit-account.php'">Edit Account</button>
          <button type="submit" class="btn btn-primary mb-2 mt-2" style="padding: 0.375rem 2rem; font-size: 20px;" >Edit Profile</button>
        </div>
      </form>
    </div>
  </div>
</div>

</main>

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
  document.addEventListener('DOMContentLoaded', function () {
    <?php if (isset($modalType) && isset($modalMessage)): ?>
      var modal = new bootstrap.Modal(document.getElementById('resultModal'));
      modal.show();
    <?php endif; ?>
  });
</script>

</body>
</html>
