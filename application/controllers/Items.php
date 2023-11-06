<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

use Laminas\Barcode\Barcode;

class Items extends MY_Controller
{

	private $item_type = "FG";

	function __construct()
	{
		parent::__construct();
		$this->init_permission_checker("order");
		$this->load->model("Bom_item_mixing_groups_model");
		$this->load->model("Account_category_model");
		$this->className = 'items';
	}

	protected function validate_access_to_items()
	{
		$access_invoice = $this->get_access_info("invoice");
		$access_estimate = $this->get_access_info("estimate");

		//don't show the items if invoice/estimate modetail_mixing_modalle is not enabled
		if (!(get_setting("module_invoice") == "1" || get_setting("module_estimate") == "1")) {
			redirect("forbidden");
		}

		if ($this->login_user->is_admin) {
			return true;
		} else if ($access_invoice->access_type === "all" || $access_estimate->access_type === "all") {
			return true;
		} else {
			redirect("forbidden");
		}
	}

	//get categories dropdown
	private function _get_categories_dropdown()
	{
		$categories = $this->Item_categories_model->get_all_where(array("deleted" => 0), 0, 0, "title")->result();

		$categories_dropdown = array(array("id" => "", "text" => "- " . lang("category") . " -"));
		foreach ($categories as $category) {
			$categories_dropdown[] = array("id" => $category->id, "text" => $category->title);
		}

		return json_encode($categories_dropdown);
	}

	/* load item modal */

	function modal_form()
	{
		if ($this->Permission_m->create_product_item != true)
			redirect("forbidden");

		validate_submitted_data(
			array(
				"id" => "numeric"
			)
		);

		$view_data['model_info'] = $this->Items_model->get_one($this->input->post('id'));
		$view_data['categories_dropdown'] = $this->Item_categories_model->get_dropdown_list(array("title"));
		$view_data["account_category"] = $this->Account_category_model->get_list_dropdown();

		// var_dump(arr($view_data)); exit;

		$this->load->view('items/modal_form', $view_data);
	}

	/* add or edit an item */

	function save()
	{
		if ($this->Permission_m->create_product_item != true)
			exit;

		validate_submitted_data(
			array(
				"id" => "numeric",
				"category_id" => "required",
			)
		);

		$id = $this->input->post('id');
		$oid = $this->input->post('oid');
		$is_duplicate = $this->input->post('is_duplicate');
		$account_id = $this->input->post('account_id');

		$item_data = array(
			"item_code" => $this->input->post('item_code'),
			"title" => $this->input->post('title'),
			"description" => $this->input->post('description'),
			"category_id" => $this->input->post('category_id'),
			"account_id" => $account_id ? $account_id : null,
			"unit_type" => $this->input->post('unit_type'),
			"barcode" => $this->input->post('barcode'),
			"noti_threshold" => $this->input->post('noti_threshold'),
			"rate" => unformat_currency($this->input->post('item_rate')),
			"show_in_client_portal" => $this->input->post('show_in_client_portal') ? $this->input->post('show_in_client_portal') : ""
		);
		// ALTER TABLE `items` ADD `account_id` INT NULL AFTER `category_id`; 

		$new_files = [];
		$target_path = get_setting("timeline_file_path");
		$timeline_file_path = get_setting("timeline_file_path");

		$files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "item");
		$new_files = unserialize($files_data);
		$files = [];
		if ($id) {
			$item_info = $this->Items_model->get_one($id);
			$new_files = update_saved_files($timeline_file_path, $item_info->files, $new_files);
		} elseif ($oid && $is_duplicate) { // duplicate
			$o_item_info = $this->Items_model->get_one($oid);
			$new_files = unserialize($o_item_info->files);
			$files_data = copy_files($new_files, $target_path, "item");
			$new_files = unserialize($files_data);

			$files = $this->Bom_item_mixing_groups_model->get_file_details(['ref_id' => $oid, 'tablename' => 'items'])->result();
			// $new_files = update_saved_files($timeline_file_path, $o_item_info->files, $new_files);
		}

		$item_data["files"] = serialize($new_files);

		$item_id = $this->Items_model->save($item_data, $id);
		if ($item_id) {
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
		}
	}

	/* delete or undo an item */

	function delete()
	{
		$this->access_only_team_members();
		$this->validate_access_to_items();

		validate_submitted_data(
			array(
				"id" => "required|numeric"
			)
		);

		$id = $this->input->post('id');
		if ($this->input->post('undo')) {
			if ($this->Items_model->delete($id, true)) {
				$options = array("id" => $id);
				$item_info = $this->Items_model->get_details($options)->row();
				echo json_encode(array("success" => true, "id" => $item_info->id, "data" => $this->_make_item_row($item_info), "message" => lang('record_undone')));
			} else {
				echo json_encode(array("success" => false, lang('error_occurred')));
			}
		} else {
			if ($this->Items_model->delete($id)) {
				$item_info = $this->Items_model->get_one($id);
				echo json_encode(array("success" => true, "id" => $item_info->id, 'message' => lang('record_deleted')));
			} else {
				echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
			}
		}
	}
	
	public function barcode($barcode)
	{
		$databarcode = ['text' => $barcode, 'drawText' => true,];
		$rendererOptions = ['imageType' => 'png', 'horizontalPosition' => 'center', 'verticalPosition' => 'middle'];
		Barcode::render('code128', 'image', $databarcode, $rendererOptions);
	}

	/* prepare a row of item list table */

	private function _make_item_row($data)
	{
		$src = @$data->barcode;
		if ($src) {
			$src = get_uri('/items/barcode/' . $src);
		}

		// var_dump($src); exit;
		// var_dump($data); exit;
		$type = $data->unit_type ? $data->unit_type : "";

		$show_in_client_portal_icon = "";
		if ($data->show_in_client_portal && get_setting("module_order")) {
			$show_in_client_portal_icon = "<i title='" . lang("showing_in_client_portal") . "' class='fa fa-shopping-basket'></i> ";
		}

		$preview = '<img class="product-preview" src="' . base_url('assets/images/file_preview.jpg') . '" />';
		$images = @unserialize($data->files);
		if (is_array($images) && sizeof($images)) {
			$preview = '<img class="product-preview" src="' . base_url('files/timeline_files/' . $images[sizeof($images) - 1]['file_name']) . '" />';
		}

		$buttons = array();
		if ($this->login_user->is_admin == "1") {
			$buttons[] = modal_anchor(get_uri("" . $this->className . "/modal_form"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_item'), "data-post-id" => $data->id))
				.
				js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("" . $this->className . "/delete"), "data-action" => "delete"));
		}

		return array(
			anchor(get_uri('' . $this->className . '/detail/' . $data->id), $data->id),
			$preview,
			($data->item_code != "" ? $data->item_code:"-"),
			anchor(get_uri('' . $this->className . '/detail/' . $data->id), $data->title),
			nl2br($data->description),
			$data->category_title ? "$data->category_title" : "-",
			// $data->account_id ? $this->Account_category_model->account_by($data->account_id) : "-",
			$type,
			@$data->barcode ? '<div style="text-align:center"><a href="' . $src . '" class="barcode_img" download><img src="' . $src . '" /><div class="text">Click to download</div></a></div>' : '-',
			$data->rate,
			implode('', $buttons)
		);
	}

	function upload_file()
	{
		$this->access_only_team_members();
		upload_file_to_temp();
	}

	function validate_items_file()
	{
		$this->access_only_team_members();
		$file_name = $this->input->post("file_name");
		if (!is_valid_file_to_upload($file_name)) {
			echo json_encode(array("success" => false, 'message' => lang('invalid_file_type')));
			exit();
		}

		if (is_image_file($file_name)) {
			echo json_encode(array("success" => true));
		} else {
			echo json_encode(array("success" => false, 'message' => lang('please_upload_valid_image_files')));
		}
	}

	function view()
	{
		validate_submitted_data(
			array(
				"id" => "required|numeric"
			)
		);

		$model_info = $this->Items_model->get_details(array("id" => $this->input->post('id'), "login_user_id" => $this->login_user->id))->row();

		$view_data['model_info'] = $model_info;
		$view_data["client_info"] = $this->Clients_model->get_one($this->login_user->client_id);

		$this->load->view('items/view', $view_data);
	}

	function save_files_sort()
	{
		$this->access_only_allowed_members();
		$id = $this->input->post("id");
		$sort_values = $this->input->post("sort_values");
		if ($id && $sort_values) {
			//extract the values from the :,: separated string
			$sort_array = explode(":,:", $sort_values);

			$item_info = $this->Items_model->get_one($id);
			if ($item_info->id) {
				$updated_file_indexes = update_file_indexes($item_info->files, $sort_array);
				$item_data = array(
					"files" => serialize($updated_file_indexes)
				);

				$this->Items_model->save($item_data, $id);
			}
		}
	}
	/* store criteria */

	function grid_view($offset = 0, $limit = 20, $category_id = 0, $search = "")
	{
		$this->check_access_to_store();

		$options = array("login_user_id" => $this->login_user->id);

		$item_search = $this->input->post("item_search");
		if ($item_search) {
			$search = $this->input->post("search");
			$category_id = $this->input->post("category_id") ? $this->input->post("category_id") : 0;
		}

		if ($search) {
			$options["search"] = $search;
		}

		if ($category_id) {
			$options["category_id"] = $category_id;
		}

		if ($this->login_user->user_type == "client") {
			$options["show_in_client_portal"] = 1; //show all items on admin side
		}

		//get all rows
		$all_items = $this->Items_model->get_details($options)->num_rows();

		$options["offset"] = $offset;
		$options["limit"] = $limit;

		$view_data["items"] = $this->Items_model->get_details($options)->result();
		$view_data["result_remaining"] = $all_items - $limit - $offset;
		$view_data["next_page_offset"] = $offset + $limit;

		$view_data["search"] = $search;
		$view_data["category_id"] = $category_id;

		$view_data["client_info"] = $this->Clients_model->get_one($this->login_user->client_id);
		$view_data['categories_dropdown'] = $this->_get_categories_dropdown();

		if ($offset) { //load more view
			$this->load->view("items/items_grid_data", $view_data);
		} else if ($item_search) { //search suggestions view
			echo json_encode(array("success" => true, "data" => $this->load->view("items/items_grid_data", $view_data, true)));
		} else { //default view
			$this->template->rander("items/grid_view", $view_data);
		}
	}

	private function check_access_to_this_item($item_info)
	{
		if ($this->login_user->user_type === "client") {
			//check if the item has the availability to show on client portal
			if (!$item_info->show_in_client_portal) {
				redirect("forbidden");
			}
		}
	}

	function add_item_to_cart()
	{
		$this->check_access_to_store();

		validate_submitted_data(
			array(
				"id" => "required|numeric"
			)
		);

		$id = $this->input->post("id");
		$item_info = $this->Items_model->get_one($id);
		$this->check_access_to_this_item($item_info);

		$order_item_data = array(
			"title" => $item_info->title,
			"quantity" => 1,
			//add 1 item first time
			"unit_type" => $item_info->unit_type,
			"rate" => $item_info->rate,
			"total" => $item_info->rate,
			//since the quantity is 1
			"created_by" => $this->login_user->id,
			"item_id" => $id
		);

		$save_id = $this->Order_items_model->save($order_item_data);

		if ($save_id) {
			echo json_encode(array("success" => true, 'message' => lang('record_saved')));
		} else {
			echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
		}
	}

	function count_cart_items()
	{
		$this->check_access_to_store();

		$cart_items_count = $this->Order_items_model->get_all_where(array("created_by" => $this->login_user->id, "order_id" => 0, "deleted" => 0))->num_rows();

		if ($cart_items_count) {
			echo json_encode(array("success" => true, "cart_items_count" => $cart_items_count));
		} else {
			echo json_encode(array("success" => false, 'message' => lang('no_record_found')));
		}
	}

	function load_cart_items()
	{
		$this->check_access_to_store();

		$view_data = get_order_making_data();

		$options = array("created_by" => $this->login_user->id, "processing" => true);
		$view_data["items"] = $this->Order_items_model->get_details($options)->result();
		$view_data["client_info"] = $this->Clients_model->get_one($this->login_user->client_id);

		$this->load->view("items/cart/cart_items_list", $view_data);
	}

	function delete_cart_item()
	{
		$this->check_access_to_store();
		validate_submitted_data(
			array(
				"id" => "required"
			)
		);

		$order_item_id = $this->input->post("id");
		$order_item_info = $this->Order_items_model->get_one($order_item_id);
		$this->check_access_to_this_order_item($order_item_info);

		if ($this->Order_items_model->delete($order_item_id)) {
			echo json_encode(array("success" => true, 'message' => lang('record_deleted'), "cart_total_view" => $this->_get_cart_total_view()));
		} else {
			echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
		}
	}

	function change_cart_item_quantity()
	{
		$this->check_access_to_store();
		validate_submitted_data(
			array(
				"id" => "required",
				"action" => "required"
			)
		);

		$id = $this->input->post("id");
		$action = $this->input->post("action");

		$item_info = $this->Order_items_model->get_one($id);
		$this->check_access_to_this_order_item($item_info);

		if ($item_info->id) {
			$quantity = $item_info->quantity;
			if ($action == "plus") {
				//plus quantity
				$quantity = $quantity + 1;
			} else if ($action == "minus" && $quantity > 1) {
				//minus quantity
				//shouldn't be less than one
				$quantity = $quantity - 1;
			}

			$data = array(
				"quantity" => $quantity,
				"total" => $item_info->rate * $quantity
			);
			$this->Order_items_model->save($data, $item_info->id);

			$options = array("id" => $id);
			$view_data["item"] = $this->Order_items_model->get_details($options)->row();
			$view_data["client_info"] = $this->Clients_model->get_one($this->login_user->client_id);

			echo json_encode(array("success" => true, 'message' => lang('record_saved'), "data" => $this->load->view("items/cart/cart_item_data", $view_data, true), "cart_total_view" => $this->_get_cart_total_view()));
		} else {
			echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
		}
	}

	private function _get_cart_total_view()
	{
		$view_data = get_order_making_data();
		return $this->load->view('items/cart/cart_total_section', $view_data, true);
	}
	
	// BOM
	function detail($item_id = 0, $tab = "")
	{
		if ($item_id) {
			$model_info = $this->Items_model->get_details(array("id" => $item_id, "login_user_id" => $this->login_user->id))->row();
			if ($model_info) {
				$view_data['model_info'] = $model_info;
				$view_data["tab"] = $tab;
				$view_data["access_product_item_formula"] = false;
				$this->template->rander("items/detail/index", $view_data);
			} else {
				show_404();
			}
		} else {
			show_404();
		}
	}

	function detail_info($item_id = 0)
	{
		$this->check_module_availability("module_stock");
		$this->access_only_team_members();
		//$this->validate_access_to_items();

		if ($item_id) {
			//$this->access_only_team_members();
			//$this->validate_access_to_items();

			$view_data['model_info'] = $this->Items_model->get_one($item_id);
			$view_data['categories_dropdown'] = $this->Item_categories_model->get_dropdown_list(array("title"));

			$view_data['label_column'] = "col-md-2";
			$view_data['field_column'] = "col-md-10";

			$this->load->view('items/detail/info', $view_data);
		}
	}

	function detail_mixings($item_id = 0)
	{
		/*$this->check_module_availability("module_stock");
		
		$view_data['can_read'] = $this->check_permission('bom_material_read');
		$view_data['can_read_production_name'] = $this->check_permission('bom_material_read_production_name');
		if(!$this->login_user->is_admin && (!$view_data['can_read'] || !$view_data['can_read_production_name'])) {
		redirect("forbidden");
		}*/

		$view_data['item_id'] = $item_id;
		$this->load->view("items/detail/mixing", $view_data);
	}

	function detail_mixing_list($item_id = 0)
	{
		$this->check_module_availability("module_stock");
		
		$view_data['can_read'] = $this->check_permission('bom_material_read');
		$view_data['can_read_production_name'] = $this->check_permission('bom_material_read_production_name');

		if(!$this->login_user->is_admin && (!$view_data['can_read'] || !$view_data['can_read_production_name'])) {
			redirect("forbidden");
		}

		$result = array();
		$list_data = $this->Bom_item_mixing_groups_model->get_details(['item_id' => $item_id])->result();

		foreach ($list_data as $data) {
			$result[] = $this->_detail_mixing_make_row($data);
		}

		echo json_encode(array("data" => $result));
	}

	private function _detail_mixing_make_row($data)
	{
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

	private function _detail_mixing_row_data($id)
	{
		$options = array(
			'id' => $id
		);
		$data = $this->Bom_item_mixing_groups_model->get_details($options)->row();
		return $this->_detail_mixing_make_row($data);
	}

	/*private function _detail_mixingcategory_make_row($data) {
	$row_data = array(
	$data->id,
	modal_anchor(get_uri("items/detail_mixingcategory_modal"), $data->title, array("class" => "edit", "title" => lang('edit_category'), "data-post-id" => $data->id,))
	);
	$row_data[] = modal_anchor(get_uri("items/detail_mixingcategory_modal"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('item_mixing_edit'), "data-post-id" => $data->id))
	. js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete_category'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("items/detail_mixingcategry_delete"), "data-action" => "delete-confirmation"));
	return $row_data;
	}*/

	/*private function _detail_mixingcategory_row_data($id) {
	$options = array(
	'id' => $id
	);
	$data = $this->Bom_item_mixing_groups_model->get_category_details($options)->row();
	return $this->_detail_mixingcategory_make_row($data);
	}*/

	/*function mixingcategories($item_id = 0) {
	$this->check_module_availability("module_stock");
	
	$view_data['can_read'] = $this->check_permission('bom_material_read');
	$view_data['can_read_production_name'] = $this->check_permission('bom_material_read_production_name');
	if(!$this->login_user->is_admin && (!$view_data['can_read'] || !$view_data['can_read_production_name'])) {
	redirect("forbidden");
	}
	// $view_data['item_id'] = $item_id;
	$this->load->view("items/detail/mixingcategories", $view_data);
	}*/

	function files($item_id = 0)
	{
		/*$this->check_module_availability("module_stock");
		
		$view_data['can_read'] = $this->check_permission('bom_material_read');
		$view_data['can_read_production_name'] = $this->check_permission('bom_material_read_production_name');
		$view_data['can_read'] = true;
		$view_data['can_read_production_name'] = true;
		if(!$this->login_user->is_admin && (!$view_data['can_read'] || !$view_data['can_read_production_name'])) {
		redirect("forbidden");
		}*/

		$view_data['item_id'] = $item_id;
		$this->load->view("items/detail/files", $view_data);
	}

	function file_list($item_id = 0)
	{
		/*$this->check_module_availability("module_stock");
		
		$view_data['can_read'] = $this->check_permission('bom_material_read');
		$view_data['can_read_production_name'] = $this->check_permission('bom_material_read_production_name');
		if(!$this->login_user->is_admin && (!$view_data['can_read'] || !$view_data['can_read_production_name'])) {
		redirect("forbidden");
		}*/

		$list_data = $this->Bom_item_mixing_groups_model->get_file_details(['ref_id' => $item_id, 'tablename' => 'items'])->result();
		$result = array();
		foreach ($list_data as $data) {
			$result[] = $this->_file_make_row($data);
		}
		echo json_encode(array("data" => $result));
	}

	private function _file_make_row($data)
	{
		$row_data = array(
			$data->id,
			$data->name,
			$data->size,
			anchor(get_uri("items/file_download/" . $data->id), lang('download'), array("class" => "download", "title" => lang('download')))
		);
		$row_data[] = modal_anchor(get_uri("items/detail_file_modal"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_file'), "data-post-id" => $data->id, "data-post-item_id" => $data->id))
			. js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete_file'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("items/file_delete"), "data-action" => "delete-confirmation"));
		return $row_data;
	}

	function detail_file_modal($item_id = 0)
	{
		/*$this->check_module_availability("module_stock");
		
		$view_data['can_read'] = $this->check_permission('bom_material_read');
		$view_data['can_read_production_name'] = $this->check_permission('bom_material_read_production_name');
		if(!$this->login_user->is_admin && (!$view_data['can_read'] || !$view_data['can_read_production_name'])) {
		redirect("forbidden");
		}*/

		$id = intval($this->input->post('id'));
		//$item_id = $this->input->post('item_id');
		validate_submitted_data(
			array(
				"id" => "numeric",
				//"item_id" => "required|numeric"
			)
		);

		$view_data['label_column'] = "col-md-3";
		$view_data['field_column'] = "col-md-9";

		$view_data['item_id'] = $item_id;

		$view_data['model_info'] = $this->Bom_item_mixing_groups_model->get_file_details(['id' => $id])->row();

		$this->load->view('items/detail/modal_file', $view_data);
	}

	private function _detail_file_row_data($id)
	{
		$options = array(
			'id' => $id
		);
		$data = $this->Bom_item_mixing_groups_model->get_file_details($options)->row();
		return $this->_file_make_row($data);
	}

	function detail_file_save()
	{
		$this->check_module_availability("module_stock");

		$view_data['can_read'] = $this->check_permission('bom_material_read');
		$view_data['can_read_production_name'] = $this->check_permission('bom_material_read_production_name');

		if (!$this->login_user->is_admin && (!$view_data['can_read'] || !$view_data['can_read_production_name'])) {
			echo json_encode(array("success" => false, 'message' => lang('no_permissions')));
			exit;
		}

		$id = $this->input->post('id');
		validate_submitted_data(
			array(
				"id" => "numeric",
				"item_id" => "required|numeric",
				"name" => "required",
				//"file" => "required",
				//"ratio" => "required|numeric"
			)
		);


		// $config = [];
		// $target_path = get_setting("timeline_file_path");
		//$config['upload_path'] = './uploads/';
		// $config['upload_path'] = $target_path;
		// $config['allowed_types'] = 'pdf|xls|doc|docs|txt|gif|jpg|png';
		// $config['max_size']     = '100';
		// $config['max_width'] = '1024';
		// $config['max_height'] = '768';
		// $this->load->library('upload', $config);


		$is_public = $this->input->post('is_public');
		$data = array(
			//"ref_id" => $this->input->post('item_id'),
			"name" => $this->input->post('name'),
			"description" => $this->input->post('description'),
			//"ratio" => $this->input->post('ratio'),
			//"is_public" => $is_public,
			//"for_client_id" => $is_public == 1? null: $this->input->post('for_client_id')
		);
		$data = clean_data($data);
		if (isset($_FILES['file']) && ($_FILES["file"]["size"] > 0)) {
			$info = pathinfo($_FILES['file']['name']);
			$new_file_name = uniqid() . (@$info['extension'] ? '.' . @$info['extension'] : '');
			$target_path = BASEPATH . 'files/';
			$file_path = $target_path . $new_file_name;
			if (move_uploaded_file($_FILES["file"]["tmp_name"], $file_path)) {
				$data["size"] = $_FILES["file"]["size"];
				$data["mimetype"] = @$_FILES["file"]["type"];
				$data["path"] = $new_file_name;
				$data["tablename"] = 'items';
				$data["ref_id"] = $this->input->post('item_id');
				$data["orig_filename"] = $_FILES["file"]["name"];
			}
		}

		$save_id = $this->Bom_item_mixing_groups_model->save_file($data, $id);

		if ($save_id) {
			echo json_encode(
				array(
					"success" => true,
					"data" => $this->_detail_file_row_data($save_id),
					'id' => $save_id,
					'message' => lang('record_saved')
				)
			);
		} else {
			echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
		}
	}

	function file_download($id)
	{
		$this->check_module_availability("module_stock");

		$view_data['can_read'] = $this->check_permission('bom_material_read');
		$view_data['can_read_production_name'] = $this->check_permission('bom_material_read_production_name');

		if (!$this->login_user->is_admin && (!$view_data['can_read'] || !$view_data['can_read_production_name'])) {
			redirect("forbidden");
		}
		$options = array(
			'id' => $id
		);
		$file = $this->Bom_item_mixing_groups_model->get_file_details($options)->row();
		if ($file) {
			$target_path = BASEPATH . 'files/';
			$file_path = $target_path . $file->path;
			header('Content-Type: ' . $file->mimetype);
			header("Content-Transfer-Encoding: Binary");
			header("Content-disposition: attachment; filename=\"" . $file->orig_filename . "\"");
			readfile($file_path);
		}
	}

	/*function mixingcategories_list($item_id = 0) {
	$this->check_module_availability("module_stock");
	
	$view_data['can_read'] = $this->check_permission('bom_material_read');
	$view_data['can_read_production_name'] = $this->check_permission('bom_material_read_production_name');
	if(!$this->login_user->is_admin && (!$view_data['can_read'] || !$view_data['can_read_production_name'])) {
	redirect("forbidden");
	}
	
	$list_data = $this->Bom_item_mixing_groups_model->get_category_details()->result();
	$result = array();
	foreach ($list_data as $data) {
	$result[] = $this->_mixingcategory_make_row($data);
	}
	echo json_encode(array("data" => $result));
	}*/
	/* private function _mixingcategory_make_row($data) {
	$row_data = array(
	$data->id,
	$data->title
	//modal_anchor(get_uri("items/detail_mixing_category_modal"), $data->title, array("class" => "edit", "title" => lang('item_mixing_edit'), "data-post-id" => $data->id, "data-post-item_id" => $data->id))
	);
	$row_data[] = modal_anchor(get_uri("items/detail_mixingcategory_modal"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_category'), "data-post-id" => $data->id, "data-post-item_id" => $data->id))
	. js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete_category'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("items/detail_mixingcategory_delete"), "data-action" => "delete-confirmation"));
	return $row_data;
	} */

	/*function detail_mixingcategory_modal() {
	$this->check_module_availability("module_stock");
	
	$view_data['can_read'] = $this->check_permission('bom_material_read');
	$view_data['can_read_production_name'] = $this->check_permission('bom_material_read_production_name');
	if(!$this->login_user->is_admin && (!$view_data['can_read'] || !$view_data['can_read_production_name'])) {
	redirect("forbidden");
	}
	$id = $this->input->post('id');
	//$item_id = $this->input->post('item_id');
	validate_submitted_data(array(
	"id" => "numeric",
	//"item_id" => "required|numeric"
	));
	
	$view_data['label_column'] = "col-md-3";
	$view_data['field_column'] = "col-md-9";
	
	//$view_data["view"] = $this->input->post('view');
	$view_data['model_info'] = $this->Bom_item_mixing_groups_model->get_category_details(['id'=>$id])->row();
	//$view_data['item'] = $this->Items_model->get_one($item_id);
	//$view_data['material_dropdown'] = $this->Bom_materials_model->get_details([])->result();
	//$view_data['clients_dropdown'] = $this->Clients_model->get_dropdown_list(array("company_name"), "id", array("is_lead" => 0));
	// if(!empty($id)){
	//     $view_data['material_mixings'] = $this->Bom_item_mixing_groups_model->get_mixings([
	//         'group_id' => $id
	//     ])->result();
	// }
	
	// if (empty($view_data['model_info']->item_id)) {
	//     $view_data['model_info']->item_id = $item_id;
	//     $view_data['model_info']->is_public = 1;
	// }
	
	$this->load->view('items/detail/modal_mixingcategory', $view_data);
	}*/

	function detail_mixing_modal()
	{
		/*$this->check_module_availability("module_stock");
		
		$view_data['can_read'] = $this->check_permission('bom_material_read');
		$view_data['can_read_production_name'] = $this->check_permission('bom_material_read_production_name');
		if(!$this->login_user->is_admin && (!$view_data['can_read'] || !$view_data['can_read_production_name'])) {
		redirect("forbidden");
		}*/

		$id = $this->input->post('id');
		$item_id = $this->input->post('item_id');
		validate_submitted_data(
			array(
				"id" => "numeric",
				"item_id" => "required|numeric"
			)
		);

		$view_data['label_column'] = "col-md-3";
		$view_data['field_column'] = "col-md-9";

		$view_data["view"] = $this->input->post('view');
		$view_data['model_info'] = $this->Bom_item_mixing_groups_model->get_one($id);
		$view_data['item'] = $this->Items_model->get_one($item_id);

		$view_data['material_dropdown'] = $this->Bom_materials_model->get_details([])->result();
		$view_data['clients_dropdown'] = $this->Clients_model->get_dropdown_list(array("company_name"), "id", array("is_lead" => 0));
		$view_data['categories_dropdown'] = $this->Bom_item_mixing_groups_model->get_categories_list();
		$items = $this->Items_model->get_details()->result();
		$view_data['items_dropdown'] = ["" => "- เลือกสินค้า -"];
		foreach ($items as $item) {
			$view_data['items_dropdown'][$item->id] = $item->title;
		}

		$view_data['material_mixings'] = [];
		$view_data['material_cat_mixings'] = [];
		$view_data['bom_material_read_production_name'] = $this->check_permission('bom_material_read_production_name');

		if (!empty($id)) {
			$view_data['material_mixings'] = $this->Bom_item_mixing_groups_model->get_mixings(['group_id' => $id])->result();
			foreach ($view_data['material_mixings'] as $mx) {
				if (!isset($view_data['material_cat_mixings'][$mx->cat_id])) {
					$view_data['material_cat_mixings'][$mx->cat_id] = [];
				}
				$view_data['material_cat_mixings'][$mx->cat_id][] = $mx;
			}
		}

		if (empty($view_data['model_info']->item_id)) {
			$view_data['model_info']->item_id = $item_id;
			$view_data['model_info']->is_public = 1;
		}

		$this->load->view('items/detail/modal_mixing', $view_data);
	}

	function detail_mixing_save()
	{
		/*$this->check_module_availability("module_stock");
		
		$view_data['can_read'] = $this->check_permission('bom_material_read');
		$view_data['can_read_production_name'] = $this->check_permission('bom_material_read_production_name');
		if(!$this->login_user->is_admin && (!$view_data['can_read'] || !$view_data['can_read_production_name'])) {
		echo json_encode(array("success" => false, 'message' => lang('no_permissions'))); exit;
		}*/

		$id = $this->input->post('id');
		$item_id = $o_item_id = $this->input->post('item_id');
		//var_dump($this->input->post('cat_id'));exit;
		validate_submitted_data(
			array(
				"id" => "numeric",
				"item_id" => "required|numeric",
				//"cat_id" => "required",
				"name" => "required",
				"ratio" => "required|numeric"
			)
		);

		//$item_new_id = $item_id;
		$clone_to_new_item = $this->input->post('clone_to_new_item');

		$is_public = $this->input->post('is_public');


		if ($clone_to_new_item) {
			$target_path = get_setting("timeline_file_path");
			$item = $this->Items_model->get_one($item_id);
			$new_files = unserialize($item->files);
			$files_data = copy_files($new_files, $target_path, "item");
			$new_files = unserialize($files_data);
			$item_data["files"] = serialize($new_files);
			$item_data = array(
				"title" => $item->title . '[COPY]',
				"description" => $item->description,
				"category_id" => $item->category_id,
				"unit_type" => $item->unit_type,
				"rate" => $item->rate,
				"show_in_client_portal" => 0,
				"files" => serialize($new_files)
			);
			$item_id = $new_item_id = $this->Items_model->save($item_data, 0);
			$item = $this->Items_model->get_one($item_id);
		}

		$data = array(
			"item_id" => $item_id,
			"name" => $this->input->post('name'),
			"ratio" => $this->input->post('ratio'),
			"is_public" => $is_public,
			"for_client_id" => $is_public == 1 ? null : $this->input->post('for_client_id')
		);
		$data = clean_data($data);
		$save_id = $this->Bom_item_mixing_groups_model->save($data, $id);
		$material_ids = $this->input->post('material_id[]');
		$cat_ids = $this->input->post('cat_id[]');
		$ratios = $this->input->post('mixing_ratio[]');
		$this->Bom_item_mixing_groups_model->mixing_save($save_id, $material_ids, $cat_ids, $ratios);

		if ($save_id) {
			echo json_encode(
				array(
					"success" => true,
					"data" => $this->_detail_mixing_row_data($save_id),
					'id' => $save_id,
					'view' => $this->input->post('view'),
					'message' => lang('record_saved')
				)
			);
		} else {
			echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
		}
	}

	/*function detail_mixingcategory_save() {
	$this->check_module_availability("module_stock");
	
	$view_data['can_read'] = $this->check_permission('bom_material_read');
	$view_data['can_read_production_name'] = $this->check_permission('bom_material_read_production_name');
	if(!$this->login_user->is_admin && (!$view_data['can_read'] || !$view_data['can_read_production_name'])) {
	echo json_encode(array("success" => false, 'message' => lang('no_permissions'))); exit;
	}
	
	$id = $this->input->post('id');
	validate_submitted_data(array(
	"id" => "numeric",
	//"item_id" => "required|numeric",
	"title" => "required",
	//"ratio" => "required|numeric"
	));
	
	$is_public = $this->input->post('is_public');
	$data = array(
	//"item_id" => $this->input->post('item_id'),
	"title" => $this->input->post('title'),
	//"ratio" => $this->input->post('ratio'),
	//"is_public" => $is_public,
	//"for_client_id" => $is_public == 1? null: $this->input->post('for_client_id')
	);
	$data = clean_data($data);
	$save_id = $this->Bom_item_mixing_groups_model->savecategorry($data, $id);
	
	$material_ids = $this->input->post('material_id[]');
	$ratios = $this->input->post('mixing_ratio[]');
	$this->Bom_item_mixing_groups_model->mixing_save($save_id, $material_ids, $ratios);
	
	if ($save_id) {
	echo json_encode(array(
	"success" => true, 
	"data" => $this->_detail_mixingcategory_row_data($save_id), 
	'id' => $save_id, 
	'message' => lang('record_saved')
	));
	} else {
	echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
	}
	}*/

	function detail_mixing_delete()
	{
		/*$this->check_module_availability("module_stock");
		
		$view_data['can_read'] = $this->check_permission('bom_material_read');
		$view_data['can_read_production_name'] = $this->check_permission('bom_material_read_production_name');
		if(!$this->login_user->is_admin && (!$view_data['can_read'] || !$view_data['can_read_production_name'])) {
		echo json_encode(array("success" => false, 'message' => lang('no_permissions'))); exit;
		}*/

		validate_submitted_data(
			array(
				"id" => "required|numeric"
			)
		);

		$id = $this->input->post('id');
		if ($this->Bom_item_mixing_groups_model->delete_mixing($id)) {
			echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
		} else {
			echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
		}
	}

	/*function detail_mixingcategory_delete() {
	$this->check_module_availability("module_stock");
	
	$view_data['can_read'] = $this->check_permission('bom_material_read');
	$view_data['can_read_production_name'] = $this->check_permission('bom_material_read_production_name');
	if(!$this->login_user->is_admin && (!$view_data['can_read'] || !$view_data['can_read_production_name'])) {
	echo json_encode(array("success" => false, 'message' => lang('no_permissions'))); exit;
	}
	
	validate_submitted_data(array(
	"id" => "required|numeric"
	));
	
	$id = $this->input->post('id');
	if ($this->Bom_item_mixing_groups_model->delete_mixingcategory($id)) {
	echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
	} else {
	echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
	}
	}*/

	//load items list view

	private function _store_headers_position($headers_row = array())
	{
		$allowed_headers = $this->_get_allowed_headers();

		//check if all headers are correct and on the right position
		$final_headers = array();
		foreach ($headers_row as $key => $header) {
			$key_value = str_replace(' ', '_', strtolower($header));
			$header_on_this_position = get_array_value($allowed_headers, $key);
			$header_array = array("key_value" => $header_on_this_position, "value" => $header);

			if ($header_on_this_position == $key_value) {
				//allowed headers
				//the required headers should be on the correct positions
				//the rest headers will be treated as custom fields
				//pushed header at last of this loop
			} else if ((count($allowed_headers) - 1) < $key) {
				//custom fields headers
				//check if there is any existing custom field with this title
				if (!$this->_get_existing_custom_field_id($header)) {
					$header_array["has_error"] = true;
					$header_array["custom_field"] = true;
				}
			} else { //invalid header, flag as red
				$header_array["has_error"] = true;
			}

			array_push($final_headers, $header_array);
		}

		return $final_headers;
	}
	
	function validate_import_clients_file_data($check_on_submit = false)
	{
		//$this->access_only_allowed_members();

		$table_data = "";
		$error_message = "";
		$headers = array();
		$got_error_header = false; //we've to check the valid headers first, and a single header at a time
		$got_error_table_data = false;

		$file_name = $this->input->post("file_name");

		require_once(APPPATH . "third_party/php-excel-reader/SpreadsheetReader.php");

		$temp_file_path = get_setting("temp_file_path");
		$excel_file = new SpreadsheetReader($temp_file_path . $file_name);

		$table_data .= '<table class="table table-responsive table-bordered table-hover" style="width: 100%; color: #444;">';

		$table_data_header_array = array();
		$table_data_body_array = array();

		foreach ($excel_file as $row_key => $value) {
			if ($row_key == 0) { //validate headers
				$headers = $this->_store_headers_position($value);

				foreach ($headers as $row_data) {
					$has_error_class = false;
					if (get_array_value($row_data, "has_error") && !$got_error_header) {
						//$has_error_class = true;
						//$got_error_header = true;

						if (get_array_value($row_data, "custom_field")) {
							$error_message = lang("no_such_custom_field_found");
						} else {
							$error_message = sprintf(lang("import_client_error_header"), lang(get_array_value($row_data, "key_value")));
						}
					}

					array_push($table_data_header_array, array("has_error_class" => $has_error_class, "value" => get_array_value($row_data, "value")));
				}
			} else { //validate data
				$error_message_on_this_row = "<ol class='pl15'>";
				$has_contact_first_name = get_array_value($value, 1) ? true : false;

				foreach ($value as $key => $row_data) {
					$has_error_class = false;

					if (!$got_error_header) {
						$row_data_validation = $this->_row_data_validation_and_get_error_message($key, $row_data, $has_contact_first_name);
						if ($row_data_validation) {
							$has_error_class = true;
							$error_message_on_this_row .= "<li>" . $row_data_validation . "</li>";
							$got_error_table_data = true;
						}
					}

					$table_data_body_array[$row_key][] = array("has_error_class" => $has_error_class, "value" => $row_data);
				}

				$error_message_on_this_row .= "</ol>";

				//error messages for this row
				if ($got_error_table_data) {
					$table_data_body_array[$row_key][] = array("has_error_text" => true, "value" => $error_message_on_this_row);
				}
			}
		}

		//return false if any error found on submitting file
		if ($check_on_submit) {
			//return ($got_error_header || $got_error_table_data) ? false : true;
		}

		//add error header if there is any error in table body
		if ($got_error_table_data) {
			array_push($table_data_header_array, array("has_error_text" => true, "value" => lang("error")));
		}

		//add headers to table
		$table_data .= "<tr>";
		foreach ($table_data_header_array as $table_data_header) {
			$error_class = get_array_value($table_data_header, "has_error_class") ? "error" : "";
			$error_text = get_array_value($table_data_header, "has_error_text") ? "text-danger" : "";
			$value = get_array_value($table_data_header, "value");
			$table_data .= "<th class='$error_class $error_text'>" . $value . "</th>";
		}
		$table_data .= "<tr>";

		//add body data to table
		foreach ($table_data_body_array as $table_data_body_row) {
			$table_data .= "<tr>";
			foreach ($table_data_body_row as $table_data_body_row_data) {
				$error_class = get_array_value($table_data_body_row_data, "has_error_class") ? "error" : "";
				$error_text = get_array_value($table_data_body_row_data, "has_error_text") ? "text-danger" : "";
				$value = get_array_value($table_data_body_row_data, "value");
				$table_data .= "<td class='$error_class $error_text'>" . $value . "</td>";
			}
			$table_data .= "<tr>";
		}

		//add error message for header
		if ($error_message) {
			$total_columns = count($table_data_header_array);
			$table_data .= "<tr><td class='text-danger' colspan='$total_columns'><i class='fa fa-warning'></i> " . $error_message . "</td></tr>";
		}

		$table_data .= "</table>";
		echo json_encode(array("success" => true, 'table_data' => $table_data, 'got_error' => false));
	}
	
	function validate_import_clients_file()
	{
		$this->access_only_allowed_members();

		$file_name = $this->input->post("file_name");
		$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
		if (!is_valid_file_to_upload($file_name)) {
			echo json_encode(array("success" => false, 'message' => lang('invalid_file_type')));
			exit();
		}

		if ($file_ext == "xlsx") {
			echo json_encode(array("success" => true));
		} else {
			echo json_encode(array("success" => false, 'message' => lang('please_upload_a_excel_file') . " (.xlsx)"));
		}
	}

	function import_modal_form()
	{
		if ($this->Permission_m->create_product_item != true) {
			echo permissionBlock();
		}
	}

	function gogo($params = array())
	{
		if (false) {
			foreach ($_SESSION['file_get_contents'] as $kc => $vc) {

				$params['file_name'] = 'assets/aaaaaaaaaa' . $_SESSION['user_id'] . '.xlsx';

				//file_put_contents( $params['file_name'], $vc );
				$params['table'] = $this->className;
				$params['configs']['category_id'] = array('call' => 'createNewItemCat');
				$this->dao->implodeExcel($params);

				//unlink( $params['file_name'] );
			}
		} else {
			if (isset($_SESSION['file_get_contents'])) {
				foreach ($_SESSION['file_get_contents'] as $kc => $vc) {
					$params['file_name'] = 'assets/aaaaaaaaaa' . $_SESSION['user_id'] . '.xlsx';
					file_put_contents($params['file_name'], $vc);
					
					$params['table'] = $this->className;
					$params['configs']['category_id'] = array('call' => 'createNewItemCat');
					
					$this->dao->implodeExcel($params);
					unlink($params['file_name']);
				}
				unset($_SESSION['file_get_contents']);
			} else {
				echo json_encode(array("success" => false, 'message' => lang('please_upload_a_excel_file') . " (.xlsx)"));
			}
		}
	}

	function upload_excel_file()
	{
		unset($_SESSION['file_get_contents']);
		foreach ($_FILES as $kf => $vf) {
			$_SESSION['file_get_contents'][] = file_get_contents($vf['tmp_name']);
		}
	}

	function index()
	{
		/*$buttonTop[] = '<a href="#" class="btn btn-default mb0" title="จัดการคำกำกับ'. lang( $this->getRolePermission['table_name'] ) .'" data-post-type="'. $this->getRolePermission['table_name'] .'" data-act="ajax-modal" data-title="จัดการคำกำกับ'. lang( $this->getRolePermission['table_name'] ) .'" data-action-url="'. base_url( 'index.php/labels/modal_form' ) .'"><i class="fa fa-tags"></i> จัดการคำกำกับ'. lang( $this->getRolePermission['table_name'] ) .'</a>';*/
		$buttonTop = [];

		if ($this->Permission_m->create_product_item == true) {
			/*$buttonTop[] = modal_anchor(get_uri("". $this->className ."/import_modal_form"), "<i class='fa fa-upload'></i> " . 'นำเข้าข้อมูลสินค้า', array("class" => "btn btn-default", "title" =>  'นำเข้าข้อมูลสินค้า'  ));*/
			$buttonTop[] = modal_anchor(get_uri("" . $this->className . "/modal_form"), "<i class='fa fa-plus-circle'></i> " . lang('add_item'), array("class" => "btn btn-default", "title" => lang('add_item')));
		}

		$view_data['buttonTop'] = implode('', $buttonTop);
		$view_data['categories_dropdown'] = $this->_get_categories_dropdown();
		$this->template->rander("items/index", $view_data);
	}

	function list_data()
	{
		$category_id = $this->input->post('category_id');
		$options = array("category_id" => $category_id);

		$result = array();

		//$fs = $this->getRolePermission["filters"]["WHERE"];

		//$this->db->select("*, title AS category_title")->from("items");
		$this->db->select("items.*, item_categories.title AS category_title")
					->from("items")
					->join("item_categories", "items.category_id = item_categories.id", "left")
					->where("item_type", $this->item_type)
					->where("items.deleted", "0");

		if($category_id)$this->db->where("items.category_id", $category_id);

		if(!empty($fs)){
			foreach($fs as $f){
				$this->db->where($f);
			}
		}

		$list_data = $this->db->get()->result();

		//$list_data = $this->Items_model->get_details($options, $this->getRolePermission)->result();//log_message("error", $this->db->last_query());
		
		foreach ($list_data as $data) 
		{
			$type = $data->unit_type ? $data->unit_type : "";
			$show_in_client_portal_icon = "";
			if ($data->show_in_client_portal && $this->cp('orders', 'view_row')) {
				$show_in_client_portal_icon = "<i title='" . lang("showing_in_client_portal") . "' class='fa fa-shopping-basket'></i> ";
			}
			$images = @unserialize($data->files);
			if (is_array($images) && sizeof($images)) {
				$preview = '<img class="product-preview" src="' . base_url('files/timeline_files/' . $images[sizeof($images) - 1]['file_name']) . '" />';
			}

			$buttons = array();
			if (empty($this->getRolePermission['read_only'])) {

				$buttons[] = modal_anchor(get_uri("" . $this->className . "/modal_form"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_item'), "data-post-id" => $data->id)) 
				. 
				js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("" . $this->className . "/delete"), "data-action" => "delete"));

			}

			$result[] = $this->_make_item_row($data);
		}

		echo json_encode(array("data" => $result));
	}
}

/* End of file items.php */
/* Location: ./application/controllers/items.php */