<div class="general-form modal-body clearfix">
    <div class="form-group">
        <label for="doc_date" class=" col-md-3">วันที่</label>
        <div class="col-md-9">
            <input type="text" id="doc_date" class="form-control" autocomplete="off" readonly>
        </div>
    </div>

    <div class="form-group">
        <label for="doc_type" class=" col-md-3"><?php echo lang('pr_type'); ?></label>
        <div class="col-md-9">
            <?php $type_rows = $this->Bom_suppliers_model->getPrType(); ?>
            <select id="doc_type" class="form-control">
                <option value="">-</option>
                <?php foreach($type_rows as $type): ?>
                    <option value="<?php echo $type->id; ?>" <?php if($pr_type == $type->id) echo "selected"?>><?php echo lang($type->keyword); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="credit" class=" col-md-3">เครดิต (วัน)</label>
        <div class="col-md-9" style="display: grid;grid-template-columns: auto auto;align-items: center; justify-items: center;justify-content: start;">
            <input type="number" id="credit" value="<?php echo $credit; ?>" class="form-control" autocomplete="off" >
        </div>
    </div>

    <div class="form-group">
        <label for="doc_valid_until_date" class=" col-md-3"><?php echo lang('valid_until'); ?></label>
        <div class="col-md-9"><input type="text" id="doc_valid_until_date" class="form-control" autocomplete="off" readonly></div>
    </div>

    <div class="form-group">
        <label for="reference_number" class=" col-md-3">เลขที่อ้างอิง</label>
        <div class="col-md-9"><input type="text" id="reference_number" value="<?php echo $reference_number; ?>" placeholder="#" class="form-control"></div>
    </div>

    <div class="form-group">
        <label for="supplier_id" class=" col-md-3"><?php echo lang('select_supplier'); ?></label>
        <div class="col-md-9">
            <?php $crows = $this->Bom_suppliers_model->getRows(); ?>
            <select id="supplier_id" class="form-control">
                <option value="">-</option>
                <?php foreach($crows as $crow): ?>
                    <option value="<?php echo $crow->id; ?>" <?php if($supplier_id == $crow->id) echo "selected"?>><?php echo $crow->company_name; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- <div class="form-group">
        <label for="lead_id" class=" col-md-3">ลูกค้าผู้มุ่งหวัง</label>
        <div class="col-md-9">
            <?php // $lrows = $this->Leads_m->getRows(); ?>
            <select id="lead_id" class="form-control">
                <option value="">-</option>
                <?php // foreach($lrows as $lrow): ?>
                    <option value="<?php // echo $lrow->id; ?>" <?php // if($lead_id == $lrow->id) echo "selected"?>><?php // echo $lrow->company_name; ?></option>
                <?php // endforeach; ?>
            </select>
        </div>
    </div> -->

    <!-- <div class="form-group">
        <label for="project_id" class=" col-md-3"><?php // echo lang('project'); ?></label>
        <div class="col-md-9">
            <?php // $prows = $this->Projects_m->getRows(); ?>
            <select id="project_id" class="form-control">
                <option value="">-</option>
                <?php // foreach($prows as $prow): ?>
                    <option value="<?php // echo $prow->id; ?>" <?php // if($project_id == $prow->id) echo "selected"?>><?php // echo $prow->title; ?></option>
                <?php // endforeach; ?>
            </select>
        </div>
    </div> -->

    <div class="form-group">
        <label for="remark" class=" col-md-3">หมายเหตุ</label>
        <div class=" col-md-9">
            <textarea id="remark" name="remark" placeholder="หมายเหตุ" class="form-control"><?php echo $remark; ?></textarea>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
    <?php if($doc_status == "1" || !isset($doc_id)): ?>
        <button type="button" id="btnSubmit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
    <?php endif; ?>
</div>

<script type="text/javascript">
$(document).ready(function() {
    <?php if($doc_status == "1" || !isset($doc_id)): ?>
        $('#supplier_id').select2();

        $("#btnSubmit").click(function() {
            axios.post('<?php echo current_url(); ?>', {
                task: 'save_doc',
                doc_id : "<?php if(isset($doc_id)) echo $doc_id; ?>",
                doc_date:$("#doc_date").val(),
                doc_type: $("#doc_type").val(),
                credit: $("#credit").val(),
                doc_valid_until_date: $("#doc_valid_until_date").val(),
                reference_number: $("#reference_number").val(),
                supplier_id: $("#supplier_id").val(),
                remark: $("#remark").val()
            }).then(function (response) {
                data = response.data;
                console.log(data);

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
            cal_valid_date_from_credit();
        });

        doc_valid_until_date = $("#doc_valid_until_date").datepicker({
            yearRange: "<?php echo date('Y'); ?>",
            format: 'dd/mm/yyyy',
            changeMonth: true,
            changeYear: true,
            autoclose: true
        }).on("changeDate", function (e) {
            cal_credit_from_valid_until_date();
        });

        doc_date.datepicker("setDate", "<?php echo date('d/m/Y', strtotime($doc_date)); ?>");
        doc_valid_until_date.datepicker("setDate", "<?php echo date('d/m/Y', strtotime($doc_valid_until_date)); ?>");

        $("#credit").blur(function(){
            cal_valid_date_from_credit();
        });        
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