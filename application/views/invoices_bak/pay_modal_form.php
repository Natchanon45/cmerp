<?php echo form_open(get_uri("invoices/save_paySplit"), array("id" => "paySplit-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <input type="hidden" name="invoice_id" value="<?php echo $model_info->id; ?>" />
    <input type="hidden" name="es_id" value="<?php echo $model_info->es_id; ?>" />
<?php 
    $ture_val = 0;
    if(isset($invoice_total_summary->pay_spilter)){
        $val1 = ($invoice_total_summary->pay_spilter*$invoice_total_summary->tax_percentage)/100;
        $val2 = ($invoice_total_summary->pay_spilter*$invoice_total_summary->tax_percentage2)/100;
        if($invoice_total_summary->tax_percentage == 7 ){           
            $ture_val = $invoice_total_summary->pay_spilter + $val1 - $val2;
        }else{
            $ture_val = $invoice_total_summary->pay_spilter - $val1 + $val2;
            
            
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
                "value" => $ture_val-$invoice_total_summary->total_paid,
                "class" => "form-control",
                "autofocus" => "true",
                "data-rule-required" => true
            ));
            ?>
        </div>

    </div>
    <!-- <div class="form-group">
        <label for="invoice_bill_date" class=" col-md-3"><?php echo lang('bill_date') ?></label>
        <div class="col-md-9">
            <?php
            echo form_input(array(
                "id" => "invoice_bill_date",
                "name" => "invoice_bill_date",
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

    setDatePicker("#invoice_bill_date, #invoice_due_date");
    // $(document).ready(function () {
    //     $("#discount-form").appForm({
    //         onSuccess: function (result) {
    //             if (result.success && result.invoice_total_view) {
    //                 $("#invoice-total-section").html(result.invoice_total_view);
    //             } else {
    //                 appAlert.error(result.message);
    //             }
    //         }
    //     });

    //     $("#discount-form .select2").select2();
    // });
</script>