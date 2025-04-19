<?php
require_once('../includes/dbconn.php');

if (isset($_POST['fetchStudents']) && isset($_POST['sectionId'])) {
    $sectionId = $_POST['sectionId'];

    $sql = "SELECT s.studentId, u.firstName, u.lastName
            FROM tblstudentinfo s
            JOIN tblusersaccount u ON s.userId = u.userId
            WHERE s.sectionId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $sectionId);
    $stmt->execute();
    $result = $stmt->get_result();

    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($students);
    exit;
}
?>
