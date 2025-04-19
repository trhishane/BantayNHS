<!DOCTYPE html>
<html lang="en">
  
  <body>

    <?php
      include('includes/dbconn.php');
      include('../login/authentication.php');

      if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] == TRUE) {
        $username = $_SESSION['auth_user']['username'];

        $sql = "SELECT * FROM tblusersaccount WHERE username = '$username'";
        $sql_run = mysqli_query($conn, $sql);

        if (mysqli_num_rows($sql_run) > 0) {
          $row = mysqli_fetch_assoc($sql_run);

          $userId = $row['userId'];
          $firstName = $row['firstName'];
          $middleName = $row['middleName'];
          $lastName = $row['lastName'];
          $username = $row['username'];
          $role = $row['role'];

          if ($role == 'Teacher') {
            echo "<script>alert('Teachers have no access to this page'); window.location.href = '../index.php';</script>";
          }elseif ($role == 'Student'){
            echo "<script>alert('Students have no access to this page'); window.location.href = '../index.php';</script>";
          }
        }
      } else {
        echo "You are not logged in.";
      }
    ?>
    <header id="header" class="header fixed-top d-flex align-items-center justify-content-between">

<div class="d-flex align-items-center">
  <a href="admin_dashboard.php" class="logo d-flex align-items-center">
    <img src="../assets/System Images/logo.png" alt="">
    <span class="d-none d-lg-block">eSkwela</span>
  </a>
  <i class="bi bi-list toggle-sidebar-btn"></i>
</div><!-- End Logo -->

<div class="d-flex align-items-center ms-auto">
  <a type="button" class="nav-link d-flex align-items-center text-white" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
    <i class="bi bi-person-circle fs-3 me-2"></i> <?= $firstName ?>
  </a>
</div>

</header>
