<?php
include('includes/dbconn.php');

if (isset($_GET['announcementId'])) {
    $announcementId = $_GET['announcementId'];
    
    $sql = "UPDATE tblannouncement SET status = 'active' WHERE announcementId = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $announcementId);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: manage-announcement.php?restored=success");
        exit();
    } else {
        header("Location: manage-announcement.php?restored=error");
        exit();
    }
} else {
    header("Location: manage-announcement.php?restored=error");
    exit();
}
?>
