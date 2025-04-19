<?php
session_start();
include '../config/dbcon.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['superAdminId'])) {
    $_SESSION['message'] = "You are not authorized to access this page";
    header("Location: ../login.php");
    exit(0);
}

// Get user role
$user_id = $_SESSION['superAdminId'];
$role_query = mysqli_query($con, "SELECT role FROM tblsuperadmin WHERE superAdminId = '$user_id'");
$user_role = mysqli_fetch_assoc($role_query)['role'];

// Only allow admin access
if ($user_role != 'Admin') {
    $_SESSION['message'] = "You are not authorized as an admin";
    header("Location: ../login.php");
    exit(0);
}

// Get admin details
$stmt = mysqli_query($con, "SELECT * FROM tblsuperadmin WHERE superAdminId = '$user_id'");
$admin_data = mysqli_fetch_assoc($stmt);

// Process report generation
$report_data = [];
$report_title = "";
$show_report = false;
$selected_report_type = isset($_POST['report_type']) ? $_POST['report_type'] : '';
$selected_section = isset($_POST['section']) ? $_POST['section'] : '';
$selected_academic_year = isset($_POST['academic_year']) ? $_POST['academic_year'] : '';
$selected_semester = isset($_POST['semester']) ? $_POST['semester'] : '';

if (isset($_POST['generate'])) {
    $selected_report_type = $_POST['report_type'];
    $selected_section = $_POST['section'];
    $selected_academic_year = isset($_POST['academic_year']) ? $_POST['academic_year'] : '';
    $selected_semester = isset($_POST['semester']) ? $_POST['semester'] : '';
    $show_report = true;
    
    // Set report title based on type
    switch ($selected_report_type) {
        case 'student_list':
            $report_title = "Student List Report";
            // Get students for selected section
            try {
                $query = "SELECT s.studentId, s.userId, s.sectionId, s.sex, 
                         ua.firstName, ua.middleName, ua.lastName, 
                         sec.sectionName as section, sec.gradeLevel as grade_level
                         FROM tblstudentinfo s 
                         JOIN tblusersaccount ua ON s.userId = ua.userId
                         LEFT JOIN tblsection sec ON s.sectionId = sec.sectionId
                         WHERE s.sectionId = '$selected_section'
                         ORDER BY ua.lastName, ua.firstName, ua.middleName";
                $result = mysqli_query($con, $query);
                if ($result) {
                    while($row = mysqli_fetch_assoc($result)) {
                        $report_data[] = $row;
                    }
                } else {
                    $_SESSION['message'] = "Error retrieving student data: " . mysqli_error($con);
                }
            } catch (Exception $e) {
                $_SESSION['message'] = "Error retrieving student data: " . $e->getMessage();
            }
            break;
            
        case 'academic_performance':
            $report_title = "Academic Performance Report";
            
            // Get academic performance data for selected section
            try {
                $query = "SELECT s.studentId, s.userId, s.sex, 
                         ua.firstName, ua.middleName, ua.lastName,
                         g.quarter1_grade, g.quarter2_grade,
                         ((IFNULL(g.quarter1_grade, 0) + IFNULL(g.quarter2_grade, 0)) / 2) as average_grade
                         FROM tblstudentinfo s 
                         JOIN tblusersaccount ua ON s.userId = ua.userId
                         LEFT JOIN tblgrades g ON s.userId = g.userId
                         WHERE s.sectionId = '$selected_section'";
                
                if (!empty($selected_academic_year)) {
                    $query .= " AND g.syId = (SELECT syId FROM tblschoolyear WHERE school_year = '$selected_academic_year' LIMIT 1)";
                }
                
                if (!empty($selected_semester)) {
                    $query .= " AND g.semester = '$selected_semester'";
                }
                
                $query .= " GROUP BY s.studentId, s.userId, s.sex, ua.firstName, ua.middleName, ua.lastName
                    ORDER BY ua.lastName, ua.firstName, ua.middleName";
        
        $result = mysqli_query($con, $query);

                $result = mysqli_query($con, $query);
                
                if ($result) {
                    while($row = mysqli_fetch_assoc($result)) {
                        // Calculate average grade if not already calculated in query
                        if (!isset($row['average_grade'])) {
                            $q1 = floatval($row['quarter1_grade']);
                            $q2 = floatval($row['quarter2_grade']);
                            $row['average_grade'] = ($q1 + $q2) / 2;
                        }
                        $report_data[] = $row;
                    }
                    
                    // Add academic year and semester to report title if available
                    if (!empty($selected_academic_year)) $report_title .= " ($selected_academic_year)";
                    if (!empty($selected_semester)) $report_title .= " - $selected_semester";
                } else {
                    $_SESSION['message'] = "Error retrieving grade data: " . mysqli_error($con);
                }
            } catch (Exception $e) {
                $_SESSION['message'] = "Error retrieving grade data: " . $e->getMessage();
            }
            break;
            
        case 'attendance':
            $report_title = "Attendance Report";
            // Get attendance data for selected section
            try {
                $query = "SELECT s.studentId, s.userId, s.sex, 
                         ua.firstName, ua.middleName, ua.lastName,
                         COUNT(a.attendanceId) AS total_attendance,
                         SUM(CASE WHEN a.attendance = 'Present' THEN 1 ELSE 0 END) AS present_count,
                         SUM(CASE WHEN a.attendance = 'Absent' THEN 1 ELSE 0 END) AS absent_count,
                         SUM(CASE WHEN a.attendance = 'Late' THEN 1 ELSE 0 END) AS late_count
                         FROM tblstudentinfo s 
                         JOIN tblusersaccount ua ON s.userId = ua.userId
                         LEFT JOIN tblattendance a ON s.studentId = a.studentId
                         WHERE s.sectionId = '$selected_section'";
                
                
                
                $query .= " GROUP BY s.studentId, s.userId, s.sex, ua.firstName, ua.middleName, ua.lastName
                            ORDER BY ua.lastName, ua.firstName, ua.middleName";
                
                $result = mysqli_query($con, $query);
                if ($result) {
                    while($row = mysqli_fetch_assoc($result)) {
                        $report_data[] = $row;
                    }
                } else {
                    $_SESSION['message'] = "Error retrieving attendance data: " . mysqli_error($con);
                }
            } catch (Exception $e) {
                $_SESSION['message'] = "Error retrieving attendance data: " . $e->getMessage();
            }
            break;
    }
}

// Get sections for dropdown
$sections = [];
try {
    $result = mysqli_query($con, "SELECT sectionId, sectionName, gradeLevel FROM tblsection ORDER BY gradeLevel, sectionName");
    if ($result) {
        while($row = mysqli_fetch_assoc($result)) {
            $sections[] = $row;
        }
    }
} catch (Exception $e) {
    $_SESSION['message'] = "Error retrieving sections: " . $e->getMessage();
}

// Get academic years for dropdown
$academic_years = [];
try {
    $result = mysqli_query($con, "SELECT DISTINCT school_year FROM tblschoolyear ORDER BY school_year DESC");
    if ($result) {
        while($row = mysqli_fetch_assoc($result)) {
            $academic_years[] = $row;
        }
    }
} catch (Exception $e) {
    $academic_years = [
        ['school_year' => '2023-2024'],
        ['school_year' => '2022-2023'],
        ['school_year' => '2021-2022']
    ];
}

// Get semesters for dropdown
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
                <li class="breadcrumb-item"><a href="dashboard.php"  style="text-decoration: none;">Home</a></li>
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
                            <!-- Step 1: Select Report Type -->
                            <div class="form-group mb-3">
                                <label for="report_type">Report Type</label>
                                <select class="form-control" id="report_type" name="report_type" required onchange="updateFormFields()">
                                    <option value="" disabled selected>Select Report Type</option>
                                    <option value="student_list" <?= $selected_report_type == 'student_list' ? 'selected' : '' ?>>Student List</option>
                                    <option value="academic_performance" <?= $selected_report_type == 'academic_performance' ? 'selected' : '' ?>>Academic Performance</option>
                                    <option value="attendance" <?= $selected_report_type == 'attendance' ? 'selected' : '' ?>>Attendance Report</option>
                                </select>
                            </div>
                            
                            <!-- Step 2: Select Section -->
                            <div class="form-group mb-3">
                                <label for="section">Section</label>
                                <select class="form-control" id="section" name="section" required>
                                    <option value="" disabled selected>Select Section</option>
                                    <?php foreach ($sections as $section): ?>
                                        <option value="<?= $section['sectionId'] ?>" <?= $selected_section == $section['sectionId'] ? 'selected' : '' ?>>
                                            Grade <?= $section['gradeLevel'] ?> - <?= $section['sectionName'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Step 3: Select Academic Year (shown for certain report types) -->
                            <div class="form-group mb-3" id="academic_year_field" style="display: none;">
                                <label for="academic_year">Academic Year</label>
                                <select class="form-control" id="academic_year" name="academic_year">
                                    <option value="" disabled selected>Select Academic Year</option>
                                    <?php foreach ($academic_years as $year): ?>
                                        <option value="<?= $year['school_year'] ?>" <?= $selected_academic_year == $year['school_year'] ? 'selected' : '' ?>>
                                            <?= $year['school_year'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Step 4: Select Semester (shown for certain report types) -->
                            <div class="form-group mb-3" id="semester_field" style="display: none;">
                                <label for="semester">Semester</label>
                                <select class="form-control" id="semester" name="semester">
                                    <option value="" disabled selected>Select Semester</option>
                                    <?php foreach ($semesters as $sem): ?>
                                        <option value="<?= $sem['semester'] ?>" <?= $selected_semester == $sem['semester'] ? 'selected' : '' ?>>
                                            <?= $sem['semester'] ?>
                                        </option>
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
                        <h4><?= $report_title ?></h4>
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
                                <h2 class="report-title"><?= $report_title ?></h2>
                                <?php 
                                // Get section info for report header
                                $section_info = [];
                                if (!empty($selected_section)) {
                                    $section_query = mysqli_query($con, "SELECT sectionName, gradeLevel FROM tblsection WHERE sectionId = '$selected_section'");
                                    $section_info = mysqli_fetch_assoc($section_query);
                                }
                                ?>
                                <p class="report-subtitle">
                                    <?php if (!empty($section_info)): ?>
                                        Grade <?= $section_info['gradeLevel'] ?> - <?= $section_info['sectionName'] ?>
                                    <?php endif; ?>
                                    <?php if (!empty($selected_academic_year)): ?>
                                        | Academic Year: <?= $selected_academic_year ?>
                                    <?php endif; ?>
                                    <?php if (!empty($selected_semester)): ?>
                                        | Semester: <?= $selected_semester ?>
                                    <?php endif; ?>
                                </p>
                                <p class="report-info">Generated by: Administrator</p>
                                <p class="report-info">Date Generated: <?= date('F d, Y') ?></p>
                                <hr class="report-divider">
                            </div>
                            
                            <?php if (empty($report_data)): ?>
                                <div class="alert alert-info">
                                    No data available for this report.
                                </div>
                            <?php elseif ($selected_report_type == 'student_list'): ?>
                                <?php
                                // Group students by gender
                                $male_students = [];
                                $female_students = [];
                                
                                foreach ($report_data as $student) {
                                    if (isset($student['sex']) && strtolower($student['sex']) == 'male') {
                                        $male_students[] = $student;
                                    } else {
                                        $female_students[] = $student;
                                    }
                                }
                                
                                // Sort students by last name
                                usort($male_students, function($a, $b) {
                                    return strcmp($a['lastName'], $b['lastName']);
                                });
                                
                                usort($female_students, function($a, $b) {
                                    return strcmp($a['lastName'], $b['lastName']);
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
                                                <td><?= $count++ ?></td>
                                                <td><?= $student['studentId'] ?></td>
                                                <td>
                                                    <?php 
                                                    $fullName = $student['lastName'] . ', ' . $student['firstName'];
                                                    if (!empty($student['middleName'])) {
                                                        $fullName .= ' ' . substr($student['middleName'], 0, 1) . '.';
                                                    }
                                                    echo $fullName;
                                                    ?>
                                                </td>
                                                <td><?= isset($student['section']) ? $student['section'] : 'N/A' ?></td>
                                                <td><?= isset($student['grade_level']) ? $student['grade_level'] : 'N/A' ?></td>
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
                                                <td><?= $count++ ?></td>
                                                <td><?= $student['studentId'] ?></td>
                                                <td>
                                                    <?php 
                                                    $fullName = $student['lastName'] . ', ' . $student['firstName'];
                                                    if (!empty($student['middleName'])) {
                                                        $fullName .= ' ' . substr($student['middleName'], 0, 1) . '.';
                                                    }
                                                    echo $fullName;
                                                    ?>
                                                </td>
                                                <td><?= isset($student['section']) ? $student['section'] : 'N/A' ?></td>
                                                <td><?= isset($student['grade_level']) ? $student['grade_level'] : 'N/A' ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                                
                                <!-- Summary -->
                                <div class="mt-3">
                                    <p><strong>Total Students:</strong> <?= count($report_data) ?></p>
                                    <p><strong>Male:</strong> <?= count($male_students) ?></p>
                                    <p><strong>Female:</strong> <?= count($female_students) ?></p>
                                </div>
                                
                            <?php elseif ($selected_report_type == 'academic_performance'): ?>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Student ID</th>
                                            <th>Name</th>
                                            <th>Q1 Grade</th>
                                            <th>Q2 Grade</th>
                                            <th>Average</th>
                                            <th>Status</th>
                                            <th>Academic Award</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($report_data as $student): ?>
                                        <tr>
                                            <td><?= $student['studentId'] ?></td>
                                            <td>
                                                <?php 
                                                $fullName = $student['lastName'] . ', ' . $student['firstName'];
                                                if (!empty($student['middleName'])) {
                                                    $fullName .= ' ' . substr($student['middleName'], 0, 1) . '.';
                                                }
                                                echo $fullName;
                                                ?>
                                            </td>
                                            <td><?= isset($student['quarter1_grade']) ? number_format($student['quarter1_grade'], 2) : 'N/A' ?></td>
                                            <td><?= isset($student['quarter2_grade']) ? number_format($student['quarter2_grade'], 2) : 'N/A' ?></td>
                                            <td><?= number_format($student['average_grade'], 2) ?></td>
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
                                
                            <?php elseif ($selected_report_type == 'attendance'): ?>
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
                                            <td><?= $student['studentId'] ?></td>
                                            <td>
                                                <?php 
                                                $fullName = $student['lastName'] . ', ' . $student['firstName'];
                                                if (!empty($student['middleName'])) {
                                                    $fullName .= ' ' . substr($student['middleName'], 0, 1) . '.';
                                                }
                                                echo $fullName;
                                                ?>
                                            </td>
                                            <td><?= $student['total_attendance'] ?></td>
                                            <td><?= $student['present_count'] ?></td>
                                            <td><?= $student['absent_count'] ?></td>
                                            <td><?= $student['late_count'] ?></td>
                                            <td>
                                                <?php 
                                                $rate = ($student['total_attendance'] > 0) 
                                                    ? ($student['present_count'] / $student['total_attendance'] * 100) 
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
                                    <p>Administrator</p>
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


<script>
    // Function to show/hide fields based on report type
    function updateFormFields() {
        const reportType = document.getElementById('report_type').value;
        const academicYearField = document.getElementById('academic_year_field');
        const semesterField = document.getElementById('semester_field');
        
        // Show academic year and semester fields for performance and attendance reports
        if (reportType === 'academic_performance' || reportType === 'attendance') {
            academicYearField.style.display = 'block';
            semesterField.style.display = 'block';
            
            // Make academic year and semester required for these reports
            document.getElementById('academic_year').setAttribute('required', 'required');
            document.getElementById('semester').setAttribute('required', 'required');
        } else {
            academicYearField.style.display = 'none';
            semesterField.style.display = 'none';
            
            // Remove required attribute for student list
            document.getElementById('academic_year').removeAttribute('required');
            document.getElementById('semester').removeAttribute('required');
        }
    }
    
    // Initialize form fields on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateFormFields();
    });
    
    function printReport() {
        var printWindow = window.open('', '_blank', 'height=600,width=800');
        var reportContent = document.getElementById('report-content').innerHTML;
        var pageTitle = document.title || 'Student Report';
        var baseUrl = window.location.origin + '/Student_Portal-Latest';
        
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
        
        // Custom header
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