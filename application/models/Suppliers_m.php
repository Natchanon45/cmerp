<?php
class Suppliers_m extends MY_Model {
    function getInfo($supplier_id){
        $db = $this->db;
        
        $suprow = $db->select("*")
                        ->from("bom_suppliers")
                        ->where("id", $supplier_id)
                        ->get()->row();

        if(empty($suprow)) return null;

        return [
                "company_name"=>$suprow->company_name,
                "address"=>$suprow->address,
                "city"=>$suprow->city,
                "state"=>$suprow->state,
                "zip"=>$suprow->zip,
                "country"=>$suprow->country,
                "website"=>$suprow->website,
                "phone"=>$suprow->phone,
                "vat_number"=>$suprow->vat_number
                ];
    }

    function getContactInfo($supplier_id, $primary_contact = true){
        $db = $this->db;

        $urow = $db->select("*")
                        ->from("bom_supplier_contacts")
                        ->where("id", $supplier_id)
                        ->where("is_primary", 1)
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

    function getRows($fields = [], $isDeleted = 0){
        $db = $this->db;

        $s = "*";

        if(count($fields) > 0){
            $s = "";
            foreach($fields as $field){
                $s .= $field.",";
            }
        }

        $q = $db->select($s)->from("bom_suppliers");

        $suprows = $q->get()->result();

        return $suprows;
    }
}