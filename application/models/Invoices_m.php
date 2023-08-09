<?php

class Invoices_m extends MY_Model {
    private $code = "IV";
    private $shareHtmlAddress = "share/invoice/html/";

    function __construct() {
        parent::__construct();
    }

    function getCode(){
        return $this->code;
    }

    function getNewDocNumber(){
        $this->db->where("DATE_FORMAT(created_datetime,'%Y-%m')", date("Y-m"));
        $this->db->where("deleted", 0);
        $running_number = $this->db->get("invoice")->num_rows() + 1;

        $doc_number = $this->getCode().date("Ym").sprintf("%04d", $running_number);

        return $doc_number;
    }

    function getStatusName($status_code){
        if($status_code == "W"){
            return "รออนุมัติ";
        }
    }


    function getIndexDataSetHTML($ivrow){
        $doc_status = "<select class='dropdown_status' data-doc_id='".$ivrow->id."' data-doc_number='".$ivrow->doc_number."'>";

        if($ivrow->status == "W"){
            $doc_status .= "<option selected>รออนุมัติ</option>";
            $doc_status .= "<option value='O'>อนุมัติ</option>";
        }elseif($ivrow->status == "O"){
            $doc_status .= "<option selected>รอรับชำระ</option>";
        }elseif($ivrow->status == "P"){
            $doc_status .= "<option selected>ชำระเงินแล้ว</option>";   
        }

        $doc_status .= "</select>";

        

        /*if($ivrow->status == "W"){
            $doc_status = "<select class='dropdown_status' data-doc_id='".$ivrow->id."'>";
            $doc_status .= "<option selected>รออนุมัติ</option>";
            $doc_status .= "<option value='A'>อนุมัติ</option>";
            $doc_status .= "</select>";
            
            
        }elseif($ivrow->status == "A"){
            $doc_status = "<a class='button_status' data-doc_id='".$ivrow->id."' data-doc_status='P'>รับชำระ</a>"; 
        }elseif($ivrow->status == "P"){
            $doc_status = "<a class='button_status' data-doc_id='".$ivrow->id."' data-doc_status='V'>แสดงการรับชำระ</a>"; 
        }*/

        $reference_number_column = $ivrow->reference_number;
        if($ivrow->quotation_id != null){
            $reference_number_column = "<a href='".get_uri("quotations/view/".$ivrow->quotation_id)."'>".$ivrow->reference_number."</a>";
        }

        $data = [
                    "<a href='".get_uri("invoices/view/".$ivrow->id)."'>".convertDate($ivrow->doc_date, 2)."</a>",
                    "<a href='".get_uri("invoices/view/".$ivrow->id)."'>".$ivrow->doc_number."</a>",
                    $reference_number_column,
                    "<a href='".get_uri("clients/view/".$ivrow->client_id)."'>".$this->Clients_m->getCompanyName($ivrow->client_id)."</a>",
                    convertDate($ivrow->due_date, true), number_format($ivrow->total, 2), $doc_status,
                    "<a data-post-id='".$ivrow->id."' data-action-url='".get_uri("invoices/addedit")."' data-act='ajax-modal' class='edit'><i class='fa fa-pencil'></i></a>"
                ];

        /*
        *Delete button
        *<a data-id='".$ivrow->id."' data-action-url='".get_uri("invoices/delete_doc")."' data-action='delete' class='delete'><i class='fa fa-times fa-fw'></i></a>
        */

        return $data;
    }

    function indexDataSet() {
        $db = $this->db;

        $db->select("*")->from("invoice");

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

        $ivrows = $db->order_by("doc_number", "desc")->get()->result();

        $dataset = [];

        foreach($ivrows as $ivrow){
            $dataset[] = $this->getIndexDataSetHTML($ivrow);
        }

        return $dataset;
    }

    function getDoc($docId){
        $db = $this->db;

        $this->data["doc_id"] = null;
        $this->data["quotation_id"] = null;
        $this->data["doc_number"] = null;
        $this->data["doc_date"] = date("Y-m-d");
        $this->data["credit"] = "0";
        $this->data["due_date"] = date("Y-m-d");
        $this->data["reference_number"] = "";
        $this->data["discount_type"] = "P";
        $this->data["discount_percent"] = 0;
        $this->data["discount_amount"] = 0;
        $this->data["vat_inc"] = "N";
        $this->data["unpaid_amount"] = 0;
        $this->data["wht_inc"] = "N";
        $this->data["total"] = 0;//ยอด
        $this->data["total_payment_amount"] = 0;//ชำระไปแล้ว
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
            $ivrow = $db->select("*")
                        ->from("invoice")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

            if(empty($ivrow)) return $this->data;

            $quotation_id = $ivrow->quotation_id;
            $unpaid_amount = 0;

            if($quotation_id != null){
                $qrow = $db->select("total")
                            ->from("quotation")
                            ->where("id", $quotation_id)
                            ->where("deleted", 0)
                            ->get()->row();

                if(empty($qrow)){
                    return $this->data;
                }

                $billed_amount = 0;
                $quotation_total = $qrow->total;
            }

            $lead_id = $client_id = null;
            
            if($this->Customers_m->isLead($ivrow->client_id) == true){
                $this->data["customer_id"] = $lead_id = $ivrow->client_id;
                $this->data["customer_is_lead"] = 1;
            }else{
                $this->data["customer_id"] = $client_id = $ivrow->client_id;
                $this->data["customer_is_lead"] = 0;
            }

            $total_payment_amount = $db->select("SUM(payment_amount) AS total_payment_amount")
                                        ->from("invoice_payment")
                                        ->where("invoice_id", $ivrow->id)
                                        ->get()->row()->total_payment_amount;

            if($total_payment_amount == null) $total_payment_amount = 0;

            $this->data["doc_id"] = $docId;
            $this->data["quotation_id"] = $quotation_id;
            $this->data["doc_number"] = $ivrow->doc_number;
            $this->data["share_link"] = $ivrow->sharekey != null ? get_uri($this->shareHtmlAddress."th/".$ivrow->sharekey) : null;
            $this->data["doc_date"] = $ivrow->doc_date;
            $this->data["credit"] = $ivrow->credit;
            $this->data["due_date"] = $ivrow->due_date;
            $this->data["reference_number"] = $ivrow->reference_number;
            $this->data["discount_type"] = $ivrow->discount_type;
            $this->data["discount_percent"] = $ivrow->discount_percent;
            $this->data["discount_amount"] = $ivrow->discount_amount;
            $this->data["unpaid_amount"] = number_format($unpaid_amount, 2);
            $this->data["vat_inc"] = $ivrow->vat_inc;
            $this->data["vat_percent"] = number_format_drop_zero_decimals($ivrow->vat_percent, 2)."%";
            $this->data["wht_inc"] = $ivrow->wht_inc;
            $this->data["total"] = $ivrow->total;
            $this->data["total_payment_amount"] = $total_payment_amount;
            $this->data["net_receivable_await_payment_amount"] = $ivrow->total - $total_payment_amount;
            $this->data["project_id"] = $ivrow->project_id;
            $this->data["client_id"] = $client_id;
            $this->data["lead_id"] = $lead_id;
            $this->data["remark"] = $ivrow->remark;
            $this->data["created_by"] = $ivrow->created_by;
            $this->data["created_datetime"] = $ivrow->created_datetime;
            $this->data["approved_by"] = $ivrow->approved_by;
            $this->data["approved_datetime"] = $ivrow->approved_datetime;
            $this->data["doc_status"] = $ivrow->status;
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

        $ivrow = $db->select("*")
                    ->from("invoice")
                    ->get()->row();

        if(empty($ivrow)) return $this->data;

        $docId = $ivrow->id;

        $ivrows = $db->select("*")
                        ->from("invoice_items")
                        ->where("invoice_id", $docId)
                        ->order_by("sort", "asc")
                        ->get()->result();

        $client_id = $ivrow->client_id;
        $created_by = $ivrow->created_by;

        $this->data["seller"] = $ci->Users_m->getInfo($created_by);

        $this->data["buyer"] = $ci->Customers_m->getInfo($client_id);
        $this->data["buyer_contact"] = $ci->Customers_m->getContactInfo($client_id);

        $this->data["doc_number"] = $ivrow->doc_number;
        $this->data["doc_date"] = $ivrow->doc_date;
        $this->data["credit"] = $ivrow->credit;
        $this->data["due_date"] = $ivrow->due_date;
        $this->data["reference_number"] = $ivrow->reference_number;
        $this->data["remark"] = $ivrow->remark;

        $this->data["sub_total_before_discount"] = $ivrow->sub_total_before_discount;

        $this->data["discount_type"] = $ivrow->discount_type;
        $this->data["discount_percent"] = $ivrow->discount_percent;
        $this->data["discount_amount"] = $ivrow->discount_amount;
        
        $this->data["sub_total"] = $ivrow->sub_total;

        $this->data["vat_inc"] = $ivrow->vat_inc;
        $this->data["vat_percent"] = $ivrow->vat_percent;
        $this->data["vat_value"] = $ivrow->vat_value;
        $this->data["total"] = $ivrow->total;
        $this->data["total_in_text"] = numberToText($ivrow->total);
        $this->data["wht_inc"] = $ivrow->wht_inc;
        $this->data["wht_percent"] = $ivrow->wht_percent;
        $this->data["wht_value"] = $ivrow->wht_value;
        $this->data["payment_amount"] = $ivrow->payment_amount;

        $this->data["sharekey_by"] = $ivrow->sharekey_by;
        $this->data["approved_by"] = $ivrow->approved_by;
        $this->data["approved_datetime"] = $ivrow->approved_datetime;
        $this->data["doc_status"] = $ivrow->status;

        $this->data["doc"] = $ivrow;
        $this->data["items"] = $ivrows;

        $this->data["status"] = "success";
        $this->data["message"] = "ok";

        return $this->data;
    }

    function updateDoc($docId = null){
        $db = $this->db;

        $ivrow = null;

        $discount_type = "P";
        $discount_percent = 0;
        $discount_amount = 0;

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
            
            $ivrow = $db->select("*")
                        ->from("invoice")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

            if(empty($ivrow)) return $this->data;

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
            $ivrow = $db->select("*")
                        ->from("invoice")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

            if(empty($ivrow)) return $this->data;            

            $discount_type = $ivrow->discount_type;
            $discount_percent = $ivrow->discount_percent;
            $discount_amount = $ivrow->discount_amount;


            $vat_inc = $ivrow->vat_inc;
            $wht_inc = $ivrow->wht_inc;

            if($vat_inc == "Y") $vat_percent = $ivrow->vat_percent;
            if($wht_inc == "Y") $wht_percent = $ivrow->wht_percent;
        }
        
        $sub_total_before_discount = $db->select("SUM(total_price) AS SUB_TOTAL")
                                        ->from("invoice_items")
                                        ->where("invoice_id", $docId)
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

        $quotation_id = $ivrow->quotation_id;
        $quotation_sub_total = 0;
        $quotation_total = 0;
        $billed_amount = 0;

        if($quotation_id != null){
            $qrow = $db->select("sub_total, total")
                        ->from("quotation")
                        ->where("id", $quotation_id)
                        ->where("deleted", 0)
                        ->get()->row();

            if(!empty($qrow)){
                $quotation_sub_total = $qrow->sub_total;
                $quotation_total = $qrow->total;
            }else{
                log_message("error", "SYSERR=>Invoices_m->updateDoc: ".$db->last_query());
            }
        }

        if($quotation_id == null){
            if($vat_inc == "Y") $vat_value = ($sub_total * $vat_percent) / 100;
            $total = $sub_total + $vat_value;

            if($wht_inc == "Y") $wht_value = ($sub_total * $wht_percent) / 100;
            $payment_amount = $total - $wht_value;
        }

        $db->where("id", $docId);
        $db->update("invoice", [
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
            $ivrow = $db->select("status")
                        ->from("invoice")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

            if(empty($ivrow)){
                $this->data["success"] = false;
                $this->data["message"] = "ขออภัย เกิดข้อผิดพลาดระหว่างดำเนินการ! โปรดลองใหม่อีกครั้งในภายหลัง";
                return $this->data;
            }

            if($ivrow->status != "W"){
                $this->data["success"] = false;
                $this->data["message"] = "ไม่สามารถบันทึกเอกสารได้เนื่องจากเอกสารมีการเปลี่ยนแปลงสถานะแล้ว";
                return $this->data;
            }

            $db->where("id", $docId);
            $db->where("deleted", 0);
            $db->update("invoice", [
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
            
            $db->insert("invoice", [
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
        
        $this->data["target"] = get_uri("invoices/view/". $docId);
        $this->data["status"] = "success";

        return $this->data;
    }

    function deleteDoc(){
        $db = $this->db;
        $docId = $this->input->post("id");

        $ivrow = $db->select("status")
                        ->from("invoice")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($ivrow)) return $this->data;

        if($ivrow->status != "W"){
            $this->data["success"] = false;
            $this->data["message"] = "คุณไม่สามารถลบเอกสารได้ เนื่องจากเอกสารมีการเปลี่ยนแปลงสถานะแล้ว";
            return $this->data;
        }

        $db->where("id", $docId);
        $db->update("invoice", ["deleted"=>1]);

        $this->data["success"] = true;
        $this->data["message"] = lang('record_deleted');

        return $this->data;
    }

    function undoDoc(){
        $db = $this->db;
        $docId = $this->input->post("id");

        $db->where("id", $docId);
        $db->update("invoice", ["deleted"=>0]);

        $ivrow = $db->select("*")
                    ->from("invoice")
                    ->where("id", $docId)
                    ->get()->row();

        $data["success"] = true;
        $data["data"] = $this->getIndexDataSetHTML($ivrow);
        $data["message"] = lang('record_undone');

        return $data;
    }

    function items(){
        $db = $this->db;
        
        $ivrow = $db->select("id, quotation_id, status")
                        ->from("invoice")
                        ->where("id", $this->json->doc_id)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($ivrow)) return $this->data;

        $invirows = $db->select("*")
                        ->from("invoice_items")
                        ->where("invoice_id", $this->json->doc_id)
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

        $this->data["doc_status"] = $ivrow->status;
        $this->data["items"] = $items;
        $this->data["status"] = "success";

        return $this->data;
    }

    function item(){
        $db = $this->db;
        $docId = $this->input->post("doc_id");
        $itemId = $this->input->post("item_id");

        $ivrow = $db->select("id, quotation_id")
                        ->from("invoice")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($ivrow)) return $this->data;

        $this->data["doc_id"] = $docId;
        $this->data["quotation_id"] = $ivrow->quotation_id;
        $this->data["product_id"] = "";
        $this->data["product_name"] = "";
        $this->data["product_description"] = "";
        $this->data["quantity"] = number_format(1, $this->Settings_m->getDecimalPlacesNumber());
        $this->data["unit"] = "";
        $this->data["price"] = number_format(0, 2);
        $this->data["total_price"] = number_format(0, 2);

        if(!empty($itemId)){
            $qirow = $db->select("*")
                        ->from("invoice_items")
                        ->where("id", $itemId)
                        ->where("invoice_id", $docId)
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

        $ivrow = $db->select("id")
                    ->from("invoice")
                    ->where("id", $docId)
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($ivrow)) return $this->data;
      
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
                    "invoice_id"=>$docId,
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
            $db->where("invoice_id", $docId);
            $total_items = $db->count_all_results("invoice_items");
            $fdata["invoice_id"] = $docId;
            $fdata["sort"] = $total_items + 1;
            $db->insert("invoice_items", $fdata);
        }else{
            $db->where("id", $itemId);
            $db->where("invoice_id", $docId);
            $db->update("invoice_items", $fdata);
        }

        
        if ($db->trans_status() === FALSE){
            $db->trans_rollback();
        }else{
            $db->trans_commit();
        }

        $this->updateDoc($docId);

        $this->data["target"] = get_uri("invoices/view/".$docId);
        $this->data["status"] = "success";

        return $this->data;

    }

    function deleteItem(){
        $db = $this->db;
        $docId = $this->json->doc_id;
        
        $db->where("id", $this->json->item_id);
        $db->where("invoice_id", $docId);
        $db->delete("invoice_items");

        if($db->affected_rows() != 1) return $this->data;

        $this->updateDoc($docId);

        $this->data["status"] = "success";

        return $this->data;
    }

    function updateStatus(){
        $db = $this->db;
        $docId = $this->json->doc_id;
        $updateStatusTo = $this->json->update_status_to;

        $ivrow = $db->select("*")
                    ->from("invoice")
                    ->where("id",$docId)
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($ivrow)) return $this->data;
        if($ivrow->status == $updateStatusTo){
            $this->data["status"] = "notchange";
            $this->data["dataset"] = $this->getIndexDataSetHTML($ivrow);
            return $this->data;
        }


        $invoice_id = $this->data["doc_id"] = $docId;
        $invoice_number = $ivrow->doc_number;
        $currentStatus = $ivrow->status;

        $this->db->trans_begin();

        if($updateStatusTo == "O"){
            $db->where("id", $docId);
            $db->where("deleted", 0);
            $db->update("invoice", [
                                        "approved_by"=>$this->login_user->id,
                                        "approved_datetime"=>date("Y-m-d H:i:s"),
                                        "status"=>"O"
                                    ]);
        }

        if ($db->trans_status() === FALSE){
            $db->trans_rollback();
            $this->data["dataset"] = $this->getIndexDataSetHTML($ivrow);
            return $this->data;
        }

        $db->trans_commit();

        $this->data["status"] = "success";
        $this->data["message"] = lang("record_saved");

        if(isset($this->data["task"])) return $this->data;

        $ivrow = $db->select("*")
                    ->from("invoice")
                    ->where("id",$docId)
                    ->where("deleted", 0)
                    ->get()->row();

        $this->data["dataset"] = $this->getIndexDataSetHTML($ivrow);
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
                if($db->count_all_results("invoice") < 1) break;
            }

            $this->data["sharelink"] = get_uri($this->shareHtmlAddress."th/".$sharekey);
        }

        $db->where("id", $docId);
        $db->update("invoice", ["sharekey"=>$sharekey, "sharekey_by"=>$sharekey_by]);

        return $this->data;
    }

    function getPayment($docId){
        $db = $this->db;

        $ivrow = $db->select("*")
                    ->from("invoice")
                    ->where("id", $docId)
                    ->where("status !=", "W")
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($ivrow)){
            return $this->data;
        }

        $total_payment_amount = $db->select("SUM(payment_amount) AS total_payment_amount")
                                    ->from("invoice_payment")
                                    ->where("invoice_id", $ivrow->id)
                                    ->get()->row()->total_payment_amount;

        if($total_payment_amount == null) $total_payment_amount = 0;

        $this->data["doc_id"] = $ivrow->id;
        $this->data["doc_number"] = $ivrow->doc_number;

        $this->data["due_date"] = $ivrow->due_date;
        $this->data["invoice_full_payment_amount"] = $ivrow->total;
        $this->data["total_net_amount_to_receive_payment"] = $ivrow->total;
        $this->data["total_paid"] = $total_payment_amount;
        $this->data["net_await_payment_receive_amount"] = $ivrow->total - $total_payment_amount;
        $this->data["doc_status"] = $ivrow->status;
        $this->data["payment_records"] = [];

        $ivprows = $db->select("*")
                        ->from("invoice_payment")
                        ->where("invoice_id", $ivrow->id)
                        ->order_by("record_number", "ASC")
                        ->get()->result();

        if(!empty($ivprows)){
            foreach($ivprows as $ivprow){
                $pr["payment_id"] = $ivprow->id;
                $pr["record_number"] = $ivprow->record_number;
                $pr["payment_method_id"] = $ivprow->payment_method_id;
                $pr["payment_amount"] = $ivprow->payment_amount;
                $pr["wht_inc"] = $ivprow->wht_inc;
                $pr["wht_value"] = $ivprow->wht_value;
                $pr["money_payment_receive"] = $ivprow->money_payment_receive;
                $pr["issued_receipt"] = $ivprow->issued_receipt;
                $pr["payment_date"] = $ivprow->payment_date;

                $this->data["payment_records"][] = $pr;
            }
        }

        $this->data["status"] = "success";
        $this->data["message"] = "success";

        return $this->data;
    }

    function addPayment(){
        $db = $this->db;

        $invoice_id = $this->json->invoice_id;
        $payment_method_id = ($this->json->payment_method_id == -1 ? null : $this->json->payment_method_id);
        $payment_date = convertDate($this->json->payment_date);
        $payment_amount = getNumber(str_replace(",", "", $this->json->payment_amount));
        $payment_remark = $this->json->remark;
        $payment_withholding_tax_percent = ($this->json->withholding_tax_percent == 0 ? 0:getNumber($this->json->withholding_tax_percent));
        $payment_withholding_tax_include = $payment_withholding_tax_percent == null ? "N":"Y";
        $payment_withholding_value = 0;

        $ivrow = $db->select("total")
                    ->from("invoice")
                    ->where("id", $invoice_id)
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($ivrow)){
            return $this->data;
        }

        $invoices_total = $ivrow->total;

        $total_payment_amount = $db->select("SUM(payment_amount) AS TOTAL_PAYMENT_AMOUNT")
                                    ->from("invoice_payment")
                                    ->get()->row()->TOTAL_PAYMENT_AMOUNT;

        if($total_payment_amount == null) $total_payment_amount = 0;

        if(($total_payment_amount + $payment_amount) > $invoices_total){
            $this->data["message"] = "ไม่สามารถชำระเงินได้.";
            return $this->data;
        }

        $db->where("invoice_id", $invoice_id);
        $total_records = $db->count_all_results("invoice_payment");

        if($payment_withholding_tax_include == "Y"){
            $payment_withholding_value = roundUp(($payment_amount * $payment_withholding_tax_percent)/100);
        }

        $money_payment_receive = $payment_amount - $payment_withholding_value;

        $db->insert("invoice_payment", [
                                        "invoice_id"=>$invoice_id,
                                        "record_number"=>++$total_records,
                                        "payment_method_id"=>$payment_method_id,
                                        "payment_amount"=>$payment_amount,
                                        "remark"=>$payment_remark,
                                        "wht_inc"=>$payment_withholding_tax_include,
                                        "wht_percent"=>$payment_withholding_tax_percent,
                                        "wht_value"=>$payment_withholding_value,
                                        "money_payment_receive"=>$money_payment_receive,
                                        "issued_receipt"=>"N",
                                        "payment_date"=>$payment_date,
                                        "created_by"=>$this->login_user->id,
                                        "created_datetime"=>date("Y-m-d H:i:s")
                                    ]);

        if($db->affected_rows() != 1){
            return $this->data;
        }

        $this->data["status"] = "success";
        $this->data["message"] = "success";

        return $this->data;
    }

    function voidPayment(){
        $db = $this->db;
        $invoice_id = $this->json->invoice_id;
        $payment_id = $this->json->payment_id;

        $ivrow = $db->select("total")
                    ->from("invoice")
                    ->where("id", $invoice_id)
                    ->get()->row();

        if(empty($ivrow)) return $this->data;

        $invoice_total = $ivrow->total;

        $iprow = $db->select("*")
                    ->from("invoice_payment")
                    ->where("id", $payment_id)
                    ->where("invoice_id", $invoice_id)
                    ->get()->row();

        if(empty($iprow)) return $this->data;

        $db->trans_begin();

        $db->where("id", $payment_id);
        $db->where("invoice_id", $invoice_id);
        $db->delete("invoice_payment");

        $total_payment_amount = $db->select("SUM(payment_amount) AS TOTAL_PAYMENT_AMOUNT")
                                    ->from("invoice_payment")
                                    ->where("invoice_id", $invoice_id)
                                    ->get()->row()->TOTAL_PAYMENT_AMOUNT;

        if($total_payment_amount == null) $total_payment_amount = 0;

        if($invoice_total > $total_payment_amount){
            $db->where("id", $invoice_id);
            $db->update("invoice", ["status"=>"O"]);
        }

        if($db->trans_status() === FALSE){
            $db->trans_rollback();
            return $this->data;
        }

        $db->trans_commit();

        $this->data["status"] = "success";
        $this->data["message"] = "success";

        return $this->data;
    }

    function createReceipt(){
        $db = $this->db;
        $invoice_id = $this->json->invoice_id;
        $payment_id = $this->json->payment_id;

        $ivrow = $db->select()
                    ->from("invoice")
                    ->where("id", $invoice_id)
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($ivrow)) return $this->data;
        
        $iprow = $db->select("*")
                    ->from("invoice_payment")
                    ->where("id", $payment_id)
                    ->where("invoice_id", $invoice_id)
                    ->get()->row();

        if(empty($iprow)) return $this->data;

        if($iprow->receipt_id != null){
            $this->data["message"] = "ไม่สามารถออกใบเสร็จได้ เนื่องจากรายการรับชำระนี้ ถูกออกใบเสร็จไปแล้ว.";
            return $this->data;
        }

        $invoice_number = $ivrow->doc_number;
        $receipt_number = $this->Receipts_m->getNewDocNumber();


        /*$db->insert("receipt", [
                                    "invoice_id"=>$invoice_id,
                                    "invoice_payment_id"=>$invoice_id,
                                    "doc_number"=>$receipt_number,
                                    "doc_date"=>date("Y-m-d"),
                                    "reference_number"=>$invoice_number,
                                    "vat_inc"=>"N",
                                    "client_id"=>$customer_id,
                                    "project_id"=>$project_id,
                                    "remark"=>$remark,
                                    "created_by"=>$this->login_user->id,
                                    "created_datetime"=>date("Y-m-d H:i:s"),
                                    "status"=>"W"
                                ]);*/

        $receipt_id = $db->insert_id();

        return ["message"=>$receipt_number];


    }
}