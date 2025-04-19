<?php
include('includes/dbconn.php');

if (isset($_GET['subjectId'])) {
    $subjectId = $_GET['subjectId'];

    $delete_query = "DELETE FROM tblsubject WHERE subjectId='$subjectId'";
    $delete_run = mysqli_query($conn, $delete_query);

    if ($delete_run) {
        echo "<script>alert('Subject deleted successfully'); window.location.href='manage-subject.php';</script>";
    } else {
        echo "<script>alert('Failed to delete subject'); window.location.href='manage-subject.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request'); window.location.href='manage-subject.php';</script>";
}
?>
