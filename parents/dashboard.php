

<link rel="icon" type="image/x-icon" href="assets/logo.png">
<title>Dashboard | Student Portal</title>
	

<?php 
include('includes/sidebar.php');
include('includes/links.php'); 


$schoolYearSql = "SELECT * FROM tblschoolyear WHERE status = 'Yes' LIMIT 1";
$schoolYearResult = mysqli_query($conn, $schoolYearSql);
$currentSchoolYear = mysqli_fetch_assoc($schoolYearResult);
$currentSchoolYearDisplay = $currentSchoolYear ? $currentSchoolYear['school_year'] : 'N/A';  


?>

<main id="main" class="main">

  <div class="pagetitle">
    <h1>Dashboard</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
        <li class="breadcrumb-item active">Dashboard</li>
      </ol>
    </nav>
  </div>

  <div class="current-school-year mb-4">
    <h4 style="font-weight: bold;">Current School Year: <?php echo $currentSchoolYearDisplay; ?></h4>
</div>
<hr>

  <div class="row g-4 mb-3">

    <div class="col-lg-3 col-md-6">
      <div class="info-card grades-card">
        <a href="view-grades.php" class="d-flex">
          <div class="card-icon">
            <i class="fas fa-graduation-cap"></i>
          </div>
          <div>
            <h5>View Grades</h5>
          </div>
        </a>
      </div>
    </div>

    <div class="col-lg-3 col-md-6">
      <div class="info-card attend-card">
        <a href="view-attendance.php" class="d-flex">
          <div class="card-icon">
            <i class="fa-regular fa-calendar-days"></i>
          </div>
          <div>
            <h5>View Attendance</h5>
          </div>
        </a>
      </div>
    </div>

    <div class="col-lg-3 col-md-6">
      <div class="info-card teachers-card">
        <a href="view-teachers.php" class="d-flex">
          <div class="card-icon">
            <i class="fa-solid fa-chalkboard-user"></i>
          </div>
          <div>
            <h5>View Teachers</h5>
          </div>
        </a>
      </div>
    </div>
  </div>
  </div>

<div>
  <h2>Announcements</h2>
  <div id="announcementsCarousel" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-indicators">
      <?php
      $sql = "SELECT * FROM tblannouncement ORDER BY date_posted DESC";
      $sql_run = mysqli_query($conn, $sql);
      $announcements = mysqli_fetch_all($sql_run, MYSQLI_ASSOC);

      foreach ($announcements as $index => $announcement) {
        echo '<button type="button" data-bs-target="#announcementsCarousel" data-bs-slide-to="' . $index . '"';
        echo $index === 0 ? ' class="active" aria-current="true"' : '';
        echo ' aria-label="Slide ' . ($index + 1) . '"></button>';
      }
      ?>
    </div>

    <div class="carousel-inner">
      <?php
      foreach ($announcements as $index => $announcement) {
        $title = $announcement['title'];
        $content = $announcement['content'];
        $date_posted = date('F d, Y', strtotime($announcement['date_posted']));
        $isImage = preg_match('/\.(jpg|jpeg|png|gif)$/i', $content);
      ?>
        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
          <div class="announcement-slide">
            <h5 class="announcement-title"><?php echo $title; ?></h5>
            <?php if ($isImage): ?>
              <img src="../admin/uploads/<?php echo $content; ?>" alt="<?php echo $title; ?>" class="announcement-image">
            <?php else: ?>
              <p class="announcement-content"><?php echo $content; ?></p>
            <?php endif; ?>
            <small class="announcement-date">Posted on: <?php echo $date_posted; ?></small>
          </div>
        </div>
      <?php } ?>
    </div>

    <button class="carousel-control-prev" type="button" data-bs-target="#announcementsCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#announcementsCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Next</span>
    </button>
  </div>

</main>

<!-- Styles -->
<style>
  .info-card {
    color: white;
    border: none;
    border-radius: 15px;
    padding: 5px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.15);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    position: relative;
    z-index: 10; 
  }

  .info-card:hover {
    transform: translateY(-5px);
    box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.2);
  }

  .info-card h5 {
    font-size: 18px;
    font-weight: bold;
    margin-top: 20px;
    margin-bottom: 20px;
  }

  .info-card a {
    color: #eee;
  }

  .info-card h6 {
    font-size: 24px;
    font-weight: bold;
    margin: 0;
  }

  .info-card .card-icon {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    margin-right: 10px;
  }

  .grades-card {
    background: #6ec5e9;
  }

  .attend-card {
    background: #f9a881;
  }

  .teachers-card {
    background: #82d283;
  }

  .subjects-card {
    background: #c9a6ff;
  }

  .announcement-slide {
    position: relative; 
    background-image: url('../assets/System Images/sp-bg.png'); 
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    padding: 20px;
    border-radius: 10px;
    height: 400px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    text-align: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    margin-bottom: 30px;
    z-index: 1; 
}

.announcement-slide::before {
    content: ''; 
    position: absolute; 
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(255, 255, 255, 0.4); 
    border-radius: 10px;
    z-index: -1;
}


  .announcement-slide:hover {
    transform: scale(1.05); 
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); 
  }

  .announcement-title {
    font-family: "Lexend Deca", serif;
    font-size: 4rem;
    font-weight: bold;
    color: #eee;
  }

  .announcement-content {
    font-family: "Anton", serif;
    font-weight: 400;
    font-size: 5.5rem;
    color: #eee;
    text-align: center;
    margin-bottom: -1rem;
  }

  .announcement-image {
    width: 100%;
    height: auto;
    max-height: 250px;
    object-fit: contain;
    border-radius: 8px;
  }

  .announcement-date {
    font-family: "Roboto Mono", serif;
    font-size: 1rem;
    color: #eee;
    margin-top: 10px;
    margin-bottom: 20px;
    opacity: 0.8;
  }

  .carousel-indicators button {
    background-color: #000;
    margin-top: 100%;
  }

  .carousel-indicators button.active {
    background-color: #2c67f2;
  }

  .carousel-inner {
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: 10px;
  }

  #announcementsCarousel {
    margin-bottom: 50px; 
  }
</style>
