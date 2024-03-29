<?php 
    echo form_open(
        get_uri("stock/item_save_client_from_excel_file"), 
        array("id" => "import-material-form", "class" => "general-form", "role" => "form")
    );
?>
<div class="modal-body clearfix import-client-modal-body">
    <div id="upload-area">
        <?php
            $this->load->view("includes/multi_file_uploader", array(
                "upload_url" => get_uri("stock/item_upload_excel_file"),
                "validation_url" => get_uri("stock/item_validate_import_file"),
                "max_files" => 1,
                "hide_description" => true
            ));
        ?>
    </div>
    <input type="hidden" name="file_name" id="import_file_name" value="" />
    <div id="preview-area"></div>
</div>

<div class="modal-footer">
    <?php echo anchor("stock/item_sample_excel_file", "<i class='fa fa-cloud-download'></i> " . lang("download_sample_file"), array("title" => lang("download_sample_file"), "class" => "btn btn-default pull-left")); ?>
    <button type="button" class="btn btn-default cancel-upload" data-dismiss="modal"><span class="fa fa-close"></span> <?php echo lang('close'); ?></button>
    <button id="form-previous" type="button" class="btn btn-default hide"><span class="fa fa-arrow-circle-left"></span> <?php echo lang('back'); ?></button>
    <button id="form-next" type="button" disabled="true" class="btn btn-info"><span class="fa  fa-arrow-circle-right"></span> <?php echo lang('next'); ?></button>
    <button id="form-submit" type="button" disabled="true" class="btn btn-primary start-upload hide"><span class="fa fa-check-circle"></span> <?php echo lang('upload'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        console.log($("#import-material-form"))

        $("#import-material-form").ajaxSuccess(function(d){
            console.log(d)
        })

        $("#import-material-form").appForm({
            onSuccess: function () {
                location.reload();
            }
        });

        var $uploadArea = $("#upload-area"),
            $previewArea = $("#preview-area"),
            $previousButton = $("#form-previous"),
            $nextButton = $("#form-next"),
            $modalBody = $(".import-client-modal-body"),
            $submitButton = $("#form-submit"),
            $ajaxModal = $("#ajaxModal");

        removeLargeModal(); //remove app-modal credentials on loading modal

        function addLargeModal() {
            $ajaxModal.addClass("import-client-app-modal");
            $ajaxModal.addClass("app-modal");
            $ajaxModal.find("div.modal-dialog").addClass("app-modal-body");
            $ajaxModal.find("div.modal-content").addClass("h100p");
        }

        function removeLargeModal() {
            $ajaxModal.find("div.modal-dialog").removeClass("app-modal-body");
            $ajaxModal.find("div.modal-content").removeClass("h100p");
            $ajaxModal.removeClass("app-modal");
        }

        $submitButton.click(function () {
            $("#import-material-form").trigger("submit");
        });

        //validate the uploaded excel file by clicking next
        $nextButton.click(function () {
            var fileName = $("#uploaded-file-previews").find("input[type=hidden]:eq(1)").val();
            if (!fileName) {
                appAlert.error("<?php echo lang('something_went_wrong'); ?>");
                return false;
            }
            appLoader.show({container: ".import-client-modal-body", css:"left:0;"});
            var $button = $(this);
            $button.attr("disabled", true);
            
            $("#import_file_name").val(fileName);

            $.ajax({
                url: "<?php echo get_uri('stock/item_validate_import_file_data') ?>",
                type: 'POST',
                dataType: 'json',
                data: {file_name: fileName},
                success: function (result) {
                    appLoader.hide();
                    $button.removeAttr('disabled');
                    
                    if (result.success) {
                        $uploadArea.addClass("hide");
                        $nextButton.addClass("hide");
                        $previousButton.removeClass("hide");
                        $submitButton.removeClass("hide");
                        $previewArea.removeClass("hide");

                        $previewArea.html(result.table_data);
                        $modalBody.height($(window).height() - 165);
                        $modalBody.addClass("overflow-y-scroll");
                        addLargeModal();

                        if (result.got_error) {
                            $submitButton.prop("disabled", true);
                        } else {
                            $submitButton.prop("disabled", false);
                        }
                    }
                }
            });
        });

        $previousButton.click(function () {
            $(this).addClass("hide");
            $submitButton.addClass("hide");
            $uploadArea.removeClass("hide");
            $previewArea.addClass("hide");
            $nextButton.removeClass("hide");

            $modalBody.height($(window).height() - 230);
            removeLargeModal();
        });
    });
</script>    
