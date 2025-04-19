<?php
session_start();
include '../config/dbcon.php';

echo "<h1>Session Test Page</h1>";
echo "<p>This page checks if your session is working correctly.</p>";

echo "<h2>Session Information</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['auth_user']) && $_SESSION['auth_role'] == "2") {
    echo "<p style='color:green;'>You are logged in as a teacher. Session is working correctly!</p>";
    echo "<p>User ID: " . $_SESSION['auth_user']['user_id'] . "</p>";
    echo "<p>Name: " . $_SESSION['auth_user']['name'] . "</p>";
    echo "<p>Role: " . $_SESSION['auth_role'] . "</p>";
} else {
    echo "<p style='color:red;'>You are not logged in as a teacher. Session is not working correctly.</p>";
    
    if (!isset($_SESSION['auth_user'])) {
        echo "<p>auth_user is not set in the session.</p>";
    }
    
    if (!isset($_SESSION['auth_role'])) {
        echo "<p>auth_role is not set in the session.</p>";
    } elseif ($_SESSION['auth_role'] != "2") {
        echo "<p>auth_role is set to " . $_SESSION['auth_role'] . " instead of 2.</p>";
    }
}

echo "<h2>Links for Testing</h2>";
echo "<ul>";
echo "<li><a href='index.php'>Dashboard</a></li>";
echo "<li><a href='reports.php'>Class List Reports</a></li>";
echo "<li><a href='create_report.php'>Detailed Reports</a></li>";
echo "<li><a href='check_fpdf.php'>Check FPDF</a></li>";
echo "</ul>";

echo "<h2>Cookie Information</h2>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";
?> 