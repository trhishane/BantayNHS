<!-- Success Modals -->
<div class="modal fade" id="statusAddSuccessModal" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-body text-center p-lg-4">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="50" height="50" fill="#198754">
                    <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"/>
                </svg>
                <h4 class="text-success mt-3">Success</h4>
                <p class="mt-3">Added successfully!</p>
                <button type="button" class="btn btn-sm mt-3 btn-success" data-bs-dismiss="modal" onclick="resetForm()">Ok</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="statusUpdateSuccessModal" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-body text-center p-lg-4">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="50" height="50" fill="#198754">
                    <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"/>
                </svg>
                <h4 class="text-success mt-3">Success</h4>
                <p class="mt-3">Updated successfully!</p>
                <button type="button" class="btn btn-sm mt-3 btn-success" data-bs-dismiss="modal" onclick="resetForm()">Ok</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="statusDeleteSuccessModal" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-body text-center p-lg-4">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="50" height="50" fill="#198754">
                    <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"/>
                </svg>
                <h4 class="text-success mt-3">Success</h4>
                <p class="mt-3">Deleted successfully!</p>
                <button type="button" class="btn btn-sm mt-3 btn-success" data-bs-dismiss="modal">Ok</button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="statusErrorsModal" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-body text-center p-lg-4">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="50" height="50" fill="#dc3545">
                    <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM368 367l-78-78-78 78c-4 4-9.3 4-13.3 0l-6.7-6.7c-4-4-4-9.3 0-13.3l78-78-78-78c-4-4-4-9.3 0-13.3l6.7-6.7c4-4 9.3-4 13.3 0l78 78 78-78c4-4 9.3-4 13.3 0l6.7 6.7c4 4 4 9.3 0 13.3l-78 78 78 78c4 4 4 9.3 0 13.3l-6.7 6.7c-4 4-9.3 4-13.3 0z"/>
                </svg>
                <h4 class="text-danger mt-3">Error</h4>
                <p class="mt-3">An error occurred. Please try again.</p>
                <button type="button" class="btn btn-sm mt-3 btn-danger" data-bs-dismiss="modal">Ok</button>
            </div>
        </div>
    </div>
</div>
