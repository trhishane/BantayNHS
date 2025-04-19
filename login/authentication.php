<?php  
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../includes/links.php'); 

if (!isset($_SESSION['authenticated'])) {
    $modalType = 'error'; 
    $modalMessage = 'Please Log in first!';
    $redirectUrl = '../index.php'; 

    ?>
   
        <!-- Modal HTML -->
        <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-body text-center p-lg-4">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="50" height="50" fill="#dc3545">
                            <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"/>
                        </svg>
                        <h4 class="text-danger mt-3">Error</h4>
                        <p class="mt-3 fs-5"><?php echo $modalMessage; ?></p>
                        <button type="button" class="btn btn-danger btn-lg mt-3" data-bs-dismiss="modal" onclick="window.location.href='<?php echo $redirectUrl; ?>'">OK</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var resultModal = new bootstrap.Modal(document.getElementById('resultModal'));
                resultModal.show();
            });
        </script>
    </body>
    </html>
    <?php
    exit(0);
}
?>
