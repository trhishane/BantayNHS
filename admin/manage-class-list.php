<?php 
include('includes/links.php');
include('includes/sidebar.php');
?>

<main id="main" class="main">

<div class="pagetitle">
  <h1>Class List</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="admin_dashboard.php">Home</a></li>
      <li class="breadcrumb-item active">Manage Class List</li>
    </ol>
  </nav>

  <div class="card">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-end mb-3">
            <h5 class="me-auto">Class Details</h5>
            <div class="d-flex flex-column flex-md-row align-items-end">
            <form class="d-flex mb-1 mb-md-0" role="search">
                    <input class="form-control" type="search" placeholder="Search" aria-label="Search" style="max-width: 160px;">
            </form>
                <a href="create-class-list.php" class="btn btn-primary ms-md-1 mt-1 mt-md-0">
                    <i class="bi bi-plus-circle"></i> Class
                </a>
            </div>
        </div>
		<div class="card">
		    <table class="table table-bordered text-center">
			    <tr>
			      <th>Class Subject</th>
			      <th>Strand</th>
			      <th>Grade Level</th>
			      <th>Subject Teacher</th>
			      <th>Action</th>
			    </tr>
			    <?php
				include('includes/dbconn.php');

				$sql = "SELECT s.subjectId, s.subjectName, s.strand, s.gradeLevel, 
				u.firstName, u.middleName, u.lastName
		 FROM tblsubject AS s
		 JOIN tblusersaccount AS u ON u.userId = s.userId";
				$sql_run = mysqli_query($conn, $sql);

				while ($row = mysqli_fetch_assoc($sql_run)) {
				    $classId = $row['classId'];
				    $subjectName = $row['subjectName'];
				    $strand = $row['strand'];
				    $gradeLevel = $row['gradeLevel'];
				    $firstName = $row['firstName'];
				    $middleName = $row['middleName'];
				    $lastName = $row['lastName'];

				    echo "
				      <tr>
				        <td>$subjectName</td>
				        <td>$strand</td>
				        <td>$gradeLevel</td>
				        <td>$firstName $middleName $lastName</td>
				        <td>
				          <a href='edit-class-list.php?classId=$classId' class='btn btn-success btn-sm' title='Edit Information'>
				            <i class='bi bi-pencil-square'></i>
				          </a>
				          <a href='delete-class-list.php?classId=$classId' class='btn btn-danger btn-sm' title='Delete Information' onclick='return confirm(\"Are you sure you want to delete this class?\")'>
				            <i class='bi bi-trash'></i>
				          </a>
				        </td>
				      </tr>
				    ";
				}
				?>
			</table>
		</div>
	</div>
  </div>
</div>
