<?php
session_start();
ob_clean(); // Clear any output buffers
require('../includes/dbconn.php');
require('../fpdf/fpdf.php');

if (!isset($_SESSION['userId'])) {
    header('Location: ../login.php');
    exit();
}

// Get student ID from URL
$student_id = $_GET['student_id'] ?? '';
if (empty($student_id)) {
    die('Student ID is required');
}

    // Fetch student information first
    $sql = "SELECT s.*, 
            CONCAT(ua.firstName, ' ', ua.lastName) as adviserName,
            ua2.firstName as studentFirstName,
            ua2.lastName as studentLastName,
            ua2.middleName as studentMiddleName,
            s.lrn as studentLRN,
            sec.sectionName, sec.gradeLevel, sec.strand,
            CONCAT(s.barangay, ', ', s.municipality, ', ', s.province) as studentAddress,
            s.municipality as birthplace,
            CONCAT(s.fathersName, ' / ', s.mothersName) as guardianName
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

    if (!$student) {
        die('Student not found');
    }

    class Form137 extends FPDF {
        function Header() {
            // Empty header - we'll create it manually
        }
        
        function Footer() {
            // Empty footer - we'll create it manually
        }
    }

    // Create PDF instance - Legal size paper (8.5 x 14 inches)
    $pdf = new Form137('P', 'mm', array(215.9, 355.6));
    $pdf->AddPage();
    $pdf->SetMargins(15, 5, 15);

    // Calculate center positions
    $pageWidth = 215.9;
    $contentWidth = 170; // Total width for content
    $startX = ($pageWidth - $contentWidth) / 2;

    // Top LRN section with tighter spacing
    $pdf->SetFont('Times', 'I', 12);
    $pdf->Cell(0, 4, '           DepEd Form 137-A', 0, 1, 'L');
    $pdf->Cell(25, 4, '      LRN: No.:', 0, 0);
    $pdf->Cell(100, 4, '_____________________', 0, 1);

    // Add more space after LRN
    $pdf->Ln(5);  // Increased from Ln()

    // Logo positioning - smaller size
    if (file_exists('../assets/images/deped.png')) {
        $pdf->Image('../assets/images/deped.png', 45, 25, 25);  // Moved down from y=20
    }

    // Header text position calculation
    $headerX = 85; // Adjusted for better centering
    $headerWidth = 50; // Width for header text

    // Header text - moved down
    $pdf->SetXY($headerX, 25);  // Moved down from y=15
    $pdf->SetFont('Times', '', 11);
    $pdf->Cell($headerWidth, 3, 'Department of Education', 0, 1, 'C');

    $pdf->SetX($headerX);
    $pdf->Cell($headerWidth, 3, 'Region I ', 0, 1, 'C');

    $pdf->SetX($headerX);
    $pdf->Cell($headerWidth, 3, 'Division of Ilocos Sur', 0, 1, 'C');

    $pdf->SetX($headerX);
    $pdf->SetFont('Times', 'B', 12);
    $pdf->Cell($headerWidth, 7, 'BULAG NATIONAL HIGH SCHOOL', 0, 1, 'C');

    $pdf->SetX($headerX);
    $pdf->SetFont('Times', '', 11);
    $pdf->Cell($headerWidth, 5, 'Bantay, Ilocos Sur', 0, 1, 'C');

    $pdf->SetX($headerX);
    $pdf->Cell($headerWidth, 5, 'Telefax (036) 6340-343', 0, 1, 'C');


    $pdf->SetFont('Times', 'B', 12);
    $pdf->Cell(0, 7, "SECONDARY STUDENT' PERMANENT RECORD", 0, 1, 'C');

    // Student Information section
    $pdf->Ln(1);
    $pdf->SetFont('Times', '', 12);

    // First line - Name and Date of Birth
    $pdf->Cell(12, 4, 'Name:', 0, 0);
    $pdf->Cell(80, 4, '______________________________________', 0, 0);
    $pdf->Cell(33, 4, 'Date of Birth: Year', 0, 0);
    $pdf->Cell(13, 4, '______', 0, 0);
    $pdf->Cell(11, 4, 'Month', 0, 0);
    $pdf->Cell(15, 4, '_______', 0, 0);
    $pdf->Cell(7, 4, 'Day', 0, 0);
    $pdf->Cell(25, 4, '_______', 0, 1);

    // Second line - Place of Birth
    $pdf->Cell(41, 4, 'Place of Birth: Province', 0, 0);
    $pdf->Cell(55, 4, '__________________________', 0, 0);
    $pdf->Cell(25, 4, 'Municipality', 0, 0);
    $pdf->Cell(30, 4, '______________', 0, 0);
    $pdf->Cell(11, 4, 'Barrio', 0, 0);
    $pdf->Cell(40, 4, '___________', 0, 1);

    // Third line - Parents/Guardian
    $pdf->Cell(30, 4, 'Parents/Guardian', 0, 0);
    $pdf->Cell(72, 4, '__________________________________', 0, 0);
    $pdf->Cell(18, 4, 'Occupation', 0, 0);
    $pdf->Cell(85, 4, '_______________________________', 0, 1);

    // Fourth line - Address
    $pdf->Cell(49, 4, 'Address of Parents/Guardian', 0, 0);
    $pdf->Cell(225, 4, '_________________________________________________________________', 0, 1);

    // Fifth line - School Information
    $pdf->Cell(62, 4, 'Junior/Senior High School Attended', 0, 0);
    $pdf->Cell(51, 4, '________________________', 0, 0);
    $pdf->Cell(21, 4, 'School Year', 0, 0);
    $pdf->Cell(19, 4, '_________', 0, 0);
    $pdf->Cell(24, 4, 'Gen. Average', 0, 0);
    $pdf->Cell(15, 4, '_______', 0, 1);

    // Total Years line
    $pdf->Cell(0, 4, 'Total Number of Years in School to Complete Junior/Senior High School ______________________________', 0, 1);

    // Add double line separator
    $pdf->Ln(1);
    $pdf->Cell(180, 0.5, '', 'B', 1);
    $pdf->Cell(180, 0.5, '', 'B', 1);

    // Strand Information
    $pdf->Cell(25, 4, 'Strand/Course:', 0, 0);
    $pdf->Cell(245, 4, '____________________________________________________________________________', 0, 1);

    // Last two lines
    $pdf->Cell(29, 4, 'Curriculum Year', 0, 0);
    $pdf->Cell(63, 4, '______________________________', 0, 0);
    $pdf->Cell(12, 4, 'School', 0, 0);
    $pdf->Cell(105, 4, '_______________________________________', 0, 1);

    $pdf->Cell(14, 4, 'Adviser', 0, 0);
    $pdf->Cell(68, 4, '________________________________', 0, 0);
    $pdf->Cell(21, 4, 'School Year', 0, 0);
    $pdf->Cell(23, 4, '___________', 0, 0);
    $pdf->Cell(15, 4, 'Semester', 0, 0);
    $pdf->Cell(40, 4, '______________________', 0, 1);

    // Add some space before grades table
    $pdf->Ln(2);

    // Grades Table Header
    $pdf->SetFont('Times', 'B', 9);

    // First row with merged cells
    $pdf->Cell(95, 10, 'SUBJECTS', 1, 0, 'C');

    // GRADING PERIOD column with merged cells
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->Cell(45, 10, '', 1, 0, 'C');

    // Draw internal vertical line for GRADING PERIOD
    $pdf->Line($x + 22.5, $y + 5, $x + 22.5, $y + 10);

    // Add GRADING PERIOD text
    $pdf->SetXY($x, $y);
    $pdf->Cell(45, 5, 'GRADING PERIOD', 'B', 0, 'C');

    // Add First and Second headers
    $pdf->SetXY($x, $y + 5);
    $pdf->SetFont('Times', 'B', 9);
    $pdf->Cell(22.5, 5, 'First', 0, 0, 'C');
    $pdf->Cell(22.5, 5, 'Second', 0, 0, 'C');

    // Add (Midterm) and (Finals) subtext
    $pdf->SetFont('Times', '', 5);
    $pdf->SetXY($x, $y + 7);
    $pdf->Cell(22.5, 5, '(Midterm)', 0, 0, 'C');
    $pdf->Cell(22.5, 5, '(Finals)', 0, 0, 'C');

    // Reset position and font for next cells
    $pdf->SetXY($x + 45, $y);
    $pdf->SetFont('Times', 'B', 9);

    // Create FINAL RATING cell with proper line break
    $x = $pdf->GetX();
    $pdf->MultiCell(20, 5, "FINAL\nRATING", 1, 'C');

    // Reset position for ACTION TAKEN
    $pdf->SetXY($x + 20, $y);

    // Create ACTION TAKEN cell with proper line break
    $pdf->MultiCell(20, 5, "ACTION\nTAKEN", 1, 'C');

    // Core Subjects section
    $pdf->SetFont('Times', 'B', 11);
    $pdf->Cell(95, 5, 'Core Subjects', 1, 0, 'L');
    $pdf->Cell(22.5, 5, '', 1, 0, 'C');
    $pdf->Cell(22.5, 5, '', 1, 0, 'C');
    $pdf->Cell(20, 5, '', 1, 0, 'C');
    $pdf->Cell(20, 5, '', 1, 1, 'C');

    // Empty rows for core subjects
    for($i = 0; $i < 6; $i++) {
        $pdf->Cell(95, 5, '', 1, 0, 'L');
        $pdf->Cell(22.5, 5, '', 1, 0, 'C');
        $pdf->Cell(22.5, 5, '', 1, 0, 'C');
        $pdf->Cell(20, 5, '', 1, 0, 'C');
        $pdf->Cell(20, 5, '', 1, 1, 'C');
    }

    // Applied Subjects section
    $pdf->SetFont('Times', 'B', 11);
    $pdf->Cell(95, 5, 'Applied Subjects', 1, 0, 'L');
    $pdf->Cell(22.5, 5, '', 1, 0, 'C');
    $pdf->Cell(22.5, 5, '', 1, 0, 'C');
    $pdf->Cell(20, 5, '', 1, 0, 'C');
    $pdf->Cell(20, 5, '', 1, 1, 'C');

    // Empty rows for applied subjects
    for($i = 0; $i < 4; $i++) {
        $pdf->Cell(95, 5, '', 1, 0, 'L');
        $pdf->Cell(22.5, 5, '', 1, 0, 'C');
        $pdf->Cell(22.5, 5, '', 1, 0, 'C');
        $pdf->Cell(20, 5, '', 1, 0, 'C');
        $pdf->Cell(20, 5, '', 1, 1, 'C');
    }

    // Attendance Report
    $pdf->Ln(2);
    $pdf->SetFont('Times', 'B', 11);
    $pdf->Cell(0, 4, 'Attendance Report', 0, 1, 'C');

    // Create attendance table
    $pdf->SetFont('Times', 'B', 11);

    // Calculate cell widths to match grades table total width (180mm)
    $labelWidth = 30;  // Width for the first column
    $monthWidth = 12.5;  // Width for each month (150mm ÷ 12 months)

    // Month headers
    $pdf->Cell($labelWidth, 4, '', 1, 0, 'L');
    $months = ['June', 'July', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'April', 'Total'];
    foreach($months as $month) {
        $pdf->Cell($monthWidth, 4, $month, 1, 0, 'C');
    }
    $pdf->Ln();

    // Days of School row
    $pdf->SetFont('Times', '', 11);
    $pdf->Cell($labelWidth, 4, 'Days of School', 1, 0, 'L');
    $days = ['14', '20', '23', '21', '15', '21', '15', '22', '20', '23', '8', '202'];
    foreach($days as $day) {
        $pdf->Cell($monthWidth, 4, $day, 1, 0, 'C');
    }
    $pdf->Ln();

    // Days Present row
    $pdf->Cell($labelWidth, 4, 'Days Present', 1, 0, 'L');
    for($i = 0; $i < 12; $i++) {
        $pdf->Cell($monthWidth, 4, '', 1, 0, 'C');
    }
    $pdf->Ln();

    // Days Tardy row
    $pdf->Cell($labelWidth, 4, 'Days Tardy', 1, 0, 'L');
    for($i = 0; $i < 12; $i++) {
        $pdf->Cell($monthWidth, 4, '', 1, 0, 'C');
    }
    $pdf->Ln();

    $pdf->Cell(100, 4, 'Has advanced units in _________________________________', 0, 0);
    $pdf->Cell(100, 4, 'Lacks units in _________________________________', 0, 1);
    $pdf->Cell(100, 4, 'Total number of years in school _________________________', 0, 0);
    $pdf->Cell(100, 4, 'Classified as __________________________________', 0, 1);

    $pdf->Ln(1);
    $pdf->Cell(180, 0.5, '', 'B', 1);
    $pdf->Cell(180, 0.5, '', 'B', 1);

    // Strand Information
    $pdf->Cell(25, 4, 'Strand/Course:', 0, 0);
    $pdf->Cell(245, 4, '____________________________________________________________________________', 0, 1);

    // Last two lines
    $pdf->Cell(29, 4, 'Curriculum Year', 0, 0);
    $pdf->Cell(63, 4, '______________________________', 0, 0);
    $pdf->Cell(12, 4, 'School', 0, 0);
    $pdf->Cell(105, 4, '_______________________________________', 0, 1);

    $pdf->Cell(14, 4, 'Adviser', 0, 0);
    $pdf->Cell(68, 4, '________________________________', 0, 0);
    $pdf->Cell(21, 4, 'School Year', 0, 0);
    $pdf->Cell(23, 4, '___________', 0, 0);
    $pdf->Cell(15, 4, 'Semester', 0, 0);
    $pdf->Cell(40, 4, '______________________', 0, 1);

    // Add some space before grades table
    $pdf->Ln(2);

    // Grades Table Header
    $pdf->SetFont('Times', 'B', 9);

    // First row with merged cells
    $pdf->Cell(95, 10, 'SUBJECTS', 1, 0, 'C');

    // GRADING PERIOD column with merged cells
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->Cell(45, 10, '', 1, 0, 'C');

    // Draw internal vertical line for GRADING PERIOD
    $pdf->Line($x + 22.5, $y + 5, $x + 22.5, $y + 10);

    // Add GRADING PERIOD text
    $pdf->SetXY($x, $y);
    $pdf->Cell(45, 5, 'GRADING PERIOD', 'B', 0, 'C');

    // Add First and Second headers
    $pdf->SetXY($x, $y + 5);
    $pdf->SetFont('Times', 'B', 9);
    $pdf->Cell(22.5, 5, 'First', 0, 0, 'C');
    $pdf->Cell(22.5, 5, 'Second', 0, 0, 'C');

    // Add (Midterm) and (Finals) subtext
    $pdf->SetFont('Times', '', 5);
    $pdf->SetXY($x, $y + 7);
    $pdf->Cell(22.5, 5, '(Midterm)', 0, 0, 'C');
    $pdf->Cell(22.5, 5, '(Finals)', 0, 0, 'C');

    // Reset position and font for next cells
    $pdf->SetXY($x + 45, $y);
    $pdf->SetFont('Times', 'B', 9);

    // Create FINAL RATING cell with proper line break
    $x = $pdf->GetX();
    $pdf->MultiCell(20, 5, "FINAL\nRATING", 1, 'C');

    // Reset position for ACTION TAKEN
    $pdf->SetXY($x + 20, $y);

    // Create ACTION TAKEN cell with proper line break
    $pdf->MultiCell(20, 5, "ACTION\nTAKEN", 1, 'C');

    // Core Subjects section
    $pdf->SetFont('Times', 'B', 11);
    $pdf->Cell(95, 5, 'Core Subjects', 1, 0, 'L');
    $pdf->Cell(22.5, 5, '', 1, 0, 'C');
    $pdf->Cell(22.5, 5, '', 1, 0, 'C');
    $pdf->Cell(20, 5, '', 1, 0, 'C');
    $pdf->Cell(20, 5, '', 1, 1, 'C');

    // Empty rows for core subjects
    for($i = 0; $i < 6; $i++) {
        $pdf->Cell(95, 5, '', 1, 0, 'L');
        $pdf->Cell(22.5, 5, '', 1, 0, 'C');
        $pdf->Cell(22.5, 5, '', 1, 0, 'C');
        $pdf->Cell(20, 5, '', 1, 0, 'C');
        $pdf->Cell(20, 5, '', 1, 1, 'C');
    }

    // Applied Subjects section
    $pdf->SetFont('Times', 'B', 11);
    $pdf->Cell(95, 5, 'Applied Subjects', 1, 0, 'L');
    $pdf->Cell(22.5, 5, '', 1, 0, 'C');
    $pdf->Cell(22.5, 5, '', 1, 0, 'C');
    $pdf->Cell(20, 5, '', 1, 0, 'C');
    $pdf->Cell(20, 5, '', 1, 1, 'C');

    // Empty rows for applied subjects
    for($i = 0; $i < 4; $i++) {
        $pdf->Cell(95, 5, '', 1, 0, 'L');
        $pdf->Cell(22.5, 5, '', 1, 0, 'C');
        $pdf->Cell(22.5, 5, '', 1, 0, 'C');
        $pdf->Cell(20, 5, '', 1, 0, 'C');
        $pdf->Cell(20, 5, '', 1, 1, 'C');
    }

    // Attendance Report
    $pdf->Ln(2);
    $pdf->SetFont('Times', 'B', 11);
    $pdf->Cell(0, 4, 'Attendance Report', 0, 1, 'C');

    // Create attendance table
    $pdf->SetFont('Times', 'B', 11);

    // Calculate cell widths to match grades table total width (180mm)
    $labelWidth = 30;  // Width for the first column
    $monthWidth = 12.5;  // Width for each month (150mm ÷ 12 months)

    // Month headers
    $pdf->Cell($labelWidth, 4, '', 1, 0, 'L');
    $months = ['June', 'July', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'April', 'Total'];
    foreach($months as $month) {
        $pdf->Cell($monthWidth, 4, $month, 1, 0, 'C');
    }
    $pdf->Ln();

    // Days of School row
    $pdf->SetFont('Times', '', 11);
    $pdf->Cell($labelWidth, 4, 'Days of School', 1, 0, 'L');
    $days = ['14', '20', '23', '21', '15', '21', '15', '22', '20', '23', '8', '202'];
    foreach($days as $day) {
        $pdf->Cell($monthWidth, 4, $day, 1, 0, 'C');
    }
    $pdf->Ln();

    // Days Present row
    $pdf->Cell($labelWidth, 4, 'Days Present', 1, 0, 'L');
    for($i = 0; $i < 12; $i++) {
        $pdf->Cell($monthWidth, 4, '', 1, 0, 'C');
    }
    $pdf->Ln();

    // Days Tardy row
    $pdf->Cell($labelWidth, 4, 'Days Tardy', 1, 0, 'L');
    for($i = 0; $i < 12; $i++) {
        $pdf->Cell($monthWidth, 4, '', 1, 0, 'C');
    }
    $pdf->Ln();

    $pdf->Cell(100, 4, 'Has advanced units in _________________________________', 0, 0);
    $pdf->Cell(100, 4, 'Lacks units in _________________________________', 0, 1);
    $pdf->Cell(100, 4, 'Total number of years in school _________________________', 0, 0);
    $pdf->Cell(100, 4, 'Classified as __________________________________', 0, 1);
    $pdf->Ln(1);
    $pdf->Cell(180, 0.5, '', 'B', 1);
    $pdf->Cell(180, 0.5, '', 'B', 1);

    // Strand Information
    $pdf->Cell(25, 4, 'Strand/Course:', 0, 0);
    $pdf->Cell(245, 4, '____________________________________________________________________________', 0, 1);

    // Last two lines
    $pdf->Cell(29, 4, 'Curriculum Year', 0, 0);
    $pdf->Cell(63, 4, '______________________________', 0, 0);
    $pdf->Cell(12, 4, 'School', 0, 0);
    $pdf->Cell(105, 4, '_______________________________________', 0, 1);

    $pdf->Cell(14, 4, 'Adviser', 0, 0);
    $pdf->Cell(68, 4, '________________________________', 0, 0);
    $pdf->Cell(21, 4, 'School Year', 0, 0);
    $pdf->Cell(23, 4, '___________', 0, 0);
    $pdf->Cell(15, 4, 'Semester', 0, 0);
    $pdf->Cell(40, 4, '______________________', 0, 1);

    // Add some space before grades table
    $pdf->Ln(2);

    // Grades Table Header
    $pdf->SetFont('Times', 'B', 9);

    // First row with merged cells
    $pdf->Cell(95, 10, 'SUBJECTS', 1, 0, 'C');

    // GRADING PERIOD column with merged cells
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->Cell(45, 10, '', 1, 0, 'C');

    // Draw internal vertical line for GRADING PERIOD
    $pdf->Line($x + 22.5, $y + 5, $x + 22.5, $y + 10);

    // Add GRADING PERIOD text
    $pdf->SetXY($x, $y);
    $pdf->Cell(45, 5, 'GRADING PERIOD', 'B', 0, 'C');

    // Add First and Second headers
    $pdf->SetXY($x, $y + 5);
    $pdf->SetFont('Times', 'B', 9);
    $pdf->Cell(22.5, 5, 'First', 0, 0, 'C');
    $pdf->Cell(22.5, 5, 'Second', 0, 0, 'C');

    // Add (Midterm) and (Finals) subtext
    $pdf->SetFont('Times', '', 5);
    $pdf->SetXY($x, $y + 7);
    $pdf->Cell(22.5, 5, '(Midterm)', 0, 0, 'C');
    $pdf->Cell(22.5, 5, '(Finals)', 0, 0, 'C');

    // Reset position and font for next cells
    $pdf->SetXY($x + 45, $y);
    $pdf->SetFont('Times', 'B', 9);

    // Create FINAL RATING cell with proper line break
    $x = $pdf->GetX();
    $pdf->MultiCell(20, 5, "FINAL\nRATING", 1, 'C');

    // Reset position for ACTION TAKEN
    $pdf->SetXY($x + 20, $y);

    // Create ACTION TAKEN cell with proper line break
    $pdf->MultiCell(20, 5, "ACTION\nTAKEN", 1, 'C');

    // Core Subjects section
    $pdf->SetFont('Times', 'B', 11);
    $pdf->Cell(95, 5, 'Core Subjects', 1, 0, 'L');
    $pdf->Cell(22.5, 5, '', 1, 0, 'C');
    $pdf->Cell(22.5, 5, '', 1, 0, 'C');
    $pdf->Cell(20, 5, '', 1, 0, 'C');
    $pdf->Cell(20, 5, '', 1, 1, 'C');

    // Empty rows for core subjects
    for($i = 0; $i < 6; $i++) {
        $pdf->Cell(95, 5, '', 1, 0, 'L');
        $pdf->Cell(22.5, 5, '', 1, 0, 'C');
        $pdf->Cell(22.5, 5, '', 1, 0, 'C');
        $pdf->Cell(20, 5, '', 1, 0, 'C');
        $pdf->Cell(20, 5, '', 1, 1, 'C');
    }

    // Applied Subjects section
    $pdf->SetFont('Times', 'B', 11);
    $pdf->Cell(95, 5, 'Applied Subjects', 1, 0, 'L');
    $pdf->Cell(22.5, 5, '', 1, 0, 'C');
    $pdf->Cell(22.5, 5, '', 1, 0, 'C');
    $pdf->Cell(20, 5, '', 1, 0, 'C');
    $pdf->Cell(20, 5, '', 1, 1, 'C');

    // Empty rows for applied subjects
    for($i = 0; $i < 4; $i++) {
        $pdf->Cell(95, 5, '', 1, 0, 'L');
        $pdf->Cell(22.5, 5, '', 1, 0, 'C');
        $pdf->Cell(22.5, 5, '', 1, 0, 'C');
        $pdf->Cell(20, 5, '', 1, 0, 'C');
        $pdf->Cell(20, 5, '', 1, 1, 'C');
    }

    // Attendance Report
    $pdf->Ln(2);
    $pdf->SetFont('Times', 'B', 11);
    $pdf->Cell(0, 4, 'Attendance Report', 0, 1, 'C');

    // Create attendance table
    $pdf->SetFont('Times', 'B', 11);

    // Calculate cell widths to match grades table total width (180mm)
    $labelWidth = 30;  // Width for the first column
    $monthWidth = 12.5;  // Width for each month (150mm ÷ 12 months)

    // Month headers
    $pdf->Cell($labelWidth, 4, '', 1, 0, 'L');
    $months = ['June', 'July', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'April', 'Total'];
    foreach($months as $month) {
        $pdf->Cell($monthWidth, 4, $month, 1, 0, 'C');
    }
    $pdf->Ln();

    // Days of School row
    $pdf->SetFont('Times', '', 11);
    $pdf->Cell($labelWidth, 4, 'Days of School', 1, 0, 'L');
    $days = ['14', '20', '23', '21', '15', '21', '15', '22', '20', '23', '8', '202'];
    foreach($days as $day) {
        $pdf->Cell($monthWidth, 4, $day, 1, 0, 'C');
    }
    $pdf->Ln();

    // Days Present row
    $pdf->Cell($labelWidth, 4, 'Days Present', 1, 0, 'L');
    for($i = 0; $i < 12; $i++) {
        $pdf->Cell($monthWidth, 4, '', 1, 0, 'C');
    }
    $pdf->Ln();

    // Days Tardy row
    $pdf->Cell($labelWidth, 4, 'Days Tardy', 1, 0, 'L');
    for($i = 0; $i < 12; $i++) {
        $pdf->Cell($monthWidth, 4, '', 1, 0, 'C');
    }
    $pdf->Ln();

    $pdf->Cell(100, 4, 'Has advanced units in _________________________________', 0, 0);
    $pdf->Cell(100, 4, 'Lacks units in _________________________________', 0, 1);
    $pdf->Cell(100, 4, 'Total number of years in school _________________________', 0, 0);
    $pdf->Cell(100, 4, 'Classified as __________________________________', 0, 1);
    $pdf->Ln(1);
    $pdf->Cell(180, 0.5, '', 'B', 1);
    $pdf->Cell(180, 0.5, '', 'B', 1);

    // Strand Information
    $pdf->Cell(25, 4, 'Strand/Course:', 0, 0);
    $pdf->Cell(245, 4, '____________________________________________________________________________', 0, 1);

    // Last two lines
    $pdf->Cell(29, 4, 'Curriculum Year', 0, 0);
    $pdf->Cell(63, 4, '______________________________', 0, 0);
    $pdf->Cell(12, 4, 'School', 0, 0);
    $pdf->Cell(105, 4, '_______________________________________', 0, 1);

    $pdf->Cell(14, 4, 'Adviser', 0, 0);
    $pdf->Cell(68, 4, '________________________________', 0, 0);
    $pdf->Cell(21, 4, 'School Year', 0, 0);
    $pdf->Cell(23, 4, '___________', 0, 0);
    $pdf->Cell(15, 4, 'Semester', 0, 0);
    $pdf->Cell(40, 4, '______________________', 0, 1);

    // Add some space before grades table
    $pdf->Ln(2);

    // Grades Table Header
    $pdf->SetFont('Times', 'B', 9);

    // First row with merged cells
    $pdf->Cell(95, 10, 'SUBJECTS', 1, 0, 'C');

    // GRADING PERIOD column with merged cells
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->Cell(45, 10, '', 1, 0, 'C');

    // Draw internal vertical line for GRADING PERIOD
    $pdf->Line($x + 22.5, $y + 5, $x + 22.5, $y + 10);

    // Add GRADING PERIOD text
    $pdf->SetXY($x, $y);
    $pdf->Cell(45, 5, 'GRADING PERIOD', 'B', 0, 'C');

    // Add First and Second headers
    $pdf->SetXY($x, $y + 5);
    $pdf->SetFont('Times', 'B', 9);
    $pdf->Cell(22.5, 5, 'First', 0, 0, 'C');
    $pdf->Cell(22.5, 5, 'Second', 0, 0, 'C');

    // Add (Midterm) and (Finals) subtext
    $pdf->SetFont('Times', '', 5);
    $pdf->SetXY($x, $y + 7);
    $pdf->Cell(22.5, 5, '(Midterm)', 0, 0, 'C');
    $pdf->Cell(22.5, 5, '(Finals)', 0, 0, 'C');

    // Reset position and font for next cells
    $pdf->SetXY($x + 45, $y);
    $pdf->SetFont('Times', 'B', 9);

    // Create FINAL RATING cell with proper line break
    $x = $pdf->GetX();
    $pdf->MultiCell(20, 5, "FINAL\nRATING", 1, 'C');

    // Reset position for ACTION TAKEN
    $pdf->SetXY($x + 20, $y);

    // Create ACTION TAKEN cell with proper line break
    $pdf->MultiCell(20, 5, "ACTION\nTAKEN", 1, 'C');

    // Core Subjects section
    $pdf->SetFont('Times', 'B', 11);
    $pdf->Cell(95, 5, 'Core Subjects', 1, 0, 'L');
    $pdf->Cell(22.5, 5, '', 1, 0, 'C');
    $pdf->Cell(22.5, 5, '', 1, 0, 'C');
    $pdf->Cell(20, 5, '', 1, 0, 'C');
    $pdf->Cell(20, 5, '', 1, 1, 'C');

    // Empty rows for core subjects
    for($i = 0; $i < 6; $i++) {
        $pdf->Cell(95, 5, '', 1, 0, 'L');
        $pdf->Cell(22.5, 5, '', 1, 0, 'C');
        $pdf->Cell(22.5, 5, '', 1, 0, 'C');
        $pdf->Cell(20, 5, '', 1, 0, 'C');
        $pdf->Cell(20, 5, '', 1, 1, 'C');
    }

    // Applied Subjects section
    $pdf->SetFont('Times', 'B', 11);
    $pdf->Cell(95, 5, 'Applied Subjects', 1, 0, 'L');
    $pdf->Cell(22.5, 5, '', 1, 0, 'C');
    $pdf->Cell(22.5, 5, '', 1, 0, 'C');
    $pdf->Cell(20, 5, '', 1, 0, 'C');
    $pdf->Cell(20, 5, '', 1, 1, 'C');

    // Empty rows for applied subjects
    for($i = 0; $i < 4; $i++) {
        $pdf->Cell(95, 5, '', 1, 0, 'L');
        $pdf->Cell(22.5, 5, '', 1, 0, 'C');
        $pdf->Cell(22.5, 5, '', 1, 0, 'C');
        $pdf->Cell(20, 5, '', 1, 0, 'C');
        $pdf->Cell(20, 5, '', 1, 1, 'C');
    }

    // Attendance Report
    $pdf->Ln(2);
    $pdf->SetFont('Times', 'B', 11);
    $pdf->Cell(0, 4, 'Attendance Report', 0, 1, 'C');

    // Create attendance table
    $pdf->SetFont('Times', 'B', 11);

    // Calculate cell widths to match grades table total width (180mm)
    $labelWidth = 30;  // Width for the first column
    $monthWidth = 12.5;  // Width for each month (150mm ÷ 12 months)

    // Month headers
    $pdf->Cell($labelWidth, 4, '', 1, 0, 'L');
    $months = ['June', 'July', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'April', 'Total'];
    foreach($months as $month) {
        $pdf->Cell($monthWidth, 4, $month, 1, 0, 'C');
    }
    $pdf->Ln();

    // Days of School row
    $pdf->SetFont('Times', '', 11);
    $pdf->Cell($labelWidth, 4, 'Days of School', 1, 0, 'L');
    $days = ['14', '20', '23', '21', '15', '21', '15', '22', '20', '23', '8', '202'];
    foreach($days as $day) {
        $pdf->Cell($monthWidth, 4, $day, 1, 0, 'C');
    }
    $pdf->Ln();

    // Days Present row
    $pdf->Cell($labelWidth, 4, 'Days Present', 1, 0, 'L');
    for($i = 0; $i < 12; $i++) {
        $pdf->Cell($monthWidth, 4, '', 1, 0, 'C');
    }
    $pdf->Ln();

    // Days Tardy row
    $pdf->Cell($labelWidth, 4, 'Days Tardy', 1, 0, 'L');
    for($i = 0; $i < 12; $i++) {
        $pdf->Cell($monthWidth, 4, '', 1, 0, 'C');
    }
    $pdf->Ln();

    $pdf->Cell(100, 4, 'Has advanced units in _________________________________', 0, 0);
    $pdf->Cell(100, 4, 'Lacks units in _________________________________', 0, 1);
    $pdf->Cell(100, 4, 'Total number of years in school _________________________', 0, 0);
    $pdf->Cell(100, 4, 'Classified as __________________________________', 0, 1);
    $pdf->Ln(1);
    $pdf->Cell(180, 0.5, '', 'B', 1);
    $pdf->Cell(180, 0.5, '', 'B', 1);

// After the double line, add certification section
$pdf->Ln(5);

// This is to certify line
    $pdf->SetFont('Times', '', 11);
$pdf->Cell(68   , 5, 'This is to certify that this is a true record of', 0, 0);
$pdf->Cell(125, 5, '________________________________________________', 0, 1);

// Classified as line
$pdf->Cell(25, 5, 'Classified as', 0, 0);
$pdf->Cell(155, 5, '________________________________________________', 0, 1);

// Date Accomplished line
$pdf->Cell(35, 5, 'Date Accomplished', 0, 0);
$pdf->Cell(145, 5, '________________________________________________', 0, 1);

// Add space before signatures
$pdf->Ln(10);

// Prepared by and Approved by section
$pdf->Cell(90, 5, 'Prepared by:', 0, 0);
$pdf->Cell(90, 5, 'Approved:', 0, 1);

// Add space for signature
$pdf->Ln(10);

// Signature lines
$pdf->Cell(90, 0.5, '_______________________', 0, 0, 'C');
$pdf->Cell(90, 0.5, '   ', 0, 1, 'C');  // Add underline for principal

// Names under signature lines
$pdf->Cell(90, 5, '', 0, 0, 'C');  // Empty cell for adviser
$pdf->SetFont('Times', 'U', 11);  // Set underlined font for principal's name
$pdf->Cell(90, 5, 'MRS. MARYJANE V. MEDINA', 0, 1, 'C');

// Titles under signatures
$pdf->SetFont('Times', '', 11);  // Reset font to normal
$pdf->Cell(90, 5, 'Adviser', 0, 0, 'C');
$pdf->Cell(90, 5, 'Principal III', 0, 1, 'C');

// At the end, use the student data for the filename
$filename = 'Form137-' . ($student['studentLastName'] ?? 'Unknown') . '.pdf';
$pdf->Output('I', $filename);
exit();
