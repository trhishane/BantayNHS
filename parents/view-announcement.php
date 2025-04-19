<?php
include('includes/sidebar.php');
include('includes/links.php');
?>

<title>Announcements | Student Portal</title>

<main id="main" class="main">

<div class="pagetitle">
  <h1>Announcements</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="student_dashboard.php">Home</a></li>
      <li class="breadcrumb-item active">Announcements</li>
    </ol>
  </nav>
</div>

<div class="row">
  <?php
  include('includes/dbconn.php');

  $sql = "SELECT * FROM tblannouncement ORDER BY date_posted DESC";
  $sql_run = mysqli_query($conn, $sql);

  while ($row = mysqli_fetch_assoc($sql_run)) {
      $title = $row['title'];
      $content = $row['content'];
      $date_posted = $row['date_posted'];
  ?>

  <div class="col-md-4">
    <div class="card mb-4 rounded-4" style="background-color: #a7c7e7; color: #333;">
      <div class="card-body">
        <h5 class="card-title"><?php echo $title; ?></h5>
        <p class="card-text"><?php echo $content; ?></p>
        <div class="d-flex justify-content-between align-items-center">
          <small class="text-muted">Posted on: <?php echo date('F d, Y', strtotime($date_posted)); ?></small>
        </div>
      </div>
    </div>
  </div>

  <?php } ?>
</div>

</main>

