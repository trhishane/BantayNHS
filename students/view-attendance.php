<?php 
include('includes/sidebar.php'); 
include('includes/links.php');

if (isset($_SESSION['auth_user'])) {
    $username = $_SESSION['auth_user']['username'];
    $sql = "SELECT * FROM tblusersaccount WHERE username = '$username'";
    $sql_run = mysqli_query($conn, $sql);

    if (mysqli_num_rows($sql_run)) {
        $row = mysqli_fetch_assoc($sql_run);
        $userId = $row['userId'];

        // Check if the logged-in user is a student
        $isStudentQuery = "SELECT * FROM tblstudentinfo WHERE userId = '$userId'";
        $isStudentResult = mysqli_query($conn, $isStudentQuery);
        $isStudent = mysqli_num_rows($isStudentResult) > 0;

        if ($isStudent) {
            $studentQuery = "
            SELECT u.firstName, u.middleName, u.lastName 
            FROM tblstudentinfo s 
            JOIN tblusersaccount u ON s.userId = u.userId 
            WHERE s.userId = '$userId'
            ";
            $studentResult = mysqli_query($conn, $studentQuery);
            $student = mysqli_fetch_assoc($studentResult);
        }
    }
} else {
    echo "You are not logged in.";
    exit();
}

// Insert audit trail only when the form is submitted with a selected month
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['month'])) {
    if (isset($_SESSION['auth_user'])) {
        $username = $_SESSION['auth_user']['username'];

        $sql = "SELECT firstName, middleName, lastName, role FROM tblusersaccount WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();

            $_SESSION['firstname'] = $user['firstName'];
            $_SESSION['middlename'] = isset($user['middleName']) ? $user['middleName'] : ''; 
            $_SESSION['lastname'] = $user['lastName'];
            $_SESSION['role'] = $user['role'];

            $name = trim($user['firstName'] . " " . $_SESSION['middlename'] . " " . $user['lastName']);
            $role = $user['role'];
            $action = "Viewed attendance"; 
            $timestamp = date('Y-m-d H:i:s');

            $checkQuery = "SELECT auditId FROM tblaudit_trail WHERE name = ? AND role = ? AND action = ? AND timestamp >= NOW() - INTERVAL 1 MINUTE LIMIT 1";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("sss", $name, $role, $action);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows == 0) {
                $query = "INSERT INTO tblaudit_trail (name, role, action, timestamp) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ssss", $name, $role, $action, $timestamp);
                $stmt->execute();
                $stmt->close();
            }

            $checkStmt->close();
        } else {
            echo "User not found in the database.";
        }
    }
}
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>View Attendance</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="admin_dashboard.php">Home</a></li>
                <li class="breadcrumb-item active">View Attendance</li>
            </ol>
        </nav>
    </div>

    <div class="container-fluid">
        <div class="card mb-5">
            <div class="card-body">
                

                <!-- Month Dropdown -->
                <form action="" method="post" id="attendanceForm">
                <div class="row mb-3 mt-3">
    <div class="col-md-12 d-flex justify-content-left">
        <select class="form-select w-25" name="month" id="month" required onchange="document.getElementById('attendanceForm').submit();">
            <option value="" disabled <?= !isset($_POST['month']) ? 'selected' : ''; ?>>Select Month:</option>
            <?php
                $months = [
                    '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June',
                    '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
                ];
                foreach ($months as $monthNumber => $monthName): ?>
                    <option value="<?= $monthNumber; ?>" <?= isset($_POST['month']) && $_POST['month'] == $monthNumber ? 'selected' : ''; ?>>
                        <?= $monthName; ?>
                    </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>


                    <!-- Student Selection Dropdown (only for parents) -->
                    <?php if (!$isStudent): ?>
                        <div class="row mb-3">
                            <div class="col-md-12 d-flex justify-content-center">
                                <label class="form-label"></label>
                                <select class="form-select w-50" name="studentId" id="studentId" required onchange="document.getElementById('attendanceForm').submit();">
                                    <option value="" disabled <?= !isset($_POST['studentId']) ? 'selected' : ''; ?>>Select Child:</option>
                                    <?php foreach ($students as $student): ?>
                                        <option value="<?= htmlspecialchars($student['studentId']); ?>" <?= isset($_POST['studentId']) && $_POST['studentId'] == $student['studentId'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($student['firstName'] . ' ' . $student['middleName'] . ' ' . $student['lastName']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>
                </form>

                <?php
                if (($isStudent || (isset($_POST['studentId']) && !empty($_POST['studentId']))) && isset($_POST['month'])) {
                    $selectedStudentId = $isStudent ? (mysqli_fetch_assoc($isStudentResult))['studentId'] : $_POST['studentId'];
                    $selectedMonth = $_POST['month'];

                    $attendanceQuery = "
                    SELECT a.classdate, s.subjectName, a.attendance
                    FROM tblattendance a
                    JOIN tblsubject s ON a.subjectId = s.subjectId
                    WHERE a.studentId = '$selectedStudentId' AND MONTH(a.classdate) = '$selectedMonth'
                    ORDER BY a.classdate ASC
                    ";
                    $attendanceResult = mysqli_query($conn, $attendanceQuery);

                    if (mysqli_num_rows($attendanceResult) > 0): ?>
                        <hr>
                        <h5 class="mt-4 text-center">
                            Attendance Records
                        </h5>
                       <table class="table table-bordered">
    <thead style="background-color: transparent;">
    <tr>
    <th style="text-align: center;">Class Date</th>
    <th style="text-align: center;">Subject</th>
    <th style="text-align: center;">Attendance</th>
</tr>

    </thead>
    <tbody>
        <?php
        $previousDate = ''; // Initialize a variable to store the previous class date
        while ($attendanceRow = mysqli_fetch_assoc($attendanceResult)): ?>
            <?php
            // Check if the class date is different from the previous one
            if ($attendanceRow['classdate'] !== $previousDate): ?>
                <!-- New class date row -->
                <tr>
                    <td colspan="3" style="font-weight: bold;"><?= htmlspecialchars($attendanceRow['classdate']); ?></td>
                </tr>
            <?php endif; ?>
            <!-- Attendance rows for subjects under the same class date -->
            <tr>
                <td></td> <!-- Empty cell for class date, since it's already displayed -->
                <td><?= htmlspecialchars($attendanceRow['subjectName']); ?></td>
                <td 
                    style="<?= (strtolower($attendanceRow['attendance']) === 'absent') ? 'color: red;' : ''; ?>">
                    <?= htmlspecialchars($attendanceRow['attendance']); ?>
                </td>
            </tr>
            <?php
            // Update the previousDate variable to the current class date
            $previousDate = $attendanceRow['classdate'];
            endwhile;
        ?>
    </tbody>
</table>


                    <?php else: ?>
                        <div class="alert alert-warning" role="alert">
                            No attendance records found for the selected student in the chosen month.
                        </div>
                    <?php endif;
                }
                ?>
            </div>
        </div>
    </div>
</main>
