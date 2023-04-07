<?php

class Quotations_m extends MY_Model {
    private $code = "QT";

    function __construct() {
        parent::__construct();
    }

    function getCode(){
        return $this->code;
    }

    function getStatusName($status_code){
        if($status_code == "W"){
            return "รออณุมัต";
        }
    }

    function getIGrid($qrow){
        $data = [
                    "<a href='".get_uri("quotations/view/".$qrow->id)."'>".$qrow->doc_number."</a>",
                    "<a href='".get_uri("clients/view/".$qrow->client_id)."'>".$this->Clients_m->getCompanyName($qrow->client_id)."</a>",
                    $qrow->doc_date,
                    $qrow->total,
                    $this->getStatusName($qrow->status),
                    "<a data-post-id='".$qrow->id."' data-action-url='".get_uri("quotations/doc")."' data-act='ajax-modal' class='edit'><i class='fa fa-pencil'></i></a><a data-id='".$qrow->id."' data-action-url='".get_uri("quotations/delete")."' data-action='delete' class='delete'><i class='fa fa-times fa-fw'></i></a>"
                ];

        return $data;
    }

    function source() {
        $db = $this->db;

        $db->select("*")->from("quotation");

        if($this->input->post("status") != null){
            $db->where("status", $this->input->post("status"));
        }

        if($this->input->post("start_date") != null && $this->input->post("end_date")){
            $db->where("doc_date >=", $this->input->post("start_date"));
            $db->where("doc_date <=", $this->input->post("end_date"));
        }

        $db->where("deleted", 0);

        $qrows = $db->get()->result();

        $data = [];

        foreach($qrows as $qrow){
            $data[] = $this->getIGrid($qrow);
        }

        return $data;
    }

    function doc($docId){
        $db = $this->db;
        $data["status"] = "fail";
        $data["success"] = false;

        $qrow = $db->select("*")
                        ->from("quotation")
                        ->where("id", $docId)
                        ->get()->row();

        if(empty($qrow)) return $this->data;


        $this->data["qrow"] = $qrow;
        $this->data["status"] = "success";

        return $this->data;
    }

    /*function doc(){
        $db = $this->db;
        $docId = $this->input->post("docId");
        $data["status"] = "fail";
        $data["success"] = false;

        $esrow = $db->select("*")
                        ->from("estimates")
                        ->where("id", $docId)
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
    }*/


    function validateDoc(){
        $this->form_validation->set_rules([
                                            [
                                                "field"=>"quotation_date",
                                                'label' => '',
                                                'rules' => 'required'
                                            ],
                                            [
                                                "field"=>"quotation_valid_until_date",
                                                'label' => '',
                                                'rules' => 'required'
                                            ]
                                        ]);

        if ($this->form_validation->run() == FALSE){
            $this->data["status"] = "validate";
            if(form_error('quotation_date') != null) $this->data["messages"]["quotation_date"] = form_error('quotation_date');
            if(form_error('quotation_valid_until_date') != null) $this->data["messages"]["quotation_valid_until_date"] = form_error('quotation_valid_until_date');
        }

    }

    function saveDoc(){
        $db = $this->db;
        $_POST = json_decode(file_get_contents('php://input'), true);

        $this->validateDoc();
        if($this->data["status"] == "validate") return $this->data;

        $docId = $this->input->post("doc_id");
        $doc_date = converDate($this->input->post("quotation_date"));
        $credit = $this->input->post("credit");
        $doc_valid_until_date = converDate($this->input->post("quotation_valid_until_date"));
        $reference_number = $this->input->post("reference_number");
        $client_id = $this->input->post("client_id");
        $project_id = $this->input->post("project_id");
        $remark = $this->input->post("remark");


        if($docId != ""){
            $db->where("id", $docId);
            $db->where("deleted", 0);
            $db->update("quotation", [
                                        "doc_date"=>$doc_date,
                                        "credit"=>$credit,
                                        "doc_valid_until_date"=>$doc_valid_until_date,
                                        "reference_number"=>$reference_number,
                                        "client_id"=>$client_id,
                                        "project_id"=>$project_id,
                                        "remark"=>$remark,
                                    ]);
        }else{
            $db->where("DATE_FORMAT(crreated_datetime,'%Y-%m')", date("Y-m"));
            $running_number = $db->get("quotation")->num_rows() + 1;

            $doc_number = $this->getCode().date("Ym").sprintf("%04d", $running_number);
            
            $db->insert("quotation", [
                                        "doc_number"=>$doc_number,
                                        "doc_date"=>$doc_date,
                                        "credit"=>$credit,
                                        "doc_valid_until_date"=>$doc_valid_until_date,
                                        "reference_number"=>$reference_number,
                                        "client_id"=>$client_id,
                                        "project_id"=>$project_id,
                                        "remark"=>$remark,
                                        "created_by"=>$this->login_user->id,
                                        "crreated_datetime"=>date("Y-m-d H:i:s"),
                                        "status"=>"W"
                                    ]);

            $docId = $db->insert_id();
        }
        
        $this->data["target"] = get_uri("quotations/view/". $docId);
        $this->data["status"] = "success";
        $this->data["message"] = "success";

        return $this->data;
    }

    function deleteDoc(){
        $db = $this->db;
        $docId = $this->input->post("id");

        $db->where("id", $docId);
        $db->update("quotation", ["deleted"=>1]);

        $data["success"] = true;
        $data["message"] = lang('record_deleted');

        return $data;
    }

    function undoDoc(){
        $db = $this->db;
        $docId = $this->input->post("id");

        $db->where("id", $docId);
        $db->update("quotation", ["deleted"=>0]);

        $qrow = $db->select("*")
                    ->from("quotation")
                    ->where("id", $docId)
                    ->get()->row();

        $data["success"] = true;
        $data["data"] = $this->getIGrid($qrow);
        $data["message"] = lang('record_undone');

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

    function jItems(){
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

    	return json_encode($data);
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
