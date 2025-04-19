<?php 
include('includes/header.php');
include('includes/links.php');
?>

<div class="container mt-5 mt-md-7">
  <div class="card border-0 shadow-lg rounded">
    <div class="card-body">
        <?php
          if (isset($_SESSION['auth_user'])) {
            $username = $_SESSION['auth_user']['username'];
            $sql = "SELECT * FROM tblusersaccount WHERE username = '$username'";
            $sql_run = mysqli_query($conn, $sql);
            
            if (mysqli_num_rows($sql_run)) {
              $row = mysqli_fetch_assoc($sql_run);
              $userId = $row['userId'];
              $role = $row['role'];
              $firstName = $row['firstName'];
              $middleName = $row['middleName'];
              $lastName = $row['lastName'];
              $suffixName = $row['suffixName'];
            }
          } else {
            echo "You are not logged in.";
          }
        ?>

        <form action="../parents/crud/add-profile.php" method="POST">
          <h1 class="text-center mb-4 mt-2">Get Started</h1>
          <p class="text-center mb-4">
              Welcome, 
              <?= htmlspecialchars($firstName);?> 
              <?= ($middleName !== "N/A") ? htmlspecialchars($middleName) . ' ' : ''; ?>
              <?= htmlspecialchars($lastName);?> 
              <?= ($suffixName !== "N/A") ? htmlspecialchars($suffixName) : ''; ?>!
              Please fill out the form to continue.
          </p>
          <p class="text-center mb-4">Tell me something about yourself!</p>

          <div class="row mb-4">
            <div class="col-md-3 mb-3">
              <label class="form-label">Birth Date</label>
              <input type="date" class="form-control" name="birthDate" id="birthDate" required>
            </div>
            <div class="col-md-2 mb-3">
              <label for="sex" class="form-label">Gender</label>
              <select id="sex" name="sex" class="form-select" required>
                <option value="" disabled selected>Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Contact Number</label>
              <input type="text" class="form-control" name="contactNumber" id="contactNumber" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Email Address</label>
              <input type="email" class="form-control" name="email" id="email" required>
            </div>
            
            <hr>
            <h5>Officially Enrolled Children</h5>
            <p class="mb-4">
              Please specify your child's <strong>Student ID</strong> and <strong>Birth Date</strong> 
              <span class="text-danger">exactly as encoded</span> in their Personal Information.
            </p>

        <div id="validation-errors" class="alert alert-danger" style="display: none;"></div>

            <div id="children-container">
              <div class="row child-info">
                <div class="col-md-5 mb-3">
                  <label class="form-label">Student ID</label>
                  <input type="text" class="form-control" name="studentId[]" required>
                </div>
                <div class="col-md-5 mb-3">
                  <label class="form-label">Birth Date</label>
                  <input type="date" class="form-control" name="bDate[]" required>
                </div>
                <div class="col-md-1 mb-3 d-flex align-items-end">
                  <button type="button" class="btn text-danger remove-child"> 
                    <i class="bi bi-x-circle-fill"></i> 
                  </button>
                </div>
              </div>
            </div>

            <div class="d-flex">
              <button type="button" id="add-child" class="btn text-success">
                <i class="bi bi-plus-circle-fill"></i>
              </button>
            </div>

            <input type="hidden" name="userId" value="<?= htmlspecialchars($userId) ?>">
            <input type="hidden" name="role" value="<?= htmlspecialchars($role) ?>">
            <div class="d-flex justify-content-end">
              <button type="submit" name="parent" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
  </div>
</div>

<script>
  document.getElementById('add-child').addEventListener('click', function() {
    var container = document.getElementById('children-container');
    
    var newChild = document.createElement('div');
    newChild.classList.add('row', 'child-info', 'mb-3');
    newChild.innerHTML = `
      <div class="col-md-5">
        <label class="form-label">Student ID</label>
        <input type="text" class="form-control" name="studentId[]" required>
      </div>
      <div class="col-md-5">
        <label class="form-label">Birth Date</label>
        <input type="date" class="form-control" name="bDate[]" required>
      </div>
      <div class="col-md-1 d-flex align-items-end">
        <button type="button" class="btn text-danger remove-child">
          <i class="bi bi-x-circle-fill"></i> <!-- X icon -->
        </button>
      </div>
    `;
    container.appendChild(newChild);
    addRemoveChildEvent(); 
  });

  function addRemoveChildEvent() {
    document.querySelectorAll('.remove-child').forEach(function(button) {
      button.addEventListener('click', function() {
        this.closest('.child-info').remove();
      });
    });
  }

  addRemoveChildEvent();

  document.addEventListener('DOMContentLoaded', function() {
    var initialRemoveButtons = document.querySelectorAll('.remove-child');
    if (initialRemoveButtons.length > 0) {
      initialRemoveButtons[0].style.display = 'none'; 
    }
  });

  function validateChildInfo() {
    const studentIds = document.querySelectorAll('input[name="studentId[]"]');
    const birthDates = document.querySelectorAll('input[name="bDate[]"]');
    let validationPassed = true;
    let errorMessage = '';

    studentIds.forEach((idInput, index) => {
      const studentId = idInput.value.trim();
      const birthDate = birthDates[index].value;

      if (studentId === '') {
        validationPassed = false;
        errorMessage += `Student ID cannot be empty.<br>`;
      }

      if (birthDate === '') {
        validationPassed = false;
        errorMessage += `Birth Date cannot be empty.<br>`;
      }

      if (studentId && birthDate) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'validate-student.php', false); 
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
          const response = JSON.parse(xhr.responseText);
          if (!response.valid) {
            errorMessage += `Student ID: ${studentId} and Birth Date: ${birthDate} do not match any records.<br>`;
            validationPassed = false;
          }
        };
        xhr.send(`studentId=${studentId}&birthDate=${birthDate}`);
      }
    });

    if (!validationPassed) {
      document.getElementById('validation-errors').innerHTML = errorMessage;
      document.getElementById('validation-errors').style.display = 'block';
    } else {
      document.getElementById('validation-errors').style.display = 'none'; 
    }

    return validationPassed;
  }

  document.querySelector('form').addEventListener('submit', function(event) {
    const isValid = validateChildInfo();
    if (!isValid) {
      event.preventDefault(); 
    }
  });
</script>
