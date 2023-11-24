<div id="page-content" class="p20 clearfix">
    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <?php
            $tab_view['active_tab'] = "project_types";
            $this->load->view("settings/tabs", $tab_view);
            ?>
        </div>

        <div class="col-sm-9 col-lg-10">
            <div class="panel panel-default">

                <ul id="project-type-tabs" data-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
                    <li><a  role="presentation" class="active" href="javascript:;" data-target="#project-types-tab">ประเภทโปรเจค</a></li>
                    <div class="tab-title clearfix no-border">
                        <div class="title-button-group">
                            <?php echo modal_anchor(get_uri("project_types/modal_form"), "<i class='fa fa-plus-circle'></i> เพิ่มประเภทโปรเจค", array("class" => "btn btn-default", "title" => "เพิ่มประเภทโปรเจค")); ?>
                        </div>
                    </div>
                </ul>

                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane fade" id="project-types-tab">
                        <div class="table-responsive">
                            <table id="project-type-table" class="display" cellspacing="0" width="100%">            
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $("#project-type-table").appTable({
            source: '<?php echo_uri("project_types/list_data") ?>',
            columns: [
                {title: '<?php echo lang("name"); ?>'},
                {title: '<i class="fa fa-bars"></i>', "class": "text-center option w100"}
            ],
            printColumns: [0, 1]
        });

        setTimeout(function () {
            var tab = "<?php echo $tab; ?>";
            if (tab === "imap") {
                $("[data-target=#imap_settings-tab]").trigger("click");
            }
        }, 210);

    });
</script>
