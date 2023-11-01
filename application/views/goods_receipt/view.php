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

    .w-adjust {
        width: 130px !important;
        vertical-align: text-top;
    }

    .docitem table {
        table-layout: unset !important;
    }

    .total_quantity td {
        padding-top: 1rem !important;
        border-bottom: none !important;
    }
    
    .total_quantity_display {
        border-bottom: double #cecece !important;
    }
</style>

<div id="dcontroller" class="clearfix">
    <div class="page-title clearfix mt15 clear">
        <h1>
            <?php echo (isset($gr_info->doc_number) && !empty($gr_info->doc_number)) ? lang("goods_receipt") . " " . $gr_info->doc_number : ""; ?>
        </h1>
        <div class="title-button-group">
            <a style="margin-left: 15px;" class="btn btn-default mt0 mb0 back-to-index-btn" href="<?php echo get_uri("accounting/buy/goods_receipt"); ?>">
                <i class="fa fa-hand-o-left" aria-hidden="true"></i> 
                <?php echo lang("back_to_table"); ?>
            </a>
            
            <?php if ($gr_info->status != "X"): ?>
                <a id="add_item_button" class="btn btn-default" data-post-doc_id="<?php echo $gr_info->id; ?>" data-act="ajax-modal" data-title="<?php echo lang("share_doc") . ' ' . $gr_info->doc_number; ?>" data-action-url="<?php echo get_uri("goods_receipt/share"); ?>">
                    <i class="fa fa-share-square-o" aria-hidden="true"></i> 
                    <?php echo lang("share"); ?>
                </a>
            <?php endif; ?>

            <?php if ($gr_info->status == "W"): ?>
                <a href="javascript:void(0);" class="btn btn-primary" id="btn-approval-info">
                    <i class="fa fa-check" aria-hidden="true"></i> 
                    <?php echo lang("goods_receipt_approve"); ?>
                </a>
            <?php endif; ?>

            <?php if ($gr_info->status != "X"): ?>
                <a href="javascript:void(0);" class="btn btn-warning" onclick="window.open('<?php echo $print_url; ?>', '_blank');">
                    <i class="fa fa-print" aria-hidden="true"></i> 
                    <?php echo lang("goods_receipt_print"); ?>
                </a>
            <?php endif; ?>

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
            </div><!-- .logo -->
            <div class="company">
                <p class="company_name"><?php echo get_setting("company_name"); ?></p>
                <p><?php echo nl2br(get_setting("company_address")); ?></p>
                
                <?php if (!empty(trim(get_setting("company_phone"))) && trim(get_setting("company_phone")) != ""): ?>
                    <p><?php echo lang("phone") . ": " . get_setting("company_phone"); ?></p>
                <?php endif; ?>

                <?php if (!empty(trim(get_setting("company_website"))) && trim(get_setting("company_website")) != ""): ?>
                    <p><?php echo lang("website") . ": " . get_setting("company_website"); ?></p>
                <?php endif; ?>

                <?php if (!empty(trim(get_setting("company_vat_number"))) && trim(get_setting("company_vat_number")) != ""): ?>
                    <p><?php echo lang("vat_number") . ": " . get_setting("company_vat_number"); ?></p>
                <?php endif; ?>
            </div><!-- .company -->
            <div class="customer">
                <p class="custom-color"><?php echo lang("supplier_name"); ?></p>
                <?php if (isset($bom_supplier_read) && $bom_supplier_read): ?>
                    <?php if (isset($supplier) && !empty($supplier)): ?>
                        <p class="customer_name"><?php echo $supplier["company_name"]; ?></p>
                        <p><?php echo nl2br($supplier["address"]); ?></p>
                        <p>
                            <?php
                                $supplier_address = $supplier["city"];
                                if ($supplier_address != "" && $supplier["state"] != "") {
                                    $supplier_address .= ", " . $supplier["state"];
                                } elseif ($supplier_address == "" && $supplier["state"] != "") {
                                    $supplier_address .= $supplier["state"];
                                }

                                if ($supplier_address != "" && $supplier["zip"] != "") {
                                    $supplier_address .= ", " . $supplier["zip"];
                                } elseif ($supplier_address == "" && $supplier["zip"] != "") {
                                    $supplier_address .= $supplier["zip"];
                                }

                                echo $supplier_address;
                            ?>
                        </p>

                        <?php if (!empty(trim($supplier["country"])) && trim($supplier["country"]) != ""): ?>
                            <p><?php // echo $supplier["country"]; ?></p>
                        <?php endif; ?>

                        <?php if (!empty(trim($supplier["vat_number"])) && trim($supplier["vat_number"]) != ""): ?>
                            <p><?php echo lang("vat_number") . ": " . $supplier["vat_number"]; ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div><!-- .company -->
        </div><!--.l-->
        <div class="r">
            <h1 class="document_name custom-color"><?php echo lang("goods_receipt"); ?></h1>
            <div class="about_company">
                <table>
                    <tr>
                        <td class="custom-color w-adjust"><?php echo lang("number_of_document"); ?></td>
                        <td><?php echo $gr_info->doc_number; ?></td>
                    </tr>
                    <tr>
                        <td class="custom-color w-adjust"><?php echo lang("document_date"); ?></td>
                        <td><?php echo convertDate($gr_info->doc_date, true); ?></td>
                    </tr>
                    <tr>
                        <td class="custom-color w-adjust"><?php echo lang("purchase_by"); ?></td>
                        <td>
                            <?php
                                if (isset($created) && !empty($created)) {
                                    echo (isset($created["last_name"]) && !empty($created["last_name"])) ? $created["first_name"] . " " . $created["last_name"] : $created["first_name"];
                                } else {
                                    echo "-";
                                }
                            ?>
                        </td>
                    </tr>

                    <?php if (isset($gr_info->supplier_invoice) && !empty($gr_info->supplier_invoice)): ?>
                        <tr>
                            <td class="custom-color w-adjust"><?php echo lang("gr_delivery_refer"); ?></td>
                            <td><?php echo $gr_info->supplier_invoice; ?></td>
                        </tr>
                    <?php endif; ?>

                    <?php if (isset($gr_info->references_links) && !empty($gr_info->references_links)): ?>
                        <tr>
                            <td class="custom-color w-adjust"><?php echo lang("po_no"); ?></td>
                            <td><?php echo $gr_info->references_links; ?></td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
            <div class="about_customer">
                <table>
                    <tr>
                        <td class="custom-color"><?php echo lang("contact_name"); ?></td>
                        <td>
                            <?php echo (isset($supplier_contact) && !empty($supplier_contact)) ? $supplier_contact["first_name"] . " " . $supplier_contact["last_name"] : "-"; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="custom-color"><?php echo lang("phone"); ?></td>
                        <td>
                            <?php echo (isset($supplier_contact) && !empty($supplier_contact)) ? $supplier_contact["phone"] : "-"; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="custom-color"><?php echo lang("email"); ?></td>
                        <td>
                            <?php echo (isset($supplier_contact) && !empty($supplier_contact)) ? $supplier_contact["email"] : '-'; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div><!--.docheader-->

    <?php $total_quantity = 0; ?>
    <div class="docitem">
        <table>
            <thead>
                <tr>
                    <td>#</td>
                    <td><?php echo lang("details"); ?></td>
                    <td><?php echo lang("quantity"); ?></td>
                    <td><?php echo lang("stock_material_unit"); ?></td>

                    <td></td>
                </tr>
            </thead>
            <tbody>
                <?php if (sizeof($gr_detail)): ?>
                    <?php foreach ($gr_detail as $key => $item): ?>
                        <?php $total_quantity += $item->quantity; ?>
                        <tr>
                            <td><?php echo $key + 1; ?></td>
                            <td>
                                <p class="desc1"><?php echo $item->product_name; ?></p>
                                <p class="desc2"><?php echo $item->product_description; ?></p>
                            </td>
                            <td><?php echo number_format($item->quantity, 2); ?></td>
                            <td><?php echo mb_strtoupper($item->unit); ?></td>

                            <td></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr height="35px">
                    <td colspan="2" style="text-align: right; vertical-align: middle !important;">
                        <span class="custom-color" style="padding-right: 42px;">
                            <?php echo lang("gr_total_quantity"); ?>
                        </span>
                    </td>
                    <td style="text-align: right; border-bottom: double #cecece; vertical-align: middle !important;">
                        <span><?php echo number_format($total_quantity, 2); ?></span>
                    </td>
                    <td colspan="3"></td>
                </tr>
                <tr><td colspan="7">&nbsp;</td></tr>
                <tr>
                    <td colspan="3" style="padding-left: 0 !important;">
                        <p>
                            <input type="text" id="total_in_text" style="margin-top: 2rem !important;" value="<?php // echo $gr_info->total_in_text; ?>" readonly>
                        </p>
                    </td>
                    <td colspan="4" class="summary">
                        <p id="s-sub-total-before-discount">
                            <span class="c1 custom-color">
                                <?php // echo lang("total_all_item"); ?>
                            </span>
                            <span class="c2">
                                <input type="text" id="sub_total_before_discount" value="<?php // echo number_format($gr_info->sub_total_before_discount, 2); ?>" readonly>
                            </span>
                            <span class="c3">
                                <span class="currency"><?php // echo lang("THB"); ?></span>
                            </span>
                        </p>
                        
                        <p id="s-vat">
                            <span class="c1 custom-color">
                                <?php // echo lang("value_add_tax") . " " . $this->Taxes_m->getVatPercent() . "%"; ?>
                            </span>
                            <span class="c2">
                                <input type="text" id="vat_value" value="<?php // echo number_format($gr_info->vat_value, 2); ?>" readonly>
                            </span>
                            <span class="c3">
                                <span class="currency"><?php // echo lang("THB"); ?></span>
                            </span>
                        </p>

                        <p id="s-total">
                            <span class="c1 custom-color">
                                <?php // echo lang('grand_total_price'); ?>
                            </span>
                            <span class="c2">
                                <input type="text" id="total" value="<?php // echo number_format($gr_info->total, 2); ?>" readonly>
                            </span>
                            <span class="c3">
                                <span class="currency"><?php echo lang("THB"); ?></span>
                            </span>
                        </p>

                        <?php // if (isset($gr_info->wht_value) && $gr_info->wht_value > 0): ?>
                            <!-- <p id="s-wht">
                                <span class="c1 custom-color">
                                    <?php // echo lang("with_holding_tax"); ?>
                                </span>
                                <span class="c2 wht">
                                    <input type="text" id="wht_value" value="<?php // echo number_format($gr_info->wht_value, 2); ?>" readonly>
                                </span>
                                <span class="c3 wht">
                                    <span class="currency"><?php // echo lang("THB"); ?></span>
                                </span>
                            </p> -->

                            <!-- <p id="s-payment-amount">
                                <span class="c1 custom-color wht">
                                    <?php // echo lang("payment_amount"); ?>
                                </span>
                                <span class="c2 wht">
                                    <input type="text" id="payment_amount" value="<?php // echo number_format($gr_info->payment_amount, 2); ?>" readonly>
                                </span>
                                <span class="c3 wht">
                                    <span class="currency"><?php // echo lang("THB"); ?></span>
                                </span>
                            </p> -->
                        <?php // endif; ?>
                    </td>
                </tr>
            </tfoot>
        </table>
        <?php if (!empty(trim($gr_info->remark)) && trim($gr_info->remark) != ""): ?>
            <div class="remark clear">
                <p class="custom-color"><?php echo lang("remark"); ?></p>
                <p><?php echo nl2br($gr_info->remark); ?></p>
            </div>
        <?php endif; ?>
    </div><!--.docitem-->

    <div class="payment_info clear" id="paymentInfo">
        <p class="custom-color"></p>
        <div id="paymentItems"></div>
    </div>

    <div class="docsignature clear">
        <div class="customer">
            <div class="on_behalf_of"></div>
            <div class="clear">
                <div class="name">
                    <span class="l1">
                        <span class="signature">
                            <?php if ($gr_info->status != "R" && $gr_info->status != "X"): if ($gr_info->created_by != null): if (null != $requester_sign = $this->Users_m->getSignature($gr_info->created_by)): ?>
                                <img src="<?php echo '/' . $requester_sign; ?>">
                            <?php endif; endif; endif; ?>
                        </span>
                    </span>
                    <span class="l2">
                        <?php
                            $issuer_name = "( " . str_repeat("_", 18) . " )";
                            if (empty($created["last_name"]) || $created["last_name"] == "") {
                                $issuer_name = "( " . $created["first_name"] . " )";
                            } else {
                                $issuer_name = "( " . $created["first_name"] . " " . $created["last_name"] . " )";
                            }
                        ?>
                        <p><?php echo $issuer_name; ?></p>
                        <?php echo lang("issuer_of_document"); ?>
                    </span>
                </div>
                <div class="date">
                    <span class="l1">
                        <?php if ($gr_info->doc_date != null && $gr_info->status != "R" && $gr_info->status != "X"): ?>
                            <span class="approved_date">
                                <?php echo convertDate($gr_info->doc_date, true); ?>
                            </span>
                        <?php endif; ?>
                    </span>
                    <span class="l2">
                        <?php echo lang("date_of_issued"); ?>
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
                            <?php if ($gr_info->approved_by != null && $gr_info->status == "A"): ?>
                                <?php if (null != $signature = $this->Users_m->getSignature($gr_info->approved_by)): ?>
                                    <img src="<?php echo '/' . $signature; ?>">
                                <?php endif; ?>
                            <?php endif; ?>
                        </span>
                    </span>
                    <span class="l2">
                        <?php
                            $approver_name = "( " . str_repeat("_", 18) . " )";
                            if ($gr_info->status == "A") {
                                if ($approved["last_name"] == "") {
                                    $approver_name = "( " . $approved["first_name"] . " )";
                                } else {
                                    $approver_name = "( " . $approved["first_name"] . " " . $approved["last_name"] . " )";
                                }
                            }
                        ?>
                        <p><?php echo $approver_name; ?></p>
                        <?php echo lang("approver"); ?>
                    </span>
                </div>
                <div class="date">
                    <span class="l1">
                        <?php if ($gr_info->approved_datetime != null && $gr_info->status == "A"): ?>
                            <span class="approved_date">
                                <?php echo convertDate($gr_info->approved_datetime, true); ?>
                            </span>
                        <?php endif; ?>
                    </span>
                    <span class="l2"><?php echo lang("day_of_approved"); ?></span>
                </div>
            </div>
        </div><!--.company-->
    </div><!--.docsignature-->
</div><!--#printd-->

<script type="text/javascript">
    async function approveDocumentInfo() {
        let url = '<?php echo_uri($active_module); ?>';
        let request = {
            taskName: 'update_doc_status',
            documentId: '<?php echo $gr_info->id; ?>',
            updateStatus: 'A'
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

    $(document).ready(function () {
        $("#btn-approval-info").on("click", async function (e) {
            e.preventDefault();
            await approveDocumentInfo();
        });
    });
</script>
