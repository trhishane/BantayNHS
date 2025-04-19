<?php
include('includes/links.php');
include('includes/sidebar.php');

include('includes/dbconn.php');

if (isset($_POST['create'])) {
  $subjectId = $_POST['subjectId'];
  $userId = $_POST['userId'];

  $check_sql = "SELECT * FROM tblclasslist WHERE subjectId = '$subjectId'";
  $check_result = mysqli_query($conn, $check_sql);

  if (mysqli_num_rows($check_result) > 0) {
    echo '<script>
    alert("The subject is already handled by another teacher!");
    window.location.href = "manage-class-list.php";
  </script>';
exit();
  } else {
    $sql = "INSERT INTO tblclasslist (subjectId, userId) 
            VALUES ('$subjectId', '$userId')";
    $sql_run = mysqli_query($conn, $sql);

    if ($sql_run) {
      echo '<script>alert("Class information created successfully!");
    window.location.href = "manage-class-list.php"; </script>';;
    exit();
    } else {
      echo '<script>alert("Error: ' . mysqli_error($conn) . '");</script>';
    }
  }
}

?>

<main id="main" class="main">

<div class="pagetitle">
  <h1>Create Class</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="#">Home</a></li>
      <li class="breadcrumb-item active">Create Class</li>
    </ol>
  </nav>
  

			<div class="card">
    <div class="card-body">
          <h5>Class Details</h5>
          <?php
          $sql = "SELECT * FROM tblsubject";
          $sql_run = mysqli_query($conn, $sql);

          if (mysqli_num_rows($sql_run) > 0) {
            ?>
            <div class="d-flex justify-content-center">
              <form action="create-class-list.php" method="POST" class="w-75">
                <div class="mb-3">
                  <label class="form-label">Subject</label>
                  <select class="form-select" name="subjectId" required>
                    <?php 
                      while ($row = mysqli_fetch_assoc($sql_run)) { 
                        $subjectId = $row['subjectId'];
                        $subjectName = $row['subjectName'];
                        $strand = $row['strand'];
                        $gradeLevel = $row['gradeLevel'];
                    ?>
                      <option value="<?= $subjectId; ?>">
                        <?= $subjectName;?> for <?= $strand;?> <?= $gradeLevel;?> 
                      </option>
                    <?php 
                      } 
                    ?>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Subject Teacher</label>
                  <select class="form-select" name="userId" required>
                    <?php
                      $sql = "SELECT * FROM tblusersaccount WHERE role = 'Teacher'";
                      $sql_run = mysqli_query($conn, $sql);
                      while ($row = mysqli_fetch_assoc($sql_run)) { 
                        $userId = $row['userId'];
                        $firstName = $row['firstName'];
                        $middleName = $row['middleName'];
                        $lastName = $row['lastName'];
                    ?>
                      <option value="<?= $userId; ?>">
                        <?= $firstName. ' ' .$middleName. ' ' .$lastName; ?>
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
          <?php } else { ?>
            <p>No subjects found.</p>
          <?php } ?>
        </div>
      </div>
    </div>
    
  </div>
</div>
</main>