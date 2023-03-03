<?php

$statuses = array(array("id" => "", "text" => "- " . lang("status") . " -"));
foreach ($pr_statuses as $status) {
    $statuses[] = array("id" => $status->id, "text" => $status->title);
}

echo json_encode($statuses);
?>