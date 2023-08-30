<?php

class Billing_notes_m extends MY_Model {
    private $code = "BN";
    private $shareHtmlAddress = "share/billing-note/html/";

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
        $doc_status = "<select class='dropdown_status' data-doc_id='".$bnrow->id."' data-post-id='".$bnrow->id."'>";

        if($bnrow->status == "W"){
            $doc_status .= "<option selected>รออนุมัติ</option>";
            $doc_status .= "<option value='A'>อนุมัติ</option>";
            $doc_status .= "<option value='V'>ยกเลิก</option>";
        }elseif($bnrow->status == "A"){
            $doc_status .= "<option selected>อนุมัติแล้ว</option>";
            $doc_status .= "<option value='V'>ยกเลิก</option>";
        }else{
            $doc_status .= "<option selected>ยกเลิก</option>";
        }

        $doc_status .= "</select>";

        $data = [
                    "<a href='".get_uri("billing-notes/view/".$bnrow->id)."'>".convertDate($bnrow->doc_date, 2)."</a>",
                    "<a href='".get_uri("billing-notes/view/".$bnrow->id)."'>".$bnrow->doc_number."</a>",
                    "<a href='".get_uri("clients/view/".$bnrow->client_id)."'>".$this->Clients_m->getCompanyName($bnrow->client_id)."</a>",
                    convertDate($bnrow->due_date, true), number_format($bnrow->total, 2), $doc_status
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

        if($this->input->post("client_id") != null){
            $db->where("client_id", $this->input->post("client_id"));
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

        $this->data["doc_id"] = null;
        $this->data["doc_number"] = null;
        $this->data["doc_date"] = date("Y-m-d");
        $this->data["due_date"] = date("Y-m-d");
        $this->data["reference_number"] = "";
        $this->data["client_id"] = null;
        $this->data["sub_total"] = 0;
        $this->data["vat_percent"] = 0;
        $this->data["vat_value"] = 0;
        $this->data["total"] = 0;
        $this->data["wht_value"] = 0;
        $this->data["payment_amount"] = 0;
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
            
            if($this->Customers_m->isLead($bnrow->client_id) == true){
                $this->data["customer_id"] = $lead_id = $bnrow->client_id;
                $this->data["customer_is_lead"] = 1;
            }else{
                $this->data["customer_id"] = $client_id = $bnrow->client_id;
                $this->data["customer_is_lead"] = 0;
            }

            $this->data["doc_id"] = $docId;
            $this->data["doc_number"] = $bnrow->doc_number;
            $this->data["share_link"] = $bnrow->sharekey != null ? get_uri($this->shareHtmlAddress."th/".$bnrow->sharekey) : null;
            $this->data["doc_date"] = $bnrow->doc_date;
            $this->data["due_date"] = $bnrow->due_date;
            $this->data["reference_number"] = $bnrow->reference_number;
            $this->data["client_id"] = $bnrow->client_id;
            $this->data["sub_total"] = $bnrow->sub_total;
            $this->data["vat_percent"] = number_format_drop_zero_decimals($bnrow->vat_percent, 2);
            $this->data["vat_value"] = $bnrow->vat_value;
            $this->data["total"] = $bnrow->total;
            $this->data["wht_value"] = $bnrow->wht_value;
            $this->data["payment_amount"] = $bnrow->payment_amount;
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

    function getEdoc($docId = null, $sharekey = null){
        $db = $this->db;
        $ci = get_instance();

        if($docId != null && $sharekey == null){
            $docId = base64_decode($docId);
            list($docId, $docNumber) = explode(":", $docId);
            $db->where("id", $docId);
            $db->where("doc_number", $docNumber);
        }elseif($docId == null && $sharekey != null){
            $db->where("sharekey", $sharekey);
        }else{
            return $this->data;
        }

        $db->where("deleted", 0);

        $bnrow = $db->select("*")
                    ->from("billing_note")
                    ->get()->row();

        if(empty($bnrow)) return $this->data;

        $docId = $bnrow->id;

        $bnrows = $db->select("*")
                        ->from("billing_note_items")
                        ->where("billing_note_id", $docId)
                        ->order_by("sort", "asc")
                        ->get()->result();

        $client_id = $bnrow->client_id;
        $created_by = $bnrow->created_by;

        $this->data["seller"] = $ci->Users_m->getInfo($created_by);

        $this->data["buyer"] = $ci->Customers_m->getInfo($client_id);
        $this->data["buyer_contact"] = $ci->Customers_m->getContactInfo($client_id);

        $this->data["doc_number"] = $bnrow->doc_number;
        $this->data["doc_date"] = $bnrow->doc_date;
        $this->data["due_date"] = $bnrow->due_date;
        $this->data["reference_number"] = $bnrow->reference_number;
        $this->data["remark"] = $bnrow->remark;
        
        $this->data["sub_total"] = $bnrow->sub_total;

        $this->data["vat_percent"] = $bnrow->vat_percent;
        $this->data["vat_value"] = $bnrow->vat_value;
        $this->data["total"] = $bnrow->total;
        $this->data["total_in_text"] = numberToText($bnrow->total);
        $this->data["wht_value"] = $bnrow->wht_value;
        $this->data["payment_amount"] = $bnrow->payment_amount;

        $this->data["sharekey_by"] = $bnrow->sharekey_by;
        $this->data["approved_by"] = $bnrow->approved_by;
        $this->data["approved_datetime"] = $bnrow->approved_datetime;
        $this->data["doc_status"] = $bnrow->status;

        $this->data["doc"] = $bnrow;
        $this->data["items"] = $bnrows;

        $this->data["status"] = "success";
        $this->data["message"] = "ok";

        return $this->data;
    }

    function updateDoc($docId = null){
        $db = $this->db;

        /*$bnrow = null;

        $total = 0;
        $payment_amount = 0;

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

            if($vat_inc == "Y") $vat_percent = $this->Taxes_m->getVatPercent();
            if($wht_inc == "Y") $wht_percent = getNumber($this->json->wht_percent);

        }else{
            $bnrow = $db->select("*")
                        ->from("billing_note")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

            if(empty($bnrow)) return $this->data;            

            


            $vat_inc = $bnrow->vat_inc;
            $wht_inc = $bnrow->wht_inc;

            if($vat_inc == "Y") $vat_percent = $bnrow->vat_percent;
            if($wht_inc == "Y") $wht_percent = $bnrow->wht_percent;
        }
        
        $billed_amount = 0;


        $db->where("id", $docId);
        $db->update("billing_note", [
                                    "sub_total"=>0,
                                    "vat_percent"=>$vat_percent,
                                    "vat_value"=>$vat_value,
                                    "total"=>$total,
                                    "wht_value"=>$wht_value,
                                    "payment_amount"=>$payment_amount
                                ]);*/
        $docId = $this->json->doc_id;

        $bnrow = $db->select("*")
                        ->from("billing_note")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

        $this->data["sub_total"] = number_format(0, 2);
        $this->data["vat_percent"] = number_format_drop_zero_decimals($bnrow->vat_percent, 2);
        $this->data["vat_value"] = number_format($bnrow->vat_value, 2);
        $this->data["total"] = number_format($bnrow->total, 2);
        $this->data["total_in_text"] = numberToText($bnrow->total);
        $this->data["wht_value"] = number_format($bnrow->wht_value, 2);
        $this->data["payment_amount"] = number_format($bnrow->payment_amount, 2);
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
                        ->where("deleted", 0)
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
        
        $bnrow = $db->select("*")
                        ->from("billing_note")
                        ->where("id", $this->json->doc_id)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($bnrow)) return $this->data;

        $bnirows = $db->select("*")
                        ->from("billing_note_items")
                        ->where("billing_note_id", $this->json->doc_id)
                        ->order_by("id", "asc")
                        ->get()->result();

        if(empty($bnirows)){
            $this->data["status"] = "notfound";
            $this->data["message"] = "ไม่พบข้อมูล";
            return $this->data;
        }

        $items = [];

        foreach($bnirows as $bnirow){
            $item["id"] = $bnirow->id;
            $item["item_id"] = $bnirow->id;
            $item["invoice_number"] = $bnirow->invoice_number;
            $item["invoice_date"] = convertDate($bnirow->invoice_date, true);
            $item["invoice_due_date"] = convertDate($bnirow->invoice_due_date, true);
            $item["net_total"] = $bnirow->net_total;
            $item["billing_amount"] = $bnirow->billing_amount;
            $item["wht_value"] = ($bnirow->wht_inc == "Y"?$bnirow->wht_value:"ยังไม่ระบุ");
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

        $bnrow = $db->select("id, quotation_id")
                        ->from("billing_note")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($bnrow)) return $this->data;

        $this->data["doc_id"] = $docId;
        $this->data["quotation_id"] = $bnrow->quotation_id;
        $this->data["product_id"] = "";
        $this->data["product_name"] = "";
        $this->data["product_description"] = "";
        $this->data["quantity"] = number_format(1, $this->Settings_m->getDecimalPlacesNumber());
        $this->data["unit"] = "";
        $this->data["price"] = number_format(0, 2);
        $this->data["total_price"] = number_format(0, 2);

        if(!empty($itemId)){
            $bnirow = $db->select("*")
                        ->from("billing_note_items")
                        ->where("id", $itemId)
                        ->where("billing_note_id", $docId)
                        ->get()->row();

            if(empty($bnirow)) return $this->data;

            $this->data["item_id"] = $bnirow->id;
            $this->data["invoice_number"] = $bnirow->invoice_number;
            $this->data["invoice_date"] = $bnirow->invoice_date;
            $this->data["invoice_due_date"] = $bnirow->invoice_due_date;
            $this->data["net_total"] = $bnirow->net_total;
            $this->data["billing_amount"] = $bnirow->billing_amount;
            $this->data["wht_value"] = $bnirow->wht_value;
            
        }

        $this->data["status"] = "success";

        return $this->data;
    }

    function validateItem(){
        $_POST = json_decode(file_get_contents('php://input'), true);

        $this->form_validation->set_rules([
                                            [
                                                "field"=>"quantity",
                                                'label' => '',
                                                'rules' => 'required'
                                            ]
                                        ]);

        if ($this->form_validation->run() == FALSE){
            $this->data["status"] = "validate";
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
        $product_id = $this->json->product_id == ""?null:$this->json->product_id;
        $product_name = $this->json->product_name;
        $product_description = $this->json->product_description;
        $quantity = round(getNumber($this->json->quantity), $this->Settings_m->getDecimalPlacesNumber());
        $unit = $this->json->unit;
        $price = round(getNumber($this->json->price), 2);
        $total_price = round($price * $quantity, 2);

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

        $db->trans_begin();

        if($updateStatusTo == "A"){
            if($currentStatus == "V"){
                $this->data["dataset"] = $this->getIndexDataSetHTML($bnrow);
                return $this->data;
            }

            $db->where("id", $docId);
            $db->where("deleted", 0);
            $db->update("billing_note", [
                                        "approved_by"=>$this->login_user->id,
                                        "approved_datetime"=>date("Y-m-d H:i:s"),
                                        "status"=>"A"
                                    ]);

        }elseif($updateStatusTo == "V"){
            if($currentStatus == "V"){
                $this->data["dataset"] = $this->getIndexDataSetHTML($bnrow);
                return $this->data;
            }

            $db->where("id", $docId);
            $db->where("deleted", 0);
            $db->update("billing_note", ["status"=>"V"]);

        }

        if ($db->trans_status() === FALSE){
            $db->trans_rollback();
            $this->data["dataset"] = $this->getIndexDataSetHTML($bnrow);
            return $this->data;
        }

        $db->trans_commit();

        $this->data["status"] = "success";
        $this->data["message"] = lang("record_saved");

        if(isset($this->data["task"])) return $this->data;
 
        $bnrow = $db->select("*")
                    ->from("billing_note")
                    ->where("id",$docId)
                    ->where("deleted", 0)
                    ->get()->row();

        $this->data["dataset"] = $this->getIndexDataSetHTML($bnrow);
        return $this->data;
    }

    function genShareKey(){
        $db = $this->db;
        $docId = $this->json->doc_id;
        $genKey = $this->json->gen_key;
        
        $sharekey = null;
        $sharekey_by = null;

        if($genKey == true){
            $sharekey = "";
            $sharekey_by = $this->login_user->id;

            while(true){
                $sharekey = uniqid();
                $db->where("sharekey", $sharekey);
                if($db->count_all_results("billing_note") < 1) break;
            }

            $this->data["sharelink"] = get_uri($this->shareHtmlAddress."th/".$sharekey);
        }

        $db->where("id", $docId);
        $db->update("billing_note", ["sharekey"=>$sharekey, "sharekey_by"=>$sharekey_by]);

        return $this->data;
    }

    function createDocByInvoiceIds($invoice_ids){
        $db = $this->db;
        $company_setting = $this->Settings_m->getCompany();
        $billing_type = $company_setting["company_billing_type"];

        if(count($invoice_ids) < 1){
            $this->data["message"] = "กรุณาเลือกใบแจ้งหนี้ที่ต้องการนำมาออกใบวางบิล.";
            return $this->data;
        }

        $db->trans_begin();

        $doc_number = $this->getNewDocNumber();

        $db->insert("billing_note", [
                                        "billing_type"=>$billing_type,
                                        "doc_number"=>$doc_number,
                                        "doc_date"=>date("Y-m-d"),
                                        "due_date"=>date("Y-m-d", strtotime(date("Y-m-d") . "+7 days")),
                                        "created_by"=>$this->login_user->id,
                                        "created_datetime"=>date("Y-m-d H:i:s"),
                                        "status"=>"W",
                                        "deleted"=>0
                                    ]);

        $billing_id = $db->insert_id();

        $billing_client_id = 0;
        $billing_sub_total = 0;
        $billing_vat_value = 0;
        $billing_total = 0;
        $billing_wht_value = 0;
        $billing_payment_amount = 0;

        $bisort = 0;

        foreach($invoice_ids as $invoice_id){
            $ivrow = $db->select("*")
                        ->from("invoice")
                        ->where("id", $invoice_id)
                        ->where("billing_type", $billing_type)
                        ->where("status", "O")
                        ->get()->row();

            if(empty($ivrow)){
                $db->trans_rollback();
                return $this->data;
            }

            $billing_client_id = $ivrow->client_id;
            $invoice_number = $ivrow->doc_number;
            $invoice_date = $ivrow->doc_date;
            $invoice_due_date = $ivrow->due_date;

            $payment_amount = $db->select("SUM(payment_amount) AS PAYMENT_AMOUNT")
                                        ->from("invoice_payment")
                                        ->get()->row()->PAYMENT_AMOUNT;

            if($payment_amount == null) $payment_amount = 0;

            $net_total = $ivrow->total;
            $invoice_await_payment_amount = $net_total - $payment_amount;

            $invoice_vat_inc = $ivrow->vat_inc;
            $invoice_vat_percent = $ivrow->vat_percent;
            $invoice_vat_value = 0;
            if($invoice_vat_inc == "Y") $invoice_vat_value = $invoice_await_payment_amount - ($invoice_await_payment_amount/(1 + $invoice_vat_percent/100));
            $invoice_await_payment_amount_exclude_vat = $invoice_await_payment_amount - $invoice_vat_value;
            $invoice_wht_inc = $ivrow->wht_inc;
            $invoice_wht_percent = $ivrow->wht_percent;
            $invoice_wht_value = 0;
            if($invoice_wht_inc == "Y") $invoice_wht_value = ($invoice_await_payment_amount_exclude_vat * $invoice_wht_percent)/100;
            $invoice_payment_amount = $invoice_await_payment_amount - $invoice_wht_value;

            $billing_sub_total = $billing_sub_total + $invoice_await_payment_amount_exclude_vat;
            $billing_vat_value = $billing_vat_value + $invoice_vat_value;
            $billing_total = $billing_total + $invoice_await_payment_amount;
            $billing_wht_value = $billing_wht_value + $invoice_wht_value;
            $billing_payment_amount = $billing_payment_amount + $invoice_payment_amount;

            $db->insert("billing_note_items", [
                                                "billing_note_id"=>$billing_id,
                                                "invoice_id"=>$invoice_id,
                                                "invoice_number"=>$invoice_number,
                                                "invoice_date"=>$invoice_date,
                                                "invoice_due_date"=>$invoice_due_date,
                                                "net_total"=>$net_total,
                                                "billing_amount"=>$invoice_await_payment_amount,
                                                "wht_inc"=>$invoice_wht_inc,
                                                "wht_percent"=>$invoice_wht_percent,
                                                "wht_value"=>$invoice_wht_value,
                                                "sort"=>++$bisort
                                            ]);

        }


        $db->update("billing_note", [
                                        "client_id"=>$billing_client_id,
                                        "sub_total"=>$billing_sub_total,
                                        "vat_percent"=>($billing_vat_value > 0?$this->Taxes_m->getVatPercent():0),
                                        "vat_value"=>$billing_vat_value,
                                        "total"=>$billing_total,
                                        "wht_value"=>$billing_wht_value,
                                        "payment_amount"=>$billing_payment_amount
                                    ]);

        if ($db->trans_status() === FALSE){
            $db->trans_rollback();
            return $this->data;
        }

        $db->trans_commit();
        $this->data["url"] = get_uri("billing-notes/view/".$billing_id);
        $this->data["status"] = "success";
        $this->data["message"] = "OK";
        return $this->data;
    }

    function getHTMLInvoices() {
        $db = $this->db;
        $customer_id = $this->json->customer_id;
        $company_setting = $this->Settings_m->getCompany();
        $billing_type = $company_setting["company_billing_type"];

        if(!isset($customer_id)) return ["html"=>"<tr class='norecord'><td colspan='8'>กรุณาเลือกชื่อลูกค้า เพื่อค้นหาเอกสาร</td></tr>"];
        if($customer_id == "") return ["html"=>"<tr class='norecord'><td colspan='8'>กรุณาเลือกชื่อลูกค้า เพื่อค้นหาเอกสาร</td></tr>"];

        $ivrows = $db->select("*")
                        ->from("invoice")
                        ->where("status", "O")
                        ->where("billing_type", $billing_type)
                        ->where("client_id", $customer_id)
                        ->where("deleted", 0)
                        ->order_by("doc_number", "desc")
                        ->get()->result();

        $html = "<tr class='norecord'><td colspan='8'>ไม่พบข้อมูลใบแจ้งหนี้</td></tr>";

        if(!empty($ivrows)){
            $html = "";
            foreach($ivrows as $ivrow){
                $payment_amount = $db->select("SUM(payment_amount) AS PAYMENT_AMOUNT")
                                        ->from("invoice_payment")
                                        ->where("invoice_id", $ivrow->id)
                                        ->get()->row()->PAYMENT_AMOUNT;

                if($payment_amount == null) $payment_amount = 0;

                /*$money_payment_receive = $db->select("SUM(money_payment_receive) AS MONEY_PAYMENT_RECEIPT")
                                            ->from("invoice_payment")
                                            ->where("invoice_id", $ivrow->id)
                                            ->get()->row()->MONEY_PAYMENT_RECEIPT;

                if($money_payment_receive == null) $money_payment_receive = 0;*/

                $html .= "<tr>";
                    $html .= "<td>".$ivrow->doc_number."</td>";
                    $html .= "<td>".convertDate($ivrow->doc_date, true)."</td>";
                    $html .= "<td>".convertDate($ivrow->due_date, true)."</td>";
                    $html .= "<td>".number_format($ivrow->total, 2)."</td>";
                    $html .= "<td>".number_format($ivrow->total - $payment_amount, 2)."</td>";
                    $html .= "<td>".($ivrow->wht_inc == 'Y'?$ivrow->wht_value:'ไม่ระบุ')."</td>";
                    $html .= "<td>".number_format($ivrow->total - $payment_amount, 2)."</td>";
                    $html .= "<td><input type='checkbox' name='invoice_numbers[]' value='".$ivrow->id."'></td>";
                $html .= "</tr>";
            }
        }

        $data["html"] = $html;

        return $data;
    }
}