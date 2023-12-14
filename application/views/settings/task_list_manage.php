<style type="text/css">
#s2id_collaborators, #dropdown-apploader-section .select2-choices{
    min-height: 80px !important;
    height: 80px !important;
}

#dropdown-apploader-section .select2-choices{
    overflow-y: scroll;
}
</style>
<?php echo form_open(current_url(), array("id" => "mainform", "class" => "general-form", "role" => "form")); ?>
    <input type="hidden" name="task" value="save">
    <?php if(isset($row)): ?>
        <input type="hidden" name="id" value="<?php echo $prow->id; ?>" />
    <?php endif; ?>
    <div class="modal-body clearfix">
        <div class="form-group">
            <label for="title" class="col-md-3">ชื่องาน</label>
            <div class=" col-md-9"><input type="text" id="title" name="title" value="<?php echo isset($row)?$row->title:''?>" class="form-control" placeholder="<?php echo lang('title'); ?>" data-rule-required="true" data-msg-required="<?php echo lang("field_required")?>"></div>
        </div>
        <div class="form-group">
            <label for="description" class=" col-md-3"><?php echo lang('description'); ?></label>
            <div class=" col-md-9">
                <textarea id="description" name="description" class="form-control" placeholder="<?php echo lang('description'); ?>" data-rich-text-editor="true"><?php echo isset($row)?$row->description:""; ?></textarea>
            </div>
        </div>

        <div class="form-group">
            <label for="assigned_to" class="col-md-3"><?php echo lang('assign_to'); ?></label>
            <div class="col-md-9">
                <input type="text" id="assigned_to" name="assigned_to" class="form-control" value="<?php echo isset($row)?$row->assigned_to:''?>">
            </div>
        </div>

        <div class="form-group">
            <label for="collaborators" class="col-md-3"><?php echo lang('collaborators'); ?></label>
            <div class="col-md-9" id="dropdown-apploader-section">
                <input type="text" id="collaborators" name="collaborators" value="<?php echo isset($row)?$row->collaborators:''?>" class="form-control">
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
        <button type="submit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
    </div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function() {
        $("#mainform").appForm({
            onSuccess: function(result) {
                $("#project-type-table").appTable({newData: result.data, dataId: result.id});
            }
        });
        

        //$('#assigned_to').select2({data: <?php //echo json_encode($assign_to_dropdown); ?>});

        $("#collaborators").select2({multiple: true, data: <?php echo json_encode($dropdown_collaborators); ?>});
        $("#assigned_to").select2({data: <?php echo json_encode($dropdown_assigned_to); ?>});

       
    });
</script>    