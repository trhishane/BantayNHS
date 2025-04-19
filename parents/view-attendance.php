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

        $sql = "
        SELECT s.studentId, u.firstName, u.middleName, u.lastName
        FROM tblparent_student ps
        JOIN tblstudentinfo s ON ps.studentId = s.studentId
        JOIN tblusersaccount u ON s.userId = u.userId
        WHERE ps.parentId = (SELECT parentId FROM tblparentinfo WHERE userId = '$userId')
        ";
        $student_sql_run = mysqli_query($conn, $sql);

        $students = [];
        if (mysqli_num_rows($student_sql_run) > 0) {
            while ($student_row = mysqli_fetch_assoc($student_sql_run)) {
                $students[] = [
                    'studentId' => $student_row['studentId'],
                    'firstName' => $student_row['firstName'],
                    'middleName' => $student_row['middleName'],
                    'lastName' => $student_row['lastName'],
                ];
            }
        }
    }
} else {
    echo "You are not logged in.";
    exit();
}

// Function to log actions
function logAction($name, $role, $action, $conn) {
    $timestamp = date("Y-m-d H:i:s");
    $query = "INSERT INTO tblaudit_trail (name, role, action, timestamp) VALUES ( ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssss", $name, $role, $action, $timestamp);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
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
                <h5 class="mt-4 mb-1 text-center">Select a Child</h5>

                <form action="" method="post" id="attendanceForm">
                    <div class="row mb-3">
                        <div class="col-md-12 d-flex justify-content-center">
                            <select class="form-select w-50" name="studentId" id="studentId" required>
                                <option value="" disabled <?= !isset($_POST['studentId']) ? 'selected' : ''; ?>>Select Child:</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?= htmlspecialchars($student['studentId']); ?>" <?= isset($_POST['studentId']) && $_POST['studentId'] == $student['studentId'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($student['firstName'] . ' ' . $student['middleName'] . ' ' . $student['lastName']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-12 d-flex justify-content-center">
                            <select class="form-select w-25" name="month" id="month" required onchange="document.getElementById('attendanceForm').submit();">
                                <option value="" disabled <?= !isset($_POST['month']) ? 'selected' : ''; ?>>Select Month:</option>
                                <?php 
                                $months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                                foreach ($months as $key => $month): ?>
                                    <option value="<?= $key + 1; ?>" <?= isset($_POST['month']) && $_POST['month'] == ($key + 1) ? 'selected' : ''; ?>>
                                        <?= $month; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </form>

                <?php
                if (isset($_POST['studentId']) && !empty($_POST['studentId']) && isset($_POST['month']) && !empty($_POST['month'])) {
                    $selectedStudentId = $_POST['studentId'];
                    $selectedMonth = $_POST['month'];

                    $studentQuery = "
                    SELECT u.firstName, u.middleName, u.lastName 
                    FROM tblstudentinfo s 
                    JOIN tblusersaccount u ON s.userId = u.userId 
                    WHERE s.studentId = '$selectedStudentId'
                    ";
                    $studentResult = mysqli_query($conn, $studentQuery);
                    $student = mysqli_fetch_assoc($studentResult);

                    $attendanceQuery = "
                    SELECT a.classdate, s.subjectName, a.attendance
                    FROM tblattendance a
                    JOIN tblsubject s ON a.subjectId = s.subjectId
                    WHERE a.studentId = '$selectedStudentId' AND MONTH(a.classdate) = '$selectedMonth'
                    ORDER BY a.classdate ASC
                    ";
                    $attendanceResult = mysqli_query($conn, $attendanceQuery);

                    if (isset($_SESSION['auth_user'])) {
                        $username = $_SESSION['auth_user']['username'];
                        $userQuery = "SELECT userId, firstName, middleName, lastName, role FROM tblusersaccount WHERE username = '$username'";
                        $userResult = mysqli_query($conn, $userQuery);
                        if ($userResult && mysqli_num_rows($userResult) > 0) {
                            $user = mysqli_fetch_assoc($userResult);
                            $name = trim($user['firstName'] . " " . $user['middleName'] . " " . $user['lastName']);
                            $userId = $user['userId'];
                            $role = $user['role'];
                            logAction($name, $role, "Viewed attendance", $conn);

                        }
                    }

                    if (mysqli_num_rows($attendanceResult) > 0): ?>
                        <hr>
                        <h5 class="mt-4 text-center">
                            Attendance Records
                        </h5>
                       <table class="table table-bordered">
    <thead style="background-color: transparent;">
    <tr class="table-primary">
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

<script>
    document.getElementById("month").addEventListener("change", function() {
        document.getElementById("attendanceForm").submit();
    });
</script>
