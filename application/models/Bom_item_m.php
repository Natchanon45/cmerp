<?php
class Bom_item_m extends MY_Model {

    function __construct() {
		parent::__construct();
    }

    function getTotalRemainingItems($item_id){
        $db = $this->db;

        /*$total_remaining = $db->select("SUM(remaining) AS TOTAL_REMAINING")
            ->from("bom_item_stocks")
            ->where("item_id", $item_id)
            ->get()->row()->TOTAL_REMAINING;

        if($total_remaining == null) return 0;*/

        $total_stock = $db->select("SUM(stock) AS TOTAL_STOCK")
                            ->from("bom_item_stocks")
                            ->where("item_id", $item_id)
                            ->get()->row()->TOTAL_STOCK;

        if($total_stock == null) $total_stock = 0;

        $total_used = $db->select("SUM(ratio) AS TOTAL_RATIO")
                            ->from("bom_project_item_items")
                            ->where("item_id", $item_id)
                            ->get()->row()->TOTAL_RATIO;


        if($total_used == null) $total_used = 0;

        return $total_stock - $total_used;
        
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
