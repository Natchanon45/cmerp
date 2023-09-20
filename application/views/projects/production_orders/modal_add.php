<style type="text/css">
.modal-dialog {
    width: calc(70% - 5rem);
    transition: width .55s ease;
}

@media only screen and (max-width: 1575px) {
    .modal-dialog {
        width: 1024px;
        transition: width .4s ease;
    }
}

@media only screen and (max-width: 1024px) {
    .modal-dialog {
        width: 99%;
        transition: width .4s ease;
    }
}

.modal-body {
    max-height: calc(100vh - 22.5rem);
    overflow-y: auto;
    overflow-x: auto;
}

.modal-body table {
    width: 100%;
}

.modal-body table th {
    text-align: center;
    border: 1px solid #f2f2f2;
}

.modal-body table th:last-child {
    width: 10%;
}

.modal-body tbody td {
    border: 1px solid #f2f2f2;
    padding: .8rem;
}

.modal-body tbody td:last-child {
    text-align: center;
    width: 10%;
}

.modal-body thead th {
    height: 50px;
}

.pointer-none {
    pointer-events: none;
    appearance: none;
}

.mw192px {
    min-width: 192px;
}

.mw96px {
    min-width: 96px;
}
</style>

<?php
echo form_open(
    get_uri("projects/production_order_modal_form_save"),
    array(
        "id" => "production-form",
        "class" => "general-form",
        "role" => "form"
    )
);
?>

<div class="modal-body clearfix">
    <input type="hidden" name="project_id" value="<?php if (isset($info["id"]) && !empty($info["id"])) { echo $info["id"]; } ?>">

    <table id="table-bom-select">
        <thead>
            <tr>
                <th width="35%"><?php echo lang("item"); ?></th>
                <th><?php echo lang("item_mixing_name"); ?></th>
                <th width="192px" class="mw192px"><?php echo lang("quantity"); ?></th>
                <th width="128px" class="mw96px">
                    <span><?php echo lang("production_order_produce_in"); ?></span>
                </th>
                <th>
                    <button id="btn-add-item" class="btn btn-primary">
                        <span class="fa fa-plus-circle"></span> 
                        <?php echo lang("add"); ?>
                    </button>
                </th>
            </tr>
        </thead>
        <tbody id="tbody-bom-select">
            <tr>
                <td>
                    <select name="item_id[]" class="form-control select-material" required>
                        <option></option>
                        <?php if (sizeof($items_dropdown)): ?>
                            <?php foreach ($items_dropdown as $item): ?>
                                <option value="<?php echo $item["id"]; ?>" data-unit="<?php echo $item["unit"]; ?>"><?php echo $item["text"]; ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </td>
                <td>
                    <select name="item_mixing[]" class="form-control pointer-none">
                        <option></option>
                    </select>
                </td>
                <td>
                    <div class="input-suffix">
                        <input name="quantity[]" class="form-control select-quantity" value="1" required>
                        <div class="input-tag"></div>
                    </div>
                </td>
                <td>
                    <select name="produce_in[]" class="form-control select-produce-in">
                        <option value="1"><?php echo lang("yes"); ?></option>
                        <option value="0"><?php echo lang("no"); ?></option>
                    </select>
                </td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">
        <span class="fa fa-close"></span> 
        <?php echo lang("close"); ?>
    </button>

    <button type="submit" class="btn btn-info" id="btn-submit" name="submit" value="1">
        <span class="fa fa-check-circle"></span> 
        <?php echo lang("save"); ?>
    </button>
</div>

<?php echo form_close(); ?>

<script type="text/javascript">
const items = <?php echo json_encode($items_dropdown); ?>;
const itemsMixing = <?php echo json_encode($items_mixing_dropdown); ?>;
const productionForm = $("#production-form");
const tableBody = $("#tbody-bom-select");

$(document).ready(function () {
    processBinding();

    $("#btn-add-item").on("click", function (e) {
        e.preventDefault();
        tableBody.append(`
            <tr>
                <td>
                    <select name="item_id[]" class="form-control select-material" required>
                        <option></option>
                        ${items.map((i) => {
                            return `<option value="${i.id}" data-unit="${i.unit}">${i.text}</option>`;
                        })}
                    </select>
                </td>
                <td>
                    <select name="item_mixing[]" class="form-control pointer-none">
                        <option></option>
                    </select>
                </td>
                <td>
                    <div class="input-suffix">
                        <input name="quantity[]" class="form-control select-quantity" value="1" required>
                        <div class="input-tag"></div>
                    </div>
                </td>
                <td>
                    <select name="produce_in[]" class="form-control select-produce-in">
                        <option value="1"><?php echo lang("yes"); ?></option>
                        <option value="2"><?php echo lang("no"); ?></option>
                    </select>
                </td>
                <td>
                    <button class="btn btn-danger button-delete">
                        <span class="fa fa-trash"></span> 
                        <?php echo lang("delete"); ?>
                    </button>
                </td>
            </tr>
        `);
        processBinding();
    });

    productionForm.appForm({
        onSuccess: function (result) {
            if (result.success) {
                setTimeout(async () => {
                    await window.parent.loadProductionOrderList();
                }, 1000);
            }
        }
    });
});

async function processBinding() {
    $(".select-produce-in").select2("destroy");
    $(".select-produce-in").select2();

    $(".button-delete").unbind();
    $(".button-delete").on("click", function (e) {
        e.preventDefault();

        $(this).closest("tr").remove();
        processBinding();
    });

    $(".select-quantity").unbind();
    $(".select-quantity").on("click", function (e) {
        e.preventDefault();
        $(this).select();
    });

    $(".select-material").select2("destroy");
    $(".select-material").select2();
    $(".select-material").unbind();
    $(".select-material").on("change", function () {
        let self = $(this);
        let option = $(this).find(`[value="${$(this).val()}"]`);
        let parent = self.closest("tr");

        parent.find(".input-tag").html(option.data("unit"));
        let optionMixing = itemsMixing.filter(i => i.item_id == $(this).val());
        let selectMixing = parent.find('[name="item_mixing[]"]');

        selectMixing.val('');
        selectMixing.find('option').remove();
        selectMixing.append('<option></option>');

        if (optionMixing.length) {
            optionMixing.map((i) => {
                selectMixing.append(`<option value="${i.id}">${i.name}</option>`);
            });

            selectMixing.removeClass('pointer-none hide');
            selectMixing.select2();
        } else {
            selectMixing.addClass('pointer-none hide');
        }
    });
};
</script>
