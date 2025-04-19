<?php
include('links.php');
include('header.php');

$userId = $_SESSION['userId']; 

$query = "SELECT isAdviser FROM tblteacherinfo WHERE userId = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $userId);
$stmt->execute();
$stmt->bind_result($isAdviser);
$stmt->fetch();
$stmt->close();
?>

<?php
$current_page = basename($_SERVER['PHP_SELF'], ".php");
?>

<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">
  <ul class="sidebar-nav" id="sidebar-nav">
    <li class="nav-item">
      <a class="nav-link <?= $current_page == 'dashboard' ? 'active' : 'collapsed' ?>" href="dashboard.php">
        <i class="bi bi-grid-fill"></i>
        <span>Dashboard</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link <?= $current_page == 'profile' ? 'active' : 'collapsed' ?>" href="profile.php">
        <i class="fa-solid fa-user"></i>
        <span>Profile</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link <?= $current_page == 'view-students-list' ? 'active' : 'collapsed' ?>" href="view-students-list.php">
        <i class="bi bi-mortarboard-fill"></i>
        <span>Students</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link <?= $current_page == 'view-parents' ? 'active' : 'collapsed' ?>" href="view-parents.php">
        <i class="bi bi-people-fill"></i>
        <span>Parents</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link <?= $current_page == 'input-grades' ? 'active' : 'collapsed' ?>" href="input-grades.php">
        <i class="bi bi-card-checklist"></i>
        <span>Grades</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link <?= $current_page == 'take-attendance' ? 'active' : 'collapsed' ?>" href="take-attendance.php">
        <i class="bi bi-calendar-check-fill"></i>
        <span>Attendance</span>
      </a>
    </li>

    <?php if ($isAdviser == 1): ?>
    <li class="nav-item">
        <a class="nav-link <?= in_array($current_page, ['form138', 'create_report']) ? '' : 'collapsed' ?>" 
           data-bs-target="#reports-nav" data-bs-toggle="collapse" href="#" style="text-decoration: none;">
            <i class="bi bi-file-earmark-text"></i>
            <span>Reports</span>
            <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="reports-nav" class="nav-content collapse <?= in_array($current_page, ['form138', 'create_report']) ? 'show' : '' ?>" data-bs-parent="#sidebar-nav">
            <li>
                <a class="<?= $current_page == 'form138' ? 'active' : '' ?>" href="form138.php" style="text-decoration: none;">
                    <i class="bi bi-file-earmark-text"></i> <span>Form 138</span>
                </a>
            </li>
            <li>
                <a class="<?= $current_page == 'create_report' ? 'active' : '' ?>" href="create_report.php" style="text-decoration: none;">
                    <i class="bi bi-card-list"></i> <span>Class List Reports</span>
                </a>
            </li>
        </ul>
    </li><!-- End Reports Section -->
<?php endif; ?>


    <li class="nav-item">
      <a class="nav-link <?= $current_page == '' ? 'active' : 'collapsed' ?>" href="../login/logout.php">
        <i class="bi bi-box-arrow-left"></i>
        <span>Logout</span>
      </a>
    </li>
  </ul>
</aside>

<style>
  .nav-content.show {
    display: block;
  }
  span{
    font-size: 20px;
  }
</style>
