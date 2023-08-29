<?php

class Tax_invoices_m extends MY_Model {
    private $code = "TIV";
    private $shareHtmlAddress = "share/tax-invoice/html/";

    function __construct() {
        parent::__construct();
    }

    function getCode(){
        return $this->code;
    }

    function getNewDocNumber(){
        $this->db->where_in("status", ["W", "A"]);
        $this->db->where("DATE_FORMAT(created_datetime,'%Y-%m')", date("Y-m"));
        $this->db->where("deleted", 0);
        $running_number = $this->db->get("tax_invoice")->num_rows() + 1;

        $doc_number = $this->getCode().date("Ym").sprintf("%04d", $running_number);

        return $doc_number;
    }

    function getStatusName($status_code){
        if($status_code == "W"){
            return "รออนุมัติ";
        }elseif($status_code == "A"){
            return "อนุมัติ";
        }elseif($status_code == "V"){
            return "ยกเลิก";
        }
    }

    function getIndexDataSetHTML($tivrow){
        $doc_buttons = $doc_status = "";

        $module = "tax-invoices";
        $doc_status = "<select class='dropdown_status' data-doc_id='".$tivrow->id."' data-doc_number='".$tivrow->doc_number."'>";
        $doc_buttons = "";

        if($tivrow->status == "W"){
            $doc_status .= "<option selected>รออนุมัติ</option>";
            $doc_status .= "<option value='A'>อนุมัติ</option>";
            $doc_status .= "<option value='V'>ยกเลิก</option>";
        }elseif($tivrow->status == "A"){
            $doc_status .= "<option selected>อนุมัติ</option>";
            $doc_status .= "<option value='V'>ยกเลิก</option>";
        }elseif($tivrow->status == "V"){
            $doc_status .= "<option selected>ยกเลิก</option>";
        }

        $doc_status .= "</select>";
        
        $reference_number_column = $tivrow->reference_number;
        if($tivrow->invoice_id != null) $reference_number_column = "<a href='".get_uri("invoices/view/".$tivrow->invoice_id)."'>".$tivrow->reference_number."</a>";

        $data = [
                    "<a href='".get_uri($module."/view/".$tivrow->id)."'>".convertDate($tivrow->doc_date, 2)."</a>",
                    "<a href='".get_uri($module."/view/".$tivrow->id)."'>".$tivrow->doc_number."</a>",
                    $reference_number_column,
                    "<a href='".get_uri("clients/view/".$tivrow->client_id)."'>".$this->Clients_m->getCompanyName($tivrow->client_id)."</a>",
                    convertDate($tivrow->due_date, true), number_format($tivrow->total, 2), $doc_status,
                    $doc_buttons
                ];

        return $data;
    }

    function indexDataSet() {
        $db = $this->db;

        $db->select("*")->from("tax_invoice");

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
        $company_setting = $this->Settings_m->getCompany();

        $this->data["doc_id"] = null;
        $this->data["billing_type"] = "";
        $this->data["invoice_id"] = null;
        $this->data["doc_number"] = null;
        $this->data["doc_date"] = date("Y-m-d");
        $this->data["credit"] = "0";
        $this->data["due_date"] = date("Y-m-d");
        $this->data["reference_number"] = "";
        $this->data["discount_type"] = "P";
        $this->data["discount_percent"] = 0;
        $this->data["discount_amount"] = 0;
        $this->data["vat_inc"] = "N";
        $this->data["wht_inc"] = "N";
        $this->data["sub_total"] = 0;//ยอด ไม่รวม VAT
        $this->data["total"] = 0;//ยอดรวม VAT
        $this->data["project_id"] = null;
        $this->data["client_id"] = null;
        $this->data["lead_id"] = null;
        $this->data["remark"] = null;
        $this->data["created_by"] = null;
        $this->data["created_datetime"] = null;
        $this->data["approved_by"] = null;
        $this->data["approved_datetime"] = null;
        $this->data["company_stamp"] = null;
        $this->data["doc_status"] = null;

        if(!empty($docId)){
            $tivrow = $db->select("*")
                            ->from("tax_invoice")
                            ->where("id", $docId)
                            ->where("deleted", 0)
                            ->get()->row();

            if(empty($tivrow)) return $this->data;

            $lead_id = $client_id = null;
            
            if($this->Customers_m->isLead($tivrow->client_id) == true){
                $this->data["customer_id"] = $lead_id = $tivrow->client_id;
                $this->data["customer_is_lead"] = 1;
            }else{
                $this->data["customer_id"] = $client_id = $tivrow->client_id;
                $this->data["customer_is_lead"] = 0;
            }

            $this->data["doc_id"] = $docId;
            $this->data["billing_type"] = $tivrow->billing_type;
            $this->data["invoice_id"] = $tivrow->invoice_id;
            $this->data["doc_number"] = $tivrow->doc_number;
            $this->data["share_link"] = $tivrow->sharekey != null ? get_uri($this->shareHtmlAddress."th/".$tivrow->sharekey) : null;
            $this->data["doc_date"] = $tivrow->doc_date;
            $this->data["credit"] = $tivrow->credit;
            $this->data["due_date"] = $tivrow->due_date;
            $this->data["reference_number"] = $tivrow->reference_number;
            $this->data["discount_type"] = $tivrow->discount_type;
            $this->data["discount_percent"] = $tivrow->discount_percent;
            $this->data["discount_amount"] = $tivrow->discount_amount;
            $this->data["vat_inc"] = $tivrow->vat_inc;
            $this->data["vat_percent"] = number_format_drop_zero_decimals($tivrow->vat_percent, 2)."%";
            $this->data["wht_inc"] = $tivrow->wht_inc;
            $this->data["sub_total"] = $tivrow->sub_total;
            $this->data["total"] = $tivrow->total;
            $this->data["project_id"] = $tivrow->project_id;
            $this->data["client_id"] = $client_id;
            $this->data["lead_id"] = $lead_id;
            $this->data["remark"] = $tivrow->remark;
            $this->data["created_by"] = $tivrow->created_by;
            $this->data["created_datetime"] = $tivrow->created_datetime;
            $this->data["approved_by"] = $tivrow->approved_by;
            $this->data["approved_datetime"] = $tivrow->approved_datetime;
            if($tivrow->approved_by != null) if(file_exists($_SERVER['DOCUMENT_ROOT']."/".$company_setting["company_stamp"])) $this->data["company_stamp"] = $company_setting["company_stamp"];
            $this->data["doc_status"] = $tivrow->status;
        }

        $this->data["status"] = "success";

        return $this->data;
    }

    function getEdoc($docId = null, $sharekey = null){
        $db = $this->db;
        $company_setting = $this->Settings_m->getCompany();
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

        $tivrow = $db->select("*")
                    ->from("tax_invoice")
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($tivrow)) return $this->data;

        $docId = $tivrow->id;

        $tivrows = $db->select("*")
                        ->from("tax_invoice_items")
                        ->where("tax_invoice_id", $docId)
                        ->order_by("sort", "asc")
                        ->get()->result();

        $client_id = $tivrow->client_id;
        $created_by = $tivrow->created_by;

        $this->data["seller"] = $ci->Users_m->getInfo($created_by);

        $this->data["buyer"] = $ci->Customers_m->getInfo($client_id);
        $this->data["buyer_contact"] = $ci->Customers_m->getContactInfo($client_id);
        $this->data["billing_type"] = $tivrow->billing_type;
        $this->data["doc_number"] = $tivrow->doc_number;
        $this->data["doc_date"] = $tivrow->doc_date;
        $this->data["credit"] = $tivrow->credit;
        $this->data["due_date"] = $tivrow->due_date;
        $this->data["reference_number"] = $tivrow->reference_number;
        $this->data["remark"] = $tivrow->remark;

        $this->data["sub_total_before_discount"] = $tivrow->sub_total_before_discount;

        $this->data["discount_type"] = $tivrow->discount_type;
        $this->data["discount_percent"] = $tivrow->discount_percent;
        $this->data["discount_amount"] = $tivrow->discount_amount;
        
        $this->data["sub_total"] = $tivrow->sub_total;

        $this->data["vat_inc"] = $tivrow->vat_inc;
        $this->data["vat_percent"] = $tivrow->vat_percent;
        $this->data["vat_value"] = $tivrow->vat_value;
        $this->data["total"] = $tivrow->total;
        $this->data["total_in_text"] = numberToText($tivrow->total);
        $this->data["wht_inc"] = $tivrow->wht_inc;
        $this->data["wht_percent"] = $tivrow->wht_percent;
        $this->data["wht_value"] = $tivrow->wht_value;
        $this->data["payment_amount"] = $tivrow->payment_amount;

        $this->data["sharekey_by"] = $tivrow->sharekey_by;
        $this->data["approved_by"] = $tivrow->approved_by;
        $this->data["approved_datetime"] = $tivrow->approved_datetime;

        if($tivrow->approved_by != null && file_exists($_SERVER['DOCUMENT_ROOT']."/".$company_setting["company_stamp"])){
            $this->data["company_stamp"] = $company_setting["company_stamp"];
        }

        $this->data["doc_status"] = $tivrow->status;

        $this->data["doc"] = $tivrow;
        $this->data["items"] = $tivrows;

        $this->data["status"] = "success";
        $this->data["message"] = "ok";

        return $this->data;
    }

    function updateDoc($docId = null){
        $db = $this->db;

        $tivrow = null;

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
            
            $tivrow = $db->select("*")
                        ->from("tax_invoice")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

            if(empty($tivrow)) return $this->data;

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
            $tivrow = $db->select("*")
                        ->from("tax_invoice")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

            if(empty($tivrow)) return $this->data;            

            $discount_type = $tivrow->discount_type;
            $discount_percent = $tivrow->discount_percent;
            $discount_amount = $tivrow->discount_amount;


            $vat_inc = $tivrow->vat_inc;
            $wht_inc = $tivrow->wht_inc;

            if($vat_inc == "Y") $vat_percent = $tivrow->vat_percent;
            if($wht_inc == "Y") $wht_percent = $tivrow->wht_percent;
        }
        
        $sub_total_before_discount = $db->select("SUM(total_price) AS SUB_TOTAL")
                                        ->from("tax_invoice_items")
                                        ->where("tax_invoice_id", $docId)
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
        $db->update("tax_invoice", [
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

    function deleteDoc(){
        $db = $this->db;
        $docId = $this->input->post("id");

        $tivrow = $db->select("status")
                        ->from("tax_invoice")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($tivrow)) return $this->data;

        if($tivrow->status != "W"){
            $this->data["success"] = false;
            $this->data["message"] = "คุณไม่สามารถลบเอกสารได้ เนื่องจากเอกสารมีการเปลี่ยนแปลงสถานะแล้ว";
            return $this->data;
        }

        $db->where("id", $docId);
        $db->update("tax_invoice", ["deleted"=>1]);

        $this->data["success"] = true;
        $this->data["message"] = lang('record_deleted');

        return $this->data;
    }

    function undoDoc(){
        $db = $this->db;
        $docId = $this->input->post("id");

        $db->where("id", $docId);
        $db->update("tax_invoice", ["deleted"=>0]);

        $tivrow = $db->select("*")
                    ->from("tax_invoice")
                    ->where("id", $docId)
                    ->get()->row();

        $data["success"] = true;
        $data["data"] = $this->getIndexDataSetHTML($tivrow);
        $data["message"] = lang('record_undone');

        return $data;
    }

    function items(){
        $db = $this->db;
        $doc_id = $this->json->doc_id;
        
        $tivrow = $db->select("id, status")
                        ->from("tax_invoice")
                        ->where_in("status", ["W", "A", "V"])
                        ->where("id", $doc_id)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($tivrow)) return $this->data;

        $tivirows = $db->select("*")
                        ->from("tax_invoice_items")
                        ->where("tax_invoice_id", $doc_id)
                        ->order_by("id", "asc")
                        ->get()->result();

        if(empty($tivirows)){
            $this->data["status"] = "notfound";
            $this->data["message"] = "ไม่พบข้อมูล";
            return $this->data;
        }

        $items = [];

        foreach($tivirows as $tivirow){
            $item["id"] = $tivirow->id;
            $item["product_name"] = $tivirow->product_name;
            $item["product_description"] = $tivirow->product_description;
            $item["quantity"] = $tivirow->quantity;
            $item["unit"] = $tivirow->unit;
            $item["price"] = number_format($tivirow->price, 2);
            $item["total_price"] = number_format($tivirow->total_price, 2);
            $items[] = $item;
        }

        $this->data["doc_status"] = $tivrow->status;
        $this->data["items"] = $items;
        $this->data["status"] = "success";

        return $this->data;
    }

    function item(){
        $db = $this->db;
        $docId = $this->input->post("doc_id");
        $itemId = $this->input->post("item_id");

        $tivrow = $db->select("id")
                        ->from("tax_invoice")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($tivrow)) return $this->data;

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

        $tivrow = $db->select("id")
                    ->from("tax_invoice")
                    ->where("id", $docId)
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($tivrow)) return $this->data;
      
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
        $company_setting = $this->Settings_m->getCompany();
        $docId = $this->json->doc_id;
        $updateStatusTo = $this->json->update_status_to;

        $tivrow = $db->select("*")
                    ->from("tax_invoice")
                    ->where("id",$docId)
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($tivrow)) return $this->data;
        if($tivrow->status == $updateStatusTo){
            $this->data["status"] = "notchange";
            $this->data["dataset"] = $this->getIndexDataSetHTML($tivrow);
            return $this->data;
        }

        $invoice_id = $this->data["doc_id"] = $docId;
        $invoice_number = $tivrow->doc_number;
        $currentStatus = $tivrow->status;

        $db->trans_begin();

        if($updateStatusTo == "A"){
            if($currentStatus == "V"){
                $this->data["dataset"] = $this->getIndexDataSetHTML($tivrow);
                return $this->data;
            }
            
            $company_stock_type = $company_setting["company_stock_type"];

            if($company_stock_type == "tax_invoice"){
                $item = [
                        "sale_id"=>$tivrow->id,
                        "sale_type"=>$this->code,
                        "sale_document"=>$tivrow->doc_number,
                        "project_id"=>$tivrow->project_id,
                        "created_by"=>$tivrow->created_by
                    ];

                $ivirows = $db->select("*")
                                ->from("tax_invoice_items")
                                ->where("invoice_id", $tivrow->id)
                                ->get()->result();

                $items = [];

                if(!empty($ivirows)){
                    foreach($ivirows as $ivirow){
                        if($ivirow->product_id != null){
                            $items[] = [
                                        "id"=>$ivirow->id,
                                        "item_id"=>$ivirow->product_id,
                                        "ratio"=>$ivirow->quantity
                                    ];
                        }
                    }
                }

                $item["items"] = $items;

                $bism = $this->Bom_item_stocks_model->processFinishedGoodsSale($item);
            }

            $db->where("id", $docId);
            $db->where("deleted", 0);
            $db->update("tax_invoice", [
                                        "approved_by"=>$this->login_user->id,
                                        "approved_datetime"=>date("Y-m-d H:i:s"),
                                        "status"=>"A"
                                    ]);

        }elseif($updateStatusTo == "V"){
            $rerow = $db->select("doc_number")
                        ->from("receipt")
                        ->where("invoice_id", $docId)
                        ->where_in("status", ["W", "P"])
                        ->get()->row();

            if(!empty($rerow)){
                $this->data["dataset"] = $this->getIndexDataSetHTML($tivrow);
                $this->data["message"] = "ไม่สามารถยกเลิกใบแจ้งหนี้ได้ เนื่องจากมีการผูกใบแจ้งหนี้กับใบเสร็จเลขที่ ".$rerow->doc_number." แล้ว";
                return $this->data;
            }

            $bism = $this->Bom_item_stocks_model->cancelFinishedGoodsSale(["sale_id"=>$docId, "sale_type"=>$this->code]);

            $db->where("id", $docId);
            $db->where("deleted", 0);
            $db->update("tax_invoice", ["status"=>"V"]);
        }

        if ($db->trans_status() === FALSE){
            $db->trans_rollback();
            $this->data["dataset"] = $this->getIndexDataSetHTML($tivrow);
            return $this->data;
        }

        $db->trans_commit();

        $this->data["status"] = "success";
        $this->data["message"] = lang("record_saved");

        if(isset($this->data["task"])) return $this->data;

        $tivrow = $db->select("*")
                    ->from("tax_invoice")
                    ->where("id",$docId)
                    ->where("deleted", 0)
                    ->get()->row();

        $this->data["dataset"] = $this->getIndexDataSetHTML($tivrow);
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
                if($db->count_all_results("tax_invoice") < 1) break;
            }

            $this->data["sharelink"] = get_uri($this->shareHtmlAddress."th/".$sharekey);
        }

        $db->where("id", $docId);
        $db->update("tax_invoice", ["sharekey"=>$sharekey, "sharekey_by"=>$sharekey_by]);

        return $this->data;
    }

    function createDocByInvoiceId($invoice_id){
        $db = $this->db;

        $tivrow = $db->select("doc_number")
                        ->from("tax_invoice")
                        ->where("invoice_id", $invoice_id)
                        ->where_in("status", ["W", "A"])
                        ->where("deleted", 0)
                        ->get()->row();

        if(!empty($tivrow)){
            $this->data["message"] = "ไม่สามารถอกใบกำกับภาษีได้ เนื่องจากเอกสาร ".$tivrow->doc_number." ถูกออกใบกำกับภาษีเรียบร้อยแล้ว";
            return $this->data;
        }

        $ivrow = $db->select("*")
                    ->from("invoice")
                    ->where("id", $invoice_id)
                    ->where_in("status", ["O", "P"])
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($ivrow)) return $this->data;

        $ivirows = $db->select("*")
                        ->from("invoice_items")
                        ->where("invoice_id", $invoice_id)
                        ->order_by("sort", "asc")
                        ->get()->result();

        $billing_type = $ivrow->billing_type;
        $invoice_id = $ivrow->id;
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
        
        $db->insert("tax_invoice", [
                                    "billing_type"=>$billing_type,
                                    "invoice_id"=>$invoice_id,
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
                                    "status"=>"W",
                                    "deleted"=>0
                                ]);

        $tax_invoice_id = $db->insert_id();

        if(!empty($ivirows)){
            foreach($ivirows as $ivirow){
                $db->insert("tax_invoice_items", [
                                                "tax_invoice_id"=>$invoice_id,
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

        if(!isset($customer_id)) return ["html"=>"<tr class='norecord'><td colspan='6'>กรุณาเลือกชื่อลูกค้า เพื่อค้นหาเอกสาร</td></tr>"];
        if($customer_id == "") return ["html"=>"<tr class='norecord'><td colspan='6'>กรุณาเลือกชื่อลูกค้า เพื่อค้นหาเอกสาร</td></tr>"];

        $ivrows = $db->select("*")
                        ->from("invoice")
                        ->where_in("status", ["O", "P"])
                        ->where("client_id", $customer_id)
                        ->where("deleted", 0)
                        ->order_by("doc_number", "desc")
                        ->get()->result();

        $html = "<tr class='norecord'><td colspan='6'>ไม่พบข้อมูลใบแจ้งหนี้</td></tr>";

        if(!empty($ivrows)){
            $html = "";
            foreach($ivrows as $ivrow){
                $db->where_in("status", ["W", "A"]);
                $db->where("invoice_id", $ivrow->id);
                $db->where("deleted", 0);
                if($db->count_all_results("tax_invoice") > 0) continue;

                $html .= "<tr>";
                    $html .= "<td>".convertDate($ivrow->doc_date, true)."</td>";
                    $html .= "<td>".$ivrow->doc_number."</td>";
                    $html .= "<td>".$this->Clients_m->getCompanyName($ivrow->client_id)."</td>";
                    $html .= "<td>".$ivrow->total."</td>";
                    $html .= "<td>".$this->Invoices_m->getStatusName($ivrow->status)."</td>";
                    $html .= "<td><a data-invoice_id='".$ivrow->id."' class='choose-inv-button custom-color-button'>เลือก</a></td>";
                $html .= "</tr>";
            }
        }

        $data["html"] = $html;

        return $data;
    }
}