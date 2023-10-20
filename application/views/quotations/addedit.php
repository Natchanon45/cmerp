<div class="general-form modal-body clearfix">
    <div class="form-group">
        <label for="doc_date" class=" col-md-3">ออก ณ วันที่</label>
        <div class="col-md-9">
            <input type="text" id="doc_date" class="form-control" autocomplete="off" readonly>
        </div>
    </div>

    <div class="form-group">
        <label for="doc_valid_until_date" class=" col-md-3">ยืนราคาถึง</label>
        <div class="col-md-9"><input type="text" id="doc_valid_until_date" class="form-control" autocomplete="off" readonly></div>
    </div>

    <div class="form-group">
        <label for="credit" class=" col-md-3">เครดิต (วัน)</label>
        <div class="col-md-9" style="display: grid;grid-template-columns: auto auto;align-items: center; justify-items: center;justify-content: start;">
            <input type="number" id="credit" value="<?php echo $credit; ?>" class="form-control" autocomplete="off" <?php if($doc_status != "W" && isset($doc_id)) echo "disabled";?> >
        </div>
    </div>

    <div class="form-group">
        <label for="reference_number" class=" col-md-3">เลขที่อ้างอิง</label>
        <div class="col-md-9"><input type="text" id="reference_number" value="<?php echo $reference_number; ?>" placeholder="#" class="form-control" <?php if($doc_status != "W" && isset($doc_id)) echo "disabled";?>></div>
    </div>

    <div class="form-group">
        <label for="seller_id" class="col-md-3">ผู้ขาย</label>
        <div class="col-md-9">
            <?php $srows = $this->Users_m->getRows(["id", "first_name", "last_name"]); ?>
            <select id="seller_id" class="form-control" <?php if($doc_status != "W" && isset($doc_id)) echo "disabled";?>>
                <option value="">-</option>
                <?php foreach($srows as $srow): ?>
                    <option value="<?php echo $srow->id; ?>" <?php if($seller_id == $srow->id) echo "selected"?>><?php echo $srow->first_name." ".$srow->last_name; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
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
        <label for="project_id" class=" col-md-3"><?php echo lang('project'); ?></label>
        <div class="col-md-9">
            <?php $prows = $this->Projects_m->getRows(); ?>
            <select id="project_id" class="form-control" <?php if($doc_status != "W" && isset($doc_id)) echo "disabled";?>>
                <option value="">-</option>
                <?php foreach($prows as $prow): ?>
                    <option value="<?php echo $prow->id; ?>" <?php if($project_id == $prow->id) echo "selected"?>><?php echo $prow->title; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label for="remark" class=" col-md-3">หมายเหตุ</label>
        <div class=" col-md-9">
            <textarea id="remark" name="remark" placeholder="หมายเหตุ" class="form-control" <?php if($doc_status != "W" && isset($doc_id)) echo "disabled";?>><?php echo $remark; ?></textarea>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
    <?php if(($doc_status == "W" || !isset($doc_id)) || (isset($doc_id) && $task == "copy_doc")): ?>
        <button type="button" id="btnSubmit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang($task == "copy_doc"?"copy":"save"); ?></button>
    <?php endif; ?>
</div>

<script type="text/javascript">
$(document).ready(function() {
    <?php if(($doc_status == "W" || !isset($doc_id)) || (isset($doc_id) && $task == "copy_doc")): ?>
        $('#project_id').select2();

        $("#client_id").select2().on("change", function (e) {
            $("#lead_id").select2("val", "");
        });

        $("#lead_id").select2().on("change", function (e) {
            $("#client_id").select2("val", "");
        });

        $("#btnSubmit").click(function() {
            axios.post('<?php echo current_url(); ?>', {
                task: "<?php echo $task; ?>",
                doc_id : "<?php if(isset($doc_id)) echo $doc_id; ?>",
                doc_date:$("#doc_date").val(),
                credit: $("#credit").val(),
                doc_valid_until_date: $("#doc_valid_until_date").val(),
                reference_number: $("#reference_number").val(),
                seller_id: $("#seller_id").val(),
                client_id: $("#client_id").val(),
                lead_id: $("#lead_id").val(),
                project_id: $("#project_id").val(),
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

        doc_valid_until_date = $("#doc_valid_until_date").datepicker({
            yearRange: "<?php echo date('Y'); ?>",
            format: 'dd/mm/yyyy',
            changeMonth: true,
            changeYear: true,
            autoclose: true
        }).on("changeDate", function (e) {
            //cal_credit_from_valid_until_date();
        });

        doc_date.datepicker("setDate", "<?php echo date('d/m/Y', strtotime($doc_date)); ?>");
        doc_valid_until_date.datepicker("setDate", "<?php echo date('d/m/Y', strtotime($doc_valid_until_date)); ?>");

        $("#credit").blur(function(){
            //cal_valid_date_from_credit();
        });
    <?php else: ?>
        $("#doc_date").val("<?php echo date('d/m/Y', strtotime($doc_date)); ?>");
        $("#doc_valid_until_date").val("<?php echo date('d/m/Y', strtotime($doc_valid_until_date)); ?>");
    <?php endif; ?>
});

function cal_valid_date_from_credit(){
    doc_date = $("#doc_date").datepicker('getDate');
    credit = Number($("#credit").val());
    if(credit < 0) credit = 0;
    $("#credit").val(credit);
    doc_date.setDate(doc_date.getDate() + credit);
    $("#doc_valid_until_date").val(todate(doc_date));
}

function cal_credit_from_valid_until_date(){
    doc_date = $("#doc_date").datepicker('getDate');
    doc_valid_until_date = $("#doc_valid_until_date").datepicker('getDate');

    if (doc_date > doc_valid_until_date) {
        doc_date = new Date(doc_valid_until_date.getFullYear(),doc_valid_until_date.getMonth(),doc_valid_until_date.getDate());
        $("#doc_date").datepicker("setDate", doc_date);
    }

    doc_date = $("#doc_date").datepicker('getDate').getTime();
    doc_valid_until_date = $("#doc_valid_until_date").datepicker('getDate').getTime();
    credit = Math.round(Math.abs((doc_valid_until_date - doc_date)/(24*60*60*1000)));
    $("#credit").val(credit);
}
</script>