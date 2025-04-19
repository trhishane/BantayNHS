<?php
session_start();
include '../includes/dbconn.php';  

require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['userId'])) {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['subjectId']) && isset($_POST['attendance'])) {
        $subjectId = $_POST['subjectId'];
        $attendanceData = $_POST['attendance'];
        $classDate = date('Y-m-d');  

        $queryAttendance = "INSERT INTO tblattendance (subjectId, studentId, attendance, classDate) VALUES (?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE attendance = ?";
        $stmtAttendance = $conn->prepare($queryAttendance);
        
        if ($stmtAttendance) {
            foreach ($attendanceData as $studentId => $attendance) {
                $stmtAttendance->bind_param("sssss", $subjectId, $studentId, $attendance, $classDate, $attendance);
                if (!$stmtAttendance->execute()) {
                    logError("Error saving attendance for Student ID $studentId: " . $stmtAttendance->error);
                    $_SESSION['modalType'] = 'error'; 
                    $_SESSION['modalMessage'] = "Error saving attendance for Student ID $studentId. Please try again.";
                    header('Location: take-attendance.php');
                    exit();
                }

                if ($attendance === 'Absent') {
                    $queryStudentInfo = "SELECT 
                                            p.email, 
                                            CONCAT(u.firstName, ' ', u.lastName) AS studentName
                                         FROM tblparentinfo p
                                         INNER JOIN tblparent_student ps ON p.parentId = ps.parentId
                                         INNER JOIN tblstudentinfo s ON ps.studentId = s.studentId
                                         INNER JOIN tblusersaccount u ON s.userId = u.userId
                                         WHERE s.studentId = ?";
                    
                    $stmtStudentInfo = $conn->prepare($queryStudentInfo);
                    $stmtStudentInfo->bind_param("s", $studentId);
                    $stmtStudentInfo->execute();
                    $stmtStudentInfo->store_result();
                    $stmtStudentInfo->bind_result($parentEmail, $studentName);
                                
                    
                    if ($stmtStudentInfo->fetch()) {
                        $mail = new PHPMailer(true);
                        try {
                            $mail->isSMTP();
                            $mail->Host = 'smtp.gmail.com';
                            $mail->SMTPAuth = true;
                            $mail->Username = 'trhishanenicole@gmail.com';  
                            $mail->Password = 'wfhy jtvw qadf qykd';  
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                            $mail->Port = 465;

                            $mail->setFrom('trhishanenicole@gmail.com', 'Bantay National Highschool');
                            $mail->addAddress($parentEmail);

                            $mail->Subject = "Attendance Notification: Absent Student";
                            $mail->Body    = "Dear Parent, your child $studentName was absent on $classDate. Kindly prepare excuse letter.";

                            $mail->send();
                            logError("Email sent to $parentEmail for student $studentName.");
                        } catch (Exception $e) {
                            logError("Mailer Error: " . $mail->ErrorInfo);
                        }
                    } else {
                        logError("Student ID $studentId not found in database.");
                    }
                    $stmtStudentInfo->close();
                }
            }

            $_SESSION['modalType'] = 'success'; 
            $_SESSION['modalMessage'] = "Attendance saved successfully, and email notifications sent to parents of absent students!";
            header('Location: take-attendance.php');
            exit();
        } else {
            logError("Failed to prepare attendance query: " . $conn->error);
            $_SESSION['modalType'] = 'error'; 
            $_SESSION['modalMessage'] = "Failed to prepare attendance query. Please try again.";
            header('Location: take-attendance.php');
            exit();
        }
    } else {
        $_SESSION['modalType'] = 'error'; 
        $_SESSION['modalMessage'] = "Missing required fields. Please ensure all information is filled out.";
        header('Location: take-attendance.php');
        exit();
    }
} else {
    $_SESSION['modalType'] = 'error';
    $_SESSION['modalMessage'] = "Invalid request method.";
    header('Location: take-attendance.php');
    exit();
}

$conn->close();

function logError($errorMessage) {
    $logFile = '../logs/error_log.txt';  
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $errorMessage" . PHP_EOL, FILE_APPEND);
}
?>