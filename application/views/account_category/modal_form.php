<?php echo form_open(get_uri("account_category/post_category"), array("id" => "category-form", "class" => "general-form", "role" => "form")); ?>

<div class="modal-body clearfix">

    <div class="form-group">
        <label for="title" class="col-md-3"><?php echo lang('title'); ?></label>
        <div class="col-md-9">
            <?php echo form_input(array(
                "id" => "title",
                "name" => "title",
                "class" => "form-control",
                "placeholder" => lang('title'),
                "required" => true
            )); ?>
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
        $("#category-form").appForm({
            onSuccess: function(result) {
                console.log(result);

                $("#category-table").appTable(
                    { newData: result.data, dataId: result.id }
                );
            }
        });

        $("#title").focus();
    });
</script>