<?php
include('../includes/dbconn.php');
require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';
require '../vendor/autoload.php'; 

use PhpOffice\PhpSpreadsheet\IOFactory;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendAccountDetailsEmail($email, $username, $rawPassword) {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'trhishanenicole@gmail.com'; 
    $mail->Password = 'wfhy jtvw qadf qykd'; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
    $mail->Port = 465;

    $mail->setFrom('trhishanenicole@gmail.com', 'Bantay National Highschool');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = "Account Details from Bantay National Highschool";

    $emailTemplate = "
        <h1>Welcome to Bantay National Highschool</h1>
        <p>Your account has been created successfully.</p>
        <p><strong>Username:</strong> $username</p>
        <p><strong>Password:</strong> $rawPassword</p>
        <p>Please change your password after logging in for security purposes.</p>
    ";

    $mail->Body = $emailTemplate;

    try {
        $mail->send();
    } catch (Exception $e) {
        error_log("Email sending error: " . $e->getMessage());
    }
}

function generateUsername($conn, $firstName, $lastName) {
    $firstInitial = substr(strtolower($firstName), 0, 1);
    $lastPart = rand(1, 99);

    $username = $firstInitial . $lastName . $lastPart;

    $sql = "SELECT * FROM tblusersaccount WHERE username = '$username' LIMIT 1";
    $sql_run = mysqli_query($conn, $sql);

    if (mysqli_num_rows($sql_run) > 0) {
        $username = $firstInitial . $lastName . rand(1, 99);
    }

    return $username;
}
if (isset($_POST['uploadExcel'])) {
    $fileName = $_FILES['excelFile']['name'];
    $fileTmp = $_FILES['excelFile']['tmp_name'];

    $allowedFileType = ['xls', 'xlsx'];
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

    if (in_array($fileExtension, $allowedFileType)) {
        $spreadsheet = IOFactory::load($fileTmp);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        foreach ($sheetData as $index => $row) {
            if ($index === 1) continue; 

            $firstName = $row['A'];
            $middleName = $row['B'];
            $lastName = $row['C'];
            $suffixName = $row['D'];
            $email = $row['E'];

            $username = generateUsername($conn, $firstName, $lastName);
            $rawPassword = substr(str_shuffle(MD5(microtime())), 0, 10);
            $hashedPassword = password_hash($rawPassword, PASSWORD_DEFAULT);

            $userId = sprintf("%02d-%05d", rand(1, 99), rand(1, 99999));

            $sql = "INSERT INTO tblusersaccount (userId, firstName, middleName, lastName, suffixName, username, password, role, email) 
                    VALUES ('$userId', '$firstName', '$middleName', '$lastName', '$suffixName', '$username', '$hashedPassword', 'Teacher', '$email')";

            $sql_run = mysqli_query($conn, $sql);

            if ($sql_run) {
                sendAccountDetailsEmail($email, $username, $rawPassword);
            } else {
                error_log("Error creating user: " . mysqli_error($conn));
            }
        }

        $modalMessage = "Account Created Successfully!";
        $modalType = "success";
    } else {
        $modalMessage = "Invalid File Format. Only Excel files are allowed.";
        $modalType = "error";
    }
} 

if (isset($_POST['create'])) {
    $firstName = $_POST['firstName'];
    $middleName = $_POST['middleName'];
    $lastName = $_POST['lastName'];
    $suffixName = $_POST['suffixName'];
    $email = $_POST['email'];

    $username = generateUsername($conn, $firstName, $lastName);
    $rawPassword = substr(str_shuffle(MD5(microtime())), 0, 10);
    $hashedPassword = password_hash($rawPassword, PASSWORD_DEFAULT);

    $userId = sprintf("%02d-%05d", rand(1, 99), rand(1, 99999));

    $sql = "INSERT INTO tblusersaccount (userId, firstName, middleName, lastName, suffixName, username, password, role, email) 
            VALUES ('$userId', '$firstName', '$middleName', '$lastName', '$suffixName', '$username', '$hashedPassword', 'Teacher', '$email')";

    $sql_run = mysqli_query($conn, $sql);

    if ($sql_run) {
        sendAccountDetailsEmail($email, $username, $rawPassword);
        $modalMessage = "Account Created Successfully!";
        $modalType = "success";
    } else {
        error_log("Error creating user: " . mysqli_error($conn));
        $modalMessage = "Error occurred while creating account.";
        $modalType = "error";
    }
}
?>

<?php
include('includes/sidebar.php');
include('includes/links.php');
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Create Account</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item">Create Account</li>
            </ol>
        </nav>
    </div>

    <div class="d-flex justify-content-center">
        <div class="card w-75 p-3">
            <div class="card-body">
            <h5>Create Teacher Account</h5>
                    <p>Account Details</p>
                    <div class="d-flex justify-content-center">
                        <form action="create-teacher-acc.php" method="POST" class="w-75">
                            <div class="mb-3">
                                <input type="text" class="form-control" name="firstName" placeholder="First Name" required>
                            </div>
                            <div class="mb-3">
                                <input type="text" class="form-control" name="middleName" placeholder="Middle Name">
                            </div>
                            <div class="mb-3">
                                <input type="text" class="form-control" name="lastName" placeholder="Last Name" required>
                            </div>
                            <div class="mb-3">
                              <input type="text" class="form-control" name="suffixName" placeholder="Suffix Name">
                            </div>
                            <div class="mb-3">
                              <input type="text" class="form-control" name="email" placeholder="Email" required>
                            </div>
                            <div class="mb-3">
                              <input type="text" class="form-control" placeholder="Username Auto Generated" disabled>
                            </div>
                            <div class="mb-3">
                              <input type="password" class="form-control" placeholder="Password Auto Generated" disabled>
                            </div>
                            <div class="d-flex justify-content-center">
                                <button type="submit" name="create" class="btn btn-primary w-50">Create</button>
                            </div>
                        </form>
                    </div>
                    <hr>
                <h5>Create Multiple Teachers Accounts</h5>
                <form action="create-teacher-acc.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <input type="file" name="excelFile" class="form-control" accept=".xls,.xlsx" required>
                    </div>
                    <div class="d-flex justify-content-center">
                        <button type="submit" name="uploadExcel" class="btn btn-primary w-50">Upload File</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal -->
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
                    <button type="button" class="btn btn-<?php echo $modalType === 'success' ? 'success' : 'danger'; ?> btn-lg mt-3" data-bs-dismiss="modal"onclick="resetForm()">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function resetForm() {
        <?php if ($modalType === 'success') { ?>
            window.location.href = "manage-teachers.php";
        <?php } ?>
    }
    <?php if (isset($modalMessage) && $modalMessage) { ?>
        var resultModal = new bootstrap.Modal(document.getElementById('resultModal'));
        resultModal.show();
    <?php } ?>
    </script>
</main>
