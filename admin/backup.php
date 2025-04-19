<?php
include('includes/dbconn.php');
include('includes/links.php');
include('includes/sidebar.php');

$backupDir = 'backups/';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

$mysqldump = "C:\\xampp\\mysql\\bin\\mysqldump"; // Adjust path if needed
$alertMessage = "";

if (isset($_POST['backup'])) {
    $backupFile = $backupDir . 'backup_' . date("Y-m-d_H-i-s") . '.sql';
    $command = "$mysqldump --user=$username --password=$password --host=$host $dbname > $backupFile 2>&1";
    exec($command, $output, $result);

    if ($result !== 0) {
        $alertMessage = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                            Backup failed:" . implode("<br>", $output) . "
                            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                         </div>";
    } else {
        $alertMessage = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                            Backup successful!
                            <a href='$backupFile' download>Download Backup</a>
                            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                         </div>";
    }
}
?>
<link rel="icon" type="image/x-icon" href="../assets/logo.png">
<title>Back up | Student Portal</title>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Back up Database</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="admin_dashboard.php" style="text-decoration: none;">Home</a></li>
                <li class="breadcrumb-item active">Back up Database</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-body">
            <h2>Back up Database</h2>
            

    <!-- Alert message above the button -->
    <?php if (!empty($alertMessage)) echo $alertMessage; ?>

    <form method="POST">
        <button type="submit" name="backup" class="btn btn-primary mt-2">Back up Database</button>
    </form>

        </div>
    </div>
</main>
