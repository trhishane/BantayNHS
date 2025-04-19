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
    header('Location: view-student-list.php');
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
        u.firstName, 
        u.middleName, 
        u.lastName, 
        u.suffixName, 
        s.studentId,
        s.sex
    FROM tblstudentinfo s
    INNER JOIN tblusersaccount u ON s.userId = u.userId
    INNER JOIN tblsection sec ON s.sectionId = sec.sectionId
    INNER JOIN tblsubject subj ON subj.sectionId = sec.sectionId
    WHERE subj.subjectName = ? AND sec.sectionName = ? 
    AND (u.firstName LIKE ? OR u.lastName LIKE ? OR s.studentId LIKE ?)
    ORDER BY s.sex DESC, u.lastName, u.firstName, u.middleName
    LIMIT ?, ?
";


$stmt = $conn->prepare($query);

$stmt->bind_param('sssssii', $selectedSubject, $selectedSection, $searchTermLike, $searchTermLike, $searchTermLike, $offset, $itemsPerPage);

$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt->close();


$query = "
    SELECT COUNT(*) 
    FROM tblstudentinfo s
    INNER JOIN tblusersaccount u ON s.userId = u.userId
    INNER JOIN tblsection sec ON s.sectionId = sec.sectionId
    INNER JOIN tblsubject subj ON subj.sectionId = sec.sectionId
    WHERE subj.subjectName = ? AND sec.sectionName = ? 
    AND (u.firstName LIKE ? OR u.lastName LIKE ? OR s.studentId LIKE ?)
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
                Class List of Grade <?php echo htmlspecialchars($gradeLevel) . ' - ' . htmlspecialchars($strand) . ' (' . htmlspecialchars($selectedSection) . ')'; ?>
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
            <!-- Male Students Section -->
            <tr><td colspan="3" class="text-start fw-bold ">Male</td></tr>
            <tr>
                <th style="background-color: black; color: white; border: none;">No.</th>
                <th style="background-color: black; color: white; border: none;">Student ID</th>
                <th style="background-color: black; color: white; border: none;">Student Name</th>
            </tr>
        </thead>
        
        <tbody>
            <?php 
            $maleCount = 1;
            foreach ($students as $student): 
                if (strtolower($student['sex']) == 'male'):
            ?>
                <tr>
                    <td><?php echo $maleCount++; ?></td>
                    <td><?php echo htmlspecialchars($student['studentId']); ?></td>
                    <td>
                        <?php 
                        echo htmlspecialchars($student['lastName']) . ', ' . htmlspecialchars($student['firstName']);
                        
                        if (!empty($student['middleName']) && strtoupper($student['middleName']) !== 'N/A') {
                            echo ' ' . htmlspecialchars($student['middleName']);
                        }

                        if (!empty($student['suffixName']) && strtoupper($student['suffixName']) !== 'N/A') {
                            echo ' ' . htmlspecialchars($student['suffixName']);
                        }
                        ?>
                    </td>
                </tr>
            <?php 
                endif;
            endforeach; 
            ?>
        </tbody>

        <thead style="background-color: transparent;">
            <!-- Female Students Section -->
            <tr><td colspan="3" class="text-start fw-bold ">Female</td></tr>
            <tr>
                <th style="background-color: black; color: white; border: none;">No.</th>
                <th style="background-color: black; color: white; border: none;">Student ID</th>
                <th style="background-color: black; color: white; border: none;">Student Name</th>
            </tr>
        </thead>

        <tbody>
            <?php 
            $femaleCount = 1;
            foreach ($students as $student): 
                if (strtolower($student['sex']) == 'female'):
            ?>
                <tr>
                    <td><?php echo $femaleCount++; ?></td>
                    <td><?php echo htmlspecialchars($student['studentId']); ?></td>
                    <td>
                        <?php 
                        echo htmlspecialchars($student['lastName']) . ', ' . htmlspecialchars($student['firstName']);
                        
                        if (!empty($student['middleName']) && strtoupper($student['middleName']) !== 'N/A') {
                            echo ' ' . htmlspecialchars($student['middleName']);
                        }

                        if (!empty($student['suffixName']) && strtoupper($student['suffixName']) !== 'N/A') {
                            echo ' ' . htmlspecialchars($student['suffixName']);
                        }
                        ?>
                    </td>
                </tr>
            <?php 
                endif;
            endforeach; 
            ?>
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
    }, 500); // 500ms delay
});
</script>

</body>
</html>
