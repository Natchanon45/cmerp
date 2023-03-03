<?php echo form_open(get_uri("receipt_taxinvoices/save_paySplit"), array("id" => "paySplit-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <input type="hidden" name="receipt_taxinvoice_id" value="<?php echo $model_info->id; ?>" />
    <input type="hidden" name="es_id" value="<?php echo $model_info->es_id; ?>" />
<?php 
    $ture_val = 0;
    if(isset($receipt_taxinvoice_total_summary->pay_spilter)){
        $val1 = ($receipt_taxinvoice_total_summary->pay_spilter*$receipt_taxinvoice_total_summary->tax_percentage)/100;
        $val2 = ($receipt_taxinvoice_total_summary->pay_spilter*$receipt_taxinvoice_total_summary->tax_percentage2)/100;
        if($receipt_taxinvoice_total_summary->tax_percentage == 7 ){           
            $ture_val = $receipt_taxinvoice_total_summary->pay_spilter + $val1 - $val2;
        }else{
            $ture_val = $receipt_taxinvoice_total_summary->pay_spilter - $val1 + $val2;
            
            
        }

        
    }
?>

    <div class="form-group">
        <label for="paySplit" class="col-md-3">แบ่งชำระ</label>
        <div class="col-md-4">
            <?php
            echo form_input(array(
                "id" => "paySplit",
                "name" => "paySpliter",
                "value" => $ture_val-$receipt_taxinvoice_total_summary->total_paid,
                "class" => "form-control",
                "autofocus" => "true",
                "data-rule-required" => true
            ));
            ?>
        </div>

    </div>
    <!-- <div class="form-group">
        <label for="receipt_taxinvoice_bill_date" class=" col-md-3"><?php echo lang('bill_date') ?></label>
        <div class="col-md-9">
            <?php
            echo form_input(array(
                "id" => "receipt_taxinvoice_bill_date",
                "name" => "receipt_taxinvoice_bill_date",
                "value" => $model_info->bill_date ? $model_info->bill_date : get_my_local_time("Y-m-d"),
                "class" => "form-control",
                "placeholder" => lang('bill_date'),
                "autocomplete" => "off",
                "data-rule-required" => true
            ))
            ?>
        </div>
    </div> -->
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
</div>
<?php echo form_close(); ?>
<script type="text/javascript">

    setDatePicker("#receipt_taxinvoice_bill_date, #receipt_taxinvoice_due_date");
    // $(document).ready(function () {
    //     $("#discount-form").appForm({
    //         onSuccess: function (result) {
    //             if (result.success && result.receipt_taxinvoice_total_view) {
    //                 $("#receipt_taxinvoice-total-section").html(result.receipt_taxinvoice_total_view);
    //             } else {
    //                 appAlert.error(result.message);
    //             }
    //         }
    //     });

    //     $("#discount-form .select2").select2();
    // });
</script>