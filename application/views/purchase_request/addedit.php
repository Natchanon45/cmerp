<style type="text/css">
    .for-credit {
        display: grid;
        grid-template-columns: auto auto;
        align-items: center;
        justify-items: center;
        justify-content: start;
    }
</style>

<div class="general-form modal-body clearfix">
    <?php if (isset($doc_number) && !empty($doc_number)): ?>
        <div class="form-group">
            <label for="doc_number" class="col-md-3"><?php echo lang("document_number"); ?></label>
            <div class="col-md-9">
                <input type="text" class="form-control" value="<?php echo $doc_number; ?>" disabled>
            </div>
        </div>
    <?php endif; ?>

    <div class="form-group">
        <label for="doc_date" class=" col-md-3"><?php echo lang("date"); ?></label>
        <div class="col-md-9">
            <input type="text" id="doc_date" class="form-control" autocomplete="off" readonly>
        </div>
    </div>

    <div class="form-group">
        <label for="doc_type" class=" col-md-3">
            <?php echo lang('pr_type'); ?>
        </label>
        <div class="col-md-9">
            <?php $type_rows = $this->Bom_suppliers_model->getPurchaseType(); ?>
            <select id="doc_type" class="form-control">
                <?php foreach ($type_rows as $type): ?>
                    <option value="<?php echo $type["id"]; ?>" <?php if ($pr_type == $type["id"]) echo "selected" ?>>
                        <?php echo $type["text"]; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="doc_valid_until_date" class=" col-md-3"><?php echo lang('valid_until'); ?></label>
        <div class="col-md-9">
            <input type="text" id="doc_valid_until_date" class="form-control" autocomplete="off" readonly>
        </div>
    </div>

    <div class="form-group">
        <label for="reference_number" class=" col-md-3"><?php echo lang('reference_number'); ?></label>
        <div class="col-md-9">
            <input type="text" id="reference_number" value="<?php echo $reference_number; ?>" placeholder="#" class="form-control">
        </div>
    </div>

    <?php if (isset($bom_supplier_read) && $bom_supplier_read): ?>
    <div class="form-group">
        <label for="supplier_id" class=" col-md-3"><?php echo lang('select_supplier'); ?></label>
        <div class="col-md-9">
            <?php $crows = $this->Bom_suppliers_model->getRows(); ?>
            <select id="supplier_id" class="form-control">
                <option value="">-</option>
                <?php foreach ($crows as $crow): ?>
                    <option value="<?php echo $crow->id; ?>" <?php if ($supplier_id == $crow->id) echo "selected" ?>>
                        <?php echo $crow->company_name; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <?php endif; ?>

    <div class="form-group">
        <label for="project_id" class=" col-md-3"><?php echo lang('project_refer'); ?></label>
        <div class="col-md-9">
            <?php $prows = $this->Projects_m->getRows(); ?>
            <select id="project_id" class="form-control">
                <option value="">-</option>
                <?php foreach ($prows as $prow): ?>
                    <option value="<?php echo $prow->id; ?>" <?php if ($project_id == $prow->id) echo "selected" ?>>
                        <?php echo $prow->title; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <?php $pr_placeholder = "เอกสารภายในใช้เพื่อขออนุมัติการสั่งซื้อ เป็นเอกสารที่บอกถึงความต้องการในการจัดซื้อว่าต้องการอะไรบ้าง เพื่อให้ฝ่ายจัดซื้อไปจัดหาวัตุดิบ, สินค้า, บริการหรือสิ่งของที่ต้องการ เพื่อเปรียบเทียบราคาของผู้จัดจำหน่่ายหรือผู้ให้บริการแต่ละเจ้า"; ?>
    <div class="form-group">
        <label for="remark" class=" col-md-3"><?php echo lang('remark'); ?></label>
        <div class=" col-md-9">
            <textarea id="remark" name="remark" placeholder="<?php echo $pr_placeholder; ?>" class="form-control" style="height: 120px;"><?php echo trim($remark); ?></textarea>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span>
        <?php echo lang('close'); ?>
    </button>
    <?php if (isset($bom_supplier_read) && $bom_supplier_read): ?>
        <?php if ($doc_status == "W" || !isset($doc_id)): ?>
            <button type="button" id="btnSubmit" class="btn btn-primary"><span class="fa fa-check-circle"></span>
                <?php echo (empty($doc_id)) ? lang('create') : lang('edit'); ?>
            </button>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        <?php if ($doc_status == "W" || !isset($doc_id)): ?>
            $('#supplier_id').select2();
            $('#project_id').select2();
            $('#doc_type').select2();

            $("#btnSubmit").click(function () {
                axios.post('<?php echo current_url(); ?>', {
                    task: 'save_doc',
                    doc_id: "<?php if (isset($doc_id) && !empty($doc_id)) echo $doc_id; ?>",
                    doc_date: $("#doc_date").val(),
                    doc_type: $("#doc_type").val(),
                    credit: 0,
                    doc_valid_until_date: $("#doc_valid_until_date").val(),
                    reference_number: $("#reference_number").val(),
                    supplier_id: $("#supplier_id").val(),
                    project_id: $("#project_id").val(),
                    remark: $("#remark").val()
                }).then(function (response) {
                    // console.log(data);
                    data = response.data;

                    $(".fnotvalid").remove();
                    if (data.status == "validate") {
                        for (var key in data.messages) {
                            if (data.messages[key] != "") {
                                $("<span class='fnotvalid'>" + data.messages[key] + "</span>").insertAfter("#" + key);
                            }
                        }
                    } else if (data.status == "success") {
                        window.location = data.target;
                    } else {
                        alert(data.message);
                    }
                }).catch(function (error) {
                    console.log(error);
                });
            });

            doc_date = $("#doc_date").datepicker({
                yearRange: "<?php echo date('Y'); ?>",
                format: 'dd/mm/yyyy',
                changeMonth: true,
                changeYear: true,
                autoclose: true
            }).on("changeDate", function () {
                // cal_valid_date_from_credit();
            });

            doc_valid_until_date = $("#doc_valid_until_date").datepicker({
                yearRange: "<?php echo date('Y'); ?>",
                format: 'dd/mm/yyyy',
                changeMonth: true,
                changeYear: true,
                autoclose: true
            }).on("changeDate", function () {
                // cal_credit_from_valid_until_date();
            });

            doc_date.datepicker("setDate", "<?php echo date('d/m/Y', strtotime($doc_date)); ?>");
            doc_valid_until_date.datepicker("setDate", "<?php echo date('d/m/Y', strtotime($doc_valid_until_date)); ?>");

            $("#credit").blur(function () {
                // cal_valid_date_from_credit();
            });
        <?php elseif ($doc_status == "A" || $doc_status == "R"): ?>
            $("#doc_date").val("<?php echo date('d/m/Y', strtotime($doc_date)); ?>");
            $("#doc_valid_until_date").val("<?php echo date('d/m/Y', strtotime($doc_valid_until_date)); ?>");

            $("#doc_date").prop("disabled", true);
            $("#doc_type").prop("disabled", true);
            // $("#credit").prop("disabled", true);
            $("#doc_valid_until_date").prop("disabled", true);
            $("#reference_number").prop("disabled", true);
            $("#supplier_id").prop("disabled", true);
            $("#project_id").prop("disabled", true);
            $("#remark").prop("disabled", true);
        <?php endif; ?>
        
        <?php if (!empty($doc_id)): ?>
            $("#doc_type").prop("disabled", true);
        <?php endif; ?>
    });

    function cal_valid_date_from_credit() {
        doc_date = $("#doc_date").datepicker('getDate');
        credit = Number($("#credit").val());
        
        if (credit < 0) credit = 0;
        $("#credit").val(credit);
        doc_date.setDate(doc_date.getDate() + credit);

        // console.log(doc_date);
        $("#doc_valid_until_date").val(todate(doc_date));
    }

    function cal_credit_from_valid_until_date() {
        doc_date = $("#doc_date").datepicker('getDate');
        doc_valid_until_date = $("#doc_valid_until_date").datepicker('getDate');

        if (doc_date > doc_valid_until_date) {
            doc_date = new Date(
                doc_valid_until_date.getFullYear(),
                doc_valid_until_date.getMonth(),
                doc_valid_until_date.getDate()
            );

            $("#doc_date").datepicker("setDate", doc_date);
        }

        doc_date = $("#doc_date").datepicker('getDate').getTime();
        doc_valid_until_date = $("#doc_valid_until_date").datepicker('getDate').getTime();
        credit = Math.round(Math.abs((doc_valid_until_date - doc_date) / (24 * 60 * 60 * 1000)));
        
        // console.log(credit);
        $("#credit").val(credit);
    }
</script>