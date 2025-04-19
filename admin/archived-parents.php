<?php
include('includes/dbconn.php');

if (isset($_GET['userId'])) {
    $userId = $_GET['userId'];

    $sql = "UPDATE tblusersaccount SET archived = 1 WHERE userId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userId);
    $stmt->execute();

    header("Location: manage-parents.php?archived=success");
    exit();
}
?>
