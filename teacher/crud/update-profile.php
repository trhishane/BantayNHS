<?php 
session_start();
include('../includes/dbconn.php');

// Validate session and user authentication
if (!isset($_SESSION['auth_user'])) {
    $_SESSION['message'] = "You are not logged in.";
    header("Location: ../profile.php");
    exit();
}

// Get user ID from session
$username = $_SESSION['auth_user']['username'];
$sql = "SELECT userId FROM tblusersaccount WHERE username = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result)) {
    $row = mysqli_fetch_assoc($result);
    $userId = $row['userId'];
    mysqli_stmt_close($stmt);
} else {
    mysqli_stmt_close($stmt);
    $_SESSION['message'] = "User not found.";
    header("Location: ../profile.php");
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize input data
    $requiredFields = [
        'firstName', 'lastName', 'birthDate', 'sex', 
        'age', 'contactNumber', 'email', 'position', 'civilStatus'
    ];
    
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['modalType'] = 'error';
            $_SESSION['modalMessage'] = "Required field '$field' is missing.";
            header("Location: ../profile.php");
            exit();
        }
    }

    // Escape and prepare data
    $firstName = mysqli_real_escape_string($conn, trim($_POST['firstName']));
    $middleName = isset($_POST['middleName']) ? mysqli_real_escape_string($conn, trim($_POST['middleName'])) : '';
    $lastName = mysqli_real_escape_string($conn, trim($_POST['lastName']));
    $suffixName = isset($_POST['suffixName']) ? mysqli_real_escape_string($conn, trim($_POST['suffixName'])) : '';
    $birthDate = mysqli_real_escape_string($conn, trim($_POST['birthDate']));
    $sex = mysqli_real_escape_string($conn, trim($_POST['sex']));
    $age = intval($_POST['age']);
    $contactNumber = mysqli_real_escape_string($conn, trim($_POST['contactNumber']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $position = mysqli_real_escape_string($conn, trim($_POST['position']));
    $civilStatus = mysqli_real_escape_string($conn, trim($_POST['civilStatus']));
    $sectionId = isset($_POST['sectionId']) ? $_POST['sectionId'] : 'none';
    
    // Handle section and adviser status
    if ($sectionId === 'none') {
        $sectionId = NULL; 
        $isAdviser = 0; 
    } else {
        $isAdviser = 1; 
        // Validate section exists if provided
        $checkSection = "SELECT sectionId FROM tblsection WHERE sectionId = ?";
        $stmt = mysqli_prepare($conn, $checkSection);
        mysqli_stmt_bind_param($stmt, "i", $sectionId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) == 0) {
            mysqli_stmt_close($stmt);
            $_SESSION['modalType'] = 'error';
            $_SESSION['modalMessage'] = "Invalid section selected.";
            header("Location: ../profile.php");
            exit();
        }
        mysqli_stmt_close($stmt);
    }

    // Get current address if not provided in form
    $query = "SELECT province, municipality, barangay FROM tblteacherinfo WHERE userId = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    $provinceName = !empty($_POST['provinceName']) ? mysqli_real_escape_string($conn, trim($_POST['provinceName'])) : $row['province'];
    $municipalityName = !empty($_POST['municipalityName']) ? mysqli_real_escape_string($conn, trim($_POST['municipalityName'])) : $row['municipality'];
    $barangayName = !empty($_POST['barangayName']) ? mysqli_real_escape_string($conn, trim($_POST['barangayName'])) : $row['barangay'];

    // Begin transaction for atomic updates
    mysqli_begin_transaction($conn);

    try {
        // Update user account
        $sql_user = "UPDATE tblusersaccount 
                     SET firstName = ?, middleName = ?, lastName = ?, suffixName = ?
                     WHERE userId = ?";
        $stmt_user = mysqli_prepare($conn, $sql_user);
        mysqli_stmt_bind_param($stmt_user, "ssssi", $firstName, $middleName, $lastName, $suffixName, $userId);
        $success_user = mysqli_stmt_execute($stmt_user);
        mysqli_stmt_close($stmt_user);

        if (!$success_user) {
            throw new Exception("Failed to update user account.");
        }

        // Update teacher info
        $sql_teacher = "UPDATE tblteacherinfo 
                        SET birthDate = ?, sex = ?, age = ?, contactNumber = ?, email = ?, 
                            barangay = ?, municipality = ?, province = ?, position = ?, 
                            civilStatus = ?, sectionId = ?, isAdviser = ?
                        WHERE userId = ?";
        $stmt_teacher = mysqli_prepare($conn, $sql_teacher);
        mysqli_stmt_bind_param($stmt_teacher, "ssisssssssiii", 
            $birthDate, $sex, $age, $contactNumber, $email, $barangayName,
            $municipalityName, $provinceName, $position, $civilStatus, $sectionId, $isAdviser, $userId
        );
        $success_teacher = mysqli_stmt_execute($stmt_teacher);
        mysqli_stmt_close($stmt_teacher);

        if (!$success_teacher) {
            throw new Exception("Failed to update teacher info.");
        }

        // Commit transaction if all queries succeeded
        mysqli_commit($conn);
        $_SESSION['modalType'] = 'success';
        $_SESSION['modalMessage'] = 'Profile updated successfully!';
    } catch (Exception $e) {
        // Rollback on error
        mysqli_rollback($conn);
        $_SESSION['modalType'] = 'error';
        $_SESSION['modalMessage'] = "Error updating profile: " . $e->getMessage();
    }

    header("Location: ../profile.php");
    exit();
} else {
    $_SESSION['modalType'] = 'error';
    $_SESSION['modalMessage'] = "Invalid request method.";
    header("Location: ../profile.php");
    exit();
}
?>