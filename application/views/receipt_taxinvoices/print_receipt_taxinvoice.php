<div id="page-content" class="clearfix">
    <?php
    load_css(array(
        "assets/css/receipt_taxinvoice.css",
    ));
    ?>

    <div class="receipt_taxinvoice-preview print-receipt_taxinvoice">
        <div class="receipt_taxinvoice-preview-container bg-white mt15">
            <div class="col-md-12">
                <div class="ribbon"><?php echo $receipt_taxinvoice_status_label; ?></div>
            </div>

            <?php echo $receipt_taxinvoice_preview; ?>
        </div>

    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("html, body").addClass("dt-print-view");
    });
</script>