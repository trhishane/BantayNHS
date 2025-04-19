<?php include('includes/sidebar.php'); ?>


<div class=" me-3 position-relative" id="students" style="margin-left: 24%;margin-top: 6%">
  <div class="mt-3">
  <h1 class="h4">Grades List</h1>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb ">
     <li class="breadcrumb-item"><a href="dashboard.php" class="text-black">Home</a></li>
     <li class="breadcrumb-item"><a href="manage-students.php" class="text-black">Grades</a></li>
    </ol>
  </nav>

  <div class="card">
    <div class="card-body">

      <div class="">
        <h5 class="">Subjects</h5>
        <div class="">
          <?php  
            $sql = "SELECT tblstudentinfo.*, tblsubject.*
                    FROM tblstudentinfo  
                    INNER JOIN tblsubject ON tblsubject.strand = tblstudentinfo.strand AND tblsubject.gradeLevel = tblstudentinfo.gradeLevel
                    WHERE tblstudentinfo.strand = '$strand' AND tblstudentinfo.gradeLevel = '$gradeLevel' AND tblstudentinfo.userId = '$userId'";
            $sql_run = mysqli_query($conn, $sql);

            if (mysqli_num_rows($sql_run)) {
              echo "<table class='table table-bordered text-center'>
                      <tr>
                        <th>Subject Name</th>
                        <th>Strand</th>
                        <th>Grade Level</th>
                        <th>View Grades</th>
                      <tr>";
              while ($row = mysqli_fetch_assoc($sql_run)) {
                $subjectId = $row['subjectId'];
                $subjectName = $row['subjectName'];
                $strand = $row['strand'];
                $gradeLevel = $row['gradeLevel'];

                  echo "
                        <tr>
                          <td>$subjectName</td>
                          <td>$strand</td>
                          <td>$gradeLevel</td>
                          <td>
                            <a href=\"view-grades.php?strand=$strand&gradeLevel=$gradeLevel&subjectId=$subjectId&semester=1st Semester\" class='btn btn-primary btn-sm'>1st Semester</a>
                            <a href=\"view-grades.php?strand=$strand&gradeLevel=$gradeLevel&subjectId=$subjectId&semester=2nd Semester\" class='btn btn-primary btn-sm'>2nd Semester</a>
                          </td>
                        </tr>
                      ";
              }
              echo "</table>";
            }
          ?>
        </div>

      </div>
      
    </div>
  </div>

  </div>
</div>
