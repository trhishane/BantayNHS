<?php
$page_title = "Form 138 (Report Card)";
$currentPage = "form138";
require_once("../includes/header.php");

include('includes/sidebar.php');
include('../includes/dbconn.php');

$userId = $_SESSION['userId'];

// First check if teacher is an adviser
$sql = "SELECT isAdviser, sectionId FROM tblteacherinfo WHERE userId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$teacherInfo = $result->fetch_assoc();

// Get class details if teacher is an adviser
$advisoryClass = null;
if ($teacherInfo['isAdviser'] == 1 && $teacherInfo['sectionId']) {
    $sql = "SELECT s.*, s.gradeLevel, s.strand, s.sectionName 
            FROM tblsection s 
            WHERE s.sectionId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $teacherInfo['sectionId']);
    $stmt->execute();
    $advisoryClass = $stmt->get_result()->fetch_assoc();
}

// Get students in advisory class
$students = [];
if ($advisoryClass) {
    $sql = "SELECT s.*, u.firstName as firstName, u.lastName as lastName 
            FROM tblstudentinfo s
            JOIN tblusersaccount u ON s.userId = u.userId 
            WHERE s.sectionId = ?
            ORDER BY u.lastName, u.firstName";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $teacherInfo['sectionId']);
    $stmt->execute();
    $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Debug output
error_log("Advisory Class: " . print_r($advisoryClass, true));
error_log("Students: " . print_r($students, true));
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<style>
.content-wrapper {
    margin-left: 300px;
    padding: 20px;
}

@media (max-width: 768px) {
    .content-wrapper {
        margin-left: 0;
    }
}

.select2-container--bootstrap5 .select2-selection--single {
    height: 38px !important;
    padding-top: 4px;
}
.select2-container--bootstrap5 .select2-selection--single .select2-selection__arrow {
    height: 36px;
}
.select2-container--bootstrap5 .select2-search--dropdown .select2-search__field {
    padding: 8px;
    border: 1px solid #ced4da;
    border-radius: 4px;
}
.select2-container--bootstrap5 .select2-results__option {
    padding: 8px;
}
.select2-dropdown {
    border: 1px solid #ced4da;
}
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <br/><br/>
                    <h1>Form 138 (Report Card)</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <?php if ($advisoryClass): ?>
                                <h3 class="card-title">Advisory Class: Grade <?php echo $advisoryClass['gradeLevel'] . ' ' . $advisoryClass['strand'] . ' - ' . $advisoryClass['sectionName']; ?></h3>
                            <?php else: ?>
                                <h3 class="card-title">You are not assigned as a class adviser</h3>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <form action="generate_form138.php" method="GET" target="_blank">
                                <div class="form-group">
                                    <label for="student_select">Select Student:</label>
                                    <select name="student_id" id="student_select" class="form-control select2" style="width: 100%;">
                                        <option></option>
                                        <option value="all">All Students</option>
                                        <?php foreach ($students as $student): ?>
                                            <option value="<?php echo htmlspecialchars($student['studentId']); ?>"
                                                    data-name="<?php echo htmlspecialchars($student['lastName'] . ', ' . $student['firstName']); ?>"
                                                    data-lrn="<?php echo htmlspecialchars($student['lrn'] ?? ''); ?>">
                                                <?php echo htmlspecialchars($student['lastName'] . ', ' . $student['firstName']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <br>
                                <button type="submit" class="btn btn-primary">Generate Form 138</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#student_select').select2({
        theme: 'bootstrap5',
        width: '100%',
        placeholder: 'Type to search students...',
        allowClear: true,
        minimumInputLength: 1,
        dropdownParent: $('#student_select').parent(),
        selectionCssClass: 'select2--large',
        dropdownCssClass: 'select2--large',
        matcher: function(params, data) {
            if ($.trim(params.term) === '') {
                return data;
            }
            if (data.id === '' || data.id === 'all') {
                return data;
            }

            var $option = $(data.element);
            var searchIn = [
                ($option.data('name') || '').toLowerCase(),
                ($option.data('lrn') || '').toLowerCase()
            ].join(' ');

            if (searchIn.indexOf(params.term.toLowerCase()) > -1) {
                return data;
            }
            return null;
        },
        templateResult: function(data) {
            if (!data.id || data.id === 'all') {
                return data.text;
            }

            var $option = $(data.element);
            var $result = $('<div class="select2-result"></div>');
            var name = $option.data('name');
            var lrn = $option.data('lrn');

            if (name) {
                $result.append($('<strong class="d-block"></strong>').text(name));
            }
            if (lrn) {
                $result.append($('<small class="text-muted"></small>').text('LRN: ' + lrn));
            }

            return $result;
        }
    });
});
</script>

<?php include('includes/footer.php'); ?> 