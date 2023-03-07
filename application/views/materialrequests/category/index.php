<div id="page-content" class="p20 clearfix">
    <div class="panel clearfix">
        <ul id="pr-cat-tabs" data-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
            <li class="title-tab"><h4 class="pl15 pt10 pr15"><?php echo lang('category_manager'); ?></h4></li>
            <div class="tab-title clearfix no-border">
                <div class="title-button-group">
					<?php echo $add_row?$buttonTops:''; ?>
                    <?php /*<?php echo js_anchor("<i class='fa fa-plus-circle'></i> " . lang('add_pr2'), array("class" => "btn btn-default", "id" => "add-pr-btn2")); ?>*/?>
                </div>
            </div>
        </ul>
        <div class="tab-content">
            <table id="cat-table" class="display" cellspacing="0" width="100%"></table>
        </div>
    </div>
</div>

<style>
</style>
<script type="text/javascript">
loadCatTable = function (selector) {
    $(selector).appTable({
        source: '<?php echo_uri("materialrequests/list_categories_data") ?>',
        order: [[0, "desc"]],
        filterDropdown: [],
        columns: [
            {title: "<?php echo lang("category_name") ?>"},
            {title: "<?php echo lang("description") ?>", "class": "w20p"},
            {visible: false, searchable: false},
            {title: "<?php echo lang("created_date") ?>", "class": "w10p"},
            {title: "<?php echo lang("created_by") ?>", "class": "w10p"},
            {title: "<i class='fa fa-bars'></i>", "class": "text-center option w100"}
        ],
        //summation: [{column: 5}]
    });
};
$(document).ready(function () {
    loadCatTable("#cat-table");
    $("#add-cat-btn").attr('data-act','ajax-modal');
    $("#back-to-pr-btn").on('click', function () {
        window.location.href = "<?php echo get_uri("materialrequests");?>";
    });
});
</script>