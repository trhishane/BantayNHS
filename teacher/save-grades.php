<?php
session_start();
include '../includes/dbconn.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
       
        if (!isset($_POST['subjectId'], $_POST['quarter1_grade'])) {
            throw new Exception("Missing required fields. Please ensure all information is filled out.");
        }

        $subjectId = $_POST['subjectId']; 
        $semester = $_POST['semester']; 
        $quarter1_grades = $_POST['quarter1_grade']; 
        $quarter2_grades = $_POST['quarter2_grade'] ?? [];
        $syId = $_POST['syId'] ?? ''; 

        if (empty($syId)) {
            throw new Exception("School Year ID (syId) is required.");
        }

        $checkQuery = "SELECT gradeId FROM tblgrades WHERE subjectId = ? AND userId = ? AND semester = ?";
        $insertQuery = "INSERT INTO tblgrades (subjectId, userId, semester, syId, quarter1_grade, quarter2_grade) VALUES (?, ?, ?, ?, ?, ?)";
        $updateQuery = "UPDATE tblgrades SET quarter1_grade = ?, quarter2_grade = ?, syId = ? WHERE gradeId = ?";

        $checkStmt = $conn->prepare($checkQuery);
        if ($checkStmt === false) {
            throw new Exception("Failed to prepare check query: " . htmlspecialchars($conn->error));
        }

        foreach ($quarter1_grades as $userId => $quarter1_grade) {
            $quarter2_grade = $quarter2_grades[$userId] ?? 0;

            $checkStmt->bind_param("iss", $subjectId, $userId, $semester); 
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows > 0) {
                $gradeRow = $checkResult->fetch_assoc();
                $gradeId = $gradeRow['gradeId'];

                $updateStmt = $conn->prepare($updateQuery);
                if ($updateStmt === false) {
                    throw new Exception("Failed to prepare update query: " . htmlspecialchars($conn->error));
                }

                $updateStmt->bind_param("ssii", $quarter1_grade, $quarter2_grade, $syId, $gradeId);
                $updateStmt->execute();

                if ($updateStmt->error) {
                    throw new Exception("Update query error: " . htmlspecialchars($updateStmt->error));
                }

                $updateStmt->close();
            } else {
                $insertStmt = $conn->prepare($insertQuery);
                if ($insertStmt === false) {
                    throw new Exception("Failed to prepare insert query: " . htmlspecialchars($conn->error));
                }

                $insertStmt->bind_param("isssss", $subjectId, $userId, $semester, $syId, $quarter1_grade, $quarter2_grade); // userId as string "s"
                $insertStmt->execute();

                if ($insertStmt->error) {
                    throw new Exception("Insert query error: " . htmlspecialchars($insertStmt->error));
                }

                $insertStmt->close();
            }
        }

        $checkStmt->close();

        setSessionSuccess("Grades saved successfully!");
        header('Location: input-grades.php');
        exit();
    } else {
        throw new Exception("Invalid request method.");
    }
} catch (Exception $e) {
    setSessionError($e->getMessage());
    header('Location: input-grades.php');
    exit();
} finally {
    if ($conn) {
        $conn->close();
    }
}

function setSessionSuccess($message) {
    $_SESSION['modalType'] = 'success'; 
    $_SESSION['modalMessage'] = $message;
}

function setSessionError($message) {
    $_SESSION['modalType'] = 'error'; 
    $_SESSION['modalMessage'] = $message;
}
?>
