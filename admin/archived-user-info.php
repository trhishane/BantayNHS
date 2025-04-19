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

        // Update the user's archived status
        $sqlUpdate = "UPDATE tblusersaccount SET archived = 1 WHERE userId = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("s", $userId);
        $stmtUpdate->execute();

        if ($role === 'Student') {
            header("Location: manage-students.php?archived=success");
        } elseif ($role === 'Parent') {
            header("Location: manage-parents.php?archived=success");
        } elseif ($role === 'Teacher') {
            header("Location: manage-teachers.php?archived=success");
        }else {
            header("Location: dashboard.php?archived=success");
        }
        exit();
    } else {
        header("Location: error.php?error=userNotFound");
        exit();
    }
}
?>
