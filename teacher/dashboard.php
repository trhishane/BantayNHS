<?php 
include('includes/sidebar.php'); 
include('includes/links.php'); 

// Include necessary files
include('includes/dbconn.php');

//  current school year
$schoolYearSql = "SELECT * FROM tblschoolyear WHERE status = 'Yes' LIMIT 1";
$schoolYearResult = mysqli_query($conn, $schoolYearSql);
$currentSchoolYear = mysqli_fetch_assoc($schoolYearResult);
$currentSchoolYearDisplay = $currentSchoolYear ? $currentSchoolYear['school_year'] : 'N/A';  

$sql = "
    SELECT s.strand, s.gradeLevel, COUNT(st.studentId) AS total_students 
    FROM tblsection s
    LEFT JOIN tblstudentinfo st ON s.sectionId = st.sectionId
    GROUP BY s.strand, s.gradeLevel
";

$sql_run = mysqli_query($conn, $sql);
$data = mysqli_fetch_all($sql_run, MYSQLI_ASSOC);


$allMonths = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

$attendance_counts = array_fill(0, 12, ['total_present' => 0, 'total_absent' => 0, 'total_late' => 0]);

$attendance_sql = "
    SELECT 
        MONTH(a.classDate) AS month,
        COUNT(CASE WHEN a.attendance = 'Present' THEN 1 END) AS total_present,
        COUNT(CASE WHEN a.attendance = 'Absent' THEN 1 END) AS total_absent,
        COUNT(CASE WHEN a.attendance = 'Late' THEN 1 END) AS total_late
    FROM tblattendance a
    GROUP BY MONTH(a.classDate)
    ORDER BY MONTH(a.classDate)
";

$attendance_sql_run = mysqli_query($conn, $attendance_sql);

while ($attendance_data = mysqli_fetch_assoc($attendance_sql_run)) {
    $attendance_counts[$attendance_data['month'] - 1] = [
        'total_present' => $attendance_data['total_present'],
        'total_absent' => $attendance_data['total_absent'],
        'total_late' => $attendance_data['total_late']
    ];
}

$attendanceLabels = json_encode($allMonths);  
$attendancePresent = json_encode(array_column($attendance_counts, 'total_present'));  
$attendanceAbsent = json_encode(array_column($attendance_counts, 'total_absent'));  
$attendanceLate = json_encode(array_column($attendance_counts, 'total_late')); 
?>


<!-- Main Dashboard -->
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
    <h4 style="font-weight: bold;">School Year: <?php echo $currentSchoolYearDisplay; ?></h4>
</div>

<hr>

  <section class="section dashboard">
    <div class="row g-4">
      <div class="col-lg-6">
        <div class="analytics-card">
          <h5>Students</h5>
          <canvas id="studentAnalyticsChart"></canvas>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="analytics-card">
          <h5>Attendance Monitoring</h5>
          <canvas id="attendanceAnalyticsChart"></canvas>
        </div>
      </div>


  <section class="section announcements mt-4">
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
  </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
  const labels = <?php echo json_encode(array_map(function($row) {
      return $row['strand'] . ' - ' . $row['gradeLevel'];
  }, $data)); ?>;

  const studentData = {
    labels: labels,
    datasets: [{
      label: 'Total Students',
      data: <?php echo json_encode(array_map(function($row) {
        return $row['total_students'];
      }, $data)); ?>,
      backgroundColor: 'rgba(255, 192, 203)', 
      borderColor: 'rgba(170, 51, 106)', 
      borderWidth: 1
    }]
  };

  const studentCtx = document.getElementById('studentAnalyticsChart').getContext('2d');
  const studentAnalyticsChart = new Chart(studentCtx, {
    type: 'bar',
    data: studentData,
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'top',
        },
        tooltip: {
          callbacks: {
            label: function(tooltipItem) {
              return tooltipItem.raw + ' students'; 
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1
          }
        }
      }
    }
  });

  const attendanceLabels = <?php echo $attendanceLabels; ?>;  
  const attendancePresent = <?php echo $attendancePresent; ?>;  
  const attendanceAbsent = <?php echo $attendanceAbsent; ?>;  
  const attendanceLate = <?php echo $attendanceLate; ?>;  

  const attendanceData = {
      labels: attendanceLabels,  
      datasets: [
          {
              label: 'Present',
              data: attendancePresent,  
              backgroundColor: 'rgba(76, 175, 80, 0.5)',
              borderColor: 'rgba(76, 175, 80, 1)',
              borderWidth: 1
          },
          {
              label: 'Absent',
              data: attendanceAbsent,  
              backgroundColor: 'rgba(255, 99, 132, 0.5)',
              borderColor: 'rgba(255, 99, 132, 1)',
              borderWidth: 1
          },
          {
              label: 'Late',
              data: attendanceLate,  
              backgroundColor: 'rgba(255, 159, 64, 0.5)',
              borderColor: 'rgba(255, 159, 64, 1)',
              borderWidth: 1
          }
      ]
  };

  const attendanceCtx = document.getElementById('attendanceAnalyticsChart').getContext('2d');
  const attendanceAnalyticsChart = new Chart(attendanceCtx, {
      type: 'bar',
      data: attendanceData,
      options: {
          responsive: true,
          plugins: {
              legend: {
                  position: 'top',
              },
              tooltip: {
                  callbacks: {
                      label: function(tooltipItem) {
                          return tooltipItem.raw + ' students'; 
                      }
                  }
              }
          },
          scales: {
              y: {
                  beginAtZero: true,
                  ticks: {
                      stepSize: 1
                  }
              }
          }
      }
  });
</script>

<!-- Styles -->
<style>
  .analytics-card {
    padding: 20px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    background: linear-gradient(145deg, #f0f8ff, #d9e6f3); 
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease-in-out;
  }

  .analytics-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
  }

  .analytics-card::before {
    content: "";
    position: absolute;
    top: 10px;
    left: 10px;
    right: 10px;
    bottom: 10px;
    border: 2px solid rgba(54, 162, 235, 0.6);
    border-radius: 12px;
    pointer-events: none;
  }

  .analytics-card h5 {
    font-size: 1.2rem;
    color: #2a2a2a;
    font-weight: bold;
    margin-bottom: 15px;
    text-transform: uppercase;
    letter-spacing: 1px;
    position: relative;
  }

  #studentAnalyticsChart {
    background-color: white;
    border-radius: 10px;
    padding: 10px;
    border: 1px solid rgba(54, 162, 235, 0.3);
  }

  @keyframes chartAnimation {
    0% {
      opacity: 0;
      transform: scale(0.95);
    }
    100% {
      opacity: 1;
      transform: scale(1);
    }
  }

  #studentAnalyticsChart {
    animation: chartAnimation 0.6s ease-out forwards;
  }

  .info-card {
    color: white;
    border: none;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.15);
    transition: transform 0.3s, box-shadow 0.3s ease-in-out; 
  }

  .info-card:hover {
    transform: translateY(-7px); 
    box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.2); 
  }

  .info-card h5 {
    margin: auto;
    display: flex;
    justify-content: center;
    font-size: 18px;
    font-weight: bold;
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
    margin-right: 15px;
  }

  .students-card { background: #6ec5e9; }
  .parents-card { background: #82d283; }
  .grade-card { background: #f9a881; }
  .attendance-card { background: #c9a6ff; }

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

  .carousel-indicators button.active {
    background-color: #2c67f2;
  }

  .analytics-card {
    padding: 20px;
    border-radius: 10px;
    background-color: #fff;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
  }

  .info-card {
    padding: 20px;
    border-radius: 10px;
    background-color: #f8f9fa;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    cursor: pointer;
  }

  .info-card:hover {
    background-color: #e9ecef;
    transform: translateY(-5px);
  }

  .carousel-inner {
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: 10px;
  }
  
</style>
