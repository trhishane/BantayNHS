<?php 
session_start();
include('../includes/dbconn.php');

$modalType = '';
$modalMessage = '';
$errorMessage = '';

if (!isset($_SESSION['auth_user'])) {
    $_SESSION['message'] = "You are not logged in.";
    header("Location: ../profile.php");
    exit();
}

$username = $_SESSION['auth_user']['username'];
$sql = "SELECT * FROM tblusersaccount WHERE username = '$username'";
$sql_run = mysqli_query($conn, $sql);

if (mysqli_num_rows($sql_run)) {
    $row = mysqli_fetch_assoc($sql_run);
    $userId = $row['userId'];
} else {
    $_SESSION['message'] = "User not found.";
    header("Location: ../profile.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newUsername = mysqli_real_escape_string($conn, $_POST['username']);
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($newUsername)) {
        $errorMessage = 'Username cannot be empty.';
    } else {
        $passwordUpdateQuery = '';

        if (!empty($newPassword) && !empty($confirmPassword)) {
            if ($newPassword !== $confirmPassword) {
                $errorMessage = 'Passwords do not match.';
            } elseif (strlen($newPassword) < 8 || !preg_match('/[A-Z]/', $newPassword) || !preg_match('/[a-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
                $errorMessage = 'Password must be at least 8 characters long and include uppercase letters, lowercase letters, and numbers.';
            } else {
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                $passwordUpdateQuery = "password = ?";
            }
        }

        if (!empty($passwordUpdateQuery)) {
            $sql_update = "UPDATE tblusersaccount SET username = ?, $passwordUpdateQuery WHERE userId = ?";
            $stmt = $conn->prepare($sql_update);
            $stmt->bind_param("ssi", $newUsername, $hashedPassword, $userId);
        } else {
            $sql_update = "UPDATE tblusersaccount SET username = ? WHERE userId = ?";
            $stmt = $conn->prepare($sql_update);
            $stmt->bind_param("si", $newUsername, $userId);
        }

        if ($stmt->execute()) {
            $_SESSION['auth_user']['username'] = $newUsername; 
            $modalType = 'success';
            $modalMessage = 'Account updated successfully!';
        } else {
            $modalType = 'error';
            $modalMessage = "Error updating account: " . $stmt->error;
        }

        $_SESSION['modalType'] = $modalType;
        $_SESSION['modalMessage'] = $modalMessage;
        header("Location: edit-account.php");
        exit();
    }
}

if (isset($_SESSION['modalType']) && isset($_SESSION['modalMessage'])) {
    $modalType = $_SESSION['modalType'];
    $modalMessage = $_SESSION['modalMessage'];
    unset($_SESSION['modalType']);
    unset($_SESSION['modalMessage']);
}
?>

<?php 
include('includes/sidebar.php');
include('includes/links.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Account | Student Portal</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .checklist {
            list-style: none;
            padding: 0;
        }
        .checklist li {
            color: red;
        }
        .checklist li.valid {
            color: green;
        }
        .checklist li.invalid {
            color: red;
        }
        .password-message {
            color: red;
            margin-top: 0.5rem;
        }
        .password-message.valid {
            color: green;
        }
    </style>
</head>
<body>
<main id="main" class="main">
  <div class="pagetitle">
    <h1>Edit Account</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
        <li class="breadcrumb-item active">Edit Account</li>
      </ol>
    </nav>
  </div>

  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8 col-xl-6">
        <div class="card mb-5">
          <div class="card-body">
            <h5 class="card-title">Update Your Account Information</h5>
            <form action="edit-account.php" method="post">
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($username) ?>" required>
                <div class="text-danger"><?= htmlspecialchars($errorMessage); ?></div>
              </div>

              <div class="mb-3 password-container">
                <label class="form-label">New Password</label>
                <div class="input-group">
                  <input type="password" class="form-control" name="password" id="password" required>
                  <span class="input-group-text" id="togglePassword">
                    <i class="fas fa-eye"></i>
                  </span>
                </div>
                <ul class="checklist mt-2">
                    <li id="length" class="invalid"><i class="bi bi-check2"></i>At least 8 characters</li>
                    <li id="uppercase" class="invalid"><i class="bi bi-check2"></i>At least one uppercase letter</li>
                    <li id="lowercase" class="invalid"><i class="bi bi-check2"></i>At least one lowercase letter</li>
                    <li id="number" class="invalid"><i class="bi bi-check2"></i>At least one number</li>
                </ul>
                <div class="text-danger"><?= htmlspecialchars($errorMessage); ?></div>
              </div>

              <div class="mb-3 password-container">
                <label class="form-label">Confirm Password</label>
                <div class="input-group">
                  <input type="password" class="form-control" name="confirm_password" id="confirmPassword" required>
                  <span class="input-group-text" id="toggleConfirmPassword">
                    <i class="fas fa-eye"></i>
                  </span>
                </div>
                <div id="confirmMessage" class="password-message"></div>
                <div class="text-danger"><?= htmlspecialchars($errorMessage); ?></div>
              </div>

              <div class="d-flex justify-content-center">
                <button type="submit" class="btn btn-primary w-75" name="update_account">Update Account</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

<div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-body text-center p-lg-4">
        <i class="fas <?= htmlspecialchars($modalType === 'success' ? 'fa-check-circle' : 'fa-times-circle'); ?>" style="font-size: 50px; color: <?= htmlspecialchars($modalType === 'success' ? '#198754' : '#dc3545'); ?>;"></i>
        <h4 class="mt-3 mb-3 text-<?= htmlspecialchars($modalType === 'success' ? 'success' : 'danger'); ?>">
          <?= htmlspecialchars($modalType === 'success' ? 'Success' : 'Error'); ?>
        </h4>
        <p class="fs-5"><?= htmlspecialchars($modalMessage); ?></p>
        <button type="button" class="btn btn-<?= htmlspecialchars($modalType === 'success' ? 'success' : 'danger'); ?> btn-lg mt-3" data-bs-dismiss="modal" id="closeModal">OK</button>
      </div>
    </div>
  </div>
</div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirmPassword');
    const togglePassword = document.getElementById('togglePassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const closeModalButton = document.getElementById('closeModal');

    togglePassword.addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        togglePassword.querySelector('i').classList.toggle('fa-eye-slash');
    });

    toggleConfirmPassword.addEventListener('click', function() {
        const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPassword.setAttribute('type', type);
        toggleConfirmPassword.querySelector('i').classList.toggle('fa-eye-slash');
    });

    password.addEventListener('input', function() {
        const length = document.getElementById('length');
        const uppercase = document.getElementById('uppercase');
        const lowercase = document.getElementById('lowercase');
        const number = document.getElementById('number');

        length.classList.toggle('valid', password.value.length >= 8);
        length.classList.toggle('invalid', password.value.length < 8);
        uppercase.classList.toggle('valid', /[A-Z]/.test(password.value));
        uppercase.classList.toggle('invalid', !/[A-Z]/.test(password.value));
        lowercase.classList.toggle('valid', /[a-z]/.test(password.value));
        lowercase.classList.toggle('invalid', !/[a-z]/.test(password.value));
        number.classList.toggle('valid', /[0-9]/.test(password.value));
        number.classList.toggle('invalid', !/[0-9]/.test(password.value));
    });

    confirmPassword.addEventListener('input', function() {
        const confirmMessage = document.getElementById('confirmMessage');
        confirmMessage.textContent = confirmPassword.value === password.value ? 'Passwords match!' : 'Passwords do not match!';
        confirmMessage.classList.toggle('valid', confirmPassword.value === password.value);
        confirmMessage.classList.toggle('invalid', confirmPassword.value !== password.value);
    });

    closeModalButton.addEventListener('click', function() {
        setTimeout(function() {
            window.location.href = 'profile.php'; 
        }, 500); 
    });

    <?php if ($modalType) : ?>
        var resultModal = new bootstrap.Modal(document.getElementById('resultModal'));
        resultModal.show();
    <?php endif; ?>
});
</script>
</body>
</html>