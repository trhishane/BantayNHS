<?php
include('includes/dbconn.php');

if (isset($_GET['userId'])) {
    $userId = $_GET['userId'];

    // Get the user's role
    $sqlRole = "SELECT role FROM tblusersaccount WHERE userId = ?";
    $stmtRole = $conn->prepare($sqlRole);
    $stmtRole->bind_param("s", $userId);
    $stmtRole->execute();
    $resultRole = $stmtRole->get_result();

    if ($resultRole->num_rows > 0) {
        $row = $resultRole->fetch_assoc();
        $role = $row['role'];

        $sqlUpdate = "UPDATE tblusersaccount SET archived = 0 WHERE userId = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("s", $userId);
        $stmtUpdate->execute();

        header("Location: view-archived.php?restored=success&role=" . $role);
        exit();
    } else {
        header("Location: view-archived.php?restored=error");
        exit();
    }
}
?>
