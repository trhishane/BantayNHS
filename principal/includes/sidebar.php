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

    <!-- Grades -->
    <li class="nav-item">
      <a class="nav-link <?= $current_page == 'grades' ? 'active' : 'collapsed' ?>" href="grades.php">
        <i class="bi bi-file-earmark-post"></i>
        <span>Grades</span>
      </a>
    </li><!-- End Grades Nav -->

    <!-- Reports -->
    <li class="nav-item">
      <a class="nav-link <?= $current_page == 'reports' ? 'active' : 'collapsed' ?>" href="reports.php">
        <i class="bi bi-file-earmark-bar-graph"></i>
        <span>Reports</span>
      </a>
    </li><!-- End Reports Nav -->

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
