<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>eSkwela</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
  </head>
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

          if ($role == 'Student') {
            echo "<script>alert('Students have no access to this page'); window.location.href = '../index.php';</script>";
          }elseif ($role == 'Parent'){
            echo "<script>alert('Parents have no access to this page'); window.location.href = '../index.php';</script>";
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
</div>

<div class="d-flex align-items-center ms-auto">
  <a type="button" class="nav-link d-flex align-items-center text-white" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
    <i class="bi bi-person-circle fs-3 me-2"></i> <?= $firstName ?>
  </a>
</div>

</header>
