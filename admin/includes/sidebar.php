<?php
include('header.php');
include('links.php');
?>

<?php
// Get the current page name
$current_page = basename($_SERVER['PHP_SELF'], ".php");
?>

<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">

  <ul class="sidebar-nav" id="sidebar-nav">

    <!-- Dashboard -->
    <li class="nav-item">
      <a class="nav-link <?= $current_page == 'dashboard' ? 'active' : 'collapsed' ?>" href="dashboard.php">
        <i class="bi bi-grid-fill"></i>
        <span>Dashboard</span>
      </a>
    </li><!-- End Dashboard Nav -->

    <!-- Announcements -->
    <li class="nav-item">
      <a class="nav-link <?= $current_page == 'manage-announcement' ? 'active' : 'collapsed' ?>" href="manage-announcement.php">
        <i class="fa-solid fa-bullhorn"></i>
        <span>Announcements</span>
      </a>
    </li><!-- End Announcements Nav -->

    <!-- User Management -->
    <li class="nav-item">
      <a class="nav-link <?= in_array($current_page, ['manage-students', 'manage-teachers', 'manage-parents']) ? '' : 'collapsed' ?>" 
         data-bs-target="#user-management-nav" data-bs-toggle="collapse" href="#">
        <i class="fa-solid fa-users"></i>
        <span>User Management</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="user-management-nav" class="nav-content collapse <?= in_array($current_page, ['manage-students', 'manage-teachers', 'manage-parents']) ? 'show' : '' ?>" data-bs-parent="#sidebar-nav">
        <li>
          <a class="<?= $current_page == 'manage-students' ? 'active' : '' ?>" href="manage-students.php">
            <i class="fa-solid fa-graduation-cap" style="font-size: 15px;"></i><span>Students</span>
          </a>
        </li>
        <li>
          <a class="<?= $current_page == 'manage-teachers' ? 'active' : '' ?>" href="manage-teachers.php">
            <i class="fa-solid fa-chalkboard-user" style="font-size: 15px;"></i><span>Teachers</span>
          </a>
        </li>
        <li>
          <a class="<?= $current_page == 'manage-parents' ? 'active' : '' ?>" href="manage-parents.php">
          <i class="fa-solid fa-user-friends" style="font-size: 15px;"></i><span>Parents</span>
          </a>
        </li>
      </ul>
    </li><!-- End User Management -->

    <!-- Grdaes -->
    <li class="nav-item">
      <a class="nav-link <?= $current_page == 'manage-grades' ? 'active' : 'collapsed' ?>" href="manage-grades.php">
      <i class="bi bi-folder-fill"></i>
        <span>Grades</span>
      </a>
    </li><!-- End Subjects Nav -->

    <!-- Subjects -->
    <li class="nav-item">
      <a class="nav-link <?= $current_page == 'manage-subject' ? 'active' : 'collapsed' ?>" href="manage-subject.php">
        <i class="fa-solid fa-rectangle-list"></i>
        <span>Subjects</span>
      </a>
    </li><!-- End Subjects Nav -->

    <!-- Reports -->
    <li class="nav-item">
      <a class="nav-link <?= in_array($current_page, ['form138', 'create_report']) ? '' : 'collapsed' ?>" 
         data-bs-target="#reports-nav" data-bs-toggle="collapse" href="#">
        <i class="fa-solid fa-chart-bar"></i>
        <span>Reports</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="reports-nav" class="nav-content collapse <?= in_array($current_page, ['form138', 'create_report']) ? 'show' : '' ?>" data-bs-parent="#sidebar-nav">
        <li>
          <a class="<?= $current_page == 'form138' ? 'active' : '' ?>" href="form138.php">
            <i class="fa-solid fa-file-alt" style="font-size: 15px;"></i><span>Form 138</span>
          </a>
        </li>
        <li>
          <a class="<?= $current_page == 'create_report' ? 'active' : '' ?>" href="create_report.php">
            <i class="fa-solid fa-file-alt" style="font-size: 15px;"></i><span>Class Reports</span>
          </a>
        </li>
      </ul>
    </li><!-- End Reports -->

   <!-- Settings -->
<li class="nav-item">
  <a class="nav-link <?= in_array($current_page, ['manage-schoolyear', 'audit-trail', 'grading_status', 'backup']) ? '' : 'collapsed' ?>" 
     data-bs-target="#settings-nav" data-bs-toggle="collapse" href="#">
    <i class="fa-solid fa-gear"></i>
    <span>Settings</span>
    <i class="bi bi-chevron-down ms-auto"></i>
  </a>
  <ul id="settings-nav" class="nav-content collapse <?= in_array($current_page, ['manage-schoolyear', 'audit-trail', 'grading_status', 'backup']) ? 'show' : '' ?>" data-bs-parent="#sidebar-nav">
    <li>
      <a class="<?= $current_page == 'manage-schoolyear' ? 'active' : '' ?>" href="manage-schoolyear.php">
        <i class="fa-regular fa-calendar-days" style="font-size: 15px;"></i><span>School Year</span>
      </a>
    </li>
    <li>
      <a class="<?= $current_page == 'grading_status' ? 'active' : '' ?>" href="grading_status.php">
        <i class="fa-regular fa-calendar-days" style="font-size: 15px;"></i><span>Quarter Grade</span>
      </a>
    </li>
    <li>
      <a class="<?= $current_page == 'audit-trail' ? 'active' : '' ?>" href="audit-trail.php">
        <i class="fa-solid fa-file-alt" style="font-size: 15px;"></i><span>Audit Trail</span>
      </a>
    </li>
    <li>
      <a class="<?= $current_page == 'backup' ? 'active' : '' ?>" href="backup.php">
        <i class="fa-solid fa-database" style="font-size: 15px;"></i><span>Backup</span>
      </a>
    </li>
  </ul>
</li><!-- End Settings -->


  </ul>

</aside><!-- End Sidebar -->

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
  .nav-content.show {
    display: block;
  }
  span {
    font-size: 20px;
    text-decoration: none;
  }
  .sidebar-nav a {
    text-decoration: none; 
  }

  .sidebar-nav a:hover {
    text-decoration: none; 
  }

</style>
