<?php echo form_open(get_uri("projects/save_Teams_Projects"), array("id" => "project-teams-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
    <input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />

    <div class="form-group" style="min-height: 50px">
        <label for="teams" class=" col-md-3"><?php echo lang('team'); ?></label>
        <div class="col-md-9">
            <div class="select-member-field">
                <div class="clearfix pb10">
                    <?php foreach ($teams as $kt) { ?>
                        <input type="checkbox" id="Check<?php echo $kt->id; ?>" value="<?php echo $kt->id; ?>" name="teams[]" class=" form-check-input" onchange="checkbox_validate(this.id)" />
                        <label class="form-check-label" for="inlineCheckbox"><?php echo $kt->title; ?></label><br/>
                    <?php }  ?>
                    
                </div>
            </div>
        </div>


    </div>

</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
    <button type="submit" id="submit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function() {
        $("#project-teams-form").appForm({
            onSuccess: function(result) {
                if (result.id !== "exists") {
                    for (i = 0; i < result.data.length; i++) {
                        $("#project-teams-table").appTable({
                            newData: result.data[i],
                            dataId: result.id[i]
                        });
                    }
                }


            }
        });
    });
    var sendbtn = document.getElementById('submit');
    sendbtn.disabled = true;

    function checkbox_validate(val) {
        var checker = document.getElementById(`${val}`);
        var countCheck = $('input:checkbox:checked').length;
        console.log(checker.checked);
        if (countCheck != 0) {
            sendbtn.disabled = false;
        } else {
            sendbtn.disabled = true;
        }
    };
</script>