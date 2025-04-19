<?php
include('links.php');
?>


  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="admin_dashboard.php" class="logo d-flex align-items-center">
        <img src="../assets/System Images/logo.png" alt="">
        <span class="d-none d-lg-block">eSkwela</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <nav class="header-nav ms-auto">
  <ul class="d-flex align-items-center">
    <li class="nav-item dropdown pe-2">
      <a class="nav-link nav-profile d-flex align-items-center " href="#" data-bs-toggle="dropdown">
        <i class="bi bi-person-circle fs-4"></i>
        <span class="d-none d-md-block dropdown-toggle ps-2" style="font-size: 20px; margin-right: 5px;">Admin</span>
      </a><!-- End Profile Icon -->

      <ul class="dropdown-menu dropdown-menu-lg-end dropdown-menu-arrow mt-1 " >
       
        
        <li>
          <a class="dropdown-item d-flex align-items-center text-danger p-1 ms-4" href="../login/logout.php">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
          </a>
        </li>
        
      </ul><!-- End Profile Dropdown Items -->
    </li><!-- End Profile Nav -->
  </ul>
</nav>


  </header><!-- End Header -->
