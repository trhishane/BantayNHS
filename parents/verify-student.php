<?php
include('dbconn.php');

if (isset($_POST['studentId']) && isset($_POST['birthDate'])) {
    $studentId = mysqli_real_escape_string($conn, $_POST['studentId']);
    $birthDate = mysqli_real_escape_string($conn, $_POST['birthDate']);

    $query = "SELECT * FROM tblstudentinfo WHERE studentId = '$studentId' AND birthDate = '$birthDate'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
}
?>
