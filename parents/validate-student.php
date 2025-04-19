<?php
header('Content-Type: application/json');

include('includes/dbconn.php'); 

if (isset($_POST['studentId']) && isset($_POST['birthDate'])) {
    $studentId = $_POST['studentId'];
    $birthDate = $_POST['birthDate'];
    
    $query = "SELECT * FROM tblstudentinfo WHERE studentId = '$studentId' AND birthDate = '$birthDate'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['valid' => true]);
    } else {
        echo json_encode(['valid' => false]);
    }
} else {
    echo json_encode(['valid' => false, 'error' => 'Missing data']);
}
?>
