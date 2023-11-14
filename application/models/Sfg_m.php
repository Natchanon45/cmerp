<?php
class Sfg_m extends MY_Model {
    private $item_type = "SFG";
    
    function __construct() {
        parent::__construct();
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
                    $irow->rate,
                    modal_anchor(get_uri("sfg/addedit"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_item'), "data-post-id" => $irow->id))."<a class='delete' data-id='".$irow->id."' data-action-url='".get_uri("sfg/addedit/delete")."' data-action='delete-confirmation'><i class='fa fa-times fa-fw'></i></a>"

                ];

        return $data;
    }

    function indexDataSet() {
        $db = $this->db;
        $company_setting = $this->Settings_m->getCompany();

        $db->select("items.*")
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

    function getIndexRestockDataSetHTML($item){
        $button = modal_anchor(get_uri('sfg/restock_addedit'), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('stock_restock_item_edit'), "data-post-id" => $item->group_id));

        $button .= js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('stock_restock_delete'), "class" => "delete", "data-id" => $item->id, "data-action-url" => get_uri("stock/dev2_restock_item_delete"), "data-action" => "delete-confirmation"));
        

        // REMARK
        $item_name = $item->item_name;
        if (isset($item->item_code) && !empty($item->item_code)) {
            $item_name = mb_strtoupper($item->item_code) . ' - ' . $item->item_name;
        }

        $display_item = '<span>
            <span style="display: block;"><b>' . lang("stock_product_name") . '</b>: ' . anchor(get_uri('items/detail/' . $item->item_id), $item_name) . '</span>
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
            anchor(get_uri('stock/restock_item_view/' . $item->group_id), $item->group_name),
            $item->sern ? $item->sern : '-',
            $display_item,
            to_decimal_format3($item->stock_qty),
            to_decimal_format3($item->remain_qty),
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
        // var_dump(arr($result)); exit();
        foreach ($result as $item) {
            $data[] = $this->getIndexRestockDataSetHTML($item);
        }

        // var_dump(arr($data)); exit();
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
                    "data" => $this->getIndexRestockDataSetHTML($this->Bom_item_groups_model->get_details(['id' => $id])->row()),
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
}