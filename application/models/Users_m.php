<?php

class Users_m extends CI_Model {

    function __construct() {}

    function getInfo($user_id){
        $db = $this->db;
        
        $urow = $db->select("id, first_name, last_name, image")
                        ->from("users")
                        ->where("id", $user_id)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($urow)) return null;

        return [
                "id"=>$urow->id,
		"first_name"=>$urow->first_name,
		"last_name"=>$urow->last_name,
                "image"=>$urow->image
		];
    }

    function getInfoByLeadId($lead_id){
        $db = $this->db;
        
        $urow = $db->select("id, first_name, last_name, image")
                        ->from("users")
                        ->where("client_id", $lead_id)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($urow)) return null;

        return [
                "id"=>$urow->id,
                "first_name"=>$urow->first_name,
                "last_name"=>$urow->last_name,
                "image"=>$urow->image
                ];
    }

    public function get_user_by_cli($cli = 0)
    {
        $this->db->select("*")->from("users")->where("client_id", $cli)->where("deleted", 0)->where("is_primary_contact")->limit(1);
        $query = $this->db->get();

        return $query->row();
    }

    public function get_user_by_id($id = 0)
    {
        $this->db->select("*")->from("users")->where("id", $id)->where("deleted", 0)->limit(1);
        $query = $this->db->get();

        return $query->row();
    }

}
