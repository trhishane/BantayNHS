<?php
$page_title = "Form 138 (Report Card)";
$currentPage = "form138";
require_once("../includes/header.php");
include('includes/sidebar.php');
include('../includes/dbconn.php');
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
                        <div class="card-body">
                            <form action="generate-form138.php" method="GET" target="_blank">
                                <!-- Section Selection -->
                                <div class="mb-3">
                                    <label for="section_select" class="form-label">Select Section:</label>
                                    <select class="form-control" id="section_select" name="sectionId" required>
                                        <option value="">-- Select Section --</option>
                                        <?php
                                        $query = "SELECT sectionId, sectionName FROM tblsection";
                                        $result = $conn->query($query);
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<option value='" . $row['sectionId'] . "'>" . $row['sectionName'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <!-- Student Selection -->
                                <div class="mb-3">
                                    <label for="student_select" class="form-label">Select Student:</label>
                                    <select class="form-control" id="student_select" name="studentId" required>
                                        <option value="">-- Select Student --</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary">Generate Form 138</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Include jQuery & Select2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
 $(document).ready(function() {
            $('#section_select').on('change', function() {
                var sectionId = $(this).val();
                if (sectionId) {
                    $.ajax({
                        url: 'fetch.php', // Use the same file for AJAX request
                        type: 'POST',
                        data: { fetchStudents: true, sectionId: sectionId },
                        dataType: 'json',
                        success: function(response) {
                            var studentSelect = $('#student_select');
                            studentSelect.empty().append('<option value="">-- Select Student --</option>', '<option value="all">All Students</option>');

                            if (response.length > 0) {
                                $.each(response, function(index, student) {
                                    studentSelect.append(
                                        `<option value="${student.studentId}">
                                            ${student.lastName}, ${student.firstName}
                                        </option>`
                                    );
                                });
                            } else {
                                studentSelect.append('<option>No students available</option>');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log("Error: " + error);
                        }
                    });
                } else {
                    $('#student_select').empty().append('<option value="">-- Select Student --</option>');
                }
            });
        });
    </script>

