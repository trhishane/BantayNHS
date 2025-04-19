<?php
include('includes/dbconn.php');

if (isset($_GET['announcementId'])) {
    $announcementId = $_GET['announcementId'];
    
    // Archive the announcement
    $sql = "UPDATE tblannouncement SET status = 'archived' WHERE announcementId = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $announcementId);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: manage-announcement.php?archived=success");
    } else {
        echo "Error archiving the announcement!";
    }
}
?>
