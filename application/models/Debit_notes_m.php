<?php

class Debit_notes_m extends MY_Model {
    private $code = "DN";
    private $shareHtmlAddress = "share/debit-note/html/";

    function __construct() {
        parent::__construct();
    }

    function getCode(){
        return $this->code;
    }

    function getNewDocNumber(){
        $this->db->where("DATE_FORMAT(created_datetime,'%Y-%m')", date("Y-m"));
        $this->db->where("deleted", 0);
        $running_number = $this->db->get("debit_note")->num_rows() + 1;

        $doc_number = $this->getCode().date("Ym").sprintf("%04d", $running_number);

        return $doc_number;
    }

    function getStatusName($status_code){
        if($status_code == "W"){
            return "รออนุมัติ";
        }
    }

    function getIndexDataSetHTML($dnrow){
        $doc_status = "<select class='dropdown_status' data-doc_id='".$dnrow->id."'>";

        if($dnrow->status == "W"){
            $doc_status .= "<option selected>รอดำเนินการ</option>";
            $doc_status .= "<option value='P'>เก็บเงินแล้ว</option>";
            //$doc_status .= "<option value='V'>ยกเลิก</option>";
        }elseif($dnrow->status == "P"){
            $doc_status .= "<option selected>เก็บเงินแล้ว</option>";
        }

        $doc_status .= "</select>";

        $reference_number_column = $dnrow->reference_number;
        if($dnrow->invoice_id != null){
            $reference_number_column = "<a href='".get_uri("invoices/view/".$dnrow->invoice_id)."'>".$dnrow->reference_number."</a>";
        }

        $data = [
                    "<a href='".get_uri("debit-notes/view/".$dnrow->id)."'>".convertDate($dnrow->doc_date, 2)."</a>",
                    "<a href='".get_uri("debit-notes/view/".$dnrow->id)."'>".$dnrow->doc_number."</a>",
                    $reference_number_column,
                    "<a href='".get_uri("clients/view/".$dnrow->client_id)."'>".$this->Clients_m->getCompanyName($dnrow->client_id)."</a>",
                    convertDate($dnrow->due_date, true), number_format($dnrow->total, 2), $doc_status,""
                ];

        return $data;
    }

    function indexDataSet() {
        $db = $this->db;

        $db->select("*")->from("debit_note");

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

        $dnrows = $db->order_by("doc_number", "desc")->get()->result();

        $dataset = [];

        foreach($dnrows as $dnrow){
            $dataset[] = $this->getIndexDataSetHTML($dnrow);
        }

        return $dataset;
    }

    function getDoc($docId = NULL){
        $db = $this->db;
        if($docId == null) return $this->data;

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
            $dnrow = $db->select("*")
                        ->from("debit_note")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

            if(empty($dnrow)) return $this->data;

            $lead_id = $client_id = null;
            
            if($this->Customers_m->isLead($dnrow->client_id) == true){
                $this->data["customer_id"] = $lead_id = $dnrow->client_id;
                $this->data["customer_is_lead"] = 1;
            }else{
                $this->data["customer_id"] = $client_id = $dnrow->client_id;
                $this->data["customer_is_lead"] = 0;
            }

            $this->data["doc_id"] = $docId;
            $this->data["doc_number"] = $dnrow->doc_number;
            $this->data["share_link"] = $dnrow->sharekey != null ? get_uri($this->shareHtmlAddress."th/".$dnrow->sharekey) : null;
            $this->data["doc_date"] = $dnrow->doc_date;
            $this->data["credit"] = $dnrow->credit;
            $this->data["due_date"] = $dnrow->due_date;
            $this->data["reference_number"] = $dnrow->reference_number;

            $this->data["original_amount"] = $dnrow->original_amount;
            $this->data["corrected_amount"] = $dnrow->corrected_amount;
            $this->data["difference"] = $dnrow->original_amount - $dnrow->corrected_amount;

            $this->data["discount_type"] = $dnrow->discount_type;
            $this->data["discount_percent"] = $dnrow->discount_percent;
            $this->data["discount_amount"] = $dnrow->discount_amount;
            $this->data["vat_inc"] = $dnrow->vat_inc;
            $this->data["vat_percent"] = number_format_drop_zero_decimals($dnrow->vat_percent, 2)."%";
            $this->data["wht_inc"] = $dnrow->wht_inc;
            $this->data["project_id"] = $dnrow->project_id;
            $this->data["client_id"] = $client_id;
            $this->data["lead_id"] = $lead_id;
            $this->data["remark"] = $dnrow->remark;
            $this->data["created_by"] = $dnrow->created_by;
            $this->data["created_datetime"] = $dnrow->created_datetime;
            $this->data["approved_by"] = $dnrow->approved_by;
            $this->data["approved_datetime"] = $dnrow->approved_datetime;
            $this->data["doc_status"] = $dnrow->status;
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

        $dnrow = $db->select("*")
                    ->from("debit_note")
                    ->get()->row();

        if(empty($dnrow)) return $this->data;

        $docId = $dnrow->id;

        $qirows = $db->select("*")
                        ->from("debit_note_items")
                        ->where("debit_note_id", $docId)
                        ->order_by("sort", "asc")
                        ->get()->result();

        $client_id = $dnrow->client_id;
        $created_by = $dnrow->created_by;

        $this->data["buyer"] = $ci->Customers_m->getInfo($client_id);
        $this->data["buyer_contact"] = $ci->Customers_m->getContactInfo($client_id);

        $this->data["doc_number"] = $dnrow->doc_number;
        $this->data["doc_date"] = $dnrow->doc_date;
        $this->data["credit"] = $dnrow->credit;
        $this->data["due_date"] = $dnrow->due_date;
        $this->data["reference_number"] = $dnrow->reference_number;
        $this->data["remark"] = $dnrow->remark;

        $this->data["original_amount"] = $dnrow->original_amount;
        $this->data["corrected_amount"] = $dnrow->corrected_amount;
        $this->data["difference"] = $dnrow->original_amount - $dnrow->corrected_amount;

        $this->data["sub_total_before_discount"] = $dnrow->sub_total_before_discount;

        $this->data["discount_type"] = $dnrow->discount_type;
        $this->data["discount_percent"] = $dnrow->discount_percent;
        $this->data["discount_amount"] = $dnrow->discount_amount;
        
        $this->data["sub_total"] = $dnrow->sub_total;

        $this->data["vat_inc"] = $dnrow->vat_inc;
        $this->data["vat_percent"] = $dnrow->vat_percent;
        $this->data["vat_value"] = $dnrow->vat_value;
        $this->data["total"] = $dnrow->total;
        $this->data["total_in_text"] = numberToText($dnrow->total);
        $this->data["wht_inc"] = $dnrow->wht_inc;
        $this->data["wht_percent"] = $dnrow->wht_percent;
        $this->data["wht_value"] = $dnrow->wht_value;
        $this->data["payment_amount"] = $dnrow->payment_amount;

        $this->data["sharekey_by"] = $dnrow->sharekey_by;
        $this->data["approved_by"] = $dnrow->approved_by;
        $this->data["approved_datetime"] = $dnrow->approved_datetime;
        $this->data["doc_status"] = $dnrow->status;

        $this->data["doc"] = $dnrow;
        $this->data["items"] = $qirows;

        $this->data["status"] = "success";
        $this->data["message"] = "ok";

        return $this->data;
    }

    function createDocByInvoiceId($invoice_id){
        $db = $this->db;

        $invrow = $db->select("*")
                        ->from("invoice")
                        ->where("id", $invoice_id)
                        ->where("deleted", 0)
                        ->where("status", "P")
                        ->get()->row();

        if(empty($invrow)) return $this->data;

        $invirows = $db->select("*")
                        ->from("invoice_items")
                        ->where("invoice_id", $invoice_id)
                        ->order_by("sort", "asc")
                        ->get()->result();

        $invoice_id = $invrow->id;
        $doc_number = $this->getNewDocNumber();
        $doc_date = date("Y-m-d");
        $credit = $invrow->credit;
        $due_date = $invrow->due_date;
        $reference_number = $invrow->doc_number;
        $project_id = $invrow->project_id;
        $client_id = $invrow->client_id;
        $original_amount = $invrow->sub_total;
        $corrected_amount = $invrow->sub_total;
        $sub_total_before_discount = $invrow->sub_total_before_discount;

        $discount_type = $invrow->discount_type;
        $discount_percent = $invrow->discount_percent;
        $discount_amount = $invrow->discount_amount;

        $sub_total = $invrow->sub_total;
        $vat_inc = $invrow->vat_inc;
        $vat_percent = $invrow->vat_percent;
        $vat_value = $invrow->vat_value;

        $total = $invrow->total;
        $wht_inc = $invrow->wht_inc;
        $wht_percent = $invrow->wht_percent;
        $wht_value = $invrow->wht_value;

        $payment_amount = $invrow->payment_amount;
        $created_by = $this->login_user->id;
        $created_datetime = date("Y-m-d H:i:s");
        $status = "W";

        $db->trans_begin();
        
        $db->insert("debit_note", [
                                    "invoice_id"=>$invoice_id,
                                    "doc_number"=>$doc_number,
                                    "doc_date"=>$doc_date,
                                    "credit"=>$credit,
                                    "due_date"=>$due_date,
                                    "reference_number"=>$reference_number,
                                    "project_id"=>$project_id,
                                    "client_id"=>$client_id,
                                    "original_amount"=>$original_amount,
                                    "corrected_amount"=>$corrected_amount,
                                    "sub_total_before_discount"=>$sub_total_before_discount,
                                    "discount_type"=>$discount_type,
                                    "discount_percent"=>$discount_amount,
                                    "discount_amount"=>$discount_amount,
                                    "sub_total"=>$sub_total,
                                    "vat_inc"=>$vat_inc,
                                    "vat_percent"=>$vat_percent,
                                    "vat_value"=>$vat_value,
                                    "total"=>$total,
                                    "wht_inc"=>$wht_inc,
                                    "wht_percent"=>$wht_percent,
                                    "wht_value"=>$wht_value,
                                    "payment_amount"=>$payment_amount,
                                    "created_by"=>$invoice_id,
                                    "created_datetime"=>$invoice_id,
                                    "status"=>$status,
                                    "deleted"=>0
                                ]);

        $debit_note_id = $db->insert_id();

        if(!empty($invirows)){
            foreach($invirows as $invirow){
                $db->insert("debit_note_items", [
                                                    "debit_note_id"=>$debit_note_id,
                                                    "product_id"=>$invirow->product_id,
                                                    "product_name"=>$invirow->product_name,
                                                    "product_description"=>$invirow->product_description,
                                                    "quantity"=>0,
                                                    "unit"=>$invirow->unit,
                                                    "price"=>0,
                                                    "total_price"=>0,
                                                    "sort"=>$invirow->sort
                                                ]);
            }
        }

        $this->data["doc_id"] = $debit_note_id;

        if ($db->trans_status() === FALSE){
            $db->trans_rollback();
            return $this->data;
        }

        $db->trans_commit();

        $this->data["url"] = get_uri("debit-notes/view/".$debit_note_id);
        $this->data["status"] = "success";
        $this->data["message"] = "success";

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

        $q = $db->select("*")->from("debit_note")->where("deleted", 0);
                        
        if($docId == null && isset($this->json->doc_id)){
            $docId = $this->json->doc_id;

            $dnrow = $q->where("id", $docId)->get()->row();
            if(empty($dnrow)) return $this->data;

            $vat_inc = $this->json->vat_inc == true ? "Y":"N";
            $wht_inc = $this->json->wht_inc == true ? "Y":"N";

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
            $dnrow = $q->where("id", $docId)->get()->row();
            if(empty($dnrow)) return $this->data;

            $discount_type = $dnrow->discount_type;
            $discount_percent = $dnrow->discount_percent;
            $discount_amount = $dnrow->discount_amount;

            $vat_inc = $dnrow->vat_inc;
            $wht_inc = $dnrow->wht_inc;

            if($vat_inc == "Y") $vat_percent = $dnrow->vat_percent;
            if($wht_inc == "Y") $wht_percent = $dnrow->wht_percent;
        }

        $sub_total_before_discount = $db->select("SUM(total_price) AS SUB_TOTAL")
                                        ->from("debit_note_items")
                                        ->where("debit_note_id", $docId)
                                        ->get()->row()->SUB_TOTAL;

        if($sub_total_before_discount == null) $sub_total_before_discount = 0;

        $original_amount = $dnrow->original_amount;
        $corrected_amount = $original_amount - $sub_total_before_discount;
        $difference = $original_amount - $corrected_amount;

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
        $db->update("debit_note", [
                                    "original_amount"=>$original_amount,
                                    "corrected_amount"=>$corrected_amount,
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

        $this->data["original_amount"] = number_format($original_amount, 2);
        $this->data["corrected_amount"] = number_format($corrected_amount, 2);
        $this->data["difference"] = number_format($difference, 2);

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

        //$this->validateDoc();
        //if($this->data["status"] == "validate") return $this->data;

        $docId = $this->json->doc_id;
        /*$doc_date = convertDate($this->json->doc_date);
        $credit = $this->json->credit;
        $due_date = date('Y-m-d', strtotime($doc_date." + ".$credit." days"));
        $reference_number = $this->json->reference_number;
        $client_id = $this->json->client_id;
        $lead_id = $this->json->lead_id;
        $project_id = $this->json->project_id;*/
        $remark = $this->json->remark;

        /*if($client_id == "" && $lead_id == ""){
            $this->data["status"] = "validate";
            $this->data["messages"]["client_id"] = "โปรดใส่ข้อมูล";
            return $this->data;
        }*/

        /*$customer_id = null;
        if($client_id != "") $customer_id = $client_id;
        if($lead_id != "") $customer_id = $lead_id;*/

        if($docId != ""){
            $dnrow = $db->select("status")
                        ->from("debit_note")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

            if(empty($dnrow)){
                $this->data["success"] = false;
                $this->data["message"] = "ขออภัย เกิดข้อผิดพลาดระหว่างดำเนินการ! โปรดลองใหม่อีกครั้งในภายหลัง";
                return $this->data;
            }

            $db->where("id", $docId);
            $db->where("deleted", 0);
            $db->update("debit_note", [
                                        "remark"=>$remark
                                    ]);
        }else{
            /*$doc_number = $this->getNewDocNumber();

            $db->insert("debit_note", [
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

            $docId = $db->insert_id();*/

            $this->data["success"] = false;
            $this->data["message"] = "ขออภัย เกิดข้อผิดพลาดระหว่างดำเนินการ! โปรดลองใหม่อีกครั้งในภายหลัง";
            return $this->data;            
        }
        
        $this->data["target"] = get_uri("debit-notes/view/". $docId);
        $this->data["status"] = "success";

        return $this->data;
    }

    function deleteDoc(){
        $db = $this->db;
        $docId = $this->input->post("id");

        $dnrow = $db->select("status")
                        ->from("debit_note")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($dnrow)) return $this->data;

        if($dnrow->status != "P"){
            $this->data["success"] = false;
            $this->data["message"] = "คุณไม่สามารถลบเอกสารได้ เนื่องจากเอกสารมีการเปลี่ยนแปลงสถานะแล้ว";
            return $this->data;
        }

        $db->where("id", $docId);
        $db->update("debit_note", ["deleted"=>1]);

        $this->data["success"] = true;
        $this->data["message"] = lang('record_deleted');

        return $this->data;
    }

    function undoDoc(){
        $db = $this->db;
        $docId = $this->input->post("id");

        $db->where("id", $docId);
        $db->update("debit_note", ["deleted"=>0]);

        $dnrow = $db->select("*")
                    ->from("debit_note")
                    ->where("id", $docId)
                    ->get()->row();

        $data["success"] = true;
        $data["data"] = $this->getIndexDataSetHTML($dnrow);
        $data["message"] = lang('record_undone');

        return $data;
    }

    function items(){
        $db = $this->db;
        
        $dnrow = $db->select("id, status")
                        ->from("debit_note")
                        ->where("id", $this->json->doc_id)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($dnrow)) return $this->data;

        $cnirows = $db->select("*")
                        ->from("debit_note_items")
                        ->where("debit_note_id", $this->json->doc_id)
                        ->order_by("id", "asc")
                        ->get()->result();

        if(empty($cnirows)){
            $this->data["status"] = "notfound";
            $this->data["message"] = "ไม่พบข้อมูล";
            return $this->data;
        }

        $items = [];

        foreach($cnirows as $cnirow){
            $item["id"] = $cnirow->id;
            $item["product_name"] = $cnirow->product_name;
            $item["product_description"] = $cnirow->product_description;
            $item["quantity"] = $cnirow->quantity;
            $item["unit"] = $cnirow->unit;
            $item["price"] = number_format($cnirow->price, 2);
            $item["total_price"] = number_format($cnirow->total_price, 2);

            $items[] = $item;
        }

        $this->data["doc_status"] = $dnrow->status;
        $this->data["items"] = $items;
        $this->data["status"] = "success";

        return $this->data;
    }

    function item(){
        $db = $this->db;
        $docId = $this->input->post("doc_id");
        $itemId = $this->input->post("item_id");

        $dnrow = $db->select("id")
                        ->from("debit_note")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($dnrow)) return $this->data;

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
                        ->from("debit_note_items")
                        ->where("id", $itemId)
                        ->where("debit_note_id", $docId)
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

        $dnrow = $db->select("id")
                    ->from("debit_note")
                    ->where("id", $docId)
                    ->where("deleted", 0)
                    ->get()->row();
 
        if(empty($dnrow)) return $this->data;
     
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
                    "debit_note_id"=>$docId,
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
            $db->where("debit_note_id", $docId);
            $total_items = $db->count_all_results("debit_note_items");
            $fdata["debit_note_id"] = $docId;
            $fdata["sort"] = $total_items + 1;
            $db->insert("debit_note_items", $fdata);
        }else{
            $db->where("id", $itemId);
            $db->where("debit_note_id", $docId);
            $db->update("debit_note_items", $fdata);
        }
        
        if ($db->trans_status() === FALSE){
            $db->trans_rollback();
        }

        $db->trans_commit();

        $this->updateDoc($docId);

        $this->data["target"] = get_uri("debit-notes/view/".$docId);
        $this->data["status"] = "success";

        return $this->data;

    }

    function deleteItem(){
        $db = $this->db;
        $docId = $this->json->doc_id;
        
        $db->where("id", $this->json->item_id);
        $db->where("debit_note_id", $docId);
        $db->delete("debit_note_items");

        if($db->affected_rows() != 1) return $this->data;

        $this->updateDoc($docId);

        $this->data["status"] = "success";
        return $this->data;
    }

    function updateStatus(){
        $db = $this->db;
        $docId = $this->json->doc_id;
        $updateStatusTo = $this->json->update_status_to;

        $dnrow = $db->select("*")
                    ->from("debit_note")
                    ->where("id",$docId)
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($dnrow)) return $this->data;
        if($dnrow->status == $updateStatusTo){
            $this->data["dataset"] = $this->getIndexDataSetHTML($dnrow);
            $this->data["message"] = "ไม่สามารถแก้ไขสถานะเอกสารได้ เนื่องจากเอกสารมีการเปลี่ยนแปลงสถานะแล้ว";
            return $this->data;
        }

        $invoice_id = $this->data["doc_id"] = $docId;
        $invoice_number = $dnrow->doc_number;
        $currentStatus = $dnrow->status;

        $this->db->trans_begin();

        if($updateStatusTo == "P"){
            if($currentStatus == "V"){
                $this->data["dataset"] = $this->getIndexDataSetHTML($dnrow);
                return $this->data;
            }

            $db->where("id", $docId);
            $db->update("debit_note", [
                                        "approved_by"=>$this->login_user->id,
                                        "approved_datetime"=>date("Y-m-d H:i:s"),
                                        "status"=>"P"
                                    ]);

            
            $this->data["status"] = "success";
            

        }elseif($updateStatusTo == "V"){
            $db->where("id", $docId);
            $db->update("debit_note", [
                                        "status"=>"V"
                                    ]);

        }

        if ($db->trans_status() === FALSE){
            $db->trans_rollback();
            $this->data["dataset"] = $this->getIndexDataSetHTML($dnrow);
            return $this->data;
        }

        $db->trans_commit();

        if(isset($this->data["task"])) return $this->data;

        $dnrow = $db->select("*")
                    ->from("debit_note")
                    ->where("id",$docId)
                    ->where("deleted", 0)
                    ->get()->row();

        $this->data["dataset"] = $this->getIndexDataSetHTML($dnrow);
        $this->data["status"] = "success";
        $this->data["message"] = lang('record_saved');
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
                if($db->count_all_results("debit_note") < 1) break;
            }

            $this->data["sharelink"] = get_uri($this->shareHtmlAddress."th/".$sharekey);
        }

        $db->where("id", $docId);
        $db->update("debit_note", ["sharekey"=>$sharekey, "sharekey_by"=>$sharekey_by]);

        return $this->data;
    }


    function getHTMLInvoices() {
        $db = $this->db;

        if(!isset($this->json->customer_id)) return ["html"=>"<tr class='norecord'><td colspan='6'>กรุณาเลือกชื่อลูกค้า เพื่อค้นหาเอกสาร</td></tr>"];
        if($this->json->customer_id == "") return ["html"=>"<tr class='norecord'><td colspan='6'>กรุณาเลือกชื่อลูกค้า เพื่อค้นหาเอกสาร</td></tr>"];

        $db->select("*")->from("invoice");
        $db->where("client_id", $this->json->customer_id);
        $db->where("status", "P");
        $db->where("deleted", 0);

        $invrows = $db->order_by("doc_number", "desc")->get()->result();

        $html = "<tr class='norecord'><td colspan='6'>ไม่พบข้อมูลใบกำกับภาษี</td></tr>";

        if(!empty($invrows)){
            $html = "";
            foreach($invrows as $invrow){
                $html .= "<tr>";
                    $html .= "<td>".convertDate($invrow->doc_date, true)."</td>";
                    $html .= "<td>".$invrow->doc_number."</td>";
                    $html .= "<td>".$this->Clients_m->getCompanyName($invrow->client_id)."</td>";
                    $html .= "<td>".$invrow->total."</td>";
                    $html .= "<td>".$this->Invoices_m->getStatusName($invrow->status)."</td>";
                    $html .= "<td><a data-invoice_id='".$invrow->id."' class='choose-inv-button custom-color-button'>เลือก</a></td>";
                $html .= "</tr>";
            }
        }

        $data["html"] = $html;

        return $data;
    }
}
