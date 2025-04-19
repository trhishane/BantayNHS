<?php


session_start();
include('../includes/dbconn.php');

if (isset($_POST['verify'])) {

    $verificationCode = $_POST['verificationCode'];
    $email = $_POST['email'];

    $sql = "SELECT * FROM tblverificationcodes WHERE verificationCode = '$verificationCode' LIMIT 1";
    $sql_run = mysqli_query($conn, $sql);

    if (!$sql_run) {
        echo "Error: " . mysqli_error($conn);
        exit;
    }

    if (mysqli_num_rows($sql_run) > 0) {
        $sql = "UPDATE tblverificationcodes SET verifyStatus = 1 WHERE verificationCode = '$verificationCode'";
        $sql_run = mysqli_query($conn, $sql);

        if (!$sql_run) {
            echo "Error: " . mysqli_error($conn);
            exit;
        }

        if (mysqli_affected_rows($conn) > 0) {
            $_SESSION['modalType'] = 'success';
            $_SESSION['modalMessage'] = 'Your email has been verified successfully.';
            $_SESSION['redirectUrl'] = '../parents/dashboard.php';
        }
    } else {
        $_SESSION['modalType'] = 'danger';
        $_SESSION['modalMessage'] = 'Invalid verification code.';
        $_SESSION['redirectUrl'] = '../users/verify-email.php';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Email Verification</title>
</head>
<body>

<!-- Modal Structure -->
<div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-body text-center p-lg-4">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="50" height="50" fill="<?php echo $_SESSION['modalType'] === 'success' ? '#198754' : '#dc3545'; ?>">
                    <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"/>
                </svg>
                <h4 class="text-<?php echo $_SESSION['modalType'] === 'success' ? 'success' : 'danger'; ?> mt-3">
                    <?php echo $_SESSION['modalType'] === 'success' ? 'Success' : 'Error'; ?>
                </h4>
                <p class="mt-3 fs-5"><?php echo $_SESSION['modalMessage']; ?></p>
                <button type="button" class="btn btn-<?php echo $_SESSION['modalType'] === 'success' ? 'success' : 'danger'; ?> btn-lg mt-3" data-bs-dismiss="modal" onclick="window.location.href='<?php echo $_SESSION['redirectUrl'] ?? 'index.php'; ?>'">OK</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    $(document).ready(function() {
        <?php if (isset($_SESSION['modalMessage'])) : ?>
            $('#resultModal').modal('show');
        <?php 
        unset($_SESSION['modalType']);
        unset($_SESSION['modalMessage']);
        unset($_SESSION['redirectUrl']);
        endif; 
        ?>
    });
</script>

</body>
</html>
