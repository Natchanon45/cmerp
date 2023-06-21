<?php

class Quotations_m extends MY_Model {
    private $code = "QT";
    private $shareHtmlAddress = "share/quotation/html/";

    function __construct() {
        parent::__construct();
    }

    function getCode(){
        return $this->code;
    }

    function getNewDocNumber(){
        $this->db->where("DATE_FORMAT(created_datetime,'%Y-%m')", date("Y-m"));
        $this->db->where("deleted", 0);
        $running_number = $this->db->get("quotation")->num_rows() + 1;

        $doc_number = $this->getCode().date("Ym").sprintf("%04d", $running_number);

        return $doc_number;
    }

    function getStatusName($status_code){
        if($status_code == "W"){
            return "รออนุมัติ";
        }
    }

    function getIndexDataSetHTML($qrow){
        $doc_status = "<select class='dropdown_status' data-doc_id='".$qrow->id."'>";

        if($qrow->status == "W"){
            $doc_status .= "<option selected>รออนุมัติ</option>";
            $doc_status .= "<option value='A'>อนุมัติ</option>";
            $doc_status .= "<option value='B'>สร้างใบวางบิล</option>";
            $doc_status .= "<option value='P'>แบ่งจ่ายใบวางบิล</option>";
            $doc_status .= "<option value='R'>ไม่อนุมัติ</option>";
        }elseif($qrow->status == "A"){
            $doc_status .= "<option selected>อนุมัติ</option>";
            $doc_status .= "<option value='I'>ดำเนินการแล้ว</option>";
            $doc_status .= "<option value='B'>สร้างใบวางบิล</option>";
            $doc_status .= "<option value='P'>แบ่งจ่ายใบวางบิล</option>";
            $doc_status .= "<option value='R'>ไม่อนุมัติ</option>";
        }elseif($qrow->status == "R"){
            $doc_status .= "<option selected>ไม่อนุมัติ</option>";
        }elseif($qrow->status == "P"){
            $doc_status .= "<option selected>แบ่งจ่าย</option>";
            $doc_status .= "<option value='P'>แบ่งจ่ายใบวางบิล</option>";
        }elseif($qrow->status == "I"){
            $doc_status .= "<option selected>ดำเนินการแล้ว</option>";
        }

        $doc_status .= "</select>";

        $data = [
                    "<a href='".get_uri("quotations/view/".$qrow->id)."'>".convertDate($qrow->doc_date, true)."</a>",
                    "<a href='".get_uri("quotations/view/".$qrow->id)."'>".$qrow->doc_number."</a>",
                    $qrow->reference_number, "<a href='".get_uri("clients/view/".$qrow->client_id)."'>".$this->Clients_m->getCompanyName($qrow->client_id)."</a>",
                    convertDate($qrow->doc_date, true), number_format($qrow->total, 2), $doc_status,
                    "<a data-post-id='".$qrow->id."' data-action-url='".get_uri("quotations/addedit")."' data-act='ajax-modal' class='edit'><i class='fa fa-pencil'></i></a>"
                ];

        /*
        *Delete Button
        *<a data-id='".$qrow->id."' data-action-url='".get_uri("quotations/delete_doc")."' data-action='delete' class='delete'><i class='fa fa-times fa-fw'></i></a>
        */

        return $data;
    }

    function indexDataSet() {
        $db = $this->db;

        $db->select("*")->from("quotation");

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

        $qrows = $db->order_by("doc_number", "desc")->get()->result();

        $dataset = [];

        foreach($qrows as $qrow){
            $dataset[] = $this->getIndexDataSetHTML($qrow);
        }

        return $dataset;
    }

    function getDoc($docId){
        $db = $this->db;

        $this->data["doc_date"] = date("Y-m-d");
        $this->data["credit"] = "0";
        $this->data["doc_valid_until_date"] = date("Y-m-d");
        $this->data["reference_number"] = "";
        $this->data["discount_type"] = "P";
        $this->data["discount_percent"] = 0;
        $this->data["discount_amount"] = 0;
        $this->data["vat_inc"] = "N";
        $this->data["wht_inc"] = "N";
        $this->data["project_id"] = null;
        $this->data["customer_id"] = null;
        $this->data["client_id"] = null;
        $this->data["lead_id"] = null;
        $this->data["remark"] = null;
        $this->data["created_by"] = null;
        $this->data["created_datetime"] = null;
        $this->data["approved_by"] = null;
        $this->data["approved_datetime"] = null;
        $this->data["doc_status"] = NULL;

        if(!empty($docId)){
            $qrow = $db->select("*")
                        ->from("quotation")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

            if(empty($qrow)) return $this->data;

            $lead_id = $client_id = null;
            
            if($this->Customers_m->isLead($qrow->client_id) == true){
                $this->data["customer_id"] = $lead_id = $qrow->client_id;
                $this->data["customer_is_lead"] = 1;
            }else{
                $this->data["customer_id"] = $client_id = $qrow->client_id;
                $this->data["customer_is_lead"] = 0;
            }

            $this->data["doc_id"] = $docId;
            $this->data["doc_number"] = $qrow->doc_number;
            $this->data["share_link"] = $qrow->sharekey != null ? get_uri($this->shareHtmlAddress."th/".$qrow->sharekey) : null;
            $this->data["doc_date"] = $qrow->doc_date;
            $this->data["credit"] = $qrow->credit;
            $this->data["doc_valid_until_date"] = $qrow->doc_valid_until_date;
            $this->data["reference_number"] = $qrow->reference_number;
            $this->data["discount_type"] = $qrow->discount_type;
            $this->data["discount_percent"] = $qrow->discount_percent;
            $this->data["discount_amount"] = $qrow->discount_amount;
            $this->data["vat_inc"] = $qrow->vat_inc;
            $this->data["vat_percent"] = number_format_drop_zero_decimals($qrow->vat_percent, 2)."%";
            $this->data["wht_inc"] = $qrow->wht_inc;
            $this->data["project_id"] = $qrow->project_id;
            $this->data["client_id"] = $client_id;
            $this->data["lead_id"] = $lead_id;
            $this->data["remark"] = $qrow->remark;
            $this->data["created_by"] = $qrow->created_by;
            $this->data["created_datetime"] = $qrow->created_datetime;
            $this->data["approved_by"] = $qrow->approved_by;
            $this->data["approved_datetime"] = $qrow->approved_datetime;
            $this->data["doc_status"] = $qrow->status;
        }

        $this->data["status"] = "success";

        return $this->data;
    }

    function getEdoc($docId = null, $sharekey = null){
        $db = $this->db;
        $ci = get_instance();

        if($docId == null && $sharekey == null){
            return false;
        }

        if($docId != null && $sharekey == null){
            $db->where("id", $docId);
        }elseif($docId == null && $sharekey != null){
            $db->where("sharekey", $sharekey);
        }else{
            return false;
        }

        $db->where("deleted", 0);

        $qrow = $db->select("*")
                    ->from("quotation")
                    ->get()->row();

        if(empty($qrow)){
            return false;
        }

        $docId = $qrow->id;

        $qirows = $db->select("*")
                        ->from("quotation_items")
                        ->where("quotation_id", $docId)
                        ->order_by("sort", "asc")
                        ->get()->result();


        $client_id = $qrow->client_id;
        $created_by = $qrow->created_by;

        $data["seller"] = $ci->Users_m->getInfo($created_by);

        $data["buyer"] = $ci->Customers_m->getInfo($client_id);
        $data["buyer_contact"] = $ci->Customers_m->getContactInfo($client_id);

        $data["doc_number"] = $qrow->doc_number;
        $data["doc_date"] = $qrow->doc_date;
        $data["credit"] = $qrow->credit;
        $data["doc_valid_until_date"] = $qrow->doc_valid_until_date;
        $data["reference_number"] = $qrow->reference_number;
        $data["remark"] = $qrow->remark;


        $data["sub_total_before_discount"] = $qrow->sub_total_before_discount;

        
        $data["discount_type"] = $qrow->discount_type;
        $data["discount_percent"] = $qrow->discount_percent;
        $data["discount_amount"] = $qrow->discount_amount;
        
        
        $data["sub_total"] = $qrow->sub_total;

        $data["vat_inc"] = $qrow->vat_inc;
        $data["vat_percent"] = $qrow->vat_percent;
        $data["vat_value"] = $qrow->vat_value;
        $data["total"] = $qrow->total;
        $data["total_in_text"] = numberToText($qrow->total);
        $data["wht_inc"] = $qrow->wht_inc;
        $data["wht_percent"] = $qrow->wht_percent;
        $data["wht_value"] = $qrow->wht_value;
        $data["payment_amount"] = $qrow->payment_amount;

        $data["doc"] = $qrow;
        $data["items"] = $qirows;

        return $data;
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
            
            $qrow = $db->select("*")
                        ->from("quotation")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

            if(empty($qrow)) return $this->data;

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
            $qrow = $db->select("*")
                        ->from("quotation")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

            if(empty($qrow)) return $this->data;            

            $discount_type = $qrow->discount_type;
            $discount_percent = $qrow->discount_percent;
            $discount_amount = $qrow->discount_amount;


            $vat_inc = $qrow->vat_inc;
            $wht_inc = $qrow->wht_inc;

            if($vat_inc == "Y") $vat_percent = $qrow->vat_percent;
            if($wht_inc == "Y") $wht_percent = $qrow->wht_percent;
        }
        
        $sub_total_before_discount = $db->select("SUM(total_price) AS SUB_TOTAL")
                                        ->from("quotation_items")
                                        ->where("quotation_id", $docId)
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
                                                "field"=>"doc_valid_until_date",
                                                'label' => '',
                                                'rules' => 'required'
                                            ]
                                        ]);

        if ($this->form_validation->run() == FALSE){
            $this->data["status"] = "validate";
            if(form_error('doc_date') != null) $this->data["messages"]["doc_date"] = form_error('doc_date');
            if(form_error('doc_valid_until_date') != null) $this->data["messages"]["doc_valid_until_date"] = form_error('doc_valid_until_date');
        }

    }

    function saveDoc(){
        $db = $this->db;

        $this->validateDoc();
        if($this->data["status"] == "validate") return $this->data;

        $docId = $this->json->doc_id;
        $doc_date = convertDate($this->json->doc_date);
        $credit = intval($this->json->credit) < 0 ? 0:intval($this->json->credit);
        $doc_valid_until_date = date('Y-m-d', strtotime($doc_date." + ".$credit." days"));
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
            $qrow = $db->select("status")
                        ->from("quotation")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

            if(empty($qrow)){
                $this->data["success"] = false;
                $this->data["message"] = "ขออภัย เกิดข้อผิดพลาดระหว่างดำเนินการ! โปรดลองใหม่อีกครั้งในภายหลัง";
                return $this->data;
            }

            if($qrow->status != "W"){
                $this->data["success"] = false;
                $this->data["message"] = "ไม่สามารถบันทึกเอกสารได้เนื่องจากเอกสารมีการเปลี่ยนแปลงสถานะแล้ว";
                return $this->data;
            }

            $db->where("id", $docId);
            $db->where("deleted", 0);
            $db->update("quotation", [
                                        "doc_date"=>$doc_date,
                                        "credit"=>$credit,
                                        "doc_valid_until_date"=>$doc_valid_until_date,
                                        "reference_number"=>$reference_number,
                                        "client_id"=>$customer_id,
                                        "project_id"=>$project_id,
                                        "remark"=>$remark
                                    ]);
        }else{
            $doc_number = $this->getNewDocNumber();

            $db->insert("quotation", [
                                        "doc_number"=>$doc_number,
                                        "doc_date"=>$doc_date,
                                        "credit"=>$credit,
                                        "doc_valid_until_date"=>$doc_valid_until_date,
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
        
        $this->data["target"] = get_uri("quotations/view/". $docId);
        $this->data["status"] = "success";

        return $this->data;
    }

    function deleteDoc(){
        $db = $this->db;
        $docId = $this->input->post("id");

        $qrow = $db->select("status")
                        ->from("quotation")
                        ->where("id", $docId)
                        ->get()->row();

        if(empty($qrow)) return $this->data;

        $bnrow = $db->select("*")
                    ->from("billing_note")
                    ->where("quotation_id", $docId)
                    ->where("deleted", 0)
                    ->get()->row();

        if(!empty($bnrow)){
            $this->data["success"] = false;
            $this->data["message"] = "คุณไม่สามารถลบเอกสารได้ เนื่องจากเอกสารถูกอ้างอิงในใบวางบิลแล้ว";
            return $this->data;
        }

        if($qrow->status != "W"){
            $this->data["success"] = false;
            $this->data["message"] = "คุณไม่สามารถลบเอกสารได้ เนื่องจากเอกสารมีการเปลี่ยนแปลงสถานะแล้ว";
            return $this->data;
        }

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
        $data["data"] = $this->getIndexDataSetHTML($qrow);
        $data["message"] = lang('record_undone');

        return $data;
    }

    function items(){
        $db = $this->db;
        
        $qrow = $db->select("id, status")
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

        $this->data["doc_status"] = $qrow->status;
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
        $this->data["quantity"] = number_format(1, $this->Settings_m->getDecimalPlacesNumber());
        $this->data["unit"] = "";
        $this->data["price"] = number_format(0, 2);
        $this->data["total_price"] = number_format(0, 2);

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

        $qrow = $db->select("id")
                    ->from("quotation")
                    ->where("id", $docId)
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($qrow)) return $this->data;
        
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

        $this->updateDoc($docId);

        $this->data["target"] = get_uri("quotations/view/".$docId);
        $this->data["status"] = "success";

        return $this->data;

    }

    function deleteItem(){
        $db = $this->db;
        $docId = $this->json->doc_id;
        
        $db->where("id", $this->json->item_id);
        $db->where("quotation_id", $docId);
        $db->delete("quotation_items");

        if($db->affected_rows() != 1) return $this->data;

        $this->updateDoc($docId);

        $this->data["status"] = "success";

        return $this->data;
    }

    function updateStatus(){
        $db = $this->db;
        $docId = $this->json->doc_id;
        $updateStatusTo = $this->json->update_status_to;

        $qrow = $db->select("*")
                    ->from("quotation")
                    ->where("id",$docId)
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($qrow)) return $this->data;

        $quotation_id = $this->data["doc_id"] = $docId;
        $quotation_number = $qrow->doc_number;
        $quotation_is_partials = $qrow->is_partials;
        $quotation_partials_type = $qrow->partials_type;
        $currentStatus = $qrow->status;

        $quotation_sub_total_before_discount = $qrow->sub_total_before_discount;

        $quotation_discount_type = $qrow->discount_type;
        $quotation_discount_percent = $qrow->discount_percent;
        $quotation_discount_amount = $qrow->discount_amount;

        $quotation_sub_total = $qrow->sub_total;

        $quotation_vat_inc = $qrow->vat_inc;
        $quotation_vat_percent = $qrow->vat_percent;
        $quotation_vat_value = $qrow->vat_value;

        $quotation_wht_inc = $qrow->wht_inc;
        $quotation_wht_percent = $qrow->wht_percent;
        $quotation_wht_value = $qrow->wht_value;

        $quotation_total = $qrow->total;
        $quotation_payment_amount = $qrow->payment_amount;

        if($qrow->status == $updateStatusTo && $updateStatusTo != "P"){
            $this->data["dataset"] = $this->getIndexDataSetHTML($qrow);
            return $this->data;
        }

        $this->db->trans_begin();

        if($updateStatusTo == "A"){//Approved
            if($currentStatus == "R"){
                $this->data["dataset"] = $this->getIndexDataSetHTML($qrow);
                return $this->data;
            }

            $db->where("id", $quotation_id);
            $db->update("quotation", [
                                        "approved_by"=>$this->login_user->id,
                                        "approved_datetime"=>date("Y-m-d H:i:s"),
                                        "status"=>"A"
                                    ]);

        }elseif($updateStatusTo == "R"){//Refused
            $db->where("id", $quotation_id);
            $db->update("quotation", ["status"=>"R"]);

        }elseif($updateStatusTo == "I"){//Issued
            $db->where("id", $quotation_id);
            $db->update("quotation", ["status"=>"I"]);

        }elseif($updateStatusTo == "P" || $updateStatusTo == "B"){//Partial OR Create Billing Note
            $partials_percent = null;
            $partials_amount = 0;

            if($updateStatusTo == "P"){
                $billed_amount = $db->select("SUM(total) AS billed_amount")
                                        ->from("billing_note")
                                        ->where("quotation_id", $quotation_id)
                                        ->where("deleted", 0)
                                        ->get()->row()->billed_amount;

                if($billed_amount == null) $billed_amount = 0;

                if($billed_amount >= $quotation_total){
                    $db->where("id", $quotation_id);
                    $db->update("quotation", ["status"=>"I"]);
                    $db->trans_commit();
                    $this->data["message"] = "ไม่สามารถดำเนินการได้ เนื่องจากจำนวนเงินที่แบ่งจ่ายเกินมูลค่าของเอกสาร";
                    $this->data["dataset"] = $this->getIndexDataSetHTML($qrow);
                    return $this->data;
                }

                /*
                * ตอนเริ่มสร้าง BL ใบแรกจาก, quotation จะมีสถานะเริ่มแรกที่ไม่ได้เป็นแบบแบ่งจ่าย $quotation_is_partials == "N"
                * หลังจากสร้าง BL ไปแล้ว quotation จะเปลี่ยนสถานะเป็นแบบแบ่งจ่าย $quotation_is_partials == "Y"
                *
                * $this->json->patials_type มี 2 ประเภทคือ P OR A (Percent/Amount)
                */
                if($quotation_is_partials == "N" && $quotation_partials_type == null){
                    $db->where("id", $quotation_id);
                    $db->update("quotation", ["is_partials"=>"Y", "partials_type"=>$this->json->patials_type, "status"=>"P"]);

                    if($this->json->patials_type == "P") $partials_percent = 0;
                }else{
                    if($quotation_partials_type == "P") $partials_percent = 0;
                }

                $quotation_vat_value = 0;
                $quotation_total = 0;
                $quotation_wht_value = 0;
                $quotation_payment_amount = 0;

            }elseif($updateStatusTo == "B"){
                $db->where("id", $quotation_id);
                $db->update("quotation", ["is_partials"=>"N", "status"=>"I"]);
            }

            $billing_note_number = $this->Billing_notes_m->getNewDocNumber();
            $billing_date = date("Y-m-d");
            $billing_credit = $qrow->credit;
            $billing_due_date = date("Y-m-d", strtotime($billing_date. " + ".$billing_credit." days"));

            $db->insert("billing_note", [
                                            "quotation_id"=>$quotation_id,
                                            "doc_number"=>$billing_note_number,
                                            "doc_date"=>$billing_date,
                                            "credit"=>$billing_credit,
                                            "due_date"=>$billing_due_date,
                                            "reference_number"=>$quotation_number,
                                            "project_id"=>$qrow->project_id,
                                            "client_id"=>$qrow->client_id,
                                            "sub_total_before_discount"=>$quotation_sub_total_before_discount,
                                            "discount_type"=>$quotation_discount_type,
                                            "discount_percent"=>$quotation_discount_percent,
                                            "discount_amount"=>$quotation_discount_amount,
                                            "sub_total"=>$quotation_sub_total,
                                            "partials_percent"=>$partials_percent,
                                            "partials_amount"=>$partials_amount,
                                            "vat_inc"=>$quotation_vat_inc,
                                            "vat_percent"=>$quotation_vat_percent,
                                            "vat_value"=>$quotation_vat_value,
                                            "total"=>$quotation_total,
                                            "wht_inc"=>$quotation_wht_inc,
                                            "wht_percent"=>$quotation_wht_percent,
                                            "wht_value"=>$quotation_wht_value,
                                            "payment_amount"=>$quotation_payment_amount,
                                            "remark"=>$qrow->remark,
                                            "created_by"=>$this->login_user->id,
                                            "created_datetime"=>date("Y-m-d H:i:s"),
                                            "status"=>"W",
                                            "deleted"=>0
                                        ]);

            

            $billing_note_id = $db->insert_id();

            $qirows = $db->select("*")
                            ->from("quotation_items")
                            ->where("quotation_id", $quotation_id)
                            ->order_by("sort", "ASC")
                            ->get()->result();

            if(empty(!$qirows)){
                foreach($qirows as $qirow){
                    $db->insert("billing_note_items", [
                                                        "billing_note_id"=>$billing_note_id,
                                                        "product_id"=>$qirow->product_id,
                                                        "product_name"=>$qirow->product_name,
                                                        "product_description"=>$qirow->product_description,
                                                        "quantity"=>$qirow->quantity,
                                                        "unit"=>$qirow->unit,
                                                        "price"=>$qirow->price,
                                                        "total_price"=>$qirow->total_price,
                                                        "sort"=>$qirow->sort
                                                    ]);
                }
            }

            $this->data["task"] = "create_billing_note";
            $this->data["status"] = "success";
            $this->data["message"] = lang('record_saved');
            $this->data["url"] = get_uri("billing-notes/view/".$billing_note_id);

        }//end elseif $updateStatusTo == "P" || $updateStatusTo == "B"

        if ($db->trans_status() === FALSE){
            $db->trans_rollback();
            $this->data["dataset"] = $this->getIndexDataSetHTML($qrow);
            return $this->data;
        }

        $db->trans_commit();

        if(isset($this->data["task"])) return $this->data;

        $qrow = $db->select("*")
                    ->from("quotation")
                    ->where("id",$docId)
                    ->where("deleted", 0)
                    ->get()->row();

        $this->data["dataset"] = $this->getIndexDataSetHTML($qrow);
        $this->data["status"] = "success";
        $this->data["message"] = lang('record_saved');
        return $this->data;
    }

    function getTotalDocPartialBillingNote(){
        $db = $this->db;
        $docId = $this->json->doc_id;
                    
        $db->where("quotation_id",$docId);
        $db->where("deleted", 0);
        $totalPartialBillingNote = $db->count_all_results("billing_note");

        if($totalPartialBillingNote == null) $totalPartialBillingNote = 0;

        $this->data["total_billing_note"] = $totalPartialBillingNote;
        $this->data["status"] = "success";
        $this->data["message"] = "success";

        return $this->data;

    }

    function genShareKey(){
        $db = $this->db;
        $docId = $this->json->doc_id;
        $genKey = $this->json->gen_key;
        
        $sharekey = null;

        if($genKey == true){
            $sharekey = "";
            while(true){
                $sharekey = uniqid();
                $db->where("sharekey", $sharekey);
                if($db->count_all_results("quotation") < 1) break;
            }

            $this->data["sharelink"] = get_uri($this->shareHtmlAddress."th/".$sharekey);
        }

        $db->where("id", $docId);
        $db->update("quotation", ["sharekey"=>$sharekey]);

        return $this->data;
    }
}
