<?php
include('includes/dbconn.php');

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    // Check if the selected school year is the current active one
    $checkQuery = "SELECT * FROM tblschoolyear WHERE syId = '$id' AND status = 'Yes'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        // If it's the current school year, prevent deletion
        header("Location: manage-schoolyear.php?warning=Cannot delete the current school year!");
        exit();
    } else {
        // Delete the school year if it's not the current one
        $deleteQuery = "DELETE FROM tblschoolyear WHERE syId = '$id'";
        if (mysqli_query($conn, $deleteQuery)) {
            header("Location: manage-schoolyear.php?message=School year deleted successfully!");
            exit();
        } else {
            header("Location: manage-schoolyear.php?warning=Error deleting school year.");
            exit();
        }
    }
} else {
    header("Location: manage-schoolyear.php?warning=Invalid request.");
    exit();
}
?>
