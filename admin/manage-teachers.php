<link rel="icon" type="image/x-icon" href="../assets/logo.png">
<title>Teachers | Student Portal</title>

<?php
include('includes/links.php');
include('includes/sidebar.php');
include('includes/dbconn.php');

$recordsPerPage = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $recordsPerPage;
$searchQuery = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$sql = "SELECT * FROM tblusersaccount WHERE role = 'Teacher' AND archived = 0";
if ($searchQuery) {
    $sql .= " AND (firstName LIKE '%$searchQuery%' OR lastName LIKE '%$searchQuery%' OR username LIKE '%$searchQuery%')";
}
$sql .= " ORDER BY accountCreatedDate DESC LIMIT $start, $recordsPerPage";
$result = mysqli_query($conn, $sql);

$totalRecordsQuery = "SELECT COUNT(*) as total FROM tblusersaccount WHERE role = 'Teacher' AND archived = 0";
if ($searchQuery) {
    $totalRecordsQuery .= " AND (firstName LIKE '%$searchQuery%' OR lastName LIKE '%$searchQuery%' OR username LIKE '%$searchQuery%')";
}
$totalRecordsResult = mysqli_query($conn, $totalRecordsQuery);
$totalRecords = mysqli_fetch_assoc($totalRecordsResult)['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);
?>

<main id="main" class="main">

<div class="pagetitle">
  <h1>Teachers</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="admin_dashboard.php" style="text-decoration: none;">Home</a></li>
      <li class="breadcrumb-item">Teachers</li>
      <li class="breadcrumb-item active">View Teachers</li>
    </ol>
  </nav>
</div>

<div class="card">
  <div class="card-body">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-end mb-2">
      <h5 class="me-auto">Account Details</h5>
      <div class="d-flex flex-column flex-md-row align-items-end">
        <form method="GET" class="d-flex mb-1 mb-md-0" id="searchForm">
          <input class="form-control" type="search" name="search" placeholder="Search" value="<?= htmlspecialchars($searchQuery) ?>" style="max-width: 160px;" id="searchInput">
        </form>
        <a href="create-teacher-acc.php" class="btn btn-primary ms-md-1 mt-1 mt-md-0">
          <i class="bi bi-plus-circle"></i> Account
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
      <a href="view-archived.php" class="btn btn-secondary">
        <i class="bi bi-archive"></i> Archived Accounts
      </a>
    </div>

    <div class="table-responsive">
    <table class="table table-bordered text-center" style="border-collapse: collapse;">
    <thead>
        <tr>
            <th style="background-color: black; color: white; border: none;">No.</th>
            <th style="background-color: black; color: white; border: none;">User ID</th>
            <th style="background-color: black; color: white; border: none;">Full Name</th>
            <th style="background-color: black; color: white; border: none;">Username</th>
            <th style="background-color: black; color: white; border: none;">Password</th>
            <th style="background-color: black; color: white; border: none;">Date Created</th>
            <th style="background-color: black; color: white; border: none;">Action</th>
        </tr>
    </thead>

        <tbody>
          <?php if (mysqli_num_rows($result) > 0): ?>
            <?php $serialNo = $start + 1; ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
              <?php   
                $userId = $row['userId'];
                $username = $row['username'];
                $password = $row['password']; 
                $firstName = $row['firstName'];
                $middleName = $row['middleName'] !== 'N/A' ? $row['middleName'] : ''; 
                $lastName = $row['lastName'];
                $suffixName = $row['suffixName'] !== 'N/A' ? $row['suffixName'] : ''; 
                $accountCreatedDate = date('M d, Y - h:i A', strtotime($row['accountCreatedDate']));
                $fullName = trim("$firstName $middleName $lastName $suffixName");
              ?>

              <tr>
              <td  style="width: 50px;"><?= $serialNo++ ?></td>
              <td  style="width: 100px;"><?= $userId ?></td>
              <td class='text-start' style="width: 250px;"><?= $fullName ?></td>
              <td class='text-start' style="width: 150px;"><?= $username ?></td>
              <td style="width: 130px;">*********</td>
              <td style="width: 180px;"><?= $accountCreatedDate ?></td>
              <td style="width: 150px;">
                  <a href='edit-teacher-info.php?userId=<?= $userId ?>' class='btn btn-success btn-sm mb-1' title='Edit Account'>
                    <i class='bi bi-pencil-square'></i>
                  </a>
                  <a href='archived-user-info.php?userId=<?= $userId ?>&archived=true' class='btn btn-danger btn-sm mb-1' title='Archive Account'>
                    <i class='bi bi-archive'></i>
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="6">No students found.</td></tr>
          <?php endif; ?>
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

<!-- Modal for Successful Archive -->
<div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-body text-center p-lg-4">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="50" height="50" fill="<?php echo isset($_GET['archived']) && $_GET['archived'] === 'success' ? '#198754' : '#dc3545'; ?>">
                    <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"/>
                </svg>
                <h4 class="text-<?php echo isset($_GET['archived']) && $_GET['archived'] === 'success' ? 'success' : 'danger'; ?> mt-3">
                    <?php echo isset($_GET['archived']) && $_GET['archived'] === 'success' ? 'Success' : 'Error'; ?>
                </h4>
                <p class="mt-3 fs-5">
                    <?php echo isset($_GET['archived']) && $_GET['archived'] === 'success' ? 'The account has been successfully archived.' : 'An error occurred while archiving the account.'; ?>
                </p>
                <button type="button" class="btn btn-<?php echo isset($_GET['archived']) && $_GET['archived'] === 'success' ? 'success' : 'danger'; ?> btn-lg mt-3" data-bs-dismiss="modal" onclick="resetForm()">OK</button>
            </div>
        </div>
    </div>
</div>

</main>

<script>
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

<?php if (isset($_GET['archived']) && $_GET['archived'] === 'success'): ?>
  var myModal = new bootstrap.Modal(document.getElementById('resultModal'));
  myModal.show();
<?php endif; ?>
</script>