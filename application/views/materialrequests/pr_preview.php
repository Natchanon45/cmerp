<div id="page-content" class="p20 clearfix">
    <?php
    //var_dump($mr_info);
    load_css(array(
        "assets/css/invoice.css",
    ));
    ?>

    <div class="invoice-preview">
        <?php
        if ($this->login_user->user_type === "client") {
            echo "<div class='text-center'>" . anchor("materialrequests/download_pdf/" . $mr_info->id, lang("download_pdf"), array("class" => "btn btn-default round")) . "</div>";
        }

        if ($show_close_preview) {
            echo "<div class='text-center'>" . anchor("materialrequests/view/" . $mr_info->id, lang("close_preview"), array("class" => "btn btn-default round")) . "</div>";
        }
        ?>

        <div class="invoice-preview-container bg-white mt15">
            <div class="col-md-12">
                <?php /*<div class="ribbon"><?php echo "<span class='mt0 label large' style='background-color: $mr_info->pr_status_color'>$mr_info->pr_status_title</span>"; </div>*/?>
            </div>

            <?php
            echo $mr_preview;
            ?>
        </div>

    </div>
</div>
