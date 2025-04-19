<?php

session_start();
include('../includes/dbconn.php');

if (isset($_POST['verify'])) {

    $verificationCode = $_POST['verificationCode'];
    $email = $_POST['email'];

    $sql = "SELECT * FROM tblverificationcodes WHERE verificationCode = '$verificationCode' LIMIT 1";
    $sql_run = mysqli_query($conn, $sql);

    if (!$sql_run) {
        echo "Error: " . mysqli_error($conn);
        exit;
    }

    if (mysqli_num_rows($sql_run) > 0) {
        $sql = "UPDATE tblverificationcodes SET verifyStatus = 1 WHERE verificationCode = '$verificationCode'";
        $sql_run = mysqli_query($conn, $sql);

        if (!$sql_run) {
            echo "Error: " . mysqli_error($conn);
            exit;
        }

        if (mysqli_affected_rows($conn) > 0) {
            echo "<script>alert('Your email has been verified successfully'); window.location.href = '../users/user-dashboard.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('Invalid verification code'); window.location.href = '../users/verify-email.php';</script>";
        exit;
    }
}