<?php
require('fpdf.php'); 

include('includes/dbconn.php'); 

$studentId = $_GET['studentId'];
$semester = $_GET['semester'];
$schoolYear = $_GET['schoolYear'];

$query = "
SELECT g.subjectId, s.subjectName, g.quarter1_grade, g.quarter2_grade, g.semester
FROM tblgrades g
JOIN tblsubject s ON g.subjectId = s.subjectId
JOIN tblschoolyear sy ON g.syId = sy.syId
WHERE g.userId = (SELECT userId FROM tblstudentinfo WHERE studentId = '$studentId')
AND g.semester = '$semester'
AND sy.school_year = '$schoolYear'
";

$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    $pdf = new FPDF();
    $pdf->AddPage();
    
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Student Grades Report', 0, 1, 'C');
    $pdf->Ln(10); 
    
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 10, 'Subject', 1, 0, 'C');
    $pdf->Cell(40, 10, 'Semester', 1, 0, 'C');
    $pdf->Cell(40, 10, 'Quarter 1', 1, 0, 'C');
    $pdf->Cell(40, 10, 'Quarter 2', 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 12);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $pdf->Cell(50, 10, htmlspecialchars($row['subjectName']), 1, 0, 'C');
        $pdf->Cell(40, 10, htmlspecialchars($row['semester']), 1, 0, 'C');
        $pdf->Cell(40, 10, ($row['quarter1_grade'] == 0 ? 'Not Available' : $row['quarter1_grade']), 1, 0, 'C');
        $pdf->Cell(40, 10, ($row['quarter2_grade'] == 0 ? 'Not Available' : $row['quarter2_grade']), 1, 1, 'C');
    }

    $pdf->Output();
} else {
    echo "No grades found for the selected student, semester, and school year.";
}
?>
