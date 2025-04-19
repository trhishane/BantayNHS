<?php
include('../includes/dbconn.php');
if (isset($_POST['inputGrade'])) {
  $semester = $_POST['semester'];
  $subjectId = $_POST['subjectId']; 
  $userId = array();
  $preliminary = array();
  $finals = array();

  foreach ($_POST as $key => $value) {
    if (strpos($key, 'preliminary') !== false) {
      $userId[] = str_replace('preliminary_', '', $key);
      $preliminary[] = $value;
    } elseif (strpos($key, 'finals') !== false) {
      $finals[] = $value;
    }
  }

  for ($i = 0; $i < count($userId); $i++) {
    $sql = "INSERT INTO tblgrades (semester, subjectId, userId, preliminary, finals) VALUES ('$semester', '$subjectId', '$userId[$i]', '$preliminary[$i]', '$finals[$i]')";
    $sql_run = mysqli_query($conn, $sql);

    if ($sql_run) {
      echo "<script>alert('Grades Inserted Successfully'); window.location.href = '../view-students-grades.php?strand=$strand&gradeLevel=$gradeLevel&subjectId=$subjectId&semester=$semester';</script>";
    } else {
      echo "Error inserting grades for user $userId[$i]: " . mysqli_error($conn);
    }
  }
}
?>