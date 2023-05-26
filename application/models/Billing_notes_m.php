<?php

class Billing_notes_m extends MY_Model {
    private $code = "BL";

    function __construct() {
        parent::__construct();
    }

    function getCode(){
        return $this->code;
    }

    function getNewDocNumber(){
        $this->db->where("DATE_FORMAT(created_datetime,'%Y-%m')", date("Y-m"));
        $this->db->where("deleted", 0);
        $running_number = $this->db->get("billing_note")->num_rows() + 1;

        $doc_number = $this->getCode().date("Ym").sprintf("%04d", $running_number);

        return $doc_number;
    }

    function getStatusName($status_code){
        if($status_code == "W"){
            return "รออนุมัติ";
        }
    }

    function getIndexDataSetHTML($bnrow){
        $doc_status = "<select class='dropdown_status' data-doc_id='".$bnrow->id."'>";

        if($bnrow->status == "W"){
            $doc_status .= "<option selected>รอวางบิล</option>";
            $doc_status .= "<option value='A'>วางบิลแล้ว</option>";
            $doc_status .= "<option value='I'>เปิดบิลแล้ว</option>";
            $doc_status .= "<option value='CREATE_INVOICE'>สร้างใบกำกับภาษี</option>";
            $doc_status .= "<option value='V'>ยกเลิก</option>";
        }elseif($bnrow->status == "A"){
            $doc_status .= "<option selected>วางบิลแล้ว</option>";
            $doc_status .= "<option value='I'>เปิดบิลแล้ว</option>";
            $doc_status .= "<option value='CREATE_INVOICE'>สร้างใบกำกับภาษี</option>";
            $doc_status .= "<option value='V'>ยกเลิก</option>";
        }elseif($bnrow->status == "I"){
            $doc_status .= "<option selected>เปิดบิลแล้ว</option>";
            $doc_status .= "<option value='V'>ยกเลิก</option>";
        }else{
            $doc_status .= "<option selected>ยกเลิก</option>";
        }

        $doc_status .= "</select>";

        $data = [
                    "<a href='".get_uri("billing-notes/view/".$bnrow->id)."'>".convertDate($bnrow->doc_date, 2)."</a>",
                    "<a href='".get_uri("billing-notes/view/".$bnrow->id)."'>".$bnrow->doc_number."</a>",
                    "<a href='".get_uri("clients/view/".$bnrow->client_id)."'>".$this->Clients_m->getCompanyName($bnrow->client_id)."</a>",
                    convertDate($bnrow->due_date, true), number_format($bnrow->total, 2), $doc_status,
                    "<a data-post-id='".$bnrow->id."' data-action-url='".get_uri("billing-notes/addedit")."' data-act='ajax-modal' class='edit'><i class='fa fa-pencil'></i></a><a data-id='".$bnrow->id."' data-action-url='".get_uri("billing-notes/delete_doc")."' data-action='delete' class='delete'><i class='fa fa-times fa-fw'></i></a>"
                ];

        return $data;
    }

    function indexDataSet() {
        $db = $this->db;

        $db->select("*")->from("billing_note");

        if($this->input->post("status") != null){
            $db->where("status", $this->input->post("status"));
        }

        if($this->input->post("start_date") != null && $this->input->post("end_date")){
            $db->where("doc_date >=", $this->input->post("start_date"));
            $db->where("doc_date <=", $this->input->post("end_date"));
        }

        $db->where("deleted", 0);

        $bnrows = $db->order_by("doc_number", "desc")->get()->result();

        $dataset = [];

        foreach($bnrows as $bnrow){
            $dataset[] = $this->getIndexDataSetHTML($bnrow);
        }

        return $dataset;
    }

    function getDoc($docId){
        $db = $this->db;

        $this->data["doc_date"] = date("Y-m-d");
        $this->data["credit"] = "0";
        $this->data["due_date"] = date("Y-m-d");
        $this->data["reference_number"] = "";
        $this->data["discount_type"] = "P";
        $this->data["discount_percent"] = 0;
        $this->data["discount_amount"] = 0;
        $this->data["vat_inc"] = "N";
        $this->data["wht_inc"] = "N";
        $this->data["project_id"] = null;
        $this->data["client_id"] = null;
        $this->data["lead_id"] = null;
        $this->data["remark"] = null;
        $this->data["created_by"] = null;
        $this->data["created_datetime"] = null;
        $this->data["approved_by"] = null;
        $this->data["approved_datetime"] = null;
        $this->data["doc_status"] = NULL;

        if(!empty($docId)){
            $bnrow = $db->select("*")
                        ->from("billing_note")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

            if(empty($bnrow)) return $this->data;

            $lead_id = $client_id = null;
            
            if($this->Customers_m->isLead($bnrow->client_id) == true){
                $this->data["customer_id"] = $lead_id = $bnrow->client_id;
                $this->data["customer_is_lead"] = 1;
            }else{
                $this->data["customer_id"] = $client_id = $bnrow->client_id;
                $this->data["customer_is_lead"] = 0;
            }

            $this->data["doc_id"] = $docId;
            $this->data["doc_number"] = $bnrow->doc_number;
            $this->data["doc_date"] = $bnrow->doc_date;
            $this->data["credit"] = $bnrow->credit;
            $this->data["due_date"] = $bnrow->due_date;
            $this->data["reference_number"] = $bnrow->reference_number;
            $this->data["discount_type"] = $bnrow->discount_type;
            $this->data["discount_percent"] = $bnrow->discount_percent;
            $this->data["discount_amount"] = $bnrow->discount_amount;
            $this->data["vat_inc"] = $bnrow->vat_inc;
            $this->data["vat_percent"] = number_format_drop_zero_decimals($bnrow->vat_percent, 2)."%";
            $this->data["wht_inc"] = $bnrow->wht_inc;
            $this->data["project_id"] = $bnrow->project_id;
            $this->data["client_id"] = $client_id;
            $this->data["lead_id"] = $lead_id;
            $this->data["remark"] = $bnrow->remark;
            $this->data["created_by"] = $bnrow->created_by;
            $this->data["created_datetime"] = $bnrow->created_datetime;
            $this->data["approved_by"] = $bnrow->approved_by;
            $this->data["approved_datetime"] = $bnrow->approved_datetime;
            $this->data["doc_status"] = $bnrow->status;
        }

        $this->data["status"] = "success";

        return $this->data;
    }

    function updateDoc($docId = null){
        $db = $this->db;

        $discount_type = "P";
        $discount_percent = 0;
        $discount_amount = 0;

        $vat_inc = "N";
        $vat_percent = $this->Taxes_m->getVatPercent();
        $vat_value = 0;

        $wht_inc = "N";
        $wht_percent = $this->Taxes_m->getWhtPercent();
        $wht_value = 0;
        
        if($docId == null && isset($this->json->doc_id)){
            $docId = $this->json->doc_id;

            $vat_inc = $this->json->vat_inc == true ? "Y":"N";
            $wht_inc = $this->json->wht_inc == true ? "Y":"N";
            
            $bnrow = $db->select("*")
                        ->from("billing_note")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

            if(empty($bnrow)) return $this->data;

            $discount_type = $this->json->discount_type;

            if($discount_type == "P"){
                $discount_percent = getNumber($this->json->discount_percent);
                if($discount_percent >= 100) $discount_percent = 99.99;
                if($discount_percent < 0) $discount_percent = 0;
            }else{
                $discount_amount = getNumber($this->json->discount_value);
            }

            if($vat_inc == "Y") $vat_percent = $this->Taxes_m->getVatPercent();
            if($wht_inc == "Y") $wht_percent = getNumber($this->json->wht_percent);

        }else{
            $bnrow = $db->select("*")
                        ->from("billing_note")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

            if(empty($bnrow)) return $this->data;            

            $discount_type = $bnrow->discount_type;
            $discount_percent = $bnrow->discount_percent;
            $discount_amount = $bnrow->discount_amount;


            $vat_inc = $bnrow->vat_inc;
            $wht_inc = $bnrow->wht_inc;

            if($vat_inc == "Y") $vat_percent = $bnrow->vat_percent;
            if($wht_inc == "Y") $wht_percent = $bnrow->wht_percent;
        }
        
        $sub_total_before_discount = $db->select("SUM(total_price) AS SUB_TOTAL")
                                        ->from("billing_note_items")
                                        ->where("billing_note_id", $docId)
                                        ->get()->row()->SUB_TOTAL;

        if($sub_total_before_discount == null) $sub_total_before_discount = 0;
        if($discount_type == "P"){
            if($discount_percent > 0){
                $discount_amount = ($sub_total_before_discount * $discount_percent)/100;
            }
        }else{
            if($discount_amount > $sub_total_before_discount) $discount_amount = $sub_total_before_discount;
            if($discount_amount < 0) $discount_amount = 0;
        }



        $sub_total = $sub_total_before_discount - $discount_amount;

        if($vat_inc == "Y") $vat_value = ($sub_total * $vat_percent)/100;
        $total = $sub_total + $vat_value;

        if($wht_inc == "Y") $wht_value = ($sub_total * $wht_percent) / 100;
        $payment_amount = $total - $wht_value;

        $db->where("id", $docId);
        $db->update("billing_note", [
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

        $this->data["sub_total_before_discount"] = number_format($sub_total_before_discount, 2);
        $this->data["discount_type"] = $discount_type;
        $this->data["discount_percent"] = number_format($discount_percent, 2);
        $this->data["discount_amount"] = number_format($discount_amount, 2);
        $this->data["sub_total"] = number_format($sub_total, 2);
        $this->data["vat_inc"] = $vat_inc;
        $this->data["vat_percent"] = number_format_drop_zero_decimals($vat_percent, 2);
        $this->data["vat_value"] = number_format($vat_value, 2);
        $this->data["total"] = number_format($total, 2);
        $this->data["total_in_text"] = numberToText($total);
        $this->data["wht_inc"] = $wht_inc;
        $this->data["wht_percent"] = number_format_drop_zero_decimals($wht_percent, 2);
        $this->data["wht_value"] = number_format($wht_value, 2);
        $this->data["payment_amount"] = number_format($payment_amount, 2);
        $this->data["status"] = "success";
        $this->data["message"] = lang("record_saved");

        return $this->data;
    }

    function validateDoc(){
        $_POST = json_decode(file_get_contents('php://input'), true);

        $this->form_validation->set_rules([
                                            [
                                                "field"=>"doc_date",
                                                'label' => '',
                                                'rules' => 'required'
                                            ],
                                            [
                                                "field"=>"due_date",
                                                'label' => '',
                                                'rules' => 'required'
                                            ]
                                        ]);

        if ($this->form_validation->run() == FALSE){
            $this->data["status"] = "validate";
            if(form_error('doc_date') != null) $this->data["messages"]["doc_date"] = form_error('doc_date');
            if(form_error('due_date') != null) $this->data["messages"]["due_date"] = form_error('due_date');
        }
    }

    function saveDoc(){
        $db = $this->db;

        $this->validateDoc();
        if($this->data["status"] == "validate") return $this->data;

        $docId = $this->json->doc_id;
        $doc_date = convertDate($this->json->doc_date);
        $credit = $this->json->credit;
        $due_date = date('Y-m-d', strtotime($doc_date." + ".$credit." days"));
        $reference_number = $this->json->reference_number;
        $client_id = $this->json->client_id;
        $lead_id = $this->json->lead_id;
        $project_id = $this->json->project_id;
        $remark = $this->json->remark;

        if($client_id == "" && $lead_id == ""){
            $this->data["status"] = "validate";
            $this->data["messages"]["client_id"] = "โปรดใส่ข้อมูล";
            return $this->data;
        }

        $customer_id = null;
        if($client_id != "") $customer_id = $client_id;
        if($lead_id != "") $customer_id = $lead_id;

        if($docId != ""){
            $bnrow = $db->select("status")
                        ->from("billing_note")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

            if(empty($bnrow)){
                $this->data["success"] = false;
                $this->data["message"] = "ขออภัย เกิดข้อผิดพลาดระหว่างดำเนินการ! โปรดลองใหม่อีกครั้งในภายหลัง";
                return $this->data;
            }

            if($bnrow->status != "W"){
                $this->data["success"] = false;
                $this->data["message"] = "ไม่สามารถบันทึกเอกสารได้เนื่องจากเอกสารมีการเปลี่ยนแปลงสถานะแล้ว";
                return $this->data;
            }

            $db->where("id", $docId);
            $db->where("deleted", 0);
            $db->update("billing_note", [
                                        "doc_date"=>$doc_date,
                                        "credit"=>$credit,
                                        "due_date"=>$due_date,
                                        "reference_number"=>$reference_number,
                                        "client_id"=>$customer_id,
                                        "project_id"=>$project_id,
                                        "remark"=>$remark
                                    ]);
        }else{
            $doc_number = $this->getNewDocNumber();
            
            $db->insert("billing_note", [
                                        "doc_number"=>$doc_number,
                                        "doc_date"=>$doc_date,
                                        "credit"=>$credit,
                                        "due_date"=>$due_date,
                                        "reference_number"=>$reference_number,
                                        "vat_inc"=>"N",
                                        "client_id"=>$customer_id,
                                        "project_id"=>$project_id,
                                        "remark"=>$remark,
                                        "created_by"=>$this->login_user->id,
                                        "created_datetime"=>date("Y-m-d H:i:s"),
                                        "status"=>"W"
                                    ]);

            $docId = $db->insert_id();
        }
        
        $this->data["target"] = get_uri("billing-notes/view/". $docId);
        $this->data["status"] = "success";

        return $this->data;
    }

    function deleteDoc(){
        $db = $this->db;
        $docId = $this->input->post("id");

        $bnrow = $db->select("status")
                        ->from("billing_note")
                        ->where("id", $docId)
                        ->get()->row();

        if(empty($bnrow)) return $this->data;

        if($bnrow->status != "W"){
            $this->data["success"] = false;
            $this->data["message"] = "คุณไม่สามารถลบเอกสารได้ เนื่องจากเอกสารมีการเปลี่ยนแปลงสถานะแล้ว";
            return $this->data;
        }

        $db->where("id", $docId);
        $db->update("billing_note", ["deleted"=>1]);

        $this->data["success"] = true;
        $this->data["message"] = lang('record_deleted');

        return $this->data;
    }

    function undoDoc(){
        $db = $this->db;
        $docId = $this->input->post("id");

        $db->where("id", $docId);
        $db->update("billing_note", ["deleted"=>0]);

        $bnrow = $db->select("*")
                    ->from("billing_note")
                    ->where("id", $docId)
                    ->get()->row();

        $data["success"] = true;
        $data["data"] = $this->getIndexDataSetHTML($bnrow);
        $data["message"] = lang('record_undone');

        return $data;
    }

    function items(){
        $db = $this->db;
        
        $bnrow = $db->select("id, status")
                        ->from("billing_note")
                        ->where("id", $this->json->doc_id)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($bnrow)) return $this->data;

        $invirows = $db->select("*")
                        ->from("billing_note_items")
                        ->where("billing_note_id", $this->json->doc_id)
                        ->order_by("id", "asc")
                        ->get()->result();

        if(empty($invirows)){
            $this->data["status"] = "notfound";
            $this->data["message"] = "ไม่พบข้อมูล";
            return $this->data;
        }

        $items = [];

        foreach($invirows as $invirow){
            $item["id"] = $invirow->id;
            $item["product_name"] = $invirow->product_name;
            $item["product_description"] = $invirow->product_description;
            $item["quantity"] = $invirow->quantity;
            $item["unit"] = $invirow->unit;
            $item["price"] = number_format($invirow->price, 2);
            $item["total_price"] = number_format($invirow->total_price, 2);

            $items[] = $item;
        }

        $this->data["doc_status"] = $bnrow->status;
        $this->data["items"] = $items;
        $this->data["status"] = "success";

        return $this->data;
    }

    function item(){
        $db = $this->db;
        $docId = $this->input->post("doc_id");
        $itemId = $this->input->post("item_id");

        $bnrow = $db->select("id")
                        ->from("billing_note")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($bnrow)) return $this->data;

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
                        ->from("billing_note_items")
                        ->where("id", $itemId)
                        ->where("billing_note_id", $docId)
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

        $bnrow = $db->select("id")
                    ->from("billing_note")
                    ->where("id", $docId)
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($bnrow)) return $this->data;
      
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
                    "billing_note_id"=>$docId,
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
            $db->where("billing_note_id", $docId);
            $total_items = $db->count_all_results("billing_note_items");
            $fdata["billing_note_id"] = $docId;
            $fdata["sort"] = $total_items + 1;
            $db->insert("billing_note_items", $fdata);
        }else{
            $db->where("id", $itemId);
            $db->where("billing_note_id", $docId);
            $db->update("billing_note_items", $fdata);
        }

        
        if ($db->trans_status() === FALSE){
            $db->trans_rollback();
        }else{
            $db->trans_commit();
        }

        $this->updateDoc($docId);

        $this->data["target"] = get_uri("billing-notes/view/".$docId);
        $this->data["status"] = "success";

        return $this->data;

    }

    function deleteItem(){
        $db = $this->db;
        $docId = $this->json->doc_id;
        
        $db->where("id", $this->json->item_id);
        $db->where("billing_note_id", $docId);
        $db->delete("billing_note_items");

        if($db->affected_rows() != 1) return $this->data;

        $this->updateDoc($docId);

        $this->data["status"] = "success";

        return $this->data;
    }

    function updateStatus(){
        $db = $this->db;
        $docId = $this->json->doc_id;
        $updateStatusTo = $this->json->update_status_to;

        $bnrow = $db->select("*")
                    ->from("billing_note")
                    ->where("id",$docId)
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($bnrow)) return $this->data;
        if($bnrow->status == $updateStatusTo){
            $this->data["dataset"] = $this->getIndexDataSetHTML($bnrow);
            $this->data["message"] = "ไม่สามารถแก้ไขสถานะเอกสารได้ เนื่องจากเอกสารมีการเปลี่ยนแปลงสถานะแล้ว";
            return $this->data;
        }


        $billing_note_id = $this->data["doc_id"] = $docId;
        $billing_note_number = $bnrow->doc_number;
        $currentStatus = $bnrow->status;

        $this->db->trans_begin();

        if($updateStatusTo == "A"){
            if($currentStatus == "I" || $currentStatus == "V"){
                $this->data["dataset"] = $this->getIndexDataSetHTML($bnrow);
                return $this->data;
            }

            $db->where("id", $docId);
            $db->update("billing_note", [
                                        "approved_by"=>$this->login_user->id,
                                        "approved_datetime"=>date("Y-m-d H:i:s"),
                                        "status"=>"A"
                                    ]);

        }elseif($updateStatusTo == "I"){
            if($currentStatus == "V"){
                $this->data["dataset"] = $this->getIndexDataSetHTML($bnrow);
                return $this->data;
            }

            $db->where("id", $docId);
            $db->update("billing_note", [
                                        "approved_by"=>$this->login_user->id,
                                        "approved_datetime"=>date("Y-m-d H:i:s"),
                                        "status"=>"I"
                                    ]);

        }elseif($updateStatusTo == "CREATE_INVOICE"){
            if($currentStatus == "V"){
                $this->data["dataset"] = $this->getIndexDataSetHTML($bnrow);
                return $this->data;
            }

            $invrow = $db->select("doc_number")
                            ->from("invoice")
                            ->where("billing_note_id", $billing_note_id)
                            ->where("status !=", "V")
                            ->where("deleted", 0)
                            ->get()->row();

            if(!empty($invrow)){
                $db->trans_rollback();
                $this->data["dataset"] = $this->getIndexDataSetHTML($bnrow);
                $this->data["message"] = "ไม่สามารถสร้างใบกำกับภาษีได้ เนื่องจากมีการเปิดบิลที่ ".$invrow->doc_number." เรียบร้อยแล้ว";
                return $this->data;
            }


            $db->where("id", $docId);
            $db->update("billing_note", [
                                        "approved_by"=>$this->login_user->id,
                                        "approved_datetime"=>date("Y-m-d H:i:s"),
                                        "status"=>"I"
                                    ]);

            $invoice_number = $this->Invoices_m->getNewDocNumber();
            $invoice_date = date("Y-m-d");
            $invoice_credit = $bnrow->credit;
            $invoice_due_date = date("Y-m-d", strtotime($invoice_date. " + ".$invoice_credit." days"));

            $db->insert("invoice", [
                                        "billing_note_id"=>$billing_note_id,
                                        "doc_number"=>$invoice_number,
                                        "doc_date"=>$invoice_date,
                                        "credit"=>$invoice_credit,
                                        "due_date"=>$invoice_due_date,
                                        "reference_number"=>$billing_note_number,
                                        "project_id"=>$bnrow->project_id,
                                        "client_id"=>$bnrow->client_id,
                                        "sub_total_before_discount"=>$bnrow->sub_total_before_discount,
                                        "discount_type"=>$bnrow->discount_type,
                                        "discount_percent"=>$bnrow->discount_percent,
                                        "discount_amount"=>$bnrow->discount_amount,
                                        "sub_total"=>$bnrow->sub_total,
                                        "vat_inc"=>$bnrow->vat_inc,
                                        "vat_percent"=>$bnrow->vat_percent,
                                        "vat_value"=>$bnrow->vat_value,
                                        "total"=>$bnrow->total,
                                        "wht_inc"=>$bnrow->wht_inc,
                                        "wht_percent"=>$bnrow->wht_percent,
                                        "wht_value"=>$bnrow->wht_value,
                                        "payment_amount"=>$bnrow->payment_amount,
                                        "remark"=>$bnrow->remark,
                                        "created_by"=>$this->login_user->id,
                                        "created_datetime"=>date("Y-m-d H:i:s"),
                                        "status"=>"P",
                                        "deleted"=>0
                                    ]);

            $invoice_id = $db->insert_id();

            $bnirows = $db->select("*")
                        ->from("billing_note_items")
                        ->where("billing_note_id", $billing_note_id)
                        ->order_by("sort", "ASC")
                        ->get()->result();

            if(empty(!$bnirows)){
                foreach($bnirows as $bnirow){
                    $db->insert("invoice_items", [
                                                        "invoice_id"=>$invoice_id,
                                                        "product_id"=>$bnirow->product_id,
                                                        "product_name"=>$bnirow->product_name,
                                                        "product_description"=>$bnirow->product_description,
                                                        "quantity"=>$bnirow->quantity,
                                                        "unit"=>$bnirow->unit,
                                                        "price"=>$bnirow->price,
                                                        "total_price"=>$bnirow->total_price,
                                                        "sort"=>$bnirow->sort
                                                    ]);
                }
            }

            $this->data["task"] = "create_invoice";
            $this->data["status"] = "success";
            $this->data["url"] = get_uri("invoices/view/".$invoice_id);

        }elseif($updateStatusTo == "V"){
            $db->where("id", $docId);
            $db->update("billing_note", [
                                        "status"=>"V"
                                    ]);

        }

        if ($db->trans_status() === FALSE){
            $db->trans_rollback();
            $this->data["dataset"] = $this->getIndexDataSetHTML($bnrow);
            return $this->data;
        }

        $db->trans_commit();

        if(isset($this->data["task"])) return $this->data;

        $bnrow = $db->select("*")
                    ->from("billing_note")
                    ->where("id",$docId)
                    ->where("deleted", 0)
                    ->get()->row();

        $this->data["dataset"] = $this->getIndexDataSetHTML($bnrow);
        $this->data["status"] = "success";
        $this->data["message"] = lang('record_saved');
        return $this->data;
    }
}
