<?php
include('includes/links.php');
include('includes/sidebar.php');
include('includes/dbconn.php');

if (isset($_GET['userId'])) {
    $userId = $_GET['userId'];

    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'manage-students.php';

    $query = "SELECT * FROM tblusersaccount WHERE userId = '$userId'";
    $query_run = mysqli_query($conn, $query);

    if (mysqli_num_rows($query_run) > 0) {
        $student = mysqli_fetch_assoc($query_run);
        ?>
        <main id="main" class="main">
            <div class="pagetitle">
                <h1>View Student Information</h1>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Student Details</h5>
                    <table class="table">
                        <tr>
                            <th>User ID:</th>
                            <td><?php echo htmlspecialchars($student['userId']); ?></td>
                        </tr>
                        <tr>
                            <th>Full Name:</th>
                            <td><?php echo htmlspecialchars($student['firstName'] . ' ' . $student['middleName'] . ' ' . $student['lastName']); ?></td>
                        </tr>
                        <tr>
                            <th>Username:</th>
                            <td><?php echo htmlspecialchars($student['username']); ?></td>
                        </tr>
                        <tr>
                            <th>Role:</th>
                            <td><?php echo htmlspecialchars($student['role']); ?></td>
                        </tr>
                    </table>
                    <a href="<?php echo htmlspecialchars($referer); ?>" class="btn btn-secondary "><i class="fa-solid fa-arrow-left me-2"></i>Back</a>
                </div>
            </div>
        </main>
        <?php
    } else {
        echo "No record found.";
    }
}
?>
