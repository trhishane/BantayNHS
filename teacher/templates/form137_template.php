<div class="form137">
    <div class="header">
        <h2>STUDENT PERMANENT RECORD</h2>
        <h3>Form 137</h3>
    </div>

    <div class="student-info">
        <table width="100%">
            <tr>
                <td width="20%">LRN:</td>
                <td width="30%"><?php echo htmlspecialchars($records['studentInfo']['lrn']); ?></td>
                <td width="20%">Grade Level:</td>
                <td width="30%"><?php echo htmlspecialchars($records['studentInfo']['gradeLevel']); ?></td>
            </tr>
            <tr>
                <td>Name:</td>
                <td colspan="3">
                    <?php 
                    echo htmlspecialchars($records['studentInfo']['lastName'] . ', ' . 
                         $records['studentInfo']['firstName'] . ' ' . 
                         $records['studentInfo']['middleName'] . ' ' . 
                         $records['studentInfo']['suffix']); 
                    ?>
                </td>
            </tr>
            <tr>
                <td>Strand:</td>
                <td><?php echo htmlspecialchars($records['studentInfo']['strand']); ?></td>
                <td>Section:</td>
                <td><?php echo htmlspecialchars($records['studentInfo']['sectionName']); ?></td>
            </tr>
        </table>
    </div>

    <?php foreach ($records['grades'] as $gradeLevel => $semesters): ?>
        <h3>Grade <?php echo $gradeLevel; ?></h3>
        <?php foreach ($semesters as $semester => $subjectGrades): ?>
            <h4><?php echo $semester; ?> Semester</h4>
            <table class="grades-table">
                <thead>
                    <tr>
                        <th>Subject Code</th>
                        <th>Subject Name</th>
                        <th>Teacher</th>
                        <th>Final Grade</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subjectGrades as $grade): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($grade['subjectCode']); ?></td>
                            <td><?php echo htmlspecialchars($grade['subjectName']); ?></td>
                            <td>
                                <?php 
                                echo htmlspecialchars($grade['teacherLastName'] . ', ' . 
                                     $grade['teacherFirstName']); 
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($grade['finalGrade']); ?></td>
                            <td><?php echo htmlspecialchars($grade['remarks']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <h3>Attendance Record</h3>
    <?php foreach ($records['attendance'] as $year => $months): ?>
        <h4>School Year <?php echo $year; ?></h4>
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Days Present</th>
                    <th>Days Absent</th>
                    <th>Days Late</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($months as $month => $attendance): ?>
                    <tr>
                        <td><?php echo date("F", mktime(0, 0, 0, $month, 1)); ?></td>
                        <td><?php echo $attendance['daysPresent']; ?></td>
                        <td><?php echo $attendance['daysAbsent']; ?></td>
                        <td><?php echo $attendance['daysLate']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>

    <div class="signatures">
        <table width="100%">
            <tr>
                <td width="50%" style="text-align: center;">
                    <p>Certified by:</p>
                    <br><br>
                    <p>
                        <strong>
                            <?php 
                            echo htmlspecialchars($records['adviserInfo']['adviserLastName'] . ', ' . 
                                 $records['adviserInfo']['adviserFirstName']); 
                            ?>
                        </strong>
                    </p>
                    <p>Class Adviser</p>
                </td>
                <td width="50%" style="text-align: center;">
                    <p>Noted by:</p>
                    <br><br>
                    <p><strong>[Principal Name]</strong></p>
                    <p>School Principal</p>
                </td>
            </tr>
        </table>
    </div>
</div> 