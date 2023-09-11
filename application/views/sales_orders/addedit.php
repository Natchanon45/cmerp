<div class="general-form modal-body clearfix">
    <div class="form-group">
        <label for="doc_date" class=" col-md-3">วันที่</label>
        <div class="col-md-9">
            <input type="text" id="doc_date" class="form-control" autocomplete="off" readonly>
        </div>
    </div>

    <div class="form-group">
        <label for="reference_number" class=" col-md-3">เลขที่อ้างอิง</label>
        <div class="col-md-9"><input type="text" id="reference_number" value="<?php echo $reference_number; ?>" placeholder="#" class="form-control" <?php if($doc_status != "W" && isset($doc_id)) echo "disabled";?>></div>
    </div>

    <div class="form-group">
        <label for="project_title" class=" col-md-3">หัวเรื่อง</label>
        <div class="col-md-9"><input type="text" id="project_title" value="<?php echo $project_title; ?>" placeholder="หัวเรื่อง" class="form-control" <?php if($doc_status != "W" && isset($doc_id)) echo "disabled";?>></div>
    </div>

    <div class="form-group">
        <label for="client_id" class=" col-md-3"><?php echo lang('client'); ?></label>
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
        <label for="lead_id" class=" col-md-3">ลูกค้าผู้มุ่งหวัง</label>
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

    <div class="form-group">
        <label for="project_description" class=" col-md-3">คำบรรยาย</label>
        <div class=" col-md-9">
            <textarea id="project_description" placeholder="คำบรรยาย" class="form-control" <?php if($doc_status != "W" && isset($doc_id)) echo "disabled";?>><?php echo $project_description; ?></textarea>
        </div>
    </div>

    <div class="form-group">
        <label for="project_start_date" class=" col-md-3">วันที่เริ่ม</label>
        <div class="col-md-9">
            <input type="text" id="project_start_date" class="form-control" placeholder="วันที่เริ่ม" autocomplete="off" readonly>
        </div>
    </div>

    <div class="form-group">
        <label for="project_deadline" class=" col-md-3">วันกำหนดส่ง</label>
        <div class="col-md-9">
            <input type="text" id="project_deadline" class="form-control" placeholder="วันกำหนดส่ง" autocomplete="off" readonly>
        </div>
    </div>

    <div class="form-group">
        <label for="project_price" class=" col-md-3">ราคา</label>
        <div class="col-md-9">
            <input type="number" id="project_price" value="<?php echo number_format($project_price, 2); ?>" class="form-control numb" autocomplete="off" <?php if($doc_status != "W" && isset($doc_id)) echo "disabled";?>>
        </div>
    </div>

    <div class="form-group">
        <label for="remark" class=" col-md-3">หมายเหตุ</label>
        <div class=" col-md-9">
            <textarea id="remark" placeholder="หมายเหตุ" class="form-control" <?php if($doc_status != "W" && isset($doc_id)) echo "disabled";?>><?php echo $remark; ?></textarea>
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
    <?php if($doc_status == "W" || !isset($doc_id)): ?>
        $('#project_id').select2();

        $("#client_id").select2().on("change", function (e) {
            $("#lead_id").select2("val", "");
        });

        $("#lead_id").select2().on("change", function (e) {
            $("#client_id").select2("val", "");
        });

        $("#btnSubmit").click(function() {
            axios.post('<?php echo current_url(); ?>', {
                task: 'save_doc',
                doc_id : "<?php if(isset($doc_id)) echo $doc_id; ?>",
                doc_date:$("#doc_date").val(),
                reference_number: $("#reference_number").val(),
                project_title: $("#project_title").val(),
                client_id: $("#client_id").val(),
                lead_id: $("#lead_id").val(),project_description,
                project_description: $("#project_description").val(),
                project_start_date: $("#project_start_date").val(),
                project_deadline: $("#project_deadline").val(),
                project_price: $("#project_price").val(),
                remark: $("#remark").val()
            }).then(function (response) {
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
        $("#project_start_date").val("<?php echo date('d/m/Y', strtotime($project_start_date)); ?>");
        $("#project_deadline").val("<?php echo date('d/m/Y', strtotime($project_deadline)); ?>");
    <?php endif; ?>

    $(".numb").blur(function(){
        let price = tonum($("#project_price").val(), <?php echo $this->Settings_m->getDecimalPlacesNumber(); ?>);
        $("#project_price").val($.number(price, 2));
    });
});
</script>