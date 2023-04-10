<div class="general-form modal-body clearfix">
    <input type="hidden" name="id" value="<?php echo !empty($qrow)?$qrow->id:''; ?>" />
    <div class="form-group">
        <label for="quotation_date" class=" col-md-3"><?php echo lang('estimate_date'); ?></label>
        <div class="col-md-9">
            <input type="text" id="quotation_date" value="<?php echo !empty($qrow)?$qrow->doc_date:''; ?>" class="form-control" placeholder="<?php echo lang('estimate_date'); ?>" autocomplete="off">
        </div>
    </div>

    <div class="form-group">
        <label for="credit" class=" col-md-3">เครดิต</label>
        <div class="col-md-9" style="display: grid;grid-template-columns: auto auto;align-items: center; justify-items: center;justify-content: start;">
            <input type="number" id="credit" name="credit" value="<?php echo !empty($qrow)?$qrow->credit:''?>" class="form-control" placeholder="กรอกเลข 0 หากชำระเงินสด" autocomplete="off" >
            <div style="padding-left: 5px;"> วันหลังออกใบแจ้งหนี้</div>
        </div>
    </div>

    <div class="form-group">
        <label for="quotation_valid_until_date" class=" col-md-3"><?php echo lang('valid_until'); ?></label>
        <div class="col-md-9"><input type="text" id="quotation_valid_until_date" name="quotation_valid_until_date" value="<?php echo !empty($qrow)?$qrow->doc_valid_until_date:''; ?>" class="form-control" placeholder="<?php echo lang('valid_until'); ?>" autocomplete="off">
        </div>
    </div>

    <div class="form-group">
        <label for="reference_number" class=" col-md-3">เลขที่อ้างอิง</label>
        <div class="col-md-9"><input type="text" id="reference_number" name="reference_number" placeholder="#" class="form-control"></div>
    </div>

    <div class="form-group">
        <label for="vat_inc" class=" col-md-3">ราคาสินค้า</label>
        <div class="col-md-9">
            <select id="vat_inc" name="vat_inc" class="form-control">
                <option value="N" <?php if(!empty($qrow)) if($qrow->vat_inc == "N") echo "selected" ?>>ราคาไม่รวมภาษี</option>
                <option value="Y" <?php if(!empty($qrow)) if($qrow->vat_inc == "Y") echo "selected" ?>>ราคารวมภาษี</option>
            </select>
        </div>
    </div>
    
    <div class="form-group">
        <label for="client_id" class=" col-md-3"><?php echo lang('client'); ?></label>
        <div class="col-md-9">
            <?php $crows = $this->Clients_m->getRows(0); ?>
            <select id="client_id" class="form-control">
                <option value="-1">-</option>
                <?php foreach($crows as $crow): ?>
                    <option value="<?php echo $crow->id; ?>" <?php if(!empty($qrow)) if($qrow->client_id == $crow->id) echo "selected"?>><?php echo $crow->company_name; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="project_id" class=" col-md-3"><?php echo lang('project'); ?></label>
        <div class="col-md-9">
            <?php $prows = $this->Projects_m->getRows(); ?>
            <select id="project_id" class="form-control">
                <option value="-1">-</option>
                <?php foreach($prows as $prow): ?>
                    <option value="<?php echo $prow->id; ?>" <?php if(!empty($qrow)) if($qrow->project_id == $prow->id) echo "selected"?>><?php echo $prow->title; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="remark" class=" col-md-3">หมายเหตุ</label>
        <div class=" col-md-9">
            <textarea id="remark" name="remark" placeholder="หมายเหตุ" class="form-control"><?php echo !empty($qrow)?$qrow->remark:''; ?></textarea>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
    <button type="button" id="btnSubmit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
</div>
<script type="text/javascript">
$(document).ready(function() {
    $("#btnSubmit").click(function() {
        axios.post('<?php echo current_url(); ?>', {
            task: 'save',
            doc_id : "<?php echo (!empty($qrow)?$qrow->id:'')?>",
            quotation_date:$("#quotation_date").val(),
            credit: $("#credit").val(),
            quotation_valid_until_date: $("#quotation_valid_until_date").val(),
            reference_number: $("#reference_number").val(),
            client_id: $("#client_id").val(),
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

      
        }).catch(function (error) {

        });

    });

    $('#project_id').select2();
    $("#client_id").select2();

    setDatePicker("#quotation_date, #quotation_valid_until_date");

});
</script>