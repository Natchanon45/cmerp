<div id="page-content" class="p20 clearfix">
    <?php
    load_css(array(
        "assets/css/invoice.css",
    ));
    ?>

    <div class="invoice-preview">
        <?php
        if ($this->login_user->user_type === "client" && $delivery_info->status == "new") {
            ?>

            <div class = "panel panel-default  p15 no-border clearfix">

                <div class="mr15 strong pull-left">
                    <?php echo ajax_anchor(get_uri("deliverys/update_delivery_status/$delivery_info->id/accepted"), "<i class='fa fa fa-check-circle'></i> " . lang('mark_as_accepted'), array("class" => "btn btn-success mr15", "title" => lang('mark_as_accepted'), "data-reload-on-success" => "1")); ?>
                    <?php echo ajax_anchor(get_uri("deliverys/update_delivery_status/$delivery_info->id/declined"), "<i class='fa fa-times-circle-o'></i> " . lang('mark_as_rejected'), array("class" => "btn btn-danger mr15", "title" => lang('mark_as_rejected'), "data-reload-on-success" => "1")); ?>
                </div>
                <!-- <div class="pull-right">
                    <?php
                        echo "<div class='text-center'>" . anchor("deliverys/download_pdf/" . $delivery_info->id, lang("download_pdf"), array("class" => "btn btn-default round")) . "</div>";
                    ?>
                </div> -->

            </div>

            <?php
        } else if ($this->login_user->user_type === "client") {

            // echo "<div class='text-center'>" . anchor("deliverys/download_pdf/" . $delivery_info->id, lang("download_pdf"), array("class" => "btn btn-default round")) . "</div>";
        }
        if ($show_close_preview)
            echo "<div class='text-center'>" . anchor("deliverys/view/" . $delivery_info->id, lang("close_preview"), array("class" => "btn btn-default round")) . "</div>"
            ?>

        <div class="invoice-preview-container bg-white mt15">
            <div class="col-md-12">
                <div class="ribbon"><?php echo $delivery_status_label; ?></div>
            </div>

            <?php
            echo $delivery_preview;
            ?>
        </div>

    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#payment-amount").change(function () {
            var value = $(this).val();
            $(".payment-amount-field").each(function () {
                $(this).val(value);
            });
        });
    });



</script>
