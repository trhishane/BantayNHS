<?php
session_start();
include '../includes/dbconn.php';

if (!isset($_SESSION['userId'])) {
    header('Location: ../index.php');
    exit();
}

include('includes/sidebar.php');
include('includes/links.php');
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>View Attendance</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item active">Attendance</li>
            </ol>
        </nav>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title text-center">Attendance Records</h5>
                        
                        <?php
                        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['attendanceSubject'], $_GET['date'])) {
                            // Extract GET parameters
                            $classDate = $_GET['date'];
                            $subjectHandle = $_GET['attendanceSubject'];
                            list($subjectName, $sectionName) = explode('_', $subjectHandle);

                       
                            $querySection = "SELECT sectionId, strand, gradeLevel FROM tblsection WHERE sectionName = ?";
                            $stmtSection = $conn->prepare($querySection);
                            $stmtSection->bind_param("s", $sectionName);
                            $stmtSection->execute();
                            $sectionResult = $stmtSection->get_result();

                            if ($sectionResult->num_rows > 0) {
                                $sectionRow = $sectionResult->fetch_assoc();
                                $sectionId = $sectionRow['sectionId'];
                                $strand = $sectionRow['strand'];
                                $gradeLevel = $sectionRow['gradeLevel'];

                               
                                $querySubject = "SELECT subjectId FROM tblsubject WHERE subjectName = ?";
                                $stmtSubject = $conn->prepare($querySubject);
                                $stmtSubject->bind_param("s", $subjectName);
                                $stmtSubject->execute();
                                $subjectResult = $stmtSubject->get_result();

                                if ($subjectResult->num_rows > 0) {
                                    $subjectRow = $subjectResult->fetch_assoc();
                                    $subjectId = $subjectRow['subjectId'];

                                  
                                    $queryAttendance = "SELECT a.studentId, u.firstName, u.middleName, u.lastName, a.attendance 
                                                        FROM tblattendance a
                                                        JOIN tblstudentinfo s ON a.studentId = s.studentId
                                                        JOIN tblusersaccount u ON s.userId = u.userId
                                                        WHERE a.classDate = ? 
                                                          AND s.sectionId = ? 
                                                          AND a.subjectId = ?";
                                    $stmtAttendance = $conn->prepare($queryAttendance);
                                    $stmtAttendance->bind_param("sii", $classDate, $sectionId, $subjectId);
                                    $stmtAttendance->execute();
                                    $attendanceResult = $stmtAttendance->get_result();

                                    if ($attendanceResult->num_rows > 0) {
                                        echo '<div class="text-center">';
                                        echo '<h4 class="mt-2"><strong>' . htmlspecialchars($subjectName) . '</strong></h4>';
                                        echo '<p class="text-center mb-1">
                                                  Grade: <strong>' . htmlspecialchars($gradeLevel) . '</strong> | 
                                                  Strand: <strong>' . htmlspecialchars($strand) . '</strong> | 
                                                  Section: <strong>' . htmlspecialchars($sectionName) . '</strong>
                                              </p>';
                                        
                                        echo '<p>Class Date:<strong> ' . htmlspecialchars($classDate) . '</strong></p>';
                                        echo '</div>';
                                        echo '<table class="table table-striped" id="attendanceTable">';
                                        echo '<thead style="background-color: transparent;">
                                                <tr><th>#</th><th>Student ID</th><th>Name</th><th>Attendance</th></tr>
                                              </thead><tbody>';

                                        $rowNumber = 1;
                                        while ($row = $attendanceResult->fetch_assoc()) {
                                            $attendanceClass = '';
                                            $attendanceText = '';

                                         
                                            if (strtolower($row['attendance']) == 'present') {
                                                $attendanceClass = 'btn-success';
                                                $attendanceText = 'Present';
                                            } elseif (strtolower($row['attendance']) == 'absent') {
                                                $attendanceClass = 'btn-danger';
                                                $attendanceText = 'Absent';
                                            } elseif (strtolower($row['attendance']) == 'late') {
                                                $attendanceClass = 'btn-warning';
                                                $attendanceText = 'Late';
                                            }

                                            echo '<tr>';
                                            echo '<td>' . $rowNumber . '</td>';
                                            echo '<td>' . htmlspecialchars($row['studentId']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) . '</td>';
                                            echo '<td><button class="btn ' . $attendanceClass . '">' . $attendanceText . '</button></td>';
                                            echo '</tr>';

                                            $rowNumber++;
                                        }
                                        echo '</tbody></table>';
                                    } else {
                                        echo '<div class="mt-4 text-center">No attendance records found for the selected date, subject, and class.</div>';
                                    }
                                } else {
                                    echo '<div class="mt-4 text-center">Subject not found.</div>';
                                }
                            } else {
                                echo '<div class="mt-4 text-center">Invalid section or section not found.</div>';
                            }
                        } else {
                            echo '<div class="mt-4 text-center">Please select a subject and date to view attendance.</div>';
                        }
                        ?>

                      
                        <div class="text-start mt-4">
                            <a href="take-attendance.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
