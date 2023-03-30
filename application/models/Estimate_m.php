<?php

class Estimate_m extends CI_Model {

    function __construct() {}

    function doc($estimate_id){
        $db = $this->db;
        $data["status"] = "fail";
        $data["success"] = false;

        $esrow = $db->select("*")
                        ->from("estimates")
                        ->where("id", $estimate_id)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($esrow)) return $data;

        $data["esrow"] = $esrow;
        $data["status"] = "success";
        $data["success"] = true;

        return $data;
    }

    function loadDoc(){
        $db = $this->db;
        $doc_id = $this->input->post("doc_id");
        $data["status"] = "fail";
        $data["success"] = false;

        $esrow = $db->select("*")
                        ->from("estimates")
                        ->where("id", $doc_id)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($esrow)){
            return $data;
        }



        $discount_percent = $this->input->post("discount_percent");

        $data["sub_total_before_discount"] = number_format($esrow->sub_total_before_discount, 2);
        
        $data["discount_amount_type"] = $esrow->discount_amount_type == "percentage" ? "P":"F";
        $data["discount_percent"] = number_format($esrow->discount_percent > 99.99 ? 99.00 : $esrow->discount_percent, 2);
        $data["discount_amount"] = number_format($esrow->discount_amount, 2);


        $data["sub_total"] = number_format($esrow->sub_total_estimate, 2);
        $data["vat_inc"] = $esrow->vat_inc;
        $data["vat_percent"] = $esrow->vat_percent;
        $data["vat_value"] = number_format($esrow->vat_value, 2);
        $data["total"] = number_format($esrow->total_estimate, 2);
        $data["wht_inc"] = $esrow->wht_inc;
        $data["wht_percent"] = $esrow->wht_percent;
        $data["wht_value"] = $esrow->wht_value;
        $data["payment_amount"] = $esrow->payment_amount;

        $data["total_in_text"] = "(".numberToText($esrow->sub_total_estimate).")";

        return $data;
    }

    function item(){
        $db = $this->db;
        $data["status"] = "fail";
        $data["success"] = false;
        $doc_id = $this->input->post("doc_id");
        $item_id = $this->input->post("item_id");

        $esrow = $db->select("id")
                        ->from("estimates")
                        ->where("id", $doc_id)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($esrow)) return $data;

        $data["doc_id"] = $doc_id;
        $data["item_id"] = "";
        $data["title"] = "";
        $data["description"] = "";
        $data["quantity"] = "0";
        $data["unit_type"] = "";
        $data["rate"] = "0";
        $data["vat_type"] = "1";


        if(!empty($item_id)){
            $esirow = $db->select("*")
                        ->from("estimate_items")
                        ->where("id", $item_id)
                        ->get()->row();

                        

            if(empty($esirow)){
                return $data;
            }

            $data["item_id"] = $esirow->id;
            $data["title"] = $esirow->title;
            $data["description"] = $esirow->description;
            $data["quantity"] = $esirow->quantity;
            $data["unit_type"] = $esirow->unit_type;
            $data["rate"] = $esirow->rate;
            $data["vat_type"] = $esirow->vat_type;
        }

        $data["status"] = "success";
        $data["success"] = true;

        return $data;
    }

    function loadItems(){
    	$db = $this->db;
    	$data["status"] = "fail";
        $data["success"] = false;

        $doc_id = $this->input->get("doc_id");

    	$esrow = $db->select("id")
    					->from("estimates")
    					->where("id", $doc_id)
    					->where("deleted", 0)
    					->get()->row();

    	if(empty($esrow)) return $data;

    	$esirows = $db->select("id, title, description, quantity, unit_type, rate, price")
    					->from("estimate_items")
    					->where("estimate_id", $doc_id)
    					->where("deleted", 0)
    					->get()->result();

    	if(empty($esirows)){
            $data["status"] = "notfound";
    		$data["success"] = true;
    		$data["message"] = "ไม่พบข้อมูล";
    		return $data;
    	}

    	$items = [];

    	foreach($esirows as $esirow){
            $item["id"] = $esirow->id;
    		$item["title"] = $esirow->title;
    		$item["description"] = $esirow->description;
    		$item["quantity"] = $esirow->quantity;
    		$item["unit_type"] = $esirow->unit_type;
    		$item["rate"] = number_format($esirow->rate, 2);
    		$item["price"] = number_format($esirow->price, 2);

    		$items[] = $item;
    	}

    	$data["items"] = $items;
    	$data["status"] = "success";
        $data["success"] = true;

    	return $data;
    }

    function saveItem(){
        $db = $this->db;
        $data["status"] = "fail";
        $data["success"] = false;

        $doc_id = $this->input->post('doc_id');
        $item_id = $this->input->post('item_id');

        $esrow = $db->select("id")
                    ->from("estimates")
                    ->where("id", $doc_id)
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($esrow)){
            return $data;
        }

        $vat_type = (int)$this->input->post("vat_type");
        $vat_value = 0;
        $quantity = unformat_currency($this->input->post('estimate_item_quantity'));
        $rate = unformat_currency($this->input->post('estimate_item_rate'));
        $price_inc_vat = $price = $rate * $quantity;

        if($vat_type == 2){
            $price = roundUp($price / $this->Taxes_m->getVat());
            $vat_value = $price_inc_vat - $price;
        }

        $fdata = [
                    "title"=>$this->input->post('estimate_item_title'),
                    "description"=>$this->input->post('estimate_item_description'),
                    "quantity"=>$quantity,
                    "unit_type"=>$this->input->post('estimate_unit_type'),
                    "rate"=>$rate,
                    "price"=>$price,
                    "vat_type"=>$vat_type,
                    "vat_value"=>$vat_value,
                    "price_inc_vat"=>$price_inc_vat,
                    "deleted"=>0
                ];

        $db->trans_begin();
        
        if(empty($item_id)){
            $fdata["estimate_id"] = $doc_id;
            $db->insert("estimate_items", $fdata);
        }else{
            $db->where("id", $item_id);
            $db->where("estimate_id", $doc_id);
            $db->update("estimate_items", $fdata);
        }
        

        $this->updateDoc($doc_id);

        
        if ($db->trans_status() === FALSE){
            $db->trans_rollback();
        }else{
            $db->trans_commit();
        }

        $data["status"] = "success";
        $data["success"] = true;

        return $data;

    }

    function updateDoc($doc_id){
        $db = $this->db;

        $esrow = $db->select("*")
                    ->from("estimates")
                    ->where("id", $doc_id)
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($esrow)) return false;

        $sub_total_before_discount = $db->select("SUM(price) AS total_price")
                                        ->from("estimate_items")
                                        ->where("estimate_id", $doc_id)
                                        ->where("deleted", 0)
                                        ->get()->row()->total_price;


        $discount_amount_type = $esrow->discount_amount_type;
        $discount_percent = $esrow->discount_percent;
        $discount_amount = $esrow->discount_amount;


        if($discount_amount_type == "percentage" && $discount_percent > 0){
            $discount_amount = ($sub_total * $discount_percent)/100;
        }

        $sub_total = $sub_total_before_discount - $discount_amount;


        $vat_inc = $esrow->vat_inc;
        $vat_percent = 0;
        $vat_value = 0;
        if($vat_inc == "Y"){
            $vat_percent = $this->Taxes_m->getVatPercent();
            $vat_value = $sub_total * $this->Taxes_m->getVat();
        }
        $wht_inc = $esrow->wht_inc;
        $wht_percent = $esrow->wht_percent;
        $wht_value = $esrow->wht_value;
        $payment_amount = $esrow->payment_amount;

        $total = $sub_total;


        $db->where("id", $doc_id);
        $db->update("estimates", [
                                    "sub_total_before_discount"=>$sub_total_before_discount,
                                    "discount_amount_type"=>$discount_amount_type,
                                    "discount_percent"=>$discount_percent,
                                    "discount_amount"=>$discount_amount,
                                    "sub_total_estimate"=>$sub_total,
                                    "vat_inc"=>$vat_inc,
                                    "vat_percent"=>$vat_percent,
                                    "vat_value"=>$vat_value,
                                    "total_estimate"=>$total
                                ]);
    }

    function deleteItem(){
        $db = $this->db;
        $doc_id = $this->input->get("doc_id");
        $item_id = $this->input->get("item_id");
        $data["success"] = false;

        $esrow = $db->select("id")
                        ->from("estimates")
                        ->where("id", $doc_id)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($esrow)) return $data;

        $db->where("id", $item_id);
        $db->where("estimate_id", $doc_id);
        $db->update("estimate_items", ["deleted"=>1]);

        if($db->affected_rows() != 1) return $data;

        $data["process"] = true;
        return $data;
    }

}
