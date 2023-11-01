<style type="text/css">
    .modal-dialog {
        width: min(70%, 1024px);
    }

    .pointer-none {
        pointer-events: none;
    }

    .item-table table {
        width: 100%;
    }

    .item-table table th {
        height: 53px;
        text-align: center;
        border: 1px solid #f2f2f2;
    }

    .item-table table th:last-child {
        width: 10%;
        padding: .7rem;
    }

    .item-table table td {
        padding: .7rem;
        border: 1px solid #f2f2f2;
    }

    .item-table table td:last-child {
        text-align: center;
    }

    .select-unit {
        text-align: center;
        border: none !important;
        background: none !important;
    }

    .right-control {
        width: 100%;
    }

    .quantity-over {
        color: #f32013;
    }

    .quantity-done {
        color: #00b900;
    }

    #remark-text {
        resize: none;
    }
</style>

<?php
echo form_open(
    get_uri("goods_receipt/addnew_save"),
    array(
        "id" => "addnew-form",
        "class" => "general-form",
        "role" => "form"
    )
);
?>

<div class="modal-body clearfix">
    <div class="form-group">
        <label for="doc-date" class=" col-md-3">
            <?php echo lang("document_date"); ?>
        </label>
        <div class="col-md-9">
            <input type="text" id="doc-date" name="doc-date" class="form-control" autocomplete="off" readonly>
        </div>
    </div>

    <div class="form-group">
        <label for="project-id" class="col-md-3">
            <?php echo lang("project_refer"); ?>
        </label>
        <div class="col-md-9">
            <select name="project-id" id="project-id" class="form-control select-project" required>
                <option value="0"><?php echo "-- " . lang("project_refer") . " --"; ?></option>
                <?php if (sizeof($project_dropdown)): ?>
                    <?php foreach ($project_dropdown as $project): ?>
                        <option value="<?php echo $project["project_id"]; ?>"><?php echo $project["project_name"]; ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <?php if (isset($bom_supplier_read) && $bom_supplier_read): ?>
        <div class="form-group">
            <label for="supplier-id" class="col-md-3">
                <?php echo lang("suppliers"); ?>
            </label>
            <div class="col-md-9">
                <select id="supplier-id" name="supplier-id" class="form-control select-supplier" required>
                    <?php if (sizeof($supplier_dropdown)): ?>
                        <?php foreach ($supplier_dropdown as $supplier): ?>
                            <option value="<?php echo $supplier["supplier_id"]; ?>"><?php echo $supplier["supplier_name"]; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>
    <?php endif; ?>

    <div class="form-group">
        <label for="delivery-refer" class="col-md-3">
            <?php echo lang("gr_delivery_refer"); ?>
        </label>
        <div class="col-md-9">
            <input type="text" name="delivery-refer" id="delivery-refer" class="form-control select-delivery-refer" placeholder="<?php echo lang("gr_delivery_refer_placeholder"); ?>" maxlength="40" required>
        </div>
    </div>

    <div class="form-group">
        <label for="remark-text" class="col-md-3">
            <?php echo lang("remark"); ?>
        </label>
        <div class="col-md-9">
            <textarea name="remark-text" id="remark-text" class="form-control" cols="30" rows="10" placeholder="<?php echo lang("gr_remark_placeholder"); ?>"></textarea>
        </div>
    </div>

    <div class="form-group item-table">
        <table>
            <thead>
                <tr>
                    <th width="25%"><?php echo lang("purchase_order"); ?></th>
                    <th width="38%"><?php echo lang("details"); ?></th>
                    <th width="17%"><?php echo lang("quantity"); ?></th>
                    <th width="10%"><?php echo lang("stock_material_unit"); ?></th>
                    <th>
                        <?php if (isset($bom_supplier_read) && $bom_supplier_read): ?>
                            <button id="btn-add-item" class="btn btn-primary right-control">
                                <span class="fa fa-plus-circle"></span> 
                                <?php echo lang("add"); ?>
                            </button>
                        <?php endif; ?>
                    </th>
                </tr>
            </thead>

            <tbody id="tbody-po-select"></tbody>
        </table>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span>
        <?php echo lang("close"); ?>
    </button>
    <?php if (isset($bom_supplier_read) && $bom_supplier_read): ?>
        <button type="submit" id="btn-submit" class="btn btn-primary"><span class="fa fa-check-circle"></span>
            <?php echo lang("create"); ?>
        </button>
    <?php endif; ?>
</div>

<?php echo form_close(); ?>

<script type="text/javascript">
    const tableBody = $("#tbody-po-select");
    const supplierId = $("#supplier-id");
    const addNewForm = $("#addnew-form");

    let purchaseOrderList = [];
    let purchaseItemList = [];

    async function toggleSupplierId () {
        let trCount = await tableBody.find("tr").length;

        if (trCount) {
            supplierId.addClass('pointer-none');
        } else {
            supplierId.removeClass('pointer-none');
        }
    }

    async function toggleButtonAdd () {
        let trCount = await tableBody.find("tr").length;
        let itemCount = await purchaseItemList.length;
        // console.log(trCount, itemCount);

        if (trCount === itemCount) {
            $("#btn-add-item").addClass('hide');
        } else {
            $("#btn-add-item").removeClass('hide');
        }
    }

    async function getPurchaseOrderList () {
        let url = '<?php echo get_uri('goods_receipt/purchase_order_list'); ?>';
        let req = {
            supplier_id: $("#supplier-id").val()
        };
        // console.log(url, req);

        purchaseOrderList = [];
        purchaseItemList = [];

        await axios.post(url, req).then(res => {
            const { success, data } = res.data;

            if (success) {
                purchaseOrderList = data.orders;
                purchaseItemList = data.items;
            }
        }).catch(err => {
            console.log(err);
        });
    }

    async function processBinding () {
        $(".select-order").select2("destroy");
        $(".select-order").select2();

        $(".select-order").unbind();
        $(".select-order").on("change", function () {
            let self = $(this);
            let parent = self.closest("tr");
            let optionItems = purchaseItemList.filter(i => i.po_id == self.val());
            let selectItems = parent.find('[name="po_item_id[]"]');

            selectItems.val('');
            selectItems.find('option').remove();
            selectItems.append('<option></option>');

            if (optionItems.length) {
                optionItems.map((i) => {
                    selectItems.append(`<option value="${i.po_item_id}">${i.product_name}</option>`);
                });
                
                selectItems.removeClass('pointer-none hide');
                selectItems.select2();
            } else {
                selectItems.addClass('pointer-none hide');
            }
        });

        $(".select-item").unbind();
        $(".select-item").on("change", function () {
            let self = $(this);
            let parent = self.closest("tr");
            let quantity = parent.find('[name="quantity[]"]');
            let unit = parent.find('[name="unit[]"]');
            let selfInfo = purchaseItemList.filter(i => i.po_item_id == self.val())[0];

            quantity.attr("data-maximum", selfInfo.quantity);
            quantity.val(selfInfo.quantity);
            unit.val(selfInfo.unit);
        });

        $(".select-quantity").unbind();
        $(".select-quantity").on("click", function (e) {
            $(this).select();
        });

        $(".select-quantity").on("keypress", function (e) {
            if (e.which === 13) {
                e.preventDefault();

                let self = $(this);
                let parent = self.closest("tr");
                let statusQty = parent.find('[name="status_qty[]"]');

                let thisVal = parseFloat(self.val());
                let thisMax = parseFloat(self.data("maximum"));

                if (thisVal < 0 || thisVal > thisMax) {
                    self.removeClass("quantity-done");
                    self.addClass("quantity-over");

                    statusQty.val("N");
                } else {
                    self.removeClass("quantity-over");
                    self.addClass("quantity-done");

                    statusQty.val("Y");
                }
            }
        });

        $(".select-quantity").on("change", function (e) {
            let self = $(this);
            let parent = self.closest("tr");
            let statusQty = parent.find('[name="status_qty[]"]');

            let thisVal = parseFloat(self.val());
            let thisMax = parseFloat(self.data("maximum"));

            if (thisVal < 0 || thisVal > thisMax) {
                self.removeClass("quantity-done");
                self.addClass("quantity-over");

                statusQty.val("N");
            } else {
                self.removeClass("quantity-over");
                self.addClass("quantity-done");

                statusQty.val("Y");
            }
        });

        $(".button-delete").unbind();
        $(".button-delete").on("click", function (e) {
            e.preventDefault();

            $(this).closest("tr").remove();
            processBinding();
        });

        toggleSupplierId();
        toggleButtonAdd();
    }

    $(document).ready(function () {
        $("#doc-date").datepicker({
            autoclose: true,
            format: 'dd/mm/yyyy'
        });

        $("#doc-date").datepicker(
            "setDate", "<?php echo date("d/m/Y"); ?>"
        );

        $("#project-id").select2();

        $("#supplier-id").select2();
        
        getPurchaseOrderList();

        $("#supplier-id").on("click", function (e) {
            e.preventDefault();
            getPurchaseOrderList();
        });

        $("#btn-add-item").on("click", async function (e) {
            e.preventDefault();
            
            await tableBody.append(`
                <tr>
                    <td>
                        <select name="po_id[]" class="form-control select-order" required>
                            <option></option>
                            ${purchaseOrderList.map((i) => {
                                return `<option value="${i.po_id}">${i.po_number}</option>`;
                            })}
                        </select>
                    </td>
                    <td>
                        <select name="po_item_id[]" class="form-control select-item pointer-none hide">
                            <option></option>
                        </select>
                    </td>
                    <td>
                        <input name="quantity[]" class="form-control select-quantity" value="1" required>
                        <input name="status_qty[]" type="hidden" class="select-status_qty" value="Y">
                    </td>
                    <td>
                        <input name="unit[]" class="form-control select-unit" readonly>
                    </td>
                    <td>
                        <button class="btn btn-danger button-delete right-control">
                            <span class="fa fa-trash"></span> 
                            <?php echo lang("delete"); ?>
                        </button>
                    </td>
                </tr>
            `);

            await processBinding();
        });

        addNewForm.appForm({
            onSuccess: function (result) {
                console.log(result);
                const { post_result } = result;

                if (post_result.trans_status == "T") {
                    setTimeout(async () => {
                        await window.open(post_result.target_url, '_blank');

                        setTimeout(async () => {
                            await window.open(post_result.reload_url, '_self');
                        }, 1000);
                    }, 100);
                }
            }
        });
    });
</script>
