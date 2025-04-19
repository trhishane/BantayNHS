<?php
include('includes/dbconn.php');

if (isset($_GET['userId']) && isset($_SERVER['HTTP_REFERER'])) {
    $userId = $_GET['userId'];

    $referer = $_SERVER['HTTP_REFERER'];
    
    $redirectPage = 'manage-students.php'; 
    if (strpos($referer, 'manage-teachers.php') !== false) {
        $redirectPage = 'manage-teachers.php';
    } elseif (strpos($referer, 'manage-parents.php') !== false) {
        $redirectPage = 'manage-parents.php';
    }

    $deleteQuery = "DELETE FROM tblusersaccount WHERE userId='$userId'";
    if (mysqli_query($conn, $deleteQuery)) {
        echo "<script>alert('Account deleted successfully'); window.location.href='$redirectPage';</script>";
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
} else {
    echo "Invalid parameters.";
}
?>
