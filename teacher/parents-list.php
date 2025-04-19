<?php
session_start();
include '../includes/dbconn.php'; 

if (!isset($_SESSION['userId'])) {
    header('Location: ../index.php');
    exit();
}

$teacherId = $_SESSION['userId'];

$selectedSubject = null;
$selectedSection = null;

if (isset($_GET['subject'])) {
    list($selectedSubject, $selectedSection) = explode('_', $_GET['subject']);
} else {
    header('Location: view-parents.php');
    exit();
}

$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

$itemsPerPage = isset($_GET['recordsPerPage']) ? (int)$_GET['recordsPerPage'] : 10;

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $itemsPerPage;

$startNumber = ($page - 1) * $itemsPerPage + 1;

$searchTermLike = '%' . $searchTerm . '%';

$query = "
  SELECT 
    p.contactNumber, 
    p.email, 
    u.firstName AS parentFirstName, 
    u.middleName AS parentMiddleName, 
    u.lastName AS parentLastName, 
    stu.firstName AS studentFirstName, 
    stu.middleName AS studentMiddleName, 
    stu.lastName AS studentLastName, 
    sec.gradeLevel, 
    sec.strand, 
    sec.sectionName
FROM tblparentinfo p
INNER JOIN tblparent_student ps ON p.parentId = ps.parentId
INNER JOIN tblstudentinfo s ON ps.studentId = s.studentId
INNER JOIN tblusersaccount stu ON s.userId = stu.userId
INNER JOIN tblusersaccount u ON p.userId = u.userId  -- Join parent user account for name search
INNER JOIN tblsection sec ON s.sectionId = sec.sectionId
INNER JOIN tblsubject subj ON subj.sectionId = sec.sectionId
WHERE subj.subjectName = ? 
AND sec.sectionName = ? 
AND (
    stu.firstName LIKE ? OR stu.lastName LIKE ? OR stu.middleName LIKE ?  -- Searching by student name
    OR u.firstName LIKE ? OR u.lastName LIKE ? OR u.middleName LIKE ?     -- Searching by parent name
)
ORDER BY stu.lastName ASC, stu.firstName ASC, stu.middleName ASC, 
         u.lastName ASC, u.firstName ASC, u.middleName ASC  -- Order by student first, then parent
LIMIT ?, ?;

";

$stmt = $conn->prepare($query);

$stmt->bind_param(
    'ssssssssii',
    $selectedSubject,
    $selectedSection,
    $searchTermLike,
    $searchTermLike,
    $searchTermLike,
    $searchTermLike,
    $searchTermLike,
    $searchTermLike,
    $offset,
    $itemsPerPage
);

$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt->close();


$query = "
    SELECT COUNT(*) 
    FROM tblparentinfo p
    INNER JOIN tblparent_student ps ON p.parentId = ps.parentId
    INNER JOIN tblstudentinfo s ON ps.studentId = s.studentId
    INNER JOIN tblusersaccount stu ON s.userId = stu.userId
    INNER JOIN tblsection sec ON s.sectionId = sec.sectionId
    INNER JOIN tblsubject subj ON subj.sectionId = sec.sectionId
    WHERE subj.subjectName = ? 
    AND sec.sectionName = ? 
    AND (stu.firstName LIKE ? OR stu.lastName LIKE ? OR stu.middleName LIKE ?)
";

$stmt = $conn->prepare($query);
$stmt->bind_param("sssss", $selectedSubject, $selectedSection, $searchTermLike, $searchTermLike, $searchTermLike);
$stmt->execute();
$stmt->bind_result($totalStudents);
$stmt->fetch();
$stmt->close();


$totalPages = ceil($totalStudents / $itemsPerPage);

$query = "SELECT gradeLevel, strand FROM tblsection WHERE sectionName = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $selectedSection);
$stmt->execute();
$stmt->bind_result($gradeLevel, $strand);
$stmt->fetch();
$stmt->close();
?>
<?php include('includes/sidebar.php'); include('includes/links.php'); ?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Students List</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item active">Students List</li>
            </ol>
        </nav>
    </div>

    <div class="card mb-5">
        <div class="card-body">
            <h3 class="card-title text-center">
                Parent List of Grade <?php echo htmlspecialchars($gradeLevel) . ' - ' . htmlspecialchars($strand) . ' (' . htmlspecialchars($selectedSection) . ')'; ?>
            </h3>
            <p class="text-center">Subject: <strong><?php echo htmlspecialchars($selectedSubject); ?></strong></p>
           
            <div class="d-flex justify-content-between align-items-center mb-3">                
              
                <form class="d-flex mb-0" method="GET" action="">
                    <input type="hidden" name="subject" value="<?php echo htmlspecialchars($selectedSubject . '_' . $selectedSection); ?>">
                    <div class="d-flex align-items-center">
                        <label for="recordsPerPage" class="me-2">Show: </label>
                        <select id="recordsPerPage" class="form-select d-inline-block" style="width: auto;" onchange="changeRecordsPerPage()">
                            <option value="5" <?php echo ($itemsPerPage == 5) ? 'selected' : ''; ?>>5</option>
                            <option value="10" <?php echo ($itemsPerPage == 10) ? 'selected' : ''; ?>>10</option>
                            <option value="20" <?php echo ($itemsPerPage == 20) ? 'selected' : ''; ?>>20</option>
                            <option value="50" <?php echo ($itemsPerPage == 50) ? 'selected' : ''; ?>>50</option>
                        </select>
                    </div>
                </form>
             
                <form class="d-flex mb-0" method="GET" action="" id="searchForm">
                <input type="hidden" name="subject" value="<?php echo htmlspecialchars($selectedSubject . '_' . $selectedSection); ?>">
                <div class="input-group" style="max-width: 200px;">
                    <span class="input-group-text bg-white border-end-0" id="searchIcon">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" id="searchInput" class="form-control" name="search" placeholder="Search" value="<?php echo htmlspecialchars($searchTerm); ?>">
                    
                </div>
            </form>

            </div>

            <div id="printable-content">
                <table id="student-list-table" class="table table-striped">
                    <thead style="background-color: transparent;">
                        <tr>
                            <th style="background-color: black; color: white; border: none;">No.</th>
                            <th style="background-color: black; color: white; border: none;">Parent Name</th>
                            <th style="background-color: black; color: white; border: none;">Student Name</th>
                            <th style="background-color: black; color: white; border: none;">Contact Number</th>
                            <th style="background-color: black; color: white; border: none;">Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $index => $student): ?>
                            <tr>
                                <td><?php echo $startNumber + $index; ?></td>
                                <td><?php echo htmlspecialchars($student['parentFirstName']) . ' ' . htmlspecialchars($student['parentLastName']); ?></td>
                                <td>
                                    <?php 
                                    echo htmlspecialchars($student['studentLastName']) . ', ' . htmlspecialchars($student['studentFirstName']);
                                    
                                    if (!empty($student['studentMiddleName']) && strtoupper($student['studentMiddleName']) !== 'N/A') {
                                        echo ' ' . htmlspecialchars($student['studentMiddleName']);
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($student['contactNumber']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav>
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?subject=<?php echo urlencode($selectedSubject . '_' . $selectedSection); ?>&page=<?php echo $page - 1; ?>&recordsPerPage=<?php echo $itemsPerPage; ?>&search=<?php echo urlencode($searchTerm); ?>">Previous</a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?subject=<?php echo urlencode($selectedSubject . '_' . $selectedSection); ?>&page=<?php echo $i; ?>&recordsPerPage=<?php echo $itemsPerPage; ?>&search=<?php echo urlencode($searchTerm); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?subject=<?php echo urlencode($selectedSubject . '_' . $selectedSection); ?>&page=<?php echo $page + 1; ?>&recordsPerPage=<?php echo $itemsPerPage; ?>&search=<?php echo urlencode($searchTerm); ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>

        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function changeRecordsPerPage() {
        var recordsPerPage = document.getElementById("recordsPerPage").value;
        var currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('recordsPerPage', recordsPerPage);
        window.location.href = currentUrl.toString();
    }
let debounceTimeout;

document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(function() {
        document.getElementById('searchForm').submit();  
    }, 500); 
});
</script>

</body>
</html>
