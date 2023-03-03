<?php echo form_open(get_uri("payment_voucher_payments/save_payment"), array("id" => "invoice-payment-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
    <input type="hidden" name="payment_vouchers_id" value="<?php echo $payment_vouchers_id; ?>" />




    <?php echo $invoice_id_s ?>
    <?php echo $invoice_id_s1 ?>
    


    <div class="form-group">
        <label for="invoice_payment_invoices" class=" col-md-3"><?php echo lang('invoices'); ?></label>
        <div class="col-md-9">
            <?php
            echo form_input(array(
                "id" => "invoice_payment_invoices",
                "name" => "invoice_payment_invoices",
                "value" => $model_info->invoice1_id,
                "class" => "form-control",
                "type" => "number",
                "placeholder" => lang('invoices'),
                "data-rule-required" => true,
                "data-msg-required" => lang("field_required"),
            ));
            ?>
        </div>
    </div>

    <div class="form-group">
        <label for="invoice_date" class=" col-md-3">วันที่ใบแจ้งหนี้</label>
        <div class="col-md-9">
            <?php
            echo form_input(array(
                "id" => "invoice_payment_date",
                "name" => "invoice_payment_date",
                "value" => $model_info->payment_date,
                "class" => "form-control",
                "placeholder" => "วันที่ใบแจ้งหนี้",
                "autocomplete" => "off",
                "data-rule-required" => true,
                "data-msg-required" => lang("field_required")
            ));
            ?>
        </div>
    </div>
    <div class="form-group">
        <label for="taxnumber_id" class=" col-md-3">ใบกำกับภาษี</label>
        <div class="col-md-9">
            <?php
            echo form_input(array(
                "id" => "taxnumber_id",
                "name" => "taxnumber_id",
                "value" => $model_info->taxnumber_id,
                "class" => "form-control",
                "type" => "number",
                "placeholder" => "ใบกำกับภาษี",
                "data-rule-required" => true,
                "data-msg-required" => lang("field_required")
            ));
            ?>
        </div>
    </div>

    <div class="form-group">
        <label for="tax_date" class=" col-md-3">วันที่ใบกำกับภาษี</label>
        <div class="col-md-9">
            <?php
            echo form_input(array(
                "id" => "tax_date",
                "name" => "tax_date",
                "value" => $model_info->tax_date,
                "class" => "form-control",
                "placeholder" => "วันที่ใบแจ้งหนี้",
                "autocomplete" => "off",
                "data-rule-required" => true,
                "data-msg-required" => lang("field_required")
            ));
            ?>
        </div>
    </div>

    <div class="form-group">
        <label for="invoice_payment_method_id" class=" col-md-3"><?php echo lang('payment_method'); ?></label>
        <div class="col-md-9">
            <?php
            echo form_dropdown("invoice_payment_method_id", $payment_methods_dropdown, array($model_info->payment_method_id), "onchange='IsTransfer(this);' class='select2' ");
            ?>
        </div>
    </div>
	
	<?php echo $select_bank ?>


    

    <div class="form-group">
        <label for="number_bank" class=" col-md-3">เลขที่เช็ค เลขบัญชี่</label>
        <div class="col-md-9">
            <?php
            echo form_input(array(
                "id" => "number_bank",
                "name" => "number_bank",
                "value" => $model_info->number_bank,
                "class" => "form-control",
                "type" => "number",
                "placeholder" => 'เลขที่ หรือ เลขบัญชี่',
                "data-rule-required" => false,
                "data-msg-required" => lang("field_required"),
            ));
            ?>
        </div>
    </div>

    <div class="form-group">
        <label for="invoice_payment_amount" class=" col-md-3"><?php echo lang('amount'); ?></label>
        <div class="col-md-9">
            <?php
            echo form_input(array(
                "id" => "invoice_payment_amount",
                "name" => "invoice_payment_amount",
                "value" => $model_info->amount ? to_decimal_format($model_info->amount) : "",
                "class" => "form-control",
                "placeholder" => lang('amount'),
                "data-rule-required" => true,
                "data-msg-required" => lang("field_required"),
            ));
            ?>
            <span style="margin: 10px; color: red">*ใส่จำนวนเงินก่อน VAT 7%*</span>
        </div>
    </div>
    <div class="form-group">
        <label for="invoice_payment_note" class="col-md-3"><?php echo lang('note'); ?></label>
        <div class=" col-md-9">
            <?php
            echo form_textarea(array(
                "id" => "invoice_payment_note",
                "name" => "invoice_payment_note",
                "value" => $model_info->note ? $model_info->note : "",
                "class" => "form-control",
                "placeholder" => lang('description'),
                "data-rich-text-editor" => true
            ));
            ?>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo lang('save'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function() {
        $("#invoice-payment-form").appForm({
            onSuccess: function(result) {
                if (typeof RELOAD_VIEW_AFTER_UPDATE !== "undefined" && RELOAD_VIEW_AFTER_UPDATE) {
                    location.reload();
                } else {
                    location.reload();
                    return;
                    if ($("#invoice-payment-table").length) {
                        location.reload();
                        return;
                        //it's from invoice details view
                        $("#invoice-payment-table").appTable({
                            newData: result.data,
                            dataId: result.id
                        });
                        $("#invoice-total-section").html(result.invoice_total_view);
                        if (typeof updateInvoiceStatusBar == 'function') {
                            updateInvoiceStatusBar(result.invoice_id);
                        }
                    } else {
                        //it's from invoices list view
                        //update table data
                        location.reload();
                        //$("#" + $(".dataTable:visible").attr("id")).appTable({reload: true});
                    }
                }
            }
        });
        $("#invoice-payment-form .select2").select2();



        setDatePicker("#invoice_payment_date");
        setDatePicker("#tax_date");


    });
</script>