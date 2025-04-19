<link rel="icon" type="image/x-icon" href="../assets/logo.png">
<title>Edit Account | Student Portal</title>

<?php
include('includes/links.php');
include('includes/sidebar.php');
include('includes/dbconn.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';

if (isset($_GET['userId'])) {
    $userId = $_GET['userId'];

    $query = "SELECT * FROM tblusersaccount WHERE userId = '$userId'";
    $query_run = mysqli_query($conn, $query);

    if (mysqli_num_rows($query_run) > 0) {
        $student = mysqli_fetch_assoc($query_run);
        
        if (isset($_POST['update'])) {
            $firstName = $_POST['firstName'];
            $middleName = $_POST['middleName'];
            $lastName = $_POST['lastName'];
            $email = $_POST['email'];

            $updateQuery = "UPDATE tblusersaccount SET firstName='$firstName', middleName='$middleName', lastName='$lastName', email='$email' WHERE userId='$userId'";
            $update_run = mysqli_query($conn, $updateQuery);
            
            if ($update_run) {
                $modalMessage = "Updated successfully!";
                $modalType = "success";
            } else {
                $modalMessage = "Error: " . mysqli_error($conn);
                $modalType = "error";
            }
        }

        if (isset($_POST['resetPassword'])) {
            
            $newPassword = generateRandomPassword();
            
            
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

           
            $updatePasswordQuery = "UPDATE tblusersaccount SET password='$hashedPassword' WHERE userId='$userId'";
            $updatePassword_run = mysqli_query($conn, $updatePasswordQuery);

            if ($updatePassword_run) {
                
                $email = $student['email'];
                sendPasswordResetEmail($email, $newPassword);

                $modalMessage = "Password reset successfully! A new password has been sent to the email.";
                $modalType = "success";
            } else {
                $modalMessage = "Error resetting password: " . mysqli_error($conn);
                $modalType = "error";
            }
        }

        ?>
        <main id="main" class="main">
            <div class="pagetitle">
                <h1>Edit Student Information</h1>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Edit Details</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" name="firstName" class="form-control" value="<?php echo $student['firstName']; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="middleName" class="form-label">Middle Name</label>
                            <input type="text" name="middleName" class="form-control" value="<?php echo $student['middleName']; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" name="lastName" class="form-control" value="<?php echo $student['lastName']; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="text" name="email" class="form-control" value="<?php echo $student['email']; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" value="<?php echo $student['username']; ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="text" name="password" class="form-control" value="<?php echo $student['password']; ?>" disabled>
                        </div>
                        <a href="manage-students.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Back</a>
                        <button type="submit" name="update" class="btn btn-primary">Update</button>
                    </form>

                    <hr>
                    <h5 class="card-title">Reset Password</h5>
                    <form method="POST">
                        <button type="submit" name="resetPassword" class="btn btn-danger">Reset Password</button>
                    </form>
                </div>
            </div>

            <!-- Custom Bootstrap Modal -->
            <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-body text-center p-lg-4">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="50" height="50" fill="<?php echo $modalType === 'success' ? '#198754' : '#dc3545'; ?>">
                                <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"/>
                            </svg>
                            <h4 class="text-<?php echo $modalType === 'success' ? 'success' : 'danger'; ?> mt-3">
                                <?php echo $modalType === 'success' ? 'Success' : 'Error'; ?>
                            </h4>
                            <p class="mt-3 fs-5"><?php echo $modalMessage; ?></p>
                            <button type="button" class="btn btn-<?php echo $modalType === 'success' ? 'success' : 'danger'; ?> btn-lg mt-3" data-bs-dismiss="modal" onclick="resetForm()">OK</button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                function resetForm() {
                    <?php if ($modalType === 'success') { ?>
                        window.location.href = "manage-students.php";
                    <?php } ?>
                }

                <?php if ($modalMessage) { ?>
                var resultModal = new bootstrap.Modal(document.getElementById('resultModal'));
                resultModal.show();
                <?php } ?>
            </script>

        </main>

<?php
    } else {
        echo "No record found.";
    }
}

function generateRandomPassword($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}

function sendPasswordResetEmail($email, $newPassword) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'trhishanenicole@gmail.com'; 
        $mail->Password = 'wfhy jtvw qadf qykd'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
        $mail->Port = 465;
      
        // Email sender and recipient
        $mail->setFrom('trhishanenicole@gmail.com', 'Bantay National Highschool');
        $mail->addAddress($email);
      


        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Password Has Been Reset';
        $mail->Body    = "<h1>Your password has been successfully reset.</h1>
                          <p>Your new password is : <strong>$newPassword</strong></p>";

        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
