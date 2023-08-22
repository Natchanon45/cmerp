<?php

class Billing_notes_m extends MY_Model {
    private $code = "BL";
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

        $reference_number_column = $bnrow->reference_number;
        if($bnrow->quotation_id != null){
            $reference_number_column = "<a href='".get_uri("quotations/view/".$bnrow->quotation_id)."'>".$bnrow->reference_number."</a>";
        }

        $data = [
                    "<a href='".get_uri("billing-notes/view/".$bnrow->id)."'>".convertDate($bnrow->doc_date, 2)."</a>",
                    "<a href='".get_uri("billing-notes/view/".$bnrow->id)."'>".$bnrow->doc_number."</a>",
                    $reference_number_column,
                    "<a href='".get_uri("clients/view/".$bnrow->client_id)."'>".$this->Clients_m->getCompanyName($bnrow->client_id)."</a>",
                    convertDate($bnrow->due_date, true), number_format($bnrow->total, 2), $doc_status,
                    "<a data-post-id='".$bnrow->id."' data-action-url='".get_uri("billing-notes/addedit")."' data-act='ajax-modal' class='edit'><i class='fa fa-pencil'></i></a>"
                ];

        /*
        *Delete button
        *<a data-id='".$bnrow->id."' data-action-url='".get_uri("billing-notes/delete_doc")."' data-action='delete' class='delete'><i class='fa fa-times fa-fw'></i></a>
        */

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
        $this->data["partials_percent"] = 0;
        $this->data["partials_amount"] = 0;
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

        $this->data["is_partial_billing"] = "N";
        $this->data["partials_type"] = null;

        if(!empty($docId)){
            $bnrow = $db->select("*")
                        ->from("billing_note")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

            if(empty($bnrow)) return $this->data;

            $quotation_id = $bnrow->quotation_id;
            $quotation_is_partial = "N";
            $quotation_partial_type = null;
            $unpaid_amount = 0;

            if($quotation_id != null){
                $qrow = $db->select("total, is_partials, partials_type")
                            ->from("quotation")
                            ->where("id", $quotation_id)
                            ->where("deleted", 0)
                            ->get()->row();

                if(empty($qrow)){
                    log_message("error", "SYSERR=>Billing_notes_m->getDoc:".$db->last_query());
                    return $this->data;
                }

                $billed_amount = 0;
                $quotation_total = $qrow->total;
                $quotation_is_partial = $qrow->is_partials;
                $quotation_partial_type = $qrow->partials_type;

                if($quotation_is_partial == "Y"){
                    $billed_amount = $db->select("SUM(partials_amount) AS billed_amount")
                                                ->from("billing_note")
                                                ->where("quotation_id", $quotation_id)
                                                ->where("deleted", 0)
                                                ->get()->row()->billed_amount;

                    if($quotation_partial_type == "A"){
                        $unpaid_amount = $quotation_total - $billed_amount;
                    }else{
                        $unpaid_amount = (($quotation_total - $billed_amount)/$quotation_total) * 100;
                    }
                }
            }

            $lead_id = $client_id = null;
            
            if($this->Customers_m->isLead($bnrow->client_id) == true){
                $this->data["customer_id"] = $lead_id = $bnrow->client_id;
                $this->data["customer_is_lead"] = 1;
            }else{
                $this->data["customer_id"] = $client_id = $bnrow->client_id;
                $this->data["customer_is_lead"] = 0;
            }

            $this->data["doc_id"] = $docId;
            $this->data["quotation_id"] = $quotation_id;
            $this->data["doc_number"] = $bnrow->doc_number;
            $this->data["share_link"] = $bnrow->sharekey != null ? get_uri($this->shareHtmlAddress."th/".$bnrow->sharekey) : null;
            $this->data["doc_date"] = $bnrow->doc_date;
            $this->data["credit"] = $bnrow->credit;
            $this->data["due_date"] = $bnrow->due_date;
            $this->data["reference_number"] = $bnrow->reference_number;
            $this->data["discount_type"] = $bnrow->discount_type;
            $this->data["discount_percent"] = $bnrow->discount_percent;
            $this->data["discount_amount"] = $bnrow->discount_amount;
            $this->data["unpaid_amount"] = number_format($unpaid_amount, 2);
            $this->data["partials_percent"] = $bnrow->partials_percent;
            $this->data["partials_amount"] = $bnrow->partials_amount;
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

            $this->data["is_partial_billing"] = $quotation_is_partial;
            $this->data["partials_type"] = $quotation_partial_type;
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
        $this->data["credit"] = $bnrow->credit;
        $this->data["due_date"] = $bnrow->due_date;
        $this->data["reference_number"] = $bnrow->reference_number;
        $this->data["remark"] = $bnrow->remark;

        $this->data["sub_total_before_discount"] = $bnrow->sub_total_before_discount;

        $this->data["discount_type"] = $bnrow->discount_type;
        $this->data["discount_percent"] = $bnrow->discount_percent;
        $this->data["discount_amount"] = $bnrow->discount_amount;
        
        $this->data["sub_total"] = $bnrow->sub_total;

        $this->data["vat_inc"] = $bnrow->vat_inc;
        $this->data["vat_percent"] = $bnrow->vat_percent;
        $this->data["vat_value"] = $bnrow->vat_value;
        $this->data["total"] = $bnrow->total;
        $this->data["total_in_text"] = numberToText($bnrow->total);
        $this->data["wht_inc"] = $bnrow->wht_inc;
        $this->data["wht_percent"] = $bnrow->wht_percent;
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

        $bnrow = null;

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

        $quotation_id = $bnrow->quotation_id;
        $quotation_sub_total = 0;
        $quotation_total = 0;
        $billed_amount = 0;
        $is_partial_billing = "N";
        $can_update_partial_billing = "N";
        $partials_type = null;
        $partials_percent = null;
        $partials_amount = null;

        if($quotation_id != null){
            $qrow = $db->select("sub_total, total, is_partials, partials_type")
                        ->from("quotation")
                        ->where("id", $quotation_id)
                        ->where("deleted", 0)
                        ->get()->row();

            if(!empty($qrow)){
                $partials_type = $qrow->partials_type;
                $quotation_sub_total = $qrow->sub_total;
                $quotation_total = $qrow->total;

                if($qrow->is_partials == "Y"){
                    $is_partial_billing = "Y";

                    $billed_amount = $db->select("SUM(partials_amount) AS billed_amount")
                                        ->from("billing_note")
                                        ->where("quotation_id", $quotation_id)
                                        ->where("deleted", 0)
                                        ->get()->row()->billed_amount;

                    if($billed_amount == null) $billed_amount = 0;

                    if($partials_type == "P"){
                        $partials_percent = getNumber($this->json->partials_percent);
                        $partials_amount = ($partials_percent * $quotation_sub_total)/100;
                        $billed_amount = $billed_amount + $partials_amount;
                    }

                    if($partials_type == "A"){
                        $partials_amount = getNumber($this->json->partials_amount);
                        $billed_amount = $billed_amount + $partials_amount;
                    }

                    if($vat_inc == "Y") $vat_value = ($partials_amount * $vat_percent) / 100;
                    $total = $partials_amount + $vat_value;

                    if($wht_inc == "Y") $wht_value = ($partials_amount * $wht_percent) / 100;
                    $payment_amount = $total - $wht_value;
                }
            }else{
                log_message("error", "SYSERR=>Billing_notes_m->updateDoc: ".$db->last_query());
            }
        }

        if($quotation_id == null || $is_partial_billing == "N"){
            if($vat_inc == "Y") $vat_value = ($sub_total * $vat_percent) / 100;
            $total = $sub_total + $vat_value;

            if($wht_inc == "Y") $wht_value = ($sub_total * $wht_percent) / 100;
            $payment_amount = $total - $wht_value;
        }

        $db->where("id", $docId);
        $db->update("billing_note", [
                                    "sub_total_before_discount"=>$sub_total_before_discount,
                                    "discount_type"=>$discount_type,
                                    "discount_percent"=>$discount_percent,
                                    "discount_amount"=>$discount_amount,
                                    "sub_total"=>$sub_total,
                                    "partials_percent"=>$partials_percent,
                                    "partials_amount"=>$partials_amount,
                                    "vat_inc"=>$vat_inc,
                                    "vat_percent"=>$vat_percent,
                                    "vat_value"=>$vat_value,
                                    "total"=>$total,
                                    "wht_inc"=>$wht_inc,
                                    "wht_percent"=>$wht_percent,
                                    "wht_value"=>$wht_value,
                                    "payment_amount"=>$payment_amount
                                ]);


        if($is_partial_billing == "Y"){
            $bnsum = $db->select("SUM(partials_amount) AS sub_total_vat_excluded, SUM(total) AS collected_amount")
                                    ->from("billing_note")
                                    ->where("quotation_id", $quotation_id)
                                    ->where("deleted", 0)
                                    ->get()->row();

            $sub_total_vat_excluded = $bnsum->sub_total_vat_excluded === null ? 0 : $bnsum->sub_total_vat_excluded;
            $collected_amount = $bnsum->collected_amount === null ? 0 : $bnsum->collected_amount;

            if($partials_type == "A"){
                $this->data["unpaid_amount"] = number_format($quotation_sub_total - $sub_total_vat_excluded, 2);
            }else{
                $this->data["unpaid_amount"] = number_format((($quotation_sub_total - $sub_total_vat_excluded) / $quotation_sub_total) * 100, 2);
            }

            $db->where("id", $quotation_id);
            $db->where("deleted", 0);
            if($collected_amount >= $quotation_total){
                $db->update("quotation", ["status"=>"I"]);
            }else{
                $db->update("quotation", ["status"=>"P"]);
            }
        }

        $this->data["sub_total_before_discount"] = number_format($sub_total_before_discount, 2);
        $this->data["discount_type"] = $discount_type;
        $this->data["discount_percent"] = number_format($discount_percent, 2);
        $this->data["discount_amount"] = number_format($discount_amount, 2);
        $this->data["sub_total"] = number_format($sub_total, 2);
        $this->data["is_partial_billing"] = $is_partial_billing;
        $this->data["partials_type"] = $partials_type;
        $this->data["partials_percent"] = ($partials_percent !== null) ? number_format($partials_percent, 2) : null;
        $this->data["partials_amount"] = ($partials_amount !== null) ? number_format($partials_amount, 2) : null;
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
        
        $bnrow = $db->select("id, quotation_id, status")
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

        $this->db->trans_begin();

        if($updateStatusTo == "A"){
            if($currentStatus == "I" || $currentStatus == "V"){
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

        }elseif($updateStatusTo == "I"){
            if($currentStatus == "V"){
                $this->data["dataset"] = $this->getIndexDataSetHTML($bnrow);
                return $this->data;
            }

            $db->where("id", $docId);
            $db->where("deleted", 0);
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
            $invrow = $db->select("doc_number")
                        ->from("invoice")
                        ->where("billing_note_id", $billing_note_id)
                        ->where("status !=", "V")
                        ->where("deleted", 0)
                        ->get()->row();

            if(!empty($invrow)){
                $this->data["dataset"] = $this->getIndexDataSetHTML($bnrow);
                $this->data["message"] = "ไม่สามารถยกเลิกใบวางบิลได้ เนื่องจากมีการผูกใบวางบิลกับใบกำกับภาษีเลขที่ ".$invrow->doc_number." แล้ว";
                $this->data["status"] = "error";
                $db->trans_rollback();
                return $this->data;
            }

            $db->where("id", $docId);
            $db->update("billing_note", ["status"=>"V"]);

            $db->where("id", $bnrow->quotation_id);
            if($db->count_all_results("quotation") > 0){
                $db->where("id", $bnrow->quotation_id);
                $db->update("quotation", ["approved_by"=>null, "approved_datetime"=>null, "status"=>"W"]);
            }
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

    function createDocByInvoiceId($invoice_id){
        $db = $this->db;

        $ivrow = $db->select("doc_number")
                    ->from("invoice")
                    ->where("invoice_id", $invoice_id)
                    ->where("doc_type", "TIV")
                    ->where_in("status", ["W", "A"])
                    ->where("deleted", 0)
                    ->get()->row();

        if(!empty($ivrow)){
            $this->data["message"] = "ไม่สามารถอกใบวางบิลได้ เนื่องจากเอกสาร ".$ivrow->doc_number." ถูกออกใบกำกับภาษีเรียบร้อยแล้ว";
            return $this->data;
        }

        $ivrow = $db->select("*")
                    ->from("invoice")
                    ->where("id", $invoice_id)
                    ->where_in("status", ["O", "P"])
                    ->where("doc_type", "IV")
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($ivrow)) return $this->data;

        $ivirows = $db->select("*")
                        ->from("invoice_items")
                        ->where("invoice_id", $invoice_id)
                        ->order_by("sort", "asc")
                        ->get()->result();

        $invoice_id = $ivrow->id;
        $doc_type = "TIV";
        $doc_number = $this->getNewDocNumber();
        $doc_date = date("Y-m-d");
        $credit = $ivrow->credit;
        $due_date = $ivrow->due_date;
        $reference_number = $ivrow->doc_number;
        $project_id = $ivrow->project_id;
        $client_id = $ivrow->client_id;
        $sub_total_before_discount = $ivrow->sub_total_before_discount;
        $discount_type = $ivrow->discount_type;
        $discount_percent = $ivrow->discount_percent;
        $discount_amount = $ivrow->discount_amount;
        $sub_total = $ivrow->sub_total;
        $vat_inc = $ivrow->vat_inc;
        $vat_percent = $ivrow->vat_percent;
        $vat_value = $ivrow->vat_value;
        $total = $ivrow->total;
        $wht_inc = $ivrow->wht_inc;
        $wht_percent = $ivrow->wht_percent;
        $wht_value = $ivrow->wht_value;
        $payment_amount = $ivrow->payment_amount;
        $remark = $ivrow->remark;
        $created_by = $this->login_user->id;
        $created_datetime = date("Y-m-d H:i:s");

        $db->trans_begin();
        
        $db->insert("invoice", [
                                    "invoice_id"=>$invoice_id,
                                    "doc_type"=>$doc_type,
                                    "doc_number"=>$doc_number,
                                    "doc_date"=>$doc_date,
                                    "credit"=>$credit,
                                    "due_date"=>$due_date,
                                    "reference_number"=>$reference_number,
                                    "project_id"=>$project_id,
                                    "client_id"=>$client_id,
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
                                    "payment_amount"=>$payment_amount,
                                    "remark"=>$remark,
                                    "created_by"=>$created_by,
                                    "created_datetime"=>$created_datetime,
                                    "tax_invoice_status"=>"W",
                                    "deleted"=>0
                                ]);

        $invoice_id = $db->insert_id();

        if(!empty($ivirows)){
            foreach($ivirows as $ivirow){
                $db->insert("invoice_items", [
                                                "invoice_id"=>$invoice_id,
                                                "product_id"=>$ivirow->product_id,
                                                "product_name"=>$ivirow->product_name,
                                                "product_description"=>$ivirow->product_description,
                                                "quantity"=>$ivirow->quantity,
                                                "unit"=>$ivirow->unit,
                                                "price"=>$ivirow->price,
                                                "total_price"=>$ivirow->total_price,
                                                "sort"=>$ivirow->sort
                                            ]);
            }
        }

        $this->data["doc_id"] = $invoice_id;

        if ($db->trans_status() === FALSE){
            $db->trans_rollback();
            return $this->data;
        }

        $db->trans_commit();

        $this->data["url"] = get_uri("tax-invoices/view/".$invoice_id);
        $this->data["status"] = "success";
        $this->data["message"] = "success";

        return $this->data;
    }

    function getHTMLInvoices() {
        $db = $this->db;
        $customer_id = $this->json->customer_id;

        if(!isset($customer_id)) return ["html"=>"<tr class='norecord'><td colspan='8'>กรุณาเลือกชื่อลูกค้า เพื่อค้นหาเอกสาร</td></tr>"];
        if($customer_id == "") return ["html"=>"<tr class='norecord'><td colspan='8'>กรุณาเลือกชื่อลูกค้า เพื่อค้นหาเอกสาร</td></tr>"];

        $ivrows = $db->select("*")
                        ->from("invoice")
                        ->where_in("status", ["O", "P"])
                        ->where_in("doc_type", ["IV", "IVT"])
                        ->where("client_id", $customer_id)
                        ->where("deleted", 0)
                        ->order_by("doc_number", "desc")
                        ->get()->result();

        $html = "<tr class='norecord'><td colspan='8'>ไม่พบข้อมูลใบแจ้งหนี้</td></tr>";

        if(!empty($ivrows)){
            $html = "";
            foreach($ivrows as $ivrow){
                $html .= "<tr>";
                    $html .= "<td>".$ivrow->doc_number."</td>";
                    $html .= "<td>".convertDate($ivrow->doc_date, true)."</td>";
                    $html .= "<td>".convertDate($ivrow->due_date, true)."</td>";
                    $html .= "<td>".number_format($ivrow->total, 2)."</td>";
                    $html .= "<td>".number_format($ivrow->payment_amount, 2)."</td>";
                    $html .= "<td>".($ivrow->wht_inc == 'Y'?$ivrow->wht_value:'ไม่ระบุ')."</td>";
                    $html .= "<td>".number_format($ivrow->payment_amount, 2)."</td>";
                    //$html .= "<td><a data-invoice_id='".$ivrow->id."' class='choose-inv-button custom-color-button'>เลือก</a></td>";
                    $html .= "<td><input type='checkbox' name='invoice_numbers[]' value='".$ivrow->id."'></td>";
                $html .= "</tr>";
            }
        }

        $data["html"] = $html;

        return $data;
    }
}