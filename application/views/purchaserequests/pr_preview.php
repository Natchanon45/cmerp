<div id="page-content" class="p20 clearfix">
    <?php
    load_css(array(
        "assets/css/invoice.css",
    ));
    ?>

    <div class="invoice-preview">
        <?php
        if ($this->login_user->user_type === "client") {
            echo "<div class='text-center'>" . anchor("purchaserequests/download_pdf/" . $pr_info->id, lang("download_pdf"), array("class" => "btn btn-default round")) . "</div>";
        }

        if ($show_close_preview) {
            echo "<div class='text-center'>" . anchor("purchaserequests/view/" . $pr_info->id, lang("close_preview"), array("class" => "btn btn-default round")) . "</div>";
        }
        ?>

        <div class="invoice-preview-container bg-white mt15">
            <div class="col-md-12">
                <div class="ribbon"><?php echo "<span class='mt0 label large' style='background-color: $pr_info->pr_status_color'>$pr_info->pr_status_title</span>"; ?></div>
            </div>

            <?php
            echo $pr_preview;
            ?>
        </div>

    </div>
</div>
