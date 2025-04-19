<?php
include "includes/dbconn.php"; // Database connection

// Fetch current grading status
$sql = "SELECT quarter1, quarter2 FROM tblgradingstatus WHERE id = 1";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    $enableQuarter1 = $row['quarter1'];
    $enableQuarter2 = $row['quarter2'];
} else {
    $enableQuarter1 = 0;
    $enableQuarter2 = 0;
}

// Handle form submission (AJAX request)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $enableQuarter1 = isset($_POST['enableQuarter1']) ? 1 : 0;
    $enableQuarter2 = isset($_POST['enableQuarter2']) ? 1 : 0;

    // Update database
    $sql = "UPDATE tblgradingstatus SET quarter1 = ?, quarter2 = ?, updated_at = NOW() WHERE id = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $enableQuarter1, $enableQuarter2);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Grading status updated successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error updating grading status."]);
    }
    exit;
}

include('includes/links.php');
include('includes/sidebar.php');
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Quarter Grade Setting</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="admin_dashboard.php" style="text-decoration: none;">Home</a></li>
                <li class="breadcrumb-item active">Enable & Disable Quarter Grading</li>
            </ol>
        </nav>

        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-end mb-3">
                    <h1 class="me-auto">Enable & Disable Quarter Grading</h1>
                </div>
                <form id="gradingStatusForm">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="enableQuarter1" name="enableQuarter1"
                            <?php echo ($enableQuarter1 ? 'checked' : ''); ?> style="width: 2rem; height: 1rem;">
                        <label class="form-check-label fw-semibold" style="font-size: 17px;" for="enableQuarter1">Enable Quarter 1</label>
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="enableQuarter2" name="enableQuarter2"
                            <?php echo ($enableQuarter2 ? 'checked' : ''); ?> style="width: 2rem; height: 1rem;">
                        <label class="form-check-label fw-semibold" style="font-size: 17px;" for="enableQuarter2">Enable Quarter 2</label>
                    </div>

                    <button type="submit" class="btn btn-primary mt-2">Save Changes</button>
                </form>

                <div id="message" class="mt-3"></div>
            </div>
        </div>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
    $("#gradingStatusForm").on("submit", function(e) {
        e.preventDefault();

        $.ajax({
            type: "POST",
            url: "grading_status.php",
            data: $(this).serialize(),
            dataType: "json",
            success: function(response) {
                console.log(response);
                let messageBox = $("#message");
                messageBox.html(`<div class='alert alert-${response.status == 'success' ? 'success' : 'danger'}'>${response.message}</div>`);
                
                // Auto-hide message after 5 seconds
                setTimeout(function() {
                    messageBox.fadeOut("slow", function() {
                        $(this).html("").show(); // Reset the message box
                    });
                }, 5000);
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                let messageBox = $("#message");
                messageBox.html(`<div class='alert alert-danger'>An error occurred. Please try again.</div>`);
                
                // Auto-hide error message after 5 seconds
                setTimeout(function() {
                    messageBox.fadeOut("slow", function() {
                        $(this).html("").show(); // Reset the message box
                    });
                }, 1000);
            }
        });
    });
});

</script>
