<?php echo form_open(get_uri("project_types/save"), array("id" => "project-type-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <?php if(isset($prow)): ?>
        <input type="hidden" name="id" value="<?php echo $prow->id; ?>" />
    <?php endif; ?>
    <div class="form-group" style="min-height: 60px;">
        <label for="title" class=" col-md-3"><?php echo lang('title'); ?></label>
        <div class=" col-md-9">
            <?php
            echo form_input(array(
                "id" => "title",
                "name" => "title",
                "value" => isset($prow)?$prow->title:"",
                "class" => "form-control",
                "placeholder" => lang('title'),
                "autofocus" => true,
                "data-rule-required" => true,
                "data-msg-required" => lang("field_required"),
            ));
            ?>
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
        $("#project-type-form").appForm({
            onSuccess: function(result) {
                $("#project-type-table").appTable({newData: result.data, dataId: result.id});
            }
        });
        $("#title").focus();
    });
</script>    