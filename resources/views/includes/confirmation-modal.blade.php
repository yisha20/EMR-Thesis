<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content emr-confirmation-modal">
            <div class="modal-header">
                <div class="emr-confirmation-heading">
                    <span class="emr-confirmation-icon" aria-hidden="true">
                        <i class="fa fa-question"></i>
                    </span>
                    <div>
                        <small>EMR confirmation</small>
                        <h5 class="modal-title" id="confirmationModalTitle">Confirm action</h5>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="confirmationModalMessage">Are you sure you want to continue?</div>
            <div class="modal-footer">
                <button type="button" class="btn emr-confirmation-cancel" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn emr-confirmation-confirm" id="confirmationModalConfirm">
                    <i class="fa fa-check"></i>
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>
