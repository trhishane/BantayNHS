<link rel="icon" type="image/x-icon" href="../assets/logo.png">
<title>Archived Accounts | Student Portal</title> 

<?php 
include('includes/links.php');
include('includes/sidebar.php');
?>

<main id="main" class="main">
  <div class="pagetitle">
    <h1>Archived Accounts</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="admin_dashboard.php" style="text-decoration: none;">Home</a></li>
        <li class="breadcrumb-item active">Archived Accounts</li>
      </ol>
    </nav>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-end mb-3">
        <h5 class="me-auto">Account Details</h5>
        <form class="d-flex mb-1 mb-md-0" role="search" id="searchForm" method="GET">
          <input class="form-control" type="search" placeholder="Search" aria-label="Search" style="max-width: 160px;" name="search" id="searchInput" value="<?php echo htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES); ?>">
        </form>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered text-center" id="studentsTable">
          <thead >
            <tr>
              <th style="background-color: black; color: white; border: none;">No.</th> 
              <th style="background-color: black; color: white; border: none;" >User ID</th>
              <th style="background-color: black; color: white; border: none;">Full Name</th>
              <th style="background-color: black; color: white; border: none;">Username</th>
              <th style="background-color: black; color: white; border: none;">Role</th>
              <th style="background-color: black; color: white; border: none;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            include('includes/dbconn.php');

            $limit = 10; 
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $start = ($page - 1) * $limit;
            $searchQuery = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

            $totalQuery = "SELECT COUNT(*) as total FROM tblusersaccount WHERE archived = 1";
            if ($searchQuery) {
                $totalQuery .= " AND (firstName LIKE '%$searchQuery%' OR lastName LIKE '%$searchQuery%' OR username LIKE '%$searchQuery%')";
            }
            $totalResult = mysqli_query($conn, $totalQuery);
            $totalRecords = mysqli_fetch_assoc($totalResult)['total'];
            $totalPages = ceil($totalRecords / $limit);

            $sql = "SELECT * FROM tblusersaccount WHERE archived = 1";
            if ($searchQuery) {
                $sql .= " AND (firstName LIKE '%$searchQuery%' OR lastName LIKE '%$searchQuery%' OR username LIKE '%$searchQuery%')";
            }
            $sql .= " ORDER BY accountCreatedDate DESC LIMIT $start, $limit";
            $result = mysqli_query($conn, $sql);

            if (mysqli_num_rows($result) > 0) {
                $serialNo = $start + 1; 
                while ($row = mysqli_fetch_assoc($result)) {
                    $userId = $row['userId'];
                    $username = $row['username'];
                    $firstName = $row['firstName'];
                    $middleName = $row['middleName'] !== 'N/A' ? $row['middleName'] : ''; 
                    $lastName = $row['lastName'];
                    $suffixName = $row['suffixName'] !== 'N/A' ? $row['suffixName'] : ''; 
                    $fullName = trim("$firstName $middleName $lastName $suffixName");
                    $role = $row['role'];

                    echo "
                    <tr>
                        <td>$serialNo</td>
                        <td>$userId</td>
                        <td>$fullName</td>
                        <td>$username</td>
                        <td>$role</td>
                        <td>
                          <a href='restore.php?userId=$userId' class='btn btn-warning btn-sm' title='Restore Account'>
                            <i class='bi bi-arrow-counterclockwise'></i> Restore
                          </a>
                        </td>
                    </tr>";
                    $serialNo++; 
                }
            } else {
                echo "
                <tr>
                  <td colspan='6'>
                    <div class='alert alert-danger mb-0' role='alert'>
                      No accounts found.
                    </div>
                  </td>
                </tr>";
            }
            ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <nav>
        <ul class="pagination justify-content-center">
          <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($searchQuery); ?>" aria-label="Previous">
              <i class="bi bi-chevron-left"></i>
            </a>
          </li>
          <?php
          for ($i = 1; $i <= $totalPages; $i++) {
            echo "<li class='page-item " . ($i == $page ? 'active' : '') . "'>
                    <a class='page-link' href='?page=$i&search=" . urlencode($searchQuery) . "'>$i</a>
                  </li>";
          }
          ?>
          <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($searchQuery); ?>" aria-label="Next">
              <i class="bi bi-chevron-right"></i>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </div>

 <!-- Modal for Successful Restore -->
 <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
      <div class="modal-content">
        <div class="modal-body text-center p-lg-4">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="50" height="50" fill="<?php echo isset($_GET['restored']) && $_GET['restored'] === 'success' ? '#198754' : '#dc3545'; ?>">
            <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"/>
          </svg>
          <h4 class="text-<?php echo isset($_GET['restored']) && $_GET['restored'] === 'success' ? 'success' : 'danger'; ?> mt-3">
            <?php echo isset($_GET['restored']) && $_GET['restored'] === 'success' ? 'Success' : 'Error'; ?>
          </h4>
          <p class="mt-3 fs-5">
            <?php echo isset($_GET['restored']) && $_GET['restored'] === 'success' ? 'The account has been successfully restored.' : 'An error occurred while restoring the account.'; ?>
          </p>
          <button type="button" class="btn btn-<?php echo isset($_GET['restored']) && $_GET['restored'] === 'success' ? 'success' : 'danger'; ?> btn-lg mt-3" data-bs-dismiss="modal" id="redirectButton">OK</button>
        </div>
      </div>
    </div>
  </div>

<script>
document.getElementById('searchInput').addEventListener('input', function () {
    clearTimeout(this.debounce);
    this.debounce = setTimeout(() => {
        document.getElementById('searchForm').submit();
    }, 600);
});

window.addEventListener('load', function () {
  const searchInput = document.getElementById('searchInput');
  if (searchInput) {
    searchInput.value = ''; 
  }

    if (<?php echo isset($_GET['restored']) ? 'true' : 'false'; ?>) {
        var myModal = new bootstrap.Modal(document.getElementById('resultModal'));
        myModal.show();
    }
});

</script>

<style>
.pagination {
    margin: 0;
    padding: 0; 
}
.page-item {
    margin: 0 3px; 
}
.page-link {
    display: flex;
    justify-content: center;
    align-items: center;
    min-width: 30px; 
    height: 30px; 
    font-size: 14px; 
    padding: 0; 
    border-radius: 3px; 
}
.page-item.active .page-link {
    background-color: #0d6efd;
    color: #fff; 
}
.page-item.disabled .page-link {
    opacity: 0.5;
    pointer-events: none;
}
</style>
