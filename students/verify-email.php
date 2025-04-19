<?php 
include('includes/header.php'); 
include('includes/links.php'); 

session_start();
include('../includes/dbconn.php');

if (isset($_SESSION['auth_user'])) {
    $username = $_SESSION['auth_user']['username'];
    $sql = "SELECT * FROM tblusersaccount WHERE username = '$username'";
    $sql_run = mysqli_query($conn, $sql);
    
    if ($row = mysqli_fetch_assoc($sql_run)) {
        $role = $row['role'];
        $userId = $row['userId'];

        if ($role == 'Student') {
            $sql = "SELECT * FROM tblstudentinfo WHERE userId = '$userId'";
            $sql_run = mysqli_query($conn, $sql);

            if ($row = mysqli_fetch_assoc($sql_run)) {
                $email = $row['email'];
            } else {
                echo "No student info found.";
                exit;
            }
        } else {
            echo "You are not authorized to verify email.";
            exit;
        }
    } else {
        echo "User not found.";
        exit;
    }
} else {
    echo "You are not logged in.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Verify Email</title>
</head>
<body>
    <div class="mt-5 d-flex align-items-center">
        <div class="container">
            <div class="card border border-0 position-relative" style="margin-top: 6%">
                <div class="card-body">
                    <form action="../students/email-verification.php" method="POST">
                        <h1 class="text-center h3 mt-3">
                        Please check your email for the verification code.
                        </h1>
                        <p class="text-center mt-3">We sent a 6-digit code to your email <?= htmlspecialchars($email) ?></p>
                        <div class="d-flex justify-content-center">
                            <div class="card border border-0" style="width: 40%">
                                <div class="card-body d-flex align-items-center">
                                    <input type="text" name="verificationCode" class="form-control fs-2 me-2 text-center" required placeholder="Enter the code here">
                                    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            <button type="submit" name="verify" class="btn btn-success">Submit Code</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Include modal code here if needed -->
    <?php if (isset($_SESSION['modalType'])): ?>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var resultModal = new bootstrap.Modal(document.getElementById('resultModal'));
            resultModal.show();
        });
    </script>
    <?php 
        unset($_SESSION['modalType']);
        unset($_SESSION['modalMessage']);
        unset($_SESSION['redirectUrl']);
    ?>
    <?php endif; ?>
</body>
</html>
