<?php

class Users_m extends CI_Model {

    function __construct() {}

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
}
