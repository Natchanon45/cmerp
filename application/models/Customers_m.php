<?php
class Customers_m extends MY_Model {
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

    function isLead($client_id){
        $cusrow = $this->db->select("is_lead")
                            ->from("clients")
                            ->where("id", $client_id)
                            ->get()->row();

        if(empty($cusrow)) return null;
        if($cusrow->is_lead == 1) return true;
        else return false;
    }

    function getRows($fields = [], $isDeleted = 0){
        $db = $this->db;

        $s = "*";

        if(count($fields) > 0){
            $s = "";
            foreach($fields as $field){
                $s .= $field.",";
            }
        }

        $q = $db->select($s)->from("clients");

        if($isDeleted != "all"){
            $q->where("deleted", $isDeleted);
        }

        $cusrows = $q->get()->result();

        return $cusrows;
    }
}