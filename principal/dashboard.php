<link rel="icon" type="image/x-icon" href="../assets/logo.png">
<title>Dashboard | Student Portal</title> 

<?php 
include('includes/links.php');
include('includes/sidebar.php');
include('includes/dbconn.php'); 

// current school year
$schoolYearSql = "SELECT * FROM tblschoolyear WHERE status = 'Yes' LIMIT 1";
$schoolYearResult = mysqli_query($conn, $schoolYearSql);
$currentSchoolYear = mysqli_fetch_assoc($schoolYearResult);
$currentSchoolYearDisplay = $currentSchoolYear ? $currentSchoolYear['school_year'] : 'N/A';  // Fallback if not found


//  total students
$student_query = "SELECT COUNT(*) as total_students FROM tblusersaccount 
                  WHERE tblusersaccount.role = 'Student' AND archived = 0";
$student_result = mysqli_query($conn, $student_query);
$student_data = mysqli_fetch_assoc($student_result);
$total_students = $student_data['total_students'];

//  total teachers
$teacher_query = "SELECT COUNT(*) as total_teachers FROM tblteacherinfo";
$teacher_result = mysqli_query($conn, $teacher_query);
$teacher_data = mysqli_fetch_assoc($teacher_result);
$total_teachers = $teacher_data['total_teachers'];

//  total parents
$parent_query = "SELECT COUNT(*) as total_parents FROM tblparentinfo";
$parent_result = mysqli_query($conn, $parent_query);
$parent_data = mysqli_fetch_assoc($parent_result);
$total_parents = $parent_data['total_parents'];

//  total subjects
$subject_query = "SELECT COUNT(*) as total_subjects FROM tblsubject";
$subject_result = mysqli_query($conn, $subject_query);
$subject_data = mysqli_fetch_assoc($subject_result);
$total_subjects = $subject_data['total_subjects'];


$section_query = "SELECT 
                    s.sectionName, 
                    COUNT(st.studentId) AS total_students
                  FROM tblsection s
                  LEFT JOIN tblstudentinfo st ON s.sectionId = st.sectionId
                  LEFT JOIN tblusersaccount u ON st.studentId = u.userId AND u.role = 'Student'
                  GROUP BY s.sectionId";

$section_result = mysqli_query($conn, $section_query);

// Prepare data for the chart
$sections = [];
$student_counts = [];

while ($row = mysqli_fetch_assoc($section_result)) {
    $sections[] = $row['sectionName'];
    $student_counts[] = $row['total_students'];
}
?>

<style>
  .info-card {
    color: white;
    border: none;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.15);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .info-card:hover {
    transform: translateY(-5px);
    box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.2);
  }

  .info-card h5 {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 10px;
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
    margin-right: 15px;
  }

  .students-card {
    background: #6ec5e9; 
  }

  .teachers-card {
    background: #f9a881; 
  }

  .parents-card {
    background: #82d283; 
  }

  .subjects-card {
    background: #c9a6ff; 
  }


  .back-to-top {
    background-color: #6c757d;
    color: white;
    border-radius: 50%;
    width: 40px;
    height: 40px;
  }

  .back-to-top:hover {
    background-color: #495057;
  }
  
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
  
</style>

<main id="main" class="main">

<div class="pagetitle">
  <h1>Dashboard</h1>
  <nav>
    <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="admin_dashboard.php" style="text-decoration: none;">Home</a></li>
      <li class="breadcrumb-item active">Dashboard</li>
    </ol>
  </nav>
</div>

<div class="current-school-year mb-4">
    <h4 style="font-weight: bold;">School Year: <?php echo $currentSchoolYearDisplay; ?></h4>
</div>

<div class="col-lg-6">
        <div class="analytics-card">
          <h5>Students</h5>
          <canvas id="sectionChart"></canvas>
        </div>
      </div>
<section class="section dashboard">
  <div class="row g-4">


    <div class="col-lg-3 col-md-6">
      <div class="info-card teachers-card">
        <div class="d-flex">
          <div class="card-icon">
            <i class="fas fa-chalkboard-teacher"></i>
          </div>
          <div>
            <h5>Teachers</h5>
            <h6><?php echo $total_teachers; ?></h6>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6">
      <div class="info-card parents-card">
        <div class="d-flex">
          <div class="card-icon">
            <i class="fas fa-users"></i>
          </div>
          <div>
            <h5>Parents</h5>
            <h6><?php echo $total_parents; ?></h6>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6">
      <div class="info-card subjects-card">
        <div class="d-flex">
          <div class="card-icon">
            <i class="fa-solid fa-rectangle-list"></i>
          </div>
          <div>
            <h5>Subjects</h5>
            <h6><?php echo $total_subjects; ?></h6>
          </div>
        </div>
      </div>
    </div>

    

  </div>
</section>


    <script>
        var ctx = document.getElementById('sectionChart').getContext('2d');
        var sectionChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($sections); ?>,
                datasets: [{
                    label: 'Total Students',
                    data: <?php echo json_encode($student_counts); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>


</main>

<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
