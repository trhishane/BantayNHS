<?php
// Database connection
$con = mysqli_connect("localhost","root","","student_portal");

// Check connection
if(!$con){
    die('Connection Failed'. mysqli_connect_error());
}
?> 