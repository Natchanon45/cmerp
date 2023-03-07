<?php
if($this->Permission_m->access_note == "all" || $this->Permission_m->access_note == "specific" || $this->login_user->is_admin == "1"){
    $note_types = $this->Note_types_model->get_dropdown_list(array("title"), "id", []);
    $note_types_dropdown = array(array("id" => "0", "text" => "- ประเภทเอกสาร -"));
    foreach ($note_types as $id => $name) {
        if($this->login_user->is_admin == "1"){
            $note_types_dropdown[] = array("id" => $id, "text" => $name);
        }else{
            if(in_array($id, $available_note_types)) $note_types_dropdown[] = array("id" => $id, "text" => $name);
        }
    } 
}

$note_label_dropdown = array(array("id" => "", "text" => "- " . lang("label") . " -"));
foreach($label as $k => $v){
    $note_label_dropdown[] = array("id" => $v->title, "text" => $v->title);
}

if(isset($clients)){
    $note_clients_dropdown = array(array("id" => "", "text" => "- เจ้าของ -"));
    foreach($clients as $kc => $vc){
        $note_clients_dropdown[] = array("id" => $vc->id, "text" => $vc->first_name.' '.$vc->last_name);
    }
}
?>
<div id="page-content" class="p20 clearfix">
    <div class="panel panel-default">
        <div class="page-title clearfix">
            <!-- <h1><?php echo lang('notes') . " (" . lang('private') . ")"; ?></h1> -->
            <h1><?php echo lang('notes'); ?></h1>
            <div class="title-button-group"><?php echo $this->buttonTop ?></div>
        </div>
        <div class="table-responsive">
            <table id="note-table" class="display" cellspacing="0" width="100%">            
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
// {targets: [0], visible: true,searchable: true},

let filters = [];

<?php if($this->Permission_m->access_note == "all" || $this->Permission_m->access_note == "specific" || $this->login_user->is_admin == "1"): ?>
    filters.push({name: "note_type_id", class: "w150", options: <?php echo json_encode( $note_types_dropdown ); ?>});
<?php endif; ?>

filters.push({name: "label", class: "w150", options: <?php echo json_encode( $note_label_dropdown ); ?>});
<?php if(isset($clients)): ?>
filters.push({name: "client", class: "w150", options: <?php echo json_encode( $note_clients_dropdown ); ?>});
<?php endif; ?>
$(document).ready(function () {
    $("#note-table").appTable({
        source: '<?php echo_uri("notes/list_data") ?>',
        order: [[0, 'desc']],
        filterDropdown: filters,       
        columns: [                
            {title: '<?php echo lang("created_date"); ?>', "class": "w200"},
            {title: '<?php echo lang("title"); ?>'},
            {title: '<?php echo lang("created_by"); ?>', "class": "w200"},
            {title: '<?php echo lang("files") ?>', "class": "w250"},
            {title: '<i class="fa fa-bars"></i>', "class": "text-center option w250" }
        ]
    });
});
</script>