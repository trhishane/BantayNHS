<?php
session_start();
include('../includes/links.php'); 

// Set logout alert message
$_SESSION['alertMessage'] = 'You have been logged out successfully.';
$_SESSION['alertType'] = 'info';

// Unset authentication session variables
unset($_SESSION['authenticated']);
unset($_SESSION['auth_user']);
unset($_SESSION['role']);

// Redirect to login page with alert message
header('Location: ../index.php');
exit();
?>
