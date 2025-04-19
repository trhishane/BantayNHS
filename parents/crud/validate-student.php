<?php
include 'incudes/dbconn.php';

if (isset($_POST['studentId']) && isset($_POST['birthDate'])) {
    $studentId = mysqli_real_escape_string($conn, $_POST['studentId']);
    $birthDate = mysqli_real_escape_string($conn, $_POST['birthDate']);

    $query = "SELECT * FROM tblstudentinfo WHERE studentId = '$studentId' AND birthDate = '$birthDate'";
    $result = mysqli_query($conn, $query);

    echo json_encode(['valid' => mysqli_num_rows($result) > 0]);
} else {
    echo json_encode(['valid' => false]);
}
?>
