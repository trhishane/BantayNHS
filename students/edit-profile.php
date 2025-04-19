<?php 
include('includes/sidebar.php'); 
include('includes/links.php');

if (isset($_SESSION['auth_user'])) {
  $username = $_SESSION['auth_user']['username'];
  $sql = "SELECT * FROM tblusersaccount WHERE username = '$username'";
  $sql_run = mysqli_query($conn, $sql);
  
  if (mysqli_num_rows($sql_run)) {
    $row = mysqli_fetch_assoc($sql_run);
    $userId = $row['userId'];
    $role = $row['role'];
    $firstName = $row['firstName'];
    $middleName = ($row['middleName'] === NULL || empty($row['middleName'])) ? 'N/A' : $row['middleName'];
    $lastName = $row['lastName'];
    $suffixName = ($row['suffixName'] === NULL || empty($row['suffixName'])) ? 'N/A' : $row['suffixName'];

    $sql = "SELECT * FROM tblstudentinfo WHERE userId = '$userId'";
    $sql_run = mysqli_query($conn, $sql);

    if (mysqli_num_rows($sql_run)) {
      $row = mysqli_fetch_assoc($sql_run);

      $studentId = $row['studentId'];
      $sectionId = $row['sectionId'];
      $contactNumber = $row['contactNumber'];
      $email = $row['email'];
      $birthDate = $row['birthDate'];
      $age = $row['age'];
      $sex = $row['sex'];
      $barangay = $row['barangay'];
      $municipality = $row['municipality'];
      $province = $row['province'];
      $mothersName = $row['mothersName'];
      $fathersName = $row['fathersName'];
      $guardian = $row['guardian'];
      $parentsNum = $row['parentsNum'];
    }
  }
} else {
  echo "You are not logged in.";
}
?>

<title>Edit Profile | Student Portal</title>

<main id="main" class="main">

<div class="pagetitle">
  <h1>Edit Profile</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="admin_dashboard.php">Home</a></li>
      <li class="breadcrumb-item active">Edit Profile</li>
    </ol>
  </nav>
</div><!-- End Page Title -->

<div class="container-fluid">
  <div class="card mb-5">
    <div class="card-body">
      <form action="crud/update-profile.php" method="post" onsubmit="return validateAge();">
        <h5 class="mt-2 mb-2">Personal Information</h5>
        <div class="mb-3">
          <label class="form-label">Learner’s Reference Number (LRN)</label>
          <input type="text" class="form-control" value="<?= $studentId ?>" disabled>
        </div>
        <div class="row mb-3">
          <div class="col-md-3">
            <label class="form-label">First Name</label>
            <input type="text" class="form-control" name="firstName" value="<?= $firstName ?>" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Middle Name</label>
            <input type="text" class="form-control" name="middleName" value="<?= $middleName ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Last Name</label>
            <input type="text" class="form-control" name="lastName" value="<?= $lastName ?>" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Suffix Name</label>
            <input type="text" class="form-control" name="suffixName" value="<?= $suffixName ?>" required>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-4">
            <label class="form-label">Birth Date</label>
            <input type="date" id="birthDate" name="birthDate" class="form-control" value="<?= $birthDate ?>" onchange="calculateAge();" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Sex</label>
            <select class="form-control" name="sex" required>
              <option value="Male" <?= ($sex == 'Male') ? 'selected' : ''; ?>>Male</option>
              <option value="Female" <?= ($sex == 'Female') ? 'selected' : ''; ?>>Female</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Age</label>
            <input type="text" id="age" class="form-control" name="age" value="<?= $age ?>" readonly required>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Contact Number</label>
            <input type="number" class="form-control" name="contactNumber" value="<?= $contactNumber ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Email Address</label>
            <input type="email" class="form-control" name="email" value="<?= $email ?>" required>
          </div>
        </div>

        <div class="row mb-3">
          
          <div class="col-md-6">
          <label class="form-label">Section, Grade & Strand</label>
          <select class="form-select" name="sectionId" id="sectionId" required>
            <option value="" disabled <?= !isset($sectionId) ? 'selected' : ''; ?>>Select Section</option>
            <?php
              $stmt = $conn->prepare("SELECT * FROM tblsection");
              $stmt->execute();
              $sections = $stmt->get_result();

              while ($section = $sections->fetch_assoc()) {
                $selected = ($section['sectionId'] == $sectionId) ? 'selected' : '';
                echo "<option value='{$section['sectionId']}' $selected>
                      {$section['sectionName']} - Grade {$section['gradeLevel']} ({$section['strand']})
                      </option>";
              }
            ?>
          </select>

          </div>
        </div>
        <hr>
        <h5 class="mt-2 mb-2">Address</h5>
        <div class="row mb-3">
  <div class="col-md-4 mb-3">
    <label class="form-label">Province</label>
    <select id="provinceSelect" class="form-select" name="provinceCode">
      <option value="<?= $province; ?>" selected><?= $province; ?></option>
    </select>
    <input type="hidden" name="currentProvinceName" value="<?= $province; ?>">
  </div>
  <div class="col-md-4 mb-3">
    <label class="form-label">Municipality</label>
    <select id="municipalitySelect" class="form-select" name="municipalityCode">
      <option value="<?= $municipality; ?>" selected><?= $municipality; ?></option>
    </select>
    <input type="hidden" name="currentMunicipalityName" value="<?= $municipality; ?>">
  </div>
  <div class="col-md-4 mb-3">
    <label class="form-label">Barangay</label>
    <select id="barangaySelect" class="form-select" name="barangayCode">
      <option value="<?= $barangay; ?>" selected><?= $barangay; ?></option>
    </select>
    <input type="hidden" name="currentBarangayName" value="<?= $barangay; ?>">
  </div>
            <input type="hidden" name="provinceName" id="provinceName">
            <input type="hidden" name="municipalityName" id="municipalityName">
            <input type="hidden" name="barangayName" id="barangayName">
</div>

        <hr>
        <h5 class="mb-2 mt-2">Parents / Guardian Information</h5>
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Mother's Name</label>
            <input type="text" class="form-control" name="mothersName" value="<?= $mothersName ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Father's Name</label>
            <input type="text" class="form-control" name="fathersName" value="<?= $fathersName ?>">
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Guardian Name</label>
            <input type="text" class="form-control" name="guardian" value="<?= $guardian ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Parents/Guardian Contact Number</label>
            <input type="text" class="form-control" name="parentsNum" value="<?= $parentsNum ?>">
          </div>
        </div>

        <div class="d-flex justify-content-end">
          <button type="submit" class="btn btn-primary mb-2 mt-2" style="padding: 0.375rem 2rem; font-size: 20px;">Save Changes</button>
        </div>
    </div>
  </div>
</div>

</main>

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

<script>
       document.addEventListener("DOMContentLoaded", () => {
    const BASE_URL = "https://psgc.gitlab.io/api/";
    const provinceSelect = document.getElementById("provinceSelect");
    const municipalitySelect = document.getElementById("municipalitySelect");
    const barangaySelect = document.getElementById("barangaySelect");

    const fetchProvinces = async () => {
        try {
            const response = await fetch(`${BASE_URL}/provinces`);
            const provinces = await response.json();

            provinces.forEach((province) => {
                const option = document.createElement("option");
                option.value = province.code;
                option.textContent = province.name;
                provinceSelect.appendChild(option);
            });
        } catch (error) {
            console.error("Error fetching provinces:", error);
        }
    };

    fetchProvinces();

    provinceSelect.addEventListener("change", async () => {
        municipalitySelect.innerHTML = '<option value="" disabled selected>Select Municipality/City</option>';
        barangaySelect.innerHTML = '<option value="" disabled selected>Select Barangay</option>';

        const selectedProvinceName = provinceSelect.options[provinceSelect.selectedIndex].text;
        document.getElementById("provinceName").value = selectedProvinceName;

        try {
            const provinceCode = provinceSelect.value;

            const [municipalitiesResponse, citiesResponse] = await Promise.all([
                fetch(`${BASE_URL}/provinces/${provinceCode}/municipalities`),
                fetch(`${BASE_URL}/provinces/${provinceCode}/cities`)
            ]);

            const municipalities = await municipalitiesResponse.json();
            const cities = await citiesResponse.json();

            const locations = [...municipalities, ...cities];

            locations.forEach((location) => {
                const option = document.createElement("option");
                option.value = location.code;
                option.setAttribute("data-type", location.type); // Store whether it's a municipality or city
                option.textContent = location.name;
                municipalitySelect.appendChild(option);
            });
        } catch (error) {
            console.error("Error fetching municipalities and cities:", error);
        }
    });

    municipalitySelect.addEventListener("change", async () => {
        barangaySelect.innerHTML = '<option value="" disabled selected>Select Barangay</option>';

        const selectedOption = municipalitySelect.options[municipalitySelect.selectedIndex];
        const selectedMunicipalityName = selectedOption.text;
        const selectedType = selectedOption.getAttribute("data-type"); // Check if it's a municipality or city
        document.getElementById("municipalityName").value = selectedMunicipalityName;

        try {
            const locationCode = municipalitySelect.value;
            let response;

            if (selectedType === "municipality") {
                // If selected location is a municipality
                response = await fetch(`${BASE_URL}/municipalities/${locationCode}/barangays`);
            } else {
                // If selected location is a city (try fetching from city endpoint)
                response = await fetch(`${BASE_URL}/cities/${locationCode}/barangays`);
            }

            if (!response.ok) throw new Error("Failed to fetch barangays");

            const barangays = await response.json();

            barangays.forEach((barangay) => {
                const option = document.createElement("option");
                option.value = barangay.code;
                option.textContent = barangay.name;
                barangaySelect.appendChild(option);
            });
        } catch (error) {
            console.error("Error fetching barangays:", error);
        }
    });

    barangaySelect.addEventListener("change", () => {
        const selectedBarangayName = barangaySelect.options[barangaySelect.selectedIndex].text;
        document.getElementById("barangayName").value = selectedBarangayName;
    });
});

        function calculateAge() {
    var birthDate = document.getElementById('birthDate').value;
    var today = new Date();
    var birthDateObj = new Date(birthDate);
    var age = today.getFullYear() - birthDateObj.getFullYear();
    var monthDiff = today.getMonth() - birthDateObj.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDateObj.getDate())) {
      age--;
    }
    
    document.getElementById('age').value = age;
  }
    </script>