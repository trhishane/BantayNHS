<?php
include('includes/dbconn.php');

$limit = 10; 
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $limit;

$searchQuery = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$totalQuery = "SELECT COUNT(*) as total FROM tblusersaccount WHERE role = 'Student' AND archived = 0";
if ($searchQuery) {
    $totalQuery .= " AND (firstName LIKE '%$searchQuery%' OR lastName LIKE '%$searchQuery%' OR username LIKE '%$searchQuery%')";
}
$totalResult = mysqli_query($conn, $totalQuery);
$totalRecords = mysqli_fetch_assoc($totalResult)['total'];
$totalPages = ceil($totalRecords / $limit);

$sql = "SELECT * FROM tblusersaccount WHERE role = 'Student' AND archived = 0";
if ($searchQuery) {
    $sql .= " AND (firstName LIKE '%$searchQuery%' OR lastName LIKE '%$searchQuery%' OR username LIKE '%$searchQuery%')";
}
$sql .= " ORDER BY accountCreatedDate DESC LIMIT $start, $limit";
$result = mysqli_query($conn, $sql);

$students = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $students[] = $row;
    }
}
?>