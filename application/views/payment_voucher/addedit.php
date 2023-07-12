<div class="general-form modal-body clearfix">
    <div class="form-group">
        <label for="doc_date" class=" col-md-3">วันที่</label>
        <div class="col-md-9">
            <input type="text" id="doc_date" class="form-control" autocomplete="off" readonly>
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
        <label for="supplier_id" class=" col-md-3">ผู้จัดจำหน่าย</label>
        <div class="col-md-9">
            <?php $suprows = $this->Suppliers_m->getRows(); ?>
            <select id="supplier_id" class="form-control">
                <option value="">-</option>
                <?php foreach($suprows as $suprow): ?>
                    <option value="<?php echo $suprow->id; ?>" <?php if($supplier_id == $suprow->id) echo "selected"?>><?php echo $suprow->company_name; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="project_id" class=" col-md-3"><?php echo lang('project'); ?></label>
        <div class="col-md-9">
            <?php $prows = $this->Projects_m->getRows(); ?>
            <select id="project_id" class="form-control">
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
            <textarea id="remark" name="remark" placeholder="หมายเหตุ" class="form-control"><?php echo $remark; ?></textarea>
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
        $('#supplier_id').select2();


        $("#btnSubmit").click(function() {
            axios.post('<?php echo current_url(); ?>', {
                task: 'save_doc',
                doc_id : "<?php if(isset($doc_id)) echo $doc_id; ?>",
                doc_date:$("#doc_date").val(),
                doc_valid_until_date: $("#doc_valid_until_date").val(),
                reference_number: $("#reference_number").val(),
                supplier_id: $("#supplier_id").val(),
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
            
        });

        doc_valid_until_date = $("#doc_valid_until_date").datepicker({
            yearRange: "<?php echo date('Y'); ?>",
            format: 'dd/mm/yyyy',
            changeMonth: true,
            changeYear: true,
            autoclose: true
        }).on("changeDate", function (e) {
            
        });

        doc_date.datepicker("setDate", "<?php echo date('d/m/Y', strtotime($doc_date)); ?>");
        doc_valid_until_date.datepicker("setDate", "<?php echo date('d/m/Y', strtotime($doc_valid_until_date)); ?>");

        
    <?php endif; ?>
});
</script>