<?php
class Sales_orders_m extends MY_Model {
    private $code = "SO";
    private $shareHtmlAddress = "share/sales-order/html/";

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

    function getIndexDataSetHTML($sorow){
        $company_setting = $this->Settings_m->getCompany();

        $doc_status = "<select class='dropdown_status' data-doc_id='".$sorow->id."'>";

        if($sorow->status == "W"){
            $doc_status .= "<option selected>รออนุมัติ</option>";
            $doc_status .= "<option value='A'>อนุมัติ</option>";
            $doc_status .= "<option value='R'>ไม่อนุมัติ</option>";
        }elseif($sorow->status == "A"){
            $doc_status .= "<option selected>อนุมัติ</option>";

            if($company_setting["company_billing_type"] == 3 || $company_setting["company_billing_type"] == 6){
                $doc_status .= "<option value='I'>ออกใบเสร็จรับเงิน</option>";
            }else{
                $doc_status .= "<option value='I'>ออกใบแจ้งหนี้</option>";
            }

            $doc_status .= "<option value='RESET'>รีเซ็ต</option>";
        }elseif($sorow->status == "R"){
            $doc_status .= "<option selected>ไม่อนุมัติ</option>";
            $doc_status .= "<option value='RESET'>รีเซ็ต</option>";
        }elseif($sorow->status == "I"){
            $doc_status .= "<option selected>ดำเนินการแล้ว</option>";
            $doc_status .= "<option value='RESET'>รีเซ็ต</option>";
        }

        $doc_status .= "</select>";

        $data = [
                    "<a href='".get_uri("quotations/view/".$sorow->id)."'>".convertDate($sorow->doc_date, true)."</a>",
                    "<a href='".get_uri("quotations/view/".$sorow->id)."'>".$sorow->doc_number."</a>",
                    $sorow->reference_number, "<a href='".get_uri("clients/view/".$sorow->client_id)."'>".$sorow->project_title."</a>",
                    $this->Clients_m->getCompanyName($sorow->client_id), $doc_status,
                    "<a data-post-id='".$sorow->id."' data-action-url='".get_uri("quotations/addedit")."' data-act='ajax-modal' class='edit'><i class='fa fa-pencil'></i></a>"
                ];

        return $data;
    }

    function indexDataSet() {
        $db = $this->db;
        $company_setting = $this->Settings_m->getCompany();

        $db->select("*")->from("sales_order");
        $db->where("billing_type", $company_setting["company_billing_type"]);

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

        $sorows = $db->order_by("doc_number", "desc")->get()->result();

        $dataset = [];

        foreach($sorows as $sorow){
            $dataset[] = $this->getIndexDataSetHTML($sorow);
        }

        return $dataset;
    }

    function getDoc($docId){
        $db = $this->db;
        $company_setting = $this->Settings_m->getCompany();

        $this->data["doc_id"] = null;
        $this->data["billing_type"] = null;
        $this->data["doc_date"] = date("Y-m-d");
        $this->data["reference_number"] = null;
        $this->data["project_id"] = null;
        $this->data["customer_id"] = null;
        $this->data["client_id"] = null;
        $this->data["lead_id"] = null;
        $this->data["project_title"] = null;
        $this->data["project_description"] = null;
        $this->data["project_start_date"] = null;
        $this->data["project_deadline"] = null;
        $this->data["project_price"] = 0;
        $this->data["remark"] = null;
        $this->data["created_by"] = null;
        $this->data["created_datetime"] = null;
        $this->data["approved_by"] = null;
        $this->data["approved_datetime"] = null;
        $this->data["company_stamp"] = null;
        $this->data["doc_status"] = null;

        if(!empty($docId)){
            $sorow = $db->select("*")
                        ->from("sales_order")
                        ->where("id", $docId)
                        ->where("billing_type", $company_setting["company_billing_type"])
                        ->where("deleted", 0)
                        ->get()->row();

            if(empty($sorow)) return $this->data;

            $lead_id = $client_id = null;
            
            if($this->Customers_m->isLead($sorow->client_id) == true){
                $this->data["customer_id"] = $lead_id = $sorow->client_id;
                $this->data["customer_is_lead"] = 1;
            }else{
                $this->data["customer_id"] = $client_id = $sorow->client_id;
                $this->data["customer_is_lead"] = 0;
            }

            $this->data["doc_id"] = $docId;
            $this->data["billing_type"] = $sorow->billing_type;
            $this->data["doc_date"] = date("Y-m-d");
            $this->data["doc_number"] = $sorow->doc_number;
            $this->data["share_link"] = $sorow->sharekey != null ? get_uri($this->shareHtmlAddress."th/".$sorow->sharekey) : null;

            $this->data["reference_number"] = "";
            $this->data["project_id"] = null;
            $this->data["customer_id"] = null;
            $this->data["client_id"] = null;
            $this->data["lead_id"] = null;
            $this->data["project_title"] = $sorow->project_title;
            $this->data["project_description"] = $sorow->project_description;
            $this->data["project_start_date"] = $sorow->project_start_date;
            $this->data["project_deadline"] = $sorow->project_deadline;
            $this->data["project_price"] = $sorow->project_price;
            
            $this->data["remark"] = $sorow->remark;
            $this->data["created_by"] = $sorow->created_by;
            $this->data["created_datetime"] = $sorow->created_datetime;
            $this->data["approved_by"] = $sorow->approved_by;
            $this->data["approved_datetime"] = $sorow->approved_datetime;
            if(file_exists($_SERVER['DOCUMENT_ROOT']."/".$company_setting["company_stamp"])) $this->data["company_stamp"] = $company_setting["company_stamp"];
            $this->data["doc_status"] = $sorow->status;
            
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

        $sorow = $db->select("*")
                    ->from("quotation")
                    ->where("billing_type", $company_setting["company_billing_type"])
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($sorow)) return $this->data;

        $docId = $sorow->id;

        $qirows = $db->select("*")
                        ->from("quotation_items")
                        ->where("quotation_id", $docId)
                        ->order_by("sort", "asc")
                        ->get()->result();

        $client_id = $sorow->client_id;
        $created_by = $sorow->created_by;

        $this->data["seller"] = $ci->Users_m->getInfo($created_by);

        $this->data["buyer"] = $ci->Customers_m->getInfo($client_id);
        $this->data["buyer_contact"] = $ci->Customers_m->getContactInfo($client_id);

        $this->data["doc_number"] = $sorow->doc_number;
        $this->data["doc_date"] = $sorow->doc_date;
        $this->data["credit"] = $sorow->credit;
        $this->data["doc_valid_until_date"] = $sorow->doc_valid_until_date;
        $this->data["reference_number"] = $sorow->reference_number;
        $this->data["remark"] = $sorow->remark;

        $this->data["sub_total_before_discount"] = $sorow->sub_total_before_discount;

        $this->data["discount_type"] = $sorow->discount_type;
        $this->data["discount_percent"] = $sorow->discount_percent;
        $this->data["discount_amount"] = $sorow->discount_amount;
        
        $this->data["sub_total"] = $sorow->sub_total;

        $this->data["vat_inc"] = $sorow->vat_inc;
        $this->data["vat_percent"] = $sorow->vat_percent;
        $this->data["vat_value"] = $sorow->vat_value;
        $this->data["total"] = $sorow->total;
        $this->data["total_in_text"] = numberToText($sorow->total);
        $this->data["wht_inc"] = $sorow->wht_inc;
        $this->data["wht_percent"] = $sorow->wht_percent;
        $this->data["wht_value"] = $sorow->wht_value;
        $this->data["payment_amount"] = $sorow->payment_amount;

        $this->data["sharekey_by"] = $sorow->sharekey_by;

        $this->data["created_by"] = $sorow->created_by;
        $this->data["created_datetime"] = $sorow->created_datetime;
        $this->data["approved_by"] = $sorow->approved_by;
        $this->data["approved_datetime"] = $sorow->approved_datetime;
        if(file_exists($_SERVER['DOCUMENT_ROOT']."/".$company_setting["company_stamp"])) $this->data["company_stamp"] = $company_setting["company_stamp"];
        $this->data["doc_status"] = $sorow->status;

        $this->data["doc"] = $sorow;
        $this->data["items"] = $qirows;

        $this->data["status"] = "success";
        $this->data["message"] = "ok";

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
            
            $sorow = $db->select("*")
                        ->from("quotation")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

            if(empty($sorow)) return $this->data;

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
            $sorow = $db->select("*")
                        ->from("quotation")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

            if(empty($sorow)) return $this->data;            

            $discount_type = $sorow->discount_type;
            $discount_percent = $sorow->discount_percent;
            $discount_amount = $sorow->discount_amount;


            $vat_inc = $sorow->vat_inc;
            $wht_inc = $sorow->wht_inc;

            if($vat_inc == "Y") $vat_percent = $sorow->vat_percent;
            if($wht_inc == "Y") $wht_percent = $sorow->wht_percent;
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
                                            ]
                                        ]);

        if ($this->form_validation->run() == FALSE){
            $this->data["status"] = "validate";
            if(form_error('doc_date') != null) $this->data["messages"]["doc_date"] = form_error('doc_date');
        }

    }

    function saveDoc(){
        $db = $this->db;
        $company_setting = $this->Settings_m->getCompany();

        $this->validateDoc();
        if($this->data["status"] == "validate") return $this->data;

        $docId = $this->json->doc_id;
        $doc_date = convertDate($this->json->doc_date);
        $reference_number = $this->json->reference_number;
        $project_title = $this->json->project_title;
        $client_id = $this->json->client_id;
        $lead_id = $this->json->lead_id;
        $project_description = $this->json->project_description;
        $project_start_date = convertDate($this->json->project_start_date);
        $project_deadline = convertDate($this->json->project_deadline);
        $project_price = getNumber($this->json->project_price);
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
            $sorow = $db->select("status")
                        ->from("sales_order")
                        ->where("id", $docId)
                        ->where("billing_type", $company_setting["company_billing_type"])
                        ->where("deleted", 0)
                        ->get()->row();

            if(empty($sorow)){
                $this->data["success"] = false;
                $this->data["message"] = "ขออภัย เกิดข้อผิดพลาดระหว่างดำเนินการ! โปรดลองใหม่อีกครั้งในภายหลัง";
                return $this->data;
            }

            if($sorow->status != "W"){
                $this->data["success"] = false;
                $this->data["message"] = "ไม่สามารถบันทึกเอกสารได้เนื่องจากเอกสารมีการเปลี่ยนแปลงสถานะแล้ว";
                return $this->data;
            }

            $db->where("id", $docId);
            $db->where("deleted", 0);
            $db->update("sales_order", [
                                        "doc_date"=>$doc_date,
                                        "reference_number"=>$reference_number,
                                        "client_id"=>$customer_id,
                                        "project_title"=>($project_id != null ? $project:null),
                                        "remark"=>$remark
                                    ]);
        }else{
            $doc_number = $this->getNewDocNumber();
        
            $db->insert("quotation", [
                                        "billing_type"=>$company_setting["company_billing_type"],
                                        "doc_number"=>$doc_number,
                                        "doc_date"=>$doc_date,
                                        "credit"=>$credit,
                                        "doc_valid_until_date"=>$doc_valid_until_date,
                                        "reference_number"=>$reference_number,
                                        "vat_inc"=>$company_setting["company_vat_registered"],
                                        "client_id"=>$customer_id,
                                        "project_id"=>($project_id != null ? $project:null),
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

        $sorow = $db->select("status")
                        ->from("quotation")
                        ->where("id", $docId)
                        ->get()->row();

        if(empty($sorow)) return $this->data;

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

        if($sorow->status != "W"){
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

        $sorow = $db->select("*")
                    ->from("quotation")
                    ->where("id", $docId)
                    ->get()->row();

        $data["success"] = true;
        $data["data"] = $this->getIndexDataSetHTML($sorow);
        $data["message"] = lang('record_undone');

        return $data;
    }

    function items(){
        $db = $this->db;
        
        $sorow = $db->select("id, status")
                        ->from("quotation")
                        ->where("id", $this->json->doc_id)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($sorow)) return $this->data;

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

        $this->data["doc_status"] = $sorow->status;
        $this->data["items"] = $items;
        $this->data["status"] = "success";

        return $this->data;
    }

    function item(){
        $db = $this->db;
        $docId = $this->input->post("doc_id");
        $itemId = $this->input->post("item_id");

        $sorow = $db->select("id")
                        ->from("quotation")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($sorow)) return $this->data;

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

        $sorow = $db->select("id")
                    ->from("quotation")
                    ->where("id", $docId)
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($sorow)) return $this->data;
        
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
        $company_setting = $this->Settings_m->getCompany();
        $docId = $this->json->doc_id;
        $updateStatusTo = $this->json->update_status_to;

        $sorow = $db->select("*")
                    ->from("quotation")
                    ->where("id",$docId)
                    ->where("billing_type", $company_setting["company_billing_type"])
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($sorow)) return $this->data;

        $quotation_billing_type = $sorow->billing_type;
        $quotation_id = $this->data["doc_id"] = $docId;
        $quotation_number = $sorow->doc_number;
        $currentStatus = $sorow->status;

        $quotation_sub_total_before_discount = $sorow->sub_total_before_discount;

        $quotation_discount_type = $sorow->discount_type;
        $quotation_discount_percent = $sorow->discount_percent;
        $quotation_discount_amount = $sorow->discount_amount;

        $quotation_sub_total = $sorow->sub_total;

        $quotation_vat_inc = $sorow->vat_inc;
        $quotation_vat_percent = $sorow->vat_percent;
        $quotation_vat_value = $sorow->vat_value;

        $quotation_wht_inc = $sorow->wht_inc;
        $quotation_wht_percent = $sorow->wht_percent;
        $quotation_wht_value = $sorow->wht_value;

        $quotation_total = $sorow->total;
        $quotation_payment_amount = $sorow->payment_amount;

        if($sorow->status == $updateStatusTo && $updateStatusTo != "P"){
            $this->data["dataset"] = $this->getIndexDataSetHTML($sorow);
            return $this->data;
        }

        $this->db->trans_begin();

        if($updateStatusTo == "A"){//Approved
            if($currentStatus == "R"){
                $this->data["dataset"] = $this->getIndexDataSetHTML($sorow);
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

        }elseif($updateStatusTo == "I"){
            $db->where("id", $quotation_id);
            $db->update("quotation", ["status"=>"I"]);

            $item_table2 = "";

            $fields1 = [
                        "doc_date"=>date("Y-m-d"),
                        "billing_type"=>$quotation_billing_type,
                        "quotation_id"=>$quotation_id,
                        "reference_number"=>$quotation_number,
                        "project_id"=>$sorow->project_id,
                        "client_id"=>$sorow->client_id,
                        "sub_total_before_discount"=>$quotation_sub_total_before_discount,
                        "discount_type"=>$quotation_discount_type,
                        "discount_percent"=>$quotation_discount_percent,
                        "discount_amount"=>$quotation_discount_amount,
                        "sub_total"=>$quotation_sub_total,
                        "vat_inc"=>$quotation_vat_inc,
                        "vat_percent"=>$quotation_vat_percent,
                        "vat_value"=>$quotation_vat_value,
                        "total"=>$quotation_total,
                        "wht_inc"=>$quotation_wht_inc,
                        "wht_percent"=>$quotation_wht_percent,
                        "wht_value"=>$quotation_wht_value,
                        "payment_amount"=>$quotation_payment_amount,
                        "remark"=>$sorow->remark,
                        "created_by"=>$this->login_user->id,
                        "created_datetime"=>date("Y-m-d H:i:s"),
                        "status"=>"W",
                        "deleted"=>0
                    ];

            if($company_setting["company_billing_type"] == 3 || $company_setting["company_billing_type"] == 6){
                $item_table2 = "receipt_items";
                $fields2 = ["doc_number"=>$this->Receipts_m->getNewDocNumber()];

                $db->insert("receipt", array_merge($fields1, $fields2));
                $doc_id2 = $db->insert_id();
                $this->data["url"] = get_uri("receipts/view/".$doc_id2);
            }else{
                $item_table2 = "invoice_items";
                $invoice_date = date("Y-m-d");
                $invoice_credit = $sorow->credit;
                $invoice_due_date = date("Y-m-d", strtotime($invoice_date. " + ".$invoice_credit." days"));

                $fields2 = [
                                "doc_number"=>$this->Invoices_m->getNewDocNumber(),
                                "credit"=>$sorow->credit,
                                "due_date"=>$invoice_due_date
                            ];

                $db->insert("invoice", array_merge($fields1, $fields2));
                $doc_id2 = $db->insert_id();
                $this->data["url"] = get_uri("invoices/view/".$doc_id2);
            }

            $qirows = $db->select("*")
                            ->from("quotation_items")
                            ->where("quotation_id", $quotation_id)
                            ->order_by("sort", "ASC")
                            ->get()->result();

            if(empty(!$qirows)){
                if($item_table2 == "receipt_items") $fields2 = ["receipt_id"=>$doc_id2];
                else $fields2 = ["invoice_id"=>$doc_id2];

                foreach($qirows as $qirow){
                    $fields1 = [
                            "product_id"=>$qirow->product_id,
                            "product_name"=>$qirow->product_name,
                            "product_description"=>$qirow->product_description,
                            "quantity"=>$qirow->quantity,
                            "unit"=>$qirow->unit,
                            "price"=>$qirow->price,
                            "total_price"=>$qirow->total_price,
                            "sort"=>$qirow->sort
                        ];

                    $db->insert($item_table2, array_merge($fields1, $fields2));
                }
            }

            $this->data["task"] = "create_invoice";
            $this->data["status"] = "success";
            $this->data["message"] = lang('record_saved');

        }elseif($updateStatusTo == "RESET"){
            if($company_setting["company_billing_type"] == 3 || $company_setting["company_billing_type"] == 6){
                $rerow = $db->select("doc_number")
                            ->from("receipt")
                            ->where("quotation_id", $quotation_id)
                            ->where("status !=", "V")
                            ->where("deleted", 0)
                            ->get()->row();

                if(!empty($rerow)){
                    $this->data["dataset"] = $this->getIndexDataSetHTML($sorow);
                    $this->data["message"] = "ไม่สามารถรีเซ็ตใบเสนอราคาได้ เนื่องจากมีการผูกใบเสนอราคากับใบเสร็จเลขที่ ".$rerow->doc_number." แล้ว";
                    $this->data["status"] = "error";
                    $db->trans_rollback();
                    return $this->data;
                }
            }else{
                $ivrow = $db->select("doc_number")
                        ->from("invoice")
                        ->where("quotation_id", $quotation_id)
                        ->where_in("status", ["W", "O", "P"])
                        ->where("deleted", 0)
                        ->get()->row();

                if(!empty($ivrow)){
                    $this->data["dataset"] = $this->getIndexDataSetHTML($sorow);
                    $this->data["message"] = "ไม่สามารถรีเซ็ตใบเสนอราคาได้ เนื่องจากมีการผูกใบเสนอราคากับใบแจ้งหนี้เลขที่ ".$ivrow->doc_number." แล้ว";
                    $this->data["status"] = "error";
                    $db->trans_rollback();
                    return $this->data;
                }
            }

            $db->where("id", $quotation_id);
            $db->update("quotation", [
                                        "approved_by"=>NULL,
                                        "approved_datetime"=>NULL,
                                        "status"=>"W"
                                    ]);
        }

        if ($db->trans_status() === FALSE){
            $db->trans_rollback();
            $this->data["dataset"] = $this->getIndexDataSetHTML($sorow);
            return $this->data;
        }

        $db->trans_commit();

        if(isset($this->data["task"])) return $this->data;

        $sorow = $db->select("*")
                    ->from("quotation")
                    ->where("id",$docId)
                    ->where("deleted", 0)
                    ->get()->row();

        $this->data["dataset"] = $this->getIndexDataSetHTML($sorow);
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
                if($db->count_all_results("quotation") < 1) break;
            }

            $this->data["sharelink"] = get_uri($this->shareHtmlAddress."th/".$sharekey);
        }

        $db->where("id", $docId);
        $db->update("quotation", ["sharekey"=>$sharekey, "sharekey_by"=>$sharekey_by]);

        return $this->data;
    }
}