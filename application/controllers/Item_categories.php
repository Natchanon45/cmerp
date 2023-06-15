<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Item_categories extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->access_only_admin();
    }

    // Load item categories list view
    function index() {
        $this->template->rander("item_categories/index");
    }

    // Load item category add/edit modal form
    function modal_form() {
        validate_submitted_data(array(
            "id" => "numeric"
        ));
        
        $view_data['model_info'] = $this->Item_categories_model->get_one($this->input->post('id'));
        $this->load->view('item_categories/modal_form', $view_data);
    }

    // Save item category
    function save() {
        validate_submitted_data(array(
            "id" => "numeric",
            "title" => "required"
        ));

        $is_duplicate = false;
        $id = $this->input->post('id');
        if ($id) {
            $is_duplicate = $this->dev2_itemCateDuplicateWithId($this->input->post('title'), $this->input->post('id'));
        } else {
            $is_duplicate = $this->dev2_itemCateDuplicate($this->input->post('title'));
        }

        if ($is_duplicate) {
            echo json_encode(array("success" => false, 'message' => lang('item_cate_duplicate')));
            return;
        }

        $data = array(
            "title" => $this->input->post('title')
        );

        $save_id = $this->Item_categories_model->save($data, $id);
        if ($save_id) {
            echo json_encode(array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, 'message' => lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

    // Delete/undo an item category
    function delete() {
        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->input->post('id');
        if ($this->input->post('undo')) {
            if ($this->Item_categories_model->delete($id, true)) {
                echo json_encode(array("success" => true, "data" => $this->_row_data($id), "message" => lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, lang('error_occurred')));
            }
        } else {
            if ($this->Item_categories_model->delete($id)) {
                echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
            }
        }
    }

    // Get data for items category list
    function list_data() {
        $list_data = $this->Item_categories_model->get_details()->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    // Get an expnese category list row
    private function _row_data($id) {
        $options = array("id" => $id);
        $data = $this->Item_categories_model->get_details($options)->row();
        
        return $this->_make_row($data);
    }

    // Prepare an item category list row
    private function _make_row($data) {
        $options = "";
        $can_delete = $this->dev2_itemCateCanDelete($data->id);

        $options .= modal_anchor(
            get_uri("item_categories/modal_form"), 
            "<i class='fa fa-pencil'></i>", 
            array(
                "class" => "edit", 
                "title" => lang('edit_items_category'), 
                "data-post-id" => $data->id
            )
        ); // btn-edit

        if ($can_delete) {
            $options .= js_anchor(
                "<i class='fa fa-times fa-fw'></i>", 
                array(
                    "title" => lang('delete_items_category'), 
                    "class" => "delete", 
                    "data-id" => $data->id, 
                    "data-action-url" => get_uri("item_categories/delete"), 
                    "data-action" => "delete"
                )
            ); // btn-delete
        }
        
        return array(
            $data->id,
            $data->title,
            $options
        );
    }

    private function dev2_itemCateDuplicate($name)
    {
        $is_duplicate = false;
        $rows = $this->Item_categories_model->dev2_getItemCateByName($name);

        if ($rows > 0) {
            $is_duplicate = true;
        }
        return $is_duplicate;
    }

    private function dev2_itemCateDuplicateWithId($name, $id)
    {
        $is_duplicate = false;
        $rows = $this->Item_categories_model->dev2_getItemCateByNameWithId($name, $id);

        if ($rows > 0) {
            $is_duplicate = true;
        }
        return $is_duplicate;
    }

    private function dev2_itemCateCanDelete($id)
    {
        $can_delete = true;
        $rows = $this->Item_categories_model->dev2_getCountItemCateById($id);

        if ($rows > 0) {
            $can_delete = false;
        }
        return $can_delete;
    }

    public function dev2_countItemCateById($id)
    {
        $rows = $this->Item_categories_model->dev2_getCountItemCateById($id);
        echo $rows;
    }

}

/* End of file item_categories.php */
/* Location: ./application/controllers/item_categories.php */