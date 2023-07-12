<?php
class MY_Controller extends CI_Controller {

    public $login_user;
    protected $data = ["print_mode"=>"private", "og_site_name"=>"", "og_url"=>"", "og_type"=>"", "og_title"=>"", "og_description"=>"", "og_image"=>""];
    protected $json = null;
    protected $access_type = "";
    protected $allowed_members = array();
    protected $allowed_ticket_types = array();
    protected $module_group = "";
    protected $is_user_a_project_member = false;
    protected $is_clients_project = false; //check if loged in user's client's project

    private $getRolePermissions=[];

    function __construct() {

        parent::__construct();

		global $class;

        $this->json = json_decode(file_get_contents('php://input'));

        $login_user_id = $this->Users_model->login_user_id();

        if (!$login_user_id) {
            $uri_string = uri_string();

            if (!$uri_string || $uri_string === "signin") {
                redirect('signin');
            } else {
                redirect('signin?redirect=' . get_uri($uri_string));
            }
        }

        $this->login_user = $this->Users_model->get_access_info( $login_user_id );

	 //arr($this->login_user);

	 //exit;
	 
	// echo 'bbbbbbbbbbbbbbbbbbbbbbbbb';
//
        if ($this->login_user->permissions) {
            $permissions = unserialize($this->login_user->permissions);


            $this->login_user->permissions = is_array($permissions) ? $permissions : array();
        } else {
            $this->login_user->permissions = array();
        }

		$request = $this->input->post();

		$this->load->model( 'db_model' );

		$this->dao = $this->db_model;
		

		$keep = array();
		foreach( explode( '/', uri_string() ) as $ke => $ve ) {

			if( count( $keep ) == 2 ) {
				break;

			}
			$keep[] = $ve;
		}

		$url = implode( '/', $keep );

		if(  in_array( strtolower( $class ), array( 'help' ) ) ) {
			$class = 'help_articles';

		}
		else if(  in_array( strtolower( $class ), array( 'invoice_payments' ) ) ) {
			$class = 'invoices';

		}
		else if(  in_array( strtolower( $class ), array( 'payment_voucher_payments' ) ) ) {
			$class = 'payment_vouchers';

		}
		else if( in_array( strtolower( $class ), array( 'roles', 'team', 'dashboard', 'left_menus', 'email_templates' ) ) ) {
			$class = 'settings';

		}
		else if( in_array( strtolower( $url ), array( 'expenses/income_vs_expenses', 'expenses/income_vs_expenses_chart_data' ) ) ) {
			$class = 'income_vs_expenses';
 
		}


		$sql = "
			SELECT
				*
			FROM left_menu
			WHERE url = '". $url ."'
			AND class_name = '". strtolower( $class ) ."'
		";

		$insert = false;
		foreach ( $this->db_model->fetchAll( $sql ) as $ka => $va ) {
			$insert = true;

			$sql = "
				UPDATE left_menu
				SET
					request = '". json_encode( $request ) ."'
				WHERE id = ". $va->id ."
			";

		}

        $params['insert'] = $insert;

		if( $insert == false ) {

			$sql = "
				INSERT INTO left_menu ( left_menu, url, class_name, every_body, request )
				SELECT
					0 as left_menu,
					'". $url ."' as url,
					'". strtolower( $class ) ."' as class_name,
					0 as every_body,
					'". json_encode( $request ) ."'  as request
			";


		}

		$this->db_model->execDatas( $sql );

		$param['table_name'] = strtolower( $class );

		$param['url'] = $url;

        // $ignore_permission_classes = ["test", "accounting", "quotations", "billing_notes", "invoices", "receipts", "credit_notes", "debit_notes", "clients", "notes"];
        $ignore_permission_classes = ["test", "accounting", "quotations", "billing_notes", "invoices", "receipts", "payment_voucher", "credit_notes", "debit_notes", "clients", "notes"];
        if(in_array($this->router->fetch_class(), $ignore_permission_classes)) return;          

        $this->getRolePermission = $this->db_model->getRolePermission( $param );
        $this->getRolePermissions = $this->db_model->getRolePermissions();

		if( $insert == true ) {

			if( empty( $this->getRolePermission['view_row'] ) ) {

				if( $va->check_permission == 1 ) {

					echo page404();

					exit;

				}
				else if( $va->left_menu == 1 ) {

					if( $va->every_body == 0 ) {

						echo page404();

						exit;

					}
				}

			}
		}
		
		
		
    }

// define(‘LINE_API’,”https://notify-api.line.me/api/notify");
// $token = “”; //ใส่Token ที่copy เอาไว้
// $str = “Hello”; //ข้อความที่ต้องการส่ง สูงสุด 1000 ตัวอักษร
// $res = notify_message($str,$token);
// print_r($res);
    protected function notifyMessage($message,$token="") {
        $LINE_API = "https://notify-api.line.me/api/notify";
        $token = "TRAHkWH4bMZ5MJYB7dl3IGyv7nJVTff2Wo9lBsjzzmY";
        $queryData = array('message' => $message);
        $queryData = http_build_query($queryData,'','&');
        $headerOptions = array( 
                'http'=>array(
                    'method'=>'POST',
                    'header'=> "Content-Type: application/x-www-form-urlencoded\r\n"
                            ."Authorization: Bearer ".$token."\r\n"
                            ."Content-Length: ".strlen($queryData)."\r\n",
                    'content' => $queryData
                ),
        );
        $context = stream_context_create($headerOptions);
        $result = file_get_contents($LINE_API, FALSE, $context);
        $res = json_decode($result);
        return $res;
    }

    protected function dump($var, $exit=0) {
        echo "<xmp>";
        var_dump($var);
        echo "</xmp>";
        if($exit) exit;
    }
	/*
    $resource = The resource you want to check, it's lowercase of table name
    $action = The action you want to check on $resource
    */
    protected function checkPermission($resource, $action) {
        if($this->login_user->is_admin)
            return 2;
        //if($this->login_user->is_admin && $action=='prove_row')
        //    return 1;
        if(!isset($this->getRolePermissions[$resource])) return false;
        if(!isset($this->getRolePermissions[$resource][$action])) return false;
        if($this->getRolePermissions[$resource]['read_only']=='1' && in_array($action, ['add_row', 'edit_row'])) {
            return 0;
        }
        if($this->getRolePermissions[$resource]['read_only']=='0' && in_array($action, ['add_row', 'edit_row'])) {
            return 1;
        }
        return $this->getRolePermissions[$resource][$action];
    }
    // function cp is alias function of checkPermission above
    protected function cp($resource, $action) {
        return $this->checkPermission($resource, $action);
    }
    protected function cop($action) {
        $class = strtolower(get_class($this));
        return $this->checkPermission($class, $action);
    }


    
    //initialize the login user's permissions with readable format
    protected function init_permission_checker( $module ) {
        $info = $this->get_access_info( $module );
        $this->access_type = $info->access_type;
        $this->allowed_members = $info->allowed_members;
        $this->allowed_ticket_types = $info->allowed_ticket_types;
        $this->module_group = $info->module_group;
    }

    //prepear the login user's permissions
    protected function get_access_info($group) {
        $info = new stdClass();
        $info->access_type = "";
        $info->allowed_members = array();
        $info->allowed_ticket_types = array();
        $info->module_group = $group;

        //admin users has access to everything
        if ($this->login_user->is_admin) {
            $info->access_type = "all";
        } else {

            //not an admin user? check module wise access permissions
            $module_permission = get_array_value($this->login_user->permissions, $group);

            if ($module_permission === "all") {
                //this user's has permission to access/manage everything of this module (same as admin)
                $info->access_type = "all";
            } else if ($module_permission === "specific") {
                //this user's has permission to access/manage sepcific items of this module

                $info->access_type = "specific";
                $module_permission = get_array_value($this->login_user->permissions, $group . "_specific");
                $permissions = explode(",", $module_permission);

                //check the accessable users list
                if ($group === "leave" || $group === "attendance" || $group === "team_member_update_permission" || $group === "timesheet_manage_permission" || $group == "message_permission") {
                    $info->allowed_members = $this->prepare_allowed_members_array($permissions, $this->login_user->id);
                } else if ($group === "ticket") {
                    //check the accessable ticket types
                    $info->allowed_ticket_types = $permissions;
                }
            } else if ($module_permission === "own") {
                $info->access_type = "own";
            } else if ($module_permission === "read_only") {
                $info->access_type = "read_only";
            } else if ($module_permission === "assigned_only") {
                $info->access_type = "assigned_only";
            }
        }
        return $info;
    }

    protected function prepare_allowed_members_array($permissions, $user_id) {
        $allowed_members = array($user_id);
        $allowed_teams = array();
        foreach ($permissions as $vlaue) {
            $permission_on = explode(":", $vlaue);
            $type = get_array_value($permission_on, "0");
            $type_value = get_array_value($permission_on, "1");
            if ($type === "member") {
                array_push($allowed_members, $type_value);
            } else if ($type === "team") {
                array_push($allowed_teams, $type_value);
            }
        }


        if (count($allowed_teams)) {
            $team = $this->Team_model->get_members($allowed_teams)->result();

            foreach ($team as $value) {
                if ($value->members) {
                    $members_array = explode(",", $value->members);
                    $allowed_members = array_merge($allowed_members, $members_array);
                }
            }
        }

        return $allowed_members;
    }

    //only allowed to access for team members
    protected function access_only_team_members() {

		//echo 'dfsdf';
		//exit;
        if ($this->login_user->user_type !== "staff") {
            //redirect("forbidden");
        }
    }

    //only allowed to access for admin users
    protected function access_only_admin() {
        if (!$this->login_user->is_admin) {
           // redirect("forbidden");
        }
    }

    //access only allowed team members
    protected function access_only_allowed_members() {
		return true;


        if ($this->access_type === "all") {
            return true; //can access if user has permission
        } else if (($this->module_group === "ticket" && ($this->access_type === "specific" || $this->access_type === "assigned_only")) || ($this->module_group === "lead" && $this->access_type === "own") || ($this->module_group === "client" && ($this->access_type === "own" || $this->access_type === "read_only"))) {
            //can access if it's tickets module and user has a pertial access
            //can access if it's leads module and user has access to own leads
            //can access if it's clients module and user has a pertial access
            return true;
        } else {
            //redirect("forbidden");
        }
    }

    //access only allowed team members or client contacts
    protected function access_only_allowed_members_or_client_contact($client_id) {
        return true;
		if ($this->access_type === "all") {
            return true; //can access if user has permission
        } else if ($this->module_group === "ticket" && ($this->access_type === "specific" || $this->access_type === "assigned_only")) {
            return true; //can access if it's tickets module and user has a pertial access
        } else if ($this->module_group === "client" && ($this->access_type === "own" || $this->access_type === "read_only")) {
            return true; //can access if it's clients module and user has a pertial access
        } else if ($this->login_user->client_id === $client_id) {
            return true; //can access if client id match
        } else {
            //redirect("forbidden");
        }
    }

    //allowed team members and clint himself can access
    protected function access_only_allowed_members_or_contact_personally($user_id) {
        if (!($this->access_type === "all" || $this->access_type === "own" || $this->access_type === "read_only" || $user_id === $this->login_user->id)) {
            //redirect("forbidden");
        }
    }

    //access all team members and client contact
    protected function access_only_team_members_or_client_contact($client_id) {
        if (!($this->login_user->user_type === "staff" || $this->login_user->client_id === $client_id)) {
            //redirect("forbidden");
        }
    }

    //only allowed to access for admin users
    protected function access_only_clients() {
        if ($this->login_user->user_type != "client") {
            //redirect("forbidden");
        }
    }

    //check module is enabled or not
    protected function check_module_availability($module_name) {
        if (get_setting($module_name) != "1") {
           // redirect("forbidden");
        }
    }

    //check who has permission to create projects
    protected function can_create_projects() {
        if ($this->login_user->user_type == "staff") {
            if ($this->login_user->is_admin || get_array_value($this->login_user->permissions, "can_manage_all_projects") == "1") {
                return true;
            } else if (get_array_value($this->login_user->permissions, "can_create_projects") == "1") {
                return true;
            }
        } else {
            if (get_setting("client_can_create_projects")) {
                return true;
            }
        }
    }

    //check who has permission to view team members list
    protected function can_view_team_members_list() {
        if ($this->login_user->user_type == "staff") {
            if (get_array_value($this->login_user->permissions, "hide_team_members_list") == "1") {
                return false;
            } else {
                return true; //all members can see team members except the selected roles
            }
        }
        return false;
    }

    //get currency dropdown list
    protected function _get_currency_dropdown_select2_data() {
        $currency = array(array("id" => "", "text" => "-"));
        foreach (get_international_currency_code_dropdown() as $value) {
            $currency[] = array("id" => $value, "text" => $value);
        }
        return $currency;
    }

    //access team members and clients
    protected function access_only_team_members_or_client() {
        if (!($this->login_user->user_type === "staff" || $this->login_user->user_type === "client")) {
            //redirect("forbidden");
        }
    }

    //When checking project permissions, to reduce db query we'll use this init function, where team members has to be access on the project
    protected function init_project_permission_checker($project_id = 0) {
        if ($this->login_user->user_type == "client") {
            $project_info = $this->Projects_model->get_one($project_id);
            if ($project_info->client_id == $this->login_user->client_id) {
                $this->is_clients_project = true;
            }
        } else {
            $this->is_user_a_project_member = $this->Project_members_model->is_user_a_project_member($project_id, $this->login_user->id);
        }
    }

    protected function can_create_tasks($in_project = true) {
        if ($this->login_user->user_type == "staff") {
            if ($this->can_manage_all_projects()) {
                return true;
            } else if (get_array_value($this->login_user->permissions, "can_create_tasks") == "1") {
                //check is user a project member
                if ($in_project) {
                    return $this->is_user_a_project_member; //check the specific project permission
                } else {
                    return true;
                }
            }
        } else {
            //check settings for client's project permission
            if (get_setting("client_can_create_tasks")) {
                if ($in_project) {
                    //check if it's client's project
                    return $this->is_clients_project;
                } else {
                    //client has permission to create tasks on own projects
                    return true;
                }
            }
        }
    }

    protected function can_manage_all_projects() {
        if ($this->login_user->is_admin || get_array_value($this->login_user->permissions, "can_manage_all_projects") == "1") {
            return true;
        }
    }

    //get currencies dropdown
    protected function _get_currencies_dropdown() {
        $used_currencies = $this->Invoices_model->get_used_currencies_of_client()->result();

        if ($used_currencies) {
            $default_currency = get_setting("default_currency");

            $currencies_dropdown = array(
                array("id" => "", "text" => "- " . lang("currency") . " -"),
                array("id" => $default_currency, "text" => $default_currency) // add default currency
            );

            foreach ($used_currencies as $currency) {
                $currencies_dropdown[] = array("id" => $currency->currency, "text" => $currency->currency);
            }

            return json_encode($currencies_dropdown);
        }
    }

    //get hidden topbar menus dropdown
    protected function get_hidden_topbar_menus_dropdown() {
        //topbar menus dropdown
        $hidden_topbar_menus = array(
            "to_do",
            "favorite_projects",
            "dashboard_customization",
            "quick_add"
        );

        if ($this->login_user->user_type == "staff") {
            //favourite clients
            $access_client = get_array_value($this->login_user->permissions, "client");
            if ($this->login_user->is_admin || $access_client) {
                array_push($hidden_topbar_menus, "favorite_clients");
            }

            //custom language
            if (!get_setting("disable_language_selector_for_team_members")) {
                array_push($hidden_topbar_menus, "language");
            }
        } else {
            //custom language
            if (!get_setting("disable_language_selector_for_clients")) {
                array_push($hidden_topbar_menus, "language");
            }
        }

        $hidden_topbar_menus_dropdown = array();
        foreach ($hidden_topbar_menus as $hidden_menu) {
            $hidden_topbar_menus_dropdown[] = array("id" => $hidden_menu, "text" => lang($hidden_menu));
        }

        return json_encode($hidden_topbar_menus_dropdown);
    }

    //get existing projects dropdown for income and expenses
    protected function _get_projects_dropdown_for_income_and_epxenses($type = "all") {
        $projects = $this->Invoice_payments_model->get_used_projects($type)->result();

        if ($projects) {
            $projects_dropdown = array(
                array("id" => "", "text" => "- " . lang("project") . " -"),
            );

            foreach ($projects as $project) {
                $projects_dropdown[] = array("id" => $project->id, "text" => $project->title);
            }

            return json_encode($projects_dropdown);
        }
    }

    protected function _get_groups_dropdown_select2_data($show_header = false) {
        $client_groups = $this->Client_groups_model->get_all()->result();
        $groups_dropdown = array();

        if ($show_header) {
            $groups_dropdown[] = array("id" => "", "text" => "- " . lang("client_groups") . " -");
        }

        foreach ($client_groups as $group) {
            $groups_dropdown[] = array("id" => $group->id, "text" => $group->title);
        }
        return $groups_dropdown;
    }

    protected function get_clients_and_leads_dropdown($return_json = false) {
        $clients_dropdown = array("" => "-");
        $clients_json_dropdown = array(array("id" => "", "text" => "-"));
        $clients = $this->Clients_model->get_all_where(array("deleted" => 0), 0, 0, "is_lead")->result();

        foreach ($clients as $client) {
            $company_name = $client->is_lead ? lang("lead") . ": " . $client->company_name : $client->company_name;

            $clients_dropdown[$client->id] = $company_name;
            $clients_json_dropdown[] = array("id" => $client->id, "text" => $company_name);
        }

        return $return_json ? $clients_json_dropdown : $clients_dropdown;
    }

    //check if the login user has restriction to show all tasks
    protected function show_assigned_tasks_only_user_id() {
        if ($this->login_user->user_type === "staff") {
            return get_array_value($this->login_user->permissions, "show_assigned_tasks_only") == "1" ? $this->login_user->id : false;
        }
    }

    //make calendar filter dropdown
    protected function get_calendar_filter_dropdown($type = "default") {
        /*
         * There should be all filters in main Events
         * On client->events tab, there will be only events and project deadlines field
         * On lead->events tab, there will be only events field
         */

        $this->load->helper('cookie');
        $selected_filters_cookie = get_cookie("calendar_filters_of_user_" . $this->login_user->id);
        $selected_filters_cookie_array = $selected_filters_cookie ? explode('-', $selected_filters_cookie) : array("events"); //load only events if there is no cookie

        $calendar_filter_dropdown = array(array("id" => "events", "text" => lang("events"), "isChecked" => in_array("events", $selected_filters_cookie_array) ? true : false));

        if ($type !== "lead") {
            if ($this->login_user->user_type == "staff" && $type == "default") {
                //approved leaves
                $leave_access_info = $this->get_access_info("leave");
                if ($leave_access_info->access_type && get_setting("module_leave")) {
                    $calendar_filter_dropdown[] = array("id" => "leave", "text" => lang("leave"), "isChecked" => in_array("leave", $selected_filters_cookie_array) ? true : false);
                }

                //task start dates
                $calendar_filter_dropdown[] = array("id" => "task_start_date", "text" => lang("task_start_date"), "isChecked" => in_array("task_start_date", $selected_filters_cookie_array) ? true : false);

                //task deadlines
                $calendar_filter_dropdown[] = array("id" => "task_deadline", "text" => lang("task_deadline"), "isChecked" => in_array("task_deadline", $selected_filters_cookie_array) ? true : false);
            }

            //project start dates
            $calendar_filter_dropdown[] = array("id" => "project_start_date", "text" => lang("project_start_date"), "isChecked" => in_array("project_start_date", $selected_filters_cookie_array) ? true : false);

            //project deadlines
            $calendar_filter_dropdown[] = array("id" => "project_deadline", "text" => lang("project_deadline"), "isChecked" => in_array("project_deadline", $selected_filters_cookie_array) ? true : false);
        }

        return $calendar_filter_dropdown;
    }

    protected function check_access_to_store() {
        $this->check_module_availability("module_order");
        if ($this->login_user->user_type == "staff") {
            $this->access_only_allowed_members();
        } else {
            if (!get_setting("client_can_access_store")) {
                //redirect("forbidden");
            }
        }
    }

    protected function check_access_to_this_order_item($order_item_info) {
        if ($order_item_info->id) {
            //item created
            if (!$order_item_info->order_id) {
                //on processing order, check if the item is created by the login user
                if ($order_item_info->created_by !== $this->login_user->id) {
                    //redirect("forbidden");
                }
            } else {
                //order created, now only allowed members can access
                if ($this->login_user->user_type == "client") {
                    //redirect("forbidden");
                }
            }
        } else if ($this->login_user->user_type !== "staff") {
            //item isn't created, only allowed member can access
            //redirect("forbidden");
        }
    }

    protected function check_access_to_this_receipt_item($receipt_item_info) {
        if ($receipt_item_info->id) {
            //item created
            if (!$receipt_item_info->receipt_id) {
                //on processing order, check if the item is created by the login user
                if ($receipt_item_info->created_by !== $this->login_user->id) {
                    //redirect("forbidden");
                }
            } else {
                //order created, now only allowed members can access
                if ($this->login_user->user_type == "client") {
                    //redirect("forbidden");
                }
            }
        } else if ($this->login_user->user_type !== "staff") {
            //item isn't created, only allowed member can access
            //redirect("forbidden");
        }
    }

    protected function check_access_to_this_pr_item($pr_item_info) {
        if ($pr_item_info->id) {
            //item created
            if (!$pr_item_info->pr_id) {
                //on processing order, check if the item is created by the login user
                if ($pr_item_info->created_by !== $this->login_user->id) {
                    //redirect("forbidden");
                }
            } else {
                //order created, now only allowed members can access
                if ($this->login_user->user_type == "client") {
                    //redirect("forbidden");
                }
            }
        } else if ($this->login_user->user_type !== "staff") {
            //item isn't created, only allowed member can access
            //redirect("forbidden");
        }
    }

    protected function check_access_to_this_mr_item($mr_item_info) {
        if ($mr_item_info->id) {
            //item created
            if (!$mr_item_info->mr_id) {
                //on processing order, check if the item is created by the login user
                if ($mr_item_info->created_by !== $this->login_user->id) {
                    //redirect("forbidden");
                }
            } else {
                //order created, now only allowed members can access
                if ($this->login_user->user_type == "client") {
                    //redirect("forbidden");
                }
            }
        } else if ($this->login_user->user_type !== "staff") {
            //item isn't created, only allowed member can access
            //redirect("forbidden");
        }
    }

    protected function make_labels_dropdown( $type = "", $label_ids = "", $is_filter = false, $custom_filter_title = "", $color = false ) {
        if (!$type) {
            show_404();
        }

        $labels_dropdown = $is_filter ? array(array("id" => "", "text" => "- " . ($custom_filter_title ? $custom_filter_title : lang("label")) . " -")) : array();

        $options = array(
            "context" => $type
        );

        if ($type == "event" || $type == "note" || $type == "to_do") {
            $options["user_id"] = $this->login_user->id;
        }

        if ($label_ids) {
            $add_label_option = true;

            //check if any string is exists,
            //if so, not include this parameter
            $explode_ids = explode(',', $label_ids);
            foreach ($explode_ids as $label_id) {
                if (!is_int($label_id)) {
                    $add_label_option = false;
                    break;
                }
            }

            if ($add_label_option) {
                $options["label_ids"] = $label_ids; //to edit labels where have access of others
            }
        }

        $labels = $this->Labels_model->get_details($options)->result();
        foreach ($labels as $label) {
			
			if( $color == true ) {
				
				$labels_dropdown[] = array("id" => $label->id, "text" => '<span style="color: red;">'. $label->title .'</span>' );
			}
			else {
				$labels_dropdown[] = array("id" => $label->id, "text" => $label->title);
			}
        }

        return $labels_dropdown;
    }

    protected function can_edit_projects() {
        if ($this->login_user->user_type == "staff") {
            if ($this->can_manage_all_projects()) {
                return true;
            } else if (get_array_value($this->login_user->permissions, "can_edit_projects") == "1") {
                return true;
            }
        } else {
            if (get_setting("client_can_edit_projects")) {
                return true;
            }
        }

        return false;
    }

    protected function get_user_options_for_query($only_type = "") {
        /*
         * team members can send message to all team members/can't send to any member/can send to specific members
         * clients can only send message to team members and to own contacts (as defined on Client settings)
         * team members can send message to clients (as defined on Client settings)
         */

        $options = array("login_user_id" => $this->login_user->id);
        $client_message_users = get_setting("client_message_users");

        if ($this->login_user->user_type == "staff") {
            //user is team member
            if ($only_type !== "client") {
                if (!get_array_value($this->login_user->permissions, "message_permission")) {
                    //user can manage all members
                    $options["all_members"] = true;
                } else if (get_array_value($this->login_user->permissions, "message_permission") == "specific") {
                    //user can manage only specific members
                    $options["specific_members"] = $this->allowed_members;
                }
            }

            $client_message_users_array = explode(",", $client_message_users);
            if (in_array($this->login_user->id, $client_message_users_array) && $only_type !== "staff") {
                //user can send message to clients
                $options["member_to_clients"] = true;
            }
        } else {
            //user is a client contact
            if ($client_message_users) {
                if ($only_type !== "client") {
                    $options["client_to_members"] = $client_message_users;
                }

                if (get_setting("client_message_own_contacts") && $only_type !== "staff") {
                    //client has permission to send message to own client contacts
                    $options["client_id"] = $this->login_user->client_id;
                }
            }
        }

        return $options;
    }

    protected function check_access_on_messages_for_this_user() {
        $accessable = true;

        if ($this->login_user->user_type == "staff") {
            $client_message_users = get_setting("client_message_users");
            $client_message_users_array = explode(",", $client_message_users);

            if (!$this->login_user->is_admin && get_array_value($this->login_user->permissions, "message_permission") == "no" && !in_array($this->login_user->id, $client_message_users_array)) {
                $accessable = false;
            }
        } else {
            if (!get_setting("client_message_users")) {
                $accessable = false;
            }
        }

        return $accessable;
    }

    protected function can_view_invoices($client_id = 0) {
        if ($this->login_user->user_type == "staff") {
            if ($this->login_user->is_admin || get_array_value($this->login_user->permissions, "invoice") === "all" || get_array_value($this->login_user->permissions, "invoice") === "read_only") {
                return true;
            }
        } else {
            if ($this->login_user->client_id === $client_id) {
                return true;
            }
        }
    }

    protected function can_edit_invoices() {

		return true;
        if ($this->login_user->user_type == "staff" && ($this->login_user->is_admin || get_array_value($this->login_user->permissions, "invoice") === "all")) {
            return true;
        }
    }

    protected function can_view_payment_vouchers($client_id = 0) {
        if ($this->login_user->user_type == "staff") {
            if ($this->login_user->is_admin || get_array_value($this->login_user->permissions, "payment_voucher") === "all" || get_array_value($this->login_user->permissions, "payment_voucher") === "read_only") {
                return true;
            }
        } else {
            if ($this->login_user->client_id === $client_id) {
                return true;
            }
        }
    }

    protected function can_edit_payment_vouchers() {

		return true;
        if ($this->login_user->user_type == "staff" && ($this->login_user->is_admin || get_array_value($this->login_user->permissions, "payment_voucher") === "all")) {
            return true;
        }
    }

    protected function can_access_expenses() {

		return true;
        $permissions = $this->login_user->permissions;
        if ($this->login_user->is_admin || get_array_value($permissions, "expense")) {
            return true;
        } else {
            return false;
        }
    }

    protected function validate_sending_message($to_user_id) {
        $users = $this->Messages_model->get_users_for_messaging($this->get_user_options_for_query())->result();
        $users = json_decode(json_encode($users), true); //convert to array
        if (!$this->check_access_on_messages_for_this_user() || !in_array($to_user_id, array_column($users, "id"))) {
            return false;
        }

        //so the sender could send message to the receiver
        //check if the receiver could also send message to the sender
        $to_user_info = $this->Users_model->get_one($to_user_id);
        if ($to_user_info->user_type == "staff") {
            //receiver is a team member
            $permissions = array();
            $user_permissions = $this->Users_model->get_access_info($to_user_id)->permissions;
            if ($user_permissions) {
                $user_permissions = unserialize($user_permissions);
                $permissions = is_array($user_permissions) ? $user_permissions : array();
            }

            if (get_array_value($permissions, "message_permission") == "no") {
                //user doesn't have permission to send any message
                return false;
            } else if (get_array_value($permissions, "message_permission") == "specific") {
                //user has access on specific members
                $module_permission = get_array_value($permissions, "message_permission_specific");
                $permissions = explode(",", $module_permission);
                $allowed_members = $this->prepare_allowed_members_array($permissions, $to_user_id);
                if (!in_array($this->login_user->id, $allowed_members)) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function show_own_clients_only_user_id() {
        if ($this->login_user->user_type === "staff") {
            return get_array_value($this->login_user->permissions, "client") == "own" ? $this->login_user->id : false;
        }
    }

    protected function check_profile_image_dimension($image_file_name = "") {
        if (!$image_file_name) {
            return false;
        }

        list($width, $height) = getimagesize($image_file_name);

        if ($width === 200 && $height === 200) {
            return true;
        }

        return false;
    }

    protected function show_assigned_tickets_only_user_id() {
        if ($this->access_type === "assigned_only") {
            return $this->login_user->id;
        }
    }

    protected function get_team_members_dropdown($is_filter = false) {
        $team_members = $this->Users_model->get_all_where(array("user_type" => "staff", "deleted" => 0, "status" => "active"))->result();

        $team_members_dropdown = array();
        if ($is_filter) {
            $team_members_dropdown = array(array("id" => "", "text" => "- " . lang("owner") . " -"));
        }

        foreach ($team_members as $member) {
            $team_members_dropdown[] = array("id" => $member->id, "text" => $member->first_name . " " . $member->last_name);
        }

        return json_encode($team_members_dropdown);
    }


    // START: BOM
    protected function bom_can_access_supplier() {
        return $this->check_permission('bom_supplier_read_self')
            || $this->check_permission('bom_supplier_read');
    }
    protected function bom_can_access_material() {
        return $this->check_permission('bom_material_read');
    }
    protected function bom_can_access_restock() {
        return $this->check_permission('bom_restock_read_self')
            || $this->check_permission('bom_restock_read');
    }
    protected function bom_can_access_calculator() {
        return $this->bom_can_access_supplier()
            && $this->bom_can_access_material()
            && $this->bom_can_access_restock();
    }

    protected function bom_can_read_supplier($owner_id = 0) {
        if($this->bom_can_access_supplier()) {
            if(
                $this->check_permission('bom_supplier_read_self')
                && !$this->check_permission('bom_supplier_read')
            ) {
                if($owner_id != $this->login_user->id) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }
    protected function bom_can_read_restock($created_by = 0) {
        if($this->bom_can_access_restock()) {
            if(
                $this->check_permission('bom_restock_read_self')
                && !$this->check_permission('bom_restock_read')
            ) {
                if($created_by != $this->login_user->id) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }
    // END: BOM

    // START: Utility
    protected function check_permission($permission) {
        if ($this->login_user->user_type == "staff") {
            if ($this->login_user->is_admin) {
                return true;
            } else if (get_array_value($this->login_user->permissions, $permission) == "1") {
                return true;
            }
        }
        return false;
    }
    // END: Utility

    protected function can_view_receipt_taxinvoices($client_id = 0) {
        if ($this->login_user->user_type == "staff") {
            if ($this->login_user->is_admin || get_array_value($this->login_user->permissions, "receipt_taxinvoice") === "all" || get_array_value($this->login_user->permissions, "receipt_taxinvoice") === "read_only") {
                return true;
            }
        } else {
            if ($this->login_user->client_id === $client_id) {
                return true;
            }
        }
    }

    protected function can_edit_receipt_taxinvoices() {

		return true;
        if ($this->login_user->user_type == "staff" && ($this->login_user->is_admin || get_array_value($this->login_user->permissions, "receipt_taxinvoice") === "all")) {
            return true;
        }
    }

}


class PublicController extends CI_Controller {
    protected $data = ["print_mode"=>"public", "og_site_name"=>"", "og_url"=>"", "og_type"=>"", "og_title"=>"", "og_description"=>"", "og_image"=>""];

    function __construct() {
        parent::__construct();
        $this->data["print_mode"] = "public";

        $this->load->model('Purchase_request_m');
    }
}

