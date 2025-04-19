<?php 
include('includes/links.php');
include('includes/sidebar.php');
include('includes/dbconn.php');

$showModal = false; // Initialize modal flag

if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $content = $_POST['content'] ?? '';
    $date_posted = date('Y-m-d H:i:s');

    // Get event start and end dates
    $event_start_date = $_POST['event_start_date'];
    $event_end_date = $_POST['event_end_date'];

    // Ensure end date is not before start date
    if (strtotime($event_end_date) < strtotime($event_start_date)) {
        $modalMessage = "Error: End date cannot be before start date.";
        $modalType = "error";
        $showModal = true;
    } else {
        $expire_date = date('Y-m-d', strtotime($event_end_date . ' +1 day')); // Auto-expire one day after event end
        $status = 'active';

        // File Upload Handling
        if (!empty($_FILES['image']['name'])) {
            $imageName = $_FILES['image']['name'];
            $imageTmpName = $_FILES['image']['tmp_name'];
            $uploadDir = "uploads/";

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $filePath = $uploadDir . basename($imageName);

            if (move_uploaded_file($imageTmpName, $filePath)) {
                $content = $imageName;
            } else {
                $modalMessage = "Failed to upload image.";
                $modalType = "error";
                $showModal = true;
            }
        }

        // Insert into Database
        $sql = "INSERT INTO tblannouncement (title, content, date_posted, event_start_date, event_end_date, expire_date, status) 
                VALUES ('$title', '$content', '$date_posted', '$event_start_date', '$event_end_date', '$expire_date', '$status')";
        $sql_run = mysqli_query($conn, $sql);

        if ($sql_run) {
            $modalMessage = "Posted successfully!";
            $modalType = "success";
            $showModal = true;
        } else {
            $modalMessage = "Error: " . mysqli_error($conn);
            $modalType = "error";
            $showModal = true;
        }
    }
}

// Auto-archive expired announcements
mysqli_query($conn, "UPDATE tblannouncement SET status = 'archived' WHERE expire_date < CURDATE() AND status = 'active'");
?>

<link rel="icon" type="image/x-icon" href="../assets/logo.png">
<title>Create Announcement | Student Portal</title> 

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Add Announcement</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="admin_dashboard.php">Home</a></li>
                <li class="breadcrumb-item active">Add Announcement</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">New Announcement</h5>
            <div class="d-flex justify-content-center">
                <form action="" method="POST" enctype="multipart/form-data" class="w-75">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="event_start_date" class="form-label">Event Start Date</label>
                        <input type="date" class="form-control" id="event_start_date" name="event_start_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="event_end_date" class="form-label">Event End Date</label>
                        <input type="date" class="form-control" id="event_end_date" name="event_end_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Content (Optional)</label>
                        <textarea class="form-control" id="content" name="content" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Image (Optional)</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/jpeg">
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" name="submit" class="btn btn-primary w-50">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<!-- Success/Error Modal -->
<?php if ($showModal): ?>
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
                <button type="button" class="btn btn-<?php echo $modalType === 'success' ? 'success' : 'danger'; ?> btn-lg mt-3" data-bs-dismiss="modal" onclick="redirectToManage()">OK</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Auto-show Modal and Redirect if Successful -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($showModal): ?>
            var modal = new bootstrap.Modal(document.getElementById('resultModal'));
            modal.show();
        <?php endif; ?>
    });

    function redirectToManage() {
        window.location.href = "manage-announcement.php";
    }
</script>
