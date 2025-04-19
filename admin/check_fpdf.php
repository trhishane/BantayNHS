<?php
session_start();
include '../config/dbcon.php';

// Check if teacher is logged in
if (!isset($_SESSION['auth_user']) || $_SESSION['auth_role'] != "2") {
    $_SESSION['message'] = "You are not authorized as a teacher";
    header("Location: ../login.php");
    exit(0);
}

echo "<h1>FPDF Check</h1>";
echo "<p>This page checks if the FPDF library is properly installed.</p>";

// Check if FPDF exists
if (file_exists('../fpdf/fpdf.php')) {
    echo "<p style='color:green;'>FPDF library found at ../fpdf/fpdf.php</p>";
    
    // Try to include it
    try {
        require('../fpdf/fpdf.php');
        echo "<p style='color:green;'>FPDF library loaded successfully!</p>";
        
        // Try to create a PDF object
        try {
            $pdf = new FPDF();
            echo "<p style='color:green;'>FPDF object created successfully!</p>";
            echo "<p>FPDF is working correctly. You can generate PDF reports.</p>";
        } catch (Exception $e) {
            echo "<p style='color:red;'>Error creating FPDF object: " . $e->getMessage() . "</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red;'>Error loading FPDF library: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color:red;'>FPDF library not found at ../fpdf/fpdf.php</p>";
    echo "<p>Please install the FPDF library:</p>";
    echo "<ol>";
    echo "<li>Download FPDF from <a href='http://www.fpdf.org/' target='_blank'>http://www.fpdf.org/</a></li>";
    echo "<li>Create a folder named 'fpdf' in the root directory of your project</li>";
    echo "<li>Extract the downloaded files into this folder</li>";
    echo "</ol>";
}

// Check session data
echo "<h2>Session Information</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?> 