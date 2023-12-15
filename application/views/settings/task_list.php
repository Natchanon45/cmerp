<div id="page-content" class="p20 clearfix">
    <div class="row">
        <div class="col-sm-3 col-lg-2"><?php $this->load->view("settings/tabs", $active_tab);?></div>
        <div class="col-sm-9 col-lg-10">
            <div class="panel panel-default">
                <div class="page-title clearfix">
                    <h4>รายการงาน</h4>
                    <div class="title-button-group">
                        <?php echo modal_anchor(get_uri("settings/task_list_manage"), "<i class='fa fa-plus-circle'></i> รายการงาน", array("class" => "btn btn-default", "title" => "รายการงาน")); ?>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="task_list" class="display" cellspacing="0" width="100%">            
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $("#task_list").appTable({
            source: '<?php echo current_url(); ?>',
            columns: [
                {title: 'ชื่องาน', "class":"w30p"},
                {title: 'ผู้ได้รับมอบหมาย', "class":"w25p"},
                {title: 'ผู้ร่วมงาน', "class":"w30p"},
                {title: '<i class="fa fa-bars"></i>', "class": "text-center option w15p"}
            ]

        });
    });
</script>