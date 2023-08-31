<style type="text/css">
.input-suffix > .input-tag-2 {
    position: absolute;
    top: 0;
    right: 0;
    padding: 6px 25px;
    font-size: 15px;
}

.font-red {
    color: #ff0000;
}

.font-green {
    color: #008000;
}
</style>

<div class="general-form modal-body clearfix">
    
    <div class="alert alert-danger hide" id="alert" role="alert"></div>

    <div class="form-group">
        <label for="payments_date" class=" col-md-3">
            <?php echo lang('payment_date'); ?>
        </label>
        <div class="col-md-9">
            <input type="text" name="payments_date" id="payments_date" class="form-control" autocomplete="off" readonly>
        </div>
    </div>

    <div class="form-group">
        <label for="payments_amount" class="col-md-3">
            <?php echo lang('payments_amount'); ?>
        </label>
        <div class="col-md-9 input-suffix">
            <input type="text" name="payments_amount" id="payments_amount" class="form-control" value="<?php echo $remain_amount; ?>" autocomplete="off">
            <div class="input-tag-2"><?php echo lang('THB'); ?></div>
        </div>
    </div>

    <div class="form-group">
        <label for="payments_method" class="col-md-3">
            <?php echo lang('payments_method'); ?>
        </label>
        <div class="col-md-9">
            <input type="text" name="payments_method" id="payments_method" class="form-control" autocomplete="off">
            <input type="hidden" name="payments_name" id="payments_name">
        </div>
    </div>

    <div class="form-group">
        <label for="payments_description" class="col-md-3">
            <span><?php echo lang('description') . ' /'; ?></span>
            <p><?php echo lang('payments_description'); ?></p>
        </label>
        <div class="col-md-9">
            <textarea name="payments_description" id="payments_description" class="form-control"></textarea>
        </div>
    </div>

</div>

<div class="modal-footer clearfix">
    <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span>
        <?php echo lang('close'); ?>
    </button>
    <button type="button" id="btnSubmit" class="btn btn-primary"><span class="fa fa-check-circle"></span>
        <?php echo lang('save'); ?>
    </button>
</div>

<script type="text/javascript">
const payments_dropdown = <?php echo json_encode($payments_dropdown); ?>;
const maximum_amount = '<?php echo $remain_amount; ?>';

const alertController = (message) => {
    let tag = document.createElement('p');
    tag.textContent = message;

    let alert = $("#alert");
    alert.empty().append(tag);
    alert.removeClass('hide');

    setTimeout(function () {
        alert.addClass('hide');
    }, 3001);
};

$(document).ready(function () {
    // Payment Date
    $("#payments_date").datepicker({
        yearRange: '<?php echo date('Y'); ?>',
        format: 'dd/mm/yyyy',
        changeMonth: true,
        changeYear: true,
        autoclose: true,
        endDate: 'today'
    });
    $("#payments_date").datepicker("setDate", "<?php echo date('d/m/Y'); ?>");

    // Payment Amount
    $("#payments_amount").on('blur', function (event) {
        event.preventDefault();

        // Verify amount
        if ($(this).val() == '') {
            $(this).addClass('font-red');
            $(this).removeClass('font-green');
        } else {
            let pay = parseFloat($(this).val());
            let max = parseFloat(maximum_amount);

            if (pay <= 0 || pay > max) {
                $(this).addClass('font-red');
                $(this).removeClass('font-green');
            } else {
                $(this).removeClass('font-red');
                $(this).addClass('font-green');
            }
        }
    });

    $("#payments_amount").on('click', function (event) {
        $(this).select();
    });

    // Payment Method
    $("#payments_method").select2({
        multiple: false,
        data: payments_dropdown
    });

    $("#payments_method").on('change', function () {
        payments_dropdown.forEach(element => {
            if ($(this).val() == element.id) {
                $("#payments_name").val(element.text);
                $("#payments_description").val(element.description);
            }
        });
    });

    // Button Save
    $("#btnSubmit").on('click', function (event) {
        event.preventDefault();

        let pay = parseFloat($("#payments_amount").val());
        let max = parseFloat(maximum_amount);

        if ($("#payments_amount").val() == '' || pay <= 0) {
            alertController('ระบุจำนวนเงินรวมชำระไม่ถูกต้อง');
            return;
        }

        if (pay > max) {
            alertController('จำนวนเงินรวมชำระเกินยอดชำระเต็มจำนวน');
            return;
        }

        if ($("#payments_method").val() == '') {
            alertController('ยังไม่ได้เลือกวิธีการชำระเงิน');
            return;
        }

        if ($("#payments_name").val() == '') {
            alertController('ยังไม่ได้เลือกวิธีการชำระเงิน');
            return;
        }

        if ($("#payments_description").val() == '') {
            alertController('ยังไม่ได้ระบุคำบรรยายหรือรายละเอียดการชำระ');
            return;
        }
        
        let url = '<?php echo get_uri('goods_receipt/payments_save'); ?>';
        let request = {
            documentId: '<?php echo $doc_id; ?>',
            paymentDate: $("#payments_date").val(),
            paymentAmount: $("#payments_amount").val(),
            paymentMethodId: $("#payments_method").val(),
            paymentMethodName: $("#payments_name").val(),
            paymentMethodDescription: $("#payments_description").val()
        };

        axios.post(url, request).then(response => {
            const { data } = response;

            if (data.status == 'success') {
                window.parent.loadPayItems();
                $("#ajaxModal").modal("hide");
            } else {
                appAlert.error('500 Internal server error.', {
                    duration: 3001
                });
            }
        }).catch(error => {
            console.log(error);
        });
    });
});
</script>
