<link rel="icon" type="image/x-icon" href="../assets/logo.png">
<title>Edit Announcement | Student Portal</title> 

<?php
include('includes/dbconn.php');
include('includes/sidebar.php');
include('includes/links.php');

if (isset($_GET['announcementId'])) {
    $announcementId = $_GET['announcementId'];

    $query = "SELECT * FROM tblannouncement WHERE announcementId='$announcementId'";
    $query_run = mysqli_query($conn, $query);

    if (mysqli_num_rows($query_run) > 0) {
        $row = mysqli_fetch_assoc($query_run);

        $title = $row['title'];
        $content = $row['content'];
    }


    if (isset($_POST['update'])) {
        $title = $_POST['title'];
        $content = $_POST['content'];

        $update_query = "UPDATE tblannouncement SET title='$title', content='$content' WHERE announcementId='$announcementId'";
        $update_run = mysqli_query($conn, $update_query);

    if ($update_run) {
        $modalMessage = "Updated successfully!";
        $modalType = "success";
    } else {
        $modalMessage = "Error: " . mysqli_error($conn);
        $modalType = "error";
    }
}
}
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Edit Announcement</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="admin_dashboard.php" style="text-decoration: none;">Home</a></li>
                <li class="breadcrumb-item"><a href="manage-announcement.php" style="text-decoration: none;">Manage Announcements</a></li>
                <li class="breadcrumb-item active">Edit Announcement</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Announcement Details</h5>
            <div class="d-flex justify-content-center">
            <!-- Edit Announcement Form -->
            <form action="" method="POST" class="w-75">
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?php echo $title; ?>"required>
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">Content</label>
                    <textarea class="form-control" id="content" name="content" rows="4" required><?php echo $content; ?></textarea>
                </div>

                <a href="manage-announcement.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Back</a>
                <button type="submit" name="update" class="btn btn-primary">Update</button>
            </form>
        </div>
        </div>
    </div>

        <!-- Custom Bootstrap Modal -->
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

        <script>
        function resetForm() {
            <?php if (isset($modalType) && $modalType === 'success') { ?>
                window.location.href = "manage-announcement.php";
            <?php } ?>
        }

        <?php if (isset($modalMessage)) { ?>
            var resultModal = new bootstrap.Modal(document.getElementById('resultModal'));
            resultModal.show();
        <?php } ?>
        </script>
    </main>
</body>
</html>

