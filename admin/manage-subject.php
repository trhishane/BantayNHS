<link rel="icon" type="image/x-icon" href="../assets/logo.png">
<title>Subjects | Student Portal</title> 

<?php
include('includes/links.php');
include('includes/sidebar.php');
include('includes/dbconn.php');

$recordsPerPage = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $recordsPerPage;
$searchQuery = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$sqlCount = "SELECT COUNT(*) AS total FROM tblsubject AS s
JOIN tblsection AS sec ON sec.sectionId = s.sectionId
JOIN tblusersaccount AS u ON u.userId = s.userId
WHERE s.subjectName LIKE '%$searchQuery%' OR sec.sectionName LIKE '%$searchQuery%' OR u.firstName LIKE '%$searchQuery%'";

$resultCount = mysqli_query($conn, $sqlCount);
$rowCount = mysqli_fetch_assoc($resultCount);
$totalRecords = $rowCount['total'];

$totalPages = ceil($totalRecords / $recordsPerPage);
?>

<main id="main" class="main">

<div class="pagetitle">
  <h1>Manage Subjects</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="admin_dashboard.php" style="text-decoration: none;">Home</a></li>
      <li class="breadcrumb-item active">Manage Subjects</li>
    </ol>
  </nav>

  <div class="card">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-end mb-3">
            <h5 class="me-auto">Subject Details</h5>
            <div class="d-flex flex-column flex-md-row align-items-end">
                <form method="GET" class="d-flex mb-1 mb-md-0" id="searchForm">
                    <input class="form-control" type="search" name="search" placeholder="Search" value="<?= htmlspecialchars($searchQuery) ?>" style="max-width: 160px;" id="searchInput">
                </form>
                <a href="create-subject.php" class="btn btn-primary ms-md-1 mt-1 mt-md-0">
                    <i class="bi bi-plus-circle"></i> Add Subject
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
        </div>
        <div class="card-responsive">
            <table class="table table-bordered text-center" id="subjectTable" style="border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="background-color: black; color: white; border: none;">No.</th>
                        <th style="background-color: black; color: white; border: none;">Subject Name</th>
                        <th style="background-color: black; color: white; border: none;">Subject Type</th>
                        <th style="background-color: black; color: white; border: none;">Section, Grade & Strand</th>
                        <th style="background-color: black; color: white; border: none;">Subject Teacher</th>
                        <th style="background-color: black; color: white; border: none;">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $sql = "SELECT s.subjectId, s.subjectName, s.semester, s.subjectType, sec.sectionName, sec.gradeLevel, sec.strand, 
                u.firstName, u.middleName, u.lastName
                FROM tblsubject AS s
                JOIN tblsection AS sec ON sec.sectionId = s.sectionId
                JOIN tblusersaccount AS u ON u.userId = s.userId
                WHERE s.subjectName LIKE '%$searchQuery%' OR sec.sectionName LIKE '%$searchQuery%' OR u.firstName LIKE '%$searchQuery%'
                LIMIT $start, $recordsPerPage";
                
                $sql_run = mysqli_query($conn, $sql);
                $counter = $start + 1; // Initialize row numbering

                if ($sql_run) {
                    if (mysqli_num_rows($sql_run) > 0) {
                        while ($row = mysqli_fetch_assoc($sql_run)) {
                            $subjectId = $row['subjectId'];
                            $subjectName = $row['subjectName'];
                            $semester = $row['semester'];
                            $subjectType = $row['subjectType'];
                            $sectionName = $row['sectionName'];
                            $gradeLevel = $row['gradeLevel'];
                            $strand = $row['strand'];
                            $firstName = $row['firstName'];
                            $middleName = $row['middleName'];
                            $lastName = $row['lastName'];

                            echo "
                              <tr>
                                <td>$counter</td>
                                <td class='text-start'>$subjectName</td>
                                <td class='text-start'>$subjectType</td>
                                <td class='text-start'>$sectionName - Grade $gradeLevel ($strand)</td>
                                <td class='text-start'>$firstName $middleName $lastName</td>
                                <td>
                                  <a href='edit-subject.php?subjectId=$subjectId' class='btn btn-success btn-sm' title='Edit Information'>
                                    <i class='bi bi-pencil-square'></i>
                                  </a>
                                  <a href='delete-subject.php?subjectId=$subjectId' class='btn btn-danger btn-sm' title='Delete Information' onclick='return confirm(\"Are you sure you want to delete this subject?\")'>
                                    <i class='bi bi-trash'></i>
                                  </a>
                                </td>
                              </tr>
                            ";
                            $counter++; 
                        }
                    }
                }
                ?>
                <tr id="noDetailsRow" style="display: none;">
                    <td colspan="6">No details found</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
  </div>
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
</script>
