<?php
include('../includes/dbconn.php');

$userId = $_POST['userId'];
$subjectId = $_POST['subjectId'];
$semester = $_POST['semester'];
$preliminary = $_POST['preliminary'];
$finals = $_POST['finals'];

$sql = "UPDATE tblgrades SET preliminary = '$preliminary', finals = '$finals' WHERE userId = '$userId' AND subjectId = '$subjectId' AND semester = '$semester'";

if (mysqli_query($conn, $sql)) {
  echo "<script>alert('Grade Updated Successfully'); window.location.href = '../view-students-grades.php?strand=$strand&gradeLevel=$gradeLevel&subjectId=$subjectId&semester=$semester';</script>";
} else {
  echo "Error updating grade: " . mysqli_error($conn);
}

mysqli_close($conn);
?>