<div id="page-content" class="p20 clearfix">
    <div class="panel panel-default">
        <div class="page-title clearfix">
            <h1> <?php echo lang('resources'); ?></h1>
            <div class="title-button-group">
			
				<?php echo $buttonTop ?>
				 
            </div>
        </div>
        <div class="table-responsive">
            <table id="resources-table" class="display" cellspacing="0" width="100%">            
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#resources-table").appTable({
            source: '<?php echo_uri("resources/list_data") ?>',
            order: [[0, 'desc']],
            filterDropdown: [
                {name: "category_id", class: "w200", options: <?php echo $categories_dropdown; ?>}
            ],
            columns: [
                {title: "<?php echo lang('preview_image') ?> ", "class": "w100"},
                {title: "<?php echo lang('title') ?> ", "class": "w20p"},
                {title: "<?php echo lang('description') ?>"},
                 //{title: "<?php //echo lang('category') ?>"},
                {title: "<?php echo lang('unit_type') ?>", "class": "w100"},
                {title: "<?php echo lang('rate') ?>", "class": "text-right w100"},
                {title: "คำกำกับ", "class": "text-right w100"},
                {title: "<i class='fa fa-bars'></i>", "class": "text-center option w100"}
            ],
            printColumns: [0, 1, 2, 3],
            xlsColumns: [0, 1, 2, 3]
        });
    });
</script>