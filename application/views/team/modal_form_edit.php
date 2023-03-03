<?php echo form_open(get_uri("team/save"), array("id" => "team-form", "class" => "general-form", "role" => "form")); ?>
    <div class="modal-body clearfix">
    <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
    <div class="form-group">
        <label for="title" class=" col-md-3"><?php echo lang('title'); ?></label>
        <?php echo validation_errors(); ?>
        <div class=" col-md-9">
            <?php
            echo form_input(array(
                "id" => "title",
                "name" => "title",
                "value" => $model_info->title,
                "class" => "form-control",
                "placeholder" => lang('title'),
                "autofocus" => true,
                "data-rule-required" => true,
                "data-msg-required" => lang("field_required"),
            ));
            ?>
        </div>
    </div>
    
    <!-- วนลูปเมนบอร์ด-->
    <div class="form-group">
    <label for="members" class=" col-md-3"><?php echo lang('team_members'); ?></label>
        <div class="form-check form-check-inline col-md-9">
        <?php foreach ($urows as $urow) { ?>
            <input type="checkbox" value="<?php echo $urow->id; ?>"  name="memberids[]" <?php if(in_array($urow->id, $members)) echo "checked"; ?> />
            <label class="form-check-label" for="inlineCheckbox"><?php echo $urow->first_name . ' ' . $urow->last_name; ?></label><br/>
        <?php } ?>
        </div><br/>
        <span id="check_error_msg" style="color: red;"></span>
    </div>
    
    
     <!-- End form Check -->
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
    <button type="submit" id="submit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function() {
        $("#team-form").appForm({
            onSuccess: function(result) {
                $("#team-table").appTable({newData: result.data, dataId: result.id});
             }
        });
    });

    var sendbtn = document.getElementById('submit');
    //sendbtn.disabled = false;

    function checkbox_validate(id,val){
        var checker = document.getElementById(`${id}`);
        var countCheck = $('input:checkbox:checked').length;
        console.log(countCheck);
        if(countCheck != 0){
           sendbtn.disabled = false;
        }else{
            sendbtn.disabled = true;
        }
    };

    
</script>    