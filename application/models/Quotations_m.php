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

    function getIGridData($qrow){
        $data = [
                    "<a href='".get_uri("quotations/view/".$qrow->id)."'>".$qrow->doc_number."</a>",
                    "<a href='".get_uri("clients/view/".$qrow->client_id)."'>".$this->Clients_m->getCompanyName($qrow->client_id)."</a>",
                    converDate($qrow->doc_date, true),
                    number_format($qrow->total, 2),
                    $this->getStatusName($qrow->status),
                    "<a data-post-id='".$qrow->id."' data-action-url='".get_uri("quotations/doc")."' data-act='ajax-modal' class='edit'><i class='fa fa-pencil'></i></a><a data-id='".$qrow->id."' data-action-url='".get_uri("quotations/delete_doc")."' data-action='delete' class='delete'><i class='fa fa-times fa-fw'></i></a>"
                ];

        return $data;
    }

    function igrid() {
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
            $data[] = $this->getIGridData($qrow);
        }

        return $data;
    }

    function doc($docId){
        $db = $this->db;

        $this->data["doc_date"] = date("Y-m-d");
        $this->data["credit"] = "0";
        $this->data["doc_valid_until_date"] = date("Y-m-d");
        $this->data["reference_number"] = "";
        $this->data["vat_inc"] = "N";
        $this->data["wht_inc"] = "N";
        $this->data["project_id"] = null;
        $this->data["client_id"] = null;
        $this->data["remark"] = "";
        $this->data["created_by"] = "";

        if(!empty($docId)){
            $qrow = $db->select("*")
                        ->from("quotation")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

            if(empty($qrow)) return $this->data;

            $this->data["doc_id"] = $docId;
            $this->data["doc_number"] = $qrow->doc_number;
            $this->data["doc_date"] = $qrow->doc_date;
            $this->data["credit"] = $qrow->credit;
            $this->data["doc_valid_until_date"] = $qrow->doc_valid_until_date;
            $this->data["reference_number"] = $qrow->reference_number;
            $this->data["vat_inc"] = $qrow->vat_inc;
            $this->data["wht_inc"] = $qrow->wht_inc;
            $this->data["project_id"] = $qrow->project_id;
            $this->data["client_id"] = $qrow->client_id;
            $this->data["remark"] = $qrow->remark;
            $this->data["created_by"] = $qrow->created_by;
        }

        $this->data["status"] = "success";

        return $this->data;
    }

    function updateDoc(){
        $db = $this->db;
        $docId = isset($this->json->doc_id) ? $this->json->doc_id : null;
        $vat_inc = $this->json->vat_inc == true ? "Y":"N";
        $wht_inc = $this->json->wht_inc == true ? "Y":"N";


        $qrow = $db->select("*")
                    ->from("quotation")
                    ->where("id", $docId)
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($qrow)) return $this->data;

        $sub_total_before_discount = $db->select("SUM(total_price) AS SUB_TOTAL")
                                        ->from("quotation_items")
                                        ->where("quotation_id", $docId)
                                        ->get()->row()->SUB_TOTAL;

        if($sub_total_before_discount == null) $sub_total_before_discount = 0;

        $discount_type = $qrow->discount_type;
        $discount_percent = $qrow->discount_percent;
        $discount_amount = $qrow->discount_amount;

        if($discount_type == "P" && $discount_percent > 0){
            $discount_amount = ($sub_total_before_discount * $discount_percent)/100;
        }

        $sub_total = $sub_total_before_discount - $discount_amount;

        
        $vat_percent = 0;
        $vat_value = 0;
        if($vat_inc == "Y"){
            $vat_percent = $this->Taxes_m->getVatPercent();
            $vat_value = ($sub_total * $this->Taxes_m->getVatPercent())/100;
        }

        $total = $sub_total + $vat_value;

        $wht_percent = $qrow->wht_percent;
        $wht_value = 0;

        if($wht_inc == "Y"){
            $wht_value = ($total * $wht_percent) / 100;
        }

        $payment_amount = $total - $wht_value;

        $db->where("id", $docId);
        $db->update("quotation", [
                                    "sub_total_before_discount"=>$sub_total_before_discount,
                                    "discount_type"=>$discount_type,
                                    "discount_percent"=>$discount_percent,
                                    "discount_amount"=>$discount_amount,
                                    "sub_total"=>$sub_total,
                                    "vat_inc"=>$vat_inc,
                                    "vat_percent"=>$vat_percent,
                                    "vat_value"=>$vat_value,
                                    "total"=>$total,
                                    "wht_inc"=>$wht_inc,
                                    "wht_percent"=>$wht_percent,
                                    "wht_value"=>$wht_value,
                                    "payment_amount"=>$payment_amount
                                ]);

        return $this->data;
    }

    function summary(){
        $db = $this->db;
        $docId = isset($this->json->doc_id) ? $this->json->doc_id : null;
        
        $qrow = $db->select("*")
                    ->from("quotation")
                    ->where("id", $docId)
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($qrow)) return $this->data;

        $this->data["status"] = "success";
        $this->data["sub_total_before_discount"] = number_format($qrow->sub_total_before_discount, 2);
        $this->data["discount_percent"] = number_format($qrow->discount_percent, 2);
        $this->data["discount_amount"] = number_format($qrow->discount_amount, 2);
        $this->data["sub_total"] = number_format($qrow->sub_total, 2);
        $this->data["vat_inc"] = $qrow->vat_inc;
        $this->data["vat_value"] = number_format($qrow->vat_value, 2);
        $this->data["total"] = number_format($qrow->total, 2);
        $this->data["wht_inc"] = $qrow->wht_inc;
        $this->data["wht_value"] = number_format($qrow->wht_value, 2);
        $this->data["payment_amount"] = number_format($qrow->payment_amount, 2);
        $this->data["payment_amount_in_text"] = numberToText(0);

        return $this->data;
    }


    function validateDoc(){
        $_POST = json_decode(file_get_contents('php://input'), true);

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
        
        $this->validateDoc();
        if($this->data["status"] == "validate") return $this->data;

        $docId = $this->json->doc_id;
        $doc_date = converDate($this->json->quotation_date);
        $credit = $this->json->credit;
        $doc_valid_until_date = converDate($this->json->quotation_valid_until_date);
        $reference_number = $this->json->reference_number;
        $vat_inc = $this->json->vat_inc;
        $client_id = $this->json->client_id;
        $project_id = $this->json->project_id;
        $remark = $this->json->remark;

        if($docId != ""){
            $db->where("id", $docId);
            $db->where("deleted", 0);
            $db->update("quotation", [
                                        "doc_date"=>$doc_date,
                                        "credit"=>$credit,
                                        "doc_valid_until_date"=>$doc_valid_until_date,
                                        "reference_number"=>$reference_number,
                                        "vat_inc"=>$vat_inc,
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
                                        "vat_inc"=>$vat_inc,
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
        $data["data"] = $this->getIGridData($qrow);
        $data["message"] = lang('record_undone');

        return $data;
    }

    function items(){
        $db = $this->db;
        
        $qrow = $db->select("id")
                        ->from("quotation")
                        ->where("id", $this->json->doc_id)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($qrow)) return $this->data;

        $qirows = $db->select("*")
                        ->from("quotation_items")
                        ->where("quotation_id", $this->json->doc_id)
                        ->order_by("id", "asc")
                        ->get()->result();

        if(empty($qirows)){
            $this->data["status"] = "notfound";
            $this->data["message"] = "ไม่พบข้อมูล";
            return $this->data;
        }

        $items = [];

        foreach($qirows as $qirow){
            $item["id"] = $qirow->id;
            $item["product_name"] = $qirow->product_name;
            $item["product_description"] = $qirow->product_description;
            $item["quantity"] = $qirow->quantity;
            $item["unit"] = $qirow->unit;
            $item["price"] = number_format($qirow->price, 2);
            $item["total_price"] = number_format($qirow->total_price, 2);

            $items[] = $item;
        }

        $this->data["items"] = $items;
        $this->data["status"] = "success";

        return $this->data;
    }

    function item(){
        $db = $this->db;
        $docId = $this->input->post("doc_id");
        $itemId = $this->input->post("item_id");

        $qrow = $db->select("id")
                        ->from("quotation")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($qrow)) return $this->data;

        $this->data["doc_id"] = $docId;
        $this->data["product_id"] = "";
        $this->data["product_name"] = "";
        $this->data["product_description"] = "";
        $this->data["quantity"] = "1.00";
        $this->data["unit"] = "";
        $this->data["price"] = "0.00";
        $this->data["total_price"] = "0.00";

        if(!empty($itemId)){
            $qirow = $db->select("*")
                        ->from("quotation_items")
                        ->where("id", $itemId)
                        ->where("quotation_id", $docId)
                        ->get()->row();

            if(empty($qirow)) return $this->data;

            $this->data["item_id"] = $qirow->id;
            $this->data["product_id"] = $qirow->product_id;
            $this->data["product_name"] = $qirow->product_name;
            $this->data["product_description"] = $qirow->product_description;
            $this->data["quantity"] = $qirow->quantity;
            $this->data["unit"] = $qirow->unit;
            $this->data["price"] = $qirow->price;
            $this->data["total_price"] = $qirow->total_price;
        }

        $this->data["status"] = "success";

        return $this->data;
    }

    function validateItem(){
        $_POST = json_decode(file_get_contents('php://input'), true);

        $this->form_validation->set_rules([
                                            [
                                                "field"=>"product_id",
                                                'label' => '',
                                                'rules' => 'required'
                                            ],
                                            [
                                                "field"=>"quantity",
                                                'label' => '',
                                                'rules' => 'required'
                                            ]
                                        ]);

        if ($this->form_validation->run() == FALSE){
            $this->data["status"] = "validate";
            if(form_error('product_id') != null) $this->data["messages"]["product_name"] = form_error('product_id');
            if(form_error('quantity') != null) $this->data["messages"]["quantity"] = form_error('quantity');
        }

    }

    function saveItem(){
        $db = $this->db;
        $docId = isset($this->json->doc_id)?$this->json->doc_id:null;

        $qrow = $db->select("id")
                    ->from("quotation")
                    ->where("id", $docId)
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($qrow)) return $this->data;
        
        $this->validateItem();
        if($this->data["status"] == "validate") return $this->data;



        $itemId = $this->json->item_id;
        $product_id = $this->json->product_id;
        $product_name = $this->json->product_name;
        $product_description = $this->json->product_description;
        $quantity = getNumber($this->json->quantity);
        $unit = $this->json->unit;
        $price = getNumber($this->json->price);
        $total_price = $price * $quantity;
        
        /*$vat_type = $this->json->vat_type;
        $price_inc_vat = $price = $rate * $quantity;

        if($vat_type == 2){
            $price = roundUp($price / $this->Taxes_m->getVat());
            $vat_value = $price_inc_vat - $price;
        }*/

        $fdata = [
                    "quotation_id"=>$docId,
                    "product_id"=>$product_id,
                    "product_name"=>$product_name,
                    "product_description"=>$product_description,
                    "quantity"=>$quantity,
                    "unit"=>$unit,
                    "price"=>$price,
                    "total_price"=>$total_price,
                ];

        $db->trans_begin();
        
        if(empty($itemId)){
            $db->where("quotation_id", $docId);
            $total_items = $db->count_all_results("quotation_items");
            $fdata["quotation_id"] = $docId;
            $fdata["sort"] = $total_items + 1;
            $db->insert("quotation_items", $fdata);
        }else{
            $db->where("id", $itemId);
            $db->where("quotation_id", $docId);
            $db->update("quotation_items", $fdata);
        }

        
        if ($db->trans_status() === FALSE){
            $db->trans_rollback();
        }else{
            $db->trans_commit();
        }

        $this->data["target"] = get_uri("quotations/view/".$docId);
        $this->data["status"] = "success";

        return $this->data;

    }

    function deleteItem(){
        $db = $this->db;
        
        $db->where("id", $this->json->item_id);
        $db->where("quotation_id", $this->json->doc_id);
        $db->delete("quotation_items");

        if($db->affected_rows() != 1) return $this->data;

        $this->data["status"] = "success";

        return $this->data;
    }

}
