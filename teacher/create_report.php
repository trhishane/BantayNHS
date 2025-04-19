<?php
session_start();
include '../config/dbcon.php';

// Check if teacher is logged in
if (!isset($_SESSION['userId'])) {
    $_SESSION['message'] = "You are not authorized as a teacher";
    header("Location: ../login.php");
    exit(0);
}

$teacher_id = $_SESSION['userId'];

// Get teacher details
$stmt = mysqli_query($con, "SELECT t.*, ua.firstName, ua.middleName, ua.lastName 
                           FROM tblteacherinfo t 
                           JOIN tblusersaccount ua ON t.userId = ua.userId 
                           WHERE t.userId = '$teacher_id'");
$teacher_data = mysqli_fetch_assoc($stmt);

// Process report generation
$report_data = [];
$report_title = "";
$show_report = false;

if (isset($_POST['generate'])) {
    $report_type = $_POST['report_type'];
    $academic_year = isset($_POST['academic_year']) ? $_POST['academic_year'] : '';
    $semester = isset($_POST['semester']) ? $_POST['semester'] : '';
    $show_report = true;
    
    // Set report title based on type
    switch ($report_type) {
        case 'student_list':
            $report_title = "Student List Report";
            // Get students under this teacher based on the actual database structure
            try {
                // Simpler query that selects all columns
                $query = "SELECT DISTINCT s.studentId, s.userId, s.sectionId, s.sex, 
                         ua.firstName, ua.middleName, ua.lastName, 
                         sec.sectionName as section, sec.gradeLevel as grade_level
                         FROM tblstudentinfo s 
                         JOIN tblusersaccount ua ON s.userId = ua.userId AND ua.role = 'student'
                         JOIN tblsection sec ON s.sectionId = sec.sectionId
                         JOIN tblteacherinfo t ON sec.sectionId = t.sectionId
                         WHERE t.userId = '$teacher_id'";
                $result = mysqli_query($con, $query);
                if ($result) {
                    while($row = mysqli_fetch_assoc($result)) {
                        $report_data[] = $row;
                    }
                } else {
                    $_SESSION['message'] = "Error retrieving student data: " . mysqli_error($con);
                }
            } catch (Exception $e) {
                // If query fails, show error message
                $_SESSION['message'] = "Error retrieving student data: " . $e->getMessage();
            }
            break;
            
        case 'academic_performance':
            $report_title = "Academic Performance Report";
            
            // Get current active academic year and semester if not selected
            if (empty($academic_year) || empty($semester)) {
                // Get current active school year
                $current_year_query = "SELECT school_year FROM tblschoolyear WHERE status = 'Active' LIMIT 1";
                $current_year_result = mysqli_query($con, $current_year_query);
                if ($current_year_result && mysqli_num_rows($current_year_result) > 0) {
                    $current_year_row = mysqli_fetch_assoc($current_year_result);
                    $academic_year = $current_year_row['school_year'];
                }
                
                // Since tblsemester doesn't exist, use a default semester
                if (empty($semester)) {
                    // Determine current month and set semester accordingly
                    $month = date('n');
                    if ($month >= 6 && $month <= 10) {
                        $semester = '1st Semester';
                    } else if ($month >= 11 || $month <= 3) {
                        $semester = '2nd Semester';
                    } else {
                        $semester = 'Summer';
                    }
                }
            }
            
            // Get academic performance data based on the actual database structure
            try {
                // First, let's check if the grades table exists and what columns it has
                $table_check = mysqli_query($con, "SHOW TABLES LIKE 'tblgrades'");
                
                if (mysqli_num_rows($table_check) > 0) {
                    // Table exists, get the column names
                    $columns_query = mysqli_query($con, "SHOW COLUMNS FROM tblgrades");
                    $grade_columns = [];
                    
                    if ($columns_query) {
                        while ($column = mysqli_fetch_assoc($columns_query)) {
                            $grade_columns[] = $column['Field'];
                        }
                        
                        // Store for debugging
                        $_SESSION['grade_columns'] = $grade_columns;
                        
                        // Get students without trying to calculate grades first
                        $query = "SELECT DISTINCT s.studentId, s.userId, s.sectionId, s.sex, 
                                 ua.firstName, ua.middleName, ua.lastName
                                 FROM tblstudentinfo s 
                                 JOIN tblusersaccount ua ON s.userId = ua.userId AND ua.role = 'student'
                                 JOIN tblsection sec ON s.sectionId = sec.sectionId
                                 JOIN tblteacherinfo t ON sec.sectionId = t.sectionId
                                 WHERE t.userId = '$teacher_id'
                                 ORDER BY ua.lastName ASC, ua.firstName ASC, ua.middleName ASC"; 
                        
                        $result = mysqli_query($con, $query);
                        
                        if ($result) {
                            // Process each student
                            while ($student = mysqli_fetch_assoc($result)) {
                                // Now get grades for this student, filtering by academic year and semester if provided
                                $grades_query = "SELECT * FROM tblgrades WHERE userId = '{$student['userId']}'";
                                
                                // Check if the grade columns array contains school_year and semester before adding them to the query
                                if (!empty($academic_year) && in_array('school_year', $grade_columns)) {
                                    $grades_query .= " AND school_year = '$academic_year'";
                                } else if (!empty($academic_year) && in_array('academicYear', $grade_columns)) {
                                    $grades_query .= " AND academicYear = '$academic_year'";
                                } else if (!empty($academic_year) && in_array('academic_year', $grade_columns)) {
                                    $grades_query .= " AND academic_year = '$academic_year'";
                                }
                                
                                if (!empty($semester) && in_array('semester', $grade_columns)) {
                                    $grades_query .= " AND semester = '$semester'";
                                }
                                
                                // Debug the query if needed
                                if(isset($_GET['debug'])) {
                                    $_SESSION['grades_query'] = $grades_query;
                                    $_SESSION['grade_columns'] = $grade_columns;
                                }
                                
                                $grades_result = mysqli_query($con, $grades_query);
                                
                                if ($grades_result && mysqli_num_rows($grades_result) > 0) {
                                    $total_grade = 0;
                                    $grade_count = 0;
                                    
                                    // Calculate average from all grade records
                                    while ($grade = mysqli_fetch_assoc($grades_result)) {
                                        // Look for any column that might contain grades
                                        foreach ($grade_columns as $column) {
                                            // Skip non-numeric or ID columns
                                            if (!in_array($column, ['userId', 'gradeId', 'subjectId', 'teacherId', 'sectionId', 'school_year', 'semester']) 
                                                && is_numeric($grade[$column])) {
                                                // Check if the grade is within a reasonable range (0-100)
                                                $grade_value = floatval($grade[$column]);
                                                if ($grade_value >= 60 && $grade_value <= 100) {
                                                    $total_grade += $grade_value;
                                                    $grade_count++;
                                                }
                                            }
                                        }
                                    }
                                    
                                    // Calculate average if we found any grades
                                    $student['average_grade'] = $grade_count > 0 ? round($total_grade / $grade_count, 2) : 0;

                                } else {
                                    // No grades found
                                    $student['average_grade'] = 0;
                                }
                                
                                $report_data[] = $student;
                            }
                            
                            // Add academic year and semester to report title if available
                            if (!empty($academic_year) && !empty($semester)) {
                                $report_title .= " ($academic_year, $semester)";
                            } elseif (!empty($academic_year)) {
                                $report_title .= " ($academic_year)";
                            } elseif (!empty($semester)) {
                                $report_title .= " ($semester)";
                            }
                        } else {
                            $_SESSION['message'] = "Error retrieving student data: " . mysqli_error($con);
                        }
                    } else {
                        $_SESSION['message'] = "Error retrieving grade table structure: " . mysqli_error($con);
                    }
                } else {
                    // Table doesn't exist
                    $_SESSION['message'] = "The grades table (tblgrades) does not exist in the database.";
                    
                    // Get students without grades
                    $query = "SELECT DISTINCT s.studentId, s.userId, s.sectionId, s.sex, 
                             ua.firstName, ua.middleName, ua.lastName,
                             0 as average_grade
                             FROM tblstudentinfo s 
                             JOIN tblusersaccount ua ON s.userId = ua.userId AND ua.role = 'student'
                             JOIN tblsection sec ON s.sectionId = sec.sectionId
                             JOIN tblteacherinfo t ON sec.sectionId = t.sectionId
                             WHERE t.userId = '$teacher_id'";
                    
                    $result = mysqli_query($con, $query);
                    if ($result) {
                        while($row = mysqli_fetch_assoc($result)) {
                            $report_data[] = $row;
                        }
                    }
                }
            } catch (Exception $e) {
                // If query fails, show error message
                $_SESSION['message'] = "Error retrieving grade data: " . $e->getMessage();
                
                // Try with a simple query as fallback
                try {
                    $fallback_query = "SELECT DISTINCT s.studentId, s.userId, s.sectionId, s.sex, 
                                     ua.firstName, ua.middleName, ua.lastName,
                                     0 as average_grade
                                     FROM tblstudentinfo s 
                                     JOIN tblusersaccount ua ON s.userId = ua.userId AND ua.role = 'student'
                                     JOIN tblsection sec ON s.sectionId = sec.sectionId
                                     JOIN tblteacherinfo t ON sec.sectionId = t.sectionId
                                     WHERE t.userId = '$teacher_id'";
                    $fallback_result = mysqli_query($con, $fallback_query);
                    if ($fallback_result) {
                        while($row = mysqli_fetch_assoc($fallback_result)) {
                            $report_data[] = $row;
                        }
                    }
                } catch (Exception $inner_e) {
                    // If even the fallback fails, just continue with empty data
                }
            }
            break;
            
        case 'attendance':
            $report_title = "Attendance Report";
            // Get attendance data based on the actual database structure
            try {
                // First, check what columns exist in the attendance table
                $attendance_columns_query = mysqli_query($con, "SHOW COLUMNS FROM tblattendance");
                $attendance_columns = [];
                
                if ($attendance_columns_query) {
                    while ($column = mysqli_fetch_assoc($attendance_columns_query)) {
                        $attendance_columns[] = $column['Field'];
                    }
                    
                    // Store for debugging
$_SESSION['attendance_columns'] = $attendance_columns;

// Determine the correct column name for attendance status
$attendance_column = 'attendance'; // Default column name

if (in_array('attendanceStatus', $attendance_columns)) {
    $attendance_column = 'attendanceStatus';
} elseif (in_array('attendance_status', $attendance_columns)) {
    $attendance_column = 'attendance_status';
}

// Build the query based on the actual column names
$query = "SELECT DISTINCT s.studentId, s.userId, s.sectionId, s.sex, 
                 ua.firstName, ua.middleName, ua.lastName,
                 COUNT(a.attendanceId) AS total_attendance";

// Only add the status counts if the attendance column exists
if (in_array($attendance_column, $attendance_columns)) {
    $query .= ", SUM(CASE WHEN a.$attendance_column = 'Present' THEN 1 ELSE 0 END) AS present_count,
               SUM(CASE WHEN a.$attendance_column = 'Absent' THEN 1 ELSE 0 END) AS absent_count,
               SUM(CASE WHEN a.$attendance_column = 'Late' THEN 1 ELSE 0 END) AS late_count";
} else {
    // If the column does not exist, set default values to 0
    $query .= ", 0 AS present_count, 0 AS absent_count, 0 AS late_count";
}
                    
$query = "SELECT DISTINCT s.studentId, s.userId, s.sectionId, s.sex, 
ua.firstName, ua.middleName, ua.lastName,
COUNT(DISTINCT DATE(a.classDate)) AS total_attendance,
COUNT(DISTINCT CASE WHEN a.$attendance_column = 'Present' THEN DATE(a.classDate) END) AS present_count,
COUNT(DISTINCT CASE WHEN a.$attendance_column = 'Absent' THEN DATE(a.classDate) END) AS absent_count,
COUNT(DISTINCT CASE WHEN a.$attendance_column = 'Late' THEN DATE(a.classDate) END) AS late_count
FROM tblstudentinfo s 
JOIN tblusersaccount ua ON s.userId = ua.userId AND ua.role = 'Student'
JOIN tblattendance a ON s.studentId = a.studentId
JOIN tblsection sec ON s.sectionId = sec.sectionId
JOIN tblteacherinfo t ON sec.sectionId = t.sectionId
WHERE t.userId = '$teacher_id'
GROUP BY s.studentId, s.userId, s.sectionId, s.sex, ua.firstName, ua.middleName, ua.lastName
ORDER BY ua.lastName ASC, ua.firstName ASC, ua.middleName ASC"; 

                    
                    $result = mysqli_query($con, $query);
                    if ($result) {
                        while($row = mysqli_fetch_assoc($result)) {
                            $report_data[] = $row;
                        }
                    } else {
                        $_SESSION['message'] = "Error retrieving attendance data: " . mysqli_error($con);
                    }
                } else {
                    $_SESSION['message'] = "Error retrieving attendance table structure: " . mysqli_error($con);
                }
            } catch (Exception $e) {
                // If query fails, show error message
                $_SESSION['message'] = "Error retrieving attendance data: " . $e->getMessage();
                
                // Try with a simple query as fallback
                try {
                    $fallback_query = "SELECT DISTINCT s.studentId, s.userId, s.sectionId, s.sex, 
                                     ua.firstName, ua.middleName, ua.lastName,
                                     0 as total_attendance, 0 as present_count, 0 as absent_count, 0 as late_count
                                     FROM tblstudentinfo s 
                                     JOIN tblusersaccount ua ON s.userId = ua.userId AND ua.role = 'Student'
                                     JOIN tblsection sec ON s.sectionId = sec.sectionId
                                     JOIN tblteacherinfo t ON sec.sectionId = t.sectionId
                                     WHERE t.userId = '$teacher_id'";
                    $fallback_result = mysqli_query($con, $fallback_query);
                    if ($fallback_result) {
                        while($row = mysqli_fetch_assoc($fallback_result)) {
                            $report_data[] = $row;
                        }
                    }
                } catch (Exception $inner_e) {
                    // If even the fallback fails, just continue with empty data
                }
            }
            break;
    }
}

// Get academic years for dropdown - adjust based on your database structure
$academic_years = [];
try {
    // Try to get academic years from your database
    $result = mysqli_query($con, "SELECT DISTINCT school_year as academic_year FROM tblschoolyear ORDER BY school_year DESC");
    if ($result) {
        while($row = mysqli_fetch_assoc($result)) {
            $academic_years[] = $row;
        }
    }
} catch (Exception $e) {
    // If table doesn't exist, use hardcoded values
    $academic_years = [
        ['academic_year' => '2023-2024'],
        ['academic_year' => '2022-2023'],
        ['academic_year' => '2021-2022']
    ];
}

// Get semesters for dropdown - use hardcoded values since tblsemester doesn't exist
$semesters = [
    ['semester' => '1st Semester'],
    ['semester' => '2nd Semester'],
    ['semester' => 'Summer']
];

// Include header and sidebar
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Create Report</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item active">Generate Detailed Reports</li>
            </ol>
        </nav>
        </div>

    <section class="section">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                        <h4>Report Parameters</h4>
                    </div>
                    <div class="card-body">
                        <?php if(isset($_SESSION['message'])): ?>
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <strong>Note:</strong> <?= $_SESSION['message']; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php unset($_SESSION['message']); ?>
                        <?php endif; ?>
                        
                                <form method="POST" action="">
                            <div class="form-group mb-3">
                                        <label for="report_type">Report Type</label>
                                        <select class="form-control" id="report_type" name="report_type" required>
                                            <option value="">Select Report Type</option>
                                            <option value="student_list">Student List</option>
                                            <option value="academic_performance">Academic Performance</option>
                                            <option value="attendance">Attendance Report</option>
                                        </select>
                                    </div>
                            <div class="form-group mb-3">
                                        <label for="academic_year">Academic Year</label>
                                <select class="form-control" id="academic_year" name="academic_year">
                                            <option value="">Select Academic Year</option>
                                            <?php foreach ($academic_years as $year): ?>
                                                <option value="<?php echo $year['academic_year']; ?>"><?php echo $year['academic_year']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                            <div class="form-group mb-3">
                                        <label for="semester">Semester</label>
                                <select class="form-control" id="semester" name="semester">
                                            <option value="">Select Semester</option>
                                            <?php foreach ($semesters as $sem): ?>
                                                <option value="<?php echo $sem['semester']; ?>"><?php echo $sem['semester']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="submit" name="generate" class="btn btn-primary">Generate Report</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($show_report): ?>
        <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                        <h4><?php echo $report_title; ?></h4>
                        <div class="float-end">
                                    <button type="button" class="btn btn-success btn-sm" onclick="printReport()">
                                        <i class="fas fa-print"></i> Print Report
                                    </button>
                                </div>
                            </div>
                            <div class="card-body" id="report-content">
                                <div class="report-container">
                            <div class="text-center mb-4">
                                    <div class="report-header">
                                    <div class="header-with-logo">
                                        <div class="logo-container">
                                            <!-- Left logo placeholder -->
                                            <div class="logo logo-left"></div>
                                            
                                            <!-- Header text -->
                                            <div class="header-text-container">
                                                <p class="header-text">Republic of the Philippines</p>
                                                <p class="header-text"><strong>DEPARTMENT OF EDUCATION</strong></p>
                                                <p class="header-text">Region I</p>
                                                <p class="header-text">Schools Division of Ilocos Sur</p>
                                                <p class="header-text"><strong>BANTAY NATIONAL HIGH SCHOOL</strong></p>
                                                <p class="header-text">Bulag, Bantay, Ilocos Sur</p>
                                                <p class="header-text">SENIOR HIGH SCHOOL</p>
                                            </div>
                                            
                                            <!-- Right logo placeholder -->
                                            <div class="logo logo-right"></div>
                                        </div>
                                    </div>
                                </div>
                                <hr class="report-divider">
                                <h2 class="report-title"><?php echo $report_title; ?></h2>
                                <?php if(!empty($academic_year) && !empty($semester)): ?>
                                <p class="report-subtitle">Academic Year: <?php echo $academic_year; ?> | Semester: <?php echo $semester; ?></p>
                                <?php endif; ?>
                                <p class="report-info">Adviser: <?php echo (isset($teacher_data['firstName']) && isset($teacher_data['lastName'])) ? $teacher_data['firstName'] . ' ' . $teacher_data['lastName'] : 'Teacher'; ?></p>
                                <p class="report-info">Date Generated: <?php echo date('F d, Y'); ?></p>
                                <hr class="report-divider">
                            </div>
                            
                            <?php if (empty($report_data)): ?>
                                <div class="alert alert-info">
                                    No data available for this report. Please check your database or try a different report type.
                                </div>
                            <?php elseif ($report_type == 'student_list'): ?>
                                <?php if(isset($_GET['debug'])): ?>
                                <div class="alert alert-info">
                                    <h5>Debug Information (First Student Record):</h5>
                                    <pre><?php print_r(!empty($report_data) ? $report_data[0] : 'No data'); ?></pre>
                                </div>
                                <?php endif; ?>
                                
                                <?php
                                // Group students by gender
                                $male_students = [];
                                $female_students = [];
                                
                                // Debug information
                                if(isset($_GET['debug'])) {
                                    echo '<div class="alert alert-info">';
                                    echo '<p>Total students: ' . count($report_data) . '</p>';
                                    echo '<p>First student data:</p>';
                                    echo '<pre>';
                                    if(!empty($report_data)) {
                                        print_r($report_data[0]);
                                    } else {
                                        echo 'No student data available';
                                    }
                                    echo '</pre>';
                                    echo '</div>';
                                }
                                
                                foreach ($report_data as $student) {
                                    if (isset($student['sex']) && strtolower($student['sex']) == 'male') {
                                        $male_students[] = $student;
                                    } else {
                                        $female_students[] = $student;
                                    }
                                }
                                
                                // Sort students by last name within each gender group
                                usort($male_students, function($a, $b) {
                                    return isset($a['lastName']) && isset($b['lastName']) ? 
                                        strcmp($a['lastName'], $b['lastName']) : 0;
                                });
                                
                                usort($female_students, function($a, $b) {
                                    return isset($a['lastName']) && isset($b['lastName']) ? 
                                        strcmp($a['lastName'], $b['lastName']) : 0;
                                });
                                ?>
                                
                                <!-- Male Students -->
                                <h4 class="mt-4">Male Students</h4>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="15%">Student ID</th>
                                            <th width="35%">Name</th>
                                            <th width="25%">Section</th>
                                            <th width="20%">Grade Level</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($male_students)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No male students found</td>
                                        </tr>
                                        <?php else: ?>
                                            <?php $count = 1; foreach ($male_students as $student): ?>
                                            <tr>
                                                <td><?php echo $count++; ?></td>
                                                <td><?php echo $student['studentId']; ?></td>
                                                <td>
                                                    <?php 
                                                    $fullName = '';
                                                    if (isset($student['lastName'])) {
                                                        $fullName .= $student['lastName'];
                                                        if (isset($student['firstName'])) {
                                                            $fullName .= ', ' . $student['firstName'];
                                                            if (isset($student['middleName']) && !empty($student['middleName'])) {
                                                                $fullName .= ' ' . substr($student['middleName'], 0, 1) . '.';
                                                            }
                                                        }
                                                    } else {
                                                        $fullName = 'N/A';
                                                    }
                                                    echo $fullName;
                                                    ?>
                                                </td>
                                                <td><?php echo isset($student['section']) ? $student['section'] : 'N/A'; ?></td>
                                                <td><?php echo isset($student['grade_level']) ? $student['grade_level'] : 'N/A'; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                                
                                <!-- Female Students -->
                                <h4 class="mt-4">Female Students</h4>
                                <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                            <th width="5%">#</th>
                                            <th width="15%">Student ID</th>
                                            <th width="35%">Name</th>
                                            <th width="25%">Section</th>
                                            <th width="20%">Grade Level</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                        <?php if (empty($female_students)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No female students found</td>
                                        </tr>
                                        <?php else: ?>
                                            <?php $count = 1; foreach ($female_students as $student): ?>
                                            <tr>
                                                <td><?php echo $count++; ?></td>
                                                <td><?php echo $student['studentId']; ?></td>
                                                <td>
                                                    <?php 
                                                    $fullName = '';
                                                    if (isset($student['lastName'])) {
                                                        $fullName .= $student['lastName'];
                                                        if (isset($student['firstName'])) {
                                                            $fullName .= ', ' . $student['firstName'];
                                                            if (isset($student['middleName']) && !empty($student['middleName'])) {
                                                                $fullName .= ' ' . substr($student['middleName'], 0, 1) . '.';
                                                            }
                                                        }
                                                    } else {
                                                        $fullName = 'N/A';
                                                    }
                                                    echo $fullName;
                                                    ?>
                                                </td>
                                                <td><?php echo isset($student['section']) ? $student['section'] : 'N/A'; ?></td>
                                                <td><?php echo isset($student['grade_level']) ? $student['grade_level'] : 'N/A'; ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                        <?php endif; ?>
                                            </tbody>
                                        </table>
                                
                                <!-- Summary -->
                                <div class="mt-3">
                                    <p><strong>Total Students:</strong> <?php echo count($report_data); ?></p>
                                    <p><strong>Male:</strong> <?php echo count($male_students); ?></p>
                                    <p><strong>Female:</strong> <?php echo count($female_students); ?></p>
                                </div>
                                    <?php elseif ($report_type == 'academic_performance'): ?>
                                        <table class="table table-bordered">
    <thead>
        <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Average Grade</th>
            <th>Status</th>
            <th>Academic Award</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($report_data as $student): ?>
        <tr>
            <td><?php echo $student['studentId']; ?></td>
            <td>
                <?php 
                $fullName = '';
                if (isset($student['lastName'])) {
                    $fullName .= $student['lastName'];
                    if (isset($student['firstName'])) {
                        $fullName .= ', ' . $student['firstName'];
                        if (isset($student['middleName']) && !empty($student['middleName'])) {
                            $fullName .= ' ' . substr($student['middleName'], 0, 1) . '.';
                        }
                    }
                } else {
                    $fullName = 'N/A';
                }
                echo $fullName;
                ?>
            </td>
            <td><?php echo number_format(round($student['average_grade'], 2), 2); ?></td>
            <td>
                <?php 
                $avg = $student['average_grade'];
                if ($avg >= 89.50) echo "Outstanding";
                elseif ($avg >= 84.50) echo "Very Satisfactory";
                elseif ($avg >= 79.50) echo "Satisfactory";
                elseif ($avg >= 74.50) echo "Fairly Satisfactory";
                else echo "Did Not Meet Expectations";
                ?>
            </td>
            <td>
                <?php 
                if ($avg >= 97.5) echo "With Highest Honors";
                elseif ($avg >= 94.5) echo "With High Honors";
                elseif ($avg >= 89.5) echo "With Honors";
                else echo "None";
                ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

                                    <?php elseif ($report_type == 'attendance'): ?>
                                <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Student ID</th>
                                                    <th>Name</th>
                                                    <th>Total Days</th>
                                                    <th>Present</th>
                                                    <th>Absent</th>
                                                    <th>Late</th>
                                                    <th>Attendance Rate</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($report_data as $student): ?>
                                                <tr>
                                            <td><?php echo $student['studentId']; ?></td>
                                            <td>
                                                <?php 
                                                $fullName = '';
                                                if (isset($student['lastName'])) {
                                                    $fullName .= $student['lastName'];
                                                    if (isset($student['firstName'])) {
                                                        $fullName .= ', ' . $student['firstName'];
                                                        if (isset($student['middleName']) && !empty($student['middleName'])) {
                                                            $fullName .= ' ' . substr($student['middleName'], 0, 1) . '.';
                                                        }
                                                    }
                                                } else {
                                                    $fullName = 'N/A';
                                                }
                                                echo $fullName;
                                                ?>
                                            </td>
                                            <td><?php echo isset($student['total_attendance']) ? $student['total_attendance'] : '0'; ?></td>
                                            <td><?php echo isset($student['present_count']) ? $student['present_count'] : '0'; ?></td>
                                            <td><?php echo isset($student['absent_count']) ? $student['absent_count'] : '0'; ?></td>
                                            <td><?php echo isset($student['late_count']) ? $student['late_count'] : '0'; ?></td>
                                                    <td>
                                                        <?php 
                                                $rate = (isset($student['total_attendance']) && $student['total_attendance'] > 0) 
                                                    ? ((isset($student['present_count']) ? $student['present_count'] : 0) / $student['total_attendance'] * 100) 
                                                            : 0;
                                                        echo number_format($rate, 2) . '%';
                                                        ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php endif; ?>
                                    
                                    <div class="row mt-5">
                                        <div class="col-md-6">
                                            <p>Prepared by:</p>
                                    <p class="mt-4"><strong><?php echo (isset($teacher_data['firstName']) && isset($teacher_data['lastName'])) ? $teacher_data['firstName'] . ' ' . $teacher_data['lastName'] : 'Teacher'; ?></strong></p>
                                    <p>Adviser</p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        </section>
</main>

<?php
include 'includes/footer.php';
?>

    <script>
        function printReport() {
        // Create a new window for printing
        var printWindow = window.open('', '_blank', 'height=600,width=800');
        
        // Get the report content
        var reportContent = document.getElementById('report-content').innerHTML;
        var pageTitle = document.title || 'Student Report';
        
        // Get base URL for images
        var baseUrl = window.location.origin + '/Student_Portal-Latest';
        
        // Create the print document with proper styling
        printWindow.document.write('<!DOCTYPE html>');
        printWindow.document.write('<html lang="en">');
        printWindow.document.write('<head>');
        printWindow.document.write('<meta charset="UTF-8">');
        printWindow.document.write('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
        printWindow.document.write('<title>' + pageTitle + '</title>');
        printWindow.document.write('<style>');
        printWindow.document.write('body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 20px; }');
        printWindow.document.write('.report-container { max-width: 100%; margin: 0 auto; }');
        printWindow.document.write('.text-center { text-align: center; }');
        printWindow.document.write('.report-header { margin-bottom: 20px; }');
        printWindow.document.write('.header-with-logo { display: flex; justify-content: center; }');
        printWindow.document.write('.logo-container { display: flex; align-items: flex-start; width: 100%; max-width: 650px; margin: 0 auto; }');
        printWindow.document.write('.header-text-container { text-align: center; flex: 1; padding: 0 10px; }');
        printWindow.document.write('.logo { width: 70px; height: 70px; object-fit: contain; }');
        printWindow.document.write('.header-text { margin: 0; padding: 2px; line-height: 1.3; }');
        printWindow.document.write('.report-title { font-size: 24px; font-weight: bold; margin-bottom: 10px; margin-top: 15px; }');
        printWindow.document.write('.report-subtitle { font-size: 16px; margin-bottom: 5px; }');
        printWindow.document.write('.report-info { font-size: 14px; margin-bottom: 5px; }');
        printWindow.document.write('.report-divider { border-top: 1px solid #ddd; margin: 15px 0; }');
        printWindow.document.write('h4 { margin-top: 20px; margin-bottom: 10px; font-size: 18px; font-weight: bold; }');
        printWindow.document.write('table { width: 100%; border-collapse: collapse; margin: 10px 0 20px 0; }');
        printWindow.document.write('th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }');
        printWindow.document.write('th { background-color: #f2f2f2; font-weight: bold; }');
        printWindow.document.write('tr:nth-child(even) { background-color: #f9f9f9; }');
        printWindow.document.write('.row { display: flex; margin: 20px 0; }');
        printWindow.document.write('.col-md-6 { width: 50%; }');
        printWindow.document.write('.text-end { text-align: right; }');
        printWindow.document.write('.mt-3 { margin-top: 15px; }');
        printWindow.document.write('.mt-4 { margin-top: 20px; }');
        printWindow.document.write('.mt-5 { margin-top: 30px; }');
        printWindow.document.write('.alert { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; }');
        printWindow.document.write('.alert-info { color: #31708f; background-color: #d9edf7; border-color: #bce8f1; }');
        printWindow.document.write('@media print { body { margin: 0; padding: 15px; } .report-container { width: 100%; } @page { size: portrait; margin: 0.5cm; } }');
        printWindow.document.write('</style>');
        printWindow.document.write('</head>');
        printWindow.document.write('<body>');
        printWindow.document.write('<div class="report-container">');
        
        // Create a custom header with logos
        printWindow.document.write('<div class="report-header">');
        printWindow.document.write('<div class="header-with-logo">');
        printWindow.document.write('<div class="logo-container">');
        printWindow.document.write('<img src="' + baseUrl + '/assets/images/deped.png" class="logo logo-left" alt="DepEd Logo">');
        printWindow.document.write('<div class="header-text-container">');
        printWindow.document.write('<p class="header-text">Republic of the Philippines</p>');
        printWindow.document.write('<p class="header-text"><strong>DEPARTMENT OF EDUCATION</strong></p>');
        printWindow.document.write('<p class="header-text">Region I</p>');
        printWindow.document.write('<p class="header-text">Schools Division of Ilocos Sur</p>');
        printWindow.document.write('<p class="header-text"><strong>BANTAY NATIONAL HIGH SCHOOL</strong></p>');
        printWindow.document.write('<p class="header-text">Bulag, Bantay, Ilocos Sur</p>');
        printWindow.document.write('<p class="header-text">SENIOR HIGH SCHOOL</p>');
        printWindow.document.write('</div>');
        printWindow.document.write('<img src="' + baseUrl + '/assets/images/Bulag_logo.jpg" class="logo logo-right" alt="School Logo">');
        printWindow.document.write('</div>');
        printWindow.document.write('</div>');
        printWindow.document.write('</div>');
        printWindow.document.write('<hr class="report-divider">');
        
        // Get the content without the header (we're replacing it with our custom one)
        var contentWithoutHeader = reportContent.substring(reportContent.indexOf('<hr class="report-divider">') + 30);
        printWindow.document.write(contentWithoutHeader);
        
        printWindow.document.write('</div>');
        printWindow.document.write('<script>');
        printWindow.document.write('window.onload = function() { window.print(); setTimeout(function() { window.close(); }, 500); };');
        printWindow.document.write('<\/script>');
        printWindow.document.write('</body>');
        printWindow.document.write('</html>');
        
        printWindow.document.close();
        }
    </script>