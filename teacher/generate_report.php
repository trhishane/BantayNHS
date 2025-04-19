<?php
session_start();
include '../config/dbcon.php';

// Debug session information
// Uncomment these lines if you need to debug session issues
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// echo "<pre>";
// print_r($_SESSION);
// echo "</pre>";
// exit;

// Check if teacher is logged in
if (!isset($_SESSION['userId'])) {
    $_SESSION['message'] = "You are not authorized as a teacher";
    header("Location: ../login.php");
    exit(0);
}

$teacher_id = $_SESSION['userId'];

// Check if form was submitted
if(isset($_POST['generate_report'])) {
    $class_id = mysqli_real_escape_string($con, $_POST['class_id']);
    $school_name = mysqli_real_escape_string($con, $_POST['school_name']);
    $region = mysqli_real_escape_string($con, $_POST['region']);
    $division = mysqli_real_escape_string($con, $_POST['division']);
    $school_address = mysqli_real_escape_string($con, $_POST['school_address']);
    $school_level = mysqli_real_escape_string($con, $_POST['school_level']);
    $class_details = mysqli_real_escape_string($con, $_POST['class_details']);
    $adviser_name = mysqli_real_escape_string($con, $_POST['adviser_name']);
    
    // Get class information - adjust query based on your database structure
    try {
        $class_query = "SELECT sectionName as class_name, gradeLevel as grade_level 
                        FROM tblsection 
                        WHERE sectionId = '$class_id'";
        $class_result = mysqli_query($con, $class_query);
        $class_data = mysqli_fetch_assoc($class_result);
        
        if (!$class_data) {
            $_SESSION['message'] = "Error retrieving section information: " . mysqli_error($con);
            header("Location: reports.php");
            exit(0);
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "Error retrieving section information: " . $e->getMessage();
        header("Location: reports.php");
        exit(0);
    }
    
    // Get students in this class - adjust query based on your database structure
    try {
        // Join with tblusersaccount to get the names
        $students_query = "SELECT s.*, ua.firstName, ua.middleName, ua.lastName 
                          FROM tblstudentinfo s
                          JOIN tblusersaccount ua ON s.userId = ua.userId AND ua.role = 'student'
                          WHERE s.sectionId = '$class_id'
                          ORDER BY s.sex DESC, ua.lastName ASC";
        $students_result = mysqli_query($con, $students_query);
        
        if (!$students_result) {
            $_SESSION['message'] = "Error retrieving student information: " . mysqli_error($con);
            header("Location: reports.php");
            exit(0);
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "Error retrieving student information: " . $e->getMessage();
        header("Location: reports.php");
        exit(0);
    }
    
    // Count male and female students
    $male_students = [];
    $female_students = [];
    
    while($student = mysqli_fetch_assoc($students_result)) {
        if(strtolower($student['gender']) == 'male') {
            $male_students[] = $student;
        } else {
            $female_students[] = $student;
        }
    }
    
    $total_students = count($male_students) + count($female_students);
    
    // Generate PDF report
    if (file_exists('../fpdf/fpdf.php')) {
        require('../fpdf/fpdf.php');
    } else {
        // If FPDF is not found, display an error message
        $_SESSION['message'] = "FPDF library not found. Please install FPDF library in the fpdf directory.";
        header("Location: reports.php");
        exit(0);
    }
    
    class PDF extends FPDF {
        function Header() {
            // Empty header
        }
        
        function Footer() {
            // Empty footer
        }
    }
    
    $pdf = new PDF('P', 'mm', 'A4');
    $pdf->AddPage();
    
    // School header
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 6, 'Republic of the Philippines', 0, 1, 'C');
    $pdf->Cell(0, 6, 'DEPARTMENT OF EDUCATION', 0, 1, 'C');
    $pdf->Cell(0, 6, $region, 0, 1, 'C');
    $pdf->Cell(0, 6, $division, 0, 1, 'C');
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 6, $school_name, 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 6, $school_address, 0, 1, 'C');
    $pdf->Cell(0, 6, $school_level, 0, 1, 'C');
    $pdf->Cell(0, 6, $class_details, 0, 1, 'C');
    
    $pdf->Ln(10);
    
    // Male students
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(30, 6, 'Male', 0, 1, 'L');
    
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(10, 6, '', 0, 0);
    $pdf->Cell(80, 6, 'Name', 0, 0);
    $pdf->Cell(100, 6, 'Address', 0, 1);
    
    $count = 1;
    foreach($male_students as $student) {
        $pdf->Cell(10, 6, $count . '.', 0, 0);
        
        // Format name as Lastname, Firstname M.
        $middle_initial = !empty($student['middleName']) ? ' ' . substr($student['middleName'], 0, 1) . '.' : '';
        $full_name = $student['lastName'] . ', ' . $student['firstName'] . $middle_initial;
        
        $pdf->Cell(80, 6, $full_name, 0, 0);
        $pdf->Cell(100, 6, $student['address'], 0, 1);
        $count++;
    }
    
    // Female students
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(30, 6, 'Female', 0, 1, 'L');
    
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(10, 6, '', 0, 0);
    $pdf->Cell(80, 6, 'Name', 0, 0);
    $pdf->Cell(100, 6, 'Address', 0, 1);
    
    $count = 1;
    foreach($female_students as $student) {
        $pdf->Cell(10, 6, $count . '.', 0, 0);
        
        // Format name as Lastname, Firstname M.
        $middle_initial = !empty($student['middleName']) ? ' ' . substr($student['middleName'], 0, 1) . '.' : '';
        $full_name = $student['lastName'] . ', ' . $student['firstName'] . $middle_initial;
        
        $pdf->Cell(80, 6, $full_name, 0, 0);
        $pdf->Cell(100, 6, $student['address'], 0, 1);
        $count++;
    }
    
    // Total number of students
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 6, 'Total Number of Students: ' . $total_students, 0, 1, 'L');
    
    // Adviser
    $pdf->Ln(15);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 6, strtoupper($adviser_name), 0, 1, 'L');
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 6, 'Adviser', 0, 1, 'L');
    
    // Output PDF
    $pdf->Output('Student_List_Report.pdf', 'I');
    exit();
} else {
    $_SESSION['message'] = "Please fill the form to generate a report";
    header("Location: reports.php");
    exit(0);
}
?> 