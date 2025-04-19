<?php
include "includes/dbconn.php"; // Database connection

// Fetch current grading status
$sql = "SELECT quarter1, quarter2 FROM tblgradingstatus WHERE id = 1";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

// Assign values
$enableQuarter1 = $row['quarter1'];
$enableQuarter2 = $row['quarter2'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grading Status Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include('includes/sidebar.php'); ?>
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Enable/Disable Grading Quarter</h1>
        </div>

        <div class="card">
        <div class="card-body">
    <form id="gradingStatusForm">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="enableQuarter1" name="enableQuarter1" 
                <?php echo ($enableQuarter1 ? 'checked' : ''); ?>>
            <label class="form-check-label" for="enableQuarter1">Enable Quarter 1</label>
        </div>

        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="enableQuarter2" name="enableQuarter2" 
                <?php echo ($enableQuarter2 ? 'checked' : ''); ?>>
            <label class="form-check-label" for="enableQuarter2">Enable Quarter 2</label>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Save Changes</button>
    </form>

        </div>
        </div>

    <div id="message" class="mt-3"></div>

    <script>
    document.getElementById("gradingStatusForm").addEventListener("submit", function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        
        fetch("update_grading_status.php", {
            method: "POST",
            body: formData
        }).then(response => response.text())
          .then(data => {
              document.getElementById("message").innerHTML = `<div class='alert alert-info'>${data}</div>`;
          });
    });
    </script>
    </main>
</body>
</html>
