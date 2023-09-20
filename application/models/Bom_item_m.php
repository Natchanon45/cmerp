<?php
class Bom_item_m extends MY_Model {

    function __construct() {
		parent::__construct();
    }

    function getTotalRemaining($item_id){
        $db = $this->db;

        $total_remaining = $db->select("SUM(remaining) AS TOTAL_REMAINING")
            ->from("bom_item_stocks")
            ->where("item_id", $item_id)
            ->get()->row()->TOTAL_REMAINING;

        if($total_remaining == null) return 0;

        return $total_remaining;
    }

    function getMixingGroupsInfoById($mixing_groups_id){
        $db = $this->db;
        
        $bimgrow = $db->select("*")
                        ->from("bom_item_mixing_groups")
                        ->where("id", $mixing_groups_id)
                        ->get()->row();

        if(empty($bimgrow)) return null;

        return [
                "name"=>$bimgrow->name
                ];
    }

}
