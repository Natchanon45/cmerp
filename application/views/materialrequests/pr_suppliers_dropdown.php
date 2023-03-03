<?php
$suppliers = array(array("id" => "", "text" => "- " . lang("suppliers") . " -"));
foreach ($pr_suppliers as $suppliier) {
    $suppliers[] = array("id" => $suppliier->id, "text" => $suppliier->title);
}

echo json_encode($suppliers);