<div class="modal-body clearfix">
    <div>
        <div class="alert alert-danger">
            <?php echo (isset($message) && !empty($message)) ? $message : ''; ?>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">
        <span class="fa fa-close"></span>
        <?php echo lang("close"); ?>
    </button>
</div>