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
            <?php echo ($po_type == '5') ? lang('record_of_expenses') : lang('record_of_receipt'); ?>
        </h1>
        <div class="title-button-group">
            <a style="margin-left: 15px;" class="btn btn-default mt0 mb0 back-to-index-btn" href="<?php echo get_uri("accounting/buy/goods_receipt"); ?>">
                <i class="fa fa-hand-o-left" aria-hidden="true"></i> 
                <?php echo lang('back_to_table'); ?>
            </a>
            
            <?php if (empty($doc_status)): ?>
                <a id="add_item_button" class="btn btn-default" data-post-doc_id="<?php echo $doc_id; ?>" data-act="ajax-modal" data-title="<?php echo lang('share_doc') . ' ' . $doc_number; ?>" data-action-url="<?php echo get_uri("purchase_order/share"); ?>">
                    <i class="fa fa-share-square-o" aria-hidden="true"></i> 
                    <?php echo lang('share'); ?>
                </a>
            <?php endif; ?>

            <?php if ($doc_status == 'N'): ?>
                <a href="#" class="btn btn-primary" id="btn-save-info">
                    <i class="fa fa-floppy-o" aria-hidden="true"></i> 
                    <?php echo lang('save'); ?>
                </a>
            <?php endif; ?>

            <?php if ($doc_status == 'W'): ?>
                <?php if ($pay_status != 'C' && $pay_status != 'O'): ?>
                    <a class="btn btn-info" id="btn-add-payment" data-post-doc_id="<?php echo $doc_id; ?>" data-act="ajax-modal" data-title="<?php echo "บันทึกข้อมูลการชำระเงิน"; ?>" data-action-url="<?php echo get_uri('goods_receipt/record_payment'); ?>">
                        <i class="fa fa-money" aria-hidden="true"></i> 
                        <?php echo "บันทึกข้อมูลการชำระเงิน"; ?>
                    </a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($doc_status != 'X'): ?>
                <span class="dropdown inline-block">
                    <button class="btn btn-warning dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-print"></i> <?php echo lang('print'); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-left rounded" role="menu" aria-labelledby="dropdownMenuButton">
                        <li role="presentation">
                            <a class="dropdown-item" href="#" onclick="window.open('<?php echo $print_gr_url; ?>', '' ,'width=980,height=720');">
                                <i class="fa fa-book" aria-hidden="true"></i> <?php echo "พิมพ์ใบรับสินค้า"; ?>
                            </a>
                        </li>
                        <li role="presentation">
                            <a class="dropdown-item" href="#" onclick="window.open('<?php echo $print_pv_url; ?>', '' ,'width=980,height=720');">
                                <i class="fa fa-file-text-o" aria-hidden="true"></i> <?php echo "พิมพ์ใบสำคัญจ่าย"; ?>
                            </a>
                        </li>
                    </ul>
                </span>
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
                    <?php echo lang("supplier_name"); ?>
                </p>
                <?php if ($supplier != null): ?>
                    <p class="customer_name">
                        <?php echo $supplier["company_name"] ?>
                    </p>
                    <p>
                        <?php if ($supplier != null)
                            echo nl2br($supplier["address"]); ?>
                    </p>
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
                    <?php if (trim($supplier["country"]) != ""): ?>
                        <p>
                            <?php // echo $supplier["country"]; ?>
                        </p>
                    <?php endif; ?>
                    <?php if (trim($supplier["vat_number"]) != ""): ?>
                        <p>
                            <?php echo lang("vat_number") . ": " . $supplier["vat_number"]; ?>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>
            </div><!-- .company -->
        </div><!--.l-->

        <div class="r">
            <h1 class="document_name custom-color">
                <?php echo ($po_type == '5') ? lang('record_of_expenses') : lang('record_of_receipt'); ?>
            </h1>
            <div class="about_company">
                <table>
                    <tr>
                        <td class="custom-color"><?php echo lang('document_number'); ?></td>
                        <td>
                            <?php echo $doc_number; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="custom-color"><?php echo lang('document_date'); ?></td>
                        <td>
                            <?php echo convertDate($doc_date, true); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="custom-color"><?php echo lang('credit'); ?></td>
                        <td><?php echo $credit . ' ' . lang('day'); ?></td>
                    </tr>
                    <tr>
                        <td class="custom-color"><?php echo lang('purchase_by'); ?></td>
                        <td>
                            <?php if ($created != null)
                                echo $created["first_name"] . " " . $created["last_name"]; ?>
                        </td>
                    </tr>
                    <?php if (trim($reference_number) != ""): ?>
                        <tr>
                            <td class="custom-color"><?php echo lang('reference_number'); ?></td>
                            <td>
                                <?php echo $reference_number; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
            <div class="about_customer">
                <table>
                    <tr>
                        <td class="custom-color"><?php echo lang('contact_name'); ?></td>
                        <td>
                            <?php echo (isset($supplier_contact) && !empty($supplier_contact)) ? $supplier_contact["first_name"] . " " . $supplier_contact["last_name"] : '-'; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="custom-color"><?php echo lang('phone'); ?></td>
                        <td>
                            <?php echo (isset($supplier_contact) && !empty($supplier_contact)) ? $supplier_contact["phone"] : '-'; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="custom-color"><?php echo lang('email'); ?></td>
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
                <tr>
                    <td colspan="7">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3" style="padding-left: 0 !important;">
                        <?php if ($doc_status == 'N'): ?>
                            <?php if ($po_id == null): ?>
                                <p>
                                    <?php
                                        echo modal_anchor(
                                            get_uri("goods_receipt/item"),
                                            "<i class='fa fa-plus-circle'></i> " . lang('add_item_product'),
                                            array(
                                                "id" => "add_item_button",
                                                "class" => "btn btn-default",
                                                "data-post-doc_id" => $doc_id
                                            )
                                        );
                                    ?>
                                </p>
                            <?php endif; ?>
                        <?php endif; ?>
                        <p><input type="text" id="total_in_text" style="margin-top: 2rem !important;" readonly></p>
                    </td>
                    <td colspan="4" class="summary">
                        <p id="s-sub-total-before-discount">
                            <span class="c1 custom-color"><?php echo lang('total_all_item'); ?></span>
                            <span class="c2"><input type="text" id="sub_total_before_discount" readonly></span>
                            <span class="c3"><span class="currency"><?php echo lang('THB'); ?></span></span>
                        </p>

                        <p id="s-vat">
                            <span class="c1 custom-color">
                                <input type="checkbox" id="vat_inc" <?php if ($vat_inc == 'Y') echo 'checked'; ?> <?php if ($doc_status != 'N') echo 'disabled'; ?> <?php if (isset($po_id) && !empty($po_id)) echo 'disabled'; ?>>
                                <?php echo lang('value_add_tax'); ?>
                                <?php echo $this->Taxes_m->getVatPercent() . "%"; ?>
                            </span>
                            <span class="c2"><input type="text" id="vat_value" readonly></span>
                            <span class="c3">
                                <span class="currency"><?php echo lang('THB'); ?></span>
                            </span>
                        </p>

                        <p id="s-total">
                            <span class="c1 custom-color">
                                <?php echo lang('grand_total_price'); ?>
                            </span>
                            <span class="c2"><input type="text" id="total" readonly></span>
                            <span class="c3">
                                <span class="currency"><?php echo lang('THB'); ?></span>
                            </span>
                        </p>

                        <?php if ($po_type == 5): ?>
                            <p id="s-wht">
                                <span class="c1 custom-color">
                                    <input type="checkbox" id="wht_inc" <?php if ($wht_inc == 'Y') echo 'checked'; ?> <?php if ($doc_status != 'N') echo 'disabled'; ?> <?php if (isset($po_id) && !empty($po_id)) echo 'disabled'; ?>> <?php echo lang('with_holding_tax'); ?>
                                    <select id="wht_percent" class="wht custom-color <?php echo $wht_inc == 'Y' ? 'v' : 'h'; ?>" <?php if ($doc_status != 'N') echo 'disabled'; ?> <?php if (isset($po_id) && !empty($po_id)) echo 'disabled'; ?>>
                                        <option value="3" <?php if ($wht_percent == '3') { echo "selected"; } ?>>3%</option>
                                        <option value="5" <?php if ($wht_percent == '5') { echo "selected"; } ?>>5%</option>
                                        <option value="0.50" <?php if ($wht_percent == '0.50') { echo "selected"; } ?>>0.5%</option>
                                        <option value="0.75" <?php if ($wht_percent == '0.75') { echo "selected"; } ?>>0.75%</option>
                                        <option value="1" <?php if ($wht_percent == '1') { echo "selected"; } ?>>1%</option>
                                        <option value="1.50" <?php if ($wht_percent == '1.50') { echo "selected"; } ?>>1.5%</option>
                                        <option value="2" <?php if ($wht_percent == '2') { echo "selected"; } ?>>2%</option>
                                        <option value="10" <?php if ($wht_percent == '10') { echo "selected"; } ?>>10%</option>
                                        <option value="15" <?php if ($wht_percent == '15') { echo "selected"; } ?>>15%</option>
                                    </select>
                                </span>
                                <span class="c2 wht <?php echo $wht_inc == 'Y' ? 'v' : 'h'; ?>">
                                    <input type="text" id="wht_value" readonly>
                                </span>
                                <span class="c3 wht <?php echo $wht_inc == 'Y' ? 'v' : 'h'; ?>">
                                    <span class="currency"><?php echo lang('THB'); ?></span>
                                </span>
                            </p>
                            <p id="s-payment-amount">
                                <span class="c1 custom-color wht <?php echo $wht_inc == 'Y' ? 'v' : 'h'; ?>">
                                    <?php echo lang('payment_amount'); ?>
                                </span>
                                <span class="c2 wht <?php echo $wht_inc == 'Y' ? 'v' : 'h'; ?>">
                                    <input type="text" id="payment_amount" readonly>
                                </span>
                                <span class="c3 wht <?php echo $wht_inc == 'Y' ? 'v' : 'h'; ?>">
                                    <span class="currency"><?php echo lang('THB'); ?></span>
                                </span>
                            </p>
                        <?php endif; ?>

                    </td>
                </tr>
            </tfoot>
        </table>
        <?php if (trim($remark) != ""): ?>
            <div class="remark clear">
                <p class="custom-color"><?php echo lang('remark'); ?></p>
                <p><?php echo nl2br($remark); ?></p>
            </div>
        <?php endif; ?>
    </div><!--.docitem-->

    <div class="payment_info clear" id="paymentInfo">
        <p class="custom-color">ข้อมูลการชำระเงิน</p>
        <div id="paymentItems"></div>
    </div>

    <div class="docsignature clear">
        <div class="customer">
            <div class="on_behalf_of">
                <?php // echo "ในนาม" . $client["company_name"]; ?>
            </div>
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
            <div class="on_behalf_of">
                <?php // echo "ในนาม" . get_setting("company_name"); ?>
            </div>
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
    window.addEventListener('keydown', function (event) {
        if (event.keyCode === 80 && (event.ctrlKey || event.metaKey) && !event.altKey && (!event.shiftKey || window.chrome || window.opera)) {
            event.preventDefault();
            if (event.stopImmediatePropagation) event.stopImmediatePropagation();
            else event.stopPropagation();
            return;
        }
    }, true);

    const paymentItems = $("#paymentItems");
    const paymentInfo = $("#paymentInfo");

    const loadPaymentItems = () => {
        let url = '<?php echo get_uri('goods_receipt/payments_items/' . $doc_id); ?>';

        axios.get(url).then(response => {
            const { data } = response;
            
            if (data.items.length === 0) {
                paymentInfo.addClass('hide');
            } else {
                let table = '';

                data.items.forEach((item, index) => {
                    table += `<table>`;
                    table += `<tr>`;
                    table += `<td rowspan="2" class="payment_number" width="5%">#${index + 1}</td>`;
                    table += `<td class="payment_key" width="15%">วันที่ชำระเงิน: </td>`;
                    table += `<td class="payment_value" width="15%">${item.date_output}</td>`;
                    table += `<td class="payment_type">`;
                    table += `<div>`;
                    table += `<span class="font-weight-bold">วิธีการชำระ: </span>`;
                    table += `<span style="margin-left: 1rem;">${item.type_name}</span>`;
                    table += `</div>`;
                    table += `</td>`
                    table += `<td rowspan="2" width="15%" class="text-center font-size-bigger font-weight-bold">${item.currency_format}</td>`;
                    table += `<td rowspan="2" class="text-center option">`;
                    table += `<a class="edit" data-post-item_id="${item.id}" data-post-doc_id="<?php echo $doc_id; ?>" data-act="ajax-modal" data-title="<?php echo "บันทึกข้อมูลการชำระเงิน"; ?>" data-action-url="<?php echo get_uri('goods_receipt/record_payment'); ?>">`;
                    table += `<i class="fa fa-pencil"></i></a></td>`;
                    table += `</tr>`;
                    table += `<tr>`;
                    table += `<td class="payment_key">จำนวนเงินรวม: </td>`;
                    table += `<td class="payment_value">${item.number_format}</td>`;
                    table += `<td class="payment_type">${item.type_description}</td>`;
                    table += `</tr>`;
                    table += `</table>`;
                });

                paymentItems.empty().append(table);
                paymentInfo.removeClass('hide');
            }
        }).catch(error => {
            console.log(error);
        });
    };

    $(document).ready(function () {
        loadItems();
        loadPaymentItems();

        $("#vat_inc, #wht_inc, #wht_percent").change(function () {
            loadSummary();
        });

        $('#btn-save-info').on('click', function (e) {
            e.preventDefault();
            saveDocumentInfo();
        });
    });

    function loadPayItems() {
        loadPaymentItems();
    }

    function loadItems() {
        let url = '<?php echo current_url(); ?>';
        let request = {
            task: 'load_items',
            doc_id: '<?php echo $doc_id; ?>'
        };
        // console.log(url, request);
        
        axios.post(url, request).then(function (response) {
            // console.log(response);

            const { doc_status, items, status, message } = response.data;
            if (status == 'notfound') {
                // console.log(status);

                let tbody = `<tr><td colspan="7" class="notfound">${message}</td></tr>`;
                $(`.docitem tbody`).empty().append(tbody);
            } else if (status == 'success') {
                // console.log(status);

                let tbody = '';
                items.map((item, id) => {

                    tbody += `<tr>`;
                    tbody += `<td>${id + 1}</td>`;
                    tbody += `<td>`;
                    tbody += `<p class="desc1">${item.product_name}</p>`;
                    tbody += `<p class="desc2">${item.product_description}</p>`;
                    tbody += `</td>`;
                    tbody += `<td>${new Intl.NumberFormat().format(item.quantity)}</td>`;
                    tbody += `<td>${item.unit}</td>`;
                    tbody += `<td>${item.price}</td>`;
                    tbody += `<td>${item.total_price}</td>`;
                    tbody += `<td class="edititem">`;
                    if (doc_status == 'N') {
                        tbody += `<a class="edit" data-post-doc_id="${request.doc_id}" data-post-item_id="${item.id}" data-act="ajax-modal" data-action-url="<?php echo_uri('goods_receipt/item'); ?>">`;
                        tbody += `<i class="fa fa-pencil"></i>`;
                        tbody += `</a>`;
                    }
                    tbody += `</td>`;
                    tbody += `</tr>`;
                });
                
                $(`.docitem tbody`).empty().append(tbody);
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
        };

        axios.post(url, request).then(function (response) {
            const { data } = response;

            $("#sub_total_before_discount").val(data.sub_total_before_discount);
            $("#wht_percent").val(data.wht_percent);
            $("#wht_value").val(data.wht_value);
            $("#total").val(data.total);
            $("#total_in_text").val(`(${data.total_in_text})`);
            $("#payment_amount").val(data.payment_amount);

            if (data.vat_inc == 'Y') {
                $("#vat_inc").prop("checked", true);
                $("#vat_value").val(data.vat_value);
            } else {
                $("#vat_inc").prop("checked", false);
                $("#vat_value").val(data.vat_value);
            }

            if (data.wht_inc == 'Y') {
                $("#wht_inc").prop("checked", true);
                $("#s-wht").removeClass("h").addClass("v");
                $(".wht").removeClass("h").addClass("v");
            } else {
                $("#wht_inc").prop("checked", false);
                $("#s-wht").removeClass("v").addClass("h");
                $(".wht").removeClass("v").addClass("h");
            }
        }).catch(function (error) {
            alert(error);
        });
    }

    const saveDocumentInfo = async () => {
        let url = '<?php echo_uri($active_module); ?>';
        let request = {
            taskName: 'update_doc_status',
            documentId: '<?php echo $doc_id; ?>',
            updateStatus: 'W'
        };

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
</script>