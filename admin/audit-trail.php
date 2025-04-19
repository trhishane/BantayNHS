<?php
session_start();
include('includes/dbconn.php');
include('includes/links.php');
include('includes/sidebar.php');

$recordsPerPage = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $recordsPerPage;

// Get total record count
$totalRecordsQuery = "SELECT COUNT(*) AS total FROM tblaudit_trail";
$totalRecordsResult = mysqli_query($conn, $totalRecordsQuery);
$totalRecords = mysqli_fetch_assoc($totalRecordsResult)['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Fetch paginated audit trail data
$sql = "SELECT name, role, action, timestamp FROM tblaudit_trail ORDER BY timestamp DESC LIMIT $start, $recordsPerPage";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Audit Trail | Student Portal</title>
</head>
<body>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Audit Trail</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php" style="text-decoration: none;">Home</a></li>
                <li class="breadcrumb-item active">Audit Trail</li>
            </ol>
        </nav>
    </div>

    <div class="container-fluid">
        <div class="card mb-5">
            <div class="card-body">
                <h2>Audit Trail</h2>
                <div class="mb-2">
                    <label for="recordsPerPage">Show: </label>
                    <select id="recordsPerPage" class="form-select d-inline-block" style="width: auto;" onchange="changeRecordsPerPage()">
                        <option value="5" <?= ($recordsPerPage == 5) ? 'selected' : '' ?>>5</option>
                        <option value="10" <?= ($recordsPerPage == 10) ? 'selected' : '' ?>>10</option>
                        <option value="20" <?= ($recordsPerPage == 20) ? 'selected' : '' ?>>20</option>
                        <option value="50" <?= ($recordsPerPage == 50) ? 'selected' : '' ?>>50</option>
                    </select>
                </div>

                <table class="table table-bordered">
                    <thead class="text-center">
                        <tr>
                            <th style="width: 3%;">No.</th>
                            <th style="width: 15%;">Name</th>
                            <th style="width: 10%;">Role</th>
                            <th style="width: 45%;">Action</th>
                            <th style="width: 20%;">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = $start + 1; // Start counting from 1 on each page
                        while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td><?= $row['name'] ?></td>
                                <td><?= $row['role'] ?></td>
                                <td><?= $row['action'] ?></td>
                                <td><?= $row['timestamp'] ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav>
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>&limit=<?= $recordsPerPage ?>">Previous</a></li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&limit=<?= $recordsPerPage ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>&limit=<?= $recordsPerPage ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</main>

<script>
    function changeRecordsPerPage() {
        let selectedValue = document.getElementById('recordsPerPage').value;
        window.location.href = "?page=1&limit=" + selectedValue;
    }
</script>

</body>
</html>
