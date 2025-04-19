<link rel="icon" type="image/x-icon" href="../assets/logo.png">
<title>Archived Announcements | Student Portal</title> 

<?php 
include('includes/links.php');
include('includes/sidebar.php');
include('includes/dbconn.php');
?>

<main id="main" class="main">

<div class="pagetitle">
  <h1>Archived Announcements</h1>
  <nav>
    <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="admin_dashboard.php" style="text-decoration: none;">Home</a></li>
      <li class="breadcrumb-item active">Archived Announcements</li>
    </ol>
  </nav>

  <div class="card">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-end mb-3">
            <h5 class="me-auto">Archived Announcements Details</h5>
            <div class="d-flex flex-column flex-md-row align-items-end">
                <form class="d-flex mb-1 mb-md-0" role="search">
                    <input class="form-control" type="search" placeholder="Search" aria-label="Search" style="max-width: 160px;" id="searchInput">
                </form>
            </div>
        </div>
   
        <div class="card">
            <table class="table table-bordered text-center" id="announceTable">
                <thead style="background-color: #f8f9fa; color: #333;">
                    <tr>
                        <th scope="col">Title</th>
                        <th scope="col">Content</th>
                        <th scope="col">Event Date</th>
                        <th scope="col">Expire Date</th>
                        <th scope="col">Date Posted</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $sql = "SELECT * FROM tblannouncement WHERE status = 'archived' ORDER BY date_posted DESC";
                $sql_run = mysqli_query($conn, $sql);

                if (mysqli_num_rows($sql_run) > 0) {
                    while ($row = mysqli_fetch_assoc($sql_run)) {
                        $announcementId = $row['announcementId'];
                        $title = $row['title'];
                        $content = $row['content'];
                        $event_date = $row['event_date'];
                        $expire_date = $row['expire_date'];
                        $date_posted = $row['date_posted'];

                        echo "
                        <tr>
                            <td>$title</td>
                            <td>$content</td>
                            <td>$event_date</td>
                            <td>$expire_date</td>
                            <td>$date_posted</td>
                            <td>
                                <a href='restore-announce.php?announcementId=$announcementId' class='btn btn-info btn-sm' title='Restore Announcement'>
                                    <i class='bi bi-arrow-repeat'></i>
                                </a>
                            </td>
                        </tr>
                        ";
                    }
                } else {
                    echo "
                    <tr id='noDetailsRow'>
                  <td colspan='5'>
                    <div class='alert alert-danger mb-0' role='alert'>
                      No archived announcements found.
                    </div>
                  </td>
                </tr>
                ";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
  </div>
</div>

<style>
    @media (max-width: 576px) {
        .btn-text {
            display: none;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const announceTable = document.getElementById('announceTable');
    const tableRows = announceTable.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    const noDetailsRow = document.getElementById('noDetailsRow');

    searchInput.addEventListener('input', function() {
        const filter = searchInput.value.toLowerCase();
        let hasVisibleRows = false;

        for (let i = 0; i < tableRows.length; i++) {
            if (tableRows[i].id === 'noDetailsRow') continue;
            
            const cells = tableRows[i].getElementsByTagName('td');
            let textFound = false;

            for (let j = 0; j < cells.length; j++) {
                const cellText = cells[j].textContent || cells[j].innerText;
                if (cellText.toLowerCase().indexOf(filter) > -1) {
                    textFound = true;
                    break;
                }
            }

            tableRows[i].style.display = textFound ? '' : 'none';
            if (textFound) hasVisibleRows = true;
        }

        noDetailsRow.style.display = hasVisibleRows ? 'none' : '';
    });
});
</script>

</main>
