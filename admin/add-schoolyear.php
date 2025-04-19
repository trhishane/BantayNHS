<?php
include('includes/dbconn.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['school_year']) && !empty($_POST['school_year'])) {
        $school_year = mysqli_real_escape_string($conn, $_POST['school_year']);

        // Check if the school year already exists
        $checkQuery = "SELECT * FROM tblschoolyear WHERE school_year = '$school_year'";
        $checkResult = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($checkResult) > 0) {
            // School year already exists, redirect with warning
            header("Location: manage-schoolyear.php?warning=School year $school_year already exists!");
            exit();
        } else {
            // Set all existing school years to "No"
            $updateQuery = "UPDATE tblschoolyear SET status = 'No'";
            mysqli_query($conn, $updateQuery);

            // Insert new school year with "Yes" as the current year
            $insertQuery = "INSERT INTO tblschoolyear (school_year, status) VALUES ('$school_year', 'Yes')";
            if (mysqli_query($conn, $insertQuery)) {
                header("Location: manage-schoolyear.php?message=School year added successfully!");
                exit();
            } else {
                echo "Error: " . mysqli_error($conn);
            }
        }
    } else {
        header("Location: manage-schoolyear.php?warning=Please enter a school year!");
        exit();
    }
}
?>

<?php

include('includes/links.php');
include('includes/sidebar.php');
?>
<link rel="icon" type="image/x-icon" href="../assets/logo.png">
<title>Add School Year | Student Portal</title>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Add School Year</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="admin_dashboard.php" style="text-decoration: none;">Home</a></li>
                <li class="breadcrumb-item active">Add School Year</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">School Year</h5>
            </div>

    <form action="add-schoolyear.php" method="post">
        <div class="mb-3">
            <label for="school_year" class="form-label">School Year</label>
            <input type="text" name="school_year" id="school_year" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Add School Year</button>
        <a href="manage-schoolyear.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
