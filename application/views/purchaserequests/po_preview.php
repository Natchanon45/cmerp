<div id="page-content" class="p20 clearfix">
    <?php
    load_css(array(
        "assets/css/invoice.css",
    ));
    ?>
    <div style="max-width: 1000px; margin: auto;">


        <div class="invoice-preview">

            <?php

            // if ($this->login_user->user_type === "client") {
            //     echo "<div class='text-center'>" . anchor(get_uri("pdf_export/po_pdf/" . $pr_info->id . "/" . $pr_info->doc_no), lang('download_po_pdf'), array("title" => lang('download_po_pdf'),"class" => "btn btn-default round")) . "</div>";
            // }

            if ($show_close_preview) {
                echo "<div class='text-center'>" . anchor("purchaserequests/PO", lang("close_preview"), array("class" => "btn btn-default round")) . "</div>";
            }
            ?>

            <div class="invoice-preview-container bg-white mt15">
                                    <div class="page-title clearfix ">
                        <h1><?php echo $pr_info->doc_no ? $pr_info->doc_no : lang('no_have_doc_no') . ':' . $pr_info->id; ?></h1>
                        <div class="title-button-group">
                            <span class="dropdown inline-block">
                                <button class="btn btn-info dropdown-toggle  mt0 mb0" type="button" data-toggle="dropdown" aria-expanded="true">
                                    <i class='fa fa-cogs'></i> <?php echo lang('actions'); ?>
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" role="menu">

                                    <li role="presentation"><?php echo anchor(get_uri("pdf_export/po_pdf/" . $pr_info->id . "/" . $pr_info->doc_no), "<i class='fa fa-download'></i> " . lang('download_po_pdf'), array("title" => lang('download_po_pdf'),)); ?> </li>
                                    <!-- <li role="presentation"><?php echo anchor(get_uri("purchaserequests/preview_po/" . $pr_info->id . "/1"), "<i class='fa fa-search'></i> " . lang('preview_po'), array("title" => lang('preview_po')), array("target" => "_blank")); ?> </li> -->
                                </ul>
                            </span>
                        </div>
                    </div>
                

                <br />
                <br />
                <div class="col-md-12">
                    <div class="ribbon"><?php echo "<span class='mt0 label large' style='background-color: $pr_info->pr_status_color'>$pr_info->pr_status_title</span>"; ?></div>
                </div>

                <?php
                echo $pr_preview;
                ?>
            </div>

        </div>

    </div>
</div>