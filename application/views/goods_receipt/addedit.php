<div class="general-form modal-body clearfix">
    <div class="form-group">
        <label for="doc_date" class=" col-md-3"><?php echo lang('doc_date'); ?></label>
        <div class="col-md-9">
            <input type="text" id="doc_date" class="form-control" autocomplete="off" readonly>
        </div>
    </div>

    <!-- <div class="form-group">
        <label for="doc_type" class=" col-md-3"><?php // echo lang('po_type'); ?></label>
        <div class="col-md-9">
            <?php // $type_rows = $this->Bom_suppliers_model->getPurchaseType(); ?>
            <select id="doc_type" class="form-control">
                <?php // foreach($type_rows as $type): ?>
                    <option value="<?php // echo $type["id"]; ?>" <?php // if($doc_type == $type["id"]) echo "selected"?>><?php // echo $type["text"]; ?></option>
                <?php // endforeach; ?>
            </select>
        </div>
    </div> -->

    <!-- <div class="form-group">
        <label for="credit" class=" col-md-3">เครดิต (วัน)</label>
        <div class="col-md-9" style="display: grid;grid-template-columns: auto auto;align-items: center; justify-items: center;justify-content: start;">
            <input type="number" id="credit" value="<?php // echo $credit; ?>" class="form-control" autocomplete="off" >
        </div>
    </div> -->

    <!-- <div class="form-group">
        <label for="doc_valid_until_date" class=" col-md-3"><?php // echo lang('valid_until'); ?></label>
        <div class="col-md-9"><input type="text" id="doc_valid_until_date" class="form-control" autocomplete="off" readonly></div>
    </div> -->

    <div class="form-group">
        <label for="supplier_id" class=" col-md-3"><?php echo lang('suppliers'); ?></label>
        <div class="col-md-9">
            <?php $crows = $this->Bom_suppliers_model->getSupplierForGoodsReceipt(); ?>
            <select id="supplier_id" class="form-control">
                <option value="">-</option>
                <?php foreach($crows as $crow): ?>
                    <option value="<?php echo $crow->supplier_id; ?>" <?php if($supplier_id == $crow->supplier_id) echo "selected"?>><?php echo $crow->company_name; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <?php $po_list_placeholder = "เลือกใบสั่งซื้อที่ต้องการทำรับ"; ?>
    <div class="form-group">
        <label for="po_list" class="col-md-3"><?php echo lang('po_no'); ?></label>
        <div class="col-md-9">
            <input type="text" class="form-control validate-hidden" placeholder="<?php echo $po_list_placeholder; ?>" id="po_list" data-custom-multi-select-input="1">
        </div>
    </div>

    <div class="form-group">
        <label for="receive_date" class=" col-md-3"><?php echo lang('receive_date'); ?></label>
        <div class="col-md-9">
            <input type="text" id="receive_date" class="form-control" autocomplete="off" readonly>
        </div>
    </div>

    <?php $reference_number_placeholder = "เลขที่ใบส่งของหรือเลขที่ใบแจ้งหนี้ที่ได้รับจากผู้จัดจำหน่าย"; ?>
    <div class="form-group">
        <label for="reference_number" class=" col-md-3">เลขที่อ้างอิง</label>
        <div class="col-md-9"><input type="text" id="reference_number" value="<?php echo $reference_number; ?>" placeholder="<?php echo $reference_number_placeholder; ?>" class="form-control"></div>
    </div>

    <!-- <div class="form-group">
        <label for="project_id" class=" col-md-3"><?php // echo 'อ้างอิง' .  lang('project'); ?></label>
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

    <?php $remark_placeholder = "หมายเหตุ"; ?>
    <div class="form-group">
        <label for="remark" class=" col-md-3">หมายเหตุ</label>
        <div class=" col-md-9">
            <textarea id="remark" name="remark" placeholder="<?php echo $remark_placeholder; ?>" class="form-control" style="height: 120px;"><?php echo $remark; ?></textarea>
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
        $('#supplier_id').select2();
        $('#project_id').select2();
        $('#doc_type').select2();

        $('#supplier_id').on('change', async function() {
            await axios.post('<?php echo current_url(); ?>', {
                task: 'get_po_list',
                supplier_id: $(this).val()
            }).then(response => {
                // console.log(response);

                let po_list = [];
                if (response.status === 200 && response.data.status == "success") {
                    response.data.po_list.forEach(item => {
                        po_list.push({
                            "id": item.id, 
                            "text": item.doc_number
                        });
                    });
                    
                    $('#po_list').val("");
                    $('#po_list').select2({
                        tags: true,
                        data: po_list
                    });
                    

                    response.data.po_list.length === 0 ? $('#po_list').attr('readonly', true) : $('#po_list').removeAttr('readonly');
                }
            }).catch(error => {
                console.log(error);
            });
        });

        $("#btnSubmit").click(function() {
            let task_list = {
                task: 'save_doc',
                doc_date: $('#doc_date').val(),
                supplier_id: $('#supplier_id').val(),
                po_list: `[${$('#po_list').val()}]`,
                receive_date: $('#receive_date').val(),
                reference_number: $("#reference_number").val(),
                remark: $("#remark").val()
            };

            console.log(task_list);

            axios.post('<?php echo current_url(); ?>', {
                task: 'save_doc',
                doc_id: "<?php if(isset($doc_id)) echo $doc_id; ?>",
                doc_date: $("#doc_date").val(),
                supplier_id: $('#supplier_id').val(),
                po_list: `[${$('#po_list').val()}]`,
                receive_date: $('#receive_date').val(),
                reference_number: $("#reference_number").val(),
                remark: $("#remark").val()
            }).then(function (response) {
                data = response.data;
                console.log(data);
            }).catch(function (error) {});
        });

        doc_date = $("#doc_date").datepicker({
            yearRange: "<?php echo date('Y'); ?>",
            format: 'dd/mm/yyyy',
            changeMonth: true,
            changeYear: true,
            autoclose: true
        }).on("changeDate", function (e) {
            // cal_valid_date_from_credit();
        });

        receive_date = $("#receive_date").datepicker({
            yearRange: "<?php echo date('Y'); ?>",
            format: 'dd/mm/yyyy',
            changeMonth: true,
            changeYear: true,
            autoclose: true
        }).on("changeDate", function (e) {
            // cal_credit_from_valid_until_date();
        });

        doc_date.datepicker("setDate", "<?php echo date('d/m/Y', strtotime($doc_date)); ?>");
        receive_date.datepicker("setDate", "<?php echo date('d/m/Y', strtotime($doc_date)); ?>");

        // $("#credit").blur(function(){
        //     cal_valid_date_from_credit();
        // });
    <?php elseif ($doc_status == "A" || $doc_status == "R"): ?>
        $("#doc_date").val("<?php echo date('d/m/Y', strtotime($doc_date)); ?>");
        $("#receive_date").val("<?php echo date('d/m/Y', strtotime($doc_date)); ?>");

        $("#doc_date").prop("disabled", true);
        $("#credit").prop("disabled", true);
        $("#receive_date").prop("disabled", true);
        $("#reference_number").prop("disabled", true);
        $("#supplier_id").prop("disabled", true);
        $("#remark").prop("disabled", true);
    <?php endif; ?>

    <?php if (!empty($doc_id)): ?>
        // $("#doc_type").prop("disabled", true);
    <?php endif; ?>
});

// function cal_valid_date_from_credit(){
//     doc_date = $("#doc_date").datepicker('getDate');
//     credit = Number($("#credit").val());
//     if(credit < 0) credit = 0;
//     $("#credit").val(credit);
//     doc_date.setDate(doc_date.getDate() + credit);
//     $("#doc_valid_until_date").val(todate(doc_date));
// }

// function cal_credit_from_valid_until_date(){
//     doc_date = $("#doc_date").datepicker('getDate');
//     doc_valid_until_date = $("#doc_valid_until_date").datepicker('getDate');

//     if (doc_date > doc_valid_until_date) {
//         doc_date = new Date(doc_valid_until_date.getFullYear(),doc_valid_until_date.getMonth(),doc_valid_until_date.getDate());
//         $("#doc_date").datepicker("setDate", doc_date);
//     }

//     doc_date = $("#doc_date").datepicker('getDate').getTime();
//     doc_valid_until_date = $("#doc_valid_until_date").datepicker('getDate').getTime();
//     credit = Math.round(Math.abs((doc_valid_until_date - doc_date)/(24*60*60*1000)));
//     $("#credit").val(credit);
// }
</script>