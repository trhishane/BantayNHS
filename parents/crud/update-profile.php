<?php 
session_start();
include('../includes/dbconn.php');

if (!isset($_SESSION['auth_user'])) {
    $_SESSION['message'] = "You are not logged in.";
    header("Location: ../profile.php");
    exit();
}

$username = $_SESSION['auth_user']['username'];
$sql = "SELECT * FROM tblusersaccount WHERE username = '$username'";
$sql_run = mysqli_query($conn, $sql);

if (mysqli_num_rows($sql_run)) {
    $row = mysqli_fetch_assoc($sql_run);
    $userId = $row['userId'];
} else {
    $_SESSION['message'] = "User not found.";
    header("Location: ../profile.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $middleName = mysqli_real_escape_string($conn, $_POST['middleName']);
    $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
    $suffixName = mysqli_real_escape_string($conn, $_POST['suffixName']);
    $birthDate = mysqli_real_escape_string($conn, $_POST['birthDate']);
    $sex = mysqli_real_escape_string($conn, $_POST['sex']);
    $age = mysqli_real_escape_string($conn, $_POST['age']);
    $contactNumber = mysqli_real_escape_string($conn, $_POST['contactNumber']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $sql_user = "UPDATE tblusersaccount 
                 SET firstName = '$firstName', middleName = '$middleName', lastName = '$lastName', suffixName = '$suffixName'
                 WHERE userId = '$userId'";

    $sql_student = "UPDATE tblparentinfo 
                    SET birthDate = '$birthDate', sex = '$sex', age = '$age', contactNumber = '$contactNumber',
                        email = '$email'
                    WHERE userId = '$userId'";

    $success_user = mysqli_query($conn, $sql_user);
    $success_student = mysqli_query($conn, $sql_student);

    if ($success_user && $success_student) {
        $_SESSION['modalType'] = 'success';
        $_SESSION['modalMessage'] = 'Updated successfully!';
    } else {
        $_SESSION['modalType'] = 'error';
        $_SESSION['modalMessage'] = "Error updating profile: " . mysqli_error($conn);
    }

    header("Location: ../profile.php");
    exit();
} else {
    $_SESSION['modalType'] = 'error';
    $_SESSION['modalMessage'] = "Invalid request method.";
    header("Location: ../profile.php");
    exit();
}
?>
