<?php
include('../includes/dbconn.php');
if (isset($_POST['insertAttendance'])) {
  $classDate = $_POST['classDate'];
  $subjectId = $_POST['subjectId']; 
  $userId = array();
  $attendance = array();

  foreach ($_POST as $key => $value) {
    if (strpos($key, 'attendance_') !== false) {
      $userId[] = str_replace('attendance_', '', $key);
      $attendance[] = $value;
    }
  }

  for ($i = 0; $i < count($userId); $i++) {
    $sql = "INSERT INTO tblattendance (userId, attendance, classDate, subjectId) VALUES ('$userId[$i]', '$attendance[$i]', '$classDate', '$subjectId')";
    $sql_run = mysqli_query($conn, $sql);

    if ($sql_run) {
      echo "<script>alert('Attendance Inserted Successfully'); window.location.href = '../view-attendance.php?strand=$strand&gradeLevel=$gradeLevel&subjectId=$subjectId&classDate=$classDate';</script>";
    } else {
      echo "Error inserting attendance for user $userId[$i]: " . mysqli_error($conn);
    }
  }
}
?>