<?php

class Clients_m extends MY_Model {

    function __construct() {
        parent::__construct();
    }

    function getCompanyName($client_id){
        $db = $this->db;

        $crow = $db->select("company_name")
                        ->from("clients")
                        ->where("id", $client_id)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($crow)) return null;

        return $crow->company_name;
    }

    function getRows($is_lead = null){
        $db = $this->db;

        $db->select("*")
            ->from("clients")
            ->where("deleted", 0);

        if($is_lead !== null){
            $db->where("is_lead", $is_lead);
        }
                    
        $crows = $db->get()->result();

        return $crows;
    }

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
