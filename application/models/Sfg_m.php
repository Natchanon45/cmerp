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
        

        return $this->data;
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

    function getDetailMixingsDataSetHTML($data){
        $row_data = array(
            $data->id,
            modal_anchor(get_uri("items/detail_mixing_modal"), $data->name, array("class" => "edit", "title" => lang('item_mixing_edit'), "data-post-id" => $data->id, "data-post-item_id" => $data->item_id)),
            //$data->category_name,
            to_decimal_format2($data->ratio) . ' ' . $data->unit_type,
            $data->is_public == 1 ? lang('yes') : lang('no'),
            $data->is_public == 0 && !empty($data->for_client_id)
            ? anchor(get_uri("clients/view/" . $data->for_client_id), $data->company_name)
            : '-',
        );

        $row_data[] = modal_anchor(get_uri("items/detail_mixing_modal"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('item_mixing_edit'), "data-post-id" => $data->id, "data-post-item_id" => $data->item_id))
            . js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('item_mixing_delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("items/detail_mixing_delete"), "data-action" => "delete-confirmation"));

        return $row_data;
    }

    function detailMixingsDataSet($docId) {
        $result = [];
        $list_data = $this->Bom_item_mixing_groups_model->get_details(['item_id' => $docId])->result();

        foreach ($list_data as $data) {
            $result[] = $this->getDetailMixingsDataSetHTML($data);
        }

        return $result;
    }

    function detailMixings(){
        validate_submitted_data(
            array(
                "id" => "numeric",
                "item_id" => "required|numeric"
            )
        );

        $id = $this->input->post("id");
        $item_id = $this->input->post("item_id");

        $view_data["label_column"] = "col-md-3";
        $view_data["field_column"] = "col-md-9";

        $view_data["view"] = $this->input->post("view");
        $view_data["model_info"] = $this->Bom_item_mixing_groups_model->get_one($id);
        $view_data["item"] = $this->Items_model->get_one($item_id);

        $view_data["material_dropdown"] = $this->Bom_materials_model->get_details([])->result();
        $view_data["clients_dropdown"] = $this->Clients_model->get_dropdown_list(array("company_name"), "id", array("is_lead" => 0));
        $view_data["categories_dropdown"] = $this->Bom_item_mixing_groups_model->get_categories_list();
        
        $view_data["items_dropdown"] = ["" => "- " . lang("item_selected") . " -"];
        $items = $this->Items_model->get_details()->result();
        foreach ($items as $item) {
            $view_data["items_dropdown"][$item->id] = $item->title;
        }

        $view_data["material_mixings"] = [];
        $view_data["material_cat_mixings"] = [];

        if (!empty($id)) {
            $view_data["material_mixings"] = $this->Bom_item_mixing_groups_model->get_mixings(["group_id" => $id])->result();
            foreach ($view_data["material_mixings"] as $mx) {
                if (!isset($view_data["material_cat_mixings"][$mx->cat_id])) {
                    $view_data["material_cat_mixings"][$mx->cat_id] = [];
                }
                $view_data["material_cat_mixings"][$mx->cat_id][] = $mx;
            }
        }

        if (empty($view_data["model_info"]->item_id)) {
            $view_data["model_info"]->item_id = $item_id;
            $view_data["model_info"]->is_public = 1;
        }

        return $view_data;
    }

    function saveDetailMixings(){
        $id = $this->input->post("id");
        $item_id = $this->input->post("item_id");
        $is_public = $this->input->post("is_public");
        $clone_to_new_item = $this->input->post("clone_to_new_item");

        validate_submitted_data(
            array(
                "id" => "numeric",
                "item_id" => "required|numeric",
                "name" => "required",
                "ratio" => "required|numeric"
            )
        );

        if ($clone_to_new_item) {
            $target_path = get_setting("timeline_file_path");
            $item = $this->Items_model->get_one($item_id);
            $new_files = unserialize($item->files);
            $files_data = copy_files($new_files, $target_path, "item");
            $new_files = unserialize($files_data);

            $item_data = array(
                "title" => $item->title . "[COPY]",
                "description" => $item->description,
                "category_id" => $item->category_id,
                "unit_type" => $item->unit_type,
                "rate" => $item->rate,
                "show_in_client_portal" => 0,
                "files" => serialize($new_files)
            );
            $item_id = $this->Items_model->save($item_data, 0);
            $item = $this->Items_model->get_one($item_id);
        }

        $data = array(
            "item_id" => $item_id,
            "name" => $this->input->post("name"),
            "ratio" => $this->input->post("ratio"),
            "is_public" => $is_public,
            "for_client_id" => $is_public == 1 ? null : $this->input->post("for_client_id")
        );
        $data = clean_data($data);

        $save_id = $this->Bom_item_mixing_groups_model->save($data, $id);
        $material_ids = $this->input->post("material_id[]");
        $cat_ids = $this->input->post("cat_id[]");
        $ratios = $this->input->post("mixing_ratio[]");
        $this->Bom_item_mixing_groups_model->mixing_save($save_id, $material_ids, $cat_ids, $ratios);

        if ($save_id) {
            echo json_encode(
                array(
                    "success" => true,
                    "data" => $this->_detail_mixing_row_data($save_id),
                    "id" => $save_id,
                    "view" => $this->input->post("view"),
                    "message" => lang("record_saved")
                )
            );
        } else {
            echo json_encode(array("success" => false, "message" => lang("error_occurred")));
        }
    }

    function deleteDetailMixings(){
        
    }

}
