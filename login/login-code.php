<?php
session_start();
include '../includes/dbconn.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

function setAlert($message, $type, $redirectUrl) {
    $_SESSION['alertMessage'] = $message;
    $_SESSION['alertType'] = $type;
    header("Location: ../$redirectUrl");
    exit();
}

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        setAlert('Please fill all fields', 'danger', 'index.php');
    }

    // Fetch user from tblusersaccount
    $sql = "SELECT * FROM tblusersaccount WHERE username = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    
    if (!$stmt->execute()) {
        setAlert('Database query failed', 'danger', 'index.php');
    }
    
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $userId = $row['userId'];
        $hashedPassword = $row['password'];

        if (password_verify($password, $hashedPassword) || $password === $hashedPassword) {
            $_SESSION['authenticated'] = TRUE;
            $_SESSION['userId'] = $userId;
            $_SESSION['auth_user'] = ['username' => $row['username']];
            $_SESSION['role'] = $row['role'];

            // Determine profile table and redirection based on role
            switch ($row['role']) {
                case 'Teacher':
                    $sql = "SELECT * FROM tblteacherinfo WHERE userId = ?";
                    $dashboardUrl = 'teacher/dashboard.php';
                    $getStartedUrl = 'teacher/get-started.php';
                    break;
                case 'Parent':
                    $sql = "SELECT * FROM tblparentinfo WHERE userId = ?";
                    $dashboardUrl = 'parents/dashboard.php';
                    $getStartedUrl = 'parents/get-started.php';
                    break;
                case 'Student':
                    $sql = "SELECT * FROM tblstudentinfo WHERE userId = ?";
                    $dashboardUrl = 'students/dashboard.php';
                    $getStartedUrl = 'students/get-started.php';
                    break;
                default:
                    setAlert('Invalid role', 'danger', 'index.php');
            }

            // Close the previous statement
            $stmt->close();

            // Prepare and execute profile check
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $userId);
            
            if (!$stmt->execute()) {
                setAlert('Error fetching user details', 'danger', 'index.php');
            }

            $profileResult = $stmt->get_result();

            if ($profileResult->num_rows > 0) {
                // Profile exists, redirect to the dashboard
                header("Location: ../$dashboardUrl");
            } else {
                // No profile found, redirect to respective get-started page
                header("Location: ../$getStartedUrl");
            }
            exit();
        } else {
            setAlert('Incorrect password', 'danger', 'index.php');
        }
    } else {
        setAlert('Username not found', 'danger', 'index.php');
    }
}
