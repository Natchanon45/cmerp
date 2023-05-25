<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Receipts extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->init_permission_checker("receipt");
        $this->load->model('Pr_items_model');
        $this->load->model('Receipts_model');
    }

    function index()
    {
        $this->check_access_to_store();

        $view_data["custom_field_headers"] = $this->Custom_fields_model->get_custom_field_headers_for_table("Receipts", $this->login_user->is_admin, $this->login_user->user_type);

        if ($this->login_user->user_type === "staff") {
            $view_data['receipt_statuses'] = $this->Order_status_model->get_details()->result();
            $this->template->rander("receipts/index", $view_data);
        } else {
            //client view
            $view_data["client_info"] = $this->Clients_model->get_one($this->login_user->client_id);
            $view_data['client_id'] = $this->login_user->client_id;
            $view_data['page_type'] = "full";

            $this->template->rander("clients/receipts/client_portal", $view_data);
        }
    }

    function process_receipt()
    {
        $this->check_access_to_store();
        $view_data = get_receipt_making_data();
        $view_data["cart_items_count"] = $this->receipt_items_model->get_all_where(array("created_by" => $this->login_user->id, "receipt_id" => 0, "deleted" => 0))->num_rows();

        $view_data['clients_dropdown'] = "";
        if ($this->login_user->user_type == "staff") {
            $view_data['clients_dropdown'] = $this->_get_clients_dropdown();
        }

        $this->template->rander("receipts/process_receipt", $view_data);
    }

    function item_list_data_of_login_user()
    {
        $this->check_access_to_store();
        $options = array("created_by" => $this->login_user->id, "processing" => true);
        $list_data = $this->receipt_items_model->get_details($options)->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_item_row($data);
        }

        echo json_encode(array("data" => $result));
    }

    /* prepare a row of receipt item list table */

    private function _make_item_row($data)
    {
        $item = "<div class='item-row strong mb5' data-id='$data->id'><i class='fa fa-bars pull-left move-icon'></i> $data->title</div>";
        if ($data->description) {
            $item .= "<span>" . nl2br($data->description) . "</span>";
        }
        $type = $data->unit_type ? $data->unit_type : "";

        return array(
            $data->sort,
            $item,
            to_decimal_format($data->quantity) . " " . $type,
            to_currency($data->rate, $data->currency_symbol),
            to_currency($data->total, $data->currency_symbol),
            modal_anchor(get_uri("receipts/item_modal_form"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_item'), "data-post-id" => $data->id, "data-post-receipt_id" => $data->receipt_id))
                . js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("Receipts/delete_item"), "data-action" => "delete"))
        );
    }

    /* load item modal */

    function item_modal_form()
    {
        $this->check_access_to_store();
        validate_submitted_data(array(
            "id" => "numeric"
        ));

        $model_info = $this->Receipt_items_model->get_one($this->input->post('id'));
        $this->check_access_to_this_receipt_item($model_info);

        $view_data['model_info'] = $model_info;
        $view_data['receipt_id'] = $this->input->post('receipt_id');

        $this->load->view('receipts/item_modal_form', $view_data);
    }

    /* add or edit an receipt item */

    function save_item()
    {
        $this->check_access_to_store();
        validate_submitted_data(array(
            "id" => "numeric"
        ));

        // var_dump($this->input->post());
        // exit;

        $id = $this->input->post('id');
        $item_info = $this->Receipt_items_model->get_one($id);
        $this->check_access_to_this_receipt_item($item_info);

        $quantity = unformat_currency($this->input->post('receipt_item_quantity'));

        $receipt_item_data = array(
            "description" => $this->input->post('receipt_item_description'),
            "quantity" => $quantity
        );
        $receipt_id = $this->input->post("receipt_id");

        if (empty($this->input->post('po_id'))) {
            if ($receipt_id) {
                $rate = unformat_currency($this->input->post('receipt_item_rate'));
                $receipt_item_data["po_id"] = !empty($this->input->post('po_item_title')) ? $this->input->post('po_item_title') : '';
                $receipt_item_data["receipt_id"] = $receipt_id;
                $receipt_item_data["title"] = $this->input->post('receipt_item_title');
                $receipt_item_data["unit_type"] = $this->input->post('receipt_unit_type');
                $receipt_item_data["rate"] = unformat_currency($this->input->post('receipt_item_rate'));
                $receipt_item_data["total"] = $rate * $quantity;
            } else {
                $receipt_item_data["total"] = $item_info->rate * $quantity;
            }

            // var_dump($receipt_item_data);exit;

            $receipt_item_id = $this->Receipt_items_model->save($receipt_item_data, $id);
            if ($receipt_item_id) {

                //check if the add_new_item flag is on, if so, add the item to libary. 
                $add_new_item_to_library = $this->input->post('add_new_item_to_library');
                if ($add_new_item_to_library) {
                    $library_item_data = array(
                        "title" => $this->input->post('receipt_item_title'),
                        "po_id" => '',
                        "description" => $this->input->post('receipt_item_description'),
                        "unit_type" => $this->input->post('receipt_unit_type'),
                        "rate" => unformat_currency($this->input->post('receipt_item_rate'))
                    );
                    //MARK
                    $this->Items_model->save($library_item_data);
                    
                }

                $options = array("id" => $receipt_item_id);
                $item_info = $this->receipt_items_model->get_details($options)->row();

                echo json_encode(array("success" => true, "receipt_id" => $item_info->receipt_id, "data" => $this->_make_item_row($item_info), "receipt_total_view" => $this->_get_receipt_total_view($item_info->receipt_id), 'id' => $receipt_item_id, 'message' => lang('record_saved')));
            } else {
                echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
            }
            exit;
        } else {
            $rate = unformat_currency($this->input->post('receipt_item_rate'));

            $receipt_item_data["total"] =  $item_info->rate * $quantity;
            



            $receipt_item_id = $this->Receipt_items_model->save($receipt_item_data, $id);
            if ($receipt_item_id) {

                //check if the add_new_item flag is on, if so, add the item to libary. 
                $add_new_item_to_library = $this->input->post('add_new_item_to_library');
                if ($add_new_item_to_library) {
                    $library_item_data = array(
                        "title" => $this->input->post('receipt_item_title'),
                        "po_id" => $this->input->post('po_item_title'),
                        "description" => $this->input->post('receipt_item_description'),
                        "unit_type" => $this->input->post('receipt_unit_type'),
                        "rate" => unformat_currency($this->input->post('receipt_item_rate'))
                    );
                    $this->Items_model->save($library_item_data);
                }

                $options = array("id" => $receipt_item_id);
                $item_info = $this->Receipt_items_model->get_details($options)->row();

                echo json_encode(array("success" => true, "receipt_id" => $item_info->receipt_id, "data" => $this->_make_item_row($item_info), 'id' => $receipt_item_id, 'message' => lang('record_saved')));
            } else {
                echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
            }
        }
    }

    //update the sort value for receipt item
    function update_item_sort_values($id = 0)
    {
        $this->check_access_to_store();
        $sort_values = $this->input->post("sort_values");
        if ($sort_values) {

            //extract the values from the comma separated string
            $sort_array = explode(",", $sort_values);

            //update the value in db
            foreach ($sort_array as $value) {
                $sort_item = explode("-", $value); //extract id and sort value

                $id = get_array_value($sort_item, 0);
                $sort = get_array_value($sort_item, 1);

                $data = array("sort" => $sort);
                $this->receipt_items_model->save($data, $id);
            }
        }
    }

    /* delete or undo an receipt item */

    function delete_item()
    {
        $this->check_access_to_store();
        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->input->post('id');
        $receipt_item_info = $this->receipt_items_model->get_one($id);
        $this->check_access_to_this_receipt_item($receipt_item_info);

        if ($this->input->post('undo')) {
            if ($this->receipt_items_model->delete($id, true)) {
                $options = array("id" => $id);
                $item_info = $this->receipt_items_model->get_details($options)->row();
                echo json_encode(array("success" => true, "receipt_id" => $item_info->receipt_id, "data" => $this->_make_item_row($item_info), "receipt_total_view" => $this->_get_receipt_total_view($item_info->receipt_id), "message" => lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, lang('error_occurred')));
            }
        } else {
            if ($this->receipt_items_model->delete($id)) {
                $item_info = $this->receipt_items_model->get_one($id);
                echo json_encode(array("success" => true, "receipt_id" => $item_info->receipt_id, "receipt_total_view" => $this->_get_receipt_total_view($item_info->receipt_id), 'message' => lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
            }
        }
    }

    /* receipt total section */

    private function _get_receipt_total_view($receipt_id = 0)
    {
        if ($receipt_id) {
            $view_data["receipt_total_summary"] = $this->Receipts_model->get_receipt_total_summary($receipt_id);
            $view_data["receipt_id"] = $receipt_id;
            return $this->load->view('Receipts/receipt_total_section', $view_data, true);
        } else {
            $view_data = get_receipt_making_data();
            return $this->load->view('Receipts/processing_receipt_total_section', $view_data, true);
        }
    }

    function place_receipt()
    {
        $this->check_access_to_store();

        $receipt_items = $this->receipt_items_model->get_all_where(array("created_by" => $this->login_user->id, "receipt_id" => 0, "deleted" => 0))->result();
        if (!$receipt_items) {
            show_404();
        }

        $receipt_data = array(
            "client_id" => $this->input->post("client_id") ? $this->input->post("client_id") : $this->login_user->client_id,
            "receipt_date" => get_today_date(),
            "note" => $this->input->post('receipt_note'),
            "created_by" => $this->login_user->id,
            "status_id" => $this->Order_status_model->get_first_status(),
            "tax_id" => get_setting('receipt_tax_id') ? get_setting('receipt_tax_id') : 0,
            "tax_id2" => get_setting('receipt_tax_id2') ? get_setting('receipt_tax_id2') : 0
        );

        $receipt_id = $this->Receipts_model->save($receipt_data);
        if ($receipt_id) {
            //save items to this receipt
            foreach ($receipt_items as $receipt_item) {
                $receipt_item_data = array("receipt_id" => $receipt_id);
                $this->Receipt_items_model->save($receipt_item_data, $receipt_item->id);
            }
            
            $redirect_to = get_uri("receipts/view/$receipt_id");
            if ($this->login_user->user_type == "client") {
                $redirect_to = get_uri("receipts/preview/$receipt_id");
            }

            //send notification
            log_notification("new_receipt_received", array("receipt_id" => $receipt_id));

            echo json_encode(array("success" => true, "redirect_to" => $redirect_to, 'message' => lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

    /* list of Receipts, prepared for datatable  */

    function list_data()
    {

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("receipts", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array(
            "status_id" => $this->input->post("status_id"),
            "receipt_date" => $this->input->post("start_date"),
            "deadline" => $this->input->post("end_date"),
            "custom_fields" => $custom_fields
        );

        $query = $this->Receipts_model->get_details($options, $this->getRolePermission['filters']);
        $list_data = $query?$query->result():[];

        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $custom_fields);
        }

        echo json_encode(array("data" => $result));
    }

    /* prepare a row of receipt list table */

    private function _make_row($data, $custom_fields)
    {
        //arr( $data );

        $receipt_url = "";

        ///echo get_receipt_id( $data->id );
        $receipt_url = anchor(get_uri("receipts/view/" . $data->id), $data->doc_no);

        

        if(!empty($data->su_comname)){
            $client = anchor(get_uri("stock/supplier_view/" . $data->supplier_id), $data->su_comname);
        }else{
            $client = anchor(get_uri("clients/view/" . $data->client_id), $data->company_name);
        }
        
        // var_dump($data);
        $row_data = array(
            $receipt_url,
            $client,
            $data->receipt_date,
            format_to_date($data->receipt_date, false),
            to_currency($data->receipt_value, $data->currency_symbol)
        );

        if ($this->login_user->user_type == "staff") {
            $row_data[] = js_anchor($data->receipt_status_title, array("style" => "background-color: $data->receipt_status_color", "class" => "label", "data-id" => $data->id, "data-value" => $data->status_id, "data-act" => "update-receipt-status"));
        } else {
            $row_data[] = "<span style='background-color: $data->receipt_status_color;' class='label'>$data->receipt_status_title</span>";
        }

        foreach ($custom_fields as $field) {
            $cf_id = "cfv_" . $field->id;
            $row_data[] = $this->load->view("custom_fields/output_" . $field->field_type, array("value" => $data->$cf_id), true);
        }

        $row_data[] = modal_anchor(get_uri("receipts/modal_form"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_receipt'), "data-post-id" => $data->id)) . js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete_receipt'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("receipts/delete"), "data-action" => "delete"));

        // $row_data[] = 'dsfdsfdsdsdsdsdfdfdsf'; 








        return $row_data;
    }

    //load the yearly view of receipt list
    function yearly()
    {
        $this->load->view("receipts/yearly_Receipts");
    }

    /* load new receipt modal */

    function modal_form()
    {



        $request = $this->input->post();

        if (empty($request['id'])) {

            if (empty($this->getRolePermission['add_row'])) {



                echo permissionBlock();

                return;
            }
        } else {

            if (empty($this->getRolePermission['edit_row'])) {

                echo permissionBlock();

                return;
            }
        }






        // $this->access_only_allowed_members();

        validate_submitted_data(array(
            "id" => "numeric",
            "client_id" => "numeric"
        ));

        $client_id = $this->input->post('client_id');
        $id = intval($this->input->post('id'));
        $view_data['model_info'] = $this->Receipts_model->get_one($id);

        //make the drodown lists
        $view_data['taxes_dropdown'] = array("" => "-") + $this->Taxes_model->get_dropdown_list(array("title"));
        $view_data['clients_dropdown'] = $this->_get_clients_dropdown();

        $view_data['receipt_statuses'] = $this->Order_status_model->get_details()->result();

        $view_data['client_id'] = $client_id;

        $view_data["custom_fields"] = $this->Custom_fields_model->get_combined_details("receipts", $view_data['model_info']->id, $this->login_user->is_admin, $this->login_user->user_type)->result();

        $this->load->view('receipts/modal_form', $view_data);
    }

    private function _get_clients_dropdown()
    {
        $clients_dropdown = array("" => "-");
        $clients = $this->Clients_model->get_dropdown_list(array("company_name"), "id", array("is_lead" => 0));
        foreach ($clients as $key => $value) {
            $clients_dropdown[$key] = $value;
        }
        return $clients_dropdown;
    }

    /* add, edit or clone an receipt */

    function save()
    {
        $request = $this->input->post();

        if (empty($request['po_item_title'])) {
            $this->access_only_allowed_members();

            validate_submitted_data(array(
                "id" => "numeric",
                "receipt_client_id" => "required|numeric",
                "receipt_date" => "required",
                "status_id" => "required"
            ));

            $client_id = $this->input->post('receipt_client_id');
            $id = $this->input->post('id');

            $receipt_data = array(
                "client_id" => $client_id,
                "receipt_date" => $this->input->post('receipt_date'),
                "tax_id" => $this->input->post('tax_id') ? $this->input->post('tax_id') : 0,
                "tax_id2" => $this->input->post('tax_id2') ? $this->input->post('tax_id2') : 0,
                "note" => $this->input->post('receipt_note'),
                "status_id" => $this->input->post('status_id')
            );


            $receipt_info = $this->Receipts_model->get_one($id);


            if ($receipt_info->status_id !== $this->input->post('status_id')) {
                log_notification("receipt_status_updated", array("receipt_id" => $id));
            }

            $receipt_id = $this->Receipts_model->save($receipt_data, $id);

            if ($receipt_id) {
                save_custom_fields("Receipts", $receipt_id, $this->login_user->is_admin, $this->login_user->user_type);

                echo json_encode(array("success" => true, "data" => $this->_row_data($receipt_id), 'id' => $receipt_id, 'message' => lang('record_saved')));
            } else {
                echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
            }
            exit;
        } else {





            //po_item_title

            //arr($request);

            $created_by = $_SESSION['user_id'];

            $ex = explode('-', $request['po_item_title']);
            // var_dump($ex);exit;
            $pr_id = $ex[0];
            $supplier_id = $ex[1];
            $receipt_id = $request['id'];

            ///$this->input->post('id');


            $config['Receipts'] = array('prefix' => 'RI');


            $param = $config['Receipts'];
            $param['LPAD'] = 4;
            $param['column'] = 'doc_no';
            $param['table'] = 'Receipts';
            $param['id'] = $receipt_id;
            $data['doc_no'] = $this->dao->genDocNo($param);


            $sql = "
            
                REPLACE INTO Receipts ( 
                    doc_no, 
                    po_id,
                    id, 
                    supplier_id,
                    client_id, 
                    
                    receipt_date, 
                    note, status_id, tax_id, tax_id2,tax_ref, discount_amount, discount_amount_type, discount_type, created_by 
                    
                ) 
                    
                SELECT 
                    '" . $data['doc_no'] . "' as doc_no,
                    '" . $request['po_item_title'] . "' as po_id,
                    
                    " . json_encode($receipt_id) . " as id, 
                    " . $supplier_id  . " as supplier_id,
                    3 as client_id, 
                    DATE_FORMAT( NOW(), '%Y-%m-%d' ) as receipt_date, 
                    note, status_id, tax_id, tax_id2,
                    '" . $request['tax_ref'] . "' as tax_ref, 
                    
                    discount_amount, discount_amount_type, discount_type, 
                    
                    " . $created_by . " as created_by 
                
                FROM purchaserequests pr 
                
                WHERE id = " . $pr_id . "
                    

            ";


            $aa = $this->dao->execDatas($sql);
            // var_dump($aa);exit;

            $receipt_id = $this->db->insert_id();


            $this->Crud_model->insertLabels($receipt_id, array(), $this->getRolePermission['table_name']);

            //exit;
            //arr( $sql );


            if ($receipt_id) {

                $sql = "
                    DELETE FROM `receipt_items` WHERE `receipt_id` = " . $receipt_id . "
                ";

                $this->dao->execDatas($sql);
                $sql = "
                    INSERT INTO receipt_items ( 
                        quantity, 
                        rate, 
                        total, 
                        receipt_id,
                        lock_dt_id,
                        lock_parent_id,
                        code, 
                        title, 
                        description, 
                        item_type, 
                        unit_type, 
                        currency, 
                        currency_symbol,  
                        created_by, 
                        item_id, 
                        material_id, 
                        sort, 
                        po_id  
                    ) 	
                    SELECT 
                        pr.quantity - IFNULL( new_tb.receive_qty, 0 ) as quantity,
                        pr.rate, 
                        pr.total - IFNULL( new_tb.total_amt, 0 ) as total, 
                        " . $receipt_id . " as receipt_id,
                        pr.id as lock_dt_id,
                        pr.pr_id lock_parent_id,
                        pr.code, 
                        pr.title, 
                        pr.description, 
                        pr.item_type,
                        pr.unit_type, 
                        pr.currency, pr.currency_symbol,
                        " . $created_by . " as created_by,
                        pr.item_id, pr.material_id, pr.sort, 
                        '" . $request['po_item_title'] . "' as po_id 
                    FROM pr_items pr	
                    LEFT JOIN (
                        SELECT 
                            ot.lock_dt_id,
                            SUM( ot.quantity ) as receive_qty,
                            SUM( ot.total ) as total_amt
                        FROM receipt_items ot
                        INNER JOIN Receipts o ON ot.receipt_id = o.id
                        WHERE ot.deleted = 0
                        AND o.deleted = 0
                        GROUP BY 
                            ot.lock_dt_id
                    ) as new_tb ON pr.id = new_tb.lock_dt_id
                    WHERE CONCAT( pr.pr_id, '-', pr.supplier_id ) = '" . $request['po_item_title'] . "'
                    HAVING quantity > 0

        
                ";

                // arr( $sql );


                $this->dao->execDatas($sql);


                save_custom_fields("receipts", $receipt_id, $this->login_user->is_admin, $this->login_user->user_type);

                echo json_encode(array("success" => true, "data" => $this->_row_data($receipt_id), 'id' => $receipt_id, 'message' => lang('record_saved')));
            } else {
                echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
            }
        }
    }

    /* delete or undo an receipt */

    function delete()
    {
        $this->access_only_allowed_members();

        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->input->post('id');
        if ($this->input->post('undo')) {
            if ($this->Receipts_model->delete($id, true)) {
                echo json_encode(array("success" => true, "data" => $this->_row_data($id), "message" => lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, lang('error_occurred')));
            }
        } else {
            if ($this->Receipts_model->delete($id)) {
                echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
            }
        }
    }

    /* load receipt details view */

    function view($receipt_id = 0)
    {
        


        $this->access_only_allowed_members();

        if ($receipt_id) {
            
            $view_data = get_receipt_making_data($receipt_id);
            //var_dump($view_data); exit;
            if (isset($view_data['receipt_info']->po_id)) {
                $po_id = explode("-", $view_data['receipt_info']->po_id);

                $sql = "SELECT pri.po_no, pri.supplier_id
                                FROM pr_items pri
                                
                                WHERE pri.pr_id = " . $po_id[0] . " AND pri.supplier_id=" . $po_id[1] . "";
                // arr($sql);
                $result = $this->db->query($sql)->row();
            }




            //var_dump($view_data); exit;
            if ($view_data) {
                //var_dump($view_data); exit;
                $view_data["po_ref"] = isset($result) ? $result : '-';
                //arr( $view_data);
                // var_dump($view_data["po_ref"]); exit;
                //exit;
                $access_info = $this->get_access_info("invoice");
                $view_data["show_invoice_option"] = (get_setting("module_invoice") && $access_info->access_type == "all") ? true : false;

                $access_info = $this->get_access_info("estimate");
                $view_data["show_estimate_option"] = (get_setting("module_estimate") && $access_info->access_type == "all") ? true : false;

                $view_data["receipt_id"] = $receipt_id;

                $view_data['receipt_statuses'] = $this->Order_status_model->get_details()->result();


                $param['id'] = $receipt_id;
                $param['tbName'] = $_SESSION['table_name'];
                //	exit;


                $view_data["proveButton"] = $this->dao->getProveButton($param);

                $this->template->rander("receipts/view", $view_data);
            } else {
                show_404();
            }
        }
    }

    private function check_access_to_this_receipt($receipt_data)
    {
        //check for valid receipt
        if (!$receipt_data) {
            show_404();
        }

        //check for security
        $receipt_info = get_array_value($receipt_data, "receipt_info");
        if ($this->login_user->user_type == "client") {
            if ($this->login_user->client_id != $receipt_info->client_id) {
                redirect("forbidden");
            }
        }
    }

    function download_pdf($receipt_id = 0, $mode = "download")
    {
        if ($receipt_id) {
            $receipt_data = get_receipt_making_data($receipt_id);
            $this->check_access_to_store();
            $this->check_access_to_this_receipt($receipt_data);

            if (@ob_get_length())
                @ob_clean();
            //so, we have a valid receipt data. Prepare the view.

            prepare_order_pdf($receipt_data, $mode);
        } else {
            show_404();
        }
    }

    //view html is accessable to client only.
    function preview($receipt_id = 0, $show_close_preview = false)
    {
        $this->check_access_to_store();

        if ($receipt_id) {
            $receipt_data = get_receipt_making_data($receipt_id);
            $this->check_access_to_this_receipt($receipt_data);

            $receipt_data['receipt_info'] = get_array_value($receipt_data, "receipt_info");

            $view_data['receipt_preview'] = prepare_receipt_pdf($receipt_data, "html");

            //show a back button
            $view_data['show_close_preview'] = $show_close_preview && $this->login_user->user_type === "staff" ? true : false;

            $view_data['receipt_id'] = $receipt_id;

            $this->template->rander("Receipts/receipt_preview", $view_data);
        } else {
            show_404();
        }
    }
    /* prepare suggestion of po item */

    function get_po_item_suggestion()
    {

        $key = isset($_REQUEST["q"]) ? $_REQUEST["q"] : NULL;
        $suggestion = array();

        $items = $this->Pr_items_model->getPo_Item_suggestion($key);


        foreach ($items as $item) {
            // var_dump($item);
            $suggestion[] = array("id" => $item->tname, "text" => $item->tname);
        }


        //  $suggestion[] = array("id" => "+", "text" => "+ " . lang("create_new_item"));

        echo json_encode($suggestion);
    }

    /* prepare suggestion of receipt item */

    function get_receipt_item_suggestion()
    {

        $key = isset($_REQUEST["q"]) ? $_REQUEST["q"] : NULL;
        $suggestion = array();

        $items = $this->Invoice_items_model->get_item_suggestion($key);

        foreach ($items as $item) {
            $suggestion[] = array("id" => $item->title, "text" => $item->title);
        }

        $suggestion[] = array("id" => "+", "text" => "+ " . lang("create_new_item"));

        echo json_encode($suggestion);
    }

    function get_receipt_item_info_suggestion()
    {
        $item = $this->Invoice_items_model->get_item_info_suggestion($this->input->post("item_name"));
        if ($item) {
            echo json_encode(array("success" => true, "item_info" => $item));
        } else {
            echo json_encode(array("success" => false));
        }
    }

    function save_receipt_status($id = 0)
    {
        $this->access_only_allowed_members();
        if (!$id) {
            show_404();
        }

        $data = array(
            "status_id" => $this->input->post('value')
        );

        $save_id = $this->Receipts_model->save($data, $id);

        if ($save_id) {
            log_notification("receipt_status_updated", array("receipt_id" => $id));
            $receipt_info = $this->Receipts_model->get_details(array("id" => $id))->row();
            echo json_encode(array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, "message" => lang('record_saved'), "receipt_status_color" => $receipt_info->receipt_status_color));
        } else {
            echo json_encode(array("success" => false, lang('error_occurred')));
        }
    }

    /* return a row of receipt list table */

    private function _row_data($id)
    {
        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("receipts", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array("id" => $id, "custom_fields" => $custom_fields);
        $data = $this->Receipts_model->get_details($options)->row();
        return $this->_make_row($data, $custom_fields);
    }

    /* load discount modal */

    function discount_modal_form()
    {
        $this->access_only_allowed_members();

        validate_submitted_data(array(
            "receipt_id" => "required|numeric"
        ));

        $receipt_id = $this->input->post('receipt_id');

        $view_data['model_info'] = $this->Receipts_model->get_one($receipt_id);

        $this->load->view('receipts/discount_modal_form', $view_data);
    }

    /* save discount */

    function save_discount()
    {
        $this->access_only_allowed_members();

        validate_submitted_data(array(
            "receipt_id" => "required|numeric",
            "discount_type" => "required",
            "discount_amount" => "numeric",
            "discount_amount_type" => "required"
        ));

        $receipt_id = $this->input->post('receipt_id');

        $data = array(
            "discount_type" => $this->input->post('discount_type'),
            "discount_amount" => $this->input->post('discount_amount'),
            "discount_amount_type" => $this->input->post('discount_amount_type')
        );

        $data = clean_data($data);

        $save_data = $this->Receipts_model->save($data, $receipt_id);
        if ($save_data) {
            echo json_encode(array("success" => true, "receipt_total_view" => $this->_get_receipt_total_view($receipt_id), 'message' => lang('record_saved'), "receipt_id" => $receipt_id));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

    /* list of receipt items, prepared for datatable  */

    function item_list_data($receipt_id = 0)
    {
        
        $this->access_only_allowed_members();

        $list_data = $this->Receipt_items_model->get_details(array("receipt_id" => $receipt_id))->result();
        //var_dump($list_data);exit;
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_item_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    /* list of receipt of a specific client, prepared for datatable  */

    function receipt_list_data_of_client($client_id)
    {
        $this->check_access_to_store();

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("receipts", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array("client_id" => $client_id, "custom_fields" => $custom_fields);

        $list_data = $this->Receipts_model->get_details($options)->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $custom_fields);
        }
        echo json_encode(array("data" => $result));
    }
}

/* End of file Receipts.php */
/* Location: ./application/controllers/Receipts.php */