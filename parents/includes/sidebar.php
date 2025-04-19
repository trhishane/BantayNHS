<?php
include('links.php');
include('header.php');
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
        <span>My Profile</span>
      </a>
    </li>

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