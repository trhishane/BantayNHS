<?php
include('includes/dbconn.php');

if (isset($_GET['classId'])) {
    $classId = $_GET['classId'];
    $delete_query = "DELETE FROM tblclasslist WHERE classId='$classId'";
    $delete_run = mysqli_query($conn, $delete_query);

    if ($delete_run) {
        echo "<script>alert('Class deleted successfully'); window.location.href='manage-class-list.php';</script>";
    } else {
        echo "<script>alert('Failed to delete class'); window.location.href='manage-class-list.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request'); window.location.href='manage-class-list.php';</script>";
}
?>
