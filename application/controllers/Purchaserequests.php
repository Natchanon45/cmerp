<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class PurchaseRequests extends MY_Controller {

    function __construct() {
		
		
        parent::__construct();
	/*	
	$param['LPAD'] = 4;
	$param['column'] = 'doc_no';
	$param['table'] = 'purchaserequests';
	$param['prefix'] = 'PR';
	echo $this->dao->genDocNo( $param  );
	exit;	
	
	*/
        $this->init_permission_checker("order");
		
		$this->className = 'purchaserequests';
        $this->load->model('Provetable_model');
        $this->load->model('Db_model');
        $this->load->helper('notifications');
        $this->load->model('Pr_categories_model');
        $this->load->model('Permission_m');
        $this->load->model('Purchaserequest_m');
    }

    function approve($mrid){
        $data = $this->Purchaserequest_m->updateStatus($mrid, 3);

        if($data["process"] == "fail"){
            $_SESSION['error'] = $data["message"];
        }

        redirect($_SERVER['HTTP_REFERER']);

    }

    function disapprove($mrid){
        $data = $this->Purchaserequest_m->updateStatus($mrid, 4);

        if($data["process"] == "fail"){
            $_SESSION['error'] = $data["message"];
        }

        redirect($_SERVER['HTTP_REFERER']);
    }


    function process_pr($pr_id=0) {
        if($this->Permission_m->create_purchase_request != true) redirect("forbidden");
        
        $view_data = get_pr_making_data();
        $conditions = array("created_by" => $this->login_user->id, "pr_id" => $pr_id, "deleted" => 0);
        $view_data["cart_items_count"] = $this->Pr_items_model->get_all_where($conditions)->num_rows();

        $view_data['clients_dropdown'] = "";
        $view_data['pr_id'] = $pr_id;
        if ($this->login_user->user_type == "staff") {
            $view_data['clients_dropdown'] = $this->_get_buyers_dropdown();
        }
        
        $this->template->rander("purchaserequests/process_pr", $view_data);

    }

    function item_list_data_of_login_user($pr_id=0) {
        // temporary $this->check_access_to_store();
        $options = array("pr_id"=>$pr_id, "created_by" => $this->login_user->id, 'item_type'=>'all');
        if(!$pr_id) {
            $options['processing'] = true;
        }
        $pr_info = $pr_id?$this->Purchaserequests_model->get_details(array("id" => $pr_id))->row():null;
        $prove = $this->Provetable_model->getProve($pr_id, 'purchaserequests')->row();
        $list_data = $this->Pr_items_model->get_details($options)->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_item_row($pr_info, $prove, $data);
        }

        echo json_encode(array("data" => $result));
    }

    /* prepare a row of order item list table */

    private function _make_item_row($pr, $prove, $data) {
        //$item = "<div class='item-row strong mb5' data-id='$data->id'><i class='fa fa-bars pull-left move-icon'></i> $data->title</div>";
        $item = "<div class='item-row strong mb5' data-id='$data->id'>".$data->code.($this->cop('prove_row')?":".$data->title:"")."</div>";
        if ($data->description) {
            $item .= "<span>" . nl2br($data->description) . "</span>";
        }
        $type = $data->unit_type ? $data->unit_type : "";

        $edit_row = false;
        $delete_row = false;

        if($pr->status_id == 1){
            if($this->Permission_m->update_purchase_request == true){
                $edit_row = true;
            }

            if($this->Permission_m->delete_purchase_request == true){
                $delete_row = true;
            }
        }

        return array(
            $data->sort,
            $item,
            $data->project_name,
            $data->supplier_name,
            to_decimal_format($data->quantity) . " " . $type,
            to_currency($data->rate, $data->currency_symbol, 4),
            to_currency($data->total, $data->currency_symbol, 4),
            ($edit_row?modal_anchor(get_uri("purchaserequests/item_modal_form"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_item'), "data-post-id" => $data->id, "data-post-pr_id" => $data->pr_id, "data-post-item_type" => $data->item_type, "data-post-item_id" => (($data->item_type!='itm')?0:$data->item_id), "data-post-material_id" => (($data->item_type!='mtr')?0:$data->material_id))):'')
            .($delete_row?js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("purchaserequests/delete_item"), "data-action" => "delete")):'')
        );
    }

    /* load item modal */

    function item_modal_form() {
        // temporary $this->check_access_to_store();
        validate_submitted_data(array(
            "id" => "numeric"
        ));
        $post = $this->input->post();
        $model_info = $this->Pr_items_model->get_one(@$post['id']);
        $this->check_access_to_this_pr_item($model_info);

        $view_data['model_info'] = $model_info;
        $view_data['code'] = $model_info->code;
        $view_data['pr_id'] = @$post['id']?$model_info->pr_id:@$post['pr_id'];
        $view_data['item_id'] = $model_info->item_id;
        $view_data['material_id'] = intval($model_info->material_id);
        $view_data['item_type'] = @$post['id']?$model_info->item_type:@$post['item_type'];
        //$view_data['supplier_id'] = $model_info->supplier_id;
        $view_data['currency'] = @$post['id']?$model_info->currency:'THB';
        $view_data['currency'] = $view_data['currency']?$view_data['currency']:'THB';
        $view_data['currency_symbol'] = @$post['id']?$model_info->currency_symbol:'฿';
        $view_data['currency_symbol'] = $view_data['currency_symbol']?$view_data['currency_symbol']:'฿';
        $view_data['supplier_name'] = $model_info->supplier_name;
        $view_data['address'] = $model_info->address;
        $view_data['city'] = $model_info->city;
        $view_data['state'] = $model_info->state;
        $view_data['zip'] = $model_info->zip;
        $view_data['country'] = $model_info->country;
        $view_data['website'] = $model_info->website;
        $view_data['phone'] = $model_info->phone;
        $view_data['vat_number'] = $model_info->vat_number;
        $view_data['suppliers'] = [];
        $view_data['currencies'] = [];

        //$this->template->rander("purchaserequests/item_modal_form", $view_data);
        $this->load->view('purchaserequests/item_modal_form', $view_data);
    }

    /* add or edit an order item */

    function save_item() {
        // temporary $this->check_access_to_store();
        validate_submitted_data(array(
            "id" => "numeric"
        ));
        
        $id = $this->input->post('id', 0);

        if(!$id && !$this->cp('purchaserequests','add_row')) {
            redirect("forbidden");
            return;
        }
        if($id && !$this->cp('purchaserequests','edit_row')) {
            redirect("forbidden");
            return;
        }

        $item_type = $this->input->post('item_type');
        $item_info = null;
        if($item_type) {
            $item_info = $this->Pr_items_model->get_one($id);
        }
        //$this->check_access_to_this_pr_item($item_info);

        $quantity = unformat_currency($this->input->post('pr_item_quantity'));

        $pr_item_data = array(
            "description" => $this->input->post('pr_item_description'),
            "quantity" => $quantity
        );

        $supplier_id = $this->input->post('supplier_id', 0);
        //check if the add_new_item flag is on, if so, add the item to libary. 
        $add_new_supplier_to_library = $this->input->post('add_new_supplier_to_library');
        if ($add_new_supplier_to_library) {
            $library_supplier_data = array(
                "company_name" => $this->input->post('supplier_name'),
                "owner_id" => $this->login_user->id,
                "created_by " => $this->login_user->id,
                "address" => $this->input->post('address'),
                "city"=>$this->input->post('city'),
                "state"=>$this->input->post('state'),
                "zip"=>$this->input->post('zip'),
                "country"=>$this->input->post('country'),
                "website"=>$this->input->post('website'),
                "phone"=>$this->input->post('phone'),
                "vat_number"=>$this->input->post('vat_number'),
                "currency"=>$this->input->post('currency'),
                "currency_symbol"=>$this->input->post('currency_symbol'),
                "created_date"=>date('Y-m-d H:i:s')
            );
            $supplier_id = $this->Bom_suppliers_model->save($library_supplier_data);
        }

        $pr_id = $this->input->post("pr_id");
        if ($pr_id) {
            $rate = unformat_currency($this->input->post('pr_item_rate'));
            $pr_item_data["pr_id"] = $pr_id;
            $pr_item_data["code"] = $this->input->post('code');
            $pr_item_data["title"] = ($item_type=='itm')?$item_info->title:$this->input->post('pr_item_title');
            $pr_item_data["unit_type"] = $this->input->post('pr_unit_type');
            $pr_item_data["rate"] = unformat_currency($this->input->post('pr_item_rate'));
            $pr_item_data["total"] = $rate * $quantity;
            $pr_item_data["item_type"] = $item_type;
            $pr_item_data["item_id"] = $id&&$item_info?$item_info->item_id:$this->input->post('item_id', 0);
            $pr_item_data["material_id"] = $this->input->post('material_id', 0);
            $pr_item_data["supplier_id"] = $supplier_id;
            $pr_item_data["supplier_name"] = $this->input->post('supplier_name', '');
            $pr_item_data["currency"] = $this->input->post('currency', 'THB');
            $pr_item_data["currency_symbol"] = $this->input->post('currency_symbol', '฿');
        } else {
            $rate = unformat_currency($this->input->post('pr_item_rate'));
            $pr_item_data["title"] = ($item_type=='itm')?$item_info->title:$this->input->post('pr_item_title');
            $pr_item_data["code"] = $this->input->post('code');
            $pr_item_data["unit_type"] = $this->input->post('pr_unit_type');
            $pr_item_data["rate"] = unformat_currency($this->input->post('pr_item_rate'));
            $pr_item_data["item_type"] = $item_type;
            $pr_item_data["item_id"] = $id?$item_info->item_id:$this->input->post('item_id', 0);
            $pr_item_data["material_id"] = $this->input->post('material_id', 0);
            $pr_item_data["supplier_id"] = $supplier_id;
            $pr_item_data["supplier_name"] = $this->input->post('supplier_name', '');
            $pr_item_data["currency"] = $this->input->post('currency', 'THB');
            $pr_item_data["currency_symbol"] = $this->input->post('currency_symbol', '฿');
            $pr_item_data["total"] = $rate * $quantity;
        }
        $pr_item_data["address"] = $this->input->post('address', '');
        $pr_item_data["city"] = $this->input->post('city', '');
        $pr_item_data["state"] = $this->input->post('state', '');
        $pr_item_data["zip"] = $this->input->post('zip', '');
        $pr_item_data["country"] = $this->input->post('country', '');
        $pr_item_data["website"] = $this->input->post('website', '');
        $pr_item_data["phone"] = $this->input->post('phone', '');
        $pr_item_data["vat_number"] = $this->input->post('vat_number', '');
        $pr_item_id = $this->Pr_items_model->save($pr_item_data, $id);
        if ($pr_item_id) {
            $options = array("id" => $pr_item_id, 'item_type'=>'all');
            $pr_info = $pr_id?$this->Purchaserequests_model->get_details(array("id" => $pr_id))->row():null;
            $prove = $this->Provetable_model->getProve($pr_id, 'purchaserequests')->row();
            $item_info = $this->Pr_items_model->get_details($options)->row();
            //redirect('/purchaserequests/process_pr/'.$item_info->pr_id);

            $this->dao->runPoNo($item_info->pr_id);

            echo json_encode(array("success" => true, "pr_id" => $item_info->pr_id, "data" => $this->_make_item_row($pr_info, $prove, $item_info), "pr_total_view" => $this->_get_pr_total_view($item_info->pr_id), 'id' => $pr_item_id, 'message' => lang('record_saved')));
        } else {
            //redirect('/purchaserequests/process_pr');
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

    //update the sort value for order item
    function update_item_sort_values($id = 0) {
        // temporary $this->check_access_to_store();
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
                $this->Pr_items_model->save($data, $id);
            }
        }
    }

    /* delete or undo an order item */

    function delete_item() {
        if(!$this->cop('delete_row')) {
            redirect("forbidden");
        }
       // temporary  $this->check_access_to_store();
        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->input->post('id');
        $pr_item_info = $this->Pr_items_model->get_one($id);
        $pr_id = $pr_item_info->pr_id;
        //$this->check_access_to_this_pr_item($pr_item_info);

        if ($this->input->post('undo')) {
            if ($this->Pr_items_model->delete($id, true)) {
                $options = array("id" => $id);
                $item_info = $this->Pr_items_model->get_details($options)->row();
                $pr_info = $item_info->pr_id?$this->Purchaserequests_model->get_details(array("id" => $pr_id))->row():null;
                $prove = $this->Provetable_model->getProve($pr_id, 'purchaserequests')->row();
                echo json_encode(array("success" => true, "pr_id" => $item_info->pr_id, "data" => $this->_make_item_row($pr_info, $prove, $item_info), "pr_total_view" => $this->_get_pr_total_view($item_info->pr_id), "message" => lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, lang('error_occurred')));
            }
        } else {
            if ($this->Pr_items_model->delete($id)) {
                $item_info = $this->Pr_items_model->get_one($id);
                echo json_encode(array("success" => true, "pr_id" => $item_info->pr_id, "pr_total_view" => $this->_get_pr_total_view($item_info->pr_id), 'message' => lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
            }
        }
    }

    /* order total section */

    private function _get_pr_total_view($pr_id = 0) {
        if ($pr_id) {
            $view_data["pr_total_summary"] = $this->Purchaserequests_model->get_pr_total_summary($pr_id);
            $view_data["pr_id"] = $pr_id;
            $view_data["edit_row"] = false;//$this->cp('purchaserequests', 'edit_row');
            return $this->load->view('purchaserequests/pr_total_section', $view_data, true);
        } else {
            $view_data = get_pr_making_data();
            $view_data["edit_row"] = false;//$this->cp('purchaserequests', 'edit_row');
            return $this->load->view('purchaserequests/processing_pr_total_section', $view_data, true);
        }
    }

    function place_order() {
        // temporary $this->check_access_to_store();



        $pr_items = $this->Pr_items_model->get_all_where(array("created_by" => $this->login_user->id, "pr_id" => 0, "deleted" => 0))->result();
        if (!$pr_items) {
            echo json_encode(array("success" => false, "redirect_to" => get_uri("purchaserequests/process_pr"), 'message' => lang('at_least_one_item')));
            //echo json_encode(array("success" => false, 'message' => lang('at_least_one_item')));
            return;
            //show_404();
        }

        /* status_id
        1 = New = #f1c40f
        2 = Request Approval = #29c2c2
        3 = Approved = #83c340
        4 = Rejected = red
        */


        $pr_data = array(
            //"buyer_id" => $this->input->post("buyer_id") ? $this->input->post("buyer_id") : $this->login_user->client_id,
            "buyer_id" => $this->input->post("buyer_id") ? $this->input->post("buyer_id") : $this->login_user->id,
            "pr_date" => get_today_date(),
            "note" => $this->input->post('pr_note'),
            "created_by" => $this->login_user->id,
            "status_id" => 1,
            "tax_id" => get_setting('pr_tax_id') ? get_setting('pr_tax_id') : 0,
            "tax_id2" => get_setting('pr_tax_id2') ? get_setting('pr_tax_id2') : 0
        );

        $pr_id = $this->Purchaserequests_model->save($pr_data);

        if ($pr_id) {
            //save items to this order
            $this->load->model('Bom_material_pricings_model');
            foreach ($pr_items as $pr_item) {
                $pr_item_data = array("pr_id" => $pr_id);
                $this->Pr_items_model->save($pr_item_data, $pr_item->id);

                if($pr_item->supplier_id && $pr_item->material_id) {
                    $data = [
                        'ratio'=>$pr_item->quantity,
                        'price'=>$pr_item->total
                    ];
                    $where = [
                        'material_id'=>$pr_item->material_id,
                        'supplier_id'=>$pr_item->supplier_id
                    ];
                    $this->Bom_material_pricings_model->update_where($data, $where);
                }
            }

            $redirect_to = get_uri("purchaserequests/view/$pr_id");
            if ($this->login_user->user_type == "client") {
                $redirect_to = get_uri("purchaserequests/preview/$pr_id");
            }

            //send notification
            log_notification("new_pr_received", array("pr_id" => $pr_id));

            echo json_encode(array("success" => true, "redirect_to" => $redirect_to, 'message' => lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

    function add_pr_material_to_cart() {

        if(!$this->cop('view_row')|| !($this->cop('add_row') || $this->cop('edit_row'))) {
            redirect("forbidden");
        }
        $data = $this->input->post();
        //var_dump($data);exit;
        if(!isset($data['materials']))
            $data['materials'] = array();
        //$this->dump($data['materials'], true);
        //$this->dump($data['materials']);
        //echo "<br />------------<br />";
        foreach($data['materials'] as $material) {
            //$row = $this->Pr_items_model->get_one_material_in_cart($material['id'], intval(@$material['supplier_id']), $this->login_user->id);
            $row = $this->Pr_items_model->get_one_material_in_cart($material['id'], $this->login_user->id);
            $material_row = $this->Pr_items_model->get_one_material($material['id']);
            if(!$row||($row['deleted']=='1')) {
                if(!$row) {
                    $row = [];
                    $row['id'] = 0;
                }
                $row['code']=(isset($material_row->name)?$material_row->name:'');
                $row['title']=(isset($material_row->production_name)?$material_row->production_name:'');
                $row['description']=(isset($material_row->description)?$material_row->description:'');
                $row['item_type']="mtr";
                $row['quantity']=intval($material['amount']);
                $row['unit_type']=(isset($material_row->unit)?$material_row->unit:'');
                $row['rate']=doubleval(@$material['price']);
                $row['total']=$row['quantity']*$row['rate'];
                $row['pr_id']=0;
                $row['created_by']=$this->login_user->id;
                $row['item_id']=0;
                $row['material_id']=$material['id'];
                $row['supplier_id']=intval(@$material['supplier_id']);
                $row['supplier_name']=@$material['supplier_name'];
                $row['project_id']=intval(@$material['project_id']);
                $row['project_name']=@$material['project_name'];
                $row['sort']=0;
                $row['deleted']=0;
            }else{
                $row['code']=(isset($material_row->name)?$material_row->name:'');
                $row['title']=(isset($material_row->production_name)?$material_row->production_name:'');
                $row['description']=(isset($material_row->description)?$material_row->description:'');
                $row['item_type']="mtr";
                $row['quantity']=intval($material['amount']);
                $row['unit_type']=(isset($material_row->unit)?$material_row->unit:'');
                $row['rate']=doubleval(@$material['price']);
                $row['total']=$row['quantity']*$row['rate'];
                $row['pr_id']=0;
                $row['created_by']=$this->login_user->id;
                $row['item_id']=0;
                $row['material_id']=$material['id'];
                $row['supplier_id']=intval(@$material['supplier_id']);
                $row['supplier_name']=@$material['supplier_name'];
                if(intval(@$material['project_id']))
                    $row['project_id']=intval(@$material['project_id']);
                if(@$material['project_name'])
                    $row['project_name']=@$material['project_name'];
                $row['deleted']=0;
                //$row['quantity'] += intval($material['amount']);
                $row['quantity'] = intval($material['amount']);
                $row['total']=$row['quantity']*$row['rate'];
                $row['supplier_name']=@$material['supplier_name'];
            }
            //$this->dump($row);
            $this->Pr_items_model->save($row, $row['id']);
            
            /*try{
                $this->Pr_items_model->save($row, $row['id']);
            }catch(Exception $e) {
                echo $e->getMessage();
            }
            $this->dump($row);*/
        }
        redirect("purchaserequests/process_pr");
    }
    
    function create_pr_material_form() {
        $options = array('item_type'=>'mtr','processing'=>true);
        $materials = $this->Pr_items_model->get_details($options)->result();
        echo 'Materials in cart<br />';
        $this->dump($materials);
    }

    function yearly() {
        $this->load->view("purchaserequests/yearly_pr");
    }

    /* load new order modal */
    function modal_form() {
		if(!$this->cp('purchaserequests','edit_row')) {
            redirect("forbidden");
        }
		
		$request = $this->input->post();
		
		if( empty( $request['id'] ) ) {
			
			if( empty( $this->getRolePermission['add_row'] ) ) {
				echo permissionBlock();
				return;
			}
		}
		else {
			if( empty( $this->getRolePermission['edit_row'] ) ) {
				
				echo permissionBlock();
				
				return;
				 
			}
		}

        // temporary $this->access_only_allowed_members();

        validate_submitted_data(array(
            "id" => "numeric",
            "buyer_id" => "numeric"
        ));

        $buyer_id = $this->input->post('buyer_id');
        $view_data['model_info'] = $this->Purchaserequests_model->get_one($this->input->post('id'));

        //make the drodown lists
        $view_data['taxes_dropdown'] = array("" => "-") + $this->Taxes_model->get_dropdown_list(array("title"));
        $view_data['buyers_dropdown'] = $this->_get_buyers_dropdown();

        $options = [];
        if(!$this->cp('purchaserequests', 'prove_row')) {
            $options['where'] = " pr_status.id!='3' AND pr_status.id!='4' ";
        }
        $view_data['pr_statuses'] = $this->Pr_status_model->get_details($options)->result();

        $options = [];
        $view_data['categories'] = $this->Pr_categories_model->get_details($options)->result();

        $view_data['buyer_id'] = $buyer_id;

        $is_clone = $this->input->post('is_clone');
        $view_data['is_clone'] = $is_clone;

        $view_data["custom_fields"] = $this->Custom_fields_model->get_combined_details("purchaserequests", $view_data['model_info']->id, $this->login_user->is_admin, $this->login_user->user_type)->result();

        $this->load->view('purchaserequests/modal_form', $view_data);
    }

    private function _get_clients_dropdown() {
        $clients_dropdown = array("" => "-");
        $clients = $this->Clients_model->get_dropdown_list(array("company_name"), "id", array("is_lead" => 0));
        foreach ($clients as $key => $value) {
            $clients_dropdown[$key] = $value;
        }
        return $clients_dropdown;
    }

    private function _get_buyers_dropdown() {
        $buyers_dropdown = array("" => "-");
        $buyers = $this->Users_model->get_dropdown_list(array("first_name", "last_name"), "id", array("`status`" => 'active'));
        foreach ($buyers as $key => $value) {
            $buyers_dropdown[$key] = $value;
        }
        return $buyers_dropdown;
    }

    /*function save_order() {
        $this->access_only_allowed_members();
        $pr_id = $this->input->post('pr_id');
        $order_id = $this->input->post('order_client_id', 0);
        $client_id = $this->input->post('client_id');
        $pr_info = $this->Purchaserequests_model->get_one($pr_id);
        if(!$pr_info) {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
            return;
        }

        $order_data = array(
            "client_id" => $client_id,
            "order_date" => $this->input->post('order_date'),
            "tax_id" => $this->input->post('tax_id') ? $this->input->post('tax_id') : 0,
            "tax_id2" => $this->input->post('tax_id2') ? $this->input->post('tax_id2') : 0,
            "note" => $this->input->post('order_note'),
            "status_id" => $this->input->post('status_id')
        );

        $order_id = $this->Orders_model->save($order_data, $order_id);
        if($order_id) {
            save_custom_fields("orders", $order_id, $this->login_user->is_admin, $this->login_user->user_type);
            $pr_data = array(
                "id" => $pr_id,
                "order_id" => $order_id
            );
            $this->Purchaserequests_model->save($pr_data, $pr_id);

            echo json_encode(array("success" => true, "data" => $this->_row_data($order_id), 'id' => $order_id, 'message' => lang('record_saved')));
        }else{
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }*/
        

    /* add, edit or clone an order */

    function save() {
        // temporary $this->access_only_allowed_members();

        validate_submitted_data(array(
            "id" => "numeric",
            "pr_buyer_id" => "required|numeric",
            "pr_date" => "required",
            "status_id" => "required"
        ));

        $buyer_id = $this->input->post('pr_buyer_id');
        $id = intval($this->input->post('id'));

        if(!$id && !$this->cp('purchaserequests','add_row')) {
            redirect("forbidden");
            return;
        }
        if($id && !$this->cp('purchaserequests','edit_row')) {
            redirect("forbidden");
            return;
        }

        $catid = $this->input->post('catid');

        $expired_date = $this->input->post('pr_date');
        $sql_date ="
                SELECT 
                    DATE_FORMAT( ADDDATE( '".$expired_date."', INTERVAL ".$this->input->post('credit')." day ), '%Y-%m-%d' ) as t
                FROM
                    purchaserequests
                WHERE
                    purchaserequests.id = $id;
            ";
            $credit_date = $this->db->query($sql_date)->row();
                        
             if($credit_date){
                $sql_inert = "
                    UPDATE `purchaserequests` SET `expired` = '".$credit_date->t."' WHERE `purchaserequests`.`id` = $id; 
                ";
                $credit_insert = $this->db->query($sql_inert);
                if($credit_insert){
                    $date = "SELECT purchaserequests.expired FROM purchaserequests WHERE purchaserequests.id = $id";
                    $c_date = $this->db->query($date)->row();
                    
                }                          
            }
        

        $pr_data = array(
            "catid" => $catid,
            "buyer_id" => $buyer_id,
            "project_name" => $this->input->post('project_name'),
            "payment" => $this->input->post('payment'),
            "credit" => $this->input->post('credit'),
            "expired" => $c_date->expired,
            "pr_date" => $this->input->post('pr_date'),
            "tax_id" => $this->input->post('tax_id') ? $this->input->post('tax_id') : 0,
            "tax_id2" => $this->input->post('tax_id2') ? $this->input->post('tax_id2') : 0,
            "note" => $this->input->post('pr_note'),
            "status_id" => $this->input->post('status_id')
        );

        $is_clone = $this->input->post('is_clone');

        //check if the status has been changed,
        //if so, send notification
        $pr_info = $this->Purchaserequests_model->get_one($id);
        if ($pr_info->status_id == '1' && $this->input->post('status_id')=='2') {
            //log_notification("new_purchaserequest_created", array("pr_id" => $id));
            //notification and email
            $this->Notifications_model->create_notification("new_purchaserequest_created", $this->login_user->id, ['pr_id'=>$id]);
            
            //chat message
            $sql = "SELECT users.id FROM users
            WHERE users.role_id IN (
                SELECT role_permission.role_id
                FROM role_permission
                WHERE role_permission.table_name='purchaserequests'
                AND role_permission.prove_row>0
            )";
            $user_ids = $this->db->query($sql)->row_array();
            $user_ids = $user_ids?$user_ids:[];
            foreach($user_ids as $to_user_id) {
                $message_data = array(
                    "from_user_id" => $this->login_user->id,
                    "to_user_id" => $to_user_id,
                    "subject" => lang('new_purchaserequest_created'),
                    "message" => lang('new_purchaserequest_created').' <a href="'.get_uri("purchaserequests/view/" . $id).'">'.lang('pr_no').':'.$id.'</a>',
                    "created_at" => get_current_utc_time(),
                    "deleted_by_users" => "",
                );
                $message_data = clean_data($message_data);
			    $this->Messages_model->save($message_data);
            }
        }

        $main_pr_id = "";
        if ($is_clone && $id) {
            $main_pr_id = $id; //store main pr id to get items later
            $id = ""; //on cloning pr, save as new
        }

        $pr_id = $this->Purchaserequests_model->save($pr_data, $id);
        if ($pr_id) {
            //$prove = $this->Provetable_model->getProve($pr_id, 'purchaserequests', $this->login_user->id)->row();
            $prove = $this->Provetable_model->get_one_where(['doc_id'=>$pr_id,'tbName'=>'purchaserequests']);
            if(!$prove && $pr_data['status_id']=='3') {
                $prove = [];
                $prove['id'] = 0;
                $prove['doc_id'] = $pr_id;
                $prove['tbName'] = 'purchaserequests';
                $prove['user_id'] = $this->login_user->id;
                $prove['doc_date'] = date('Y-m-d H:i:s');
                //$prove['status_id'] = $pr_data['status_id'];
                $this->Provetable_model->save($prove);
            }else{
                //$this->Purchaserequests_model->db->where('id', $prove['id']);
                //$prove['status_id'] = $pr_data['status_id'];
                //$this->Purchaserequests_model->db->update('purchaserequests', $prove);
                //$this->Provetable_model->save($prove, $prove['id']);
            }
            
            if ($is_clone && $main_pr_id) {
                //add estimate items
                save_custom_fields("purchaserequests", $pr_id, 1, "staff"); //we have to keep this regarding as an admin user because non-admin user also can acquire the access to clone a estimate
                $pr_items = $this->Pr_items_model->get_all_where(array("pr_id" => $main_pr_id, "deleted" => 0))->result();

                foreach ($pr_items as $pr_item) {
                    //prepare new estimate item data
                    $pr_item_data = (array) $pr_item;
                    unset($pr_item_data["id"]);
                    $pr_item_data['pr_id'] = $pr_id;

                    $pr_item = $this->Pr_items_model->save($pr_item_data);
                }
            } else {
                save_custom_fields("purchaserequests", $pr_id, $this->login_user->is_admin, $this->login_user->user_type);
            }
            echo json_encode(array("success" => true, "data" => $this->_row_data($pr_id), 'id' => $pr_id, 'message' => lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

    /* delete or undo an order */

    function delete() {
        // temporary $this->access_only_allowed_members();
		if(!$this->cp('purchaserequests', 'delete_row')) {
            redirect("forbidden");
        }

        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->input->post('id');
        if ($this->input->post('undo')) {
            if ($this->Purchaserequests_model->delete($id, true)) {
                echo json_encode(array("success" => true, "data" => $this->_row_data($id), "message" => lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, lang('error_occurred')));
            }
        } else {
            if ($this->Purchaserequests_model->delete($id)) {
                echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
            }
        }
    }

    function updatestatus($pr_id) {
        // if(!$this->cop('prove_row')) {
        //     //redirect("forbidden");
        //     echo json_encode(array("success" => false, 'message' => 'Cannot access this page.'));
        //     return;
        // }

        //$pr_info = $this->Purchaserequests_model->get_one($pr_id);
        $pr_data = ['status_id'=> intval($this->input->post('status_id'))];
        $pr_id = $this->Purchaserequests_model->save($pr_data, $pr_id);
        echo json_encode(array("success" => true, 'message' => 'Update status success.'));
    }

    function clearAllStatuses() {
        $approvals = $this->Provetable_model->getApprovals('purchaserequests')->result();
        foreach($approvals as $apr) {
            $status_id = 1;
            if($apr->status_id=='1')
                $status_id = 3;
            if($apr->status_id=='2')
                $status_id = 4;
            $pr_data['status_id'] = $status_id;
            $pr_id = $this->Purchaserequests_model->save($pr_data, $apr->doc_id);
        }
    }

    /* load pr details view */
    function view($pr_id = 0) {
        // temporary $this->access_only_allowed_members();
        if($this->Permission_m->access_purchase_request != true){
            redirect("forbidden");
            return;
        }

        if ($pr_id) {

            $view_data = get_pr_making_data($pr_id);

            if ($view_data) {
                $access_info = $this->get_access_info("invoice");
                $view_data["show_invoice_option"] = (get_setting("module_invoice") && $access_info->access_type == "all") ? true : false;

                $access_info = $this->get_access_info("estimate");
                $view_data["show_estimate_option"] = (get_setting("module_estimate") && $access_info->access_type == "all") ? true : false;

                $view_data["pr_id"] = $pr_id;

                $is_approved = (!$view_data['pr_info'] || $view_data['pr_info']->pr_status_title=='Approved')?true:false;
                $edit_row = ($is_approved && $this->cop('prove_row')) || (!$is_approved && $this->cop('edit_row'));
                $delete_row = ($is_approved && $this->cop('prove_row')) || (!$is_approved && $this->cop('delete_row'));

                $options = [];
                if(!$this->cp('purchaserequests', 'prove_row')) {
                    $options['where'] = " pr_status.id!='3' AND pr_status.id!='4' ";
                }
                $view_data['pr_statuses'] = $this->Pr_status_model->get_details($options)->result();

                $view_data['view_row'] = $this->cop('view_row');
                $view_data['add_row'] = $this->cop('add_row');
                $view_data['edit_row'] = $edit_row;
                $view_data['delete_row'] = $delete_row;
                $view_data['prove_row'] = $this->cop('prove_row');
                $view_data['is_approved'] = $is_approved;

                /* for test only */
                /*$view_data['view_row'] = false;
                $view_data['add_row'] = false;
                $view_data['edit_row'] = false;
                $view_data['delete_row'] = false;
                $view_data['prove_row'] = false;*/

                $param['id'] = $pr_id;
				$param['tbName'] = $this->className;
				$view_data["proveButton"] = $this->dao->getProveButton( $param );

                $this->template->rander("purchaserequests/view", $view_data);
            } else {
                show_404();
            }
        }
    }

    private function check_access_to_this_pr($pr_data) {
        //check for valid order
        if (!$pr_data) {
            show_404();
        }

        //check for security
        $pr_info = get_array_value($pr_data, "pr_info");
        if ($this->login_user->user_type == "client") {
            if ($this->login_user->buyer_id != $pr_info->buyer_id) {
                redirect("forbidden");
            }
        }
    }

    function download_pdf( $pr_id = 0, $mode = "download" ) {
		if(!$this->cp('purchaserequests','view_row')) {
            redirect("forbidden");
            return;
        }
        if ( $pr_id ) {
            $pr_data = get_pr_making_data( $pr_id );
			
			//arr( $pr_data);
			
			//exit;
            $this->check_access_to_store();
            $this->check_access_to_this_pr($pr_data);

            if (@ob_get_length())
                @ob_clean();
            //so, we have a valid order data. Prepare the view.
            $pr_data["usgn"] = $this->Db_model->signature_approve( $pr_id, "purchaserequests" );
            prepare_pr_pdf( $pr_data, $mode );
        } else {
            show_404();
        }
    }

    //view html is accessable to client only.
    function preview( $pr_id = 0, $show_close_preview = false ) {
        // temporary $this->check_access_to_store();
        if(!$this->cp('purchaserequests','view_row')) {
            redirect("forbidden");
            return;
        }
        if ($pr_id) {
            $pr_data = get_pr_making_data($pr_id);
            $this->check_access_to_this_pr($pr_data);

            $pr_data['pr_info'] = get_array_value($pr_data, "pr_info");

            $pr_data["usgn"] = $this->Db_model->signature_approve( $pr_id, "purchaserequests" );

            $view_data['pr_preview'] = prepare_pr_pdf($pr_data, "html");

            //show a back button
            $view_data['show_close_preview'] = $show_close_preview && $this->login_user->user_type === "staff" ? true : false;

            $view_data['pr_id'] = $pr_id;

            $this->template->rander("purchaserequests/pr_preview", $view_data);
        } else {
            show_404();
        }
    }


    function download_po_pdf( $pr_id = 0, $mode = "download" ) {
		if(!$this->cp('purchaserequests','view_row')) {
            redirect("forbidden");
            return;
        }
        if ( $pr_id ) {
            $pr_data = get_po_making_data( $pr_id );

            $this->check_access_to_store();
            $this->check_access_to_this_pr($pr_data);

            if (@ob_get_length())
                @ob_clean();
            //so, we have a valid order data. Prepare the view.
            $pr_data["usgn"] = $this->Db_model->signature_approve( $pr_id, "purchaserequests" );
            prepare_po_pdf( $pr_data, $mode );
        } else {
            show_404();
        }
    }

    function preview_po( $pr_id = 0, $show_close_preview = false, $po_no = NULL ) {
        // temporary $this->check_access_to_store();
        // echo $po_no;exit;
        if(!$this->cp('purchaserequests','view_row')) {
            redirect("forbidden");
            return;
        }
        if ($pr_id) {
            $pr_data = get_po_making_data($pr_id,$po_no);
            // arr($pr_id);exit;
            $this->check_access_to_this_pr($pr_data);

            $pr_data['pr_info'] = get_array_value($pr_data, "pr_info");

            $pr_data["usgn"] = $this->Db_model->signature_approve( $pr_id, "purchaserequests" );

            $view_data['pr_preview'] = prepare_po_pdf($pr_data, "html");

            //show a back button
            $view_data['show_close_preview'] = $show_close_preview && $this->login_user->user_type === "staff" ? true : false;

            $view_data['pr_id'] = $pr_id;

            $this->template->rander("purchaserequests/po_preview", $view_data);
        } else {
            show_404();
        }
    }

    /* prepare suggestion of order item */

    function get_pr_item_suggestion() {
        $key = @$_REQUEST["q"];
        $suggestion = array();

        $items = $this->Invoice_items_model->get_item_suggestion($key);

        foreach ($items as $item) {
            $suggestion[] = array("id" => $item->title, "text" => $item->title);
        }

        //$suggestion[] = array("id" => "+", "text" => "+ " . lang("create_new_item"));

        echo json_encode($suggestion);
    }

    function get_pr_supplier_suggestion() {
        $suggestion = [];
        $req = $this->input->get();
        $q = @$req['q'];
        $model_info = $this->Pr_items_model->get_one(@$req['id']);
        $material_id = intval(@$req['material_id']);
        $this->check_access_to_this_pr_item($model_info);
        $items = $this->Pr_items_model->get_supplier_suggestion($material_id, $q);

        foreach ($items as $item) {
            $suggestion[] = array(
                "id" => $item->supplier_name,
                "text" => $item->supplier_name.(@$item->price?"(".$item->price.$item->currency_symbol.")":""),
                "supplier_id"=>$item->id,//supplier_id
                "supplier_name"=>$item->supplier_name,
                "price"=>intval(@$item->price),
                "currency"=>$item->currency?$item->currency:'THB',
                "currency_symbol"=>$item->currency_symbol?$item->currency_symbol:'฿',
                "address" => $item->address,
                "city"=>$item->city,
                "state"=>$item->state,
                "zip"=>$item->zip,
                "country"=>$item->country,
                "website"=>$item->website,
                "phone"=>$item->phone,
                "vat_number"=>$item->vat_number
            );
        }

        $suggestion[] = array("id" => "+", "text" => "+ " . lang("create_new_supplier"),'currency'=>'THB','currency_symbol'=>'฿');

        echo json_encode($suggestion);
    }

    function get_pr_item_info_suggestion() {
        $item = $this->Invoice_items_model->get_item_info_suggestion($this->input->post("item_name"));
        if ($item) {
            echo json_encode(array("success" => true, "item_info" => $item));
        } else {
            echo json_encode(array("success" => false));
        }
    }

    function get_pr_supplier_info_suggestion() {
        $item = $this->Pr_items_model->get_supplier_info_suggestion($this->input->post("supplier_id"), intval($this->input->post("material_id")));
        if ($item) {
            $item->currency = $item->currency?$item->currency:'THB';
            $item->currency_symbol = $item->currency_symbol?$item->currency_symbol:'฿';
            echo json_encode(array("success" => true, "supplier_info" => $item));
        } else {
            echo json_encode(array("success" => false));
        }
    }

    function get_materials_suggestion() {
        $key = $this->input->get("q");
        $suggestion = array();

        $items = $this->Bom_materials_model->get_materials_suggestion($key);

        foreach ($items as $item) {
            $suggestion[] = array("id" => $item->id, "text" => $item->text);
        }
        echo json_encode($suggestion);
    }

    function get_material_info_suggestion() {
        $item = $this->Bom_materials_model->get_material_info_suggestion($this->input->post("matrial_id"));
        if ($item) {
            echo json_encode(array("success" => true, "item_info" => $item));
        } else {
            echo json_encode(array("success" => false));
        }
    }

    function save_pr_status($id = 0) {
        // temporary $this->access_only_allowed_members();
        if (!$id) {
            show_404();
        }

        if(!$this->cp('purchaserequests','view_row')||!($this->cp('purchaserequests','add_row')||$this->cp('purchaserequests','edit_row'))) {
            redirect("forbidden");
            return;
        }

        $data = array(
            "status_id" => $this->input->post('value')
        );

        $save_id = $this->Purchaserequests_model->save($data, $id);

        if ($save_id) {
            log_notification("pr_status_updated", array("pr_id" => $id));
            $pr_info = $this->Purchaserequests_model->get_details(array("id" => $id))->row();
            echo json_encode(array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, "message" => lang('record_saved'), "pr_status_color" => $pr_info->pr_status_color));
        } else {
            echo json_encode(array("success" => false, lang('error_occurred')));
        }
    }

    /* return a row of order list table */

    private function _row_data($id) {
        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("purchaserequests", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array("id" => $id, "custom_fields" => $custom_fields);
        $data = $this->Purchaserequests_model->get_details($options)->row();
        return $this->_make_row($data, $custom_fields);
    }

    /* load discount modal */

    function discount_modal_form() {
        // temporary $this->access_only_allowed_members();

        validate_submitted_data(array(
            "pr_id" => "required|numeric"
        ));

        $pr_id = $this->input->post('pr_id');

        $view_data['model_info'] = $this->Purchaserequests_model->get_one($pr_id);

        $this->load->view('purchaserequests/discount_modal_form', $view_data);
    }

    /* save discount */

    function save_discount() {
        // temporary $this->access_only_allowed_members();
        if(!$this->cp('purchaserequests','view_row')||!($this->cp('purchaserequests','add_row')||$this->cp('purchaserequests','edit_row'))) {
            redirect("forbidden");
            return;
        }
        validate_submitted_data(array(
            "pr_id" => "required|numeric",
            "discount_type" => "required",
            "discount_amount" => "numeric",
            "discount_amount_type" => "required"
        ));

        $pr_id = $this->input->post('pr_id');

        $data = array(
            "discount_type" => $this->input->post('discount_type'),
            "discount_amount" => $this->input->post('discount_amount'),
            "discount_amount_type" => $this->input->post('discount_amount_type')
        );

        $data = clean_data($data);

        $save_data = $this->Purchaserequests_model->save($data, $pr_id);
        if ($save_data) {
            echo json_encode(array("success" => true, "pr_total_view" => $this->_get_pr_total_view($pr_id), 'message' => lang('record_saved'), "pr_id" => $pr_id));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

    /* list of order items, prepared for datatable  */

    function item_list_data($pr_id = 0) {
        // temporary $this->access_only_allowed_members();
        //$this->dump([$this->cop('prove_row'), $this->cp('purchaserequests', 'prove_row')], true);
        if(!$this->cp('purchaserequests','view_row')) {
            redirect("forbidden");
            return;
        }
        $pr_info = $pr_id?$this->Purchaserequests_model->get_details(array("id" => $pr_id))->row():null;
        $prove = $this->Provetable_model->getProve($pr_id, 'purchaserequests')->row();
        $list_data = $this->Pr_items_model->get_details(array("pr_id" => $pr_id, 'item_type'=>'all'))->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_item_row($pr_info, $prove, $data);
        }
        echo json_encode(array("data" => $result));
    }

    /* list of order of a specific client, prepared for datatable  */

    function pr_list_data_of_client($buyer_id) {
        // temporary $this->check_access_to_store();

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("purchaserequests", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array("buyer_id" => $buyer_id, "custom_fields" => $custom_fields);

        $list_data = $this->Purchaserequests_model->get_details($options)->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $custom_fields);
        }
        echo json_encode(array("data" => $result));
    }
	
	
    function index() {
        if($this->Permission_m->access_purchase_request != true) redirect("forbidden");

        $view_data["custom_field_headers"] = $this->Custom_fields_model->get_custom_field_headers_for_table("purchaserequests", $this->login_user->is_admin, $this->login_user->user_type);
		
		$buttonTops = array();

        $buttonTops[] = anchor(get_uri("purchaserequests/categories"), "<i class='fa fa-bars'></i> ".lang('category_manager'), array("class" => "btn btn-primary", "title" => lang('category_manager'), "id" => "cat-mng-btn"));

        if($this->Permission_m->create_purchase_request == true){
            $buttonTops[] = js_anchor( "<i class='fa fa-shopping-cart'></i> ".lang('add_pr'), array("class" => "btn btn-primary", "title" => lang('add_pr'), "id" => "add-pr-btn"));
        }

		$view_data['buttonTops'] = implode( '', $buttonTops );

        /*$options = [];
        if(!$this->cp('purchaserequests', 'prove_row')) {
            $options['where'] = " pr_status.id!='3' AND pr_status.id!='4' ";
        }
		$view_data['pr_statuses'] = $this->Pr_status_model->get_details($options)->result();*/

        $view_data['pr_status_indexs'] = [["id"=>"", "text"=>"- ".lang("status")." -"], ["id"=>1, "text"=>"New"], ["id"=>2, "text"=>"Request Approval"], ["id"=>3, "text"=>"Approved"], ["id"=>4, "text"=>"Rejected"]];


        $view_data['pr_suppliers'] = $this->Bom_suppliers_model->get_options()->result();
		
        /*$view_data['view_row'] = $this->cp('purchaserequests','view_row');
        $view_data['add_row'] = $this->cp('purchaserequests','add_row');
        $view_data['edit_row'] = $this->cp('purchaserequests','edit_row');
        $view_data['delete_row'] = $this->cp('purchaserequests','delete_row');
        $view_data['prove_row'] = $this->cp('purchaserequests','prove_row');*/
        
        $this->template->rander("purchaserequests/index", $view_data );
    }

    
	


    private function _make_row( $data, $custom_fields ) {
        $pr_url = "";
        if ($this->login_user->user_type == "staff") {
            $pr_url = anchor(get_uri("purchaserequests/view/" . $data->id), $data->doc_no?$data->doc_no:lang('no_have_doc_no').':'.$data->id);
        } else {
            //for client
            $pr_url = anchor(get_uri("purchaserequests/preview/" . $data->id), $data->doc_no?$data->doc_no:lang('no_have_doc_no').':'.$data->id);
        }

        //$client = anchor(get_uri("clients/view/" . $data->buyer_id), $data->buyer_name);
        $client = $data->buyer_name;

        $row_data = array(
            $pr_url,
            $data->category_name?$data->category_name:lang('undefined_category'),
            $data->project_name?$data->project_name:"<span style=\"color:red\">".lang('undefined_project')."</span>",
            $client,
            $data->pr_date,
            format_to_date($data->pr_date, false),
            to_currency($data->pr_value, $data->currency_symbol)
        );

        $pr_status_color = "#777777";
        $pr_status_title = "New";

        if($data->status_id == 3){
            $pr_status_color = "#18a589";
            $pr_status_title = "อนุมัติ";
        }

        if($data->status_id == 4){
            $pr_status_color = "#ff0201";
            $pr_status_title = "ไม่อนุมัติ";
        }

     
		
		$row_data[] = "<span style='background-color: $pr_status_color;' class='label'>$pr_status_title</span>";
        foreach ($custom_fields as $field) {
            $cf_id = "cfv_" . $field->id;
            $row_data[] = $this->load->view("custom_fields/output_" . $field->field_type, array("value" => $data->$cf_id), true);
        }



        if($data->status_id=="3" || $data->status_id=="4"){
            $row_data[] = "";
        }else{
            /*$row_data[] = ($edit_row?modal_anchor(get_uri("purchaserequests/modal_form"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_purchaserequest'), "data-post-id" => $data->id))
                . anchor(get_uri("purchaserequests/process_pr/".$data->id), "<i class='fa fa-bars'></i>", array("class" => "edit", "title" => lang('edit_pr_items'), "data-post-id" => $data->id)):'')
                . ($delete_row?js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete_order'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("purchaserequests/delete"), "data-action" => "delete")):'')
                ;*/


            $buttons = "";

            if($this->login_user->is_admin == 1){
                $buttons = "<a href='#' data-action-url='".get_uri("purchaserequests/modal_form")."' data-post-id='".$data->id."' title='".lang('edit_purchaserequest')."' class='edit' data-act='ajax-modal'><i class='fa fa-pencil'></i></a>";
                $buttons .= "<a href='".get_uri("purchaserequests/process_pr/".$data->id)."' data-post-id='".$data->id."' title='".lang('edit_mr_items')."' class='edit'><i class='fa fa-bars'></i></a>";
                $buttons .= "<a href='#' data-action-url='".get_uri("purchaserequests/delete")."' class='delete' data-action='delete' data-id='".$data->id."' title='".lang('delete_order')."'><i class='fa fa-times fa-fw'></i></a>";
            }

            $row_data[] = $buttons;
        }
        return $row_data;
    }
    
    function categories() {
        // temporary $this->check_access_to_store();
        //$this->dump([$this->cop('prove_row'), $this->cp('purchaserequests', 'prove_row')], true);
        if(!$this->cp('purchaserequests','view_row')) {
            redirect("forbidden");
            return;
        }
        $view_data["custom_field_headers"] = $this->Custom_fields_model->get_custom_field_headers_for_table("purchaserequests", $this->login_user->is_admin, $this->login_user->user_type);
		
		$buttonTops = array();
		
		if( !empty( $this->getRolePermission['add_row'] ) ) {
			//$buttonTops[] = '<a href="'.get_uri("purchaserequests").'" class="btn btn-default">'.lang('back_to_purchases').'</a>';
            $buttonTops[] = js_anchor( "<i class='fa fa-bars'></i> ".lang('back_to_purchases'), array("data-action-url"=>get_uri("purchaserequests"), "class" => "btn btn-default", "title" => lang('back_to_purchases'), "id" => "back-to-pr-btn"));
            //$buttonTops[] = anchor(get_uri("purchaserequests/categories"), "<i class='fa fa-bars'></i> ".lang('category_manager'), array("class" => "btn btn-primary", "title" => lang('category_manager'), "id" => "cat-mng-btn"));
            //$buttonTops[] = modal_anchor( get_uri("purchaserequests/category_form"), "<i class='fa fa-plus-circle'></i> ".lang('add_category'), array("class" => "btn btn-primary", "title" => lang('add_category'), "id" => "add-cat-btn"));
            $buttonTops[] = js_anchor( "<i class='fa fa-plus-circle'></i> ".lang('add_category'), array("data-action-url"=>get_uri("purchaserequests/category_form"), "class" => "btn btn-primary", "title" => lang('add_category'), "id" => "add-cat-btn"));
			//$buttonTops[] = js_anchor("<i class='fa fa-plus-circle'></i> " . lang('add_pr'), array("class" => "btn btn-default", "id" => "add-pr-btn"));
            //$buttonTops[] = modal_anchor(get_uri("purchaserequests/item_modal_form"), "<i class='fa fa-plus-circle'></i>" . lang('add_more_items'), array("class" => "btn btn-default pull-right", "title" => lang('add_more_items'), "data-post-id" => 0, "data-post-pr_id" => 0,'data-post-item_type'=>'oth'));
            //$buttonTops[] = anchor(get_uri("pr_items/grid_view"), "<i class='fa fa-plus-circle'></i> " . lang('add_internal_items'), array("class" => "btn btn-default pull-right"));
            //$buttonTops[] = modal_anchor(get_uri("purchaserequests/item_modal_form"), "<i class='fa fa-plus-circle'></i>" . lang('add_materials'), array("class" => "btn btn-default pull-right", "title" => lang('add_materials'), "data-post-id" => 0, "data-post-pr_id" => 0,'data-post-item_type'=>'mtr'));
		}

		$view_data['buttonTops'] = implode( '', $buttonTops );

        $options = [];
        /*if(!$this->cp('purchaserequests', 'prove_row')) {
            $options['where'] = " pr_status.id!='3' AND pr_status.id!='4' ";
        }
		$view_data['pr_statuses'] = $this->Pr_status_model->get_details($options)->result();
		*/
        $view_data['view_row'] = $this->cp('purchaserequests','view_row');
        $view_data['add_row'] = $this->cp('purchaserequests','add_row');
        $view_data['edit_row'] = $this->cp('purchaserequests','edit_row');
        $view_data['delete_row'] = $this->cp('purchaserequests','delete_row');
        $view_data['prove_row'] = $this->cp('purchaserequests','prove_row');
        $this->template->rander("purchaserequests/category/index", $view_data );
    }

    function list_categories_data() {
        // temporary $this->access_only_allowed_members();
        if(!$this->cp('purchaserequests','view_row')) {
            redirect("forbidden");
            return;
        }

        $options = array(
            //"status_id" => $this->input->post("status_id"),
            //"pr_date" => $this->input->post("start_date"),
            //"deadline" => $this->input->post("end_date"),
            //"custom_fields" => $custom_fields
            "count_pr"=>true
        );

        $list_data = $this->Pr_categories_model->get_details( $options )->result();
        $result = array();
        foreach ($list_data as $data ) {
            $result[] = $this->_make_category_row($data);
        }

        echo json_encode( array("data" => $result ) );
    }

    private function _make_category_row( $data ) {
        //$cat_url = anchor(get_uri("purchaserequests/category_form/" . $data->id), $data->title);
        $cat_url = modal_anchor(get_uri("purchaserequests/category_form/".$data->id), $data->title, array("class" => "edit", "title" => lang('edit_category'), "data-post-id" => $data->id,'data-act'=>'ajax-modal'));
        $creator = $data->creator_name;

        $row_data = array(
            $cat_url,
            $data->description,
            substr($data->created_date,0,10),
            format_to_date($data->created_date, false),
            $creator
        );

        //$view_row = $this->cp('purchaserequests','view_row');
        //$add_row = $this->cp('purchaserequests','add_row');
        $edit_row = $this->cp('purchaserequests','edit_row');
        $delete_row = $this->cp('purchaserequests','delete_row');
        //$prove_row = $this->cp('purchaserequests','prove_row');
        $can_action = ($edit_row || $delete_row);
        if(!$can_action || $data->id=='1')
            $row_data[] = "";
        else{
            $row_data[] = ($edit_row?modal_anchor(get_uri("purchaserequests/category_form/".$data->id), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_category'), "data-post-id" => $data->id,'data-act'=>'ajax-modal')):'')
                //. anchor(get_uri("purchaserequests/process_pr/".$data->id), "<i class='fa fa-bars'></i>", array("class" => "edit", "title" => lang('edit_pr_items'), "data-post-id" => $data->id)):'')
                . ($delete_row&&$data->count_pr=='0'?js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete_category'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("purchaserequests/delete_category"), "data-action" => "delete_category")):'')
            ;
        }
        return $row_data;
    }

    function category_form($id = 0) {
        $id = intval($id);
        $is_new = !$id;
        $view_row = $this->cp('purchaserequests','view_row');
        $add_row = $this->cp('purchaserequests','add_row');
        $edit_row = $this->cp('purchaserequests','edit_row');
        $delete_row = $this->cp('purchaserequests','delete_row');
        //$prove_row = $this->cp('purchaserequests','prove_row');
        
        if(!$view_row || ($is_new&&!$add_row) || (!$is_new&&!$edit_row)) {
            redirect('forbidden');
            return;
        }
        $view_data = [];
        $view_data['model_info'] = $this->Pr_categories_model->get_one($id);
        $this->load->view("purchaserequests/category/form", $view_data );
        //$this->template->rander("purchaserequests/category/form", $view_data );
    }

    public function save_category() {
        validate_submitted_data(array(
            "id" => "numeric"
        ));
        
        $id = $this->input->post('id', 0);

        if(!$id && !$this->cp('purchaserequests','add_row')) {
            redirect("forbidden");
            return;
        }
        if($id && !$this->cp('purchaserequests','edit_row')) {
            redirect("forbidden");
            return;
        }

        $title = $this->input->post('title');
        $description = $this->input->post('description');
        //$cat_info = $this->Pr_categories_model->get_one($id);

        $quantity = unformat_currency($this->input->post('pr_item_quantity'));

        $cat_data = [
            'title'=>$title,
            'description'=>$description
        ];
        if(!$id) {
            $cat_data['created_date'] = date('Y-m-d H:i:s');
            $cat_data['created_by'] = $this->login_user->id;
        }
        $catid = $this->Pr_categories_model->save($cat_data, $id);
        if($catid) {
            //$cat_info = $this->Pr_categories_model->get_one($catid);
            $cat_info = $this->Pr_categories_model->get_details( ['id'=>$catid,"count_pr"=>true] )->row();
            echo json_encode(array("success" => true, "data" => $this->_make_category_row($cat_info), 'id' => $cat_info->id, 'message' => lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

    function list_lacked_project_materials() {
        header('Content-Type: application/json; charset=utf-8');
        echo $this->Purchaserequest_m->jLackedMaterial();
    }
    
    function list_lacked_stock_materials() {

        $sql = "
            SELECT bs.*, 
            bm.name `material_name`, bm.unit `material_unit`, bm.noti_threshold,
            bsg.name as importname,
            IF(bmp.supplier_id IS NULL, 0, bmp.supplier_id) as supplier_id,
            IF(bmp.price IS NULL, 0, bmp.price) as price,
            IF(bsp.company_name IS NULL, '', bsp.company_name) as supplier_name,bsp.currency,bsp.currency_symbol
            FROM bom_stocks bs 
            INNER JOIN bom_materials bm ON bm.id = bs.material_id 
            INNER JOIN bom_stock_groups bsg ON bsg.id = bs.group_id 
            LEFT JOIN (SELECT material_id,supplier_id,price/ratio as price FROM bom_material_pricings ORDER BY price ASC LIMIT 0,1) as bmp ON bmp.material_id=bm.id
            LEFT JOIN bom_suppliers as bsp ON bmp.supplier_id=bsp.id
            WHERE bs.remaining<bm.noti_threshold
            GROUP BY bs.id 
        ";
        $rows = $this->db->query($sql)->result();
        
        $imports = [];
        foreach($rows as $row) {
            $row->ratio = abs($row->noti_threshold-$row->remaining);
            $span = '<div class="stock'.$row->group_id.'_lacked_material lacked_material" data-material-id="'.$row->material_id.'" data-lacked-amount="'.$row->ratio.'" data-unit="'.$row->material_unit.'" data-supplier-name="'.$row->supplier_name.'" data-supplier-id="'.$row->supplier_id.'" data-price="'.$row->price.'" data-currency="'.$row->currency.'" data-currency_symbol="'.$row->currency_symbol.'" style="display:none;">'.$row->material_name.' '.$row->ratio.$row->material_unit.'</div>';
            $button = '<button type="button" class="btn btn-warning pull-right btn-pr" id="btn-pr1" onclick="purchaseRequest(\'#btn-pr1\',\'stock'.$row->group_id.'_\')"><i class="fa fa-shopping-cart"></i> '.lang('request_low_materials').'</button>';
            if(!isset($imports[$row->group_id])) {
                $import = [];
                $import[] = $row->group_id;
                $import[] = '<a href="javascript:;" onclick="javascript:jQuery(\'.stock'.$row->group_id.'_lacked_material\').toggle();">'.$row->importname.'</a>'.$span;
                $import[] = 1;
                $import[] = $button;
                $imports[$row->group_id] = $import;
            }else{
                $import = $imports[$row->group_id];
                $import[1] = $import[1].$span;
                $import[2]++;
                $import[3] = $button;
                $imports[$row->group_id] = $import;
            }
        }

        echo json_encode(array("data" => array_values($imports),"success"=>1,"message"=>"Success"));
    }



    function PO() {
        // temporary $this->check_access_to_store();
        //$this->dump([$this->cop('prove_row'), $this->cp('purchaserequests', 'prove_row')], true);
        if(!$this->cp('purchaserequests','view_row')) {
            redirect("forbidden");
            return;
        }
        $view_data["custom_field_headers"] = $this->Custom_fields_model->get_custom_field_headers_for_table("purchaserequests", $this->login_user->is_admin, $this->login_user->user_type);
		
		$buttonTops = array();
		
		//if( !empty( $this->getRolePermission['add_row'] ) ) {
			//$buttonTops[] = anchor( "<i class='fa fa-bars'></i> ".lang('category_manager'), array("class" => "btn btn-primary", "title" => lang('category_manager'), "id" => "cat-mng-btn"));
            $buttonTops[] = anchor(get_uri("purchaserequests/categories"), "<i class='fa fa-bars'></i> ".lang('category_manager'), array("class" => "btn btn-primary", "title" => lang('category_manager'), "id" => "cat-mng-btn"));
            $buttonTops[] = js_anchor( "<i class='fa fa-shopping-cart'></i> ".lang('add_pr'), array("class" => "btn btn-primary", "title" => lang('add_pr'), "id" => "add-pr-btn"));
			//$buttonTops[] = js_anchor("<i class='fa fa-plus-circle'></i> " . lang('add_pr'), array("class" => "btn btn-default", "id" => "add-pr-btn"));
            //$buttonTops[] = modal_anchor(get_uri("purchaserequests/item_modal_form"), "<i class='fa fa-plus-circle'></i>" . lang('add_more_items'), array("class" => "btn btn-default pull-right", "title" => lang('add_more_items'), "data-post-id" => 0, "data-post-pr_id" => 0,'data-post-item_type'=>'oth'));
            //$buttonTops[] = anchor(get_uri("pr_items/grid_view"), "<i class='fa fa-plus-circle'></i> " . lang('add_internal_items'), array("class" => "btn btn-default pull-right"));
            //$buttonTops[] = modal_anchor(get_uri("purchaserequests/item_modal_form"), "<i class='fa fa-plus-circle'></i>" . lang('add_materials'), array("class" => "btn btn-default pull-right", "title" => lang('add_materials'), "data-post-id" => 0, "data-post-pr_id" => 0,'data-post-item_type'=>'mtr'));
		//}

		$view_data['buttonTops'] = implode( '', $buttonTops );

        $options = [];
        if(!$this->cp('purchaserequests', 'prove_row')) {
            $options['where'] = " pr_status.id!='3' AND pr_status.id!='4' ";
        }
		$view_data['pr_statuses'] = $this->Pr_status_model->get_details($options)->result();

        $view_data['pr_suppliers'] = $this->Bom_suppliers_model->get_options()->result();
		
        $view_data['view_row'] = $this->cp('purchaserequests','view_row');
        $view_data['add_row'] = $this->cp('purchaserequests','add_row');
        $view_data['edit_row'] = $this->cp('purchaserequests','edit_row');
        $view_data['delete_row'] = $this->cp('purchaserequests','delete_row');
        $view_data['prove_row'] = $this->cp('purchaserequests','prove_row');
        $this->template->rander("purchaserequests/POview", $view_data );
    }




    /*function approve($id) {
        $pr = $this->Purchaserequests_model->get_one($id);
        if($pr) {
            $pr->status_id = 3;
            $pr->save($pr, $pr->id);

            $prove = $this->Provetable_model->get_where(['doc_id'=>$id,'tbName'=>'purchaserequests']);
            if(!$prove) {
                $prove = [];
                $prove['id'] = 0;
                $prove['doc_id'] = $pr_id;
                $prove['tbName'] = 'purchaserequests';
                $prove['user_id'] = $this->login_user->id;
                $prove['doc_date'] = date('Y-m-d H:i:s');
                $prove['status_id'] = $pr_data['status_id'];
                $this->Provetable_model->save($prove);
            }
        }
        redirect('purchaserequests/view/'.$id);
    }*/
	
    function PO_data() {
        // temporary $this->access_only_allowed_members();
        if(!$this->cp('purchaserequests','view_row')) {
            redirect("forbidden");
            return;
        }
        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("purchaserequests", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array(
            "status_id" => $this->input->post("status_id"),
            "supplier_id" => $this->input->post("supplier_id"),
            "pr_date" => $this->input->post("start_date"),
            "deadline" => $this->input->post("end_date"),
            "custom_fields" => $custom_fields
        );
		//$this->dump($options);
        $options['view_row'] = $this->cop('view_row');
        $list_data = $this->Purchaserequests_model->get_PO_details( $options )->result();
        $result = array();
        foreach ($list_data as $data ) {
            $result[] = $this->_make_PO_row($data, $custom_fields);
        }

        echo json_encode( array("data" => $result ) );
    }
	
    private function _make_PO_row( $data, $custom_fields ) {
        $pr_url = "";
        
        if ($this->login_user->user_type == "staff") {
            $pr_url = anchor(get_uri("purchaserequests/preview_po/".$data->id."/1/".$data->doc_no), $data->doc_no?$data->doc_no:lang('no_have_doc_no').':'.$data->id);
        } else {
            //for client
            $pr_url = anchor(get_uri("purchaserequests/preview_po/" . $data->id."/1/".$data->doc_no), $data->doc_no?$data->doc_no:lang('no_have_doc_no').':'.$data->id);
        }
        // arr($data);exit;
        $pr_id_link ="";
        if ($this->login_user->user_type == "staff") {
            $pr_id_link = anchor(get_uri('purchaserequests/view/'.$data->id), $data->prID?$data->prID:lang('no_have_doc_no').':'.$data->id);
        } else {
            //for client
            $pr_id_link = anchor(get_uri("purchaserequests/preview/" . $data->id), $data->prID?$data->prID:lang('no_have_doc_no').':'.$data->id);
        }
        //$client = anchor(get_uri("clients/view/" . $data->buyer_id), $data->buyer_name);
        $client = $data->buyer_name;

        $row_data = array(
            $pr_id_link,
            $pr_url,
            $data->category_name?$data->category_name:lang('undefined_category'),
            $data->project_name?$data->project_name:"<span style=\"color:red\">".lang('undefined_project')."</span>",
            $client,
            $data->pr_date,
            format_to_date($data->pr_date, false),
            to_decimal_format3($data->pr_value),
            !empty($data->currency) ? lang($data->currency) : lang('THB')
        );

        if ($this->login_user->user_type == "staff") {
            $row_data[] = js_anchor( $data->pr_status_title, array( "style" => "background-color: $data->pr_status_color", "class" => "label", "data-id" => $data->id, "data-value" => $data->status_id, "data-act" => "update-order-status" ) );
        } else {
            $row_data[] = "<span style='background-color: $data->pr_status_color;' class='label'>$data->pr_status_title</span>";
        }

        foreach ($custom_fields as $field) {
            $cf_id = "cfv_" . $field->id;
            $row_data[] = $this->load->view("custom_fields/output_" . $field->field_type, array("value" => $data->$cf_id), true);
        }

        //$view_row = $this->cp('purchaserequests','view_row');
        //$add_row = $this->cp('purchaserequests','add_row');
        $edit_row = $this->cp('purchaserequests','edit_row');
        $delete_row = $this->cp('purchaserequests','delete_row');
        //$prove_row = $this->cp('purchaserequests','prove_row');

        if($data->status_id=="3")
            $row_data[] = "";
        else{
            $row_data[] = ($edit_row?modal_anchor(get_uri("purchaserequests/modal_form"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_purchaserequest'), "data-post-id" => $data->id))
                . anchor(get_uri("purchaserequests/process_pr/".$data->id), "<i class='fa fa-bars'></i>", array("class" => "edit", "title" => lang('edit_pr_items'), "data-post-id" => $data->id)):'')
                . ($delete_row?js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete_order'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("purchaserequests/delete"), "data-action" => "delete")):'')
                ;
        }
        return $row_data;
    }
	
    function list_data() {
        // temporary $this->access_only_allowed_members();
        /*if(!$this->cp('purchaserequests','view_row')) {
            redirect("forbidden");
            return;
        }*/
        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("purchaserequests", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array(
            "status_id" => $this->input->post("status_id"),
            "supplier_id" => $this->input->post("supplier_id"),
            "pr_date" => $this->input->post("start_date"),
            "deadline" => $this->input->post("end_date"),
            "custom_fields" => $custom_fields
        );
		//$this->dump($options);
        $options['view_row'] = $this->cop('view_row');
        $list_data = $this->Purchaserequests_model->get_details( $options )->result();
        $result = array();
        foreach ($list_data as $data ) {
            $result[] = $this->_make_row($data, $custom_fields);
        }

        echo json_encode( array("data" => $result ) );
    }

	function add_pr_item_to_cart() {
        if(!$this->cop('view_row')|| !($this->cop('add_row') || $this->cop('edit_row'))) {
            redirect("forbidden");
        }
        $data = $this->input->post();
        //var_dump($data);exit;
        if(!isset($data['item']))
            $data['item'] = array();
        //$this->dump($data['materials'], true);
        //$this->dump($data['materials']);
        //echo "<br />------------<br />";
        foreach($data['item'] as $item) {
            //$row = $this->Pr_items_model->get_one_material_in_cart($material['id'], intval(@$material['supplier_id']), $this->login_user->id);
            $row = $this->Pr_items_model->get_one_item_in_cart($item['id'], $this->login_user->id);
            $item_row = $this->Pr_items_model->get_one_item($item['id']);
            if(!$row||($row['deleted']=='1')) {
                if(!$row) {
                    $row = [];
                    $row['id'] = 0;
                }
                $row['code']=$item_row->title;
                $row['title']=$item_row->description;
                $row['description']=$item_row->description;
                $row['item_type']="itm";
                $row['quantity']=intval($item['amount']);
                $row['unit_type']=$item_row->unit_type;
                $row['rate']=doubleval(@$item['price']);
                $row['total']=$row['quantity']*$row['rate'];
                $row['pr_id']=0;
                $row['created_by']=$this->login_user->id;
                $row['material_id']=0;
                $row['item_id']=$item['id'];
                $row['supplier_id']=intval(@$item['supplier_id']);
                $row['supplier_name']=@$item['supplier_name'];
                $row['project_id']=intval(@$item['project_id']);
                $row['project_name']=@$item['project_name'];
                $row['sort']=0;
                $row['deleted']=0;
            }else{
                $row['code']=$item_row->title;
                $row['title']=$item_row->description;
                $row['description']=$item_row->description;
                $row['item_type']="itm";
                $row['quantity']=intval($item['amount']);
                $row['unit_type']=$item_row->unit_type;
                $row['rate']=doubleval(@$item['price']);
                $row['total']=$row['quantity']*$row['rate'];
                $row['pr_id']=0;
                $row['created_by']=$this->login_user->id;
                $row['material_id']=0;
                $row['item_id']=$item['id'];
                $row['supplier_id']=intval(@$item['supplier_id']);
                $row['supplier_name']=@$item['supplier_name'];
                if(intval(@$item['project_id']))
                    $row['project_id']=intval(@$item['project_id']);
                if(@$item['project_name'])
                    $row['project_name']=@$item['project_name'];
                $row['deleted']=0;
                //$row['quantity'] += intval($material['amount']);
                $row['quantity'] = intval($item['amount']);
                $row['total']=$row['quantity']*$row['rate'];
                $row['supplier_name']=@$item['supplier_name'];
            }
            //var_dump($row);exit;
            //$this->dump($row);
            $this->Pr_items_model->save($row, $row['id']);
            
            /*try{
                $this->Pr_items_model->save($row, $row['id']);
            }catch(Exception $e) {
                echo $e->getMessage();
            }
            $this->dump($row);*/
            //var_dump($row);
        }
        redirect("purchaserequests/process_pr");
    }
    function list_lacked_stock_item() {
        $sql = "
            SELECT bs.*, 
            bm.title `item_name`, bm.unit_type `item_unit`, bm.noti_threshold,
            bsg.name as importname,
            IF(bmp.supplier_id IS NULL, 0, bmp.supplier_id) as supplier_id,
            IF(bmp.price IS NULL, 0, bmp.price) as price,
            IF(bsp.company_name IS NULL, '', bsp.company_name) as supplier_name,bsp.currency,bsp.currency_symbol
            FROM bom_item_stocks bs 
            INNER JOIN items bm ON bm.id = bs.item_id 
            INNER JOIN bom_item_groups bsg ON bsg.id = bs.group_id 
            LEFT JOIN (SELECT item_id,supplier_id,price/ratio as price FROM bom_item_pricings ORDER BY price ASC LIMIT 0,1) as bmp ON bmp.item_id=bm.id
            LEFT JOIN bom_suppliers as bsp ON bmp.supplier_id=bsp.id
            WHERE bs.remaining<bm.noti_threshold
            GROUP BY bs.id  
        ";
        $rows = $this->db->query($sql)->result();
        $imports = [];
        foreach($rows as $row) {
            $row->ratio = abs($row->noti_threshold-$row->remaining);
            $span = '<div class="stock'.$row->group_id.'_lacked_material lacked_material" data-item-id="'.$row->item_id.'" data-lacked-amount="'.$row->ratio.'" data-unit="'.$row->item_unit.'" data-supplier-name="'.$row->supplier_name.'" data-supplier-id="'.$row->supplier_id.'" data-price="'.$row->price.'" data-currency="'.$row->currency.'" data-currency_symbol="'.$row->currency_symbol.'" style="display:none;">'.$row->item_name.' '.$row->ratio.$row->item_unit.'</div>';
            $button = '<button type="button" class="btn btn-warning pull-right btn-pr" id="btn-pr1" onclick="purchaseRequestItem(\'#btn-pr1\',\'stock'.$row->group_id.'_\')"><i class="fa fa-shopping-cart"></i> '.lang('request_low_item').'</button>';
            if(!isset($imports[$row->group_id])) {
                $import = [];
                $import[] = $row->group_id;
                $import[] = '<a href="javascript:;" onclick="javascript:jQuery(\'.stock'.$row->group_id.'_lacked_material\').toggle();">'.$row->importname.'</a>'.$span;
                $import[] = 1;
                $import[] = $button;
                $imports[$row->group_id] = $import;
            }else{
                $import = $imports[$row->group_id];
                $import[1] = $import[1].$span;
                $import[2]++;
                $import[3] = $button;
                $imports[$row->group_id] = $import;
            }
        }

        echo json_encode(array("data" => array_values($imports),"success"=>1,"message"=>"Success"));
    }
}

/* End of file purchaserequests.php */
/* Location: ./application/controllers/purchaserequests.php */