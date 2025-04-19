<?php
session_start();
include '../config/dbcon.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Column Check</h1>";

// Get a sample student record to see the actual column names
echo "<h2>Sample student record:</h2>";
$query = "SELECT * FROM tblstudentinfo LIMIT 1";
$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $student = mysqli_fetch_assoc($result);
    echo "<pre>";
    print_r($student);
    echo "</pre>";
} else {
    echo "<p>No student records found or error: " . mysqli_error($con) . "</p>";
}

// Get a sample teacher record
echo "<h2>Sample teacher record:</h2>";
$query = "SELECT * FROM tblteacherinfo LIMIT 1";
$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $teacher = mysqli_fetch_assoc($result);
    echo "<pre>";
    print_r($teacher);
    echo "</pre>";
} else {
    echo "<p>No teacher records found or error: " . mysqli_error($con) . "</p>";
}
?> 