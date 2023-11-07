<?php
class Sfg_m extends MY_Model {
    private $item_type = "SFG";
    
    function __construct() {
        parent::__construct();
    }

    function getIndexDataSetHTML($irow){
        $preview = '<img class="product-preview" src="' . base_url('assets/images/file_preview.jpg'). '">';
        if ($irow->files) {
            $images = @unserialize($irow->files);
            if (is_array($images) && sizeof($images)) {
                $preview = '<img class="product-preview" src="' . base_url('files/timeline_files/' . $images[sizeof($images) - 1]['file_name']) . '" />';
            }
        }

        $src = @$irow->barcode;
        if ($src) {
            $src = get_uri('/sfg/barcode/' . $src);
        }

        $data = [
                    "<a href='".get_uri('sfg/detail/' . $irow->id)."'>".$irow->id."</a>",
                    $preview,
                    $irow->item_code ? $irow->item_code : '-',
                    "<a href='".get_uri('sfg/detail/' . $irow->id)."'>".$irow->title."</a>",
                    nl2br($irow->description),
                    $irow->unit_type ? $irow->unit_type : "",
                    @$irow->barcode ? '<div style="text-align:center"><a href="' . $src . '" class="barcode_img" download><img src="' . $src . '" /><div class="text">Click to download</div></a></div>' : '-',
                    $irow->rate,
                    modal_anchor(get_uri("sfg/addedit"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_item'), "data-post-id" => $irow->id))."<a class='delete' data-id='".$irow->id."' data-action-url='".get_uri("sfg/item_delete")."' data-action='delete-confirmation'><i class='fa fa-times fa-fw'></i></a>"

                ];

        return $data;
    }

    function indexDataSet() {
        $db = $this->db;
        $company_setting = $this->Settings_m->getCompany();

        $db->select("items.*, item_categories.title AS category_title")
            ->from("items")
            ->join("item_categories", "item_categories.id = items.category_id", "left")
            ->join("bom_item_stocks", "bom_item_stocks.item_id = items.id", "left")
            ->where("item_type", $this->item_type)
            ->where("items.deleted", 0);

        if($this->input->post("category_id") != null){
            $db->where("category_id", $this->input->post("category_id"));
        }

        $irows = $db->get()->result();

        $dataset = [];

        foreach($irows as $irow){
            $dataset[] = $this->getIndexDataSetHTML($irow);
        }

        return $dataset;
    }

    function getRow($docId){
        $db = $this->db;

        $irow = $db->select("*")
                    ->from("items")
                    ->where("id", $docId)
                    ->where("item_type", $this->item_type)
                    ->get()->row();
        
        return $irow;
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

    function validateFile(){
        $file_name = $this->input->post("file_name");

        if (!is_valid_file_to_upload($file_name)) return ["success" => false, 'message' => lang('invalid_file_type')];
        if (is_image_file($file_name)) return ["success" => true];
        else return ["success" => false, 'message' => lang('please_upload_valid_image_files')];
    }

    function saveDoc(){
        $db = $this->db;
        $company_setting = $this->Settings_m->getCompany();

        //$this->validateDoc();
        //if($this->data["status"] == "validate") return $this->data;

        $sfg_id = $this->input->post('id');
        $oid = $this->input->post('oid');
        $is_duplicate = $this->input->post('is_duplicate');
        $account_id = $this->input->post('account_id');

        $new_files = [];
        $target_path = get_setting("timeline_file_path");
        $timeline_file_path = get_setting("timeline_file_path");

        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "item");
        $new_files = unserialize($files_data);

        $files = [];

        if ($sfg_id) {
            $item_info = $this->Items_model->get_one($sfg_id);
            $new_files = update_saved_files($timeline_file_path, $item_info->files, $new_files);
        } elseif ($oid && $is_duplicate) {// duplicate
            $o_item_info = $this->Items_model->get_one($oid);
            $new_files = unserialize($o_item_info->files);
            $files_data = copy_files($new_files, $target_path, "item");
            $new_files = unserialize($files_data);

            $files = $this->Bom_item_mixing_groups_model->get_file_details(['ref_id' => $oid, 'tablename' => 'items'])->result();
            // $new_files = update_saved_files($timeline_file_path, $o_item_info->files, $new_files);
        }

        $item_data = array(
            "item_type"=>$this->item_type,
            "title" => $this->input->post('title'),
            "description" => $this->input->post('description'),
            "unit_type" => $this->input->post('unit_type'),
            "rate" => unformat_currency($this->input->post('item_rate')),
            "files"=>serialize($new_files),
            "barcode" => $this->input->post('barcode'),
            "show_in_client_portal" => $this->input->post('show_in_client_portal') ? $this->input->post('show_in_client_portal') : "",
            "account_id" => $account_id ? $account_id : null,
            "noti_threshold" => $this->input->post('noti_threshold'),
            "item_code" => $this->input->post('item_code')
        );

        if ($sfg_id) {
            $db->where("id", $sfg_id);
            $db->update("items", $item_data);
        }else{
            $item_data["created_by"] = $this->login_user->id;
            $db->insert("items", $item_data);
            $sfg_id = $db->insert_id();
        }

        $sfgrow = $db->select("*")
                        ->from("items")
                        ->where("id", $sfg_id)
                        ->get()->row();

        

        return array("success" => true, "id" => $sfg_id, "data" => $this->getIndexDataSetHTML($sfgrow), 'message' => lang('record_saved'));


        /*if ($item_id) {
            
            if ($oid && $is_duplicate) {
                $mixing_groups = $this->Bom_item_mixing_groups_model->get_details(['item_id' => $oid])->result();
                foreach ($mixing_groups as $g) {
                    $g_a = [];
                    $g_a['id'] = 0;
                    $g_a['item_id'] = $item_id;
                    $g_a['name'] = $g->name;
                    $g_a['ratio'] = $g->ratio;
                    $g_a['is_public'] = $g->is_public;
                    $g_a['for_client_id'] = $g->for_client_id;

                    $new_gid = $this->Bom_item_mixing_groups_model->save($g_a, 0);
                    if ($new_gid) {
                        $material_mixings = $this->Bom_item_mixing_groups_model->get_mixings(['group_id' => $g->id])->result();
                        $material_ids = [];
                        $ratios = [];
                        $cat_ids = [];
                        foreach ($material_mixings as $mm) {
                            if (!isset($material_ids[$mm->cat_id]))
                                $material_ids[$mm->cat_id] = [];
                            $material_ids[$mm->cat_id][] = $mm->material_id;

                            if (!isset($ratios[$mm->cat_id]))
                                $ratios[$mm->cat_id] = [];
                            $ratios[$mm->cat_id][] = $mm->ratio;

                            $cat_ids[$mm->cat_id] = $mm->cat_id;
                        }
                        $this->Bom_item_mixing_groups_model->mixing_save($new_gid, $material_ids, $cat_ids, $ratios);
                    } else {
                        var_dump($g_a, $this->Bom_item_mixing_groups_model->db);
                    }
                }
                $target_path = BASEPATH . 'files/';
                foreach ($files as $f) {
                    $file_path = $target_path . $f->path;
                    if (file_exists($file_path)) {
                        $new_file_name = '_new_' . $f->path;
                        $new_file_path = $target_path . $new_file_name;
                        if (@copy($file_path, $new_file_path)) {
                            $new_f_item = (array) $f;
                            $new_f_item['id'] = 0;
                            $new_f_item['ref_id'] = $item_id;
                            $new_f_item['path'] = $new_file_name;
                            $this->Bom_item_mixing_groups_model->save_file($new_f_item, 0);
                        }
                    }
                }
            }
            $options = array("id" => $item_id);
            $item_info = $this->Items_model->get_details($options)->row();
            echo json_encode(array("success" => true, "id" => $item_info->id, "data" => $this->_make_item_row($item_info), 'message' => lang('record_saved')));

        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }*/
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
            $item["discount_per_unit"] = number_format($qirow->discount_amount, 2);
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
        $this->data["discount_type"] = "P";
        $this->data["discount_value"] = number_format(0, 2);
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
            $this->data["discount_type"] = $qirow->discount_type;
            $this->data["discount_value"] = number_format(($qirow->discount_type == "P" ? $qirow->discount_percent:$qirow->discount_amount), 2);
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
        $quantity = round(getNumber($this->json->quantity), DEC);
        $unit = $this->json->unit;
        $price = getNumber($this->json->price);
        $discount_type = $this->json->discount_type;
        $discount_value = getNumber($this->json->discount_value);
        $discount_percent = null;
        $discount_amount = 0;
        $price_after_discount = $total_price = 0;

        if($quantity > 0){
            $total_price = $price * $quantity;
            $price_after_discount = $total_price;

            if($discount_type == "P"){
                if($discount_value < 0) $discount_value = 0;
                if($discount_value >= 100) $discount_value = 100;

                $discount_percent = $discount_value;
                $discount_amount = ($total_price * $discount_percent)/100;
                $price_after_discount = $total_price - $discount_amount;

            }else{
                if($discount_value < 0) $discount_value = 0;
                if($discount_value > $total_price) $discount_value = $total_price;

                $discount_amount = $discount_value;
                $price_after_discount = $total_price - $discount_value;
                
            }
        }


        /*if($discount_type == "P"){
            if($discount_value < 0) $discount_value = 0;
            if($discount_value >= 100){
                $discount_value = 100;
            }
            $discount_percent = $discount_value;
            $discount_amount = ($price * $discount_percent)/100;
            $price_after_discount = $price - $discount_amount;
            
        }else{
            if($discount_value < 0) $discount_value = 0;
            if($discount_value > $price) $discount_value = $price;
            $discount_amount = $discount_value;
            $price_after_discount = $price - $discount_value;
        }*/

        //$total_price = $price_after_discount * $quantity;

        $fdata = [
                    "quotation_id"=>$docId,
                    "product_id"=>$product_id,
                    "product_name"=>$product_name,
                    "product_description"=>$product_description,
                    "quantity"=>$quantity,
                    "unit"=>$unit,
                    "price"=>$price,
                    "discount_type"=>$discount_type,
                    "discount_percent"=>$discount_percent,
                    "discount_amount"=>$discount_amount,
                    "total_price"=>round($price_after_discount, 2),
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
        }

        $db->trans_commit();

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

        $qrow = $db->select("*")
                    ->from("quotation")
                    ->where("id",$docId)
                    ->where("billing_type", $company_setting["company_billing_type"])
                    ->where("deleted", 0)
                    ->get()->row();

        if(empty($qrow)) return $this->data;

        $quotation_billing_type = $qrow->billing_type;
        $quotation_id = $this->data["doc_id"] = $docId;
        $quotation_number = $qrow->doc_number;
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

        }elseif($updateStatusTo == "I"){
            $db->where("id", $quotation_id);
            $db->update("quotation", ["status"=>"I"]);

            $item_table2 = "";

            $fields1 = [
                        "doc_date"=>date("Y-m-d"),
                        "billing_type"=>$quotation_billing_type,
                        "quotation_id"=>$quotation_id,
                        "reference_number"=>$quotation_number,
                        "project_id"=>$qrow->project_id,
                        "client_id"=>$qrow->client_id,
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
                        "remark"=>$qrow->remark,
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
                $invoice_credit = $qrow->credit;
                $invoice_due_date = date("Y-m-d", strtotime($invoice_date. " + ".$invoice_credit." days"));

                $fields2 = [
                                "doc_number"=>$this->Invoices_m->getNewDocNumber(),
                                "credit"=>$qrow->credit,
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
                            "price"=>$qirow->total_price / $qirow->quantity,
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
                    $this->data["dataset"] = $this->getIndexDataSetHTML($qrow);
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
                    $this->data["dataset"] = $this->getIndexDataSetHTML($qrow);
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
        $this->data["message"] = lang("record_saved");
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
