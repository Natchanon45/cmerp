<?php echo form_open(get_uri("clients/save"), array("id" => "client-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>" />
    <?php $this->load->view("clients/client_form_fields"); ?>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
$(document).ready(function () {
    var ticket_id = "<?php echo $ticket_id; ?>";

    $("#client-form").appForm({
        onSuccess: function (result) {
            if (result.view === "details" || ticket_id) {
                appAlert.success(result.message, {duration: 10000});
                setTimeout(function () {
                    location.reload();
                }, 500);

            } else {
                $("#client-table").appTable({newData: result.data, dataId: result.id});
            }
        }
    });
    $("#company_name").focus();

     $('[data-toggle="tooltip"]').tooltip();

    <?php if (isset($currency_dropdown)) { ?>
            if ($('#currency').length) {
                $('#currency').select2({data: <?php echo json_encode($currency_dropdown); ?>});
            }
    <?php } ?>

    <?php if (isset($groups_dropdown)) { ?>
            $("#group_ids").select2({
                multiple: true,
                data: <?php echo json_encode($groups_dropdown); ?>
            });
    <?php } ?>

    <?php if ($this->login_user->is_admin || get_array_value($this->login_user->permissions, "client") === "all") { ?>
            $('#created_by').select2({data: <?php echo $team_members_dropdown; ?>});
    <?php } ?>
});
</script>    