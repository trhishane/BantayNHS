<?php
// Enable full error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include files
include('includes/sidebar.php');
include('includes/links.php');
include('includes/dbconn.php');

// Verify database connection
if (!$conn) {
    die("<div class='alert alert-danger'>Database connection failed. Check dbconn.php</div>");
}

// 1. Get total student count
$totalStudents = 0;
$totalStudentsSql = "SELECT COUNT(*) as total FROM tblstudentinfo";
$totalStudentsResult = mysqli_query($conn, $totalStudentsSql);

if ($totalStudentsResult) {
    $totalStudents = mysqli_fetch_assoc($totalStudentsResult)['total'];
} else {
    $dbError = mysqli_error($conn);
    error_log("Student count query failed: " . $dbError);
}

// 2. Get total teacher count
$totalTeachers = 0;
$totalTeachersSql = "SELECT COUNT(*) as total FROM tblteacherinfo";
$totalTeachersResult = mysqli_query($conn, $totalTeachersSql);

if ($totalTeachersResult) {
    $totalTeachers = mysqli_fetch_assoc($totalTeachersResult)['total'];
} else {
    $dbError = mysqli_error($conn);
    error_log("Teacher count query failed: " . $dbError);
}

// 3. Section and Gender Distribution (Students)
$sectionGenderData = [];
$sections = [];
$datasets = [];

$sectionGenderSql = "
    SELECT 
        IFNULL(s.sectionName, 'Unassigned') as sectionName,
        IFNULL(st.sex, 'Unknown') as sex,
        COUNT(st.studentId) as student_count
    FROM tblsection s
    LEFT JOIN tblstudentinfo st ON s.sectionId = st.sectionId
    GROUP BY s.sectionName, st.sex
    ORDER BY s.sectionName, st.sex
";

$sectionGenderResult = mysqli_query($conn, $sectionGenderSql);

if ($sectionGenderResult) {
    $sectionGenderData = mysqli_fetch_all($sectionGenderResult, MYSQLI_ASSOC);
    
    // Process data for chart
    $sections = array_values(array_unique(array_column($sectionGenderData, 'sectionName')));
    $genders = ['Male', 'Female', 'Unknown'];
    
    foreach ($genders as $gender) {
        $counts = array_fill(0, count($sections), 0);
        foreach ($sectionGenderData as $row) {
            if ($row['sex'] === $gender) {
                $index = array_search($row['sectionName'], $sections);
                if ($index !== false) {
                    $counts[$index] = $row['student_count'];
                }
            }
        }
        $datasets[] = [
            'label' => $gender,
            'data' => $counts,
            'backgroundColor' => $gender == 'Male' ? 'rgba(54, 162, 235, 0.7)' : 
                              ($gender == 'Female' ? 'rgba(255, 99, 132, 0.7)' : 'rgba(201, 203, 207, 0.7)')
        ];
    }
} else {
    $dbError = mysqli_error($conn);
    error_log("Section/Gender query failed: " . $dbError);
}

// 4. Age Distribution (Students)
$ageData = [];
$ageSql = "SELECT 
              CASE
                WHEN age BETWEEN 15 AND 16 THEN '15-16'
                WHEN age BETWEEN 17 AND 18 THEN '17-18'
                WHEN age BETWEEN 19 AND 20 THEN '19-20'
                WHEN age >= 21 THEN '21+'
                ELSE 'Unknown'
              END as age_group,
              COUNT(*) as count
           FROM tblstudentinfo
           GROUP BY age_group
           ORDER BY age_group";

$ageResult = mysqli_query($conn, $ageSql);

if ($ageResult) {
    $ageData = mysqli_fetch_all($ageResult, MYSQLI_ASSOC);
    
    // Ensure all age groups exist
    $expectedAgeGroups = ['15-16', '17-18', '19-20', '21+', 'Unknown'];
    $existingGroups = array_column($ageData, 'age_group');
    
    foreach ($expectedAgeGroups as $group) {
        if (!in_array($group, $existingGroups)) {
            $ageData[] = ['age_group' => $group, 'count' => 0];
        }
    }
    
    // Sort by age group
    usort($ageData, function($a, $b) {
        return $a['age_group'] <=> $b['age_group'];
    });
} else {
    $dbError = mysqli_error($conn);
    error_log("Age distribution query failed: " . $dbError);
}

// 5. Teacher Gender Distribution
$teacherGenderData = [];
$teacherGenderSql = "SELECT 
                        IFNULL(sex, 'Unknown') as gender,
                        COUNT(*) as count 
                     FROM tblteacherinfo 
                     GROUP BY gender";

$teacherGenderResult = mysqli_query($conn, $teacherGenderSql);

if ($teacherGenderResult) {
    $teacherGenderData = mysqli_fetch_all($teacherGenderResult, MYSQLI_ASSOC);
    
    // Ensure all gender categories exist
    $expectedGenders = ['Male', 'Female', 'Unknown'];
    $existingGenders = array_column($teacherGenderData, 'gender');
    
    foreach ($expectedGenders as $gender) {
        if (!in_array($gender, $existingGenders)) {
            $teacherGenderData[] = ['gender' => $gender, 'count' => 0];
        }
    }
} else {
    $dbError = mysqli_error($conn);
    error_log("Teacher gender distribution query failed: " . $dbError);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eSkwela - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        .no-data {
            text-align: center;
            padding: 50px;
            color: #6c757d;
            font-style: italic;
        }
        .stat-card {
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%);  
    border: 1px solid rgba(255, 255, 255, 0.5);  
    border: 1px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
}

.stat-card:hover {
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}
        .stat-card i {
            font-size: 2.5rem;
            margin-right: 15px;
            color: #4e73df;
        }
    </style>
</head>
<body>
    <main id="main" class="main">
        <div class="pagetitle">
        <h1>Dashboard</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php" style="text-decoration: none;">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </nav>
        </div>

        <section class="section dashboard">
            <div class="row">
                <!-- Student Count -->
                <div class="col-lg-4">
                    <div class="stat-card">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Total Students</h5>
                                <h2 class="mb-0"><?php echo $totalStudents; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Teacher Count -->
                <div class="col-lg-4">
                    <div class="stat-card">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="bi bi-person-badge-fill"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Total Teachers</h5>
                                <h2 class="mb-0"><?php echo $totalTeachers; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <!-- Section Distribution Chart -->
                <div class="col-lg-8">
                    <div class="stat-card">
                        <h5 class="card-title">Students by Section and Gender</h5>
                        <div class="chart-container">
                            <?php if (!empty($sections) && !empty($datasets)): ?>
                                <canvas id="sectionGenderChart"></canvas>
                            <?php else: ?>
                                <div class="no-data">No section/gender data available</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Age Distribution Chart -->
                <div class="col-lg-4">
                    <div class="stat-card">
                        <h5 class="card-title">Student Age</h5>
                        <div class="chart-container">
                            <?php if (!empty($ageData)): ?>
                                <canvas id="ageDistributionChart"></canvas>
                            <?php else: ?>
                                <div class="no-data">No age distribution data available</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- New Row for Teacher Distribution -->
            <div class="row mt-4">
                <div class="col-lg-6">
                    <div class="stat-card">
                        <h5 class="card-title">Teacher Gender Distribution</h5>
                        <div class="chart-container">
                            <?php if (!empty($teacherGenderData)): ?>
                                <canvas id="teacherGenderChart"></canvas>
                            <?php else: ?>
                                <div class="no-data">No teacher gender data available</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Section and Gender Distribution Chart
        const sectionGenderCtx = document.getElementById('sectionGenderChart');
        if (sectionGenderCtx) {
            try {
                new Chart(sectionGenderCtx, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode($sections); ?>,
                        datasets: <?php echo json_encode($datasets); ?>
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            } catch (e) {
                console.error('Chart initialization error:', e);
            }
        }
        
        // 2. Age Distribution Chart
        const ageCtx = document.getElementById('ageDistributionChart');
        if (ageCtx) {
            try {
                new Chart(ageCtx, {
                    type: 'pie',
                    data: {
                        labels: <?php echo json_encode(array_column($ageData, 'age_group')); ?>,
                        datasets: [{
                            data: <?php echo json_encode(array_column($ageData, 'count')); ?>,
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.7)',
                                'rgba(54, 162, 235, 0.7)',
                                'rgba(255, 206, 86, 0.7)',
                                'rgba(75, 192, 192, 0.7)',
                                'rgba(153, 102, 255, 0.7)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            } catch (e) {
                console.error('Chart initialization error:', e);
            }
        }
        
        // 3. Teacher Gender Distribution Chart
        const teacherGenderCtx = document.getElementById('teacherGenderChart');
        if (teacherGenderCtx) {
            try {
                new Chart(teacherGenderCtx, {
                    type: 'doughnut',
                    data: {
                        labels: <?php echo json_encode(array_column($teacherGenderData, 'gender')); ?>,
                        datasets: [{
                            data: <?php echo json_encode(array_column($teacherGenderData, 'count')); ?>,
                            backgroundColor: [
                                'rgba(54, 162, 235, 0.7)', // Male
                                'rgba(255, 99, 132, 0.7)',  // Female
                                'rgba(201, 203, 207, 0.7)'  // Unknown
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                            }
                        }
                    }
                });
            } catch (e) {
                console.error('Teacher Gender Chart initialization error:', e);
            }
        }
    });
    </script>
</body>
</html>