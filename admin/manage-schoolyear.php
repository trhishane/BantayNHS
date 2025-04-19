<?php
include('includes/links.php');
include('includes/sidebar.php');
include('includes/dbconn.php');
?>
<link rel="icon" type="image/x-icon" href="../assets/logo.png">
<title>School Year | Student Portal</title>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>School Year</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="admin_dashboard.php" style="text-decoration: none;">Home</a></li>
                <li class="breadcrumb-item active">School Year</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">School Year</h5>
                <a href="add-schoolyear.php" class="btn btn-primary">Add School Year</a>
            </div>

            <!-- Alert Messages -->
            <?php if (isset($_GET['message'])) : ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_GET['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['warning'])) : ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_GET['warning']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Table for School Year Records -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>School Year</th>
                            <th>Current School Year</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM tblschoolyear ORDER BY school_year DESC";
                        $result = mysqli_query($conn, $query);
                        while ($row = mysqli_fetch_assoc($result)) :
                        ?>
                            <tr>
                                <td><?= $row['syId']; ?></td>
                                <td><?= $row['school_year']; ?></td>
                                <td><?= $row['status']; ?></td>
                                <td>
                                    <a href="edit-schoolyear.php?id=<?= $row['syId']; ?>" class="btn btn-warning btn-sm">
                                        <i class='bi bi-pencil-square'></i>
                                    </a>
                                    <a href="delete-schoolyear.php?id=<?= $row['syId']; ?>" class="btn btn-danger btn-sm">
                                        <i class='bi bi-archive'></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

