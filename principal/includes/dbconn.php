<?php  
$host = "localhost";
$username = "root";
$password = "";
$dbname = "student_portal";

$conn = mysqli_connect($host, $username,$password, $dbname);

if (!$conn) {
	die("Could not connect to database");
}
?>