<?php
session_start();
include '../config/dbcon.php';

// Check if teacher is logged in
if (!isset($_SESSION['userId'])) {
    $_SESSION['message'] = "You are not authorized as a teacher";
    header("Location: ../login.php");
    exit(0);
}

$teacher_id = $_SESSION['userId'];

// Get sections taught by this teacher - adjust query based on your database structure
try {
    $query = "SELECT sec.sectionId as id, sec.sectionName as class_name, sec.gradeLevel as grade_level 
              FROM tblsection sec 
              JOIN tblteacherinfo t ON sec.sectionId = t.sectionId
              WHERE t.userId = '$teacher_id'";
    $result = mysqli_query($con, $query);
    
    if (!$result) {
        $_SESSION['message'] = "Error retrieving sections: " . mysqli_error($con);
    }
} catch (Exception $e) {
    $_SESSION['message'] = "Error retrieving sections: " . $e->getMessage();
    $result = false;
}

// Include header and sidebar
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Student Reports</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item active">Generate Student Reports</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Generate Class List Report</h4>
                    </div>
                    <div class="card-body">
                        <?php if(isset($_SESSION['message'])): ?>
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <strong>Note:</strong> <?= $_SESSION['message']; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php unset($_SESSION['message']); ?>
                        <?php endif; ?>
                        
                        <form action="generate_report.php" method="POST" target="_blank">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="class">Select Class</label>
                                    <select name="class_id" class="form-control" required>
                                        <option value="">--Select Class--</option>
                                        <?php
                                        if($result && mysqli_num_rows($result) > 0) {
                                            foreach($result as $class) {
                                                ?>
                                                <option value="<?= $class['id'] ?>">
                                                    <?= $class['class_name'] ?> - <?= $class['grade_level'] ?>
                                                </option>
                                                <?php
                                            }
                                        } else {
                                            echo "<option value=''>No classes found</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="school_name">School Name</label>
                                    <input type="text" name="school_name" class="form-control" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="region">Region</label>
                                    <input type="text" name="region" class="form-control" required placeholder="e.g. Region I">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="division">Division</label>
                                    <input type="text" name="division" class="form-control" required placeholder="e.g. Schools Division of Ilocos Sur">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="school_address">School Address</label>
                                    <input type="text" name="school_address" class="form-control" required placeholder="e.g. Bulag, Bantay, Ilocos Sur">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="school_level">School Level</label>
                                    <input type="text" name="school_level" class="form-control" required placeholder="e.g. SENIOR HIGH SCHOOL">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="class_details">Class/Section Details</label>
                                    <input type="text" name="class_details" class="form-control" required placeholder="e.g. 12- (TVL) Socrates">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="adviser_name">Adviser Name</label>
                                    <input type="text" name="adviser_name" class="form-control" required placeholder="e.g. RASHEL DOMINA S. PERIA">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <button type="submit" name="generate_report" class="btn btn-primary">Generate Report</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
include 'includes/footer.php';
include 'includes/scripts.php';
?> 