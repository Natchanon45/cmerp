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
        $running_number = $this->db->get("sales_order")->num_rows() + 1;

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
            $doc_status .= "<option value='V'>ยกเลิก</option>";
        }elseif($sorow->status == "A"){
            $doc_status .= "<option selected>อนุมัติ</option>";
            $doc_status .= "<option value='V'>ยกเลิก</option>";
        }elseif($sorow->status == "V"){
            $doc_status .= "<option selected>ยกเลิก</option>";
        }

        $doc_status .= "</select>";

        $data = [
                    "<a href='".get_uri("sales-orders/view/".$sorow->id)."'>".convertDate($sorow->doc_date, true)."</a>",
                    "<a href='".get_uri("sales-orders/view/".$sorow->id)."'>".$sorow->doc_number."</a>",
                    $sorow->reference_number, "<a href='".get_uri("clients/view/".$sorow->client_id)."'>".$sorow->project_title."</a>",
                    $this->Clients_m->getCompanyName($sorow->client_id), $sorow->project_price, $doc_status,
                    "<a data-post-id='".$sorow->id."' data-action-url='".get_uri("sales-orders/addedit")."' data-act='ajax-modal' class='edit'><i class='fa fa-pencil'></i></a>"
                ];

        return $data;
    }

    function indexDataSet() {
        $db = $this->db;
        $company_setting = $this->Settings_m->getCompany();

        $db->select("*")->from("sales_order");

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
            $this->data["doc_date"] = $sorow->doc_date;
            $this->data["doc_number"] = $sorow->doc_number;
            $this->data["share_link"] = $sorow->sharekey != null ? get_uri($this->shareHtmlAddress."th/".$sorow->sharekey) : null;

            $this->data["reference_number"] = $sorow->reference_number;;
            $this->data["project_id"] = null;
            $this->data["project_title"] = $sorow->project_title;
            $this->data["client_id"] = $client_id;
            $this->data["lead_id"] = $lead_id;
            $this->data["project_description"] = $sorow->project_description;
            $this->data["project_start_date"] = $sorow->project_start_date;
            $this->data["project_deadline"] = $sorow->project_deadline;
            $this->data["project_price"] = $sorow->project_price;
            
            $this->data["remark"] = $sorow->remark;
            $this->data["created_by"] = $sorow->created_by;
            $this->data["created_datetime"] = $sorow->created_datetime;
            $this->data["approved_by"] = $sorow->approved_by;
            $this->data["approved_datetime"] = $sorow->approved_datetime;
            if($sorow->approved_by != null) if(file_exists($_SERVER['DOCUMENT_ROOT']."/".$company_setting["company_stamp"])) $this->data["company_stamp"] = $company_setting["company_stamp"];
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
                    ->from("sales_order")
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($sorow)) return $this->data;

        $docId = $sorow->id;

        $soirows = $db->select("*")
                        ->from("sales_order_items")
                        ->where("sales_order_id", $docId)
                        ->order_by("sort", "asc")
                        ->get()->result();

        $client_id = $sorow->client_id;
        $created_by = $sorow->created_by;

        $this->data["seller"] = $ci->Users_m->getInfo($created_by);

        $this->data["buyer"] = $ci->Customers_m->getInfo($client_id);
        $this->data["buyer_contact"] = $ci->Customers_m->getContactInfo($client_id);

        $this->data["doc_number"] = $sorow->doc_number;
        $this->data["doc_date"] = $sorow->doc_date;
        $this->data["reference_number"] = $sorow->reference_number;
        $this->data["remark"] = $sorow->remark;

        $this->data["sharekey_by"] = $sorow->sharekey_by;

        $this->data["created_by"] = $sorow->created_by;
        $this->data["created_datetime"] = $sorow->created_datetime;
        $this->data["approved_by"] = $sorow->approved_by;
        $this->data["approved_datetime"] = $sorow->approved_datetime;
        if($sorow->approved_by != null) if(file_exists($_SERVER['DOCUMENT_ROOT']."/".$company_setting["company_stamp"])) $this->data["company_stamp"] = $company_setting["company_stamp"];
        $this->data["doc_status"] = $sorow->status;

        $this->data["doc"] = $sorow;
        $this->data["items"] = $soirows;

        $this->data["status"] = "success";
        $this->data["message"] = "ok";

        return $this->data;
    }

    function updateDoc($docId = null){}

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
                                        "project_title"=>$project_title,
                                        "project_description"=>$project_description,
                                        "project_start_date"=>$project_start_date,
                                        "project_deadline"=>$project_deadline,
                                        "project_price"=>getNumber($project_price),
                                        "remark"=>$remark
                                    ]);
        }else{
            $doc_number = $this->getNewDocNumber();
        
            $db->insert("sales_order", [
                                        "doc_number"=>$doc_number,
                                        "doc_date"=>$doc_date,
                                        "reference_number"=>$reference_number,
                                        "client_id"=>$customer_id,
                                        "project_title"=>$project_title,
                                        "project_description"=>$project_description,
                                        "project_start_date"=>$project_start_date,
                                        "project_deadline"=>$project_deadline,
                                        "project_price"=>getNumber($project_price),
                                        "remark"=>$remark,
                                        "created_by"=>$this->login_user->id,
                                        "created_datetime"=>date("Y-m-d H:i:s"),
                                        "status"=>"W"
                                    ]);

            $docId = $db->insert_id();
        }
        
        $this->data["target"] = get_uri("sales-orders/view/". $docId);
        $this->data["status"] = "success";

        return $this->data;
    }

    function deleteDoc(){
        $db = $this->db;
        $docId = $this->input->post("id");

        $sorow = $db->select("status")
                        ->from("sales_order")
                        ->where("id", $docId)
                        ->get()->row();

        if(empty($sorow)) return $this->data;

        $db->where("id", $docId);
        $db->update("sales_order", ["deleted"=>1]);

        $data["success"] = true;
        $data["message"] = lang('record_deleted');

        return $data;
    }

    function undoDoc(){
        $db = $this->db;
        $docId = $this->input->post("id");

        $db->where("id", $docId);
        $db->update("sales_order", ["deleted"=>0]);

        $sorow = $db->select("*")
                    ->from("sales_order")
                    ->where("id", $docId)
                    ->get()->row();

        $data["success"] = true;
        $data["data"] = $this->getIndexDataSetHTML($sorow);
        $data["message"] = lang('record_undone');

        return $data;
    }

    function items(){
        $db = $this->db;
        $ci = get_instance();
        
        $sorow = $db->select("id, status")
                        ->from("sales_order")
                        ->where("id", $this->json->doc_id)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($sorow)) return $this->data;

        $soirows = $db->select("*")
                        ->from("sales_order_items")
                        ->where("sales_order_id", $this->json->doc_id)
                        ->order_by("id", "asc")
                        ->get()->result();

        if(empty($soirows)){
            $this->data["status"] = "notfound";
            $this->data["message"] = "ไม่พบข้อมูล";
            return $this->data;
        }

        $items = [];

        foreach($soirows as $soirow){
            $product_formula = $ci->Bom_item_m->getMixingGroupsInfoById($soirow->item_mixing_groups_id) == null ? "":$ci->Bom_item_m->getMixingGroupsInfoById($soirow->item_mixing_groups_id);
            $item["id"] = $soirow->id;
            $item["product_id"] = $soirow->product_id;
            $item["product_name"] = $soirow->product_name;
            $item["product_description"] = $soirow->product_description;
            $item["product_formula_name"] = $product_formula["name"];
            $item["quantity"] = $soirow->quantity;
            $item["unit"] = $soirow->unit;
            $item["price"] = number_format($soirow->price, 2);
            $item["total_price"] = number_format($soirow->total_price, 2);

            $items[] = $item;
        }

        $this->data["doc_status"] = $sorow->status;
        $this->data["items"] = $items;
        $this->data["status"] = "success";

        return $this->data;
    }

    function item(){
        $db = $this->db;
        $ci = get_instance();
        $docId = $this->input->post("doc_id");
        $itemId = $this->input->post("item_id");

        $sorow = $db->select("id")
                        ->from("sales_order")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($sorow)) return $this->data;

        $this->data["doc_id"] = $docId;
        $this->data["product_id"] = "";
        $this->data["product_formulas"] = [];
        $this->data["item_mixing_groups_id"] = null;
        $this->data["product_name"] = "";
        $this->data["product_description"] = "";
        $this->data["quantity"] = number_format(1, $this->Settings_m->getDecimalPlacesNumber());
        $this->data["unit"] = "";
        $this->data["price"] = number_format(0, 2);
        $this->data["total_price"] = number_format(0, 2);

        if(!empty($itemId)){
            $soirow = $db->select("*")
                        ->from("sales_order_items")
                        ->where("id", $itemId)
                        ->where("sales_order_id", $docId)
                        ->get()->row();

            if(empty($soirow)) return $this->data;

            $this->data["item_id"] = $soirow->id;
            $this->data["product_id"] = $soirow->product_id;
            $this->data["product_formulas"] = $ci->Products_m->getFomulasByItemId($soirow->product_id);
            $this->data["item_mixing_groups_id"] = $soirow->item_mixing_groups_id;
            $this->data["product_name"] = $soirow->product_name;
            $this->data["product_description"] = $soirow->product_description;
            $this->data["quantity"] = number_format($soirow->quantity, $this->Settings_m->getDecimalPlacesNumber());
            $this->data["unit"] = $soirow->unit;
            $this->data["price"] = number_format($soirow->price, 2);
            $this->data["total_price"] = number_format($soirow->total_price, 2);
        }

        $this->data["status"] = "success";
        $this->data["message"] = "ok";

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
                    ->from("sales_order")
                    ->where("id", $docId)
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($sorow)) return $this->data;
        
        $this->validateItem();
        if($this->data["status"] == "validate") return $this->data;

        $itemId = $this->json->item_id;
        $product_id = $this->json->product_id == ""?null:$this->json->product_id;
        $item_mixing_groups_id = $this->json->product_formula_id == "none"?null:$this->json->product_formula_id;
        $product_name = $this->json->product_name;
        $product_description = $this->json->product_description;
        $quantity = round(getNumber($this->json->quantity), $this->Settings_m->getDecimalPlacesNumber());
        $unit = $this->json->unit;
        $price = round(getNumber($this->json->price), 2);
        $total_price = round($price * $quantity, 2);

        $fdata = [
                    "sales_order_id"=>$docId,
                    "product_id"=>$product_id,
                    "item_mixing_groups_id"=>$item_mixing_groups_id,
                    "product_name"=>$product_name,
                    "product_description"=>$product_description,
                    "quantity"=>$quantity,
                    "unit"=>$unit,
                    "price"=>$price,
                    "total_price"=>$total_price,
                ];

        $db->trans_begin();
        
        if(empty($itemId)){
            $db->where("sales_order_id", $docId);
            $total_items = $db->count_all_results("sales_order_items");
            $fdata["sales_order_id"] = $docId;
            $fdata["sort"] = $total_items + 1;
            $db->insert("sales_order_items", $fdata);

        }else{
            $db->where("id", $itemId);
            $db->where("sales_order_id", $docId);
            $db->update("sales_order_items", $fdata);
        }

        
        if ($db->trans_status() === FALSE){
            $db->trans_rollback();
        }else{
            $db->trans_commit();
        }

        //$this->updateDoc($docId);

        $this->data["target"] = get_uri("sales-orders/view/".$docId);
        $this->data["status"] = "success";

        return $this->data;

    }

    function deleteItem(){
        $db = $this->db;
        $docId = $this->json->doc_id;
        
        $db->where("id", $this->json->item_id);
        $db->where("sales_order_id", $docId);
        $db->delete("sales_order_items");

        if($db->affected_rows() != 1) return $this->data;

        //$this->updateDoc($docId);

        $this->data["status"] = "success";

        return $this->data;
    }

    function updateStatus(){
        $db = $this->db;
        $docId = $this->json->doc_id;
        $updateStatusTo = $this->json->update_status_to;

        $sorow = $db->select("*")
                    ->from("sales_order")
                    ->where("id",$docId)
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($sorow)) return $this->data;

        $sales_order_id = $this->data["doc_id"] = $docId;
        $sales_order_number = $sorow->doc_number;
        $currentStatus = $sorow->status;

        if($sorow->status == $updateStatusTo){
            $this->data["dataset"] = $this->getIndexDataSetHTML($sorow);
            return $this->data;
        }

        $this->db->trans_begin();

        if($updateStatusTo == "A"){//Approved
            if($currentStatus == "V"){
                $this->data["dataset"] = $this->getIndexDataSetHTML($sorow);
                return $this->data;
            }

            $db->where("id", $sales_order_id);
            $db->update("sales_order", [
                                        "approved_by"=>$this->login_user->id,
                                        "approved_datetime"=>date("Y-m-d H:i:s"),
                                        "status"=>"A"
                                    ]);

        }elseif($updateStatusTo == "V"){
            $db->where("id", $docId);
            $db->where("deleted", 0);
            $db->update("sales_order", ["status"=>"V"]);
        }else{
            return $this->data;
        }

        if ($db->trans_status() === FALSE){
            $db->trans_rollback();
            $this->data["dataset"] = $this->getIndexDataSetHTML($sorow);
            return $this->data;
        }

        $db->trans_commit();

        if(isset($this->data["task"])) return $this->data;

        $sorow = $db->select("*")
                    ->from("sales_order")
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
                if($db->count_all_results("sales_order") < 1) break;
            }

            $this->data["sharelink"] = get_uri($this->shareHtmlAddress."th/".$sharekey);
        }

        $db->where("id", $docId);
        $db->update("sales_order", ["sharekey"=>$sharekey, "sharekey_by"=>$sharekey_by]);

        return $this->data;
    }


    function itemInfo($item_id){
        $db = $this->db;
        $ci = get_instance();

        $irow = $db->select("*")
                        ->from("items")
                        ->where("id", $item_id)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($irow)) return $this->data;

        $this->data["id"] = $item_id = $irow->id;
        $this->data["title"] = $irow->title;
        $this->data["description"] = $irow->description;
        $this->data["formulas"] = $ci->Products_m->getFomulasByItemId($item_id);
        $this->data["quantity"] = number_format(1, $this->Settings_m->getDecimalPlacesNumber());
        $this->data["unit"] = $irow->unit_type;
        $this->data["price"] = number_format($irow->rate, 2);
        $this->data["total_price"] = number_format($irow->rate, 2);
        $this->data["status"] = "success";
        $this->data["message"] = "ok";

        return $this->data;
    }


}