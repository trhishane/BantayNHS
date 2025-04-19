
<?php
include('../includes/dbconn.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../phpmailer/src/Exception.php';
require '../../phpmailer/src/PHPMailer.php';
require '../../phpmailer/src/SMTP.php';

function sendVerificationEmail($email, $verifyToken) {
  $mail = new PHPMailer(true);

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

  // Email subject and body
  $mail->isHTML(true);
  $mail->Subject = "Verification Email From Bantay National Highschool";

  $emailTemplate = "
    <h1>Don't share this code with anyone!</h1>
    <p>Your verification code is: <strong>$verifyToken</strong></p>
    <p>Please enter this code on the verification page to continue.</p>
  ";

  $mail->Body = $emailTemplate;

  try {
    $mail->send();
  } catch (Exception $e) {
    error_log("Email sending error: ". $e->getMessage());
  }
}

function calculateAge($birthDate) {
  $today = date("Y-m-d");
  $age = date_diff(date_create($birthDate), date_create($today));
  return $age->y;
}

$modalType = '';
$modalMessage = '';


if (isset($_POST['parent'])) {
  $userId = $_POST['userId'];
  $role = $_POST['role'];

  if ($role == 'Parent') {
    $parentId = sprintf("%02d-%05d", rand(1, 99), rand(1, 99999));
    $contactNumber = $_POST['contactNumber'];
    $email = $_POST['email'];
    $birthDate = $_POST['birthDate'];
    $age = calculateAge($birthDate);
    $sex = $_POST['sex'];
    $studentIds = $_POST['studentId']; 
    $bDates = $_POST['bDate']; 
    
    $verifyToken = rand(100000, 999999);
    $verifyStatus = 0;

    $sql = "INSERT INTO tblverificationcodes (userId, email, verificationCode, verifyStatus) 
            VALUES ('$userId', '$email', '$verifyToken', '$verifyStatus')";
    $sql_run = mysqli_query($conn, $sql);

    if ($sql_run) {
      $sql = "INSERT INTO tblparentinfo (parentId, userId, contactNumber, email, birthDate, age, sex) 
              VALUES ('$parentId', '$userId', '$contactNumber', '$email', '$birthDate', '$age', '$sex')";
      $sql_run = mysqli_query($conn, $sql);

      if ($sql_run) {
        foreach ($studentIds as $index => $studentId) {
          $sql = "INSERT INTO tblparent_student (parentId, studentId) 
                  VALUES ('$parentId', '$studentId')";
          mysqli_query($conn, $sql);
        }
        
        sendVerificationEmail($email, $verifyToken);
        $modalType = 'success';
        $modalMessage = 'Profile created successfully! A verification email has been sent to you.';
      } else {
        $modalType = 'error';
        $modalMessage = 'Error: Could not create profile.';
      }
    } else {
      $modalType = 'error';
      $modalMessage = 'Error: Could not send verification code.';
    }
  }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Modal Structure -->
    <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
          <div class="modal-body text-center p-lg-4">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="50" height="50" fill="<?php echo $modalType === 'success' ? '#198754' : '#dc3545'; ?>">
              <path d="<?php echo $modalType === 'success' ? 'M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z' : 'M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm135.3 141.3c6.2 6.2 6.2 16.4 0 22.6L278.6 256l112.7 112.7c6.2 6.2 6.2 16.4 0 22.6s-16.4 6.2-22.6 0L256 278.6l-112.7 112.7c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6L233.4 256 120.7 143.3c-6.2-6.2-6.2-16.4 0-22.6s16.4-6.2 22.6 0L256 233.4l112.7-112.7c6.2-6.2 16.4-6.2 22.6 0z'; ?>">
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

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    <script>
        <?php if ($modalType): ?>
            var resultModal = new bootstrap.Modal(document.getElementById('resultModal'));
            resultModal.show();
            <?php unset($modalType); unset($modalMessage); ?>
        <?php endif; ?>

        function resetForm() {
            location.href = '../verify-email.php';
        }
    </script>
</body>
</html>
