<div id="page-content" class="p20 clearfix">
    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <?php
                $tab_view["active_tab"] = "service_wage";
                $this->load->view("settings/tabs", $tab_view);
            ?>
        </div>

        <div class="col-sm-9 col-lg-10">
            <div class="panel panel-default">
                <div class="page-title clearfix">
                    <h4>
                        <?php echo lang("service_wage"); ?>
                    </h4>
                    <div class="title-button-group">
                        <?php
                            echo modal_anchor(
                                get_uri("account_category/services_modal"),
                                "<i class='fa fa-plus-circle'></i> " . lang("add_category"),
                                array(
                                    "class" => "btn btn-default",
                                    "title" => lang("service_wage")
                                )
                            );
                        ?>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="services-table" class="display" cellspacing="0" width="100%">
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#services-table").appTable({
            source: '<?php echo_uri("account_category/display_services_list"); ?>',
            filterDropdown: [
                { name: 'income_acct_cate_id', class: 'w250', options: JSON.parse('<?php echo $income_dropdown; ?>') },
                { name: 'expense_acct_cate_id', class: 'w250', options: JSON.parse('<?php echo $expense_dropdown; ?>') }
            ],
            columns: [
                { title: '<?php echo lang("id"); ?>', class: 'w50 text-center' },
                { title: '<?php echo lang("service_wage"); ?>' },
                { title: '<?php echo lang("expense_account_category"); ?>' },
                { title: '<?php echo lang("income_account_category"); ?>' },
                { title: '<i class="fa fa-bars"></i>', class: 'w100 text-center option' }
            ]
        });
    });
</script>