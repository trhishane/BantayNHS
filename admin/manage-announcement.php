<?php 
include('includes/links.php');
include('includes/sidebar.php');
include('includes/dbconn.php');

$recordsPerPage = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $recordsPerPage;
$searchQuery = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$totalRecordsQuery = "SELECT COUNT(*) AS total FROM tblannouncement WHERE status = 'active'";
if ($searchQuery) {
    $totalRecordsQuery .= " AND title LIKE '%$searchQuery%'"; // Add search condition
}
$totalRecordsResult = mysqli_query($conn, $totalRecordsQuery);
$totalRecords = mysqli_fetch_assoc($totalRecordsResult)['total'];

$totalPages = ceil($totalRecords / $recordsPerPage);

if (isset($_GET['archived']) && $_GET['archived'] == 'success') {
    $modalMessage = 'Announcement successfully archived!';
    $modalType = 'success'; 
} elseif (isset($_GET['restored']) && $_GET['restored'] == 'success') {
    $modalMessage = 'Announcement successfully restored!'; 
    $modalType = 'success'; 
} else {
    $modalMessage = ''; 
    $modalType = '';
}
?>

<link rel="icon" type="image/x-icon" href="../assets/logo.png">
<title>Announcements | Student Portal</title>

<main id="main" class="main">

<div class="pagetitle">
  <h1>Announcements</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="admin_dashboard.php" style="text-decoration: none;">Home</a></li>
      <li class="breadcrumb-item active">Manage Announcements</li>
    </ol>
  </nav>

  <div class="card">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-end mb-1">
            <h5 class="me-auto">Announcements Details</h5>
            <div class="d-flex flex-column flex-md-row align-items-end">
                <form method="GET" class="d-flex mb-1 mb-md-0" id="searchForm">
                    <input class="form-control" type="search" name="search" placeholder="Search" value="<?= htmlspecialchars($searchQuery) ?>" style="max-width: 160px;" id="searchInput">
                </form>
                <a href="create-announce.php" class="btn btn-primary ms-md-1 mt-1 mt-md-0">
                    <i class="bi bi-plus-circle"></i> Announcements
                </a>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <label for="recordsPerPage">Show: </label>
                <select id="recordsPerPage" class="form-select d-inline-block" style="width: auto;" onchange="changeRecordsPerPage()">
                    <option value="5" <?= ($recordsPerPage == 5) ? 'selected' : '' ?>>5</option>
                    <option value="10" <?= ($recordsPerPage == 10) ? 'selected' : '' ?>>10</option>
                    <option value="20" <?= ($recordsPerPage == 20) ? 'selected' : '' ?>>20</option>
                    <option value="50" <?= ($recordsPerPage == 50) ? 'selected' : '' ?>>50</option>
                </select>
            </div>
            <a href="view-archived-announce.php" class="btn btn-secondary">
                <i class="bi bi-archive"></i> Archived
            </a>
        </div>
   
        <div class="card">
    <div class="table-responsive">
        <table class="table table-bordered text-center" style="border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="background-color: black; color: white; border: none;">No.</th>
                    <th style="background-color: black; color: white; border: none;">Title</th>
                    <th style="background-color: black; color: white; border: none;">Content/Image</th>
                    <th style="background-color: black; color: white; border: none;">Event Date</th>
                    <th style="background-color: black; color: white; border: none;">Expire Date</th>
                    <th style="background-color: black; color: white; border: none;">Date Posted</th>
                    <th style="background-color: black; color: white; border: none;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $sql = "SELECT * FROM tblannouncement WHERE status = 'active' ORDER BY date_posted DESC LIMIT $start, $recordsPerPage";
            if ($searchQuery) {
                $sql = "SELECT * FROM tblannouncement WHERE status = 'active' AND title LIKE '%$searchQuery%' ORDER BY date_posted DESC LIMIT $start, $recordsPerPage";
            }
            $sql_run = mysqli_query($conn, $sql);

            if (mysqli_num_rows($sql_run) > 0) {
                $counter = $start + 1; 
                while ($row = mysqli_fetch_assoc($sql_run)) {
                    $announcementId = $row['announcementId'];
                    $title = $row['title'];
                    $content = $row['content'];
                    $event_start_date = $row['event_start_date'];
                    $event_end_date = $row['event_end_date'];
                    $expire_date = $row['expire_date'];
                    $date_posted = $row['date_posted'];

                    echo "
                    <tr>
                        <td>$counter</td>
                        <td>$title</td>
                        <td>";
                    if (str_ends_with($content, '.jpg') || str_ends_with($content, '.jpeg') || str_ends_with($content, '.png')) {
                        echo "<img src='uploads/$content' alt='Announcement Image' style='max-width: 200px;'>";
                    } else {
                        echo $content;
                    }
                    echo "</td>
                        <td>$event_start_date - $event_end_date</td> <!-- Updated event date display -->
                        <td>$expire_date</td>
                        <td>$date_posted</td>
                        <td>
                            <a href='edit-announce.php?announcementId=$announcementId' class='btn btn-success btn-sm' title='Edit Information'>
                                <i class='bi bi-pencil-square'></i>
                            </a>
                            <a href='archived-announce.php?announcementId=$announcementId' class='btn btn-warning btn-sm' title='Archive Information'>
                                <i class='bi bi-archive'></i>
                            </a>
                        </td>
                    </tr>
                    ";
                    $counter++; 
                }
            } else {
                echo "
                <tr id='noDetailsRow'>
                    <td colspan='7' class='text-center'>No announcements found</td>
                </tr>";
            }
            ?>
            </tbody>
        </table>
    </div>



        <!-- Pagination -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>&limit=<?= $recordsPerPage ?>&search=<?= urlencode($searchQuery) ?>">Previous</a></li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&limit=<?= $recordsPerPage ?>&search=<?= urlencode($searchQuery) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>&limit=<?= $recordsPerPage ?>&search=<?= urlencode($searchQuery) ?>">Next</a></li>
                <?php endif; ?>
            </ul>
        </nav>

    </div>
  </div>
</div>

<!-- Modal for Success or Error -->
<?php if (!empty($modalMessage)) : ?>
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-body text-center p-lg-4">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="50" height="50" fill="<?php echo $modalType === 'success' ? '#198754' : '#dc3545'; ?>">
                    <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"/>
                </svg>
                <h4 class="text-<?php echo $modalType; ?> mt-3">
                    <?php echo ucfirst($modalType); ?>
                </h4>
                <p class="mt-3 fs-5">
                    <?php echo $modalMessage; ?>
                </p>
                <button type="button" class="btn btn-<?php echo $modalType; ?> btn-lg mt-3" data-bs-dismiss="modal" aria-label="Close">Ok</button>
            </div>
        </div>
    </div>
</div>
<script>
    const modal = new bootstrap.Modal(document.getElementById('successModal'));
    modal.show();
</script>
<?php endif; ?>

</main>



<script>
    function changeRecordsPerPage() {
        const limit = document.getElementById('recordsPerPage').value;
        const search = document.getElementById('searchInput').value;
        window.location.href = "?page=1&limit=" + limit + "&search=" + encodeURIComponent(search);
    }
function changeRecordsPerPage() {
    let selectedValue = document.getElementById('recordsPerPage').value;
    let currentSearch = "<?= urlencode($searchQuery) ?>";
    window.location.href = "?page=1&limit=" + selectedValue + "&search=" + currentSearch;
}

let debounceTimeout;

document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(function() {
      document.getElementById('searchForm').submit();
    }, 500); 
});

</script>
