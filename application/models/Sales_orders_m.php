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
            return lang("account_status_awaiting");
        }
    }

    function getPurposeInfo($purpose_code){
        if($purpose_code == "P") return lang("account_docname_production_order");
        elseif($purpose_code == "S") return lang("account_docname_sales_order");
        return "";
    }

    function getIndexDataSetHTML($sorow){
        $company_setting = $this->Settings_m->getCompany();

        $doc_status = "<select class='dropdown_status' data-doc_id='".$sorow->id."'>";

        if($sorow->status == "W"){
            $doc_status .= "<option selected>".lang("account_status_awaiting")."</option>";
            $doc_status .= "<option value='A'>".lang("account_status_approved")."</option>";
            $doc_status .= "<option value='V'>".lang("account_status_rejected")."</option>";
        }elseif($sorow->status == "A"){
            if($sorow->purpose == "S"){
                $doc_status .= "<option selected>".lang("account_status_approved")."</option>";
                $doc_status .= "<option value='PR'>".lang("account_so_view_po")."</option>";
                $doc_status .= "<option value='MR'>".lang("account_so_view_mr")."</option>";
            }

            if($sorow->purpose == "P"){
                $doc_status .= "<option selected>".lang("account_status_approved")."</option>";
                $doc_status .= "<option value='PROJECT'>".lang("account_so_view_project")."</option>";
            }

        }elseif($sorow->status == "V"){
            $doc_status .= "<option selected>".lang("account_status_rejected")."</option>";
        }

        $doc_status .= "</select>";

        $customer_group_names = "";
        $customer_groups = $this->Customers_m->getGroupTitlesByCustomerId($sorow->client_id);
        if(!empty($customer_groups)){
            foreach($customer_groups as $cgname){
                $customer_group_names .= $cgname.", ";
            }

            $customer_group_names = substr($customer_group_names, 0, -2);
        }

        $data = [
                    "<a href='".get_uri("sales-orders/view/".$sorow->id)."'>".convertDate($sorow->doc_date, true)."</a>",
                    "<a href='".get_uri("sales-orders/view/".$sorow->id)."'>".$sorow->doc_number."</a>",
                    $sorow->reference_number, $this->getPurposeInfo($sorow->purpose),
                    $this->Clients_m->getCompanyName($sorow->client_id), $customer_group_names, $doc_status,
                    "<a data-post-id='".$sorow->id."' data-action-url='".get_uri("sales-orders/addedit")."' data-act='ajax-modal' class='edit'><i class='fa fa-pencil'></i></a>"
                ];

        return $data;
    }

    function indexDataSet($doc_id = null) {
        $db = $this->db;        

        $db->select("sales_order.*, clients.group_ids")
            ->from("sales_order")
            ->join("clients", "sales_order.client_id = clients.id")
            ->where("sales_order.deleted", 0);

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

        if($this->input->post("client_group_id") != null){
            $db->where("find_in_set('".$this->input->post('client_group_id')."', group_ids)");
        }

        $sorows = $db->order_by("id", "DESC")->get()->result();

        $dataset = [];

        foreach($sorows as $sorow){
            $dataset[] = $this->getIndexDataSetHTML($sorow);
        }

        return $dataset;
    }

    function getDoc($docId){
        $db = $this->db;
        $company_setting = $this->Settings_m->getCompany();

        $this->data["dropdown_task_list"] = [];
        $this->data["dropdown_project_types"] = $this->Projects_m->getTypeRows();

        $trows = $this->Tasks_m->getRows();

        if(!empty($trows)){
            foreach($trows as $trow){
                $assigned_to = "";
                $collaborators = "";

                $urow = $this->Users_m->getRow($trow->assigned_to, ["first_name", "last_name"]);
                if($urow == null) continue;
                $assigned_to = $urow->first_name." ".$urow->last_name;

                $cuids = explode(",", $trow->collaborators);
                if(count($cuids) >= 0){
                    foreach($cuids as $cuid){
                        $urow = $this->Users_m->getRow($cuid, ["first_name", "last_name"]);
                        if($urow == null) continue;
                        $collaborators .= $urow->first_name." ".$urow->last_name.", ";

                    }
                    
                    $collaborators = substr($collaborators, 0, -2);
                }

                $dtl_text = $trow->title.", <b>".$assigned_to."</b>";
                if($collaborators != "") $dtl_text .= ", ". $collaborators;
                $this->data["dropdown_task_list"][] = ["id"=>$trow->id, "text"=>$dtl_text];
            }//endforeach
        }

        $this->data["doc_id"] = null;
        $this->data["purpose"] = null;
        $this->data["doc_date"] = date("Y-m-d");
        $this->data["reference_number"] = null;
        $this->data["project_id"] = null;
        $this->data["customer_id"] = null;
        $this->data["client_id"] = null;
        $this->data["lead_id"] = null;
        $this->data["project_title"] = null;
        $this->data["project_type_id"] = null;
        $this->data["project_task_ids"] = null;
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
        $this->data["sotrow"] = null;

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
            $this->data["purpose"] = $sorow->purpose;
            $this->data["doc_date"] = $sorow->doc_date;
            $this->data["doc_number"] = $sorow->doc_number;
            $this->data["share_link"] = $sorow->sharekey != null ? get_uri($this->shareHtmlAddress."th/".$sorow->sharekey) : null;

            $this->data["reference_number"] = $sorow->reference_number;
            $this->data["project_id"] = null;
            $this->data["project_title"] = $sorow->project_title;
            $this->data["project_type_id"] = $sorow->project_type_id;
            $this->data["project_task_ids"] = $this->getTaskIds($sorow->id);
            $this->data["client_id"] = $client_id;
            $this->data["lead_id"] = $lead_id;
            $this->data["project_description"] = $sorow->project_description;
            $this->data["project_start_date"] = $sorow->project_start_date;
            $this->data["project_deadline"] = $sorow->project_deadline;
            $this->data["project_price"] = $sorow->project_price;
            
            $this->data["remark"] = $sorow->remark;

            if($sorow->created_by != null) $this->data["created"] = $this->Users_m->getInfo($sorow->created_by);
            $this->data["created_by"] = $sorow->created_by;
            $this->data["created_datetime"] = $sorow->created_datetime;

            if($sorow->approved_by != null) $this->data["approved"] = $this->Users_m->getInfo($sorow->approved_by);
            $this->data["approved_by"] = $sorow->approved_by;
            $this->data["approved_datetime"] = $sorow->approved_datetime;
            if($sorow->approved_by != null) if(file_exists($_SERVER['DOCUMENT_ROOT']."/".$company_setting["company_stamp"])) $this->data["company_stamp"] = $company_setting["company_stamp"];
            $this->data["doc_status"] = $sorow->status;
            $this->data["sotrows"] = $db->select("*")
                                        ->from("sales_order_tasks")
                                        ->where("sales_order_id", $docId)
                                        ->order_by("id", "asc")
                                        ->get()->result();
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

        $this->data["purpose"] = $sorow->purpose;
        $this->data["doc_number"] = $sorow->doc_number;
        $this->data["doc_date"] = $sorow->doc_date;
        $this->data["reference_number"] = $sorow->reference_number;
        $this->data["remark"] = $sorow->remark;

        $this->data["sharekey_by"] = $sorow->sharekey_by;

        $this->data["created"] = $ci->Users_m->getInfo($created_by);
        $this->data["created_by"] = $sorow->created_by;
        $this->data["created_datetime"] = $sorow->created_datetime;

        if($sorow->approved_by != null) $this->data["approved"] = $ci->Users_m->getInfo($sorow->approved_by);
        $this->data["approved_by"] = $sorow->approved_by;
        $this->data["approved_datetime"] = $sorow->approved_datetime;
        
        if($sorow->approved_by != null) if(file_exists($_SERVER['DOCUMENT_ROOT']."/".$company_setting["company_stamp"])) $this->data["company_stamp"] = $company_setting["company_stamp"];
        $this->data["doc_status"] = $sorow->status;
        $this->data["sotrows"] = $db->select("*")
                                        ->from("sales_order_tasks")
                                        ->where("sales_order_id", $docId)
                                        ->order_by("id", "asc")
                                        ->get()->result();

        $this->data["doc"] = $sorow;
        $this->data["items"] = $soirows;

        $this->data["status"] = "success";
        $this->data["message"] = "ok";

        return $this->data;
    }

    function validateDoc($purpose){
        $_POST = json_decode(file_get_contents('php://input'), true);

        $vrules[] = [
                        "field"=>"doc_date",
                        'label' => '',
                        'rules' => 'required'
                    ];

        if($purpose == "P"){
            $vrules[] = [
                        "field"=>"project_title",
                        'label' => '',
                        'rules' => 'required'
                    ];

            $vrules[] = [
                        "field"=>"project_type_id",
                        'label' => '',
                        'rules' => 'required'
                    ];
        }

        

        $this->form_validation->set_rules($vrules);

        if ($this->form_validation->run() == FALSE){
            $this->data["status"] = "validate";
            if(form_error('doc_date') != null) $this->data["messages"]["doc_date"] = form_error('doc_date');
            if(form_error('project_title') != null) $this->data["messages"]["project_title"] = form_error('project_title');
            if(form_error('project_type_id') != null) $this->data["messages"]["project_type_id"] = "โปรดเลือกประเภทโปรเจค";
        }

    }

    function saveDoc(){
        $db = $this->db;
        $company_setting = $this->Settings_m->getCompany();

        $this->validateDoc($this->json->purpose);
        if($this->data["status"] == "validate") return $this->data;

        $docId = $this->json->doc_id;
        $doc_date = convertDate($this->json->doc_date);
        $purpose = $this->json->purpose;
        $reference_number = $this->json->reference_number;
        $client_id = $this->json->client_id;
        $lead_id = $this->json->lead_id;
        $project_type_id = isset($this->json->project_type_id)?$this->json->project_type_id:null;
        $project_title = isset($this->json->project_title)?$this->json->project_title:null;
        $project_task_ids = isset($this->json->project_task_ids)?$this->json->project_task_ids:null;
        $project_description = isset($this->json->project_description)?$this->json->project_description:null;
        $project_start_date = isset($this->json->project_start_date)?convertDate($this->json->project_start_date):null;
        $project_deadline = isset($this->json->project_deadline)?convertDate($this->json->project_deadline):null;
        $project_price = isset($this->json->project_price)?getNumber($this->json->project_price):null;
        $remark = $this->json->remark;

        if($client_id == "" && $lead_id == ""){
            $this->data["status"] = "validate";
            $this->data["messages"]["client_id"] = "โปรดใส่ข้อมูล";
            return $this->data;
        }

        $customer_id = null;
        if($client_id != "") $customer_id = $client_id;
        if($lead_id != "") $customer_id = $lead_id;

        $db->trans_begin();

        if($docId != ""){
            $sorow = $db->select("status")
                        ->from("sales_order")
                        ->where("id", $docId)
                        ->get()->row();

            if(empty($sorow)){
                $this->data["message"] = "ขออภัย เกิดข้อผิดพลาดระหว่างดำเนินการ! โปรดลองใหม่อีกครั้งในภายหลัง";
                return $this->data;
            }

            if($sorow->status != "W"){
                $this->data["message"] = "ไม่สามารถบันทึกเอกสารได้เนื่องจากเอกสารมีการเปลี่ยนแปลงสถานะแล้ว";
                return $this->data;
            }

            $db->where("id", $docId);
            $db->where("deleted", 0);
            $db->update("sales_order", [
                                        "purpose"=>$purpose,
                                        "doc_date"=>$doc_date,
                                        "reference_number"=>$reference_number,
                                        "client_id"=>$customer_id,
                                        "project_type_id"=>$project_type_id,
                                        "project_title"=>$project_title,
                                        "project_description"=>$project_description,
                                        "project_start_date"=>$project_start_date,
                                        "project_deadline"=>$project_deadline,
                                        "project_price"=>getNumber($project_price),
                                        "remark"=>$remark
                                    ]);

            $db->where("sales_order_id", $docId)->delete("sales_order_tasks");


        }else{
            $doc_number = $this->getNewDocNumber();
        
            $db->insert("sales_order", [
                                        "purpose"=>$purpose,
                                        "doc_number"=>$doc_number,
                                        "doc_date"=>$doc_date,
                                        "reference_number"=>$reference_number,
                                        "client_id"=>$customer_id,
                                        "project_type_id"=>$project_type_id,
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

        $ptids = explode(",", $project_task_ids);
        if(count($ptids) > 0){
            foreach($ptids as $ptid){
                $trow = $this->Tasks_m->getRow($ptid);
                if($ptid == null) continue;

                $db->insert("sales_order_tasks", [
                                                    "sales_order_id"=>$docId,
                                                    "task_id"=>$ptid,
                                                    "task_title"=>$trow->title,
                                                    "task_description"=>$trow->description,
                                                    "task_assigned_to"=>$trow->assigned_to,
                                                    "task_collaborators"=>$trow->collaborators
                                                ]);

            }
        }

        if($db->trans_status() === FALSE){
            $db->trans_rollback();
            return $this->data;
        }

        $db->trans_commit();
        
        $this->data["target"] = get_uri("sales-orders/view/". $docId);
        $this->data["status"] = "success";
        $this->data["success"] = true;

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
            $product_formula = $this->Bom_item_m->getMixingGroupsInfoById($soirow->item_mixing_groups_id) == null ? "":$this->Bom_item_m->getMixingGroupsInfoById($soirow->item_mixing_groups_id);

            $product_image_file = $this->Products_m->getImage($soirow->product_id);
            if($product_image_file != null) $item["product_image_file"] = $product_image_file;

            $item["id"] = $soirow->id;
            $item["product_id"] = $soirow->product_id;
            $item["product_name"] = $soirow->product_name;
            $item["product_description"] = $soirow->product_description;
            $item["product_formula_name"] = $product_formula != null ? $product_formula["name"] : "";
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

        $sorow = $db->select("id, purpose")
                        ->from("sales_order")
                        ->where("id", $docId)
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($sorow)) return $this->data;

        $this->data["doc_id"] = $docId;
        $this->data["purpose"] = $sorow->purpose;
        $this->data["product_id"] = "";
        $this->data["product_formulas"] = [];
        $this->data["item_mixing_groups_id"] = null;
        $this->data["add_stock"] = "Y";
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
            $this->data["add_stock"] = $soirow->add_stock;
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
        $add_stock = $this->json->add_stock == "none"?null:$this->json->add_stock;
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
                    "add_stock"=>$add_stock,
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
            $db->where("product_id", $product_id);
            $db->where("item_mixing_groups_id", $item_mixing_groups_id);
            if($db->count_all_results("sales_order_items") > 0){
                $bimgrow = $db->select("name")
                                ->from("bom_item_mixing_groups")
                                ->where("id", $item_mixing_groups_id)
                                ->get()->row();

                if(empty($bimgrow)) return $this->data;
                $this->data["message"] = "ไม่สามารถเพิ่มข้อมูลได้ เนื่องจาก ".$product_name." : ".$bimgrow->name." มีอยู่ในรายการแล้ว.";
                return $this->data;
            }


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

    function getTaskRows($sales_order_id){
        $db = $this->db;

        $sotrows = $db->select("*")
                        ->from("sales_order_tasks")
                        ->where("sales_order_id", $sales_order_id)
                        ->get()->result();

        if(empty($sotrows)) return null;

        return $sotrows;
    }

    function getTaskIds($sales_order_id){
        $sotids = [];
        $sotrows = $this->getTaskRows($sales_order_id);

        if($sotrows != null){
            foreach($sotrows as $sotrow){
                $sotids[] = $sotrow->task_id;
            }
        }

        if(count($sotids) < 1) return null;
        return implode(",", $sotids);
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

        $this->data["dataset"] = $this->getIndexDataSetHTML($sorow);

        if($sorow->status == $updateStatusTo) return $this->data;

        $this->db->trans_begin();

        if($updateStatusTo == "A"){
            if($currentStatus == "V") return $this->data;

            $db->where("sales_order_id", $sales_order_id);
            if($db->count_all_results("sales_order_items") < 1){
                $this->data["message"] = lang("account_so_message_product_not_found");
                return $this->data;
            }

            $db->where("id", $sales_order_id);
            $db->update("sales_order", [
                                        "approved_by"=>$this->login_user->id,
                                        "approved_datetime"=>date("Y-m-d H:i:s"),
                                        "status"=>"A"
                                    ]);

        }elseif($updateStatusTo == "PR"){
            if($currentStatus == "V") return $this->data;
            $this->data["popup_doc_id"] = $sales_order_id;
            $this->data["popup_title"] = lang("account_so_title_create_purchase_requisition");
            $this->data["popup_url"] = get_uri("sales-orders/make_purchase_requisition");
            $this->data["task"] = "popup";
            $this->data["status"] = "success";
            return $this->data;
        }elseif($updateStatusTo == "MR"){
            if($currentStatus == "V") return $this->data;
            $this->data["popup_doc_id"] = $sales_order_id;
            $this->data["popup_title"] = lang("account_so_title_automatically_generate_material_requests");
            $this->data["popup_url"] = get_uri("sales-orders/make_material_request");
            $this->data["task"] = "popup";
            $this->data["status"] = "success";
            return $this->data;
        }elseif($updateStatusTo == "PROJECT"){
            if($currentStatus == "V") return $this->data;
            $this->data["popup_doc_id"] = $sales_order_id;
            $this->data["popup_title"] = lang("account_so_title_automatically_create_project");
            $this->data["popup_url"] = get_uri("sales-orders/make_production_order");
            $this->data["task"] = "popup";
            $this->data["status"] = "success";
            return $this->data;
        }elseif($updateStatusTo == "V"){
            $db->where("id", $docId);
            $db->where("deleted", 0);
            $db->update("sales_order", ["status"=>"V"]);
        }else{
            return $this->data;
        }

        if ($db->trans_status() === FALSE){
            $db->trans_rollback();
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

    function productsToPR($sales_order_id){
        $db = $this->db;
        $ci = get_instance();

        $sorow = $db->select("*")
                        ->from("sales_order")
                        ->where("id", $sales_order_id)
                        ->where("status", "A")
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($sorow)) return $this->data;
        
        $soirows = $db->select("*")
                        ->from("sales_order_items")
                        ->where("sales_order_id", $sales_order_id)
                        ->order_by("sort", "asc")
                        ->get()->result();

        $html = "";
        $total_records = 0;
        
        if(!empty($soirows)){

            foreach($soirows as $soirow){
                $product_remaining = 0;

                if($soirow->pr_header_id == null){
                    $product_remaining = $ci->Bom_item_m->getTotalRemainingItems($soirow->product_id);
                    //if($product_remaining >= $soirow->quantity) continue;
                    
                }else{
                    $product_remaining = $soirow->product_remaining;
                }

                $product_to_pr = 0;

                if($soirow->mr_header_id == null){
                    $product_to_pr = $soirow->quantity - $product_remaining;    
                }

                $biprows = $db->select("*")
                                ->from("bom_item_pricings")
                                ->where("item_id", $soirow->product_id)
                                ->get()->result();

                $html .= "<tr class='sales_order_items' ".($product_to_pr > 0 ? "data-id='".$soirow->id."'":"").">";
                    $html .= "<td class='product_name'>".$soirow->product_name."</td>";
                    $html .= "<td class='product_supplier'>";

                        if($this->Permission_m->bom_supplier_read == "1"){
                            if($soirow->pr_header_id == null){
                                if(empty($biprows)){
                                    $html .= "<span class='supplier_not_found'>".lang('account_so_message_supplier_not_found')."</span>";
                                }else{
                                    $html .= "<select class='suppliers'>";
                                        foreach($biprows as $biprow){
                                            $html .= "<option value='".$biprow->supplier_id."'>".$this->Suppliers_m->getInfo($biprow->supplier_id)["company_name"]."</option>";
                                        }
                                    $html .= "</select>";
                                }
                            }else{
                                $supplier = $ci->Suppliers_m->getInfo($soirow->supplier_id);
                                if($supplier != null) $html .= "<span class='supplier_name'>".$supplier["company_name"]."</span>";
                            }
                        }else{
                            $html .= "-";
                        }

                    $html .= "</td>";
                    $html .= "<td class='unit'>".$soirow->unit."</td>";
                    $html .= "<td class='instock'>".($soirow->pr_header_id == null ? number_format($product_remaining, 2):'-')."</td>";

                    if($soirow->mr_header_id == null){
                        $html .= "<td class='quantity'>".number_format($soirow->quantity, DEC)."</td>";
                    }else{
                        $html .= "<td class='quantity'>0.00</td>";
                    }
                    

                    if($product_to_pr <= 0){
                        $html .= "<td class='topurchase'>0.00</td>";
                    }else{
                        $html .= "<td class='topurchase'>".number_format(abs($product_remaining - $soirow->quantity), 2)."</td>";
                    }
                    
                    $html .= "<td class='reference_number'>";

                    if($soirow->pr_header_id != null){
                        $reference_number = $ci->Purchase_request_m->getDocNumber($soirow->pr_header_id);
                        if($reference_number != "") $html .= "<a href='".get_uri("purchase_request/view/".$soirow->pr_header_id)."'>".$reference_number."</a>";
                    }else{
                        $html .= "#";
                    }

                    $html .= "</td>";
                $html .= "</tr>";
                $total_records++;
            }
        }

        if($total_records >= 1){
            $this->data["html"] = $html;
        }else{
            $this->data["html"] = "<tr class='norecord'><td colspan='7'>ไม่พบข้อมูลสินค้า</td></tr>";
        }

        return $this->data;
    }

    function makePR(){
        $db = $this->db;
        $sales_order_id = $this->json->sales_order_id;
        $sales_order_items = json_decode($this->json->sales_order_items);

        $sorow = $db->select("*")
                    ->from("sales_order")
                    ->where("id", $sales_order_id)
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($sorow)) return $this->data;

        $suppliers = [];

        if(!empty($sales_order_items)){
            foreach($sales_order_items as $soi){
                if($soi->supplier_id == null){
                    $this->data["message"] = "กรุณาสร้างผู้จัดจำหน่ายในรายการสินค้าที่ยังไม่มีผู้จัดจำหน่าย";
                    return $this->data;
                }

                $soirow = $db->select("*")
                                    ->from("sales_order_items")
                                    ->where("id", $soi->sales_order_item_id)
                                    ->where("sales_order_id", $sales_order_id)
                                    ->where("pr_header_id IS NULL")
                                    ->get()->row();

                //ถ้าไม่มีรายการ is null ก็แสดงว่ารายการนี้ถูกนำไปออก PR หมดแล้ว
                if(empty($soirow)) continue;

                $biprow = $db->select("*")
                                    ->from("bom_item_pricings")
                                    ->where("item_id", $soirow->product_id)
                                    ->where("supplier_id", $soi->supplier_id)
                                    ->get()->row();

                //รายการนี้มี supplier หรือไม่
                if(empty($biprow)) continue;

                $suppliers[$soi->supplier_id][] = [
                                                        "sales_order_items_id"=>$soi->sales_order_item_id,
                                                        "product_id"=>$soirow->product_id,
                                                        "product_name"=>$soirow->product_name,
                                                        "product_description"=>$soirow->product_description,
                                                        "quantity"=>$soirow->quantity,
                                                        "unit"=>$soirow->unit,
                                                        "price"=>$soirow->price,
                                                        "total_price"=>$soirow->total_price
                                                    ];
            }
        }

        $db->trans_begin();

        foreach($suppliers as $supid => $products){
            if(!empty($products)){
                $pr_doc_number = $this->Purchase_request_m->getNewDocNumber();
                $pr_doc_date = date("Y-m-d");
                $pr_type = 3;
                $pr_doc_valid_until_date = date("Y-m-d");
                $pr_supplier_id = $supid;

                $db->insert("pr_header", [
                                            "doc_number"=>$pr_doc_number,
                                            "pr_type"=>$pr_type,
                                            "doc_date"=>$pr_doc_date,
                                            "doc_valid_until_date"=>$pr_doc_valid_until_date,
                                            "project_id"=>0,
                                            "supplier_id"=>$pr_supplier_id,
                                            "created_by"=>$this->login_user->id,
                                            "created_datetime"=>date("Y-m-d H:i:s"),
                                            "status"=>"W",
                                        ]);

                $pr_header_id = $db->insert_id();
                $sort = 0;

                foreach($products as $p){
                    $product_remaining = $this->Bom_item_m->getTotalRemainingItems($p["product_id"]);

                    $db->insert("pr_detail", [
                                                "pr_id"=>$pr_header_id,
                                                "product_id"=>$p["product_id"],
                                                "product_name"=>$p["product_name"],
                                                "product_description"=>$p["product_description"],
                                                "quantity"=>abs($product_remaining - $p["quantity"]),
                                                "unit"=>$p["unit"],
                                                "price"=>0,
                                                "total_price"=>0,
                                                "sort"=>++$sort,
                                            ]);

                    $db->where("id", $p["sales_order_items_id"]);
                    $db->update("sales_order_items", [
                                                        "pr_header_id"=>$pr_header_id,
                                                        "supplier_id"=>$pr_supplier_id,
                                                        "product_remaining"=>$product_remaining,
                                                        "product_remaining_datetime"=>date("Y-m-d H:i:s")
                                                    ]);
                }
            }
        }
        
        if ($db->trans_status() === FALSE){
            $db->trans_rollback();
            return $this->data;
        }

        $db->trans_commit();

        $this->data["can_make_pr"] = $this->canMakePR($sales_order_id);
        $this->data["status"] = "success";
        $this->data["message"] = "สร้างใบขอซื้อเรียบร้อย";
        return $this->data;
    }

    function canMakePR($sales_order_id){
        $db = $this->db;
        $ci = get_instance();

        $soirows = $db->select("*")
                        ->from("sales_order_items")
                        ->where("sales_order_id", $sales_order_id)
                        ->where("pr_header_id IS NULL")
                        ->get()->result();

        if(empty($soirows)) return false;

        foreach($soirows as $soirow){
            $product_remaining = $ci->Bom_item_m->getTotalRemainingItems($soirow->product_id);
            if($product_remaining >= $soirow->quantity) continue;
            $db->where("item_id", $soirow->product_id);
            if($db->count_all_results("bom_item_pricings") < 1) continue;

            return true;
        }

        return false;
    }

    function canViewPR($sales_order_id){
        $db = $this->db;
        $ci = get_instance();

        if($this->canMakePR($sales_order_id)) return true;

        $db->where("sales_order_id", $sales_order_id);
        $db->where("pr_header_id IS NOT NULL");
        if($db->count_all_results("sales_order_items") > 0) return true;

        return false;
    }

    function productsToMR($sales_order_id){
        $db = $this->db;

        $sorow = $db->select("*")
                        ->from("sales_order")
                        ->where("id", $sales_order_id)
                        ->where("status", "A")
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($sorow)) return $this->data;
        
        $soirows = $db->select("*")
                        ->from("sales_order_items")
                        ->where("sales_order_id", $sales_order_id)
                        ->order_by("sort", "asc")
                        ->get()->result();

        $html = "";
        $total_records = 0;
        
        if(!empty($soirows)){

            foreach($soirows as $soirow){
                $product_remaining = 0;
                $total_submit_quantity = 0;

                if($soirow->mr_header_id != null){
                    $product_remaining = $soirow->product_remaining;
                    $total_submit_quantity = $this->Bom_item_m->getRatioByMaterialRequestId($soirow->mr_header_id);
                }else{
                    $product_remaining = $this->Bom_item_m->getTotalRemainingItems($soirow->product_id);
                    //if($product_remaining <= 0) continue;

                    if($product_remaining < $soirow->quantity){
                        $total_submit_quantity = $product_remaining;
                    }else{
                        $total_submit_quantity = $soirow->quantity;
                    }
                }

                $html .= "<tr class='sales_order_items' data-id='".$soirow->id."'>";
                    $html .= "<td class='product_name'>".$soirow->product_name."</td>";
                    $html .= "<td class='unit'>".$soirow->unit."</td>";
                    $html .= "<td class='instock'>".($soirow->mr_header_id == null ? number_format($product_remaining, 2):'-')."</td>";
                    $html .= "<td class='total_used'>".number_format($soirow->quantity, 2)."</td>";
                    $html .= "<td class='total_submit'>".number_format($total_submit_quantity, 2)."</td>";
                    $html .= "<td class='reference_number'>";

                    if($soirow->mr_header_id != null){
                        $reference_number = $this->Materialrequest_m->getDocNumber($soirow->mr_header_id);
                        if($reference_number != "") $html .= "<a href='".get_uri("materialrequests/view/".$soirow->mr_header_id)."'>".$reference_number."</a>";
                    }else{
                        $html .= "#";
                    }

                    $html .= "</td>";
                $html .= "</tr>";
                $total_records++;
            }
        }

        if($total_records >= 1){
            $this->data["html"] = $html;
        }else{
            $this->data["html"] = "<tr class='norecord'><td colspan='7'>ไม่พบข้อมูลสินค้า</td></tr>";
        }

        return $this->data;
    }

    function makeMR(){
        $db = $this->db;
        $sales_order_id = $this->json->sales_order_id;

        $sorow = $db->select("*")
                    ->from("sales_order")
                    ->where("id", $sales_order_id)
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($sorow)) return $this->data;

        $mr_doc_id = null;
        $mr_doc_number = null;
        
        $soirows = $db->select("*")
                        ->from("sales_order_items")
                        ->where("sales_order_id", $sales_order_id)
                        ->order_by("sort", "asc")
                        ->get()->result();

        if(!empty($soirows)){
            foreach($soirows as $soirow){
                if($soirow->mr_header_id != null){
                    $mr_doc_id = $soirow->mr_header_id;
                    break;
                }
            }
        }

        $db->trans_begin();

        if($mr_doc_id == null){
            $mr_doc_number = $this->Db_model->genDocNo(["prefix" => "MR","LPAD" => 4,"column" => "doc_no","table" => "materialrequests"]);
            $db->insert("materialrequests", [
                                                "doc_no"=>$mr_doc_number,
                                                "sale_order_id"=>$sorow->id,
                                                "sale_order_no"=>$sorow->doc_number,
                                                "mr_type"=>"2",
                                                "mr_date"=>date("Y-m-d"),
                                                "status_id"=>1,
                                                "discount_amount"=>0,
                                                "discount_amount_type"=>"percentage",
                                                "discount_type"=>"before_tax",
                                                "created_by"=>$this->login_user->id,
                                                "requester_id"=>$this->login_user->id
                                            ]);

            $mr_doc_id = $db->insert_id();
        }

        if(!empty($soirows)){
            foreach($soirows as $soirow){
                if($soirow->mr_header_id != null) continue;
                $total_submit_quantity = 0;
                $product_remaining = $this->Bom_item_m->getTotalRemainingItems($soirow->product_id);

                if($product_remaining <= 0) continue;

                if($product_remaining < $soirow->quantity){
                    $total_submit_quantity = $product_remaining;
                }else{
                    $total_submit_quantity = $soirow->quantity;
                }

                $bisrows = $db->select("*")
                                ->from("bom_item_stocks")
                                ->where("item_id", $soirow->product_id)
                                ->where("remaining >", 0)
                                ->order_by("id", "asc")
                                ->get()->result();

                foreach($bisrows as $bisrow){
                    $in_stock = $bisrow->stock - $total_submit_quantity;

                    $db->insert("bom_project_item_items", [
                                                        "item_id"=>$soirow->product_id,
                                                        "stock_id"=>$bisrow->id,
                                                        "ratio"=>($in_stock < 0 ? $bisrow->stock : $total_submit_quantity),
                                                        "mr_id"=>$mr_doc_id,
                                                        "used_status"=>0,
                                                        "note"=>$sorow->remark,
                                                    ]);

                    $bpim_id = $db->insert_id();

                    $db->insert("mr_items", [
                                            "mr_id"=>$mr_doc_id,
                                            "title"=>$soirow->product_name,
                                            "description"=>$soirow->product_description,
                                            "quantity"=>($in_stock < 0 ? $bisrow->stock : $total_submit_quantity),
                                            "unit_type"=>$soirow->unit,
                                            "rate"=>$soirow->price,
                                            "total"=>$soirow->total_price,
                                            "created_by"=>$this->login_user->id,
                                            "item_id"=>$soirow->product_id,
                                            "bpim_id"=>$bpim_id,
                                            "stock_id"=>$bisrow->id
                                        ]);

                    if($in_stock >= 0) break;
                }

                $db->where("id", $soirow->id);
                $db->update("sales_order_items", [
                                                    "mr_header_id"=>$mr_doc_id,
                                                    "product_remaining"=>$product_remaining,
                                                    "product_remaining_datetime"=>date("Y-m-d H:i:s")
                                                ]);
            }
        }
        
        if ($db->trans_status() === FALSE){
            $db->trans_rollback();
            return $this->data;
        }

        $db->trans_commit();

        $this->data["can_make_mr"] = $this->canMakeMR($sales_order_id);
        $this->data["status"] = "success";
        $this->data["message"] = "สร้างใบขอเบิกเรียบร้อย";
        return $this->data;
    }

    function canMakeMR($sales_order_id){
        $db = $this->db;
        $ci = get_instance();

        $soirows = $db->select("*")
                        ->from("sales_order_items")
                        ->where("sales_order_id", $sales_order_id)
                        ->where("mr_header_id IS NULL")
                        ->get()->result();

        if(empty($soirows)) return false;

        foreach($soirows as $soirow){
            $product_remaining = $ci->Bom_item_m->getTotalRemainingItems($soirow->product_id);
            if($product_remaining <= 0) continue;
            return true;
        }

        return false;
    }

    function canViewMR($sales_order_id){
        $db = $this->db;
        $ci = get_instance();

        if($this->canMakeMR($sales_order_id)) return true;

        $db->where("sales_order_id", $sales_order_id);
        $db->where("mr_header_id IS NOT NULL");
        if($db->count_all_results("sales_order_items") > 0) return true;

        return false;
    }



    /***** start project ****/

    function productsToProject($sales_order_id){
        $db = $this->db;
        $ci = get_instance();

        $sorow = $db->select("*")
                        ->from("sales_order")
                        ->where("id", $sales_order_id)
                        ->where("status", "A")
                        ->where("deleted", 0)
                        ->get()->row();

        if(empty($sorow)) return $this->data;
        
        $soirows = $db->select("*")
                        ->from("sales_order_items")
                        ->where("sales_order_id", $sales_order_id)
                        ->order_by("sort", "asc")
                        ->get()->result();

        $html = "";
        $total_records = 0;
        
        if(!empty($soirows)){

            foreach($soirows as $soirow){
                /*$product_remaining = 0;
                $total_submit_quantity = 0;

                if($soirow->mr_header_id != null){
                    $product_remaining = $soirow->product_remaining;
                    $total_submit_quantity = $ci->Bom_item_m->getRatioByMaterialRequestId($soirow->mr_header_id);
                }else{
                    $product_remaining = $ci->Bom_item_m->getTotalRemainingItems($soirow->product_id);
                    if($product_remaining <= 0) continue;

                    if($product_remaining < $soirow->quantity){
                        $total_submit_quantity = $product_remaining;
                    }else{
                        $total_submit_quantity = $soirow->quantity;
                    }
                }*/

                $product_remaining = $this->Bom_item_m->getTotalRemainingItems($soirow->product_id);;

                $html .= "<tr class='sales_order_items' data-id='".$soirow->id."'>";
                    $html .= "<td class='product_name'>".$soirow->product_name."</td>";
                    $html .= "<td class='unit'>".$soirow->unit."</td>";
                    $html .= "<td class='instock'>".($soirow->mr_header_id == null ? number_format($product_remaining, DEC):'-')."</td>";
                    $html .= "<td class='total_used'><input type='text' value='".number_format($soirow->quantity, DEC)."' readonly></td>";
                    
                    $html .= "<td class='total_submit'>";
                    $html .= "<input type='text' value='".number_format($soirow->quantity, DEC)."' ".($sorow->project_id != null ? 'readonly':'').">";
                    $html .= "</span></td>";

                $html .= "</tr>";
                $total_records++;
            }
        }

        if($total_records >= 1){
            $this->data["html"] = $html;
        }else{
            $this->data["html"] = "<tr class='norecord'><td colspan='7'>ไม่พบข้อมูลสินค้า</td></tr>";
        }

        return $this->data;
    }

    function makeProject(){
        $db = $this->db;

        $sorow = $db->select("*")
                    ->from("sales_order")
                    ->where("id", $this->json->sales_order_id)
                    ->where("deleted", 0)
                    ->where("purpose", "P")
                    ->where("status", "A")
                    ->where("project_id IS NULL")
                    ->get()->row();


        if(empty($sorow)) return $this->data;

        $sales_order_id = $sorow->id;
        
        $db->trans_begin();
        
        $db->insert("projects", [
                                    "project_type_id"=>$sorow->project_type_id,
                                    "title"=>$sorow->project_title,
                                    "description"=>$sorow->project_description,
                                    "start_date"=>$sorow->project_start_date,
                                    "deadline"=>$sorow->project_deadline,
                                    "client_id"=>$sorow->client_id,
                                    "client_type"=>$this->Customers_m->isLead($sorow->client_id) == true ? 1:0,
                                    "created_date"=>date("Y-m-d"),
                                    "created_by"=>$this->login_user->id,
                                    "status"=>"open",
                                    "price"=>$sorow->project_price,
                                    "starred_by"=>"",
                                    "estimate_id"=>0,
                                ]);

        $project_id = $db->insert_id();
        $made_to_order = json_decode($this->json->made_to_order);
        $production_bom_data = [];

        foreach($made_to_order as $mto){
            $soirow = $db->select("*")
                        ->from("sales_order_items")
                        ->where("id", $mto->item_id)
                        ->where("sales_order_id", $sales_order_id)
                        ->get()->row();

            if(empty($soirow)){
                $db->trans_rollback();
                return $this->data;
            }

            $submit_num = getNumber($mto->submit_num);

            if($submit_num <= 0){
                $this->data["message"] = "ตัวเลข 'สั่งผลิด' ต้องมีค่ามากกว่า ".number_format($submit_num, DEC);
                $db->trans_rollback();
                return $this->data;
            }

            if($soirow->item_mixing_groups_id == null){
                $db->trans_rollback();
                $this->data["message"] = "ไม่สามารถสร้างโปรเจคได้ เนื่องจากมีรายการสินค้าที่ยังไม่ผูกสูตรผสมอยู่ในรายการั่งผลิต";
                return $this->data;
            }

            $production_bom_data[] = [
                                    "project_id"=>$project_id,
                                    "item_type"=>$this->Products_m->getItemType($soirow->product_id),
                                    "item_id"=>$soirow->product_id,
                                    "item_mixing"=>$soirow->item_mixing_groups_id,
                                    "quantity" =>$submit_num,
                                    "produce_in"=>$soirow->add_stock == "Y" ? 1 : 0,
                                ];  
        }

        $this->Projects_model->dev2_postProductionBomDataProcessing($production_bom_data);

        $sotrows = $db->select("*")
                        ->from("sales_order_tasks")
                        ->where("sales_order_id", $sales_order_id)
                        ->order_by("id", "asc")
                        ->get()->result();

        if(!empty($sotrows)){
            $member_ids = [];

            foreach($sotrows as $sotrow){
                if (!in_array($sotrow->task_assigned_to, $member_ids)) $member_ids[] = $sotrow->task_assigned_to;

                if(trim($sotrow->task_collaborators) != ""){
                    $uids = explode(",", $sotrow->task_collaborators);
                    foreach($uids as $uid){
                        if (!in_array($uid, $member_ids)) $member_ids[] = $uid;
                    }
                }
                
                $db->insert("tasks", [
                                        "title"=>$sotrow->task_title,
                                        "description"=>$sotrow->task_description,
                                        "project_id"=>$project_id,
                                        "assigned_to"=>$sotrow->task_assigned_to,
                                        "status_id"=>1,
                                        "collaborators"=>$sotrow->task_collaborators,
                                        "created_date"=>date("Y-m-d H:i:s")
                                    ]);
            }

            foreach($member_ids as $member_id){
                $db->insert("project_members", ["user_id"=>$member_id, "project_id"=>$project_id]);
            }
        }


        $db->where("id", $sales_order_id);
        $db->update("sales_order", ["project_id"=>$project_id]);

        //$db->trans_rollback();
        //return $this->data;

        if ($db->trans_status() === FALSE){
            $db->trans_rollback();
            return $this->data;
        }

        $db->trans_commit();
        $this->data["status"] = "success";
        $this->data["message"] = lang('record_saved');
        return $this->data;
    }

    function canMakeProject($sales_order_id){
        $db = $this->db;

        $sorow = $db->select("project_id")
                        ->from("sales_order")
                        ->where("id", $sales_order_id)
                        ->get()->row();

        if(empty($sorow)) return false;
        if($sorow->project_id == null) return true;

        return false;
    }

    function canViewProject($sales_order_id){
        $db = $this->db;
        $ci = get_instance();

        if($this->canMakeMR($sales_order_id)) return true;

        $db->where("sales_order_id", $sales_order_id);
        $db->where("mr_header_id IS NOT NULL");
        if($db->count_all_results("sales_order_items") > 0) return true;

        return false;
    }


}