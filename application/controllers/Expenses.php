<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Expenses extends MY_Controller 
{
    function __construct()
    {
        parent::__construct();

        $this->init_permission_checker("expense");
        $this->access_only_allowed_members();

        $this->load->model("Account_category_model");
        $this->load->model("Payment_voucher_m");
    }

    //load the expenses list view
    function index() {
        $this->check_module_availability("module_expense");

        $view_data["custom_field_headers"] = $this->Custom_fields_model->get_custom_field_headers_for_table("expenses", $this->login_user->is_admin, $this->login_user->user_type);
        $view_data["categories_dropdown"] = $this->_get_categories_dropdown();
        $view_data["members_dropdown"] = $this->_get_team_members_dropdown();
        $view_data["projects_dropdown"] = $this->_get_projects_dropdown_for_income_and_epxenses("expenses");
        
        $view_data["supplier_dropdown"] = $this->_get_expenses_supplier_dropdown();
        $view_data["account_secondary_dropdown"] = $this->_get_account_secondary_dropdown();
        $view_data["account_category_dropdown"] = $this->_get_account_category_dropdown();
        
        $buttonTop[] = modal_anchor(get_uri("labels/modal_form"), "<i class='fa fa-tags'></i> " . lang('manage_labels'), array("class" => "btn btn-default mb0", "title" => lang('manage_labels'), "data-post-type" => $_SESSION['table_name'] ));
        $view_data["buttonTop"] = implode( '', $buttonTop );
        
        // var_dump(arr($view_data)); exit();
        $this->template->rander("expenses/index", $view_data);
    }

    private function _get_expenses_supplier_dropdown()
    {
        $suppliers = $this->Bom_suppliers_model->dev2_getSuppliersList();
        $supplier_dropdown = array(
            array("id" => "0", "text" => "- " . lang("suppliers") . " -")
        );

        if (sizeof($suppliers)) {
            foreach ($suppliers as $supplier) {
                $supplier_dropdown[] = array("id" => $supplier->id, "text" => $supplier->company_name);
            }
        }

        return json_encode($supplier_dropdown);
    }

    private function _get_account_secondary_dropdown()
    {
        $account_secondary = $this->Account_category_model->dev2_getExpenseSecondaryList();
        $secondary_dropdown = array(
            array("id" => "", "text" => "- " . lang("account_sub_type") . " -")
        );

        if (sizeof($account_secondary)) {
            foreach ($account_secondary as $secondary) {
                $secondary_dropdown[] = array("id" => $secondary->id, "text" => $secondary->thai_name . " (" . $secondary->account_code . ")");
            }
        }

        return json_encode($secondary_dropdown);
    }

    private function _get_account_category_dropdown()
    {
        $account_category = $this->Account_category_model->dev2_getExpenseCategoryList();
        $categories_dropdown = array(
            array("id" => "", "text" => "- " . lang("account_expense") . " -")
        );

        if (sizeof($account_category)) {
            foreach ($account_category as $category) {
                $categories_dropdown[] = array("id" => $category->id, "text" => $category->account_code . " - " . $category->thai_name);
            }
        }

        return json_encode($categories_dropdown);
    }

    //get categories dropdown
    private function _get_categories_dropdown() {
        $categories = $this->Expense_categories_model->get_all_where(array("deleted" => 0), 0, 0, "title")->result();

        $categories_dropdown = array(array("id" => "", "text" => "- " . lang("category") . " -"));
        foreach ($categories as $category) {
            $categories_dropdown[] = array("id" => $category->id, "text" => $category->title);
        }

        return json_encode($categories_dropdown);
    }

    //get team members dropdown
    private function _get_team_members_dropdown() {
        $team_members = $this->Users_model->get_all_where(array("deleted" => 0, "user_type" => "staff"), 0, 0, "first_name")->result();

        $members_dropdown = array(array("id" => "", "text" => "- " . lang("member") . " -"));
        foreach ($team_members as $team_member) {
            $members_dropdown[] = array("id" => $team_member->id, "text" => $team_member->first_name . " " . $team_member->last_name);
        }

        return json_encode($members_dropdown);
    }

    //load the expenses list yearly view
    function yearly() {
        $this->load->view("expenses/yearly_expenses");
    }

    //load custom expenses list
    function custom() {
        $this->load->view("expenses/custom_expenses");
    }

    //load the recurring view of expense list 
    function recurring() {
        $this->load->view("expenses/recurring_expenses_list");
    }

    //load the add/edit expense form
    function modal_form() {
		
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
		
		
		
        validate_submitted_data(array(
            "id" => "numeric"
        ));

        $client_id = $this->input->post('client_id');

        $model_info = $this->Expenses_model->get_one($this->input->post('id'));
        $view_data['categories_dropdown'] = $this->Expense_categories_model->get_dropdown_list(array("title"));

        $team_members = $this->Users_model->get_all_where(array("deleted" => 0, "user_type" => "staff"))->result();
        $members_dropdown = array();

        foreach ($team_members as $team_member) {
            $members_dropdown[$team_member->id] = $team_member->first_name . " " . $team_member->last_name;
        }

        $view_data['members_dropdown'] = array("0" => "-") + $members_dropdown;
        $view_data['clients_dropdown'] = array("" => "-") + $this->Clients_model->get_dropdown_list(array("company_name"), "id", array("is_lead" => 0));
        $view_data['projects_dropdown'] = array("0" => "-") + $this->Projects_model->get_dropdown_list(array("title"));
        $view_data['taxes_dropdown'] = array("" => "-") + $this->Taxes_model->get_dropdown_list(array("title"));

        $model_info->project_id = $model_info->project_id ? $model_info->project_id : $this->input->post('project_id');
        $model_info->user_id = $model_info->user_id ? $model_info->user_id : $this->input->post('user_id');

        $view_data['model_info'] = $model_info;
        $view_data['client_id'] = $client_id;

        $view_data['can_access_expenses'] = $this->can_access_expenses();
        $view_data['can_access_clients'] = $this->can_access_clients();

        $view_data['custom_fields'] = $this->Custom_fields_model->get_combined_details("expenses", $view_data['model_info']->id, $this->login_user->is_admin, $this->login_user->user_type)->result();

        // account category new
        $view_data["expense_secondary"] = $this->Account_category_model->dev2_getExpenseSecondaryList();
        $view_data["expense_category"] = json_encode($this->Account_category_model->dev2_getExpenseCategoryList());
        
        if (isset($view_data["model_info"]->account_category_id) && !empty($view_data["model_info"]->account_category_id)) {
            $view_data["account_category_info"] = $this->Account_category_model->dev2_selectDataListByColumnIndex("account_category", "id", $view_data["model_info"]->account_category_id)[0];
            $view_data["account_secondary_info"] = $this->Account_category_model->dev2_selectDataListByColumnIndex("account_secondary", "id", $view_data["account_category_info"]->secondary_id)[0];
        }

        // supplier dropdown
        $view_data["supplier_dropdown"] = array("0" => "-") + $this->Bom_suppliers_model->dev2_get_dropdown_list();
        
        // var_dump(arr($view_data)); exit();
        $this->load->view('expenses/modal_form', $view_data);
    }

    //save an expense
    function save()
    {	
        validate_submitted_data(
            array(
                "id" => "numeric",
                "expense_date" => "required",
                "expense_secondary" => "required",
                "expense_category" => "required",
                "category_id" => "required",
                "sub_total" => "required"
            )
        );

        $id = $this->input->post('id');

        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "expense");
        $new_files = unserialize($files_data);

        $recurring = $this->input->post('recurring') ? 1 : 0;
        $expense_date = $this->input->post('expense_date');
        $repeat_every = $this->input->post('repeat_every');
        $repeat_type = $this->input->post('repeat_type');
        $no_of_cycles = $this->input->post('no_of_cycles');

        $sub_total = round(getNumber($this->input->post('sub_total')), 2);
        $vat_percent = round(getNumber($this->input->post('vat_percent')), 2);
        $wht_percent = round(getNumber($this->input->post('wht_percent')), 2);
        $vat_value = $wht_value = 0;

        if($wht_percent > 0) $wht_value = ($sub_total * $wht_percent) / 100;
        if($vat_percent > 0) $vat_value = ($sub_total * $vat_percent) / 100;

        $total = ($sub_total + $vat_value);
        $payment_amount = $total - $wht_value;

        $data = array(
            "expense_date" => $expense_date,
            "account_secondary_id" => $this->input->post('expense_secondary'),
            "account_category_id" => $this->input->post('expense_category'),
            "category_id" => $this->input->post('category_id'),
            "description" => $this->input->post('description'),
            "sub_total"=>$sub_total,
            "vat_percent"=>$vat_percent,
            "vat_value" => $vat_value,
            "total" => $total,
            "wht_percent" => $wht_percent,
            "wht_value" => $wht_value,
            "payment_amount" => $payment_amount,
            "title" => $this->input->post('title'),
            "supplier_id" => $this->input->post('supplier_id'),
            "project_id" => $this->input->post('expense_project_id'),
            "user_id" => $this->input->post('expense_user_id'),
            "client_id" => $this->input->post('expense_client_id') ? $this->input->post('expense_client_id') : 0,
            "recurring" => $recurring,
            "repeat_every" => $repeat_every ? $repeat_every : 0,
            "repeat_type" => $repeat_type ? $repeat_type : NULL,
            "no_of_cycles" => $no_of_cycles ? $no_of_cycles : 0,
        );

        $expense_info = $this->Expenses_model->get_one($id);

        // is editing? update the files if required
        if ($id) {
            $timeline_file_path = get_setting("timeline_file_path");
            $new_files = update_saved_files($timeline_file_path, $expense_info->files, $new_files);
        }

        $data["files"] = serialize($new_files);

        if ($recurring) {
            // set next recurring date for recurring expenses
            if ($id) {
                // update
                if ($this->input->post('next_recurring_date')) { 
                    // submitted any recurring date? set it.
                    $data['next_recurring_date'] = $this->input->post('next_recurring_date');
                } else {
                    // re-calculate the next recurring date, if any recurring fields has changed.
                    if ($expense_info->recurring != $data['recurring'] || $expense_info->repeat_every != $data['repeat_every'] || $expense_info->repeat_type != $data['repeat_type'] || $expense_info->expense_date != $data['expense_date']) {
                        $data['next_recurring_date'] = add_period_to_date($expense_date, $repeat_every, $repeat_type);
                    }
                }
            } else {
                // insert new
                $data['next_recurring_date'] = add_period_to_date($expense_date, $repeat_every, $repeat_type);
            }

            // recurring date must have to set a future date
            if (get_array_value($data, "next_recurring_date") && get_today_date() >= $data['next_recurring_date']) {
                echo json_encode(array("success" => false, 'message' => lang('past_recurring_date_error_message_title'), 'next_recurring_date_error' => lang('past_recurring_date_error_message'), "next_recurring_date_value" => $data['next_recurring_date']));
                return false;
            }
        }

        $save_id = $this->Expenses_model->save($data, $id);

        if ($save_id) {            
            save_custom_fields("expenses", $save_id, $this->login_user->is_admin, $this->login_user->user_type);
            echo json_encode(array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, 'message' => lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

    //delete/undo an expense
    function delete() {
		
		if( empty( $this->getRolePermission['delete_row'] ) ) {
			echo json_encode(array("success" => false, 'message' => 'คุณไม่มีสิทธิ์ในการลบข้อมูล' ));
			exit;
		}
		
        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->input->post('id');
        $expense_info = $this->Expenses_model->get_one($id);


        if ($this->Expenses_model->delete($id)) {
            //delete the files
            $file_path = get_setting("timeline_file_path");
            if ($expense_info->files) {
                $files = unserialize($expense_info->files);

                foreach ($files as $file) {
                    delete_app_files($file_path, array($file));
                }
            }

            echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
        }
    }

    //get the expnese list data

    //get a row of expnese list
    private function _row_data($id) {
        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("expenses", $this->login_user->is_admin, $this->login_user->user_type);
        $options = array("id" => $id, "custom_fields" => $custom_fields);
        $data = $this->Expenses_model->get_details($options)->row();
        return $this->_make_row($data, $custom_fields);
    }

    //prepare a row of expnese list
    private function _make_row($data, $custom_fields) {
        // $data->account_category_id;
        $sub_account = "-";
        $expense_account = "-";

        if ($data->account_category_id) {
            $account_category_info = $this->Account_category_model->dev2_selectDataListByColumnIndex("account_category", "id", $data->account_category_id)[0];
            if (!empty($account_category_info)) {
                $expense_account = $account_category_info->account_code . " - " . $account_category_info->thai_name;

                $account_sub_type_info = $this->Account_category_model->dev2_selectDataListByColumnIndex("account_secondary", "id", $account_category_info->secondary_id)[0];
                if (!empty($account_sub_type_info)) {
                    $sub_account = $account_sub_type_info->thai_name . " (" . $account_sub_type_info->account_code . ")";
                }
            }
        }

        $supplier_name = 0;
        if ($data->supplier_id) {
            $supplier_name = $this->Expenses_model->dev2_getSupplierNameById($data->supplier_id);
        }

        $description = $data->description;
        if ($supplier_name) {
            if ($description) {
                $description .= "<br />";
            }
            $description .= lang("suppliers") . ": " . $supplier_name;
        }

        if ($data->linked_client_name) {
            if ($description) {
                $description .= "<br />";
            }
            $description .= lang("client") . ": " . $data->linked_client_name;
        }

        if ($data->project_title) {
            if ($description) {
                $description .= "<br /> ";
            }
            $description .= lang("project") . ": " . $data->project_title;
        }

        if ($data->linked_user_name) {
            if ($description) {
                $description .= "<br /> ";
            }
            $description .= lang("team_member") . ": " . $data->linked_user_name;
        }

        if ($data->recurring) {
            // show recurring information
            $recurring_stopped = false;
            $recurring_cycle_class = "";
            if ($data->no_of_cycles_completed > 0 && $data->no_of_cycles_completed == $data->no_of_cycles) {
                $recurring_cycle_class = "text-danger";
                $recurring_stopped = true;
            }

            $cycles = $data->no_of_cycles_completed . "/" . $data->no_of_cycles;
            if (!$data->no_of_cycles) { // if not no of cycles, so it's infinity
                $cycles = $data->no_of_cycles_completed . "/&#8734;";
            }

            if ($description) {
                $description .= "<br /> ";
            }

            $description .= lang("repeat_every") . ": " . $data->repeat_every . " " . lang("interval_" . $data->repeat_type);
            $description .= "<br /> ";
            $description .= "<span class='$recurring_cycle_class'>" . lang("cycles") . ": " . $cycles . "</span>";

            if (!$recurring_stopped && (int) $data->next_recurring_date) {
                $description .= "<br /> ";
                $description .= lang("next_recurring_date") . ": " . format_to_date($data->next_recurring_date, false);
            }
        }

        if ($data->recurring_expense_id) {
            if ($description) {
                $description .= "<br /> ";
            }
            $description .= modal_anchor(get_uri("expenses/expense_details"), lang("original_expense"), array("title" => lang("expense_details"), "data-post-id" => $data->recurring_expense_id));
        }

        $files_link = "";
        if ($data->files) {
            $files = unserialize($data->files);
            if (count($files)) {
                foreach ($files as $key => $value) {
                    $file_name = get_array_value($value, "file_name");
                    $link = " fa fa-" . get_file_icon(strtolower(pathinfo($file_name, PATHINFO_EXTENSION)));
                    $files_link .= js_anchor(" ", array('title' => "", "data-toggle" => "app-modal", "data-sidebar" => "0", "class" => "pull-left font-22 mr10 $link", "title" => remove_file_prefix($file_name), "data-url" => get_uri("expenses/file_preview/" . $data->id . "/" . $key)));
                }
            }
        }
        
        $row_data = array(
            $data->expense_date,
            modal_anchor(get_uri("expenses/expense_details"), format_to_date($data->expense_date, false), array("title" => lang("expense_details"), "data-post-id" => $data->id)),
            $data->title,
            $supplier_name,
            $data->description,
            $data->linked_client_name,
            $sub_account,
            $expense_account,
            $files_link,
            to_decimal_format3($data->sub_total, 3),
            to_decimal_format3($data->payment_amount, 3),
            lang("THB")
        );

        foreach ($custom_fields as $field) {
            $cf_id = "cfv_" . $field->id;
            $row_data[] = $this->load->view("custom_fields/output_" . $field->field_type, array("value" => $data->$cf_id), true);
        }

        $buttons = "";

        if (!$data->pv_id) {
            $buttons = modal_anchor(
                get_uri("expenses/modal_form"),
                "<i class='fa fa-pencil'></i>",
                array(
                    "class" => "edit",
                    "title" => lang("edit_expense"),
                    "data-post-id" => $data->id
                )
            );

            $buttons .= modal_anchor(
                get_uri("expenses/pv_creation"),
                "<i class='fa fa-file-o'></i>",
                array(
                    "class" => "edit",
                    "title" => lang("payment_voucher_add"),
                    "data-post-id" => $data->id
                )
            );

            $buttons .= js_anchor(
                "<i class='fa fa-times fa-fw'></i>",
                array(
                    "title" => lang("delete_expense"),
                    "class" => "delete",
                    "data-id" => $data->id,
                    "data-action-url" => get_uri("expenses/delete"),
                    "data-action" => "delete-confirmation"
                )
            );
        }

        $row_data[] = $buttons;

        return $row_data;
    }

    function file_preview($id = "", $key = "") {
        if ($id) {
            $expense_info = $this->Expenses_model->get_one($id);
            $files = unserialize($expense_info->files);
            $file = get_array_value($files, $key);

            $file_name = get_array_value($file, "file_name");
            $file_id = get_array_value($file, "file_id");
            $service_type = get_array_value($file, "service_type");

            $view_data["file_url"] = get_source_url_of_file($file, get_setting("timeline_file_path"));
            $view_data["is_image_file"] = is_image_file($file_name);
            $view_data["is_google_preview_available"] = is_google_preview_available($file_name);
            $view_data["is_viewable_video_file"] = is_viewable_video_file($file_name);
            $view_data["is_google_drive_file"] = ($file_id && $service_type == "google") ? true : false;

            $this->load->view("expenses/file_preview", $view_data);
        } else {
            show_404();
        }
    }

    /* upload a file */

    function upload_file() {
        upload_file_to_temp();
    }

    /* check valid file for ticket */

    function validate_expense_file() {
        return validate_post_file($this->input->post("file_name"));
    }

    //load the expenses yearly chart view
    function yearly_chart() {
        $this->load->view("expenses/yearly_chart");
    }

    function yearly_chart_data() {

        $months = array("january", "february", "march", "april", "may", "june", "july", "august", "september", "october", "november", "december");
        $result = array();

        $year = $this->input->post("year");
        if ($year) {
            $expenses = $this->Expenses_model->get_yearly_expenses_chart($year);
            $values = array();
            foreach ($expenses as $value) {
                $values[$value->month - 1] = $value->total; //in array the month january(1) = index(0)
            }

            foreach ($months as $key => $month) {
                $value = get_array_value($values, $key);
                $result[] = array(lang("short_" . $month), $value ? $value : 0);
            }

            echo json_encode(array("data" => $result));
        }
    }

    function income_vs_expenses() {
        $view_data["projects_dropdown"] = $this->_get_projects_dropdown_for_income_and_epxenses();
        $this->template->rander("expenses/income_vs_expenses_chart", $view_data);
    }

    function income_vs_expenses_chart_data() {
        
        // arr( $this->getRolePermission['filters']);
        $year = $this->input->post("year");
        $project_id = $this->input->post("project_id");

        if ( $year ) {
			
            $expenses_data = $this->Expenses_model->get_yearly_expenses_chart( $year, $project_id );
			
            $payments_data = $this->Invoice_payments_model->get_yearly_payments_chart( $year, "", $project_id );

            $payments = array();
            $payments_array = array();

            $expenses = array();
            $expenses_array = array();

            for ($i = 1; $i <= 12; $i++) {
                $payments[$i] = 0;
                $expenses[$i] = 0;
            }

            foreach ($payments_data as $payment) {
                $payments[$payment->month] = $payment->total;
            }
            foreach ($expenses_data as $expense) {
                $expenses[$expense->month] = $expense->total;
            }

            foreach ($payments as $key => $payment) {
                $payments_array[] = array($key, $payment);
            }

            foreach ($expenses as $key => $expense) {
                $expenses_array[] = array($key, $expense);
            }

            echo json_encode(array("income" => $payments_array, "expenses" => $expenses_array));
        }
    }

    function income_vs_expenses_summary() {
        $view_data["projects_dropdown"] = $this->_get_projects_dropdown_for_income_and_epxenses();
        $this->load->view("expenses/income_vs_expenses_summary", $view_data);
    }

    function income_vs_expenses_summary_list_data() {

        $year = $this->input->post("start_date") ? explode("-", $this->input->post("start_date")) : explode("-", date("Y-m-d"));

        $project_id = $this->input->post("project_id");

        if ($year) {
            $expenses_data = $this->Expenses_model->get_yearly_expenses_chart($year[0], $project_id);

            $payments_data = $this->Invoice_payments_model->get_yearly_payments_chart($year[0], "", $project_id);

            $payments = array();
            $expenses = array();

            for ($i = 1; $i <= 12; $i++) {
                $payments[$i] = 0;
                $expenses[$i] = 0;
            }

            foreach ($payments_data as $payment) {
                $payments[$payment->month] = $payment->total;
            }
            foreach ($expenses_data as $expense) {
                $expenses[$expense->month] = $expense->total;
            }

            //get the list of summary
            $result = array();
            for ($i = 1; $i <= 12; $i++) {
                $result[] = $this->_row_data_of_summary($i, $payments[$i], $expenses[$i]);
            }

            // var_dump(arr($result)); exit;
            echo json_encode(array("data" => $result));
        }
    }

    //get the row of summary
    private function _row_data_of_summary($month_index, $payments, $expenses) {
        //get the month name
        $month_array = array(" ", "january", "february", "march", "april", "may", "june", "july", "august", "september", "october", "november", "december");

        $month = get_array_value($month_array, $month_index);

        $month_name = lang($month);
        $profit = $payments - $expenses;

        return array(
            $month_index,
            $month_name,
            to_decimal_format3($payments),
            to_decimal_format3($expenses),
            to_decimal_format3($profit),
            lang('THB')
        );
    }

    /* list of expense of a specific client, prepared for datatable  */

    function expense_list_data_of_client($client_id) {
        $this->access_only_team_members();

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("expenses", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array("client_id" => $client_id);

        $list_data = $this->Expenses_model->get_details($options)->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $custom_fields);
        }
        echo json_encode(array("data" => $result));
    }

    private function can_access_clients() {
        $permissions = $this->login_user->permissions;

        if (get_array_value($permissions, "client")) {
            return true;
        } else {
            return false;
        }
    }

    function expense_details()
    {
        validate_submitted_data(
            array(
                "id" => "required|numeric"
            )
        );

        $expense_id = $this->input->post('id');
        $options = array("id" => $expense_id);
        
        $info = $this->Expenses_model->get_details($options)->row();
        if (!$info) {
            show_404();
        }
		
		$param['id'] = $expense_id;
		$param['tbName'] = $_SESSION['table_name'];
        // exit;
        
        $view_data["proveButton"] = $this->dao->getProveButton($param);

        $view_data["expense_info"] = $info;
        $view_data["custom_fields_list"] = $this->Custom_fields_model->get_combined_details("expenses", $expense_id, $this->login_user->is_admin, $this->login_user->user_type)->result();

        // var_dump(arr($view_data)); exit();
        $this->load->view("expenses/expense_details", $view_data);
    }
	
    function list_data( $recurring = false ) {
		
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        $category_id = $this->input->post('category_id');
        $project_id = $this->input->post('project_id');
        $user_id = $this->input->post('user_id');
        $account_secondary_id = $this->input->post('acct_secondary_id');
        $account_category_id = $this->input->post('acct_category_id');
        $supplier_id = $this->input->post("supplier_id");

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("expenses", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array(
            "start_date" => $start_date,
            "end_date" => $end_date,
            "category_id" => $category_id,
            "project_id" => $project_id,
            "user_id" => $user_id,
            "custom_fields" => $custom_fields,
            "recurring" => $recurring,
            "account_secondary_id" => $account_secondary_id,
            "account_category_id" => $account_category_id,
            "supplier_id" => $supplier_id
        );

        $list_data = $this->Expenses_model->get_details( $options, $this->getRolePermission )->result();

        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $custom_fields);
        }

        // var_dump(arr($list_data)); exit();
        echo json_encode(array("data" => $result));
    }

    function pv_creation()
    {
        $view_data = $this->input->post();

        // var_dump(arr($view_data)); exit();
        $this->load->view("expenses/pv_creation", $view_data);
    }

    function pv_creation_save()
    {
        $post = $this->input->post();

        $result = array();

        if ($post["expenseId"]) {
            $result = $this->Payment_voucher_m->dev2_postPaymentVoucherHeaderFromExpense($post["expenseId"]);
        }

        echo json_encode($result);
    }

}

/* End of file expenses.php */
/* Location: ./application/controllers/expenses.php */