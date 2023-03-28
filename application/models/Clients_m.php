<?php

class Clients_m extends CI_Model {

    function __construct() {}

    function getInfo($client_id){
        $db = $this->db;
        
        $crow = $db->select("*")
                        ->from("clients")
                        ->where("id", $client_id)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($crow)) return null;

        return [
        		"company_name"=>$crow->company_name,
        		"address"=>$crow->address,
        		"city"=>$crow->city,
        		"state"=>$crow->state,
        		"zip"=>$crow->zip,
        		"country"=>$crow->country,
        		"website"=>$crow->website,
        		"phone"=>$crow->phone,
        		"vat_number"=>$crow->vat_number
        		];
    }

    function getContactInfo($client_id, $primary_contact = true){
        $db = $this->db;

        $urow = $db->select("*")
                        ->from("users")
                        ->where("client_id", $client_id)
                        ->where("is_primary_contact", 1)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($urow)) return null;

        return [
                "id"=>$urow->id,
                "first_name"=>$urow->first_name,
                "last_name"=>$urow->last_name,
                "phone"=>$urow->phone,
                "email"=>$urow->email
                ];
    }

    

}
