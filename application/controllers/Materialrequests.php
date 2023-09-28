<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Materialrequests extends MY_Controller
{

	function __construct()
	{
		parent::__construct();
		// $param['LPAD'] = 4;
		// $param['column'] = 'doc_no';
		// $param['table'] = 'materialrequests';
		// $param['prefix'] = 'PR';
		// echo $this->dao->genDocNo( $param  );
		// exit;

		$this->init_permission_checker("order");
		$this->className = 'materialrequests';
		$this->load->model('Provetable_model');
		$this->load->model('Db_model');
		$this->load->helper('notifications');
		$this->load->model('Mr_categories_model');
		$this->load->model('Materialrequests_model');
		$this->load->model('Mr_items_model');
		$this->load->model("Mr_status_model");
		$this->load->model('Items_model');
		$this->load->model('Permission_m');
		$this->load->model('Materialrequest_m');
		$this->load->model('Pr_categories_model');
		$this->load->model('Account_category_model');
	}

	function approve($mr_id = 0)
	{
		if ($mr_id == 0) {
			$this->load->view("error/html/error_404");
			return;
		}

		$info = $this->Materialrequests_model->get_one($mr_id);

		// Get material request detail
		$items = $this->Mr_items_model->get_materialrequest_item_by_id($mr_id);
		if (sizeof($items) == 0) {
			redirect("materialrequests/view/" . $mr_id . "/nodata");
			return;
		}

		// Verify stock remaining to usabled
		$usabled = array();
		if ($info->mr_type == 2) {
			foreach ($items as $item) {
				array_push($usabled, $this->Bom_item_stocks_model->dev2_verifyStockUsabled($item->stock_id, $item->quantity));
			}
		} else {
			foreach ($items as $item) {
				array_push($usabled, $this->Bom_stocks_model->dev2_verifyStockUsabled($item->stock_id, $item->quantity));
			}
		}

		if (in_array(false, $usabled)) {
			redirect("materialrequests/view/" . $mr_id . "/error");
			return;
		} else {
			// Update stock remaining to used and update used status
			if ($info->mr_type == 2) {
				foreach ($items as $item) {
					$this->Bom_item_stocks_model->dev2_updateStockUsed($item->stock_id, $item->quantity);
					$this->Bom_project_item_items_model->dev2_updateUsedStatusById($item->bpim_id, 1);
				}
			} else {
				foreach ($items as $item) {
					$this->Bom_stocks_model->dev2_updateStockUsed($item->stock_id, $item->quantity);
					$this->Bom_project_item_materials_model->dev2_updateUsedStatusById($item->bpim_id, 1);
				}
			}

			// Update material request status
			$this->Materialrequests_model->dev2_updateApprovalStatus($mr_id, 3, $this->login_user->id);
			redirect("materialrequests/view/" . $mr_id . "/success");
		}
	}

	function disapprove($mr_id = 0)
	{
		if ($mr_id == 0) {
			$this->load->view("error/html/error_404");
			return;
		}

		// Get material request info
		$info = $this->Materialrequests_model->get_one($mr_id);

		// Get material request detail
		$items = $this->Mr_items_model->get_materialrequest_item_by_id($mr_id);
		if (sizeof($items) == 0) {
			redirect("materialrequests/view/" . $mr_id . "/nodata");
			return;
		}

		// Update used status
		if ($info->mr_type == 2) {
			foreach ($items as $item) {
				$this->Bom_project_item_items_model->dev2_rejectMaterialRequestById($item->bpim_id);
			}
		} else {
			foreach ($items as $item) {
				$this->Bom_project_item_materials_model->dev2_rejectMaterialRequestById($item->bpim_id);
			}
		}

		// Clear project material stock id
		$this->Mr_items_model->dev2_clearProjectMaterialStockId($mr_id);

		// Update material request status 
		$this->Materialrequests_model->dev2_updateApprovalStatus($mr_id, 4, $this->login_user->id);

		redirect("materialrequests/view/" . $mr_id . "/reject");
	}

	function process_pr($mr_id = 0)
	{
		if ($this->Permission_m->access_material_request != true)
			redirect("forbidden");
		if ($this->Permission_m->create_material_request != true)
			redirect("forbidden");


		$view_data = get_mr_making_data();
		$conditions = array("created_by" => $this->login_user->id, "mr_id" => $mr_id, "deleted" => 0);
		$view_data["cart_items_count"] = $this->Mr_items_model->get_all_where($conditions)->num_rows();

		$view_data['clients_dropdown'] = "";
		$view_data['mr_id'] = $mr_id;
		if ($this->login_user->user_type == "staff") {
			$view_data['clients_dropdown'] = $this->_get_buyers_dropdown();
		}

		$this->template->rander("materialrequests/process_pr", $view_data);
	}

	function item_list_data_of_login_user($mr_id = 0)
	{
		// temporary $this->check_access_to_store();
		$options = array("mr_id" => $mr_id, "created_by" => $this->login_user->id, 'item_type' => 'all');
		if (!$mr_id) {
			$options['processing'] = true;
		}
		$mr_info = $mr_id ? $this->Materialrequests_model->get_details(array("id" => $mr_id))->row() : null;
		$prove = $this->Provetable_model->getProve($mr_id, 'materialrequests')->row();
		$list_data = $this->Mr_items_model->get_details($options)->result();
		$result = array();
		foreach ($list_data as $data) {
			$result[] = $this->_make_item_row($mr_info, $prove, $data);
		}

		echo json_encode(array("data" => $result));
	}

	/* prepare a row of order item list table */
	private function _make_item_row($pr, $prove, $data)
	{
		if ($data->item_id) {
			$item = "<div class='item-row strong mb5' data-id='$data->id'>" . $data->title . "</div>";
		} else {
			$item = "<div class='item-row strong mb5' data-id='$data->id'>" . $data->code . ($this->cop('prove_row') ? ":" . $data->title : "") . "</div>";
		}

		if ($data->description) {
			$item .= "<span>" . nl2br($data->description) . "</span>";
		}
		$type = $data->unit_type ? $data->unit_type : "";

		$edit_row = false;
		$delete_row = false;

		if ($pr->status_id == 1) {
			if ($this->Permission_m->update_material_request == true) {
				$edit_row = true;
			}

			if ($this->Permission_m->delete_material_request == true) {
				$delete_row = true;
			}
		}

		return array(
			$data->sort,
			$item,
			//$data->supplier_name,
			to_decimal_format($data->quantity) . " " . $type,
				/* to_currency($data->rate, $data->currency_symbol, 4),
				to_currency($data->total, $data->currency_symbol, 4), */
			($edit_row ? modal_anchor(get_uri("materialrequests/item_modal_form"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_item'), "data-post-id" => $data->id, "data-post-mr_id" => $data->mr_id, "data-post-item_type" => $data->item_type, "data-post-item_id" => (($data->item_type != 'itm') ? 0 : $data->item_id), "data-post-material_id" => (($data->item_type != 'mtr') ? 0 : $data->material_id))) : '')
			. ($delete_row ? js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("materialrequests/delete_item"), "data-action" => "delete")) : '')
		);
	}

	/* load item modal */
	function item_modal_form()
	{
		// temporary $this->check_access_to_store();
		validate_submitted_data(
			array(
				"id" => "numeric"
			)
		);
		$post = $this->input->post();
		$model_info = $this->Mr_items_model->get_one(@$post['id']);
		$this->check_access_to_this_mr_item($model_info);

		$view_data['model_info'] = $model_info;
		$view_data['code'] = $model_info->code;
		$view_data['mr_id'] = @$post['id'] ? $model_info->mr_id : @$post['mr_id'];
		$view_data['item_id'] = $model_info->item_id;
		$view_data['material_id'] = intval($model_info->material_id);
		$view_data['item_type'] = @$post['id'] ? $model_info->item_type : @$post['item_type'];
		//$view_data['supplier_id'] = $model_info->supplier_id;
		$view_data['currency'] = @$post['id'] ? $model_info->currency : 'THB';
		$view_data['currency'] = $view_data['currency'] ? $view_data['currency'] : 'THB';
		$view_data['currency_symbol'] = @$post['id'] ? $model_info->currency_symbol : '฿';
		$view_data['currency_symbol'] = $view_data['currency_symbol'] ? $view_data['currency_symbol'] : '฿';
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

		//$this->template->rander("materialrequests/item_modal_form", $view_data);
		$this->load->view('materialrequests/item_modal_form', $view_data);
	}

	function fin_item_modal_form()
	{
		// temporary $this->check_access_to_store();
		validate_submitted_data(
			array(
				"id" => "numeric"
			)
		);
		$post = $this->input->post();
		$model_info = $this->Mr_items_model->get_one(@$post['id']);
		$this->check_access_to_this_mr_item($model_info);

		$view_data['model_info'] = $model_info;
		$view_data['code'] = $model_info->code;
		$view_data['mr_id'] = @$post['id'] ? $model_info->mr_id : @$post['mr_id'];
		$view_data['item_id'] = $model_info->item_id;
		$view_data['material_id'] = intval($model_info->material_id);
		$view_data['item_type'] = @$post['id'] ? $model_info->item_type : @$post['item_type'];

		//$this->template->rander("materialrequests/item_modal_form", $view_data);
		$this->load->view('materialrequests/fin_item_modal_form', $view_data);
	}

	/* add or edit an order item */

	function save_item()
	{
		// temporary $this->check_access_to_store();
		validate_submitted_data(
			array(
				"id" => "numeric"
			)
		);

		$id = $this->input->post('id', 0);

		if (!$id || $this->Permission_m->update_material_request != true) {
			echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
			return;
		}

		$item_type = $this->input->post('item_type');
		$item_info = null;
		if ($item_type) {
			$item_info = $this->Mr_items_model->get_one($id);
		}
		//$this->check_access_to_this_mr_item($item_info);

		$quantity = unformat_currency($this->input->post('mr_item_quantity'));
		$limit = $this->input->post('material_limit');

		$mr_item_data = array(
			"description" => $this->input->post('mr_item_description'),
			"quantity" => $quantity
		);

		//var_dump($quantity,$limit,(int)$quantity>(int)$limit);exit;

		//check if item quantity exceed limit

		if ($quantity > $limit) {
			//echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
			echo json_encode(array("success" => false, 'message' => lang('out_of_stock')));
			return;
		}

		$supplier_id = $this->input->post('supplier_id', 0);

		$mr_id = $this->input->post("mr_id");
		//var_dump($mr_id);exit;
		if ($mr_id) {
			$rate = unformat_currency($this->input->post('mr_item_rate'));
			$mr_item_data["mr_id"] = $mr_id;
			$mr_item_data["code"] = $this->input->post('code');
			$mr_item_data["title"] = ($item_type == 'itm') ? $item_info->title : $this->input->post('mr_item_title');
			$mr_item_data["unit_type"] = $this->input->post('mr_unit_type');
			$mr_item_data["item_type"] = $item_type;
			$mr_item_data["item_id"] = $id && $item_info ? $item_info->item_id : $this->input->post('item_id', 0);
			$mr_item_data["material_id"] = $this->input->post('material_id', 0);
		} else {
			//var_dump($rate,$quantity);exit;
			$mr_item_data["title"] = ($item_type == 'itm') ? $item_info->title : $this->input->post('mr_item_title');
			$mr_item_data["code"] = $this->input->post('code');
			$mr_item_data["unit_type"] = $this->input->post('mr_unit_type');
			$mr_item_data["rate"] = unformat_currency($this->input->post('mr_item_rate'));
			$mr_item_data["item_type"] = $item_type;
			$mr_item_data["item_id"] = $id ? $item_info->item_id : $this->input->post('item_id', 0);
			$mr_item_data["material_id"] = $this->input->post('material_id', 0);
		}
		$mr_item_id = $this->Mr_items_model->save($mr_item_data, $id);
		if ($mr_item_id) {
			$options = array("id" => $mr_item_id, 'item_type' => 'all');
			$mr_info = $mr_id ? $this->Materialrequests_model->get_details(array("id" => $mr_id))->row() : null;
			$prove = $this->Provetable_model->getProve($mr_id, 'materialrequests')->row();
			$item_info = $this->Mr_items_model->get_details($options)->row();
			//redirect('/materialrequests/process_pr/'.$item_info->mr_id);

			$this->dao->runPoNo($item_info->mr_id);

			echo json_encode(array("success" => true, "mr_id" => $item_info->mr_id, "data" => $this->_make_item_row($mr_info, $prove, $item_info), "pr_total_view" => $this->_get_mr_total_view($item_info->mr_id), 'id' => $mr_item_id, 'message' => lang('record_saved')));
		} else {
			//redirect('/materialrequests/process_pr');
			echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
		}
	}

	//update the sort value for order item
	function update_item_sort_values($id = 0)
	{
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
				$this->Mr_items_model->save($data, $id);
			}
		}
	}

	/* delete or undo an order item */

	function delete_item()
	{
		if ($this->Permission_m->delete_material_request != true) {
			echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
			return;
		}

		validate_submitted_data(
			array(
				"id" => "required|numeric"
			)
		);

		$id = $this->input->post('id');
		$pr_item_info = $this->Mr_items_model->get_one($id);
		$mr_id = $pr_item_info->mr_id;
		//$this->check_access_to_this_mr_item($pr_item_info);

		if ($this->input->post('undo')) {
			if ($this->Mr_items_model->delete($id, true)) {
				$options = array("id" => $id);
				$item_info = $this->Mr_items_model->get_details($options)->row();
				$mr_info = $item_info->mr_id ? $this->Materialrequests_model->get_details(array("id" => $mr_id))->row() : null;
				$prove = $this->Provetable_model->getProve($mr_id, 'materialrequests')->row();
				echo json_encode(array("success" => true, "mr_id" => $item_info->mr_id, "data" => $this->_make_item_row($mr_info, $prove, $item_info), "pr_total_view" => $this->_get_mr_total_view($item_info->mr_id), "message" => lang('record_undone')));
			} else {
				echo json_encode(array("success" => false, lang('error_occurred')));
			}
		} else {
			if ($this->Mr_items_model->delete($id)) {
				$item_info = $this->Mr_items_model->get_one($id);
				echo json_encode(array("success" => true, "mr_id" => $item_info->mr_id, "pr_total_view" => $this->_get_mr_total_view($item_info->mr_id), 'message' => lang('record_deleted')));
			} else {
				echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
			}
		}
	}

	/* order total section */

	private function _get_mr_total_view($mr_id = 0)
	{
		if ($mr_id) {
			$view_data["mr_total_summary"] = $this->Materialrequests_model->get_mr_total_summary($mr_id);
			$view_data["mr_id"] = $mr_id;
			$view_data["edit_row"] = false; //$this->cp('materialrequests', 'edit_row');
			return $this->load->view('materialrequests/pr_total_section', $view_data, true);
		} else {
			$view_data = get_mr_making_data();
			$view_data["edit_row"] = false; //$this->cp('materialrequests', 'edit_row');
			return $this->load->view('materialrequests/processing_pr_total_section', $view_data, true);
		}
	}

	function place_order()
	{
		// temporary $this->check_access_to_store();

		$mr_items = $this->Mr_items_model->get_all_where(array("created_by" => $this->login_user->id, "mr_id" => 0, "deleted" => 0))->result();
		if (!$mr_items) {
			echo json_encode(array("success" => false, "redirect_to" => get_uri("materialrequests/process_pr"), 'message' => lang('at_least_one_item')));
			//echo json_encode(array("success" => false, 'message' => lang('at_least_one_item')));
			return;
			//show_404();
		}

		$mr_data = array(
			//"buyer_id" => $this->input->post("buyer_id") ? $this->input->post("buyer_id") : $this->login_user->client_id,
			"buyer_id" => $this->input->post("buyer_id") ? $this->input->post("buyer_id") : $this->login_user->id,
			"mr_date" => get_today_date(),
			"note" => $this->input->post('pr_note'),
			"created_by" => $this->login_user->id,
			"status_id" => $this->Mr_status_model->get_first_status(),
			"tax_id" => get_setting('mr_tax_id') ? get_setting('mr_tax_id') : 0,
			"tax_id2" => get_setting('mr_tax_id2') ? get_setting('mr_tax_id2') : 0
		);

		$mr_id = $this->Materialrequests_model->save($mr_data);

		if ($mr_id) {
			//save items to this order
			$this->load->model('Bom_material_pricings_model');
			foreach ($mr_items as $pr_item) {
				$mr_item_data = array("mr_id" => $mr_id);
				$this->Mr_items_model->save($mr_item_data, $pr_item->id);

				if ($pr_item->supplier_id && $pr_item->material_id) {
					$data = [
						'ratio' => $pr_item->quantity,
						'price' => $pr_item->total
					];
					$where = [
						'material_id' => $pr_item->material_id,
						'supplier_id' => $pr_item->supplier_id
					];
					$this->Bom_material_pricings_model->update_where($data, $where);
				}
			}

			$redirect_to = get_uri("materialrequests/view/$mr_id");
			if ($this->login_user->user_type == "client") {
				$redirect_to = get_uri("materialrequests/preview/$mr_id");
			}

			//send notification
			log_notification("new_pr_received", array("mr_id" => $mr_id));

			echo json_encode(array("success" => true, "redirect_to" => $redirect_to, 'message' => lang('record_saved')));
		} else {
			echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
		}
	}

	function add_pr_material_to_cart()
	{
		if (!$this->cop('view_row') || !($this->cop('add_row') || $this->cop('edit_row'))) {
			redirect("forbidden");
		}
		$data = $this->input->post();
		//var_dump($data);exit;
		if (!isset($data['materials']))
			$data['materials'] = array();
		//$this->dump($data['materials'], true);
		//$this->dump($data['materials']);
		//echo "<br />------------<br />";
		foreach ($data['materials'] as $material) {
			//$row = $this->Mr_items_model->get_one_material_in_cart($material['id'], intval(@$material['supplier_id']), $this->login_user->id);
			$row = $this->Mr_items_model->get_one_material_in_cart($material['id'], $this->login_user->id);
			$material_row = $this->Mr_items_model->get_one_material($material['id']);
			if (!$row || ($row['deleted'] == '1')) {
				if (!$row) {
					$row = [];
					$row['id'] = 0;
				}
				$row['code'] = $material_row->name;
				$row['title'] = $material_row->production_name;
				$row['description'] = $material_row->description;
				$row['item_type'] = "mtr";
				$row['quantity'] = intval($material['amount']);
				$row['unit_type'] = $material_row->unit;
				;
				$row['rate'] = doubleval(@$material['price']);
				$row['total'] = $row['quantity'] * $row['rate'];
				$row['mr_id'] = 0;
				$row['created_by'] = $this->login_user->id;
				$row['item_id'] = 0;
				$row['material_id'] = $material['id'];
				$row['supplier_id'] = intval(@$material['supplier_id']);
				$row['supplier_name'] = @$material['supplier_name'];
				$row['project_id'] = intval(@$material['project_id']);
				$row['project_name'] = @$material['project_name'];
				$row['sort'] = 0;
				$row['deleted'] = 0;
			} else {
				$row['code'] = $material_row->name;
				$row['title'] = $material_row->production_name;
				$row['description'] = $material_row->description;
				$row['item_type'] = "mtr";
				$row['quantity'] = intval($material['amount']);
				$row['unit_type'] = $material_row->unit;
				;
				$row['rate'] = doubleval(@$material['price']);
				$row['total'] = $row['quantity'] * $row['rate'];
				$row['mr_id'] = 0;
				$row['created_by'] = $this->login_user->id;
				$row['item_id'] = 0;
				$row['material_id'] = $material['id'];
				$row['supplier_id'] = intval(@$material['supplier_id']);
				$row['supplier_name'] = @$material['supplier_name'];
				if (intval(@$material['project_id']))
					$row['project_id'] = intval(@$material['project_id']);
				if (@$material['project_name'])
					$row['project_name'] = @$material['project_name'];
				$row['deleted'] = 0;
				//$row['quantity'] += intval($material['amount']);
				$row['quantity'] = intval($material['amount']);
				$row['total'] = $row['quantity'] * $row['rate'];
				$row['supplier_name'] = @$material['supplier_name'];
			}
			//$this->dump($row);
			$this->Mr_items_model->save($row, $row['id']);

			/*try{
			$this->Mr_items_model->save($row, $row['id']);
			}catch(Exception $e) {
			echo $e->getMessage();
			}
			$this->dump($row);*/
		}
		redirect("materialrequests/process_pr");
	}

	function create_pr_material_form()
	{
		$options = array('item_type' => 'mtr', 'processing' => true);
		$materials = $this->Mr_items_model->get_details($options)->result();
		echo 'Materials in cart<br />';
		$this->dump($materials);
	}

	function yearly()
	{
		$this->load->view("materialrequests/yearly_pr");
	}

	/* load new order modal */
	function modal_form() // dev2
	{
		if (!$this->check_permission('access_material_request')) {
			redirect("forbidden");
			return;
		}

		validate_submitted_data(
			array(
				"id" => "numeric"
			)
		);

		$view_data = array();
		if (!empty($this->input->post('id'))) {
			// edit-update-view
			$view_data['model_info'] = $this->Materialrequests_model->get_one($this->input->post('id'));
		}

		$view_data['buyers_dropdown'] = $this->_get_buyers_dropdown();
		$view_data['categories'] = $this->Mr_categories_model->get_details(array())->result();

		// var_dump(arr($view_data)); exit();
		$this->load->view('materialrequests/modal_form', $view_data);
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

	private function _get_buyers_dropdown()
	{
		$buyers_dropdown = array("" => "-");
		$buyers = $this->Users_model->get_dropdown_list(array("first_name", "last_name"), "id", array("`status`" => 'active'));
		foreach ($buyers as $key => $value) {
			$buyers_dropdown[$key] = $value;
		}
		return $buyers_dropdown;
	}

	/*function save_order() {
	$this->access_only_allowed_members();
	$mr_id = $this->input->post('mr_id');
	$order_id = $this->input->post('order_client_id', 0);
	$client_id = $this->input->post('client_id');
	$mr_info = $this->Materialrequests_model->get_one($mr_id);
	if(!$mr_info) {
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
	$mr_data = array(
	"id" => $mr_id,
	"order_id" => $order_id
	);
	$this->Materialrequests_model->save($mr_data, $mr_id);
	echo json_encode(array("success" => true, "data" => $this->_row_data($order_id), 'id' => $order_id, 'message' => lang('record_saved')));
	}else{
	echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
	}
	}*/


	/* add, edit or clone an order */

	function save()
	{
		// temporary $this->access_only_allowed_members();
		if ($this->Permission_m->update_material_request != true) {
			echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
			return;
		}

		validate_submitted_data(
			array(
				"id" => "numeric",
				"pr_buyer_id" => "required|numeric",
				"mr_date" => "required",
				"status_id" => "required"
			)
		);

		$buyer_id = $this->input->post('pr_buyer_id');
		$id = intval($this->input->post('id'));

		$catid = $this->input->post('catid');
		$expired_date = $this->input->post('mr_date');
		$sql_date = "
                SELECT 
                    DATE_FORMAT( ADDDATE( '" . $expired_date . "', INTERVAL " . $this->input->post('credit') . " day ), '%Y-%m-%d' ) as t
                FROM
                    materialrequests
                WHERE
                    materialrequests.id = $id;
            ";
		$credit_date = $this->db->query($sql_date)->row();

		if ($credit_date) {
			$sql_inert = "
                    UPDATE `materialrequests` SET `expired` = '" . $credit_date->t . "' WHERE `materialrequests`.`id` = $id; 
                ";
			$credit_insert = $this->db->query($sql_inert);
			if ($credit_insert) {
				$date = "SELECT materialrequests.expired FROM materialrequests WHERE materialrequests.id = $id";
				$c_date = $this->db->query($date)->row();

			}
		}

		$mr_data = array(
			"catid" => $catid,
			"buyer_id" => $buyer_id,
			"project_name" => $this->input->post('project_name'),
			"payment" => $this->input->post('payment'),
			"credit" => $this->input->post('credit'),
			"expired" => $c_date->expired,
			"mr_date" => $this->input->post('mr_date'),
			"tax_id" => $this->input->post('tax_id') ? $this->input->post('tax_id') : 0,
			"tax_id2" => $this->input->post('tax_id2') ? $this->input->post('tax_id2') : 0,
			"note" => $this->input->post('pr_note'),
			"status_id" => $this->input->post('status_id')
		);

		$is_clone = $this->input->post('is_clone');

		//check if the status has been changed,
		//if so, send notification
		$mr_info = $this->Materialrequests_model->get_one($id);
		if ($mr_info->status_id == '1' && $this->input->post('status_id') == '2') {
			//log_notification("new_purchaserequest_created", array("mr_id" => $id));
			//notification and email
			$this->Notifications_model->create_notification("new_materialrequests_created", $this->login_user->id, ['mr_id' => $id]);

			//chat message
			$sql = "SELECT users.id FROM users
            WHERE users.role_id IN (
                SELECT role_permission.role_id
                FROM role_permission
                WHERE role_permission.table_name='materialrequests'
                AND role_permission.prove_row>0
            )";
			$user_ids = $this->db->query($sql)->row_array();
			$user_ids = $user_ids ? $user_ids : [];
			foreach ($user_ids as $to_user_id) {
				$message_data = array(
					"from_user_id" => $this->login_user->id,
					"to_user_id" => $to_user_id,
					"subject" => lang('new_materialrequests_created'),
					"message" => lang('new_materialrequests_created') . ' <a href="' . get_uri("materialrequests/view/" . $id) . '">' . lang('pr_no') . ':' . $id . '</a>',
					"created_at" => get_current_utc_time(),
					"deleted_by_users" => "",
				);
				$message_data = clean_data($message_data);
				$this->Messages_model->save($message_data);
			}
		}

		$main_mr_id = "";
		if ($is_clone && $id) {
			$main_mr_id = $id; //store main pr id to get items later
			$id = ""; //on cloning pr, save as new
		}

		$mr_id = $this->Materialrequests_model->save($mr_data, $id);


		if ($mr_id) {
			//$prove = $this->Provetable_model->getProve($mr_id, 'materialrequests', $this->login_user->id)->row();
			$prove = $this->Provetable_model->get_one_where(['doc_id' => $mr_id, 'tbName' => 'materialrequests']);
			if (!$prove && $mr_data['status_id'] == '3') {
				$prove = [];
				$prove['id'] = 0;
				$prove['doc_id'] = $mr_id;
				$prove['tbName'] = 'materialrequests';
				$prove['user_id'] = $this->login_user->id;
				$prove['doc_date'] = date('Y-m-d H:i:s');
				//$prove['status_id'] = $mr_data['status_id'];
				$this->Provetable_model->save($prove);
			} else {
				//$this->Materialrequests_model->db->where('id', $prove['id']);
				//$prove['status_id'] = $mr_data['status_id'];
				//$this->Materialrequests_model->db->update('materialrequests', $prove);
				//$this->Provetable_model->save($prove, $prove['id']);
			}

			if ($is_clone && $main_mr_id) {
				//add estimate items
				save_custom_fields("materialrequests", $mr_id, 1, "staff"); //we have to keep this regarding as an admin user because non-admin user also can acquire the access to clone a estimate
				$mr_items = $this->Mr_items_model->get_all_where(array("mr_id" => $main_mr_id, "deleted" => 0))->result();

				foreach ($mr_items as $pr_item) {
					//prepare new estimate item data
					$mr_item_data = (array) $pr_item;
					unset($mr_item_data["id"]);
					$mr_item_data['mr_id'] = $mr_id;

					$pr_item = $this->Mr_items_model->save($mr_item_data);
				}
			} else {
				save_custom_fields("materialrequests", $mr_id, $this->login_user->is_admin, $this->login_user->user_type);
			}
			echo json_encode(array("success" => true, "data" => $this->_row_data($mr_id), 'id' => $mr_id, 'message' => lang('record_saved')));
		} else {
			echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
		}
	}

	/* delete or undo an order */

	function delete()
	{
		// temporary $this->access_only_allowed_members();
		if (!$this->cp('materialrequests', 'delete_row')) {
			redirect("forbidden");
		}

		validate_submitted_data(
			array(
				"id" => "required|numeric"
			)
		);

		$id = $this->input->post('id');
		if ($this->input->post('undo')) {
			if ($this->Materialrequests_model->delete($id, true)) {
				echo json_encode(array("success" => true, "data" => $this->_row_data($id), "message" => lang('record_undone')));
			} else {
				echo json_encode(array("success" => false, lang('error_occurred')));
			}
		} else {
			if ($this->Materialrequests_model->delete($id)) {
				echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
			} else {
				echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
			}
		}
	}

	function updatestatus($mr_id)
	{
		// if(!$this->cop('prove_row')) {
		//     //redirect("forbidden");
		//     echo json_encode(array("success" => false, 'message' => 'Cannot access this page.'));
		//     return;
		// }

		//$mr_info = $this->Materialrequests_model->get_one($mr_id);
		$mr_data = ['status_id' => intval($this->input->post('status_id'))];
		$mr_id = $this->Materialrequests_model->save($mr_data, $mr_id);
		echo json_encode(array("success" => true, 'message' => 'Update status success.'));
	}

	function clearAllStatuses()
	{
		$approvals = $this->Provetable_model->getApprovals('materialrequests')->result();
		foreach ($approvals as $apr) {
			$status_id = 1;
			if ($apr->status_id == '1')
				$status_id = 3;
			if ($apr->status_id == '2')
				$status_id = 4;
			$mr_data['status_id'] = $status_id;
			$mr_id = $this->Materialrequests_model->save($mr_data, $apr->doc_id);
		}
	}

	/* load pr details view */
	function view($mr_id = 0, $message = null)
	{
		// Get permissions to access, update and approve the material request
		$view_data["access_material_request"] = $this->check_permission('access_material_request');
		$view_data["update_material_request"] = $this->check_permission('update_material_request');
		$view_data["approve_material_request"] = $this->check_permission('approve_material_request');
		
		// Check permission
		if (!$view_data["access_material_request"]) {
			redirect("forbidden");
		}

		// Check material request id.
		if ($mr_id == 0) {
			$this->load->view("error/html/error_404");
			return;
		}

		// Retrieve the components of the requisition of raw materials. Retrieve the components of the requisition of raw materials.
		$view_data["mr_id"] = $mr_id;
		$view_data["mat_req_info"] = $this->Materialrequests_model->get_materialrequest_by_id($mr_id);
		$view_data["mat_items_info"] = $this->Mr_items_model->get_materialrequest_item_by_id($mr_id);
		
		if (isset($view_data["mat_req_info"]->project_id) && !empty($view_data["mat_req_info"]->project_id)) {
			$view_data["mat_project_info"] = $this->Projects_model->get_project_by_id($view_data["mat_req_info"]->project_id);
		}

		if (isset($view_data["mat_req_info"]->requester_id) && !empty($view_data["mat_req_info"]->requester_id)) {
			$view_data["mat_requester_info"] = $this->Users_m->get_user_by_id($view_data["mat_req_info"]->requester_id);
		}
		
		if (isset($view_data["mat_project_info"]->client_id) && !empty($view_data["mat_project_info"]->client_id)) {
			$view_data["mat_client_info"] = $this->Clients_model->get_client_by_id($view_data["mat_project_info"]->client_id);
		}

		if (isset($view_data["mat_client_info"]->id) && !empty($view_data["mat_client_info"]->id)) {
			$view_data['mat_client_contact'] = $this->Users_m->get_user_by_cli($view_data["mat_client_info"]->id);
		}

		if (isset($view_data["mat_req_info"]->approved_by) && !empty($view_data["mat_req_info"]->approved_by)) {
			$view_data["mat_req_info"]->approved_by_name = $this->Account_category_model->created_by($view_data["mat_req_info"]->approved_by);
		}

		if ($message == "nodata") {
			$view_data["error_message"] = lang('nodata_item_request');
		}

		if ($message == "error") {
			$view_data["error_message"] = lang('not_enough_stock');
		}

		if ($message == "success") {
			$view_data["success_message"] = lang('approved_success');
		}

		if ($message == "reject") {
			$view_data["reject_message"] = lang('rejected_message');
		}

		// var_dump(arr($view_data)); exit();
		$this->template->rander("materialrequests/view", $view_data);
	}

	private function check_access_to_this_mr($mr_data)
	{
		//check for valid order
		if (!$mr_data) {
			show_404();
		}

		//check for security
		$mr_info = get_array_value($mr_data, "mr_info");
		if ($this->login_user->user_type == "client") {
			if ($this->login_user->buyer_id != $mr_info->buyer_id) {
				redirect("forbidden");
			}
		}
	}

	function download_pdf($mr_id = 0, $mode = "download")
	{
		if (!$this->cp('materialrequests', 'view_row')) {
			redirect("forbidden");
			return;
		}
		if ($mr_id) {
			$mr_data = get_mr_making_data($mr_id);

			//arr( $mr_data);

			//exit;
			$this->check_access_to_store();
			$this->check_access_to_this_mr($mr_data);

			if (@ob_get_length())
				@ob_clean();
			//so, we have a valid order data. Prepare the view.
			$mr_data["usgn"] = $this->Db_model->signature_approve($mr_id, "materialrequests");
			prepare_mr_pdf($mr_data, $mode);
		} else {
			show_404();
		}
	}

	//view html is accessable to client only.
	function preview($mr_id = 0, $show_close_preview = false)
	{
		// temporary $this->check_access_to_store();
		if (!$this->cp('materialrequests', 'view_row')) {
			redirect("forbidden");
			return;
		}
		if ($mr_id) {
			$mr_data = get_mr_making_data($mr_id);
			//var_dump($mr_data);exit;
			$this->check_access_to_this_mr($mr_data);

			$mr_data['mr_info'] = get_array_value($mr_data, "mr_info");

			$mr_data["usgn"] = $this->Db_model->signature_approve($mr_id, "materialrequests");

			$view_data['mr_preview'] = prepare_mr_pdf($mr_data, "html");

			//var_dump($view_data);exit;

			//show a back button
			$view_data['show_close_preview'] = $show_close_preview && $this->login_user->user_type === "staff" ? true : false;

			$view_data['mr_id'] = $mr_id;

			$this->template->rander("materialrequests/pr_preview", $view_data);
		} else {
			show_404();
		}
	}


	function download_po_pdf($mr_id = 0, $mode = "download")
	{
		if (!$this->cp('materialrequests', 'view_row')) {
			redirect("forbidden");
			return;
		}
		if ($mr_id) {
			$mr_data = get_po_making_data($mr_id);

			$this->check_access_to_store();
			$this->check_access_to_this_mr($mr_data);

			if (@ob_get_length())
				@ob_clean();
			//so, we have a valid order data. Prepare the view.
			$mr_data["usgn"] = $this->Db_model->signature_approve($mr_id, "materialrequests");
			prepare_po_pdf($mr_data, $mode);
		} else {
			show_404();
		}
	}

	function preview_po($mr_id = 0, $show_close_preview = false, $po_no = NULL)
	{
		// temporary $this->check_access_to_store();
		// echo $po_no;exit;
		if (!$this->cp('materialrequests', 'view_row')) {
			redirect("forbidden");
			return;
		}
		if ($mr_id) {
			$mr_data = get_po_making_data($mr_id, $po_no);
			//arr($mr_data);exit;
			$this->check_access_to_this_mr($mr_data);

			$mr_data['mr_info'] = get_array_value($mr_data, "mr_info");

			$mr_data["usgn"] = $this->Db_model->signature_approve($mr_id, "materialrequests");

			$view_data['pr_preview'] = prepare_po_pdf($mr_data, "html");

			//show a back button
			$view_data['show_close_preview'] = $show_close_preview && $this->login_user->user_type === "staff" ? true : false;

			$view_data['mr_id'] = $mr_id;

			$this->template->rander("materialrequests/po_preview", $view_data);
		} else {
			show_404();
		}
	}

	/* prepare suggestion of order item */

	function get_pr_item_suggestion()
	{
		$key = @$_REQUEST["q"];
		$suggestion = array();

		$items = $this->Invoice_items_model->get_item_suggestion($key);

		foreach ($items as $item) {
			$suggestion[] = array("id" => $item->title, "text" => $item->title);
		}

		//$suggestion[] = array("id" => "+", "text" => "+ " . lang("create_new_item"));

		echo json_encode($suggestion);
	}

	function get_pr_supplier_suggestion()
	{
		$suggestion = [];
		$req = $this->input->get();
		$q = @$req['q'];
		$model_info = $this->Mr_items_model->get_one(@$req['id']);
		$material_id = intval(@$req['material_id']);
		$this->check_access_to_this_mr_item($model_info);
		$items = $this->Mr_items_model->get_supplier_suggestion($material_id, $q);

		foreach ($items as $item) {
			$suggestion[] = array(
				"id" => $item->supplier_name,
				"text" => $item->supplier_name . (@$item->price ? "(" . $item->price . $item->currency_symbol . ")" : ""),
				"supplier_id" => $item->id,
				//supplier_id
				"supplier_name" => $item->supplier_name,
				"price" => intval(@$item->price),
				"currency" => $item->currency ? $item->currency : 'THB',
				"currency_symbol" => $item->currency_symbol ? $item->currency_symbol : '฿',
				"address" => $item->address,
				"city" => $item->city,
				"state" => $item->state,
				"zip" => $item->zip,
				"country" => $item->country,
				"website" => $item->website,
				"phone" => $item->phone,
				"vat_number" => $item->vat_number
			);
		}

		$suggestion[] = array("id" => "+", "text" => "+ " . lang("create_new_supplier"), 'currency' => 'THB', 'currency_symbol' => '฿');

		echo json_encode($suggestion);
	}

	function get_pr_item_info_suggestion()
	{
		$item = $this->Invoice_items_model->get_item_info_suggestion($this->input->post("item_name"));
		if ($item) {
			echo json_encode(array("success" => true, "item_info" => $item));
		} else {
			echo json_encode(array("success" => false));
		}
	}

	function get_pr_supplier_info_suggestion()
	{
		$item = $this->Mr_items_model->get_supplier_info_suggestion($this->input->post("supplier_id"), intval($this->input->post("material_id")));
		if ($item) {
			$item->currency = $item->currency ? $item->currency : 'THB';
			$item->currency_symbol = $item->currency_symbol ? $item->currency_symbol : '฿';
			echo json_encode(array("success" => true, "supplier_info" => $item));
		} else {
			echo json_encode(array("success" => false));
		}
	}

	function get_materials_suggestion()
	{
		$key = $this->input->get("q");
		$suggestion = array();

		$items = $this->Bom_materials_model->get_materials_request_suggestion($key);

		foreach ($items as $item) {
			$suggestion[] = array("id" => $item->id, "text" => $item->text, "remaining" => $item->remaining);
		}
		echo json_encode($suggestion);
	}

	function get_material_info_suggestion()
	{
		$item = $this->Bom_materials_model->get_material_request_info_suggestion($this->input->post("matrial_id"));
		if ($item) {
			echo json_encode(array("success" => true, "item_info" => $item));
		} else {
			echo json_encode(array("success" => false));
		}
	}

	function get_item_suggestion()
	{
		$key = $this->input->get("q");
		$suggestion = array();

		$items = $this->Items_model->get_item_request_suggestion($key);

		foreach ($items as $item) {
			$suggestion[] = array("id" => $item->id, "text" => $item->text, "remaining" => $item->remaining);
		}
		echo json_encode($suggestion);
	}

	function get_item_info_suggestion()
	{
		$item = $this->Items_model->get_item_info_suggestion($this->input->post("matrial_id"));
		if ($item) {
			echo json_encode(array("success" => true, "item_info" => $item));
		} else {
			echo json_encode(array("success" => false));
		}
	}

	function save_pr_status($id = 0)
	{
		// temporary $this->access_only_allowed_members();
		if (!$id) {
			show_404();
		}

		if (!$this->cp('materialrequests', 'view_row') || !($this->cp('materialrequests', 'add_row') || $this->cp('materialrequests', 'edit_row'))) {
			redirect("forbidden");
			return;
		}

		$data = array(
			"status_id" => $this->input->post('value')
		);

		$save_id = $this->Materialrequests_model->save($data, $id);

		if ($save_id) {
			log_notification("pr_status_updated", array("mr_id" => $id));
			$mr_info = $this->Materialrequests_model->get_details(array("id" => $id))->row();
			echo json_encode(array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, "message" => lang('record_saved'), "pr_status_color" => $mr_info->pr_status_color));
		} else {
			echo json_encode(array("success" => false, lang('error_occurred')));
		}
	}

	/* return a row of order list table */

	private function _row_data($id)
	{
		$custom_fields = $this->Custom_fields_model->get_available_fields_for_table("materialrequests", $this->login_user->is_admin, $this->login_user->user_type);

		$options = array("id" => $id, "custom_fields" => $custom_fields);
		$data = $this->MaterialRequests_model->get_details($options)->row();
		return $this->_make_row($data, $custom_fields);
	}

	/* load discount modal */

	function discount_modal_form()
	{
		// temporary $this->access_only_allowed_members();

		validate_submitted_data(
			array(
				"mr_id" => "required|numeric"
			)
		);

		$mr_id = $this->input->post('mr_id');

		$view_data['model_info'] = $this->Materialrequests_model->get_one($mr_id);

		$this->load->view('materialrequests/discount_modal_form', $view_data);
	}

	/* save discount */

	function save_discount()
	{
		// temporary $this->access_only_allowed_members();
		if (!$this->cp('materialrequests', 'view_row') || !($this->cp('materialrequests', 'add_row') || $this->cp('materialrequests', 'edit_row'))) {
			redirect("forbidden");
			return;
		}
		validate_submitted_data(
			array(
				"mr_id" => "required|numeric",
				"discount_type" => "required",
				"discount_amount" => "numeric",
				"discount_amount_type" => "required"
			)
		);

		$mr_id = $this->input->post('mr_id');

		$data = array(
			"discount_type" => $this->input->post('discount_type'),
			"discount_amount" => $this->input->post('discount_amount'),
			"discount_amount_type" => $this->input->post('discount_amount_type')
		);

		$data = clean_data($data);

		$save_data = $this->Materialrequests_model->save($data, $mr_id);
		if ($save_data) {
			echo json_encode(array("success" => true, "pr_total_view" => $this->_get_mr_total_view($mr_id), 'message' => lang('record_saved'), "mr_id" => $mr_id));
		} else {
			echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
		}
	}

	/* list of order items, prepared for datatable  */

	function item_list_data($mr_id = 0)
	{
		if ($this->Permission_m->access_material_request != true)
			redirect("forbidden");

		$mr_info = $mr_id ? $this->Materialrequests_model->get_details(array("id" => $mr_id))->row() : null;
		$prove = $this->Provetable_model->getProve($mr_id, 'materialrequests')->row();
		$list_data = $this->Mr_items_model->get_details(array("mr_id" => $mr_id, 'item_type' => 'all', 'mrAllow' => 0))->result();
		//var_dump($list_data);exit;
		$result = array();
		foreach ($list_data as $data) {
			$result[] = $this->_make_item_row($mr_info, $prove, $data);
		}

		//var_dump($result);exit;
		echo json_encode(array("data" => $result));
	}

	/* list of order of a specific client, prepared for datatable  */

	function pr_list_data_of_client($buyer_id)
	{
		// temporary $this->check_access_to_store();

		$custom_fields = $this->Custom_fields_model->get_available_fields_for_table("materialrequests", $this->login_user->is_admin, $this->login_user->user_type);

		$options = array("buyer_id" => $buyer_id, "custom_fields" => $custom_fields);

		$list_data = $this->Materialrequests_model->get_details($options)->result();

		$result = array();
		foreach ($list_data as $data) {
			$result[] = $this->_make_row($data, $custom_fields);
		}
		echo json_encode(array("data" => $result));
	}


	function index()
	{
		// verified auth to access
		if (!$this->check_permission('access_material_request')) {
			redirect("forbidden");
			return;
		}

		// load custom field
		$view_data["custom_field_headers"] = $this->Custom_fields_model->get_custom_field_headers_for_table(
			"materialrequests",
			$this->login_user->is_admin,
			$this->login_user->user_type
		);

		// load auth to create / update
		$view_data["create_material_request"] = $this->check_permission('create_material_request');
		$view_data["update_material_request"] = $this->check_permission('update_material_request');

		// add related btn
		$buttonTops = array();
		
		if ($view_data["update_material_request"]) {
			$buttonTops[] = js_anchor(
				'<i class="fa fa-bars"></i> ' . lang('category_management'),
				array(
					'class' => 'btn btn-primary',
					'title' => lang('category_management'),
					'id' => 'cat-mng-btn'
				)
			);
		}

		if ($this->check_permission('access_material_request')) {
			$buttonTops[] = js_anchor(
				'<i class="fa fa-chevron-left"></i> ' . lang('back_to_stock'),
				array(
					'class' => 'btn btn-primary',
					'title' => lang('stock'),
					'id' => 'back-to-stock'
				)
			);
		}

		if ($view_data["create_material_request"]) {
			$buttonTops[] = modal_anchor(
				get_uri('materialrequests/modal_form'),
				'<i class="fa fa-plus-circle"></i> ' . lang('add_materialrequests'),
				array(
					'id' => 'add-mr-btn-new',
					'class' => 'btn btn-default',
					'title' => lang('add_materialrequests')
				)
			);
		}

		$view_data['buttonTops'] = implode('', $buttonTops);
		// var_dump(arr($view_data)); exit();

		$this->template->rander("materialrequests/index", $view_data);
	}

	private function _make_row($data, $custom_fields)
	{
		// arr($data);
		$mr_url = "";
		if ($this->login_user->user_type == "staff") {
			$mr_url = anchor(get_uri("materialrequests/view/" . $data->id), $data->doc_no ? $data->doc_no : lang('no_have_doc_no') . ':' . $data->id);
		} else {
			// for client
			$mr_url = anchor(get_uri("materialrequests/preview/" . $data->id), $data->doc_no ? $data->doc_no : lang('no_have_doc_no') . ':' . $data->id);
		}

		$client = $data->buyer_name;

		$row_data = array(
			$mr_url,
			$data->category_name ? $data->category_name : lang('undefined_category'),
			$data->project_name ? $data->project_name : "<span style=\"color:red\">" . lang('undefined_project') . "</span>",
			$client,
			$data->mr_date
		);

		$pr_status_color = "#efc050";
		$pr_status_title = lang("status_waiting_for_approve");

		if ($data->status_id == 3) {
			$pr_status_color = "#009b77";
			$pr_status_title = lang("status_already_approved");
		}

		if ($data->status_id == 4) {
			$pr_status_color = "#ff1a1a";
			$pr_status_title = lang("status_already_rejected");
		}


		$row_data[] = "<span style='background-color: $pr_status_color;' class='label'>$pr_status_title</span>";
		foreach ($custom_fields as $field) {
			$cf_id = "cfv_" . $field->id;
			$row_data[] = $this->load->view("custom_fields/output_" . $field->field_type, array("value" => $data->$cf_id), true);
		}

		if ($data->status_id == "3" || $data->status_id == "4")
			$row_data[] = "";
		else {
			$buttons = "";

			if ($this->login_user->is_admin == 1) {
				$buttons = "<a href='#' data-action-url='" . get_uri("materialrequests/modal_form") . "' data-post-id='" . $data->id . "' title='" . lang('edit_purchaserequest') . "' class='edit' data-act='ajax-modal'><i class='fa fa-pencil'></i></a>";
				$buttons .= "<a href='" . get_uri("materialrequests/process_pr/" . $data->id) . "' data-post-id='" . $data->id . "' title='" . lang('edit_mr_items') . "' class='edit'><i class='fa fa-bars'></i></a>";
				$buttons .= "<a href='#' data-action-url='" . get_uri("materialrequests/delete") . "' class='delete' data-action='delete' data-id='" . $data->id . "' title='" . lang('delete_order') . "'><i class='fa fa-times fa-fw'></i></a>";
			}

			$row_data[] = $buttons;
		}

		return $row_data;
	}

	function categories()
	{
		$buttonTops = array();
		if (!empty($this->getRolePermission["add_row"])) {
			$buttonTops[] = js_anchor("<i class='fa fa-bars'></i> " . lang("back_to_materialrequest"), array("data-action-url" => get_uri("materialrequests"), "class" => "btn btn-default", "title" => lang("back_to_materialrequest"), "id" => "back-to-pr-btn"));
			$buttonTops[] = js_anchor("<i class='fa fa-plus-circle'></i> " . lang("add_category"), array("data-action-url" => get_uri("materialrequests/category_form"), "class" => "btn btn-primary", "title" => lang("add_category"), "id" => "add-cat-btn"));
		}

		$view_data["buttonTops"] = implode('', $buttonTops);
		$view_data["view_row"] = $this->cp("materialrequests", "view_row");
		$view_data["add_row"] = $this->cp("materialrequests", "add_row");
		$view_data["edit_row"] = $this->cp("materialrequests", "edit_row");
		$view_data["delete_row"] = $this->cp("materialrequests", "delete_row");
		$view_data["prove_row"] = $this->cp("materialrequests", "prove_row");

		$this->template->rander("materialrequests/category/index", $view_data);
	}

	function list_categories_data()
	{
		if (!$this->cp("materialrequests", "view_row")) {
			redirect("forbidden");
			return;
		}

		$options = array(
			"count_pr" => true
		);

		$list_data = $this->Pr_categories_model->get_details($options)->result();

		$result = array();
		foreach ($list_data as $data) {
			$result[] = $this->_make_category_row($data);
		}

		echo json_encode(array("data" => $result));
	}

	private function _make_category_row($data)
	{
		$cat_url = modal_anchor(get_uri("materialrequests/category_form/" . $data->id), $data->title, array("class" => "edit", "title" => lang("edit_category"), "data-post-id" => $data->id, "data-act" => "ajax-modal"));

		$row_data = array(
			"id" => $data->id,
			"title" => $data->id != 1 ? $cat_url : "<a class='edit'>" . $data->title . "</>",
			"desc" => $data->description,
			"set_date" => substr($data->created_date, 0, 10),
			"created_date" => format_to_date($data->created_date, false),
			"creator" => $data->creator_name
		);

		$edit_row = $this->cp("materialrequests", "edit_row");
		$delete_row = $this->cp("materialrequests", "delete_row");

		$can_action = ($edit_row || $delete_row);
		// If admin return TRUE, but not will checking user auth the return.
		if (!$can_action || $data->id == 1) {
			$row_data["action"] = "";
		} else {
			$row_data["action"] = ($edit_row ? modal_anchor(get_uri("materialrequests/category_form/" . $data->id), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang("edit_category"), "data-post-id" => $data->id, "data-act" => "ajax-modal")) : '')
				. ($delete_row && $data->count_pr == "0" ? js_anchor("<i class='fa fa-times fa-fw'></i>", array("title" => lang("delete_category"), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("materialrequests/category_delete"), "data-action" => "delete-confirmation")) : '');
		}

		return $row_data;
	}

	function category_form($id = 0)
	{
		$id = intval($id);
		$is_new = !$id;
		$view_row = $this->cp('materialrequests', 'view_row');
		$add_row = $this->cp('materialrequests', 'add_row');
		$edit_row = $this->cp('materialrequests', 'edit_row');
		$delete_row = $this->cp('materialrequests', 'delete_row');
		//$prove_row = $this->cp('materialrequests','prove_row');

		if (!$view_row || ($is_new && !$add_row) || (!$is_new && !$edit_row)) {
			redirect('forbidden');
			return;
		}
		$view_data = [];
		$view_data['model_info'] = $this->Pr_categories_model->get_one($id);
		$this->load->view("materialrequests/category/form", $view_data);
		//$this->template->rander("materialrequests/category/form", $view_data );
	}

	public function save_category()
	{
		validate_submitted_data(
			array(
				"id" => "numeric"
			)
		);

		$id = $this->input->post('id', 0);

		if (!$id && !$this->cp('materialrequests', 'add_row')) {
			redirect("forbidden");
			return;
		}
		if ($id && !$this->cp('materialrequests', 'edit_row')) {
			redirect("forbidden");
			return;
		}

		$title = $this->input->post('title');
		$description = $this->input->post('description');
		//$cat_info = $this->Pr_categories_model->get_one($id);

		$quantity = unformat_currency($this->input->post('mr_item_quantity'));

		$cat_data = [
			'title' => $title,
			'description' => $description
		];
		if (!$id) {
			$cat_data['created_date'] = date('Y-m-d H:i:s');
			$cat_data['created_by'] = $this->login_user->id;
		}
		$catid = $this->Pr_categories_model->save($cat_data, $id);
		if ($catid) {
			//$cat_info = $this->Pr_categories_model->get_one($catid);
			$cat_info = $this->Pr_categories_model->get_details(['id' => $catid, "count_pr" => true])->row();
			echo json_encode(array("success" => true, "data" => $this->_make_category_row($cat_info), 'id' => $cat_info->id, 'message' => lang('record_saved')));
		} else {
			echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
		}
	}

	function list_lacked_project_materials()
	{
		$sql = "SELECT bpim.*,p.title as projecttitle,bm.name as material_name, bm.unit `material_unit`,bpi.project_id,prj.title as project_name,
		IF(bmp.supplier_id IS NULL, 0, bmp.supplier_id) as supplier_id,
		IF(bmp.price IS NULL, 0, bmp.price) as price,
		IF(bs.company_name IS NULL, '', bs.company_name) as supplier_name,bs.currency,bs.currency_symbol
		FROM bom_project_item_materials as bpim
		INNER JOIN bom_project_items bpi ON bpi.id = bpim.project_item_id
		INNER JOIN projects as prj ON prj.id = bpi.project_id
		LEFT JOIN projects as p ON bpi.project_id=p.id
		LEFT JOIN bom_materials as bm ON bpim.material_id=bm.id
		LEFT JOIN (SELECT material_id,supplier_id,price/ratio as price FROM bom_material_pricings ORDER BY price ASC LIMIT 0,1) as bmp
			ON bmp.material_id=bpim.material_id
		LEFT JOIN bom_suppliers as bs ON bmp.supplier_id=bs.id
		WHERE bpim.ratio<0
		";
		$rows = $this->db->query($sql)->result();
		$projects = [];
		foreach ($rows as $row) {
			$row->ratio = abs($row->ratio);
			$span = '<div class="project' . $row->project_item_id . '_lacked_material lacked_material prj_' . $row->project_item_id . '" data-project-id="' . $row->project_id . '" data-project-name="' . $row->project_name . '" data-material-id="' . $row->material_id . '" data-lacked-amount="' . $row->ratio . '" data-unit="' . $row->material_unit . '" data-supplier-name="' . $row->supplier_name . '" data-supplier-id="' . @$row->supplier_id . '" data-price="' . $row->price . '" data-currency="' . $row->currency . '" data-currency_symbol="' . $row->currency_symbol . '" style="display:none;">' . $row->material_name . ' ' . $row->ratio . $row->material_unit . '</div>';
			$button = '<button type="button" class="btn btn-danger pull-right btn-pr" id="btn-pr1" onclick="purchaseRequest(\'#btn-pr1\',\'project' . $row->project_item_id . '_\')"><i class="fa fa-shopping-cart"></i> ' . lang('request_purchasing_materials') . '</button>';
			if (!isset($projects[$row->project_item_id])) {
				$project = [];
				$project[] = $row->project_item_id;
				$project[] = '<a href="javascript:;" onclick="javascript:jQuery(\'.prj_' . $row->project_item_id . '\').toggle();">' . $row->projecttitle . '</a>' . $span;
				$project[] = 1;
				$project[] = $button;
				$projects[$row->project_item_id] = $project;
			} else {
				$project = $projects[$row->project_item_id];
				$project[1] = $project[1] . $span;
				$project[2]++;
				$project[3] = $button;
				$projects[$row->project_item_id] = $project;
			}
		}

		echo json_encode(array("data" => array_values($projects), "success" => 1, "message" => "Success"));
	}

	function list_lacked_stock_materials()
	{
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
		foreach ($rows as $row) {
			$row->ratio = abs($row->noti_threshold - $row->remaining);
			$span = '<div class="stock' . $row->group_id . '_lacked_material lacked_material" data-material-id="' . $row->material_id . '" data-lacked-amount="' . $row->ratio . '" data-unit="' . $row->material_unit . '" data-supplier-name="' . $row->supplier_name . '" data-supplier-id="' . $row->supplier_id . '" data-price="' . $row->price . '" data-currency="' . $row->currency . '" data-currency_symbol="' . $row->currency_symbol . '" style="display:none;">' . $row->material_name . ' ' . $row->ratio . $row->material_unit . '</div>';
			$button = '<button type="button" class="btn btn-warning pull-right btn-pr" id="btn-pr1" onclick="purchaseRequest(\'#btn-pr1\',\'stock' . $row->group_id . '_\')"><i class="fa fa-shopping-cart"></i> ' . lang('request_low_materials') . '</button>';
			if (!isset($imports[$row->group_id])) {
				$import = [];
				$import[] = $row->group_id;
				$import[] = '<a href="javascript:;" onclick="javascript:jQuery(\'.stock' . $row->group_id . '_lacked_material\').toggle();">' . $row->importname . '</a>' . $span;
				$import[] = 1;
				$import[] = $button;
				$imports[$row->group_id] = $import;
			} else {
				$import = $imports[$row->group_id];
				$import[1] = $import[1] . $span;
				$import[2]++;
				$import[3] = $button;
				$imports[$row->group_id] = $import;
			}
		}

		echo json_encode(array("data" => array_values($imports), "success" => 1, "message" => "Success"));
	}



	function PO()
	{
		// temporary $this->check_access_to_store();
		//$this->dump([$this->cop('prove_row'), $this->cp('materialrequests', 'prove_row')], true);
		if (!$this->cp('materialrequests', 'view_row')) {
			redirect("forbidden");
			return;
		}
		$view_data["custom_field_headers"] = $this->Custom_fields_model->get_custom_field_headers_for_table("materialrequests", $this->login_user->is_admin, $this->login_user->user_type);

		$buttonTops = array();

		//if( !empty( $this->getRolePermission['add_row'] ) ) {
		//$buttonTops[] = anchor( "<i class='fa fa-bars'></i> ".lang('category_manager'), array("class" => "btn btn-primary", "title" => lang('category_manager'), "id" => "cat-mng-btn"));
		$buttonTops[] = anchor(get_uri("materialrequests/categories"), "<i class='fa fa-bars'></i> " . lang('category_manager'), array("class" => "btn btn-primary", "title" => lang('category_manager'), "id" => "cat-mng-btn"));
		$buttonTops[] = js_anchor("<i class='fa fa-shopping-cart'></i> " . lang('add_pr'), array("class" => "btn btn-primary", "title" => lang('add_pr'), "id" => "add-pr-btn"));
		//$buttonTops[] = js_anchor("<i class='fa fa-plus-circle'></i> " . lang('add_pr'), array("class" => "btn btn-default", "id" => "add-pr-btn"));
		//$buttonTops[] = modal_anchor(get_uri("materialrequests/item_modal_form"), "<i class='fa fa-plus-circle'></i>" . lang('add_more_items'), array("class" => "btn btn-default pull-right", "title" => lang('add_more_items'), "data-post-id" => 0, "data-post-mr_id" => 0,'data-post-item_type'=>'oth'));
		//$buttonTops[] = anchor(get_uri("mr_items/grid_view"), "<i class='fa fa-plus-circle'></i> " . lang('add_internal_items'), array("class" => "btn btn-default pull-right"));
		//$buttonTops[] = modal_anchor(get_uri("materialrequests/item_modal_form"), "<i class='fa fa-plus-circle'></i>" . lang('add_materials'), array("class" => "btn btn-default pull-right", "title" => lang('add_materials'), "data-post-id" => 0, "data-post-mr_id" => 0,'data-post-item_type'=>'mtr'));
		//}

		$view_data['buttonTops'] = implode('', $buttonTops);

		$options = [];
		if (!$this->cp('materialrequests', 'prove_row')) {
			$options['where'] = " pr_status.id!='3' AND pr_status.id!='4' ";
		}
		$view_data['pr_statuses'] = $this->Mr_status_model->get_details($options)->result();

		$view_data['pr_suppliers'] = $this->Bom_suppliers_model->get_options()->result();

		$view_data['view_row'] = $this->cp('materialrequests', 'view_row');
		$view_data['add_row'] = $this->cp('materialrequests', 'add_row');
		$view_data['edit_row'] = $this->cp('materialrequests', 'edit_row');
		$view_data['delete_row'] = $this->cp('materialrequests', 'delete_row');
		$view_data['prove_row'] = $this->cp('materialrequests', 'prove_row');
		$this->template->rander("materialrequests/POview", $view_data);
	}

	// function approve_unused($id)
	// {
	// 	$pr = $this->Materialrequests_model->get_one($id);
	// 	if ($pr) {
	// 		$pr->status_id = 3;
	// 		$pr->save($pr, $pr->id);
	// 		$prove = $this->Provetable_model->get_where(['doc_id' => $id, 'tbName' => 'materialrequests']);
	// 		if (!$prove) {
	// 			$prove = [];
	// 			$prove['id'] = 0;
	// 			$prove['doc_id'] = $mr_id;
	// 			$prove['tbName'] = 'materialrequests';
	// 			$prove['user_id'] = $this->login_user->id;
	// 			$prove['doc_date'] = date('Y-m-d H:i:s');
	// 			$prove['status_id'] = $mr_data['status_id'];
	// 			$this->Provetable_model->save($prove);
	// 		}
	// 	}
	// 	redirect('materialrequests/view/' . $id);
	// }

	function PO_data()
	{
		// temporary $this->access_only_allowed_members();
		if (!$this->cp('materialrequests', 'view_row')) {
			redirect("forbidden");
			return;
		}
		$custom_fields = $this->Custom_fields_model->get_available_fields_for_table("materialrequests", $this->login_user->is_admin, $this->login_user->user_type);

		$options = array(
			"status_id" => $this->input->post("status_id"),
			"supplier_id" => $this->input->post("supplier_id"),
			"mr_date" => $this->input->post("start_date"),
			"deadline" => $this->input->post("end_date"),
			"custom_fields" => $custom_fields
		);
		//$this->dump($options);
		$options['view_row'] = $this->cop('view_row');
		$list_data = $this->Materialrequests_model->get_PO_details($options)->result();
		$result = array();
		foreach ($list_data as $data) {
			$result[] = $this->_make_PO_row($data, $custom_fields);
		}

		echo json_encode(array("data" => $result));
	}

	private function _make_PO_row($data, $custom_fields)
	{
		$pr_url = "";

		if ($this->login_user->user_type == "staff") {
			$pr_url = anchor(get_uri("materialrequests/preview_po/" . $data->id . "/1/" . $data->doc_no), $data->doc_no ? $data->doc_no : lang('no_have_doc_no') . ':' . $data->id);
		} else {
			//for client
			$pr_url = anchor(get_uri("materialrequests/preview_po/" . $data->id . "/1/" . $data->doc_no), $data->doc_no ? $data->doc_no : lang('no_have_doc_no') . ':' . $data->id);
		}
		// arr($data);exit;
		$mr_id_link = "";
		if ($this->login_user->user_type == "staff") {
			$mr_id_link = anchor(get_uri('materialrequests/view/' . $data->id), $data->prID ? $data->prID : lang('no_have_doc_no') . ':' . $data->id);
		} else {
			//for client
			$mr_id_link = anchor(get_uri("materialrequests/preview/" . $data->id), $data->prID ? $data->prID : lang('no_have_doc_no') . ':' . $data->id);
		}
		//$client = anchor(get_uri("clients/view/" . $data->buyer_id), $data->buyer_name);
		$client = $data->buyer_name;

		$row_data = array(
			$mr_id_link,
			$pr_url,
			$data->category_name ? $data->category_name : lang('undefined_category'),
			$data->project_name ? $data->project_name : "<span style=\"color:red\">" . lang('undefined_project') . "</span>",
			$client,
			$data->mr_date,
			format_to_date($data->mr_date, false),
			to_currency($data->pr_value, $data->currency_symbol)
		);

		if ($this->login_user->user_type == "staff") {
			$row_data[] = js_anchor($data->pr_status_title, array("style" => "background-color: $data->pr_status_color", "class" => "label", "data-id" => $data->id, "data-value" => $data->status_id, "data-act" => "update-order-status"));
		} else {
			$row_data[] = "<span style='background-color: $data->pr_status_color;' class='label'>$data->pr_status_title</span>";
		}

		foreach ($custom_fields as $field) {
			$cf_id = "cfv_" . $field->id;
			$row_data[] = $this->load->view("custom_fields/output_" . $field->field_type, array("value" => $data->$cf_id), true);
		}

		//$view_row = $this->cp('materialrequests','view_row');
		//$add_row = $this->cp('materialrequests','add_row');
		$edit_row = $this->cp('materialrequests', 'edit_row');
		$delete_row = $this->cp('materialrequests', 'delete_row');
		//$prove_row = $this->cp('materialrequests','prove_row');

		if ($data->status_id == "3")
			$row_data[] = "";
		else {
			$row_data[] = ($edit_row ? modal_anchor(get_uri("materialrequests/modal_form"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_purchaserequest'), "data-post-id" => $data->id))
				. anchor(get_uri("materialrequests/process_pr/" . $data->id), "<i class='fa fa-bars'></i>", array("class" => "edit", "title" => lang('edit_mr_items'), "data-post-id" => $data->id)) : '')
				. ($delete_row ? js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete_order'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("materialrequests/delete"), "data-action" => "delete")) : '')
			;
		}
		return $row_data;
	}

	function list_data()
	{
		if ($this->Permission_m->access_material_request != true) {
			redirect("forbidden");
			return;
		}

		$custom_fields = $this->Custom_fields_model->get_available_fields_for_table("materialrequests", $this->login_user->is_admin, $this->login_user->user_type);

		$options = array(
			"status_id" => $this->input->post("status_id"),
			"supplier_id" => $this->input->post("supplier_id"),
			"mr_date" => $this->input->post("start_date"),
			"deadline" => $this->input->post("end_date"),
			"custom_fields" => $custom_fields
		);

		$options['view_row'] = $this->cop('view_row');
		$list_data = $this->Materialrequests_model->get_details($options)->result();

		$result = array();
		foreach ($list_data as $data) {
			$result[] = $this->_make_row($data, $custom_fields);
		}
		// var_dump(arr($result)); exit;

		echo json_encode(array("data" => $result));
	}

	function add_pr_item_to_cart()
	{
		if (!$this->cop('view_row') || !($this->cop('add_row') || $this->cop('edit_row'))) {
			redirect("forbidden");
		}
		$data = $this->input->post();
		//var_dump($data);exit;
		if (!isset($data['item']))
			$data['item'] = array();
		//$this->dump($data['materials'], true);
		//$this->dump($data['materials']);
		//echo "<br />------------<br />";
		foreach ($data['item'] as $item) {
			//$row = $this->Mr_items_model->get_one_material_in_cart($material['id'], intval(@$material['supplier_id']), $this->login_user->id);
			$row = $this->Mr_items_model->get_one_item_in_cart($item['id'], $this->login_user->id);
			$item_row = $this->Mr_items_model->get_one_item($item['id']);
			if (!$row || ($row['deleted'] == '1')) {
				if (!$row) {
					$row = [];
					$row['id'] = 0;
				}
				$row['code'] = $item_row->title;
				$row['title'] = $item_row->description;
				$row['description'] = $item_row->description;
				$row['item_type'] = "itm";
				$row['quantity'] = intval($item['amount']);
				$row['unit_type'] = $item_row->unit_type;
				$row['rate'] = doubleval(@$item['price']);
				$row['total'] = $row['quantity'] * $row['rate'];
				$row['mr_id'] = 0;
				$row['created_by'] = $this->login_user->id;
				$row['material_id'] = 0;
				$row['item_id'] = $item['id'];
				$row['supplier_id'] = intval(@$item['supplier_id']);
				$row['supplier_name'] = @$item['supplier_name'];
				$row['project_id'] = intval(@$item['project_id']);
				$row['project_name'] = @$item['project_name'];
				$row['sort'] = 0;
				$row['deleted'] = 0;
			} else {
				$row['code'] = $item_row->title;
				$row['title'] = $item_row->description;
				$row['description'] = $item_row->description;
				$row['item_type'] = "itm";
				$row['quantity'] = intval($item['amount']);
				$row['unit_type'] = $item_row->unit_type;
				$row['rate'] = doubleval(@$item['price']);
				$row['total'] = $row['quantity'] * $row['rate'];
				$row['mr_id'] = 0;
				$row['created_by'] = $this->login_user->id;
				$row['material_id'] = 0;
				$row['item_id'] = $item['id'];
				$row['supplier_id'] = intval(@$item['supplier_id']);
				$row['supplier_name'] = @$item['supplier_name'];
				if (intval(@$item['project_id']))
					$row['project_id'] = intval(@$item['project_id']);
				if (@$item['project_name'])
					$row['project_name'] = @$item['project_name'];
				$row['deleted'] = 0;
				//$row['quantity'] += intval($material['amount']);
				$row['quantity'] = intval($item['amount']);
				$row['total'] = $row['quantity'] * $row['rate'];
				$row['supplier_name'] = @$item['supplier_name'];
			}
			//var_dump($row);exit;
			//$this->dump($row);
			$this->Mr_items_model->save($row, $row['id']);

			/*try{
			$this->Mr_items_model->save($row, $row['id']);
			}catch(Exception $e) {
			echo $e->getMessage();
			}
			$this->dump($row);*/
			//var_dump($row);
		}
		redirect("materialrequests/process_pr");
	}

	function list_lacked_stock_item()
	{
		$sql = "SELECT bs.*, bm.title `item_name`, bm.unit_type `item_unit`, bm.noti_threshold, bsg.name AS importname, 
		IF(bmp.supplier_id IS NULL, 0, bmp.supplier_id) AS supplier_id, 
		IF(bmp.price IS NULL, 0, bmp.price) as price, 
		IF(bsp.company_name IS NULL, '', bsp.company_name) AS supplier_name,bsp.currency,bsp.currency_symbol 
		FROM bom_item_stocks bs 
		INNER JOIN items bm ON bm.id = bs.item_id 
		INNER JOIN bom_item_groups bsg ON bsg.id = bs.group_id 
		LEFT JOIN (SELECT item_id,supplier_id,price/ratio AS price FROM bom_item_pricings ORDER BY price ASC LIMIT 0,1) AS bmp ON bmp.item_id = bm.id 
		LEFT JOIN bom_suppliers AS bsp ON bmp.supplier_id = bsp.id 
		WHERE bs.remaining < bm.noti_threshold GROUP BY bs.id ";

		$rows = $this->db->query($sql)->result();

		$imports = [];
		foreach ($rows as $row) {
			$row->ratio = abs($row->noti_threshold - $row->remaining);
			$span = '<div class="stock' . $row->group_id . '_lacked_material lacked_material" data-item-id="' . $row->item_id . '" data-lacked-amount="' . $row->ratio . '" data-unit="' . $row->item_unit . '" data-supplier-name="' . $row->supplier_name . '" data-supplier-id="' . $row->supplier_id . '" data-price="' . $row->price . '" data-currency="' . $row->currency . '" data-currency_symbol="' . $row->currency_symbol . '" style="display:none;">' . $row->item_name . ' ' . $row->ratio . $row->item_unit . '</div>';
			$button = '<button type="button" class="btn btn-warning pull-right btn-pr" id="btn-pr1" onclick="purchaseRequestItem(\'#btn-pr1\',\'stock' . $row->group_id . '_\')"><i class="fa fa-shopping-cart"></i> ' . lang('request_low_item') . '</button>';
			if (!isset($imports[$row->group_id])) {
				$import = [];
				$import[] = $row->group_id;
				$import[] = '<a href="javascript:;" onclick="javascript:jQuery(\'.stock' . $row->group_id . '_lacked_material\').toggle();">' . $row->importname . '</a>' . $span;
				$import[] = 1;
				$import[] = $button;
				$imports[$row->group_id] = $import;
			} else {
				$import = $imports[$row->group_id];
				$import[1] = $import[1] . $span;
				$import[2]++;
				$import[3] = $button;
				$imports[$row->group_id] = $import;
			}
		}
		echo json_encode(array("data" => array_values($imports), "success" => 1, "message" => "Success"));
	}

	function category_list()
	{
		$id = $this->input->post("id") ? $this->input->post("id") : 0;
		$result = array();
		$row = array();

		if ($id === 0) {
			$result = $this->Pr_categories_model->getRequestCategoryList();
		} else {
			$result = $this->Pr_categories_model->getRequestCategoryById($id);
		}

		if (sizeof($result)) {
			foreach ($result as $data) {
				if ($data->id == 1) {
					$operation = '';
					$title = $data->title;
				} else {
					$title = modal_anchor(get_uri("materialrequests/category_form/" . $data->id), $data->title, array("class" => "edit", "title" => lang("edit_category"), "data-post-id" => $data->id, "data-act" => "ajax-modal"));
					$operation = (modal_anchor(get_uri("materialrequests/category_form/" . $data->id), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang("edit_category"), "data-post-id" => $data->id, "data-act" => "ajax-modal"))) . (js_anchor("<i class='fa fa-times fa-fw'></i>", array("title" => lang("delete_category"), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("materialrequests/category_delete"), "data-action" => "delete-confirmation")));
				}

				$row[] = array(
					"id" => $data->id,
					"title" => $title,
					"description" => $data->description,
					"created_date" => format_to_date($data->created_date),
					"created_by" => $data->first_name . " " . $data->last_name,
					"operation" => $operation
				);
			}
		}

		echo json_encode(array("data" => $row));
	}

	function category_delete()
	{
		$this->check_module_availability("module_stock");

		$id = $this->input->post("id");

		validate_submitted_data(
			array(
				"id" => "required|numeric"
			)
		);

		if ($this->Pr_categories_model->delete_by_id($id)) {
			echo json_encode(
				array(
					"success" => true,
					"message" => lang("record_deleted")
				)
			);
		} else {
			echo json_encode(
				array(
					"success" => true,
					"message" => lang("record_cannot_be_deleted")
				)
			);
		}
	}

	function material_request_list()
	{
		$row = array();

		$options = array(
			"status_id" => $this->input->post("status_id"),
			"start_date" => $this->input->post("start_date"),
			"end_date" => $this->input->post("end_date")
		);

		$result = $this->Materialrequests_model->get_meterial_request_list($options);

		if (sizeof($result)) {
			foreach ($result as $data) {
				$buttons = array();
				$status_color = "#efc050";
				$status_text = lang("status_waiting_for_approve");
				$buttons[] = modal_anchor(
					get_uri('materialrequests/modal_form'),
					'<i class="fa fa-pencil"></i>',
					array(
						'data-post-id' => $data->id,
						'data-act' => 'ajax-modal',
						'title' => lang('edit_materialrequest'),
						'class' => 'edit'
					)
				);
				$buttons[] = '<a href="javascript:void(0);" data-action-url="' . get_uri("materialrequests/delete") . '" class="delete" data-action="delete-confirmation" data-id="' . $data->id . '" title="' . lang('delete_materialrequests') . '"><i class="fa fa-times fa-fw"></i></a>';
				
				if ($data->status_id == 3) {
					$buttons = array();
					$status_color = "#009b77";
					$status_text = lang("status_already_approved");
					$buttons[] = modal_anchor(
						get_uri('materialrequests/modal_form'),
						'<i class="fa fa-eye"></i>',
						array(
							'data-post-id' => $data->id,
							'data-act' => 'ajax-modal',
							'title' => lang('edit_materialrequest'),
							'class' => 'edit'
						)
					);
				}

				if ($data->status_id == 4) {
					$buttons = array();
					$status_color = "#ff1a1a";
					$status_text = lang("status_already_rejected");
					$buttons[] = modal_anchor(
						get_uri('materialrequests/modal_form'),
						'<i class="fa fa-eye"></i>',
						array(
							'data-post-id' => $data->id,
							'data-act' => 'ajax-modal',
							'title' => lang('edit_materialrequest'),
							'class' => 'edit'
						)
					);
				}

				$operation = implode('', $buttons);
				$row[] = array(
					"id" => $data->id,
					"doc_no" => $data->doc_no ? anchor(get_uri("materialrequests/view/" . $data->id), $data->doc_no) : lang("have_no_document_number"),
					"category_name" => $data->title,
					"project_name" => $data->project_id ? anchor(get_uri("projects/view/" . $data->project_id), $data->project_name ? $data->project_name : $data->project_names) : lang("have_no_project_name"),
					"client_name" => $data->company_name ? $data->company_name : '-',
					"user_name" => $data->first_name . " " . $data->last_name,
					"request_date" => format_to_date($data->mr_date),
					"status" => "<span style='background-color: $status_color;' class='label'>$status_text</span>",
					"operation" => $operation
				);
			}
		}

		echo json_encode(array("data" => $row));
	}

	function material_request_modal()
	{
		$request = $this->input->post();

		$category_list = $this->Pr_categories_model->getRequestCategoryList();
		foreach ($category_list as $item) {
			$view_data["category_dropdown"][$item->id] = $item->title;
		}

		$view_data["model_info"] = $this->Materialrequests_model->get_one($request["id"]);
		$view_data["taxes_dropdown"] = array("0" => lang("tax")) + $this->Taxes_model->get_dropdown_list(array("title"));

		var_dump(arr($view_data));
	}

	function save_header()
	{
		$post = $this->input->post();

		if ($this->Permission_m->update_material_request != true) {
			echo json_encode(array("success" => false, "message" => lang("error_occurred")));
			return;
		}

		validate_submitted_data(
			array(
				"id" => "numeric",
				"mr_date" => "required",
				"status_id" => "required"
			)
		);

		$id = intval($post["id"]);
		$mr_date = $post["mr_date"];
		$credit = $post["credit"];

		$sql_date = "SELECT DATE_FORMAT( ADDDATE( '" . $mr_date . "', INTERVAL " . $credit . " day ), '%Y-%m-%d' ) AS expired FROM materialrequests WHERE materialrequests.id=$id";
		$credit_date = $this->db->query($sql_date)->row();

		$mr_data = array(
			"id" => $id,
			"doc_no" => $post["doc_no"],
			"project_name" => $post["project_name"],
			"catid" => $post["catid"],
			"payment" => $post["payment"],
			"credit" => $credit,
			"expired" => $credit_date->expired,
			"mr_date" => $mr_date,
			"note" => $post["mr_note"],
			"status_id" => $post["status_id"],
			"tax_id" => $this->input->post("tax_id") ? $this->input->post("tax_id") : 0,
			"tax_id2" => $this->input->post("tax_id2") ? $this->input->post("tax_id2") : 0
		);

		$affected = $this->Materialrequests_model->save_header($mr_data);
		if ($affected === 1) {
			echo json_encode(array("success" => true, "data" => $mr_data, "message" => lang("record_saved")));
		} else {
			echo json_encode(array("success" => false, "message" => lang("error_occurred")));
		}
	}

	function mr_create_save() // dev2
	{
		$post = $this->input->post();

		$id = '';
		$record = '';
		if (empty($post['id'])) {
			$mr_data = array(
				'mr_type' => $post['mr_type'],
				'catid' => $post['catid'],
				'mr_date' => $post['mr_date'],
				'status_id' => 1,
				'project_id' => $post['project_id'],
				'created_by' => $this->login_user->id,
				'requester_id' => $post['requester_id'],
				'note' => $post['note']
			);

			$insert_id = $this->Materialrequests_model->dev2_postMaterialRequestHeader($mr_data);
			$result = $this->Materialrequests_model->get_materialrequest_by_id($insert_id);
			$record = 'created';
		} else {
			$id = $post['id'];
			$mr_data = array(
				'mr_type' => $post['mr_type'],
				'catid' => $post['catid'],
				'mr_date' => $post['mr_date'],
				'project_id' => $post['project_id'],
				'requester_id' => $post['requester_id'],
				'note' => $post['note']
			);

			$affected = $this->Materialrequests_model->dev2_putMaterialRequestHeader($mr_data, $id);
			$result = $this->Materialrequests_model->get_materialrequest_by_id($id);
			$record = 'updated';
		}
		
		$status_color = "#efc050";
		$status_text = lang("status_waiting_for_approve");
		$operation = "<a href='javascript:void(0);' data-action-url='" . get_uri("materialrequests/modal_form") . "' data-post-id='" . $result->id . "' title='" . lang('edit_materialrequest') . "' class='edit' data-act='ajax-modal'><i class='fa fa-pencil'></i></a>";
		$operation .= "<a href='javascript:void(0);' data-action-url='" . get_uri("materialrequests/delete") . "' class='delete' data-action='delete-confirmation' data-id='" . $result->id . "' title='" . lang('delete_materialrequests') . "'><i class='fa fa-times fa-fw'></i></a>";

		if ($result->status_id == 3) {
			$status_color = "#009b77";
			$status_text = lang("status_already_approved");
			$operation = "<a href='javascript:void(0);' data-action-url='" . get_uri("materialrequests/modal_form") . "' data-post-id='" . $result->id . "' title='" . lang('edit_materialrequest') . "' class='edit' data-act='ajax-modal'><i class='fa fa-eye'></i></a>";
		}

		if ($result->status_id == 4) {
			$status_color = "#ff1a1a";
			$status_text = lang("status_already_rejected");
			$operation = "<a href='javascript:void(0);' data-action-url='" . get_uri("materialrequests/modal_form") . "' data-post-id='" . $result->id . "' title='" . lang('edit_materialrequest') . "' class='edit' data-act='ajax-modal'><i class='fa fa-eye'></i></a>";
		}

		$request_by = '-';
		if ($result->requester_id) {
			$user = $this->Users_model->getUserById($result->requester_id);
			$url = get_avatar($user->image);
			$span = '<span class="avatar avatar-xs mr10"><img src="' . $url . '" alt=""></span>' . $user->first_name . ' ' . $user->last_name;
			$request_by = get_team_member_profile_link($user->id, $span);
		}

		$row = array(
			"id" => $result->id,
			"doc_no" => $result->doc_no ? anchor(get_uri("materialrequests/view/" . $result->id), $result->doc_no) : lang("have_no_document_number"),
			"category_name" => $this->Pr_categories_model->dev2_getCategoryTitleById($result->catid),
			"project_name" => $result->project_id ? anchor(get_uri("projects/view/" . $result->project_id), $result->project_name ? $result->project_name : $result->project_names) : lang("have_no_project_name"),
			"client_name" => $result->project_id ? $this->Clients_model->dev2_getClientNameByProjectId($result->project_id) : '-',
			"user_name" => $request_by,
			"request_date" => format_to_date($result->mr_date),
			"status" => "<span style='background-color: $status_color;' class='label'>$status_text</span>",
			"operation" => $operation
		);

		echo json_encode(array('success' => true, 'data' => $row, 'record' => $record, 'data_id' => $result->id, 'message' => 'posting...'));
	}

	function dev2_mapDataBetweenMaterialRequestAndStockUsedList($key = null)
	{
		if ($key !== "google555") {
			redirect("forbidden");
			exit();
		}

		// get project id from material request that status is wait for approve (materialrequests.status_id == 1)
		$mr_wfa_list = $this->Materialrequests_model->dev2_getProjectIdFromMaterialRequestByStatusId(1);
		if (sizeof($mr_wfa_list)) {
			foreach ($mr_wfa_list as $mr_wfa) {
				// get project item by project id
				$project_wfa_list = $this->Bom_project_item_materials_model->dev2_getProjectItemIdByProjectId($mr_wfa->project_id);
				if (sizeof($project_wfa_list)) {
					foreach ($project_wfa_list as $project_wfa) {
						$this->Bom_project_item_materials_model->dev2_updateUsedStatusByProjectItemId($project_wfa->id, 0);
						$this->Bom_project_item_materials_model->dev2_updateMaterialRequestIdByProjectItemId($project_wfa->id, $mr_wfa->id);
					}
				}
			}
		}
		echo "<pre>Stock used list that material request is waiting for approve, updated successfully.</pre>";

		// get project id from material request that status is approved (materialrequests.status_id == 3)
		$mr_apd_list = $this->Materialrequests_model->dev2_getProjectIdFromMaterialRequestByStatusId(3);
		if (sizeof($mr_apd_list)) {
			foreach ($mr_apd_list as $mr_apd) {
				// get project item by project id
				$project_apd_list = $this->Bom_project_item_materials_model->dev2_getProjectItemIdByProjectId($mr_apd->project_id);
				if (sizeof($project_apd_list)) {
					foreach ($project_apd_list as $project_apd) {
						$this->Bom_project_item_materials_model->dev2_updateUsedStatusByProjectItemId($project_apd->id, 1);
						$this->Bom_project_item_materials_model->dev2_updateMaterialRequestIdByProjectItemId($project_apd->id, $mr_apd->id);
					}
				}
			}
		}
		echo "<pre>Stock used list that material request is approved, updated successfully.</pre>";

		// get bom list was created material request (bom_project_item_materials.mr_id is not null)
		$bom_list = $this->Bom_project_item_materials_model->dev2_getBomListByMaterialRequestIsNotNull();
		if (sizeof($bom_list)) {
			foreach ($bom_list as $bom) {
				$this->Mr_items_model->dev2_updateMaterialRequestItemByBomProjectItem(array(
					'mr_id' => $bom->mr_id,
					'material_id' => $bom->material_id,
					'bpim_id' => $bom->id,
					'stock_id' => $bom->stock_id
				));
			}
		}
		echo "<pre>Material request item map to stock, updated successfully.</pre>";
	}

	function view_items($mr_id)
	{
		$mr_info = $this->Materialrequests_model->get_one($mr_id);
		$items = $this->Materialrequests_model->dev2_getItemListByMaterialRequestId($mr_id, $mr_info->mr_type);

		jout($items);
	}

	function item_delete()
	{
		$req = $this->json;
		$data = array(
			'id' => $req->item_id,
			'mr_id' => $req->doc_id
		);

		$info = $this->Materialrequests_model->dev2_deleteMaterialRequestItem($data, $req->doc_type);
		jout($info);
	}

	function item_add()
	{
		$view_data['post'] = $this->input->post();

		if (isset($view_data['post']['doc_id']) && !empty($view_data['post']['doc_id'])) {
			$view_data['mat_req_info'] = $this->Materialrequests_model->get_materialrequest_by_id($view_data['post']['doc_id']);
		}

		if (isset($view_data['post']['item_id']) && !empty($view_data['post']['item_id'])) {
			$view_data['mat_item_info'] = $this->Materialrequests_model->get_materialrequest_item_by_id($view_data['post']['item_id']);
		}

		// var_dump(arr($view_data)); exit();
		$this->load->view("materialrequests/item_add", $view_data);
	}

	function item_edit()
	{
		$view_data['post'] = $this->input->post();

		if (isset($view_data['post']['doc_id']) && !empty($view_data['post']['doc_id'])) {
			$view_data['mat_req_info'] = $this->Materialrequests_model->get_materialrequest_by_id($view_data['post']['doc_id']);
		}

		if (isset($view_data['post']['item_id']) && !empty($view_data['post']['item_id'])) {
			$view_data['mat_item_info'] = $this->Materialrequests_model->get_materialrequest_item_by_id($view_data['post']['item_id']);
		}

		if (isset($view_data['mat_item_info']->stock_id) && !empty($view_data['mat_item_info']->stock_id)) {
			if ($view_data['mat_req_info']->mr_type == 2) {
				$view_data['mat_stock_info'] = $this->Bom_item_stocks_model->dev2_getRestockingByStockId($view_data['mat_item_info']->stock_id);	
			} else {
				$view_data['mat_stock_info'] = $this->Bom_stocks_model->dev2_getRestockingByStockId($view_data['mat_item_info']->stock_id);
			}
		}

		// var_dump(arr($view_data)); exit();
		$this->load->view("materialrequests/item_edit", $view_data);
	}

	function item_add_save()
	{
		$data = array(
			'item_id' => $this->json->item_id,
			'bpim_id' => $this->json->bpim_id,
			'mr_id' => $this->json->mr_id,
			'mr_type' => $this->json->mr_type,
			'project_id' => $this->json->project_id,
			'project_name' => $this->json->project_name,
			'material_id' => $this->json->material_id,
			'stock_id' => $this->json->stock_id,
			'quantity' => $this->json->quantity
		);

		// get material info
		$bs = '';
		if (isset($data['mr_type']) && $data['mr_type'] == '2') {
			$bs = $this->Items_model->get_one($data['material_id']);
		} else {
			$bs = $this->Bom_materials_model->get_one($data['material_id']);
		}

		// prepare data and insert to bom project item material
		$bpim_id = null;
		if ($data['bpim_id'] == '0') {
			$bpim = array(
				'project_item_id' => $this->Projects_model->getItemIdByProjectId($data['project_id']),
				'stock_id' => $data['stock_id'],
				'ratio' => $data['quantity'],
				'mr_id' => $data['mr_id'],
				'used_status' => 0,
				'entry_flag' => 1,
				'created_by' => $this->login_user->id
			);

			if (isset($data['mr_type']) && $data['mr_type'] == '2') {
				$bpim['item_id'] = $data['material_id'];
				$bpim_id = $this->Bom_project_item_items_model->postProjectItemItemFromMaterialRequest($bpim);
			} else {
				$bpim['material_id'] = $data['material_id'];
				$bpim_id = $this->Bom_project_item_materials_model->postProjectItemMaterialFromMaterialRequest($bpim);
			}
		} else {
			$bpim = array(
				'id' => $data['bpim_id'],
				'stock_id' => $data['stock_id'],
				'ratio' => $data['quantity']
			);

			if (isset($data['mr_type']) && $data['mr_type'] == '2') {
				$this->Bom_project_item_items_model->patchProjectItemItemFromMaterialRequest($bpim);
			} else {
				$this->Bom_project_item_materials_model->patchProjectItemMaterialFromMaterialRequest($bpim);
			}
			$bpim_id = $data['bpim_id'];
		}
		
		// prepare data and insert to material request item
		$mri_id = null;
		if ($data['item_id'] == '0') {
			$mri = array(
				'mr_id' => $data['mr_id'],
				'project_id' => $data['project_id'],
				'project_name' => $data['project_name'],
				'description' => $bs->description,
				'quantity' => $data['quantity'],
				'bpim_id' => $bpim_id,
				'stock_id' => $data['stock_id'],
				'created_by' => $this->login_user->id
			);

			if (isset($data['mr_type']) && $data['mr_type'] == '2') {
				$mri['item_id'] = $data['material_id'];
				$mri['code'] = $bs->item_code ? $bs->item_code : null;
				$mri['title'] = $bs->title ? $bs->title : null;
				$mri['unit_type'] = $bs->unit_type;

				$mri_id = $this->Materialrequests_model->postItemRequestItemFromMaterialRequest($mri);
			} else {
				$mri['material_id'] = $data['material_id'];
				$mri['code'] = $bs->name;
				$mri['title'] = $bs->production_name;
				$mri['unit_type'] = $bs->unit;

				$mri_id = $this->Materialrequests_model->postMaterialRequestItemFromMaterialRequest($mri);
			}
		} else {
			$mri = array(
				'id' => $data['item_id'],
				'stock_id' => $data['stock_id'],
				'quantity' => $data['quantity']
			);

			$this->Materialrequests_model->patchMaterialRequestItemFromMaterialRequest($mri);
			$mri_id = $data['item_id'];
		}
		
		$result = array(
			'bpim_id' => $bpim_id,
			'mri_id' => $mri_id
		);

		jout($result);
	}

	function stock_material_list($material_id)
	{
		$list = $this->Materialrequests_model->getStockMaterialListByMaterialId($material_id);
		jout($list);
	}

	function stock_item_list($item_id)
	{
		$list = $this->Materialrequests_model->getStockItemListByItemId($item_id);
		jout($list);
	}

	function print($id)
	{
		$this->data['mat_req_info'] = $this->Materialrequests_model->get_materialrequest_by_id($id);
		$this->data['mat_item_info'] = $this->Materialrequests_model->dev2_getItemListForPrintByMaterialRequestId($id, $this->data['mat_req_info']->mr_type);
		$this->data['mat_requester_info'] = $this->Users_m->get_user_by_id($this->data['mat_req_info']->requester_id);
		$this->data['mat_project_info'] = $this->Projects_model->get_project_by_id($this->data['mat_req_info']->project_id);
		$this->data["docmode"] = "private_print";

		// var_dump(arr($this->data)); exit();
		$this->load->view('edocs/material_request', $this->data);
	}

}

/* End of file materialrequests.php */
/* Location: ./application/controllers/materialrequests.php */