<style type="text/css">
#s2id_task_list, #s2id_task_list .select2-choices{
    min-height: 80px !important;
    height: 80px !important;word-wrap: break-word;
}

#s2id_task_list .select2-choices{
    overflow-y: scroll;
}


</style>
<div class="general-form modal-body clearfix">
    <div class="form-group">
        <label for="doc_date" class=" col-md-3"><?php echo lang('account_issue_date'); ?></label>
        <div class="col-md-9">
            <input type="text" id="doc_date" class="form-control" autocomplete="off" readonly>
        </div>
    </div>

    <div class="form-group">
        <label for="reference_number" class=" col-md-3"><?php echo lang('account_refernce_no'); ?></label>
        <div class="col-md-9"><input type="text" id="reference_number" value="<?php echo $reference_number; ?>" placeholder="#" class="form-control" <?php if($doc_status != "W" && isset($doc_id)) echo "disabled";?>></div>
    </div>

    <div class="form-group">
        <label for="purpose" class=" col-md-3"><?php echo lang('account_purpose'); ?></label>
        <div class="col-md-9">
            <select id="purpose" class="form-control" <?php if(isset($doc_id)) echo "disabled";?>>
                <option value="P" <?php if($purpose == "P") echo "selected"; ?>><?php echo lang('account_docname_production_order'); ?></option>
                <option value="S" <?php if($purpose == "S") echo "selected"; ?>><?php echo lang('account_docname_sales_order'); ?></option>
            </select>
        </div>
    </div>

    <div class="form-group objective project">
        <label for="project_title" class=" col-md-3"><?php echo lang('title'); ?></label>
        <div class="col-md-9"><input type="text" id="project_title" value="<?php echo $project_title; ?>" placeholder="<?php echo lang('title'); ?>" class="form-control" <?php if($doc_status != "W" && isset($doc_id)) echo "disabled";?>></div>
    </div>

    <div class="form-group objective project">
        <label for="type" class=" col-md-3">ประเภทโปรเจค</label>
        <div class=" col-md-9">
            <select id="project_type_id" name="project_type_id" class="select2 validate-hidden" data-msg-required="<?php echo lang('field_required'); ?>" data-rule-required='true'>
                <option value="">- เลือกประเภทโปรเจค -</option>
                <?php if(!empty($dropdown_project_types)): ?>
                    <?php foreach($dropdown_project_types as $project_type): ?>
                        <option value="<?php echo $project_type->id; ?>" <?php if($project_type->id == $project_type_id) echo "selected"; ?>><?php echo $project_type->title; ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <div class="form-group objective project">
        <label for="type" class=" col-md-3">รายการงาน</label>
        <div class="col-md-9">
            <input type="text" id="task_list" name="task_list" value="<?php echo $project_task_ids; ?>" class="form-control">
        </div>
    </div>

    <div class="form-group">
        <label for="client_id" class=" col-md-3"><?php echo lang('account_client'); ?></label>
        <div class="col-md-9">
            <?php $crows = $this->Clients_m->getRows(); ?>
            <select id="client_id" class="form-control" <?php if($doc_status != "W" && isset($doc_id)) echo "disabled";?>>
                <option value="">-</option>
                <?php foreach($crows as $crow): ?>
                    <option value="<?php echo $crow->id; ?>" <?php if($client_id == $crow->id) echo "selected"?>><?php echo $crow->company_name; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="lead_id" class=" col-md-3"><?php echo lang('account_lead'); ?></label>
        <div class="col-md-9">
            <?php $lrows = $this->Leads_m->getRows(); ?>
            <select id="lead_id" class="form-control" <?php if($doc_status != "W" && isset($doc_id)) echo "disabled";?>>
                <option value="">-</option>
                <?php foreach($lrows as $lrow): ?>
                    <option value="<?php echo $lrow->id; ?>" <?php if($lead_id == $lrow->id) echo "selected"?>><?php echo $lrow->company_name; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group objective project">
        <label for="project_description" class=" col-md-3"><?php echo lang('description'); ?></label>
        <div class=" col-md-9">
            <textarea id="project_description" placeholder="<?php echo lang('description'); ?>" class="form-control" <?php if($doc_status != "W" && isset($doc_id)) echo "disabled";?>><?php echo $project_description; ?></textarea>
        </div>
    </div>

    <div class="form-group objective project">
        <label for="project_start_date" class=" col-md-3"><?php echo lang('start_date'); ?></label>
        <div class="col-md-9">
            <input type="text" id="project_start_date" class="form-control" placeholder="<?php echo lang('start_date'); ?>" autocomplete="off" readonly>
        </div>
    </div>

    <div class="form-group objective project">
        <label for="project_deadline" class=" col-md-3"><?php echo lang('deadline'); ?></label>
        <div class="col-md-9">
            <input type="text" id="project_deadline" class="form-control" placeholder="<?php echo lang('deadline'); ?>" autocomplete="off" readonly>
        </div>
    </div>

    <div class="form-group objective project">
        <label for="project_price" class=" col-md-3"><?php echo lang('price'); ?></label>
        <div class="col-md-9">
            <input type="number" id="project_price" value="<?php echo number_format($project_price, 2); ?>" class="form-control numb" autocomplete="off" <?php if($doc_status != "W" && isset($doc_id)) echo "disabled";?>>
        </div>
    </div>

    <div class="form-group">
        <label for="remark" class=" col-md-3"><?php echo lang('account_remarks'); ?></label>
        <div class=" col-md-9">
            <textarea id="remark" placeholder="<?php echo lang('account_remarks'); ?>" class="form-control" <?php if($doc_status != "W" && isset($doc_id)) echo "disabled";?>><?php echo $remark; ?></textarea>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
    <?php if($doc_status == "W" || !isset($doc_id)): ?>
        <button type="button" id="btnSubmit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
    <?php endif; ?>
</div>

<script type="text/javascript">
$(document).ready(function() {
    setPopupSize($("#purpose").val());

    <?php if($doc_status == "W" || !isset($doc_id)): ?>
        $("#project_type_id").select2();
        $("#task_list").select2({
            multiple: true,
            data: <?php echo json_encode($dropdown_task_list); ?>
        });
        
        $("#client_id").select2().on("change", function (e) {
            $("#lead_id").select2("val", "");
        });

        $("#lead_id").select2().on("change", function (e) {
            $("#client_id").select2("val", "");
        });

        $("#purpose").on("change", function() {
            setPopupSize($(this).val());
        });

        $("#btnSubmit").click(function() {
            data = {
                task: 'save_doc',
                doc_id : "<?php if(isset($doc_id)) echo $doc_id; ?>",
                purpose: $("#purpose").val(),
                doc_date:$("#doc_date").val(),
                reference_number: $("#reference_number").val(),
                client_id: $("#client_id").val(),
                lead_id: $("#lead_id").val(),
                remark: $("#remark").val()
            };

            if($("#purpose").val() == "P"){
                data.project_title = $("#project_title").val();
                data.project_type_id = $("#project_type_id").val();
                data.project_task_ids = $("#task_list").val();
                data.project_description = $("#project_description").val();
                data.project_start_date = $("#project_start_date").val();
                data.project_deadline = $("#project_deadline").val();
                data.project_price = $("#project_price").val();
            }

            axios.post('<?php echo current_url(); ?>', data).then(function (response) {
                data = response.data;
                $(".fnotvalid").remove();

                if(data.status == "validate"){
                    for(var key in data.messages){
                        if(data.messages[key] != ""){
                            $("<span class='fnotvalid'>"+data.messages[key]+"</span>").insertAfter("#"+key);
                        }
                    }
                }else if(data.status == "success"){
                    window.location = data.target;
                }else{
                    alert(data.message);
                }
            }).catch(function (error) {});
        });

        doc_date = $("#doc_date").datepicker({
            yearRange: "<?php echo date('Y'); ?>",
            format: 'dd/mm/yyyy',
            changeMonth: true,
            changeYear: true,
            autoclose: true
        }).on("changeDate", function (e) {
            //cal_valid_date_from_credit();
        });

        project_start_date = $("#project_start_date").datepicker({
            yearRange: "<?php echo date('Y'); ?>",
            format: 'dd/mm/yyyy',
            changeMonth: true,
            changeYear: true,
            autoclose: true
        }).on("changeDate", function (e) {
            //cal_valid_date_from_credit();
        });

        project_deadline = $("#project_deadline").datepicker({
            yearRange: "<?php echo date('Y'); ?>",
            format: 'dd/mm/yyyy',
            changeMonth: true,
            changeYear: true,
            autoclose: true
        }).on("changeDate", function (e) {
            //cal_valid_date_from_credit();
        });

        doc_date.datepicker("setDate", "<?php echo date('d/m/Y', strtotime($doc_date)); ?>");
        <?php if($project_start_date != null): ?>
            project_start_date.datepicker("setDate", "<?php echo date('d/m/Y', strtotime($project_start_date)); ?>");
        <?php endif;?>

        <?php if($project_deadline != null): ?>
            project_deadline.datepicker("setDate", "<?php echo date('d/m/Y', strtotime($project_deadline)); ?>");
        <?php endif;?>

    <?php else: ?>
        $("#doc_date").val("<?php echo date('d/m/Y', strtotime($doc_date)); ?>");
        <?php if($project_start_date != null): ?>
            $("#project_start_date").val("<?php echo date('d/m/Y', strtotime($project_start_date)); ?>");
        <?php endif;?>
        <?php if($project_deadline != null): ?>
        $("#project_deadline").val("<?php echo date('d/m/Y', strtotime($project_deadline)); ?>");
        <?php endif;?>
    <?php endif; ?>

    $(".numb").blur(function(){
        let price = tonum($("#project_price").val(), <?php echo $this->Settings_m->getDecimalPlacesNumber(); ?>);
        $("#project_price").val($.number(price, 2));
    });
});

function setPopupSize(purpose){
    $(".fnotvalid").remove();
    if(purpose == "P"){
        $(".objective.project").css("display", "block");
        $(".modal-content").css("height", "610px");
        $(".general-form").css("height", "488px");
        $(".general-form").css("overflow-y", "scroll");
    }else{
        $(".objective.project").css("display", "none");
        $(".modal-content").css("height", "488px");
        $(".general-form").css("height", "366px");
        $(".general-form").css("overflow-y", "scroll");
    }
}
</script>