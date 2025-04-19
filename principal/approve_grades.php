<?php
session_start();
include '../includes/dbconn.php';

// Check if sectionId and subjectId are set
if (!isset($_POST['sectionId']) || !isset($_POST['subjectId'])) {
    $_SESSION['modalMessage'] = "Invalid request.";
    $_SESSION['modalType'] = "danger";
    $_SESSION['redirectUrl'] = "grades.php";
    header("Location: grades.php");
    exit();
}

$sectionId = $_POST['sectionId'];
$subjectId = $_POST['subjectId'];

// Retrieve section name
$sectionQuery = "SELECT sectionName FROM tblsection WHERE sectionId = ?";
$sectionStmt = $conn->prepare($sectionQuery);
$sectionStmt->bind_param("i", $sectionId);
$sectionStmt->execute();
$sectionResult = $sectionStmt->get_result();
$sectionRow = $sectionResult->fetch_assoc();
$sectionName = $sectionRow['sectionName'] ?? "Unknown Section";
$sectionStmt->close();

// Retrieve subject name
$subjectQuery = "SELECT subjectName FROM tblsubject WHERE subjectId = ?";
$subjectStmt = $conn->prepare($subjectQuery);
$subjectStmt->bind_param("i", $subjectId);
$subjectStmt->execute();
$subjectResult = $subjectStmt->get_result();
$subjectRow = $subjectResult->fetch_assoc();
$subjectName = $subjectRow['subjectName'] ?? "Unknown Subject";
$subjectStmt->close();

// Update grades status to 1 (Approved)
$query = "UPDATE tblgrades SET status = 1 WHERE subjectId = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $subjectId);

if ($stmt->execute()) {
    $_SESSION['modalMessage'] = "Grades approved successfully!";
    $_SESSION['modalType'] = "success";

    // Hardcoded values for the audit trail
    $name = "Mary Jane";
    $role = "Principal";
    $action = "Approved grades for section " . htmlspecialchars($sectionName) . " - " . htmlspecialchars($subjectName);
    $timestamp = date("Y-m-d H:i:s");

    $logQuery = "INSERT INTO tblaudit_trail (name, role, action, timestamp) VALUES (?, ?, ?, ?)";
    $logStmt = $conn->prepare($logQuery);
    $logStmt->bind_param("ssss", $name, $role, $action, $timestamp);
    $logStmt->execute();
    $logStmt->close();
} else {
    $_SESSION['modalMessage'] = "Failed to approve grades.";
    $_SESSION['modalType'] = "danger";
}

$stmt->close();
$conn->close();

// Redirect back to grades page
header("Location: grades.php");
exit();
?>
