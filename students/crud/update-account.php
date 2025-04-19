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
    $newUsername = mysqli_real_escape_string($conn, $_POST['username']);
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validate and check password strength
    if ($newPassword !== $confirmPassword) {
        $_SESSION['modalType'] = 'error';
        $_SESSION['modalMessage'] = 'Passwords do not match.';
        header("Location: ../edit-account.php");
        exit();
    }
    
    if (strlen($newPassword) < 8 || !preg_match('/[A-Z]/', $newPassword) || !preg_match('/[a-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
        $_SESSION['modalType'] = 'error';
        $_SESSION['modalMessage'] = 'Password is not strong enough. It must be at least 8 characters long and include uppercase letters, lowercase letters, and numbers.';
        header("Location: ../edit-account.php");
        exit();
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    // Update the username and password in the database
    $sql_update = "UPDATE tblusersaccount 
                   SET username = '$newUsername', password = '$hashedPassword' 
                   WHERE userId = '$userId'";

    $success = mysqli_query($conn, $sql_update);

    if ($success) {
        $_SESSION['auth_user']['username'] = $newUsername; // Update session
        $_SESSION['modalType'] = 'success';
        $_SESSION['modalMessage'] = 'Account updated successfully!';
    } else {
        $_SESSION['modalType'] = 'error';
        $_SESSION['modalMessage'] = "Error updating account: " . mysqli_error($conn);
    }

    // Redirect to profile page to show the modal
    header("Location: ../profile.php");
    exit();
} else {
    $_SESSION['modalType'] = 'error';
    $_SESSION['modalMessage'] = "Invalid request method.";
    header("Location: ../profile.php");
    exit();
}
?>
