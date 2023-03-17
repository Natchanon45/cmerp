<div class="panel">
    <div class="tab-title clearfix">
        <h4><?php echo lang('notes'); ?></h4>
        <div class="title-button-group">
            <?php echo modal_anchor(get_uri("notes/modal_form"), "<i class='fa fa-plus-circle'></i> " . lang('add_note'), array("class" => "btn btn-default", "title" => lang('add_note'), "data-post-project_id" => $project_id)); ?>           
        </div>
    </div>
    <div class="table-responsive">
        <table id="note-table" class="display" cellspacing="0" width="100%">            
        </table>
    </div>
</div>


<script type="text/javascript">
    $(document).ready(function () {
        $("#note-table").appTable({
            source: '<?php echo_uri("notes/list_data/project/" . $project_id) ?>',
            order: [[0, 'desc']],
            columns: [
                {title: '<?php echo lang("created_date"); ?>', "class": "w180"},
                {title: '<?php echo lang("title"); ?>'},
                {title: '<?php echo lang("created_by"); ?>'},
                {title: '<?php echo lang("files"); ?>', "class": "w180"},
                {title: '<i class="fa fa-bars"></i>', "class": "text-center option w120"}
            ]
        });
    });
</script>