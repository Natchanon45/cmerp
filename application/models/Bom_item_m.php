<?php
class Bom_item_m extends MY_Model {

    function __construct() {
		parent::__construct();
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
