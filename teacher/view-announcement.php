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
      <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
      <li class="breadcrumb-item active">Announcements</li>
    </ol>
  </nav>
</div>

<div id="announcementsCarousel" class="carousel slide" data-bs-ride="carousel">
  <div class="carousel-indicators">
    <?php
    include('includes/dbconn.php');

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
          <div class="">
            <img src="../admin/uploads/<?php echo $content; ?>" alt="<?php echo $title; ?>" class="announcement-image">
          </div>
        <?php else: ?>
          <p class="announcement-content"><?php echo $content; ?></p>
        <?php endif; ?>
        
        <small class="announcement-date">Posted on: <?php echo $date_posted; ?></small>
      </div>
    </div>
    <?php
    }
    ?>
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

<style>
  .announcement-slide {
    background: linear-gradient(to bottom, #2c67f2, #62cff4);
    padding: 20px;
    border-radius: 10px;
    height: 400px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    text-align: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease-in-out;
  }

  .announcement-slide:hover {
    transform: scale(1.03);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
  }

  .announcement-title {
    font-size: 2.5rem;
    font-weight: bold;
    color: #333;
    margin-bottom: 10px;
  }

  .announcement-content {
    font-size: 2.1rem;
    color: #555;
    text-align: center;
    margin: 10px 0;
  }

  .announcement-image {
    width: 100%;
    height: auto;
    max-height: 250px; 
    object-fit: contain;
    border-radius: 8px;
  }


  .announcement-date {
    font-size: 0.9rem;
    color: #777;
    margin-top: 5px;
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

</style>
