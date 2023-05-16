<div class="general-form modal-body clearfix">
    <div class="form-group">
        <label for="quotation_date" class=" col-md-3"><?php echo lang('estimate_date'); ?></label>
        <div class="col-md-9">
            <input type="text" id="quotation_date" value="<?php echo $doc_date; ?>" class="form-control" placeholder="<?php echo lang('estimate_date'); ?>" autocomplete="off">
        </div>
    </div>

    <div class="form-group">
        <label for="credit" class=" col-md-3">เครดิต (วัน)</label>
        <div class="col-md-9" style="display: grid;grid-template-columns: auto auto;align-items: center; justify-items: center;justify-content: start;">
            <input type="number" id="credit" value="<?php echo $credit; ?>" class="form-control" placeholder="กรอกเลข 0 หากชำระเงินสด" autocomplete="off" >
        </div>
    </div>

    <div class="form-group">
        <label for="quotation_valid_until_date" class=" col-md-3"><?php echo lang('valid_until'); ?></label>
        <div class="col-md-9"><input type="text" id="quotation_valid_until_date" value="<?php echo $doc_valid_until_date; ?>" class="form-control" placeholder="<?php echo lang('valid_until'); ?>" autocomplete="off">
        </div>
    </div>

    <div class="form-group">
        <label for="reference_number" class=" col-md-3">เลขที่อ้างอิง</label>
        <div class="col-md-9"><input type="text" id="reference_number" value="<?php echo $reference_number; ?>" placeholder="#" class="form-control"></div>
    </div>

    <div class="form-group">
        <label for="client_id" class=" col-md-3"><?php echo lang('client'); ?></label>
        <div class="col-md-9">
            <?php $crows = $this->Clients_m->getRows(0); ?>
            <select id="client_id" class="form-control">
                <option value="">-</option>
                <?php foreach($crows as $crow): ?>
                    <option value="<?php echo $crow->id; ?>" <?php if($client_id == $crow->id) echo "selected"?>><?php echo $crow->company_name; ?></option>
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
        $("#btnSubmit").click(function() {
            axios.post('<?php echo current_url(); ?>', {
                task: 'save_doc',
                doc_id : "<?php if(isset($doc_id)) echo $doc_id; ?>",
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
            }).catch(function (error) {});
        });

        $('#project_id').select2();
        $("#client_id").select2();
        setDatePicker("#quotation_date");
    <?php endif; ?>


    $("#quotation_date").change(function() {
        //alert($(this).setDate);
        //alert($(this).datepicker('getDate', '+1d'));
        /*var date2 = $('.pickupDate').datepicker('getDate', '+1d'); 
        date2.setDate(date2.getDate()+1); 
        $('.dropoffDate').datepicker('setDate', date2);*/

        //var date2 = $("#quotation_date").datepicker('getDate');
        //var nextDayDate = new Date();
        //nextDayDate.setDate(date2.getDate() + 5);
        //$('input').val(nextDayDate);
        //alert(nextDayDate);
        var date2 = $("#quotation_date").datepicker('getDate');
        date2.setDate(date2.getDate()+5);
        //var nextDayDate = new Date();
        //nextDayDate.setDate(date.getDate() + 2);
        //$("#quotation_valid_until_date").val($.datepicker.formatDate('dd-mm-yy', nextDayDate));
        //$("#quotation_valid_until_date").datepicker({ dateFormat: 'yy-mm-dd' }).val(nextDayDate);
        //alert($.datepicker.formatDate("dd-mm-yy", nextDayDate));
        alert(date2.getDate());


        //$('#datepicker').datepicker({ dateFormat: 'dd-mm-yy' }).val();
    });
});
</script>