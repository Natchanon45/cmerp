<?php

class Users_m extends CI_Model {

    function __construct() {}

    function getRows($field_names = null, $user_type = "staff"){
        $db = $this->db;
        
        if(!is_array($field_names)) $field_names = "*";

        return $db->select($field_names)
                    ->from("users")
                    ->where("user_type", $user_type)
                    ->where("status", "active")
                    ->where("deleted", 0)
                    ->get()->result();
    }

    function getRow($row_id, $field_names = null){
        $db = $this->db;
        if(!is_array($field_names)) $field_names = "*";

        $row = $db->select($field_names)
                    ->from("users")
                    ->where("id", $row_id)
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($row)) return null;

        return $row;
    }

    function getInfo($user_id){
        $db = $this->db;
        
        $urow = $db->select("id, first_name, last_name, email, image")
                        ->from("users")
                        ->where("id", $user_id)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($urow)) return null;

        return [
                "id"=>$urow->id,
        		"first_name"=>$urow->first_name,
        		"last_name"=>$urow->last_name,
                "email"=>$urow->email,
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

    function getSignature($user_id){
        $urow = $this->db->select("signature")
                            ->from("users")
                            ->where("id", $user_id)
                            ->get()->row();

        if(empty($urow)) return null;
        return $urow->signature;
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
