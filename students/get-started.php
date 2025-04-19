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

            $stmt = $conn->prepare("SELECT * FROM tblusersaccount WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
              $row = $result->fetch_assoc();
              $userId = $row['userId'];
              $role = $row['role'];
              $firstName = htmlspecialchars($row['firstName']);
              $middleName = htmlspecialchars($row['middleName']);
              $lastName = htmlspecialchars($row['lastName']);
              $suffixName = htmlspecialchars($row['suffixName']);
            } else {
              echo "<div class='alert alert-danger'>User not found.</div>";
              exit();
            }
          } else {
            echo "<div class='alert alert-danger'>You are not logged in.</div>";
            exit();
          }
        ?>

        <form action="../students/crud/add-profile.php" method="POST">
          <h1 class="text-center mb-4 mt-2">Get Started</h1>
          <p class="text-center mb-4">
              Welcome, 
              <?= htmlspecialchars($firstName);?> 
              <?= ($middleName !== "N/A") ? htmlspecialchars($middleName) . ' ' : ''; ?>
              <?= htmlspecialchars($lastName);?> 
              <?= ($suffixName !== "N/A") ? htmlspecialchars($suffixName) : ''; ?>!
              Please fill out the form to continue.
          </p>
          <p class="text-center mb-4">Tell us something about yourself!</p>

          <div class="row mb-4">
          <div class="col-md-4 mb-3">
            <label class="form-label">Learner’s Reference Number (LRN)</label>
            <input type="text" class="form-control" name="studentId" id="studentId" required>
          </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Birth Date</label>
              <input type="date" class="form-control" name="birthDate" id="birthDate" required>
            </div>
            <div class="col-md-4 mb-3">
              <label for="sex" class="form-label">Gender</label>
              <select id="sex" name="sex" class="form-select" required>
                <option value="" disabled selected>Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Contact Number</label>
              <input type="tel" class="form-control" name="contactNumber" id="contactNumber" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Email Address</label>
              <input type="email" class="form-control" name="email" id="email" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Section, Grade & Strand</label>
              <select class="form-select" name="sectionId" id="sectionId" required>
                <option value="" disabled selected>Select Section</option>
                <?php
                  $stmt = $conn->prepare("SELECT * FROM tblsection");
                  $stmt->execute();
                  $sections = $stmt->get_result();

                  while ($section = $sections->fetch_assoc()) {
                    echo "<option value='{$section['sectionId']}'>
                          {$section['sectionName']} - Grade {$section['gradeLevel']} ({$section['strand']})
                          </option>";
                  }
                ?>
              </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="provinceSelect" class="form-label">Province</label>
                <select id="provinceSelect" class="form-select" name="provinceCode" required>
                    <option value="" disabled selected>Select Province</option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="municipalitySelect" class="form-label">Municipality</label>
                <select id="municipalitySelect" class="form-select" name="municipalityCode" required>
                    <option value="" disabled selected>Select Municipality</option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="barangaySelect" class="form-label">Barangay</label>
                <select id="barangaySelect" class="form-select" name="barangayCode" required>
                    <option value="" disabled selected>Select Barangay</option>
                </select>
            </div>

            <input type="hidden" name="provinceName" id="provinceName">
            <input type="hidden" name="municipalityName" id="municipalityName">
            <input type="hidden" name="barangayName" id="barangayName">


            <div class="col-md-3 mb-3">
              <label class="form-label">Mother's Name</label>
              <input type="text" class="form-control" name="mothersName" id="mothersName" required>
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Father's Name</label>
              <input type="text" class="form-control" name="fathersName" id="fathersName" required>
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Guardian</label>
              <input type="text" class="form-control" name="guardian" id="guardian">
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Parents/Guardian Contact No.</label>
              <input type="tel" class="form-control" name="parentsNum" id="parentsNum" required>
            </div>

            <input type="hidden" name="userId" value="<?= htmlspecialchars($userId) ?>">
            <input type="hidden" name="role" value="<?= htmlspecialchars($role) ?>">

            <div class="d-flex justify-content-end">
              <button type="submit" name="student" class="btn btn-primary">Submit</button>
            </div>
          </div>
        </form>
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

    </script>