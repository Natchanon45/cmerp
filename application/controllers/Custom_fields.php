<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Custom_fields extends MY_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->access_only_admin();
	}

	function index()
	{
		redirect("custom_fields/view");
	}

	function view($tab = "leads")
	{
		$view_data["tab"] = $tab;
		$this->template->rander("custom_fields/settings/index", $view_data);
	}

	//add/edit fields
	function modal_form()
	{

		$model_info = $this->Custom_fields_model->get_one($this->input->post('id'));
		$related_to = $model_info->related_to;
		if (!$related_to) {
			$related_to = $this->input->post("related_to");
		}
		$view_data['model_info'] = $model_info;
		$view_data['related_to'] = $related_to;

		$this->load->view('custom_fields/settings/modal_form', $view_data);
	}

	function modal_form_leads()
	{
		$model_info = $this->Custom_fields_model->get_leads_custom_fields_by_id($this->input->post("id"))->result();
		$model_info = $this->realization_leads_custom_field($model_info);

		$view_data["model_info"] = $model_info[0];
		$view_data["related_to"] = "leads";

		$this->load->view("custom_fields/settings/modal_form", $view_data);
	}

	private function realization_leads_custom_field($custom_fields)
	{
		$index = 0;
		$list_data = array();

		if (sizeof($custom_fields)) {
			foreach ($custom_fields as $field) {
				$data_options = "";
				if (isset($field->options)) {
					$data_options = implode(",", json_decode($field->options));
				}
				
				$list_data[$index] = new stdClass();
				$list_data[$index]->id = $field->code;
				$list_data[$index]->title = $field->title;
				$list_data[$index]->placeholder = $field->placeholder;
				$list_data[$index]->example_variable_name = "";
				$list_data[$index]->options = $data_options;
				$list_data[$index]->field_type = $field->field_type;
				$list_data[$index]->related_to = "leads";
				$list_data[$index]->sort = $field->sort;
				$list_data[$index]->required = $field->required == "Y" ? 1 : 0;
				$list_data[$index]->show_in_table = $field->show_in_table == "Y" ? 1 : 0;
				$list_data[$index]->show_in_invoice = "0";
				$list_data[$index]->show_in_estimate = "0";
				$list_data[$index]->show_in_order = "0";
				$list_data[$index]->visible_to_admins_only = 0;
				$list_data[$index]->hide_from_clients = $field->show_in_client == "Y" ? 1 : 0;
				$list_data[$index]->disable_editing_by_clients = $field->show_in_lead == "Y" ? 1 : 0;
				$list_data[$index]->show_on_kanban_card = $field->show_on_kanban == "Y" ? 1 : 0;
				$list_data[$index]->deleted = 0;

				$index++;
			}
		}
		return $list_data;
	}

	private function toJsonOptions($data)
	{
		return json_encode(explode(",", $data));
	}
	
	// save/update custom field
	function save()
	{
		$id = $this->input->post("id");
		$related_to = $this->input->post("related_to");

		$validate = array(
			"title" => "required",
			"related_to" => "required"
		);

		// field type is required when inserting
		if (!$id) {
			$validate["field_type"] = "required";
		}

		if ($related_to != "leads") {
			$validate["id"] = "numeric";
		}

		validate_submitted_data($validate);

		if ($related_to == "leads") {
			$data = array(
				"title" => $this->input->post("title"),
				"placeholder" => $this->input->post("placeholder"),
				"options" => $this->input->post("options") ? $this->toJsonOptions($this->input->post("options")) : "",
				"required" => $this->input->post("required") ? "Y" : "N",
				"show_in_table" => $this->input->post("show_in_table") ? "Y" : "N",
				"show_on_kanban" => $this->input->post("show_on_kanban_card") ? "Y" : "N",
				"show_in_lead" => $this->input->post("show_in_lead") ? "Y" : "N",
				"show_in_client" => $this->input->post("show_in_client") ? "Y" : "N",
				"status" => "E"
			);
		} else {
			$data = array(
				"title" => $this->input->post('title'),
				"placeholder" => $this->input->post('placeholder'),
				"example_variable_name" => strtoupper($this->input->post('example_variable_name')),
				"required" => $this->input->post('required') ? 1 : 0,
				"show_in_table" => $this->input->post('show_in_table') ? 1 : 0,
				"show_in_invoice" => $this->input->post('show_in_invoice') ? 1 : 0,
				"show_in_estimate" => $this->input->post('show_in_estimate') ? 1 : 0,
				"show_in_order" => $this->input->post('show_in_order') ? 1 : 0,
				"visible_to_admins_only" => $this->input->post('visible_to_admins_only') ? 1 : 0,
				"hide_from_clients" => $this->input->post('hide_from_clients') ? 1 : 0,
				"disable_editing_by_clients" => $this->input->post('disable_editing_by_clients') ? 1 : 0,
				"show_on_kanban_card" => $this->input->post('show_on_kanban_card') ? 1 : 0,
				"related_to" => $this->input->post('related_to'),
				"options" => $this->input->post('options') ? $this->input->post('options') : ""
			);
		}

		if (!$id) {
			$data["field_type"] = $this->input->post("field_type");
		}

		if (!$id) {
			// get sort value
			if ($related_to == "leads") {
				$max_sort_value = $this->Custom_fields_model->get_leads_custom_field_enable_sort()->sort;
			} else {
				$max_sort_value = $this->Custom_fields_model->get_max_sort_value($related_to);
			}
			
			$data["sort"] = $max_sort_value * 1 + 1; // increase sort value
		}

		if ($related_to == "leads") {
			if (!$id) {
				$id = $this->Custom_fields_model->get_leads_custom_field_disable();
				if (empty($id->code)) {
					echo json_encode(array("success" => false, "message" => lang("error_leads_max_cf")));
					return;
				} else {
					$save_id = $this->Custom_fields_model->post_leads_costom_field($data, $id->code);
					$info = $this->Custom_fields_model->get_leads_custom_fields_by_id($save_id)->result();
				}
			} else {
				$save_id = $this->Custom_fields_model->post_leads_costom_field($data, $id);
				$info = $this->Custom_fields_model->get_leads_custom_fields_by_id($save_id)->result();
			}
			
			$realize = $this->realization_leads_custom_field($info);
			$save_data = $this->_make_field_row($realize[0]);
		} else {
			$save_id = $this->Custom_fields_model->save($data, $id);
			$save_data = $this->_row_data($save_id);
		}

		if ($save_data) {
			echo json_encode(array("success" => true, "data" => $save_data, "newData" => $id ? false : true, "id" => $save_id, "message" => lang("record_saved")));
		} else {
			echo json_encode(array("success" => false, "message" => lang("error_occurred")));
		}
	}
	
	// prepare data for datatable for fields list
	function list_data($related_to)
	{
		// accessable from client and team members 

		$options = array("related_to" => $related_to);

		if ($related_to === "leads") {
			$custom_fields = $this->Custom_fields_model->get_leads_custom_fields()->result();
			$list_data = $this->realization_leads_custom_field($custom_fields);
		} else {
			$list_data = $this->Custom_fields_model->get_details($options)->result();
		}

		// var_dump(arr($list_data));
		// exit;

		$result = array();
		foreach ($list_data as $data) {
			$result[] = $this->_make_field_row($data);
		}
		echo json_encode(array("data" => $result));
	}

	// get a row of fields list
	private function _row_data($id)
	{
		$options = array("id" => $id);
		$data = $this->Custom_fields_model->get_details($options)->row();
		return $this->_make_field_row($data);
	}

	// prepare a row of fields list
	private function _make_field_row($data)
	{
		$required = "";
		if ($data->required) {
			$required = "*";
		}

		$field = "<label for='custom_field_$data->id' data-id='$data->id' class='field-row'>$data->title $required</label>";
		$field .= "<div class='form-group'>" . $this->load->view("custom_fields/input_" . $data->field_type, array("field_info" => $data), true) . "</div>";

		if ($data->related_to == "leads") {
			$button = modal_anchor(get_uri("custom_fields/modal_form_leads/"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_field'), "data-post-id" => $data->id))
			. js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete_field'), "class" => "delete", "data-id" => $data->id, "data-related" => $data->related_to, "data-action-url" => get_uri("custom_fields/delete_leads"), "data-action" => "delete"));
		} else {
			$button = modal_anchor(get_uri("custom_fields/modal_form/"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_field'), "data-post-id" => $data->id))
			. js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete_field'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("custom_fields/delete"), "data-action" => "delete"));
		}
		
		return array(
			$field,
			$data->sort,
			$button
		);
	}

	// update the sort value for the fields
	function update_field_sort_values($id = 0)
	{
		$sort_values = $this->input->post("sort_values");

		if ($sort_values) {

			// extract the values from the comma separated string
			$sort_array = explode(",", $sort_values);

			// update the value in db
			foreach ($sort_array as $value) {
				$sort_item = explode("-", $value); // extract id and sort value

				$id = get_array_value($sort_item, 0);
				$sort = get_array_value($sort_item, 1);

				$data = array("sort" => $sort);
				$this->Custom_fields_model->save($data, $id);
			}
		}
	}

	function update_field_sort_values_leads($id = 0)
	{
		$sort_values = $this->input->post("sort_values");

		if ($sort_values) {
			$sort_array = explode(",", $sort_values);

			foreach ($sort_array as $value) {
				$sort_item = explode("-", $value);
				$id = get_array_value($sort_item, 0);
				$sort = get_array_value($sort_item, 1);
				
				$this->Custom_fields_model->post_leads_costom_field_sort($sort, $id);
			}
		}
	}

	//delete/undo field
	function delete()
	{
		validate_submitted_data(
			array(
				"id" => "required|numeric"
			)
		);

		$id = $this->input->post("id");

		if ($this->input->post('undo')) {
			if ($this->Custom_fields_model->delete($id, true)) {
				echo json_encode(array("success" => true, "data" => $this->_row_data($id), "message" => lang('record_undone')));
			} else {
				echo json_encode(array("success" => false, lang('error_occurred')));
			}
		} else {
			if ($this->Custom_fields_model->delete($id)) {
				echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
			} else {
				echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
			}
		}
	}

	function delete_leads()
	{
		validate_submitted_data(
			array(
				"id" => "required"
			)
		);

		$id = $this->input->post("id");

		if ($this->input->post("undo")) {
			if ($this->Custom_fields_model->delete_leads_costom_field($id, true)) {
				$info = $this->Custom_fields_model->get_leads_custom_fields_by_id($id)->result();
				$realize = $this->realization_leads_custom_field($info);
				$save_data = $this->_make_field_row($realize[0]);

				echo json_encode(array("success" => true, "data" => $save_data, "message" => lang("record_undone")));
			} else {
				echo json_encode(array("success" => false, lang("error_occurred")));
			}
		} else {
			if ($this->Custom_fields_model->delete_leads_costom_field($id)) {
				echo json_encode(array("success" => true, "message" => lang("record_deleted")));
			} else {
				echo json_encode(array("success" => false, "message" => lang("record_cannot_be_deleted")));
			}
		}
	}

	function leads()
	{
		$this->load->view('custom_fields/settings/leads');
	}

	function client_contacts()
	{
		$this->load->view('custom_fields/settings/client_contacts');
	}

	function lead_contacts()
	{
		$this->load->view('custom_fields/settings/lead_contacts');
	}

	function projects()
	{
		$this->load->view('custom_fields/settings/projects');
	}

	function tasks()
	{
		$this->load->view('custom_fields/settings/tasks');
	}

	function team_members()
	{
		$this->load->view('custom_fields/settings/team_members');
	}

	function tickets()
	{
		$this->load->view('custom_fields/settings/tickets');
	}

	function invoices()
	{
		$this->load->view('custom_fields/settings/invoices');
	}

	function events()
	{
		$this->load->view('custom_fields/settings/events');
	}

	function expenses()
	{
		$this->load->view('custom_fields/settings/expenses');
	}

	function estimates()
	{
		$this->load->view('custom_fields/settings/estimates');
	}

	function orders()
	{
		$this->load->view('custom_fields/settings/orders');
	}

	function timesheets()
	{
		$this->load->view('custom_fields/settings/timesheets');
	}
}

/* End of file custom_fields.php */
/* Location: ./application/controllers/custom_fields.php */