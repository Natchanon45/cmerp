<?php
class Sfg_m extends MY_Model {
    private $item_type = "SFG";
    
    function __construct() {
        parent::__construct();
    }

    function getRow($docId){
        $db = $this->db;

        $q = $db->select("*")
                    ->from("items")
                    ->where("id", $docId)
                    ->where("item_type", $this->item_type);

        
        if($this->Permission_m->access_semi_product_item == "own"){
            $q->where("created_by", $this->login_user->id);
        }

        $irow = $q->get()->row();

        if(empty($irow)) return null;

        return $irow;
        
    }

    function deleteRow(){
        $id = $this->input->post('id');
        validate_submitted_data(
            array(
                "id" => "required|numeric"
            )
        );

        if ($this->Bom_item_model->delete_material_and_sub_items($id)) {
            return array("success" => true, 'message' => lang('record_deleted'));
        } else {
            return array("success" => false, 'message' => lang('record_cannot_be_deleted'));
        }   
    }

    function saveCategory(){
        validate_submitted_data(
            array(
                "id" => "numeric",
                "title" => "required"
            )
        ); 

        $data = array(
            "id" => $this->input->post("id") ? $this->input->post("id") : null,
            "title" => $this->input->post("title"),
            "item_type" => $this->item_type
        ); 

        if (isset($data["id"]) && !empty($data["id"])) {
            $is_duplicate = false;
            $rows = $this->Material_categories_m->dev2_getDuplicatedCategoryByNameWithId($data["id"], $data["title"], $data["item_type"]);

            if ($rows > 0) $is_duplicate = true;
            
            if ($is_duplicate) {
                echo json_encode(array("success" => true, "post" => $is_duplicate, "message" => lang("item_cate_duplicate")));
                exit;
            } 
        } else {

            $is_duplicate = false;
            $rows = $this->Material_categories_m->dev2_getDuplicatedCategoryByName($data["title"], $data["item_type"]);

            if ($rows > 0) $is_duplicate = true;

            if ($is_duplicate) {
                echo json_encode(array("success" => true, "post" => $is_duplicate, "message" => lang("item_cate_duplicate")));
                exit;
            }
        } 

        $save_id = $this->Material_categories_m->dev2_postCategoryData($data);

        if ($save_id) { 
            return array(
                    "success" => true,
                    "data" => $this->Material_categories_m->dev2_getCategoryInfoById($save_id),
                    "post" => $is_duplicate,
                    "id" => $save_id,
                    "message" => lang("record_saved")
                );
        } else {
            return array("success" => false, "message" => lang("error_occurred"));
        }
    }

    function deleteCategory(){
        $post = $this->input->post();

        validate_submitted_data(
            array(
                "id" => "required|numeric"
            )
        );

        if ($this->Material_categories_m->dev2_deleteCategoryById($post["id"])) {
            return array("success" => true, "message" => lang("record_deleted"), "id" => $post["id"]);
        } else {
            return array("success" => false, "message" => lang("record_cannot_be_deleted"));
        }
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

        $category_title = $this->Material_categories_m->getTitle($irow->category_id);

        $data = [
                    "<a href='".get_uri('sfg/detail/' . $irow->id)."'>".$irow->id."</a>",
                    $preview,
                    $irow->item_code ? $irow->item_code : '-',
                    "<a href='".get_uri('sfg/detail/' . $irow->id)."'>".$irow->title."</a>",
                    nl2br($irow->description),
                    $category_title != null ? $category_title : "-",
                    $irow->unit_type ? $irow->unit_type : "",
                    @$irow->barcode ? '<div style="text-align:center"><a href="' . $src . '" class="barcode_img" download><img src="' . $src . '" /><div class="text">Click to download</div></a></div>' : '-',
                    to_decimal_format3($irow->rate, 3),
                    modal_anchor(get_uri("sfg/addedit"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_item'), "data-post-id" => $irow->id))."<a class='delete' data-id='".$irow->id."' data-action-url='".get_uri("sfg/addedit/delete")."' data-action='delete-confirmation'><i class='fa fa-times fa-fw'></i></a>"

                ];

        return $data;
    }

    function indexDataSet() {
        $db = $this->db;
        $company_setting = $this->Settings_m->getCompany();
        if($this->Permission_m->access_semi_product_item == false) return [];

        $db->select("items.*")
            ->from("items")
            ->join("bom_item_stocks", "bom_item_stocks.item_id = items.id", "left")
            ->where("item_type", $this->item_type)
            ->where("items.deleted", 0);

        if($this->login_user->is_admin != 1){
            if($this->Permission_m->access_semi_product_item == "own"){
                $db->where("created_by", $this->login_user->id);
            }
        }

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

    function saveDetailInfo(){
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
            "category_id" => $this->input->post('category_id') ? $this->input->post('category_id') : 0,
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
    }

    function getDetailPricingDataSetHTML($data){
        $buttons = "";
        
        $buttons .= modal_anchor(
            get_uri('stock/item_pricing_modal'), '<i class="fa fa-pencil"></i>', 
            array(
                'class' => 'edit',
                'title' => lang('stock_supplier_fg_pricing_edit'),
                'data-title' => lang('stock_supplier_fg_pricing_edit'),
                'data-post-id' => $data->id,
                'data-post-item_id' => $data->item_id,
                'data-post-supplier_id' => $data->supplier_id
            )
        );
        
        $buttons .= js_anchor(
            '<i class="fa fa-times fa-fw"></i>', 
            array(
                'title' => lang('stock_supplier_fg_pricing_delete'),
                'class' => 'delete',
                'data-id' => $data->id,
                'data-action-url' => get_uri('stock/supplier_fg_pricing_delete'),
                'data-action' => 'delete-confirmation'
            )
        );

        return [
            $data->id,
            anchor(get_uri('stock/supplier_view/' . $data->supplier_id), $data->supplier_data->company_name),
            (isset($data->supplier_contact->first_name) && !empty($data->supplier_contact->first_name)) ? $data->supplier_contact->first_name . ' ' . $data->supplier_contact->last_name : '-',
            (isset($data->supplier_contact->phone) && !empty($data->supplier_contact->phone)) ? $data->supplier_contact->phone : '-',
            (isset($data->supplier_contact->email) && !empty($data->supplier_contact->email)) ? $data->supplier_contact->email : '-',
            number_format($data->ratio, 4),
            $data->item_data->unit_type,
            number_format($data->price, 2),
            lang('THB'),
            $buttons
        ];
    }

    function detailPricingDataSet($docId) {
        $result = [];
        $list_data = $this->Items_model->dev2_getItemPricings(['item_id' => $docId]);

        if (sizeof($list_data)) {
            foreach ($list_data as $data) {
                $result[] = $this->getDetailPricingDataSetHTML($data);
            }
        }

        return $result;
    }

    function saveDetailPricing(){
        validate_submitted_data([
            'id' => 'numeric',
            'supplier_id' => 'required|numeric',
            'item_id' => 'required|numeric',
            'ratio' => 'required|numeric',
            'price' => 'required|numeric'
        ]);

        $post = $this->input->post();
        $data_id = '';
        $item = '';
        $type = '';

        if (isset($post['id']) && empty($post['id'])) {
            $item = $this->Bom_item_pricings_model->getItemPricingByItemSupplierId($post['item_id'], $post['supplier_id']);

            if (isset($item) && !empty($item)) {
                $type = 'patch';

                $this->Bom_item_pricings_model->patchItemPricingByPricingInfo($post['item_id'], $post['supplier_id'], [
                    'ratio' => $post['ratio'],
                    'price' => $post['price']
                ]);

                $data_id = $item->id;
            } else {
                $type = 'post';

                $data_id = $this->Bom_item_pricings_model->postItemPricingByInfo([
                    'item_id' => $post['item_id'],
                    'supplier_id' => $post['supplier_id'],
                    'ratio' => $post['ratio'],
                    'price' => $post['price']
                ]);
            }
        } else {
            $type = 'put';

            $this->Bom_item_pricings_model->putItemPricingByPricingInfo($post['id'], [
                'item_id' => $post['item_id'],
                'supplier_id' => $post['supplier_id'],
                'ratio' => $post['ratio'],
                'price' => $post['price']
            ]);

            $item = $this->Bom_item_pricings_model->getItemPricingById($post['id']);
            $data_id = $item->id;
        }

        $data_result = '';
        if ($data_id) {
            $data = $this->Items_model->dev2_getItemPricings(['id' => $data_id]);
            $data_result = $this->getDetailPricingDataSetHTML($data[0]);
        }

        return ['success' => true, 'data_post' => $post, 'data_result' => $data_result, 'data_result_id' => $data_id, 'type' => $type];
    }

    function getDetailMixingsDataSetHTML($data){
        $row_data = array(
            $data->id,
            modal_anchor(get_uri("sfg/detail_mixings_modal"), $data->name, array("class" => "edit", "title" => lang('item_mixing_edit'), "data-post-id" => $data->id, "data-post-item_id" => $data->item_id)),
            //$data->category_name,
            to_decimal_format2($data->ratio) . ' ' . $data->unit_type,
            $data->is_public == 1 ? lang('yes') : lang('no'),
            $data->is_public == 0 && !empty($data->for_client_id)
            ? anchor(get_uri("clients/view/" . $data->for_client_id), $data->company_name)
            : '-',
        );

        $row_data[] = modal_anchor(get_uri("sfg/detail_mixings_modal"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('item_mixing_edit'), "data-post-id" => $data->id, "data-post-item_id" => $data->item_id))
            . js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('item_mixing_delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("sfg/detail_mixings_modal/delete"), "data-action" => "delete-confirmation"));

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

        $view_data["sfg_dropdown"] = $this->Bom_item_model->dev2_getSfgDetail([])->result();
        $view_data["sfg_categories_dropdown"] = $this->Bom_item_mixing_groups_model->get_categories_list_sfg();
        
        $view_data["items_dropdown"] = ["" => "- " . lang("item_selected") . " -"];
        $items = $this->Items_model->get_details(["item_type"=>$this->item_type])->result();
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
        $item_types = $this->input->post("item_type[]");
        $this->Bom_item_mixing_groups_model->mixing_save($save_id, $material_ids, $cat_ids, $ratios, $item_types);

        if ($save_id) {
            return array(
                    "success" => true,
                    "data" => $this->getDetailMixingsDataSetHTML($this->Bom_item_mixing_groups_model->get_details(["id" => $save_id])->row()),
                    "id" => $save_id,
                    "view" => $this->input->post("view"),
                    "message" => lang("record_saved"));
            
        } else {
            return array("success" => false, "message" => lang("error_occurred"));
        }
    }

    function deleteDetailMixings(){
        validate_submitted_data(
            array(
                "id" => "required|numeric"
            )
        );

        $id = $this->input->post('id');
        if ($this->Bom_item_mixing_groups_model->delete_mixing($id)) {
            return array("success" => true, 'message' => lang('record_deleted'));
        } else {
            return array("success" => false, 'message' => lang('record_cannot_be_deleted'));
        }
    }

    function getDetailFileDataSetHTML($data){
        $file_icon = get_file_icon(strtolower(pathinfo($data->file_name, PATHINFO_EXTENSION)));

        $user_image = get_avatar($data->user_image);
        $user_name = "<span class='avatar avatar-xs mr10'><img src='$user_image' alt='...'></span> $data->user_name";
        if ($data->user_type == "staff") {
            $uploaded_by = get_team_member_profile_link($data->uploaded_by, $user_name);
        } else {
            $uploaded_by = get_client_contact_profile_link($data->uploaded_by, $user_name);
        }

        $description = "<div class='pull-left'>"
            . js_anchor(remove_file_prefix($data->file_name), array('title' => "", "data-toggle" => "app-modal", "data-sidebar" => "0", "data-url" => get_uri("stock/item_view_file/" . $data->id)));
        if ($data->description) {
            $description .= "<br /><span>" . trim($data->description) . "</span></div>";
        } else {
            $description .= "</div>";
        }

        $options = anchor(get_uri("stock/item_download_file/" . $data->id), "<i class='fa fa fa-cloud-download'></i>", array("title" => lang("download")));

        if ($this->Permission_m->bom_material_update) {
            $options .= js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete_file'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("stock/item_delete_file"), "data-action" => "delete-confirmation"));
        }

        return array(
            $data->id,
            "<div class='fa fa-$file_icon font-22 mr10 pull-left'></div>" . $description,
            convert_file_size($data->file_size),
            $uploaded_by,
            format_to_datetime($data->created_at),
            $options
        );
    }

    function detailFileDataSet($docId) {
        $options = array("item_id" => $docId);
        $list_data = $this->Bom_item_files_model->get_details($options)->result();

        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->getDetailFileDataSetHTML($data);
        }

        return $result;
    }

    

    function detailFile(){
        $post = $this->input->post();

        if (isset($post['id']) && !empty($post['id'])) {
            $view_data['model_info'] = $this->Bom_item_files_model->get_one($post['id']);
        }

        $view_data['item_id'] = $post['item_id'];

        return $view_data;
    }

    function saveDetailFile(){
        $item_id = $this->input->post('item_id');
        validate_submitted_data(
            array(
                "id" => "numeric",
                "item_id" => "required|numeric"
            )
        );

        $files = $this->input->post("files");
        $success = false;
        $now = get_current_utc_time();

        $target_path = getcwd() . "/" . get_general_file_path("client", $item_id);

        if ($files && get_array_value($files, 0)) {
            foreach ($files as $file) {
                $file_name = $this->input->post('file_name_' . $file);
                $file_info = move_temp_file($file_name, $target_path);
                if ($file_info) {
                    $data = array(
                        "item_id" => $item_id,
                        "file_name" => get_array_value($file_info, 'file_name'),
                        "file_id" => get_array_value($file_info, 'file_id'),
                        "service_type" => get_array_value($file_info, 'service_type'),
                        "description" => $this->input->post('description_' . $file),
                        "file_size" => $this->input->post('file_size_' . $file),
                        "created_at" => $now,
                        "uploaded_by" => $this->login_user->id
                    );
                    $success = $this->Bom_item_files_model->save($data); 
                } else {
                    $success = false;
                }
            }
        }

        if ($success) {
            return array("success" => true, 'message' => lang('record_saved'));
        } else {
            return array("success" => false, 'message' => lang('error_occurred'));
        }
    }

    function getDetailItemRemainingDataSetHTML($data){
        $remaining_value = 0;
        if (!empty($data->price) && !empty($data->stock) && $data->stock > 0) {
            $remaining_value = $data->price * $data->remaining / $data->stock;
        }

        //$can_delete = $this->dev2_canDeleteRestockItem($data->id);
        $can_delete = true;

        $user_name = $data->user_first_name . ' ' . $data->user_last_name;
        $user_image = get_avatar($data->user_image);
        $user_name = "<span class='avatar avatar-xs mr10'><img src='$user_image' alt='...'></span> $user_name";

        $row_data = array(
            $data->id,
            anchor(get_uri('sfg/restock_view/' . $data->group_id), $data->group_name),
            $data->serial_number ? $data->serial_number : '-',
            anchor(get_uri('team_members/view/' . $data->user_id), $user_name),
            format_to_date($data->created_date),
            is_date_exists($data->expiration_date) ? format_to_date($data->expiration_date, false) : '-',
            $data->stock ? to_decimal_format3($data->stock, 6) : to_decimal_format3(0, 6),
            $data->remaining ? to_decimal_format3($data->remaining, 6) : to_decimal_format3(0, 6),
            $data->item_unit ? mb_strtoupper($data->item_unit) : '-'
        );

        if ($this->Permission_m->bom_restock_read_price) {
            $row_data[] = to_decimal_format3($data->price, 3);
            $row_data[] = to_decimal_format3($remaining_value, 3);
            $row_data[] = lang('THB');
        }

        $options = '';
        if ($this->Permission_m->bom_restock_update && $data->remaining > 0) {
            $options .= modal_anchor(
                get_uri("sfg/restock_item_details_modal_addedit"),
                "<i class='fa fa-pencil'></i>",
                array("class" => "edit", "title" => "แก้ไขการนำเข้าสินค้ากึ่งสำเร็จ", "data-post-id" => $data->id)
            ); // btn-update
            $options .= modal_anchor(
                get_uri("sfg/restock_item_details_modal_withdraw"),
                "<i class='fa fa-share-square-o'></i>",
                array("class" => "edit", "title" => "เพิ่มการนำออกสินค้ากึ่งสำเร็จ", "data-post-id" => $data->id, "data-post-view" => "restock")
            ); // btn-withdraw
        } else {
            /*$options .= modal_anchor(
                get_uri("stock/restock_item_view_modal"), 
                "<i class='fa fa-eye'></i>", 
                array(
                    "class" => "edit", 
                    "title" => lang('stock_restock_item_edit'), 
                    "data-post-id" => $data->id, 
                    "data-post-view" => "item"
                )
            );*/
        }

        if ($can_delete && $this->Permission_m->bom_restock_delete) {
            $options .= js_anchor(
                "<i class='fa fa-times fa-fw'></i>",
                array(
                    "title" => lang('stock_restock_item_delete'),
                    "class" => "delete",
                    "data-id" => $data->id,
                    "data-action-url" => get_uri('sfg/restock_item_details_modal_withdraw/delete'),
                    "data-action" => "delete-confirmation"
                )
            ); // btn-delete
        }

        $row_data[] = $options;
        return $row_data;
    }

    function detailItemRemainingDataSet($docId) {
        $options = array("item_id" => $docId);
        $list_data = $this->Bom_item_groups_model->get_restocks($options)->result();

        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->getDetailItemRemainingDataSetHTML($data);
        }

        return $result;
    }

    function getDetailItemUsedDataSetHTML($data){
        $used_value = 0;
        if (!empty($data->price) && !empty($data->stock) && $data->stock > 0) {
            $used_value = $data->price * $data->ratio / $data->stock;
        } 

        $item_name = $data->item_code;
        if ($this->Permission_m->bom_material_read_production_name) {
            $item_name .= " - " . $data->item_name;
        }

        $mr_doc_number = $this->Materialrequest_m->getDocNumber($data->mr_id);

        $row_data = array(
            $data->id,
            anchor(get_uri('sfg/detail/' . $data->item_id), $item_name),
            !empty($data->project_title) ? anchor(get_uri('projects/view/' . $data->project_id), $data->project_title) : '-',
            $mr_doc_number != null ? "<a href='".get_uri('materialrequests/view/'.$data->mr_id)."'>".$mr_doc_number."</a>":"-",
            is_date_exists($data->created_at) ? format_to_date($data->created_at, false) : '-',
            !empty($data->created_by) ? $this->Account_category_model->created_by($data->created_by) : '-',
            !empty($data->note) ? $data->note : '-',
            to_decimal_format3($data->ratio, 6),
            mb_strtoupper($data->item_unit)
        );

        if ($this->Permission_m->bom_restock_read_price) {
            $row_data[] = to_decimal_format3($used_value, 3);
            $row_data[] = !empty($data->currency_symbol) ? lang($data->currency_symbol) : lang('THB');
        } 

        return $row_data;
    }

    function detailItemUsedDataSet($item_id) {
        $options = array("item_id" => $item_id);
        $list_data = $this->Bom_project_item_items_model->get_details($options)->result();
        $result = array();

        foreach ($list_data as $data) {
            $result[] = $this->getDetailItemUsedDataSetHTML($data);
        }

        return $result; 
    }

    function getIndexRestockDataSetHTML($item){
        $button = modal_anchor(get_uri('sfg/restock_import_modal'), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => "แก้ไขการนำเข้าสินค้ากึ่งสำเร็จ", "data-post-id" => $item->group_id));

        $button .= js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('stock_restock_delete'), "class" => "delete", "data-id" => $item->id, "data-action-url" => get_uri("stock/dev2_restock_item_delete"), "data-action" => "delete-confirmation"));
        

        // REMARK
        $item_name = $item->item_name;
        if (isset($item->item_code) && !empty($item->item_code)) {
            $item_name = mb_strtoupper($item->item_code) . ' - ' . $item->item_name;
        }

        $display_item = '<span>
            <span style="display: block;"><b>' . lang("stock_product_name") . '</b>: ' . anchor(get_uri('sfg/detail/' . $item->item_id), $item_name) . '</span>
        </span>';

        $mixing_name = null;
        if (isset($item->mixing_group_id) && !empty($item->mixing_group_id)) {
            $mixing_name = $this->Bom_item_groups_model->dev2_getMixingNameByMixingGroupId($item->mixing_group_id);

            $display_item = '<span>
                <span style="display: block;"><b>' . lang("stock_product_name") . '</b>: ' . anchor(get_uri('items/detail/' . $item->item_id), $item_name) . '</span>
                <span style="display: block;"><b>' . lang("production_order_bom_name") . '</b>: ' . $mixing_name . '</span>
            </span>';
        }

        return array(
            $item->id,
            anchor(get_uri('sfg/restock_view/' . $item->group_id), $item->group_name),
            $item->sern ? $item->sern : '-',
            $display_item,
            to_decimal_format3($item->stock_qty, 6),
            to_decimal_format3($item->remain_qty, 6),
            mb_strtoupper($item->item_unit),
            $item->create_by ? anchor(get_uri('team_members/view/' . $item->create_by), $this->Account_category_model->created_by($item->create_by)) : '',
            format_to_date($item->create_date),
            $button
        );
    }

    function indexRestockDataSet() {
        $post = $this->input->post('created_by') ? $this->input->post('created_by') : '';
        $result = $this->Bom_item_groups_model->dev2_getRestockingItemList($post, "SFG");
        
        $data = array();
        
        foreach ($result as $item) {
            $data[] = $this->getIndexRestockDataSetHTML($item);
        }

        return $data;
    }

    function restock(){
        $group_id = $this->input->post('id');
        validate_submitted_data(
            array(
                "id" => "numeric"
            )
        );

        $view_data["view"] = $this->input->post('view');
        $view_data['model_info'] = $this->Bom_item_groups_model->get_one($group_id);

        $view_data['item_dropdown'] = $this->Items_model->get_details(["item_type"=>$this->item_type])->result();
        $view_data['item_restocks'] = $this->Bom_item_groups_model->get_restocks(['group_id' => $group_id ? $group_id : -1])->result();
        // var_dump(arr($view_data['item_restocks'])); exit();

        foreach ($view_data['item_restocks'] as $key => $value) {
            $rows = $this->Bom_project_item_items_model->getCountStockUsedById($value->id);

            if ($rows == 0) $view_data['item_restocks'][$key]->can_delete = true;    
            else $view_data['item_restocks'][$key]->can_delete = false;
        }

        return $view_data;
    }

    function saveRestock(){
        $id = $this->input->post('id');

        validate_submitted_data(
            array(
                "id" => "numeric"
            )
        );

        $data = array(
            "name" => $this->input->post('name'),
            "created_by" => $this->input->post('created_by'),
            "created_date" => $this->input->post('created_date'),
            "po_no" => $this->input->post('po_no')
        );

        if (!$this->login_user->is_admin || empty($data["created_by"])) {
            $data["created_by"] = $this->login_user->id;
        }

        $data = clean_data($data);

        $save_id = $this->Bom_item_groups_model->save($data, $id);

        if ($save_id) {
            $restock_ids = $this->input->post('restock_id[]');
            $item_ids = $this->input->post('item_id[]');
            $expire_date = $this->input->post('expired_date[]');
            $stocks = $this->input->post('stock[]');
            $prices = $this->input->post('price[]');
            $serns = $this->input->post('sern[]');
            if (isset($restock_ids) && isset($item_ids) && isset($stocks)) {
                $this->Bom_item_groups_model->restock_item_save(
                    $save_id,
                    $restock_ids,
                    $item_ids,
                    $expire_date,
                    $stocks,
                    $prices,
                    $serns
                );
            }

            return array(
                    "success" => true,
                    "data" => $this->indexRestockDataSet(),
                    'id' => $save_id,
                    'view' => $this->input->post('view'),
                    'message' => lang('record_saved')
                );
            
        } else {
            return array("success" => false, 'message' => lang('error_occurred'));
        }
    }

    function deleteRestock(){

        $id = $this->input->post('id');
        validate_submitted_data(
            array(
                "id" => "required|numeric"
            )
        );

        echo json_encode(array('success' => true, 'message' => $id));
    }


    function getRestockViewDetailDataSetHTML($data) {
        $remaining_value = 0;
        if (!empty($data->price) && !empty($data->stock) && $data->stock > 0) {
            $remaining_value = $data->price * $data->remaining / $data->stock;
        }

        $rows = $this->Bom_project_item_items_model->getCountStockUsedById($data->id);
        if ($rows == 0) $can_delete = true;
        else $can_delete = false;
        

        $files_link = "";
        if ($data->files) {
            $files = unserialize($data->files);
            if (count($files)) {
                foreach ($files as $key => $value) {
                    $file_name = get_array_value($value, "file_name");
                    $link = " fa fa-" . get_file_icon(strtolower(pathinfo($file_name, PATHINFO_EXTENSION)));
                    $files_link .= js_anchor(
                        " ",
                        array(
                            "data-toggle" => "app-modal",
                            "data-sidebar" => "0",
                            "class" => "pull-left font-22 mr10 $link",
                            "title" => remove_file_prefix($file_name),
                            "data-url" => get_uri("stock/restock_file_preview/" . $data->id . "/" . $key)
                        )
                    );
                }
            }
        }

        // REMARK
        $item_name = $data->item_name;
        if (isset($data->item_code) && !empty($data->item_code)) {
            $item_name = mb_strtoupper($data->item_code) . ' - ' . $data->item_name;
        }

        $display_item = '<span>
            <span style="display: block;"><b>' . lang("stock_product_name") . '</b>: ' . anchor(get_uri('sfg/detail/' . $data->item_id), $item_name) . '</span>
        </span>';

        $mixing_name = null;
        if (isset($data->mixing_group_id) && !empty($data->mixing_group_id)) {
            $mixing_name = $this->Bom_item_groups_model->dev2_getMixingNameByMixingGroupId($data->mixing_group_id);

            $display_item = '<span>
                <span style="display: block;"><b>' . lang("stock_product_name") . '</b>: ' . anchor(get_uri('sfg/detail/' . $data->item_id), $item_name) . '</span>
                <span style="display: block;"><b>' . lang("production_order_bom_name") . '</b>: ' . $mixing_name . '</span>
            </span>';
        }

        $row_data = array(
            $data->id,
            $display_item,
            $data->serial_number ? $data->serial_number : '-',
            $files_link ? $files_link : '-',
            is_date_exists($data->expiration_date) ? format_to_date($data->expiration_date, false) : '-',
            to_decimal_format3($data->stock, 6),
            to_decimal_format3($data->remaining, 6),
            mb_strtoupper($data->item_unit)
        );

        if ($this->Permission_m->bom_restock_read_price) { 
            $row_data[] = to_decimal_format3($data->price, 3);
            $row_data[] = to_decimal_format3($remaining_value, 3);
            $row_data[] = !empty($data->currency_symbol) ? lang($data->currency_symbol) : lang('THB');
        }

        $options = ''; 
        if ($this->Permission_m->bom_restock_update && $data->remaining > 0) {
            $options .= modal_anchor(
                get_uri("sfg/restock_item_details_modal_addedit"),
                "<i class='fa fa-pencil'></i>",
                array("class" => "edit", "title" => "แก้ไขการนำเข้าสินค้ากึ่งสำเร็จ", "data-post-id" => $data->id)
            ); // btn-update
            $options .= modal_anchor(
                get_uri("sfg/restock_item_details_modal_withdraw"),
                "<i class='fa fa-share-square-o'></i>",
                array("class" => "edit", "title" => "เพิ่มการนำออกสินค้ากึ่งสำเร็จ", "data-post-id" => $data->id, "data-post-view" => "restock")
            ); // btn-withdraw
        } else { 
            /*$options .= modal_anchor(
                get_uri("stock/restock_item_view_modal"),
                "<i class='fa fa-eye'></i>",
                array("class" => "edit", "title" => lang('stock_restock_item_edit'), "data-post-id" => $data->id)
            );*/
        }

        if ($this->Permission_m->bom_restock_delete) { 
            $options .= js_anchor(
                "<i class='fa fa-times fa-fw'></i>",
                array(
                    "title" => lang('stock_restock_item_delete'),
                    "class" => "delete",
                    "data-id" => $data->id,
                    "data-action-url" => get_uri('sfg/restock_item_details_modal_withdraw/delete'),
                    "data-action" => "delete-confirmation"
                )
            ); // btn-delete
        }

        $row_data[] = $options;
        return $row_data;
    }

    function restockViewDetail(){
        $view_data['can_read_price'] = $this->Permission_m->bom_restock_read_price;
        $view_data['can_create'] = $this->Permission_m->bom_restock_create;
        $view_data['can_update'] = $this->Permission_m->bom_restock_update;
        $view_data['bom_material_read_production_name'] = $this->Permission_m->bom_material_read_production_name;

        $restock_id = $this->input->post('id');
        validate_submitted_data(
            array(
                "id" => "numeric"
            )
        );

        $view_data['view'] = $this->input->post('view');
        $view_data['model_info'] = $this->Bom_item_stocks_model->get_one($restock_id); 

        if ($view_data['model_info']->stock > 0 && $view_data['model_info']->price > 0) {
            $view_data['model_info']->priceunit = $view_data['model_info']->price / $view_data['model_info']->stock;
            $view_data['model_info']->priceunit = to_decimal_format3($view_data['model_info']->priceunit);
        } 

        if (!empty($view_data['model_info']->id)) {
            $rows = $this->Bom_project_item_items_model->getCountStockUsedById($view_data['model_info']->id);
            if ($rows == 0) $view_data['model_info']->can_delete = true;
            else $view_data['model_info']->can_delete = false;
        
        }

        if (empty($view_data['model_info']->id)) {
            
        } else {
            $group_info = $this->Bom_item_groups_model->get_one($view_data['model_info']->group_id);
            $created_by = $group_info->created_by;
        }

        $group_id = $this->input->post('group_id'); 
        if (!empty($group_id) && empty($view_data['model_info']->group_id)) {
            $view_data['model_info']->group_id = $group_id;
        }

        $view_data['item_dropdown'] = $this->Items_model->get_details(["item_type"=>$this->item_type])->result();

        return $view_data;   
    }

    function saveRestockViewDetail(){
        $id = $this->input->post('id');
        if (empty($id)) {
            if (!$this->Permission_m->bom_restock_create) {
                return array("success" => false, 'message' => lang('no_permissions'));
            }
        } else {
            if (!$this->Permission_m->bom_restock_update) {
                return array("success" => false, 'message' => lang('no_permissions'));
            }
        } 

        validate_submitted_data(
            array(
                "id" => "numeric",
                "group_id" => "required|numeric",
                "sern" => "required"
            )
        ); 

        $serial_number = $this->input->post('sern');
        if (!$id) {
            $serials = $this->Bom_item_stocks_model->dev2_getSerialNumByGroupId($this->input->post('group_id'));
            $is_duplicate = in_array($serial_number, $serials);

            if ($is_duplicate) {
                return array('success' => false, 'message' => lang('serial_number_duplicate'));
            }
        } else {
            $serials = $this->Bom_item_stocks_model->dev2_getSerialNumByGroupIdWithoutSelf($this->input->post('group_id'), $this->input->post('id'));
            $is_duplicate = in_array($serial_number, $serials);

            if ($is_duplicate) {
                return array('success' => false, 'message' => lang('serial_number_duplicate'));
            }
        } 

        $data = array(
            "group_id" => $this->input->post('group_id'),
            "item_id" => $this->input->post('item_id'),
            "serial_number" => $serial_number,
            "stock" => $this->input->post('stock'),
            "remaining" => $this->input->post('remaining'),
            "note" => $this->input->post('note'),
            "expiration_date" => $this->input->post('expiration_date')
        );

        if ($this->Permission_m->bom_restock_read_price) $data["price"] = $this->input->post('price');

        $target_path = get_setting("timeline_file_path"); 
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "material");
        $new_files = unserialize($files_data);
        if ($id) {
            $model_info = $this->Bom_item_stocks_model->get_one($id);
            $timeline_file_path = get_setting("timeline_file_path");
            $new_files = update_saved_files($timeline_file_path, $model_info->files, $new_files);
        }
        $data["files"] = serialize($new_files);
        $data = clean_data($data);

        $save_id = $this->Bom_item_stocks_model->save($data, $id); 
        if ($save_id) {
            $view = $this->input->post('view');
            if (isset($view) && $view == 'item') {
                $data = $this->Bom_item_groups_model->get_restocks(['id' => $save_id])->row();
                
                return array(
                            "success" => true,
                            "data" => $this->Sfg_m->getRestockViewDetailDataSetHTML($this->Bom_item_groups_model->get_restocks(['id' => $save_id])->row()),
                            'id' => $save_id,
                            'view' => $this->input->post('view'),
                            'message' => lang('record_saved')
                        );
            } else {
                return array(
                        "success" => true,
                        "data" => $this->Sfg_m->getRestockViewDetailDataSetHTML($this->Bom_item_groups_model->get_restocks(['id' => $save_id])->row()),
                        'id' => $save_id,
                        'view' => $this->input->post('view'),
                        'message' => lang('record_saved')
                    );
            }
        } else {
            return array("success" => false, 'message' => lang('error_occurred'));
        }
    }

    function restockViewDetailWithdraw(){
        $restock_id = $this->input->post('id');

        validate_submitted_data(
            array(
                "id" => "numeric"
            )
        );

        $view_data["view"] = $this->input->post('view');
        $view_data['model_info'] = $this->Bom_item_stocks_model->get_one($restock_id);

        $group_id = $this->input->post('group_id');
        if (!empty($group_id) && empty($view_data['model_info']->group_id)) {
            $view_data['model_info']->group_id = $group_id;
        }

        $view_data['item_dropdown'] = $this->Items_model->get_details(["item_type"=>"SFG"])->result();

        return $view_data;
    }

    function saveRestockViewDetailWithdraw(){
        $id = $this->input->post('id');
        validate_submitted_data(
            array(
                "id" => "required|numeric",
                "item_id" => "required|numeric",
                "ratio" => "required|numeric"
            )
        );

        $ratio = $this->input->post('ratio'); 
        $data = array(
            "item_id" => $this->input->post('item_id'),
            "stock_id" => $id,
            "ratio" => $ratio,
            "note" => $this->input->post('note') ? $this->input->post('note') : ''
        );
        $data = clean_data($data);

        $save_id = $this->Bom_project_item_items_model->save($data, null);
        if ($save_id) {
            $this->Bom_item_stocks_model->reduce_material($id, $ratio);
            $view = $this->input->post('view');
            $data = $this->Bom_item_groups_model->get_restocks(['id' => $id])->row();

            if (isset($view) && $view == 'item') {
                return array(
                            "success" => true,
                            "data" => $this->getRestockViewDetailDataSetHTML($data), 
                            'id' => $id,
                            'view' => $this->input->post('view'),
                            'message' => lang('record_saved')
                        );
            } else {
                return
                    array(
                        "success" => true,
                        "data" => $this->getRestockViewDetailDataSetHTML($data),
                        'id' => $id,
                        'view' => $this->input->post('view'),
                        'message' => lang('record_saved')
                    );
            }
        } else { 
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

    function deleteRestockViewDetailWithdraw(){
        validate_submitted_data(
            array(
                "id" => "required|numeric"
            )
        );

        $id = $this->input->post('id');
        if ($this->Bom_item_stocks_model->delete_one($id)) {
            return array("success" => true, 'message' => lang('record_deleted'));
        } else {
            return array("success" => false, 'message' => lang('record_cannot_be_deleted'));
        }
    }

    function getReportDataSetHTML($data){
        $remaining_value = 0;
        if (!empty($data->price) && !empty($data->stock) && $data->stock > 0) {
            $remaining_value = $data->price * $data->remaining / $data->stock;
        }

        // REMARK
        $item_name = $data->item_name;
        if (isset($data->item_code) && !empty($data->item_code)) {
            $item_name = mb_strtoupper($data->item_code) . ' - ' . $data->item_name;
        }

        $display_item = '<span>
            <span style="display: block;"><b>' . lang("stock_product_name") . '</b>: ' . anchor(get_uri('sfg/detail/' . $data->item_id), $item_name) . '</span>
        </span>';

        $mixing_name = null;
        if (isset($data->mixing_group_id) && !empty($data->mixing_group_id)) {
            $mixing_name = $this->Bom_item_groups_model->dev2_getMixingNameByMixingGroupId($data->mixing_group_id);

            $display_item = '<span>
                <span style="display: block;"><b>' . lang("stock_product_name") . '</b>: ' . anchor(get_uri('stock/item_view/' . $data->item_id), $item_name) . '</span>
                <span style="display: block;"><b>' . lang("production_order_bom_name") . '</b>: ' . $mixing_name . '</span>
            </span>';
        }

        $lack = $data->noti_threshold - $data->remaining;
        $is_lack = $lack > 0 ? true : false;
        $row_data = array(
            $data->id,
            anchor(get_uri('sfg/restock_view/' . $data->group_id), $data->group_name),
            $display_item,
            $data->item_desc,
            format_to_date($data->created_date),
            is_date_exists($data->expiration_date) ? format_to_date($data->expiration_date, false) : '-',
            to_decimal_format3($data->stock),
            '<span class="' . ($is_lack ? 'lacked_material' : '') . '" data-item-id="' . $data->item_id . '" data-lacked-amount="' . ($is_lack ? $lack : 0) . '" data-unit="' . mb_strtoupper($data->item_unit) . '" data-supplier-id="' . $data->supplier_id . '" data-supplier-name="' . $data->supplier_name . '" data-price="' . $data->price . '" data-currency="' . $data->currency . '" data-currency-symbol="' . $data->currency_symbol . '">' . to_decimal_format3($data->remaining) . '</span>',
            mb_strtoupper($data->item_unit)
        );

        if ($this->Permission_m->bom_restock_read_price == true) {
            $price_per_stock = 0;
            if ($data->stock != 0) {
                $price_per_stock = $data->price / $data->stock;
            }

            $row_data[] = to_decimal_format3($data->price);
            $row_data[] = to_decimal_format3($price_per_stock);
            $row_data[] = to_decimal_format3($remaining_value);
            $row_data[] = !empty($data->currency) && isset($data->currency) ? lang($data->currency) : lang("THB");
        }

        return $row_data;
    }

    function reportDataSet() {
        $is_zero = $this->input->post("is_zero");
        $startDate = $this->input->post("start_date");
        $endDate = $this->input->post("end_date");

        $options = array();

        $options["item_type"] = "SFG";

        if (!$this->Permission_m->bom_restock_read_self && !$this->Permission_m->bom_restock_read) $options['created_by'] = $this->login_user->id;

        if ((isset($startDate) && !empty($startDate)) && (isset($endDate) && !empty($endDate))) {
            $options["start_date"] = $startDate;
            $options["end_date"] = $endDate;
        }

        if (isset($is_zero) && !empty($is_zero)) {
            $options["is_zero"] = $is_zero;
        }

        $list_data = $this->Bom_item_groups_model->get_restocks2($options)->result();
        // var_dump(arr($list_data)); exit;

        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->getReportDataSetHTML($data);
        }

        return $result;
    }

    function report(){
        $view_data['can_update'] = !$this->Permission_m->bom_material_update && !$this->Permission_m->bom_restock_update;
        $view_data['can_delete'] = !$this->Permission_m->bom_material_delete && !$this->Permission_m->bom_restock_delete;
        $view_data['can_read_price'] = $this->Permission_m->bom_restock_read_price;
        $view_data['is_admin'] = $this->login_user->is_admin;

        return $view_data;
    }
}