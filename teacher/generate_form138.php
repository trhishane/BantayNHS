<?php
session_start();
include('../includes/dbconn.php');
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../vendor/PDFMerger/PDFMerger.php';
use Mpdf\Mpdf;

if (!isset($_SESSION['userId'])) {
    die('Unauthorized access');
}

$userId = $_SESSION['userId'];

// First check if teacher is an adviser
$sql = "SELECT isAdviser, sectionId FROM tblteacherinfo WHERE userId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$teacherInfo = $result->fetch_assoc();

if (!$teacherInfo || $teacherInfo['isAdviser'] != 1) {
    die('Unauthorized access - Not an adviser');
}

// Check if student_id is provided
$student_id = $_GET['student_id'] ?? '';

if (empty($student_id)) {
    die('Student ID is required');
}

// Function to generate Form 138 for a single student
function generateStudentForm138($student_id, $conn, $outputMode = 'I') {
    // Get student information
    $sql = "SELECT s.*, 
            CONCAT(ua.firstName, ' ', ua.lastName) as adviserName,
            ua2.firstName as studentFirstName,
            ua2.lastName as studentLastName,
            ua2.middleName as studentMiddleName,
            s.studentId as studentLRN,
            sec.sectionName, sec.gradeLevel, sec.strand
            FROM tblstudentinfo s
            JOIN tblteacherinfo t ON t.sectionId = s.sectionId
            JOIN tblusersaccount ua ON ua.userId = t.userId
            JOIN tblusersaccount ua2 ON ua2.userId = s.userId
            JOIN tblsection sec ON sec.sectionId = s.sectionId
            WHERE s.studentId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();

    // Add error checking
    if (!$student) {
        die('Student not found');
    }

    // Debug line to check available fields
    error_log("Student data: " . print_r($student, true));

    // Check if LRN exists
    if (empty($student['studentLRN'])) {
        // If LRN is missing, display placeholder or handle accordingly
        $student['studentLRN'] = 'Not Available';
    }

    // Get student's grades for first semester
    $sql = "SELECT s.subjectName, g.quarter1_grade, g.quarter2_grade, 
            ROUND((g.quarter1_grade + g.quarter2_grade) / 2) as final_grade,
            CASE 
                WHEN s.subjectType = 'Applied' THEN 'Applied and Specialized'
                ELSE s.subjectType 
            END as subjectType
            FROM tblgrades g
            JOIN tblsubject s ON s.subjectId = g.subjectId
            JOIN tblusersaccount ua ON ua.userId = g.userId
            WHERE g.userId = ? 
            AND (s.semester = '1st Semester' OR s.semester = 1)
            AND ua.role = 'Student'
            AND (g.quarter1_grade IS NOT NULL OR g.quarter2_grade IS NOT NULL)
            ORDER BY 
                CASE 
                    WHEN s.subjectType = 'Core' THEN 1
                    WHEN s.subjectType = 'Applied' THEN 2
                    ELSE 3
                END,
                s.subjectName";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student['userId']);
    $stmt->execute();
    $firstSemGrades = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Debug output for first semester
    error_log("First Semester Query: " . $sql);
    error_log("Student userId: " . $student['userId']);
    error_log("First semester grades count: " . count($firstSemGrades));
    error_log("First semester grades detail: " . print_r($firstSemGrades, true));

    // Get student's grades for second semester
    $sql = "SELECT s.subjectName, g.quarter1_grade, g.quarter2_grade,
            ROUND((g.quarter1_grade + g.quarter2_grade) / 2) as final_grade,
            CASE 
                WHEN s.subjectType = 'Applied' THEN 'Applied and Specialized'
                ELSE s.subjectType 
            END as subjectType
            FROM tblgrades g
            JOIN tblsubject s ON s.subjectId = g.subjectId
            JOIN tblusersaccount ua ON ua.userId = g.userId
            WHERE g.userId = ? 
            AND (s.semester = '2nd Semester' OR s.semester = 2)
            AND ua.role = 'Student'
            AND (g.quarter1_grade IS NOT NULL OR g.quarter2_grade IS NOT NULL)
            ORDER BY 
                CASE 
                    WHEN s.subjectType = 'Core' THEN 1
                    WHEN s.subjectType = 'Applied' THEN 2
                    ELSE 3
                END,
                s.subjectName";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student['userId']);
    $stmt->execute();
    $secondSemGrades = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Debug output for second semester
    error_log("Second Semester Query: " . $sql);
    error_log("Second semester grades count: " . count($secondSemGrades));
    error_log("Second semester grades detail: " . print_r($secondSemGrades, true));

    // Also check the tblgrades table structure
    $sql = "DESCRIBE tblgrades";
    $result = $conn->query($sql);
    error_log("tblgrades table structure: " . print_r($result->fetch_all(MYSQLI_ASSOC), true));

    // Add debug logging
    error_log("Student userId: " . $student['userId']);
    error_log("First semester grades: " . print_r($firstSemGrades, true));
    error_log("Second semester grades: " . print_r($secondSemGrades, true));

    // After getting student information, add this query for attendance
    $sql = "SELECT DATE_FORMAT(classDate, '%M') as month, 
            COUNT(CASE WHEN attendance = 'Present' THEN 1 END) as days_present,
            COUNT(CASE WHEN attendance = 'Absent' THEN 1 END) as days_absent,
            COUNT(*) as total_days
            FROM tblattendance 
            WHERE studentId = ? 
            AND YEAR(classDate) = YEAR(CURDATE())
            GROUP BY MONTH(classDate), DATE_FORMAT(classDate, '%M')
            ORDER BY MONTH(classDate)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $attendance = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Debug attendance data
    error_log("Attendance data: " . print_r($attendance, true));

    // Create attendance array with all months
    $attendanceData = array_fill_keys(
        ['June', 'July', 'August', 'September', 'October', 'November', 
         'December', 'January', 'February', 'March', 'April', 'May'],
        ['days_present' => 0, 'days_absent' => 0, 'total_days' => 0]
    );

    // Fill in actual attendance data
    foreach ($attendance as $record) {
        if (isset($attendanceData[$record['month']])) {
            $attendanceData[$record['month']] = [
                'days_present' => (int)$record['days_present'],
                'days_absent' => (int)$record['days_absent'],
                'total_days' => (int)$record['total_days']
            ];
        }
    }

    // Create new PDF document - Changed to Landscape
    $pdf = new TCPDF('L', 'mm', 'Legal', true, 'UTF-8');
    $pdf->SetCreator('School System');
    $pdf->SetTitle('Form 138 - ' . $student['studentLastName']);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(15, 15, 15);
    $pdf->AddPage();

    // Left Column (Width: 140mm)
    // Report on Attendance
    $pdf->Ln(10);   
    $pdf->SetFont('times', 'B', 12);
    $pdf->Cell(140, 7, 'REPORT ON ATTENDANCE', 0, 1, 'C');

    // Attendance Table
    $pdf->SetFont('times', '', 10);

    // Header row with months
    $pdf->Cell(30, 7, '', 1, 0); // Empty cell for row labels
    $totalPresent = 0;
    $totalAbsent = 0;
    $totalDays = 0;

    foreach(array_keys($attendanceData) as $month) {
        $pdf->Cell(9.2, 7, substr($month, 0, 3), 1, 0, 'C');
    }
    $pdf->Cell(9.2, 7, 'Total', 1, 1, 'C');

    // School days
    $pdf->Cell(30, 7, 'No. of School Days', 1, 0);
    foreach($attendanceData as $month => $data) {
        $pdf->Cell(9.2, 7, $data['total_days'], 1, 0, 'C');
        $totalDays += $data['total_days'];
    }
    $pdf->Cell(9.2, 7, $totalDays, 1, 1, 'C');

    // Days present
    $pdf->Cell(30, 7, 'Days Present', 1, 0);
    foreach($attendanceData as $month => $data) {
        $pdf->Cell(9.2, 7, $data['days_present'], 1, 0, 'C');
        $totalPresent += $data['days_present'];
    }
    $pdf->Cell(9.2, 7, $totalPresent, 1, 1, 'C');

    // Days absent
    $pdf->Cell(30, 7, 'Days Absent', 1, 0);
    foreach($attendanceData as $month => $data) {
        $pdf->Cell(9.2, 7, $data['days_absent'], 1, 0, 'C');
        $totalAbsent += $data['days_absent'];
    }
    $pdf->Cell(9.2, 7, $totalAbsent, 1, 1, 'C');

    // Parent's Signature
    $pdf->Ln(5);
    $pdf->SetFont('times', 'B', 9);
    $pdf->Cell(140, 7, "PARENT/GUARDIAN'S SIGNATURE", 0, 1);

    $quarters = ['1st Quarter', '2nd Quarter', '3rd Quarter', '4th Quarter'];
    foreach($quarters as $quarter) {
        $pdf->Cell(18, 7, $quarter . ':', 0, 0);
        $pdf->Cell(110, 7, '_____________________________________________________________________________', 0, 1);
    }

    // Certificate of Transfer
    $pdf->Ln(7);
    $pdf->SetFont('times', 'B', 10);
    $pdf->Cell(140, 7, 'Certificate of Transfer', 0, 1, 'C');

    $pdf->SetFont('times', '', 9);
    $pdf->Cell(25, 7, 'Admitted to Grade:', 0, 0);
    $pdf->Cell(40, 7, '_________________________', 0, 0);
    $pdf->Cell(10, 7, 'Section:', 0, 0);
    $pdf->Cell(35, 7, '_________________________________________', 0, 1);

    $pdf->Cell(140, 7, 'Eligibility for Admission to Grade: ____________________________________________________________', 0, 1);

    // Add Approved section
    $pdf->Cell(15, 7, 'Approved:', 0, 0);
    $pdf->Cell(70, 7, '___________________________________', 0, 0);
    $pdf->Cell(45, 7, '___________________________________', 0, 1);

    // Add labels in italic
    $pdf->SetFont('times', 'I', 9);
    $pdf->Cell(25, 4, '', 0, 0); // Space for "Approved:"
    $pdf->Cell(35, 4, 'School Head', 0, 0, 'C');
    $pdf->Cell(110, 4, 'Adviser', 0, 1, 'C');

    $pdf->Ln(7); // Small space before next section

    // Cancellation of Eligibility to Transfer
    $pdf->Ln(5);
    $pdf->SetFont('times', 'B', 10);
    $pdf->Cell(140, 7, 'Cancellation of Eligibility to Transfer', 0, 1, 'C');

    $pdf->SetFont('times', '', 9);
    $pdf->Cell(18, 7, 'Admitted in:', 0, 0);
    $pdf->Cell(150, 7, '___________________________________________________________________________', 0, 1);

    $pdf->Cell(8, 7, 'Date:', 0, 0);
    $pdf->Cell(45, 7, '__________________________________', 0, 0);
    $pdf->SetX(60);
    $pdf->Cell(125, 7, '______________________________________', 0, 1, 'C');

    // Add School Head label with italic style
    $pdf->SetX(100);
    $pdf->SetFont('times', 'I', 9);
    $pdf->Cell(45, 4, 'School Head', 0, 1, 'C');

    // Right Column (starts at X=160)
    // Header - SF9-SHS and LRN on same line
    $pdf->SetXY(160, 15);
    $pdf->SetFont('times', 'I', 8);
    $pdf->Cell(35, 5, 'SF9-SHS', 0, 0, 'L');

    // Display LRN in boxes
    $pdf->SetXY(220, 15);
    $pdf->SetFont('times', '', 8);
    $pdf->Cell(8, 5, 'LRN:', 0, 0);

    // Split LRN into individual digits
    $lrn = str_pad($student['studentLRN'], 12, ' ', STR_PAD_RIGHT); // Ensure 12 digits
    for($i = 0; $i < 12; $i++) {
        $digit = substr($lrn, $i, 1);
        $pdf->Cell(5, 5, $digit, 1, 0, 'C');
    }

    // School Header with proper spacing
    $pdf->SetXY(170, 20);
    $pdf->Image('../assets/images/deped.png', 170, 25, 15);

    $pdf->SetXY(160, 25); // Moved up from 35
    // School Header
    $pdf->SetXY(160, 25);
    $pdf->SetFont('times', '', 12);
    $pdf->Cell(140, 5, 'Republic of the Philippines', 0, 1, 'C');

    $pdf->SetFont('times', 'B', 14);
    $pdf->Cell(430, 5, 'DEPARTMENT OF EDUCATION', 0, 1, 'C');

    $pdf->SetFont('times', 'I', 12);
    $pdf->Cell(430, 5, 'I', 0, 1, 'C');
    $pdf->Cell(430, 5, 'Region', 0, 1, 'C');
    $pdf->Ln(2); // Reduced space
    $pdf->SetFont('times', 'B U', 12);
    $pdf->Cell(430, 5, 'DIVISION OF ILOCOS SUR', 0, 1, 'C');

    $pdf->SetFont('times', 'I', 12);
    $pdf->Cell(430, 5, 'Division', 0, 1, 'C');
    $pdf->Ln(2); // Reduced space
    $pdf->SetFont('times', 'B U', 12);
    $pdf->Cell(435, 5, 'BANTAY NATIONAL HIGH SCHOOL', 0, 1, 'C');

    $pdf->SetFont('times', 'I', 12);
    $pdf->Cell(430, 5, 'School', 0, 1, 'C');

    // Student Information


    // Student Information - Updated layout
    $pdf->SetXY(160, 75);
    $pdf->SetFont('times', '', 12);
    $pdf->Cell(25, 6, 'Name:', 0, 0);
    $pdf->Cell(200, 6, $student['studentLastName'] . ',                 ' . $student['studentFirstName'] . '               ' . $student['studentMiddleName'], 'B', 1);

    // Labels under name
    $pdf->SetXY(185, 81);
    $pdf->SetFont('times', '', 10);
    $pdf->Cell(30, 4, 'Last Name', 0, 0, 'C');
    $pdf->Cell(40, 4, 'First Name', 0, 0, 'C');
    $pdf->Cell(35, 4, 'Middle Name', 0, 1, 'C');

    // Age and Sex
    $pdf->SetXY(160, 87);
    $pdf->SetFont('times', '', 12);
    $pdf->Cell(25, 6, 'Age:', 0, 0);
    $pdf->Cell(35, 6, '16', 'B', 0); // You might want to calculate age from birthdate
    $pdf->Cell(25, 6, 'Sex:', 0, 0);
    $pdf->Cell(45, 6, 'Male', 'B', 1); // Get this from student data

    // Grade and Section
    $pdf->SetXY(160, 95);
    $pdf->Cell(25, 6, 'Grade:', 0, 0);
    $pdf->Cell(35, 6, $student['gradeLevel'], 'B', 0);
    $pdf->Cell(25, 6, 'Section:', 0, 0);
    $pdf->Cell(45, 6, $student['sectionName'], 'B', 1);

    // Curriculum
    $pdf->SetXY(160, 103);
    $pdf->Cell(25, 6, 'Curriculum:', 0, 0);
    $pdf->SetFont('times', 'B U', 12);
    $pdf->Cell(200, 6, 'K to 12 Basic Education Curriculum', 0, 1);

    // School Year
    $pdf->SetXY(160, 111);
    $pdf->SetFont('times', '', 12);
    $pdf->Cell(25, 6, 'School Year:', 0, 0);
    $pdf->SetFont('times', 'B U', 12);
    $pdf->Cell(200, 6, date('Y') . '-' . (date('Y') + 1), 0, 1);

    // Track/Strand
    $pdf->SetXY(160, 119);
    $pdf->SetFont('times', '', 12);
    $pdf->Cell(25, 6, 'Track/Strand:', 0, 0);
    $pdf->SetFont('times', 'B U', 10);
    $pdf->Cell(200, 6, $student['strand'], 0, 1);

    // Dear Parent text moved below Track/Strand with proper margins
    $pdf->SetXY(160, 130);
    $pdf->SetFont('times', 'I', 11);
    $pdf->Cell(180, 5, 'Dear Parent/Guardian,', 0, 1, 'L');

    // Message paragraphs with proper margins and width
    $pdf->SetXY(160, 137);
    $pdf->SetFont('times', 'I', 11);
    $pdf->Cell(180, 5, '            This report card shows the ability and progress your child has made in the', 0, 1, 'L');
    $pdf->SetX(160);
    $pdf->Cell(180, 5, 'different learning areas as well as his/her core values.', 0, 1, 'L');

    $pdf->SetXY(160, 150);
    $pdf->Cell(180, 5, '           The school welcomes you should you desire to know more about your child\'s', 0, 1, 'L');
    $pdf->SetX(160);
    $pdf->Cell(180, 5, 'progress.', 0, 1, 'L');

    // Adviser's name and Principal at bottom with proper spacing
    $pdf->SetXY(160, 163);
    $pdf->SetFont('times', 'B U', 10);
    $pdf->Cell(180, 6, strtoupper($student['adviserName']), 0, 1, 'C');
    $pdf->SetX(160);
    $pdf->SetFont('times', '', 10);
    $pdf->Cell(180, 4, 'Adviser', 0, 1, 'C');

    // Add Principal line
    $pdf->SetFont('times', 'BU', 10); // 'B' for Bold, 'U' for Underline
    $pdf->SetX(100);
    $pdf->Cell(180, 6, 'MRS. MARYJANE V. MEDINA', 0, 1, 'C');

    $pdf->SetFont('times', '', 10); // Reset font style
    $pdf->SetX(160);
    $pdf->Cell(65, 4, 'Principal IV', 0, 1, 'C');

    // Add new page for grades
    $pdf->AddPage();
    $pdf->SetMargins(15, 5, 15);

    // LEARNER'S PROGRESS REPORT CARD header
    $pdf->SetFont('times', 'B', 12);
    $pdf->Cell(140, 7, "LEARNER'S PROGRESS REPORT CARD", 0, 1, 'C');

    // First Semester subheading
    $pdf->SetFont('times', 'B', 10);
    $pdf->Cell(50, 5, 'First Semester', 0, 1, 'L');

    // Table header for first semester
    $pdf->SetFillColor(200, 200, 200);
    $pdf->SetLineWidth(0.1);

    // First Semester table header
    $pdf->Cell(90, 10, 'Subjects', 1, 0, 'C', true);
    $pdf->Cell(20, 4, 'Quarter', 'TLR', 0, 'C', true);
    $pdf->Cell(30, 4, 'Semester', 1, 1, 'C', true);

    // Quarter numbers subheader
    $pdf->SetX($pdf->GetX() + 90);
    $pdf->Cell(10, 4, '1', 1, 0, 'C', true);
    $pdf->Cell(10, 4, '2', 1, 0, 'C', true);
    $pdf->Cell(30, 4, 'Final Grade', 1, 1, 'C', true);

    // Core Subjects header
    $pdf->SetFont('times', 'B', 10);
    $pdf->Cell(140, 5, 'Core Subjects', 1, 1, 'L', true);

    // Display Core Subjects grades
    $coreSubjectsDisplayed = 0;
    foreach($firstSemGrades as $grade) {
        if($grade['subjectType'] == 'Core') {
            $pdf->Cell(90, 5, $grade['subjectName'], 1, 0);
            $pdf->Cell(10, 5, $grade['quarter1_grade'], 1, 0, 'C');
            $pdf->Cell(10, 5, $grade['quarter2_grade'], 1, 0, 'C');
            $pdf->Cell(30, 5, $grade['final_grade'], 1, 1, 'C');
            $coreSubjectsDisplayed++;
        }
    }

    // Fill remaining core subject rows with empty cells
    for($i = $coreSubjectsDisplayed; $i < 6; $i++) {
        $pdf->Cell(90, 5, '', 1, 0);
        $pdf->Cell(10, 5, '', 1, 0, 'C');
        $pdf->Cell(10, 5, '', 1, 0, 'C');
        $pdf->Cell(30, 5, '', 1, 1, 'C');
    }

    // Applied and Specialized Subjects header
    $pdf->Cell(140, 5, 'Applied and Specialized Subjects', 1, 1, 'L', true);

    // Display Applied/Specialized Subjects grades
    $specializedSubjectsDisplayed = 0;
    foreach($firstSemGrades as $grade) {
        if($grade['subjectType'] == 'Applied and Specialized') {
            $pdf->Cell(90, 5, $grade['subjectName'], 1, 0);
            $pdf->Cell(10, 5, $grade['quarter1_grade'], 1, 0, 'C');
            $pdf->Cell(10, 5, $grade['quarter2_grade'], 1, 0, 'C');
            $pdf->Cell(30, 5, $grade['final_grade'], 1, 1, 'C');
            $specializedSubjectsDisplayed++;
        }
    }

    // Fill remaining specialized subject rows with empty cells
    for($i = $specializedSubjectsDisplayed; $i < 3; $i++) {
        $pdf->Cell(90, 5, '', 1, 0);
        $pdf->Cell(10, 5, '', 1, 0, 'C');
        $pdf->Cell(10, 5, '', 1, 0, 'C');
        $pdf->Cell(30, 5, '', 1, 1, 'C');
    }

    // Calculate and display General Average for first semester
    $totalGrades = 0;
    $subjectCount = 0;

    foreach($firstSemGrades as $grade) {
        // Calculate final grade only if both quarters have grades
        if (is_numeric($grade['quarter1_grade']) && is_numeric($grade['quarter2_grade'])) {
            $finalGrade = number_format(($grade['quarter1_grade'] + $grade['quarter2_grade']) / 2, 2);
            $totalGrades += (float)$finalGrade;
            $subjectCount++;
            
            // Debug each subject's grades
            error_log("First Sem - Subject: " . $grade['subjectName'] . 
                     " Q1: " . $grade['quarter1_grade'] . 
                     " Q2: " . $grade['quarter2_grade'] . 
                     " Final: " . $finalGrade);
        }
    }

// Calculate first semester average and round off
$firstSemesterAverage = ($subjectCount > 0) ? round($totalGrades / $subjectCount) : '';

    // Display first semester general average
    $pdf->Cell(90, 5, 'General Average for the Semester', 1, 0, 'C');
    $pdf->Cell(20, 5, '', 1, 0, 'C');
    $pdf->Cell(30, 5, $firstSemesterAverage, 1, 1, 'C');

    // Debug first semester data
    error_log("First Semester - Total Grades: " . $totalGrades . 
             " Subject Count: " . $subjectCount . 
             " Average: " . $firstSemesterAverage);

    // Space between semesters
    $pdf->Ln(5);

    // Second Semester section with same layout and spacing
    $pdf->SetFont('times', 'B', 10);
    $pdf->Cell(50, 5, 'Second Semester', 0, 1, 'L');

    // Table header for second semester
    $pdf->SetFillColor(200, 200, 200);

    // Second Semester table header
    $pdf->Cell(90, 10, 'Subjects', 1, 0, 'C', true);
    $pdf->Cell(20, 4, 'Quarter', 'TLR', 0, 'C', true);
    $pdf->Cell(30, 4, 'Semester', 1, 1, 'C', true);

    // Quarter numbers subheader
    $pdf->SetX($pdf->GetX() + 90);
    $pdf->Cell(10, 4, '1', 1, 0, 'C', true);
    $pdf->Cell(10, 4, '2', 1, 0, 'C', true);
    $pdf->Cell(30, 4, 'Final Grade', 1, 1, 'C', true);

    // Core Subjects header
    $pdf->SetFont('times', 'B', 10);
    $pdf->Cell(140, 5, 'Core Subjects', 1, 1, 'L', true);

    // Display Core Subjects grades for second semester
    $coreSubjectsDisplayed = 0;
    foreach($secondSemGrades as $grade) {
        if($grade['subjectType'] == 'Core') {
            $pdf->Cell(90, 5, $grade['subjectName'], 1, 0);
            $pdf->Cell(10, 5, $grade['quarter1_grade'], 1, 0, 'C');
            $pdf->Cell(10, 5, $grade['quarter2_grade'], 1, 0, 'C');
            $pdf->Cell(30, 5, $grade['final_grade'], 1, 1, 'C');
            $coreSubjectsDisplayed++;
        }
    }

    // Fill remaining core subject rows
    for($i = $coreSubjectsDisplayed; $i < 6; $i++) {
        $pdf->Cell(90, 5, '', 1, 0);
        $pdf->Cell(10, 5, '', 1, 0, 'C');
        $pdf->Cell(10, 5, '', 1, 0, 'C');
        $pdf->Cell(30, 5, '', 1, 1, 'C');
    }

    // Applied and Specialized Subjects header
    $pdf->Cell(140, 5, 'Applied and Specialized Subjects', 1, 1, 'L', true);

    // Display Applied/Specialized Subjects grades for second semester
    $specializedSubjectsDisplayed = 0;
    foreach($secondSemGrades as $grade) {
        if($grade['subjectType'] == 'Applied and Specialized') {
            $pdf->Cell(90, 5, $grade['subjectName'], 1, 0);
            $pdf->Cell(10, 5, $grade['quarter1_grade'], 1, 0, 'C');
            $pdf->Cell(10, 5, $grade['quarter2_grade'], 1, 0, 'C');
            $pdf->Cell(30, 5, $grade['final_grade'], 1, 1, 'C');
            $specializedSubjectsDisplayed++;
        }
    }

    // Fill remaining specialized subject rows
    for($i = $specializedSubjectsDisplayed; $i < 3; $i++) {
        $pdf->Cell(90, 5, '', 1, 0);
        $pdf->Cell(10, 5, '', 1, 0, 'C');
        $pdf->Cell(10, 5, '', 1, 0, 'C');
        $pdf->Cell(30, 5, '', 1, 1, 'C');
    }

    // Calculate and display General Average for second semester
    $totalGrades = 0;
    $subjectCount = 0;

    foreach($secondSemGrades as $grade) {
        // Calculate final grade only if both quarters have grades
        if (is_numeric($grade['quarter1_grade']) && is_numeric($grade['quarter2_grade'])) {
            $finalGrade = number_format(($grade['quarter1_grade'] + $grade['quarter2_grade']) / 2, 2);
            $totalGrades += (float)$finalGrade;
            $subjectCount++;
            
            // Debug each subject's grades
            error_log("Second Sem - Subject: " . $grade['subjectName'] . 
                     " Q1: " . $grade['quarter1_grade'] . 
                     " Q2: " . $grade['quarter2_grade'] . 
                     " Final: " . $finalGrade);
        }
    }

    // Calculate second semester average
    $secondSemesterAverage = ($subjectCount > 0) ? round($totalGrades / $subjectCount) : '';

    // Display second semester general average
    $pdf->Cell(90, 5, 'General Average for the Semester', 1, 0, 'C');
    $pdf->Cell(20, 5, '', 1, 0, 'C');
    $pdf->Cell(30, 5, $secondSemesterAverage, 1, 1, 'C');

    // Add Iniwasto ni: at the bottom
    $pdf->SetFont('times', 'I', 10);
    $pdf->Cell(15, 4, 'Iniwasto ni:', 0, 0, 'L');

    // Right column - Report on Learner's Observed Values
    $pdf->SetXY(170, 10);
    $pdf->SetFont('times', 'B', 10);
    $pdf->Cell(140, 5, 'REPORT ON LEARNER\'S OBSERVED VALUES', 0, 1, 'C');

    // Values table header - Core Values and Behavior Statements
    $pdf->SetXY(170, 20);
    $pdf->SetFillColor(200, 200, 200);
    $pdf->SetFont('times', 'B', 10);
    // First row with Core Values and Behavior Statements
    $pdf->Cell(30, 14, 'Core Values', 1, 0, 'C', true);
    $pdf->Cell(60, 14, 'Behavior Statements', 1, 0, 'C', true);
    $pdf->Cell(30, 7, 'Quarter', 1, 1, 'C', true);

    // Quarter numbers row
    $pdf->SetX(260);
    $pdf->Cell(7.5, 7, '1', 1, 0, 'C', true);
    $pdf->Cell(7.5, 7, '2', 1, 0, 'C', true);
    $pdf->Cell(7.5, 7, '3', 1, 0, 'C', true);
    $pdf->Cell(7.5, 7, '4', 1, 1, 'C', true);

    // Content rows - update all SetX values
    $pdf->SetX(170);
    $pdf->SetFont('times', 'B', 8);
    $pdf->Cell(30, 14, '1. Maka-Diyos', 1, 0, 'L');
    $pdf->SetFont('times', '', 8);
    $pdf->MultiCell(60, 7, "Expresses one's spiritual beliefs\nwhile respecting others' spiritual beliefs", 1, 'J', false, 0);
    $pdf->Cell(7.5, 7, '', 1, 0, 'C');
    $pdf->Cell(7.5, 7, '', 1, 0, 'C');
    $pdf->Cell(7.5, 7, '', 1, 0, 'C');
    $pdf->Cell(7.5, 7, '', 1, 1, 'C');

    $pdf->SetX(200);
    $pdf->MultiCell(60, 7, "Shows adherence to ethical principles by\nupholding truth in all undertakings", 1, 'J', false, 0);
    $pdf->Cell(7.5, 7, '', 1, 0, 'C');
    $pdf->Cell(7.5, 7, '', 1, 0, 'C');
    $pdf->Cell(7.5, 7, '', 1, 0, 'C');
    $pdf->Cell(7.5, 7, '', 1, 1, 'C');

    // 2. Makatao
    $pdf->SetX(170);
    $pdf->SetFont('times', 'B', 8);
    $pdf->Cell(30, 14 ,'2. Makatao', 1, 0, 'L');
    $pdf->SetFont('times', '', 8);
    $pdf->MultiCell(60, 7, "Is sensitive to individual, social and cultural differences; resists stereotyping people", 1, 'J', false, 0);
    $pdf->Cell(7.5, 7, '', 1, 0, 'C');
    $pdf->Cell(7.5, 7, '', 1, 0, 'C');
    $pdf->Cell(7.5, 7, '', 1, 0, 'C');
    $pdf->Cell(7.5, 7, '', 1, 1, 'C');

    $pdf->SetX(200);
    $pdf->Cell(60, 7, 'Demonstrates contributions toward solidarity', 1, 0, 'J');
    $pdf->Cell(7.5, 7, '', 1, 0, 'C');
    $pdf->Cell(7.5, 7, '', 1, 0, 'C');
    $pdf->Cell(7.5, 7, '', 1, 0, 'C');
    $pdf->Cell(7.5, 7, '', 1, 1, 'C');

    // 3. Makakalikasan
    $pdf->SetX(170);
    $pdf->SetFont('times', 'B', 8);
    $pdf->Cell(30, 14, '3. Makakalikasan', 1, 0, 'L');
    $pdf->SetFont('times', '', 8);
    $pdf->MultiCell(60, 14, 'Cares for the environment and utilizes resources wisely, judiciously and economically', 1, 'J', false, 0);
    $pdf->Cell(7.5, 14, '', 1, 0, 'C');
    $pdf->Cell(7.5, 14, '', 1, 0, 'C');
    $pdf->Cell(7.5, 14, '', 1, 0, 'C');
    $pdf->Cell(7.5, 14, '', 1, 1, 'C');

    // 4. Makabansa
    $pdf->SetX(170);
    $pdf->SetFont('times', 'B', 8);
    $pdf->Cell(30, 14, '4. Makabansa', 1, 0, 'L');
    $pdf->SetFont('times', '', 8);
    $pdf->MultiCell(60, 7, 'Demonstrates pride in being a Filipino; exercises the rights and responsibilities of a Filipino citizen', 1, 'J', false, 0);
    $pdf->Cell(7.5, 7, '', 1, 0, 'C');
    $pdf->Cell(7.5, 7, '', 1, 0, 'C');
    $pdf->Cell(7.5, 7, '', 1, 0, 'C');
    $pdf->Cell(7.5, 7, '', 1, 1, 'C');

    $pdf->SetX(200);
    $pdf->MultiCell(60, 7, 'Demonstrates appropriate behavior in carrying out activities in the school, community and country', 1, 'J', false, 0);
    $pdf->Cell(7.5, 7, '', 1, 0, 'C');
    $pdf->Cell(7.5, 7, '', 1, 0, 'C');
    $pdf->Cell(7.5, 7, '', 1, 0, 'C');
    $pdf->Cell(7.5, 7, '', 1, 1, 'C');

    // Add Observed Values section
    $pdf->Ln(2);
    $pdf->SetFont('times', 'B', 10);
    $pdf->SetX(170);
    $pdf->Cell(50, 7, 'Observed Values', 0, 1, 'L');

    $pdf->SetX(180);
    $pdf->SetFont('times', 'B', 10);
    $pdf->Cell(25, 7, 'Marking', 0, 0, 'C');
    $pdf->Cell(95, 7, 'Non-numerical Rating', 0, 1, 'C');

    $markings = [
        'AO' => 'Always Observed',
        'SO' => 'Sometimes Observed',
        'RO' => 'Rarely Observed',
        'NO' => 'Not Observed'
    ];

    foreach($markings as $code => $desc) {
        $pdf->SetX(190);
        $pdf->SetFont('times', '', 10);
        $pdf->Cell(30, 7, $code, 0, 0, 'L');
        $pdf->Cell(60, 7, $desc, 0, 1, 'C');
    }

    // Add Learner Progress section
    $pdf->Ln(5);
    $pdf->SetFont('times', 'B', 10);
    $pdf->SetX(170);
    $pdf->Cell(120, 7, 'Learner Progress and Achievement', 0, 1, 'L');

    // Progress table headers
    $pdf->SetX(170);
    $pdf->Cell(40, 7, 'Descriptors', 0, 0, 'C');
    $pdf->Cell(40, 7, 'Grading Scale', 0, 0, 'C');
    $pdf->Cell(40, 7, 'Remarks', 0, 1, 'C');

    // Progress table content
    $pdf->SetFont('times', '', 10);
    $grades = [
        ['Outstanding', '90-100', 'Passed'],
        ['Very Satisfactory', '85-89', 'Passed'],
        ['Satisfactory', '80-84', 'Passed'],
        ['Fairly Satisfactory', '75-79', 'Passed'],
        ['Did Not Meet Expectation', 'Below 75', 'Failed']
    ];

    foreach($grades as $grade) {
        $pdf->SetX(170);
        $pdf->Cell(40, 7, $grade[0], 0, 0, 'L');
        $pdf->Cell(40, 7, $grade[1], 0, 0, 'C');
        $pdf->Cell(40, 7, $grade[2], 0, 1, 'C');
    }

    // Change output mode based on whether we're generating multiple forms
    if ($outputMode === 'S') {
        return $pdf->Output('', 'S'); // Return PDF as string
    } else {
        $pdf->Output('Form138-' . $student['studentLastName'] . '.pdf', 'I');
    }
}

// Function to get all students under an adviser
function getAdviserStudents($conn, $sectionId) {
    $sql = "SELECT s.studentId, u.lastName 
            FROM tblstudentinfo s
            JOIN tblusersaccount u ON s.userId = u.userId 
            WHERE s.sectionId = ?
            ORDER BY u.lastName, u.firstName";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $sectionId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Handle the request
if ($student_id === 'all') {
    // Get adviser's section
    $sql = "SELECT sectionId FROM tblteacherinfo WHERE userId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['userId']);
    $stmt->execute();
    $result = $stmt->get_result();
    $teacherInfo = $result->fetch_assoc();
    
    if ($teacherInfo && $teacherInfo['sectionId']) {
        // Get all students in the section
        $students = getAdviserStudents($conn, $teacherInfo['sectionId']);
        
        // Create PDFMerger instance
        $pdf = new PDFMerger();
        
        // Temporary directory for individual PDFs
        $tempDir = sys_get_temp_dir();
        $tempFiles = [];
        
        // Generate and merge PDFs for each student
        foreach ($students as $student) {
            // Generate PDF content
            $pdfContent = generateStudentForm138($student['studentId'], $conn, 'S');
            
            // Save to temporary file
            $tempFile = tempnam($tempDir, 'form138_');
            file_put_contents($tempFile, $pdfContent);
            $tempFiles[] = $tempFile;
            
            // Add to merger
            $pdf->addPDF($tempFile, 'all');
        }
        
        // Merge all PDFs and output
        $pdf->merge('browser', 'Form138-AllStudents.pdf');
        
        // Clean up temporary files
        foreach ($tempFiles as $file) {
            unlink($file);
        }
        
    } else {
        die('Teacher section not found');
    }
} else {
    // Generate form for single student
    generateStudentForm138($student_id, $conn);
} 