<?php

$btn_add = lang("btn_add_raw_material");
$btn_edit = lang("btn_edit_raw_material");

?>

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
</style>

<div id="dcontroller" class="clearfix">
    <div class="page-title clearfix mt15 clear">
        <h1><?php echo (isset($doc_number) && !empty($doc_number)) ? lang('purchase_order') . ' ' . $doc_number : ''; ?></h1>

        <div class="title-button-group">
            <a style="margin-left: 15px;" class="btn btn-default mt0 mb0 back-to-index-btn" href="<?php echo get_uri("accounting/buy/purchase_order"); ?>">
                <i class="fa fa-hand-o-left" aria-hidden="true"></i> 
                <?php echo lang('back_to_table'); ?>
            </a>
            <?php if ($doc_status != 'R' && $doc_status != 'X'): ?>
                <a id="add_item_button" class="btn btn-default" data-post-doc_id="<?php echo $doc_id; ?>" data-act="ajax-modal" data-title="<?php echo lang('share_doc') . ' ' . $doc_number; ?>" data-action-url="<?php echo get_uri("purchase_order/share"); ?>">
                    <?php echo lang('share'); ?>
                </a>
                <a onclick="window.open('<?php echo $print_url; ?>', '' ,'width=980,height=720');" class="btn btn-default">
                    <?php echo lang("print"); ?>
                </a>
            <?php endif; ?>

            <?php if ($doc_status == "W"): ?>
                <a href="javascript:void(0);" id="btn-approval" class="btn btn-info">
                    <i class="fa fa-check-square-o"></i>
                    <?php echo str_repeat("&nbsp;", 1) . lang('purchase_order_approve'); ?>
                </a>
            <?php endif; ?>

            <?php if ($doc_status == "A"): ?>
                <?php if ($doc_receipt_status == "W" || $doc_payment_status == "W"): ?>
                    <span class="dropdown inline-block">
                        <button class="btn btn-warning dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-info-circle"></i>
                            <?php echo str_repeat("&nbsp;", 1) . lang("operation"); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-left rounded" role="menu" aria-labelledby="dropdownMenuButton">
                            
                            <?php if($doc_payment_status == "W"): ?>
                                <li role="presentation">
                                    <a href="javascript:void(0);" id="btn-payment" class="dropdown-item">
                                        <i class="fa fa-file" aria-hidden="true"></i>
                                        <?php echo str_repeat("&nbsp;", 1) . lang("record_of_payment_voucher"); ?>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if ($doc_receipt_status == "W"): ?>
                                <li role="presentation">
                                    <a href="javascript:void(0);" id="btn-receipt" class="dropdown-item">
                                        <i class="fa fa-file-o" aria-hidden="true"></i> 
                                        <?php echo str_repeat("&nbsp;", 1) . lang("record_of_goods_receipt"); ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </span>
                <?php endif; ?>
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
            </div>

            <div class="company">
                <p class="company_name"><?php echo get_setting("company_name"); ?></p>
                <p><?php echo nl2br(get_setting("company_address")); ?></p>
                <?php if (trim(get_setting("company_phone")) != ""): ?>
                    <p><?php echo lang("phone") . ": " . get_setting("company_phone"); ?></p>
                <?php endif; ?>
                <?php if (trim(get_setting("company_website")) != ""): ?>
                    <p><?php echo lang("website") . ": " . get_setting("company_website"); ?></p>
                <?php endif; ?>
                <?php if (trim(get_setting("company_vat_number")) != ""): ?>
                    <p><?php echo lang("vat_number") . ": " . get_setting("company_vat_number"); ?></p>
                <?php endif; ?>
            </div><!-- .company -->

            <div class="customer">
                <p class="custom-color"><?php echo lang("supplier_name"); ?></p>
                <?php if ($supplier != null): ?>
                    <p class="customer_name"><?php echo $supplier["company_name"]; ?></p>
                    <p><?php if ($supplier != null) echo nl2br($supplier["address"]); ?></p>
                    <p>
                        <?php
                            $supplier_address = $supplier["city"];
                            if ($supplier_address != "" && $supplier["state"] != "") $supplier_address .= ", " . $supplier["state"];
                            elseif ($supplier_address == "" && $supplier["state"] != "") $supplier_address .= $supplier["state"];
                            if ($supplier_address != "" && $supplier["zip"] != "") $supplier_address .= " " . $supplier["zip"];
                            elseif ($supplier_address == "" && $supplier["zip"] != "") $supplier_address .= $supplier["zip"];
                            echo $supplier_address;
                        ?>
                    </p>
                    <?php if (trim($supplier["vat_number"]) != ""): ?>
                        <p><?php echo lang("vat_number") . ": " . $supplier["vat_number"]; ?></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div><!-- .company -->
        </div><!--.l-->

        <div class="r">
            <h1 class="document_name custom-color">
                <?php echo lang('purchase_order'); ?>
            </h1>
            <div class="about_company">
                <table width="100%">
                    <tr>
                        <td class="custom-color" style="width: 30%;"><?php echo lang('document_number'); ?></td>
                        <td><?php echo $doc_number; ?></td>
                    </tr>
                    <tr>
                        <td class="custom-color"><?php echo lang('document_date'); ?></td>
                        <td><?php echo convertDate($doc_date, true); ?></td>
                    </tr>
                    <tr>
                        <td class="custom-color"><?php echo lang('credit'); ?></td>
                        <td><?php echo $credit . ' ' . lang('day'); ?></td>
                    </tr>
                    <tr>
                        <td class="custom-color"><?php echo lang('purchase_by'); ?></td>
                        <td><?php if ($created != null) echo $created["first_name"] . " " . $created["last_name"]; ?></td>
                    </tr>
                    <?php if (trim($reference_number) != ""): ?>
                        <tr>
                            <td class="custom-color"><?php echo lang('reference_number'); ?></td>
                            <td><?php echo $reference_number; ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="custom-color"><?php echo lang('delivery_schedule'); ?></td>
                        <td><?php echo convertDate($due_date, true); ?></td>
                    </tr>
                </table>
            </div>
            <div class="about_customer">
                <table width="100%">
                    <tr>
                        <td class="custom-color" style="width: 30%;"><?php echo lang('contact_name'); ?></td>
                        <td><?php echo (isset($supplier_contact) && !empty($supplier_contact)) ? $supplier_contact["first_name"] . " " . $supplier_contact["last_name"] : '-'; ?></td>
                    </tr>
                    <tr>
                        <td class="custom-color"><?php echo lang('phone'); ?></td>
                        <td><?php echo (isset($supplier_contact) && !empty($supplier_contact)) ? $supplier_contact["phone"] : '-'; ?></td>
                    </tr>
                    <tr>
                        <td class="custom-color"><?php echo lang('email'); ?></td>
                        <td><?php echo (isset($supplier_contact) && !empty($supplier_contact)) ? $supplier_contact["email"] : '-'; ?></td>
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
                    <td><?php echo lang('details'); ?></td>
                    <td><?php echo lang('quantity'); ?></td>
                    <td><?php echo lang('stock_material_unit'); ?></td>
                    <td><?php echo lang('rate'); ?></td>
                    <td><?php echo lang('total_item'); ?></td>
                    <td></td>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr><td colspan="7">&nbsp;</td></tr>
                <tr>
                    <td colspan="3">
                        <?php if ($doc_status == "W"): ?>
                            <?php if ($pr_id == null || $pr_id == ''): ?>
                                <?php
                                    if ($doc_type == 3) {
                                        $btn_add = lang("btn_add_finished_goods");
                                        $btn_edit = lang("btn_edit_finished_goods");
                                    }

                                    if ($doc_type == 5) {
                                        $btn_add = lang("btn_add_expense");
                                        $btn_edit = lang("btn_edit_expense");
                                    }
                                ?>
                                <p>
                                    <?php
                                        echo modal_anchor(
                                            get_uri("purchase_order/item"), 
                                            "<i class='fa fa-plus-circle'></i> " . $btn_add, 
                                            array(
                                                "id" => "add_item_button", 
                                                "class" => "btn btn-default", 
                                                "title" => $btn_add, 
                                                "data-title" => $btn_add, 
                                                "data-post-doc_id" => $doc_id
                                            )
                                        );
                                    ?>
                                </p>
                            <?php endif; ?>
                        <?php endif; ?>
                        <p><textarea name="total_in_text" id="total_in_text" rows="4" readonly></textarea></p>
                    </td>
                    <td colspan="4" class="summary">
                        <p id="s-sub-total-before-discount">
                            <span class="c1 custom-color">
                                <?php echo lang('total_all_item'); ?>
                            </span>
                            <span class="c2">
                                <input type="text" id="sub_total_before_discount" readonly>
                            </span>
                            <span class="c3">
                                <span class="currency">
                                    <?php echo lang('THB'); ?>
                                </span>
                            </span>
                        </p>

                        <p id="s-vat">
                            <span class="c1 custom-color">
                                <input type="checkbox" id="vat_inc" <?php if ($vat_inc == "Y") echo "checked" ?> <?php if ($doc_status != "W") echo "disabled"; ?>>
                                <?php echo lang('value_add_tax'); ?>
                                <?php echo $this->Taxes_m->getVatPercent() . "%"; ?>
                            </span>
                            <span class="c2">
                                <input type="text" id="vat_value" readonly>
                            </span>
                            <span class="c3">
                                <span class="currency"><?php echo lang('THB'); ?></span>
                            </span>
                        </p>

                        <p id="s-total">
                            <span class="c1 custom-color">
                                <?php echo lang('grand_total_price'); ?>
                            </span>
                            <span class="c2">
                                <input type="text" id="total" readonly>
                            </span>
                            <span class="c3">
                                <span class="currency"><?php echo lang('THB'); ?>></span>
                            </span>
                        </p>

                        <p id="s-wht">
                            <span class="c1 custom-color">
                                <input type="checkbox" id="wht_inc" <?php if ($wht_inc == "Y") echo "checked" ?> <?php if ($doc_status != "W") echo "disabled"; ?> <?php if ($doc_type != "5") echo "disabled"; ?>>
                                <span><?php echo lang('with_holding_tax'); ?></span>
                                <select id="wht_percent" class="wht custom-color <?php echo $wht_inc == "Y" ? "v" : "h"; ?>" <?php if ($doc_status != "W") echo "disabled"; ?>>
                                    <?php if ($wht_inc == 'Y' && isset($wht_percent) && !empty($wht_percent)): ?>
                                        <option value="<?php echo $wht_percent; ?>" selected><?php echo $wht_percent . '%'; ?></option>
                                    <?php endif; ?>
                                    <option value="3">3%</option>
                                    <option value="5">5%</option>
                                    <option value="0.50">0.5%</option>
                                    <option value="0.75">0.75%</option>
                                    <option value="1">1%</option>
                                    <option value="1.50">1.5%</option>
                                    <option value="2">2%</option>
                                    <option value="10">10%</option>
                                    <option value="15">15%</option>
                                </select>
                            </span>
                            <span class="c2 wht <?php echo $wht_inc == "Y" ? "v" : "h"; ?>">
                                <input type="text" id="wht_value" readonly>
                            </span>
                            <span class="c3 wht <?php echo $wht_inc == "Y" ? "v" : "h"; ?>">
                                <span class="currency"><?php echo lang('THB'); ?></span>
                            </span>
                        </p>

                        <p id="s-payment-amount">
                            <span class="c1 custom-color wht <?php echo $wht_inc == "Y" ? "v" : "h"; ?>">
                                <?php echo lang('payment_amount'); ?>
                            </span>
                            <span class="c2 wht <?php echo $wht_inc == "Y" ? "v" : "h"; ?>">
                                <input type="text" id="payment_amount" readonly>
                            </span>
                            <span class="c3 wht <?php echo $wht_inc == "Y" ? "v" : "h"; ?>">
                                <span class="currency"><?php echo lang('THB'); ?></span>
                            </span>
                        </p>
                    </td>
                </tr>
            </tfoot>
        </table>
        <?php if (trim($remark) != ""): ?>
            <div class="remark clear">
                <p class="custom-color"><?php echo lang('remark') . ' / ' . lang('payment_condition'); ?></p>
                <p><?php echo nl2br($remark); ?></p>
            </div>
        <?php endif; ?>
    </div><!--.docitem-->

    <div class="docsignature clear">
        <div class="customer">
            <div class="on_behalf_of"></div>
            <div class="clear">
                <div class="name">
                    <span class="l1">
                        <span class="signature">
                            <?php if ($doc_status != 'R' && $doc_status != 'X'): if ($created_by != null): if (null != $requester_sign = $this->Users_m->getSignature($created_by)): ?>
                                <img src="<?php echo '/' . $requester_sign; ?>">
                            <?php endif; endif; endif; ?>
                        </span>
                    </span>
                    <span class="l2"><?php echo lang('purchase_by'); ?></span>
                </div>
                <div class="date">
                    <span class="l1">
                        <?php if ($doc_date != null && $doc_status != 'R' && $doc_status != 'X'): ?>
                            <span class="approved_date">
                                <?php echo convertDate($doc_date, true); ?>
                            </span>
                        <?php endif; ?>
                    </span>
                    <span class="l2"><?php echo lang('date'); ?></span>
                </div>
            </div>
        </div><!--.customer -->
        <div class="company">
            <div class="on_behalf_of"></div>
            <div class="clear">
                <div class="name">
                    <span class="l1">
                        <span class="signature">
                            <?php if ($approved_by != null && $doc_status == 'A'): if (null != $signature = $this->Users_m->getSignature($approved_by)): ?>
                                <img src="<?php echo '/' . $signature; ?>">
                            <?php endif; endif; ?>
                        </span>
                    </span>
                    <span class="l2"><?php echo lang('approver'); ?></span>
                </div>
                <div class="date">
                    <span class="l1">
                        <?php if ($approved_datetime != null && $doc_status == 'A'): ?>
                            <span class="approved_date">
                                <?php echo convertDate($approved_datetime, true); ?>
                            </span>
                        <?php endif; ?>
                    </span>
                    <span class="l2"><?php echo lang('date'); ?></span>
                </div>
            </div>
        </div><!--.company-->
    </div><!--.docsignature-->
</div><!--#printd-->

<script type="text/javascript">
    const requestId = '<?php echo $pr_id; ?>';

    window.addEventListener('keydown', (e) => {
        if (e.keyCode === 80 && (e.ctrlKey || e.metaKey) && !e.altKey && (!e.shiftKey || window.chrome || window.opera)) {
            e.preventDefault();

            if (e.stopImmediatePropagation) {
                e.stopImmediatePropagation();
            } else {
                e.stopPropagation();
            }

            return;
        }
    }, true);

    $(document).ready(function () {
        loadItems();

        $("#vat_inc, #wht_inc, #wht_percent").change(function () {
            loadSummary();
        });

        $("#btn-approval").on('click', function (e) {
            e.preventDefault();
            approval();
        });

        $("#btn-receipt").on('click', function (e) {
            e.preventDefault();
            receipt();
        });

        $("#btn-payment").on('click', function (e) {
            e.preventDefault();
            payment();
        });
    });

    function loadItems() {
        let url = '<?php echo current_url(); ?>';
        let request = {
            task: 'load_items',
            doc_id: '<?php echo $doc_id; ?>'
        }

        axios.post(url, request).then(function (response) {
            let data = response.data;
            if (data.status == "notfound") {
                $(".docitem tbody").empty().append(`<tr><td colspan="7" class="notfound">${data.message}</td></tr>`);
            } else if (data.status == "success") {
                let tbody = '';
                let items = data.items;

                for (let i = 0; i < items.length; i++) {
                    tbody += `<tr>`;
                    tbody += `<td>${i + 1}</td>`;
                    tbody += `<td>`;
                    tbody += `<p class="desc1">${items[i]['product_name']}</p>`;
                    tbody += `<p class="desc2">${items[i]['product_description']}</p>`;
                    tbody += `</td>`;
                    tbody += `<td>${items[i]['quantity']}</td>`;
                    tbody += `<td>${items[i]['unit']}</td>`;
                    tbody += `<td>${items[i]['price']}</td>`;
                    tbody += `<td>${items[i]['total_price']}</td>`;
                    tbody += `<td class="edititem">`;
                    if (data.doc_status == 'W') {
                        tbody += `<a class="edit" data-post-doc_id="<?php echo $doc_id; ?>" data-title="<?php echo $btn_edit; ?>" data-post-item_id="${items[i]['id']}" data-act="ajax-modal" data-action-url="<?php echo_uri("purchase_order/item"); ?>"><i class="fa fa-pencil"></i></a>`;
                        if (!requestId) {
                            tbody += `<a class="delete" data-item_id="${items[i]['id']}"><i class="fa fa-times fa-fw"></i></a>`;
                        }
                    }
                    tbody += `</td>`;
                    tbody += `</tr>`;
                }

                $(".docitem tbody").empty().append(tbody);
                $(".edititem .delete").click(function () {
                    deletion($(this).data("item_id"));
                });
            }

            loadSummary();
        }).catch(function (error) {
            console.log(error);
        });
    }

    function loadSummary() {
        let url = '<?php echo current_url(); ?>';
        let request = {
            task: 'update_doc',
            doc_id: '<?php echo $doc_id; ?>',
            discount_type: 'P',
            discount_percent: 0,
            discount_value: 0,
            vat_inc: $("#vat_inc").is(":checked"),
            wht_inc: $("#wht_inc").is(":checked"),
            wht_percent: $("#wht_percent").val()
        }

        axios.post(url, request).then(function (response) {
            let data = response.data;

            $("#sub_total_before_discount").val(data.sub_total_before_discount);
            $("#discount_percent").val(data.discount_percent);
            $("#discount_amount").val(data.discount_amount);
            $("#sub_total").val(data.sub_total);
            $("#wht_percent").val(data.wht_percent);
            $("#wht_value").val(data.wht_value);
            $("#total").val(data.total);
            $("#total_in_text").val("(" + data.total_in_text + ")");
            $("#payment_amount").val(data.payment_amount);

            if (data.vat_inc == "Y") {
                $("#vat_inc").prop("checked", true);
                $("#vat_value").val(data.vat_value);
            } else {
                $("#vat_inc").prop("checked", false);
                $("#vat_value").val(data.vat_value);
            }

            if (data.wht_inc == "Y") {
                $("#wht_inc").prop("checked", true);
                $("#s-wht").removeClass("h").addClass("v");
                $(".wht").removeClass("h").addClass("v");
            } else {
                $("#wht_inc").prop("checked", false);
                $("#s-wht").removeClass("v").addClass("h");
                $(".wht").removeClass("v").addClass("h");
            }
        }).catch(function (error) {
            console.log(error);
        });
    }

    const deletion = async (item_id) => {
        let url = '<?php echo current_url(); ?>';
        let request = {
            task: 'delete_item',
            doc_id: '<?php echo $doc_id; ?>',
            item_id: item_id
        }

        await axios.post(url, request).then(response => {
            let data = response.data;

            if (data.status == "success") {
                loadItems();
            }
        }).catch(error => {
            appAlert.error('500 Internal Server Error.', {
                duration: 3001
            });
        });
    };

    const approval = async () => {
        let url = '<?php echo_uri($active_module); ?>';
        let request = {
            task: 'update_doc_status',
            doc_id: '<?php echo $doc_id; ?>',
            update_status_to: 'A'
        }

        await axios.post(url, request).then(response => {
            let data = response.data;

            if (data.status == "success") {
                if (typeof data.task !== "undefined") {
                    window.location.href = data.url;
                }
            } else {
                appAlert.error(data.message, {
                    duration: 3001
                });
            }
        }).catch(error => {
            appAlert.error('500 Internal Server Error.', {
                duration: 3001
            });
        });
    };
    
    const receipt = async () => {
        let url = '<?php echo_uri($active_module); ?>';
        let request = {
            task: 'update_doc_status',
            doc_id: '<?php echo $doc_id; ?>',
            update_status_to: 'GR'
        }

        await axios.post(url, request).then(response => {
            let data = response.data;

            if (data.status == "success") {
                if (typeof data.task !== "undefined") {
                    window.location.href = data.url;
                }
            } else {
                appAlert.error(data.message, {
                    duration: 3001
                });
            }
        }).catch(error => {
            appAlert.error('500 Internal Server Error.', {
                duration: 3001
            });
        });
    };

    const payment = async () => {
        let url = '<?php echo_uri($active_module); ?>';
        let request = {
            task: 'update_doc_status',
            doc_id: '<?php echo $doc_id; ?>',
            update_status_to: 'PV'
        };

        await axios.post(url, request).then(response => {
            let data = response.data;

            if (data.status == "success") {
                if (typeof data.task !== "undefined") {
                    window.location.href = data.url;
                }
            } else {
                appAlert.error(data.message, {
                    duration: 3001
                });
            }
        }).catch(error => {
            appAlert.error('500 Internal Server Error.', {
                duration: 3001
            });
        });
    };
</script>
