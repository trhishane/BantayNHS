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
  $mail->SMTPSecure = 'ssl';
  $mail->Port = 465;

  $mail->setFrom('trhishanenicole@gmail.com', 'Bantay National Highschool');
  $mail->addAddress($email);

  $mail->isHTML(true);
  $mail->Subject = "Verification Email From Bantay National Highschool";

  $emailTemplate = "
    <h1>Don't share this code with anyone!</h1>
    <p>Your verification code is: $verifyToken</p>
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

if (isset($_POST['teacher'])) {
  $userId = $_POST['userId'];
  $role = $_POST['role'];

  if ($role == 'Teacher') {
    $teacherId = $_POST['teacherId']; // Get teacherId from form input
    $position = $_POST['position'];
    $birthDate = $_POST['birthDate'];
    $contactNumber = $_POST['contactNumber'];
    $age = calculateAge($birthDate);
    $sex = $_POST['sex'];
    $civilStatus = $_POST['civilStatus'];
    $email = $_POST['email'];
    $provinceName = $_POST['provinceName'];
    $municipalityName = $_POST['municipalityName'];
    $barangayName = $_POST['barangayName'];
    $sectionId = isset($_POST['sectionId']) && $_POST['sectionId'] !== '' ? $_POST['sectionId'] : NULL;
    $isAdviser = $sectionId ? 1 : 0;

    $verifyToken = rand(100000, 999999);
    $verifyStatus = 0;

    $sql = "INSERT INTO tblverificationcodes (userId, email, verificationCode, verifyStatus) 
            VALUES ('$userId', '$email', '$verifyToken', '$verifyStatus')";
    $sql_run = mysqli_query($conn, $sql);

    if ($sql_run) {
      $sql = "INSERT INTO tblteacherinfo (teacherId, userId, birthDate, contactNumber, email, age, sex, position, civilStatus, barangay, municipality, province, sectionId, isAdviser) 
              VALUES ('$teacherId', '$userId', '$birthDate', '$contactNumber', '$email', '$age', '$sex', '$position', '$civilStatus', '$barangayName', '$municipalityName', '$provinceName', '$sectionId', '$isAdviser')";
      $sql_run = mysqli_query($conn, $sql);

      if ($sql_run) {
        sendVerificationEmail($email, $verifyToken);
        $modalType = 'success';
        $modalMessage = "Profile created successfully!";
      } else {
        $modalType = 'error';
        $modalMessage = "Profile creation failed. Please try again.";
      }
    } else {
      $modalType = 'error';
      $modalMessage = "Failed to generate verification code. Please try again.";
    }
  } else {
    $modalType = 'error';
    $modalMessage = "Unauthorized access.";
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
