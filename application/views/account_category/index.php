<div id="page-content" class="p20 clearfix">
    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <?php
            $tab_view['active_tab'] = "account_category";
            $this->load->view("settings/tabs", $tab_view);
            ?>
        </div>

        <div class="col-sm-9 col-lg-10">
            <div class="panel panel-default">
                <div class="page-title clearfix">
                    <h4>
                        <?php echo lang('account_category'); ?>
                    </h4>
                    <div class="title-button-group">
                        <?php echo modal_anchor(get_uri("account_category/modal_form"), "<i class='fa fa-plus-circle'></i> " . lang('add_category'), array("class" => "btn btn-default", "title" => lang('add_category'))); ?>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="category-table" class="display" cellspacing="0" width="100%">
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#category-table").appTable({
            source: '<?php echo_uri("account_category/get_category_list"); ?>',
            columns: [
                { title: '<?php echo lang("id"); ?>', class: 'w50 text-center' },
                { title: '<?php echo lang("code"); ?>', class: 'w100 text-center' },
                { title: '<?php echo lang("title"); ?>' },
                { title: '<?php echo lang("created_by"); ?>', class: 'w150 text-center' },
                { title: '<?php echo lang("created_date"); ?>', class: 'w150 text-center' }
            ]
        });
    });
</script>