<?php

class Users_m extends CI_Model {

    function __construct() {}

    function getInfo($user_id){
        $db = $this->db;
        
        $urow = $db->select("*")
                        ->from("users")
                        ->where("id", $user_id)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($urow)) return null;

        return [
        		"first_name"=>$urow->first_name,
        		"last_name"=>$urow->last_name
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
