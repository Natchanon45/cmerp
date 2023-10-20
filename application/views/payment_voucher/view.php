<link rel="stylesheet" href="/assets/css/printd.css?t=<?php echo time(); ?>">
<style type="text/css">
    .top-vertical {
        vertical-align: text-top;
    }

    #total_in_text {
        outline: none;
        border: none;
        resize: none;
    }

    .payment_info {
        padding-top: 1rem;
        padding-bottom: 1rem;
    }

    .payment_info p {
        font-weight: bolder;
    }

    .payment_info table {
        margin: 1rem 1rem 0 1rem;
        width: calc(100% - 2rem);
        border-top: 1px solid #e7e7e7;
        border-bottom: 1px solid #e7e7e7;
    }

    .payment_number {
        padding: 0 .8rem;
        font-weight: bolder;
    }

    .payment_key {
        padding: .2rem 1rem .1rem 2rem;
        font-weight: bolder;
        vertical-align: text-top;
    }

    .payment_value {
        padding: 0 1rem;
        text-align: right;
        vertical-align: text-top;
    }

    .payment_type {
        width: 40%;
        padding-left: 1.8rem;
    }

    .font-weight-bold {
        font-weight: bold;
    }

    .font-size-bigger {
        font-size: 120%;
    }
</style>

<div id="dcontroller" class="clearfix">
    <div class="page-title clearfix mt15 clear">
        <h1>
            <?php echo (isset($pv_info->doc_number) && !empty($pv_info->doc_number)) ? lang('payment_voucher') . " " . $pv_info->doc_number : ""; ?>
        </h1>
        <div class="title-button-group">
            <a style="margin-left: 15px;" class="btn btn-default mt0 mb0 back-to-index-btn" href="<?php echo get_uri("accounting/buy/payment_voucher"); ?>">
                <i class="fa fa-hand-o-left" aria-hidden="true"></i>
                <?php echo lang('back_to_table'); ?>
            </a>

            <?php if ($pv_info->status != "R" && $pv_info->status != "X"): ?>
                <a id="add_item_button" class="btn btn-default" data-post-doc_id="<?php echo $pv_info->id; ?>" 
                    data-act="ajax-modal" data-title="<?php echo lang("share_doc") . " " . $pv_info->doc_number; ?>" 
                    data-action-url="<?php echo get_uri("payment_voucher/share"); ?>"> <?php echo lang("share"); ?>
                </a>

                <a href="javascript:void(0);" class="btn btn-warning" onclick="window.open('<?php echo $print_url; ?>', '_blank');">
                    <i class="fa fa-print" aria-hidden="true"></i> 
                    <?php echo lang("payment_voucher_print"); ?>
                </a>
            <?php endif; ?>

            <?php if ($pv_info->status == "W" && $pv_info->pay_status == "C"): ?>
                <a href="javascript:void(0);" id="btn-approval" class="btn btn-primary">
                    <i class="fa fa-check" aria-hidden="true"></i> 
                    <?php echo lang("payment_voucher_approve"); ?>
                </a>
            <?php endif; ?>

            <?php
                if ($pv_info->pay_status != "C") {
                    if ($pv_info->pay_status != "O") {
                        echo modal_anchor(
                            get_uri("payment_voucher/record_payment"),
                            '<i class="fa fa-money" aria-hidden="true"></i> ' . lang("payment_information_add"),
                            array(
                                "data-post-doc_id" => $pv_info->id,
                                "data-act" => "ajax-modal",
                                "data-title" => lang("payment_information_add"),
                                "title" => lang("payment_information_add"),
                                "class" => "btn btn-info"
                            )
                        );
                    }
                }
            ?>
        </div>
    </div>
</div><!--#dcontroller-->

<div id="printd" class="clear">
    <div class="docheader clear">
        <div class="l">
            <div class="logo">
                <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . get_file_from_setting("estimate_logo", true)) != false): ?>
                    <img src="<?php echo get_file_from_setting("estimate_logo", get_setting('only_file_path')); ?>" />
                <?php else: ?>
                    <span class="nologo">&nbsp;</span>
                <?php endif; ?>
            </div>

            <div class="company">
                <p class="custom-color">
                    <?php echo lang("payment_voucher_payer"); ?>
                </p>
                <p class="company_name">
                    <?php echo get_setting("company_name"); ?>
                </p>
                <p>
                    <?php echo nl2br(get_setting("company_address")); ?>
                </p>
                <?php if (trim(get_setting("company_phone")) != ""): ?>
                    <p>
                        <?php echo lang("phone") . ": " . get_setting("company_phone"); ?>
                    </p>
                <?php endif; ?>
                <?php if (trim(get_setting("company_website")) != ""): ?>
                    <p>
                        <?php echo lang("website") . ": " . get_setting("company_website"); ?>
                    </p>
                <?php endif; ?>
                <?php if (trim(get_setting("company_vat_number")) != ""): ?>
                    <p>
                        <?php echo lang("vat_number") . ": " . get_setting("company_vat_number"); ?>
                    </p>
                <?php endif; ?>
            </div><!-- .company -->

            <div class="customer">
                <p class="custom-color">
                    <?php echo lang("payment_voucher_payee"); ?>
                </p>
                <?php if (isset($supplier) && !empty($supplier)): ?>
                    <p class="customer_name">
                        <?php echo $supplier["company_name"] ? $supplier["company_name"] : ''; ?>
                    </p>
                    <p>
                        <?php echo $supplier["address"] ? nl2br($supplier["address"]) : ''; ?>
                    </p>
                    <p>
                        <?php
                        $supplier_address = $supplier["city"];

                        if ($supplier_address != "" && $supplier["state"] != "") {
                            $supplier_address .= ", " . $supplier["state"];
                        } elseif ($supplier_address == "" && $supplier["state"] != "") {
                            $supplier_address .= $supplier["state"];
                        }
                            
                        if ($supplier_address != "" && $supplier["zip"] != "") {
                            $supplier_address .= " " . $supplier["zip"];
                        } elseif ($supplier_address == "" && $supplier["zip"] != "") {
                            $supplier_address .= $supplier["zip"];
                        }
                        
                        echo $supplier_address;
                        ?>
                    </p>
                    <?php if (isset($supplier["vat_number"]) && !empty($supplier["vat_number"]) && trim($supplier["vat_number"]) != ""): ?>
                        <p>
                            <?php echo lang("vat_number") . ": " . trim($supplier["vat_number"]); ?>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>
            </div><!-- .company -->
        </div><!--.l-->

        <div class="r">
            <h1 class="document_name custom-color">
                <?php echo lang('payment_voucher'); ?>
            </h1>

            <div class="about_company">
                <table width="100%">
                    <tr>
                        <td class="custom-color" style="width: 30%;">
                            <?php echo lang("number_of_document"); ?>
                        </td>
                        <td>
                            <?php echo $pv_info->doc_number; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="custom-color">
                            <?php echo lang("document_date"); ?>
                        </td>
                        <td>
                            <?php echo convertDate($pv_info->doc_date, true); ?>
                        </td>
                    </tr>
                    <?php if (isset($pv_info->reference_number) && !empty($pv_info->reference_number) && trim($pv_info->reference_number) != ""): ?>
                        <tr>
                            <td class="custom-color">
                                <?php echo lang("reference_number"); ?>
                            </td>
                            <td>
                                <?php echo trim($pv_info->reference_number); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php if (isset($pv_info->references) && !empty($pv_info->references)): ?>
                        <tr>
                            <td class="custom-color">
                                <?php echo lang("reference_number"); ?>
                            </td>
                            <td>
                                <?php echo trim($pv_info->references); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>

            <div class="about_customer">
                <table width="100%">
                    <tr>
                        <td class="custom-color" style="width: 30%;">
                            <?php echo lang('contact_name'); ?>
                        </td>
                        <td>
                            <?php echo (isset($supplier_contact) && !empty($supplier_contact)) ? $supplier_contact["first_name"] . " " . $supplier_contact["last_name"] : '-'; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="custom-color">
                            <?php echo lang('phone'); ?>
                        </td>
                        <td>
                            <?php echo (isset($supplier_contact) && !empty($supplier_contact)) ? $supplier_contact["phone"] : '-'; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="custom-color">
                            <?php echo lang('email'); ?>
                        </td>
                        <td>
                            <?php echo (isset($supplier_contact) && !empty($supplier_contact)) ? $supplier_contact["email"] : '-'; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div><!--.docheader-->

    <div class="docitem">
        <table>
            <thead>
                <tr>
                    <td>#</td>
                    <td>
                        <?php echo lang("details"); ?>
                    </td>
                    <td>
                        <?php echo lang("quantity"); ?>
                    </td>
                    <td>
                        <?php echo lang("stock_material_unit"); ?>
                    </td>
                    <td>
                        <?php echo lang("rate"); ?>
                    </td>
                    <td>
                        <?php echo lang("total_item"); ?>
                    </td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                <?php if (sizeof($pv_detail)): ?>
                    <?php foreach ($pv_detail as $key => $item): ?>
                        <tr>
                            <td><?php echo $key + 1; ?></td>
                            <td>
                                <p class="desc1"><?php echo $item->product_name; ?></p>
                                <p class="desc2"><?php echo $item->product_description; ?></p>
                            </td>
                            <td><?php echo number_format($item->quantity, 2); ?></td>
                            <td><?php echo mb_strtoupper($item->unit); ?></td>
                            <td><?php echo number_format($item->price, 2); ?></td>
                            <td><?php echo number_format($item->total_price, 2); ?></td>
                            <td></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3">
                        <p><textarea name="total_in_text" id="total_in_text" rows="4" readonly><?php echo $pv_info->total_in_text; ?></textarea></p>
                    </td>
                    <td colspan="4" class="summary">
                        <p id="s-sub-total-before-discount">
                            <span class="c1 custom-color">
                                <?php echo lang('total_all_item'); ?>
                            </span>
                            <span class="c2">
                                <input type="text" id="sub_total_before_discount" value="<?php echo number_format($pv_info->sub_total, 2); ?>" readonly>
                            </span>
                            <span class="c3">
                                <span class="currency">
                                    <?php echo lang("THB"); ?>
                                </span>
                            </span>
                        </p>

                        <p id="s-vat">
                            <span class="c1 custom-color">
                                <?php echo lang("value_add_tax") . " " . $this->Taxes_m->getVatPercent() . "%"; ?>
                            </span>
                            <span class="c2">
                                <input type="text" id="vat_value" value="<?php echo number_format($pv_info->vat_value, 2); ?>" readonly>
                            </span>
                            <span class="c3">
                                <span class="currency">
                                    <?php echo lang("THB"); ?>
                                </span>
                            </span>
                        </p>

                        <p id="s-total">
                            <span class="c1 custom-color">
                                <?php echo lang("grand_total_price"); ?>
                            </span>
                            <span class="c2">
                                <input type="text" id="total" value="<?php echo number_format($pv_info->total, 2); ?>" readonly>
                            </span>
                            <span class="c3">
                                <span class="currency">
                                    <?php echo lang("THB"); ?>>
                                </span>
                            </span>
                        </p>

                        <?php if (isset($pv_info->wht_value) && $pv_info->wht_value > 0): ?>
                            <p id="s-wht">
                                <span class="c1 custom-color">
                                    <?php echo lang("with_holding_tax"); ?>
                                </span>
                                <span class="c2 wht">
                                    <input type="text" id="wht_value" value="<?php echo number_format($pv_info->wht_value, 2); ?>" readonly>
                                </span>
                                <span class="c3 wht">
                                    <span class="currency">
                                        <?php echo lang("THB"); ?>
                                    </span>
                                </span>
                            </p>
                            <p id="s-payment-amount">
                                <span class="c1 custom-color wht">
                                    <?php echo lang("payment_amount"); ?>
                                </span>
                                <span class="c2 wht">
                                    <input type="text" id="payment_amount" value="<?php echo number_format($pv_info->payment_amount, 2); ?>" readonly>
                                </span>
                                <span class="c3 wht">
                                    <span class="currency">
                                        <?php echo lang("THB"); ?>
                                    </span>
                                </span>
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>
            </tfoot>
        </table>
        <?php if (trim($pv_info->remark) != ""): ?>
            <div class="remark clear">
                <p class="custom-color">
                    <?php echo lang('remark') . ' / ' . lang('payment_condition'); ?>
                </p>
                <p>
                    <?php echo nl2br($pv_info->remark); ?>
                </p>
            </div>
        <?php endif; ?>
    </div><!--.docitem-->

    <div class="payment_info clear" id="paymentInfo">
        <?php if (isset($pv_method) && !empty($pv_method)): ?>
            <p class="custom-color"><?php echo lang("payment_information"); ?></p>
            <?php
                if (sizeof($pv_method)):
                    $index = 1;
                    foreach ($pv_method as $item):
                        ?>
                            <div id="paymentItems">
                                <table>
                                    <tr>
                                        <td rowspan="2" class="payment_number" width="5%">#<?php echo $index; ?></td>
                                        <td class="payment_key" width="15%"><?php echo lang("payments_date"); ?>: </td>
                                        <td class="payment_value" width="15%"><?php echo $item->date_output; ?></td>
                                        <td class="payment_type">
                                            <div>
                                                <span class="font-weight-bold"><?php echo lang("payment_method"); ?>: </span>
                                                <span style="margin-left: 1rem;"><?php echo $item->type_name; ?></span>
                                            </div>
                                        </td>
                                        <td rowspan="2" width="15%" class="text-center font-size-bigger font-weight-bold"><?php echo $item->currency_format; ?></td>
                                        <td rowspan="2" class="text-center option opx">
                                            <?php if ($item->receipt_flag == 0): ?>
                                                <a class="edit" data-item_id="<?php echo $item->id; ?>"><i class="fa fa-check"></i></a>
                                                <a class="delete" data-item_id="<?php echo $item->id; ?>"><i class="fa fa-times"></i></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="payment_key"><?php echo lang("payments_amount"); ?>: </td>
                                        <td class="payment_value"><?php echo $item->number_format; ?></td>
                                        <td class="payment_type"><?php echo $item->type_description?></td>
                                    </tr>
                                </table>
                            </div>
                        <?php
                        $index++;
                    endforeach;
                endif;
            ?>
        <?php endif; ?>
    </div>

    <div class="docsignature clear">
        <div class="customer">
            <div class="on_behalf_of"></div>
            <div class="clear">
                <div class="name">
                    <span class="l1">
                        <span class="signature">
                            <?php if ($pv_info->status != "R" && $pv_info->status != "X"): if ($pv_info->created_by != null): if (null != $requester_sign = $this->Users_m->getSignature($pv_info->created_by)): ?>
                                <img src="<?php echo '/' . $requester_sign; ?>">
                            <?php endif; endif; endif; ?>
                        </span>
                    </span>
                    <span class="l2">
                        <?php
                            $issuer_name = "( " . str_repeat("_", 18) . " )";
                            if ($created_by["last_name"] == "") {
                                $issuer_name = "( " . $created_by["first_name"] . " )";
                            } else {
                                $issuer_name = "( " . $created_by["first_name"] . " " . $created_by["last_name"] . " )";
                            }
                        ?>
                        <p><?php echo $issuer_name; ?></p>
                        <?php echo lang('issuer_of_document'); ?>
                    </span>
                </div>
                <div class="date">
                    <span class="l1">
                        <?php if ($pv_info->doc_date != null && $pv_info->status != "R" && $pv_info->status != "X"): ?>
                            <span class="approved_date">
                                <?php echo convertDate($pv_info->doc_date, true); ?>
                            </span>
                        <?php endif; ?>
                    </span>
                    <span class="l2">
                        <?php echo lang('date_of_issued'); ?>
                    </span>
                </div>
            </div>
        </div><!--.customer -->

        <div class="company">
            <div class="on_behalf_of"></div>
            <div class="clear">
                <div class="name">
                    <span class="l1">
                        <span class="signature">
                            <?php if ($pv_info->approved_by != null && $pv_info->status == "A"): ?>
                                <?php if (null != $signature = $this->Users_m->getSignature($pv_info->approved_by)): ?>
                                    <img src="<?php echo '/' . $signature; ?>">
                                <?php endif; ?>
                            <?php endif; ?>
                        </span>
                    </span>
                    <span class="l2">
                        <?php
                            $approver_name = "( " . str_repeat("_", 18) . " )";
                            if ($pv_info->status == "A") {
                                if ($approved_by["last_name"] == "") {
                                    $approver_name = "( " . $approved_by["first_name"] . " )";
                                } else {
                                    $approver_name = "( " . $approved_by["first_name"] . " " . $approved_by["last_name"] . " )";
                                }
                            }
                        ?>
                        <p><?php echo $approver_name; ?></p>
                        <?php echo lang('approver'); ?>
                    </span>
                </div>
                <div class="date">
                    <span class="l1">
                        <?php if ($pv_info->approved_datetime != null && $pv_info->status == "A"): ?>
                            <span class="approved_date">
                                <?php echo convertDate($pv_info->approved_datetime, true); ?>
                            </span>
                        <?php endif; ?>
                    </span>
                    <span class="l2">
                        <?php echo lang('day_of_approved'); ?>
                    </span>
                </div>
            </div>
        </div><!--.company-->
    </div><!--.docsignature-->
</div><!--#printd-->

<script type="text/javascript">
    $(document).ready(function () {
        $("#btn-approval").on('click', function (e) {
            e.preventDefault();
            approveDocumentInfo();
        });

        $(".opx .edit").on("click", function (e) {
            e.preventDefault();
            saveItemGotReceipt($(this).data("item_id"));
        });

        $(".opx .delete").on("click", function (e) {
            e.preventDefault();
            saveItemDeleted($(this).data("item_id"));
        });
    });

    async function approveDocumentInfo () {
        let url = '<?php echo_uri($active_module); ?>';
        let request = {
            taskName: 'update_doc_status',
            doc_id: '<?php echo $pv_info->id; ?>',
            update_status_to: 'A'
        };
        // console.log(url, request);

        await axios.post(url, request).then(response => {
            const { data } = response;

            if (data.status == 'success') {
                window.location.reload();
            } else {
                appAlert.error(data.message, {
                    duration: 3001
                });
            }
        }).catch(error => {
            console.log(error);
        });
    }

    async function saveItemGotReceipt (id) {
        let url = '<?php echo_uri($active_module); ?>';
        let request = {
            taskName: 'got_a_receipt',
            id: id
        };

        await axios.post(url, request).then(response => {
            const { data } = response;

            if (data.status == 'success') {
                window.location.reload();
            } else {
                appAlert.error('500 Internal server error.', { duration: 3001 });
            }
        }).catch(error => {
            console.log(error);
            appAlert.error('500 Internal server error.', { duration: 3001 });
        });
    }

    async function saveItemDeleted (id) {
        let url = '<?php echo_uri($active_module); ?>';
        let request = {
            taskName: 'item_deleted',
            id: id
        };

        // console.log(url, request);

        await axios.post(url, request).then(response => {
            console.log(response);

            const { data } = response;

            if (data.status == 'success') {
                window.location.reload();
            } else {
                appAlert.error('500 Internal server error.', { duration: 3001 });
            }
        }).catch(error => {
            console.log(error);
            appAlert.error('500 Internal server error.', { duration: 3001 });
        });
    }
</script>
