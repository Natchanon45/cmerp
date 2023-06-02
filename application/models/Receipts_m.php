<?php

class Receipts_m extends MY_Model {
    private $code = "RE";

    function __construct() {
        parent::__construct();
    }

    function getCode(){
        return $this->code;
    }

    function getNewDocNumber(){
        $this->db->where("DATE_FORMAT(created_datetime,'%Y-%m')", date("Y-m"));
        $this->db->where("deleted", 0);
        $running_number = $this->db->get("receipt")->num_rows() + 1;

        $doc_number = $this->getCode().date("Ym").sprintf("%04d", $running_number);

        return $doc_number;
    }

    function getStatusName($status_code){
        if($status_code == "W"){
            return "รออนุมัติ";
        }
    }

    function getIndexDataSetHTML($rerow){
        $doc_status = "<select class='dropdown_status' data-doc_id='".$rerow->id."'>";

        if($rerow->status == "W"){
            $doc_status .= "<option selected>รอดำเนินการ</option>";
            $doc_status .= "<option value='P'>เก็บเงิน</option>";
            $doc_status .= "<option value='V'>ยกเลิก</option>";
        }elseif($rerow->status == "P"){
            $doc_status .= "<option selected>เก็บเงินแล้ว</option>";
        }elseif($rerow->status == "V"){
            $doc_status .= "<option selected>ยกเลิก</option>";
        }

        $doc_status .= "</select>";

        $data = [
                    "<a href='".get_uri("receipts/view/".$rerow->id)."'>".convertDate($rerow->doc_date, 2)."</a>",
                    "<a href='".get_uri("receipts/view/".$rerow->id)."'>".$rerow->doc_number."</a>",
                    $rerow->reference_number, "<a href='".get_uri("clients/view/".$rerow->client_id)."'>".$this->Clients_m->getCompanyName($rerow->client_id)."</a>",
                    number_format($rerow->total, 2), $doc_status,
                    "<a data-post-id='".$rerow->id."' data-action-url='".get_uri("receipts/addedit")."' data-act='ajax-modal' class='edit'><i class='fa fa-pencil'></i></a><a data-id='".$rerow->id."' data-action-url='".get_uri("receipts/delete_doc")."' data-action='delete' class='delete'><i class='fa fa-times fa-fw'></i></a>"
                ];

        return $data;
    }

    function indexDataSet() {
        $db = $this->db;

        $db->select("*")->from("receipt");

        if($this->input->post("status") != null){
            $db->where("status", $this->input->post("status"));
        }

        if($this->input->post("start_date") != null && $this->input->post("end_date")){
            $db->where("doc_date >=", $this->input->post("start_date"));
            $db->where("doc_date <=", $this->input->post("end_date"));
        }

        if($this->input->post("client_id") != null){
            $db->where("client_id", $this->input->post("client_id"));
        }

        $db->where("deleted", 0);

        $rerows = $db->order_by("doc_number", "desc")->get()->result();

        $dataset = [];

        foreach($rerows as $rerow){
            $dataset[] = $this->getIndexDataSetHTML($rerow);
        }

        return $dataset;
    }

    function getDoc($docId){
        $db = $this->db;

        $this->data["invoice_id"] = null;
        $this->data["doc_date"] = date("Y-m-d");
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
            $rerow = $db->select("*")
                        ->from("receipt")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

            if(empty($rerow)) return $this->data;

            $lead_id = $client_id = null;
            
            if($this->Customers_m->isLead($rerow->client_id) == true){
                $this->data["customer_id"] = $lead_id = $rerow->client_id;
                $this->data["customer_is_lead"] = 1;
            }else{
                $this->data["customer_id"] = $client_id = $rerow->client_id;
                $this->data["customer_is_lead"] = 0;
            }

            $this->data["invoice_id"] = $rerow->invoice_id;
            $this->data["doc_id"] = $docId;
            $this->data["doc_number"] = $rerow->doc_number;
            $this->data["doc_date"] = $rerow->doc_date;
            $this->data["reference_number"] = $rerow->reference_number;
            $this->data["discount_type"] = $rerow->discount_type;
            $this->data["discount_percent"] = $rerow->discount_percent;
            $this->data["discount_amount"] = $rerow->discount_amount;
            $this->data["vat_inc"] = $rerow->vat_inc;
            $this->data["vat_percent"] = number_format_drop_zero_decimals($rerow->vat_percent, 2)."%";
            $this->data["wht_inc"] = $rerow->wht_inc;
            $this->data["project_id"] = $rerow->project_id;
            $this->data["client_id"] = $client_id;
            $this->data["lead_id"] = $lead_id;
            $this->data["remark"] = $rerow->remark;
            $this->data["created_by"] = $rerow->created_by;
            $this->data["created_datetime"] = $rerow->created_datetime;
            $this->data["approved_by"] = $rerow->approved_by;
            $this->data["approved_datetime"] = $rerow->approved_datetime;
            $this->data["doc_status"] = $rerow->status;
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
            
            $rerow = $db->select("*")
                        ->from("receipt")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

            if(empty($rerow)) return $this->data;

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
            $rerow = $db->select("*")
                        ->from("receipt")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

            if(empty($rerow)) return $this->data;            

            $discount_type = $rerow->discount_type;
            $discount_percent = $rerow->discount_percent;
            $discount_amount = $rerow->discount_amount;


            $vat_inc = $rerow->vat_inc;
            $wht_inc = $rerow->wht_inc;

            if($vat_inc == "Y") $vat_percent = $rerow->vat_percent;
            if($wht_inc == "Y") $wht_percent = $rerow->wht_percent;
        }
        
        $sub_total_before_discount = $db->select("SUM(total_price) AS SUB_TOTAL")
                                        ->from("receipt_items")
                                        ->where("receipt_id", $docId)
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
        $db->update("receipt", [
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
                                            ]
                                        ]);

        if ($this->form_validation->run() == FALSE){
            $this->data["status"] = "validate";
            if(form_error('doc_date') != null) $this->data["messages"]["doc_date"] = form_error('doc_date');
        }
    }

    function saveDoc(){
        $db = $this->db;

        $this->validateDoc();
        if($this->data["status"] == "validate") return $this->data;

        $docId = $this->json->doc_id;
        $doc_date = convertDate($this->json->doc_date);
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
            $rerow = $db->select("status")
                        ->from("receipt")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

            if(empty($rerow)){
                $this->data["success"] = false;
                $this->data["message"] = "ขออภัย เกิดข้อผิดพลาดระหว่างดำเนินการ! โปรดลองใหม่อีกครั้งในภายหลัง";
                return $this->data;
            }

            if($rerow->status != "W"){
                $this->data["success"] = false;
                $this->data["message"] = "ไม่สามารถบันทึกเอกสารได้เนื่องจากเอกสารมีการเปลี่ยนแปลงสถานะแล้ว";
                return $this->data;
            }

            $db->where("id", $docId);
            $db->where("deleted", 0);
            $db->update("receipt", [
                                        "doc_date"=>$doc_date,
                                        "reference_number"=>$reference_number,
                                        "client_id"=>$customer_id,
                                        "project_id"=>$project_id,
                                        "remark"=>$remark
                                    ]);
        }else{
            $doc_number = $this->getNewDocNumber();

            $db->insert("receipt", [
                                        "doc_number"=>$doc_number,
                                        "doc_date"=>$doc_date,
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
        
        $this->data["target"] = get_uri("receipts/view/". $docId);
        $this->data["status"] = "success";

        return $this->data;
    }

    function deleteDoc(){
        $db = $this->db;
        $docId = $this->input->post("id");

        $rerow = $db->select("status")
                        ->from("receipt")
                        ->where("id", $docId)
                        ->get()->row();

        if(empty($rerow)) return $this->data;

        if($rerow->status != "W"){
            $this->data["success"] = false;
            $this->data["message"] = "คุณไม่สามารถลบเอกสารได้ เนื่องจากเอกสารมีการเปลี่ยนแปลงสถานะแล้ว";
            return $this->data;
        }

        $db->where("id", $docId);
        $db->update("receipt", ["deleted"=>1]);

        $this->data["success"] = true;
        $this->data["message"] = lang('record_deleted');

        return $this->data;
    }

    function undoDoc(){
        $db = $this->db;
        $docId = $this->input->post("id");

        $db->where("id", $docId);
        $db->update("receipt", ["deleted"=>0]);

        $rerow = $db->select("*")
                    ->from("receipt")
                    ->where("id", $docId)
                    ->get()->row();

        $data["success"] = true;
        $data["data"] = $this->getIndexDataSetHTML($rerow);
        $data["message"] = lang('record_undone');

        return $data;
    }

    function items(){
        $db = $this->db;
        $this->data["edit"] = false;
        
        $rerow = $db->select("id, invoice_id, status")
                        ->from("receipt")
                        ->where("id", $this->json->doc_id)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($rerow)) return $this->data;

        $invirows = $db->select("*")
                        ->from("receipt_items")
                        ->where("receipt_id", $this->json->doc_id)
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

        if($rerow->invoice_id == null && $rerow->status == "W"){
            $this->data["edit"] = true;
        }

        $this->data["items"] = $items;
        $this->data["status"] = "success";

        return $this->data;
    }

    function item(){
        $db = $this->db;
        $docId = $this->input->post("doc_id");
        $itemId = $this->input->post("item_id");

        $rerow = $db->select("id")
                        ->from("receipt")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($rerow)) return $this->data;

        $this->data["doc_id"] = $docId;
        $this->data["product_id"] = "";
        $this->data["product_name"] = "";
        $this->data["product_description"] = "";
        $this->data["quantity"] = number_format(1, $this->Settings_m->getDecimalPlacesNumber());
        $this->data["unit"] = "";
        $this->data["price"] = number_format(0, 2);
        $this->data["total_price"] = number_format(0, 2);

        if(!empty($itemId)){
            $qirow = $db->select("*")
                        ->from("receipt_items")
                        ->where("id", $itemId)
                        ->where("receipt_id", $docId)
                        ->get()->row();

            if(empty($qirow)) return $this->data;

            $this->data["item_id"] = $qirow->id;
            $this->data["product_id"] = $qirow->product_id;
            $this->data["product_name"] = $qirow->product_name;
            $this->data["product_description"] = $qirow->product_description;
            $this->data["quantity"] = number_format($qirow->quantity, $this->Settings_m->getDecimalPlacesNumber());
            $this->data["unit"] = $qirow->unit;
            $this->data["price"] = number_format($qirow->price, 2);
            $this->data["total_price"] = number_format($qirow->total_price, 2);
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

        $rerow = $db->select("id")
                    ->from("receipt")
                    ->where("id", $docId)
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($rerow)) return $this->data;
      
        $this->validateItem();
        if($this->data["status"] == "validate") return $this->data;

        $itemId = $this->json->item_id;
        $product_id = $this->json->product_id;
        $product_name = $this->json->product_name;
        $product_description = $this->json->product_description;
        $quantity = round(getNumber($this->json->quantity), $this->Settings_m->getDecimalPlacesNumber());
        $unit = $this->json->unit;
        $price = round(getNumber($this->json->price), 2);
        $total_price = round($price * $quantity, 2);

        $fdata = [
                    "receipt_id"=>$docId,
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
            $db->where("receipt_id", $docId);
            $total_items = $db->count_all_results("receipt_items");
            $fdata["receipt_id"] = $docId;
            $fdata["sort"] = $total_items + 1;
            $db->insert("receipt_items", $fdata);
        }else{
            $db->where("id", $itemId);
            $db->where("receipt_id", $docId);
            $db->update("receipt_items", $fdata);
        }

        
        if ($db->trans_status() === FALSE){
            $db->trans_rollback();
        }else{
            $db->trans_commit();
        }

        $this->updateDoc($docId);

        $this->data["target"] = get_uri("receipts/view/".$docId);
        $this->data["status"] = "success";

        return $this->data;

    }

    function deleteItem(){
        $db = $this->db;
        $docId = $this->json->doc_id;
        
        $db->where("id", $this->json->item_id);
        $db->where("receipt_id", $docId);
        $db->delete("receipt_items");

        if($db->affected_rows() != 1) return $this->data;

        $this->updateDoc($docId);

        $this->data["status"] = "success";

        return $this->data;
    }

    function updateStatus(){
        $db = $this->db;
        $docId = $this->json->doc_id;
        $updateStatusTo = $this->json->update_status_to;

        $rerow = $db->select("*")
                    ->from("receipt")
                    ->where("id",$docId)
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($rerow)) return $this->data;
        if($rerow->status == $updateStatusTo){
            $this->data["dataset"] = $this->getIndexDataSetHTML($rerow);
            $this->data["message"] = "ไม่สามารถแก้ไขสถานะเอกสารได้ เนื่องจากเอกสารมีการเปลี่ยนแปลงสถานะแล้ว";
            return $this->data;
        }

        $this->data["doc_id"] = $docId;
        $currentStatus = $rerow->status;

        $this->db->trans_begin();

        if($updateStatusTo == "P"){
            if($currentStatus == "V"){
                $this->data["dataset"] = $this->getIndexDataSetHTML($rerow);
                return $this->data;
            }
            
            $db->where("id", $docId);
            $db->update("receipt", [
                                        "approved_by"=>$this->login_user->id,
                                        "approved_datetime"=>date("Y-m-d H:i:s"),
                                        "status"=>"P"
                                    ]);

        }elseif($updateStatusTo == "V"){
            $db->where("id", $docId);
            $db->update("receipt", [
                                        "status"=>"V"
                                    ]);
        }

        if ($db->trans_status() === FALSE){
            $db->trans_rollback();
            $this->data["dataset"] = $this->getIndexDataSetHTML($rerow);
            return $this->data;
        }

        $db->trans_commit();

        if(isset($this->data["task"])) return $this->data;

        $rerow = $db->select("*")
                    ->from("receipt")
                    ->where("id",$docId)
                    ->where("deleted", 0)
                    ->get()->row();

        $this->data["dataset"] = $this->getIndexDataSetHTML($rerow);
        $this->data["status"] = "success";
        $this->data["message"] = lang('record_saved');
        return $this->data;
    }
}
