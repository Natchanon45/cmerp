<?php
$note_label_dropdown = array(array("id" => "", "text" => "- " . lang("label") . " -"));
foreach($label as $k => $v){
    $note_label_dropdown[] = array("id" => $v->title, "text" => $v->title);
}
echo json_encode($note_label_dropdown);
?>