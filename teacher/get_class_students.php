<?php
session_start();
include('../includes/dbconn.php');

if (!isset($_SESSION['teacher_id']) || !isset($_GET['class_id'])) {
    http_response_code(403);
    exit;
}

$class_id = $_GET['class_id'];
$sql = "SELECT s.student_id, s.first_name, s.last_name 
        FROM tbl_students s 
        JOIN tbl_enrollment e ON s.student_id = e.student_id 
        WHERE e.class_id = ? 
        ORDER BY s.last_name, s.first_name";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $class_id);
$stmt->execute();
$result = $stmt->get_result();

$students = array();
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

header('Content-Type: application/json');
echo json_encode($students); 