<?php echo form_open(get_uri("receipt_taxinvoice_payments/save_payment"), array("id" => "receipt_taxinvoice-payment-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />

    <?php if ( $receipt_taxinvoice_id ) { ?>
        <input type="hidden" name="receipt_taxinvoice_id" value="<?php echo $receipt_taxinvoice_id; ?>" />
    <?php } else { ?>
        <div class="form-group">
            <label for="receipt_taxinvoice_id" class=" col-md-3"><?php echo lang('receipt_taxinvoice'); ?></label>
            <div class="col-md-9">
                <?php
				
				echo $dsdfsdsfads;
                
                ?>
            </div>
        </div>
    <?php } ?>

    <div class="form-group">
        <label for="receipt_taxinvoice_payment_method_id" class=" col-md-3"><?php echo lang('payment_method'); ?></label>
        <div class="col-md-9">
            <?php
			
            echo form_dropdown("receipt_taxinvoice_payment_method_id", $payment_methods_dropdown, array( $model_info->payment_method_id ), "class='select2'" );
            ?>
        </div>
    </div>
    <div class="form-group">
        <label for="receipt_taxinvoice_payment_date" class=" col-md-3"><?php echo lang('payment_date'); ?></label>
        <div class="col-md-9">
            <?php
            echo form_input(array(
                "id" => "receipt_taxinvoice_payment_date",
                "name" => "receipt_taxinvoice_payment_date",
                "value" => $model_info->payment_date,
                "class" => "form-control",
                "placeholder" => lang('payment_date'),
                "autocomplete" => "off",
                "data-rule-required" => true,
                "data-msg-required" => lang("field_required")
            ));
            ?>
        </div>
    </div>

    <div class="form-group">
        <label for="receipt_taxinvoice_payment_amount" class=" col-md-3"><?php echo lang('amount'); ?></label>
        <div class="col-md-9">
            <?php
            echo form_input(array(
                "id" => "receipt_taxinvoice_payment_amount",
                "name" => "receipt_taxinvoice_payment_amount",
                "value" => $model_info->amount ? to_decimal_format($model_info->amount) : "",
                "class" => "form-control",
                "placeholder" => lang('amount'),
                "data-rule-required" => true,
                "data-msg-required" => lang("field_required"),
                "type" => "number"
            ));
            ?>
        </div>
    </div>
    <div class="form-group">
        <label for="receipt_taxinvoice_payment_namebank" class="col-md-3"><?php echo lang('namebank'); ?></label>
        <div class=" col-md-9">
            <?php
            echo form_input(array(
                "id" => "receipt_taxinvoice_payment_namebank",
                "name" => "receipt_taxinvoice_payment_namebank",
                "value" => $model_info->namebank ? $model_info->namebank : "",
                "class" => "form-control",
                "placeholder" => lang('namebank'),
                "data-rule-required" => true,
                "data-msg-required" => lang("field_required")
            ));
            ?>
        </div>
    </div>
    <div class="form-group">
        <label for="receipt_taxinvoice_payment_runnumber" class="col-md-3"><?php echo lang('runnumber'); ?></label>
        <div class=" col-md-9">
            <?php
            echo form_input(array(
                "id" => "receipt_taxinvoice_payment_runnumber",
                "name" => "receipt_taxinvoice_payment_runnumber",
                "value" => $model_info->runnumber ? to_decimal_format($model_info->runnumber) : "",
                "class" => "form-control",
                "placeholder" => lang('runnumber'),
                "data-rule-required" => true,
                "data-msg-required" => lang("field_required"),
                "type" => "number"
            ));
            ?>
        </div>
    </div>
    <div class="form-group">
        <label for="receipt_taxinvoice_payment_note" class="col-md-3"><?php echo lang('detailpayment'); ?></label>
        <div class=" col-md-9">
            <?php
            echo form_textarea(array(
                "id" => "receipt_taxinvoice_payment_note",
                "name" => "receipt_taxinvoice_payment_note",
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
    $(document).ready(function () {
        $("#receipt_taxinvoice-payment-form").appForm({
            onSuccess: function (result) {
                if (typeof RELOAD_VIEW_AFTER_UPDATE !== "undefined" && RELOAD_VIEW_AFTER_UPDATE) {
                    location.reload();
                } else {
                    if ($("#receipt_taxinvoice-payment-table").length) {
                        //it's from receipt_taxinvoice details view
                        $("#receipt_taxinvoice-payment-table").appTable({newData: result.data, dataId: result.id});
                        $("#receipt_taxinvoice-total-section").html(result.receipt_taxinvoice_total_view);
                        if (typeof updateInvoiceStatusBar == 'function') {
                            updateInvoiceStatusBar(result.receipt_taxinvoice_id);
                        }
                    } else {
                        //it's from receipt_taxinvoices list view
                        //update table data
                        $("#" + $(".dataTable:visible").attr("id")).appTable({reload: true});
                    }
                }
            }
        });
        $("#receipt_taxinvoice-payment-form .select2").select2();

        setDatePicker("#receipt_taxinvoice_payment_date");

    });
</script>