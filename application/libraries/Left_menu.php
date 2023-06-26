<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Left_menu {
    private $ci = null;

    public function __construct() {
        $this->ci = & get_instance();
		///echo $this->ci->gogo;
		
		//arr($this->ci->login_user->is_admin);
	//exit;
    }
	
	function gogo_left_menu() {
		
	
		$view_data["show_devider"] = false;
		

		$this->ci->load->model( 'db_model' );
		
	// arr($this->ci->login_user->is_admin );	
	
	
	
		// if( $this->ci->login_user->user_type == 'client' ) {
			
		// 	$sql = "
		// 		SELECT 
		// 			l.id 
		// 		FROM left_menu l
		// 		WHERE  l.left_menu = 1
		// 		ORDER BY
		// 			l.order_number ASC
		// 	";
		// }
		// else 
        if( $this->ci->login_user->is_admin == 0 ) {

			$sql = "
				SELECT 
					l.id 
				FROM left_menu l
				WHERE ( 
					( l.every_body = 1 ) 
					OR
					( l.left_menu = 1 ) 
					OR 
					( 1 = ". $this->ci->login_user->is_admin ." ) 
					OR 
					(
						SELECT 
							count( * ) 
						FROM role_permission 
						WHERE table_name = l.class_name 
						AND view_row != 0
						AND role_id = ". $this->ci->login_user->role_id ."
					) > 0
				) 
				AND l.left_menu = 1
				ORDER BY
					l.order_number ASC
			";
			
		}else {
			
			$sql = "
				SELECT 
					l.id 
				FROM left_menu l
				WHERE ( 
					( l.every_body = 1 ) 
					OR 
					( 1 = ". $this->ci->login_user->is_admin ." ) 
				) 
				AND l.left_menu = 1
				ORDER BY
					l.order_number ASC
			";
		}

        // echo $sql;
        // exit;
        // echo $sql;
        //var_dump(unserialize());
        

        $rows = $this->ci->dao->fetchAll( $sql );
		$allowed_menus = [];
        foreach($rows as $row) {
            $allowed_menus[] = $row->id;
        }

        // var_dump($allowed_menus);

        // $available_items = $this->get_available_items();
        $sortable_items = $this->get_sortable_items_array();
		$liss = array();

        $permissions = $this->ci->login_user->permissions;
		foreach ( $sortable_items as $ka => $main_menu ) {

			if($this->ci->login_user->is_admin != 1){
				if(!in_array($main_menu['id'], $allowed_menus)) continue;
				if($main_menu['name'] == "leads") if($permissions["lead"] == null) continue;
				if($main_menu['name'] == "settings") if(!isset($permissions["can_setting"])) continue;
			}
			
            $value = get_setting('module_'.$main_menu['name']);

            if($value!==null && !$value) continue;

            $url = $main_menu['url'];
			
			$submenu = get_array_value($main_menu, "submenu");
			$expend_class = $submenu ? " expand " : "";
			
			$active_class = uri_string() == $url? "active" : (get_instance()->uri->segment(1) == $url? "active" : "");

			$submenu_open_class = "";
			if ($expend_class && $active_class) {
				$submenu_open_class = " open ";
			}

			$devider_class = ( $view_data["show_devider"] && get_array_value($main_menu, "devider")) ? "devider" : "";
			
			$badge_class = get_array_value($main_menu, "badge_class");
			$target = (isset($main_menu['is_custom_menu_item']) && isset($main_menu['open_in_new_tab']) && $main_menu['open_in_new_tab']) ? "target='_blank'" : "";
			
			
			if( $main_menu['id'] == 4 ) {
				
				
				$is_client = false;
				if ( $this->ci->login_user->user_type == "client") {
					$is_client = true;
				}
				
				$sql = "
					SELECT
						date_format( now(), '%Y-%m-%d' ) as start
				";
				
				$res = $this->ci->dao->fetch( $sql );

				$options_of_events = array("user_id" => $this->ci->login_user->id, "team_ids" => $this->ci->login_user->team_ids, "client_id" => 0, "start_date" => $res->start, "end_date" => '2250-01-01', "include_recurring" => true, "is_client" => $is_client, "label_id" => 0, 'badge' => 1 );


 
				$list_data_of_events = $this->ci->Events_model->get_details( $options_of_events )->result();
				$spanBadge = '';
				if( count(  $list_data_of_events  ) > 0 ) {
					
					$spanBadge = '<span class="badge '. $badge_class .'">'. count( $list_data_of_events ) .'</span> '; 
				}
				 
			}
			else {
				
				$badge = get_array_value($main_menu, "badge");
				$spanBadge = '';
				if ( $badge ) 
					$spanBadge = '<span class="badge '. $badge_class .'">'. count_unread_message() .'</span> '; 
			}
			
			
			$ul = '';
			$expand = ' ';
			if( isset( $main_menu['submenu'] ) ) {
				
				$lis = array();
				foreach( $main_menu['submenu'] as $ks => $s_menu ) {
					$s_menu = convertObJectToArray( $s_menu );
					
					$sub_menu_target = ( isset($s_menu['is_custom_menu_item']) && isset($s_menu['open_in_new_tab']) && $s_menu['open_in_new_tab']) ? "target='_blank'" : "";
					
					$link = isset($s_menu['is_custom_menu_item']) ? $s_menu['url'] : get_uri( $s_menu['url'] );  
					
					$title = isset($s_menu['is_custom_menu_item']) ? lang($s_menu['name']) : lang($s_menu['name']); 		
													
								   
									
					$lis[] = '
						<li>
							<a '. $sub_menu_target .' href="'. $link .'">
								<i class="fa fa-list-ul"></i>
								
								
								<span>'. $title .'</span>
								
							</a>
						</li>
					'; 
				}
				
				$ul = '<ul>'. implode( '', $lis ) .'</ul>';
				$expand = ' expand';
				
			}
			
			$link = get_uri( $url ); 
			 
			//$title = isset($main_menu['name']) ? lang( $main_menu['name'] ) : $va->title;
            $title = @$main_menu['name'];
			
			$class = isset( $main_menu['class']) ? '<i class="fa '. $main_menu['class'] .'"></i>' : '<i class="'. $main_menu['icon'] .'"></i>';

			$liss[] = '
				<li class="'. $expend_class .' '. $devider_class .' '. $active_class .' '. $submenu_open_class .' main">
					<a '. $target .' href="'. $link .'">
						'. $class .'
						<span>'. lang($title) .'</span>'. $spanBadge .'
					</a>
								
					'. $ul .'			
				</li>
			';


		}
		
		return '
			<div id="sidebar" class="box-content ani-width">
				<div id="sidebar-scroll">
					<ul class="" id="sidebar-menu">
						'. implode( '', $liss ) .'
					</ul>
				</div>
			</div> 
		';
	
	}
	
    function rander_left_menu($is_preview = false, $type = "default", $need = false ) {
		
		
		
		
        $final_left_menu_items = array();
        $custom_left_menu_items = $this->_get_left_menu_from_setting_for_rander($is_preview, $type);

        if ($custom_left_menu_items) {
            $left_menu_items = $this->_prepare_sidebar_menu_items($type);
			
			
			//arr( $left_menu_items );
			
			//exit;

            $last_menu_item = ""; //store last menu item to the get the data on creating submenu
            $last_final_menu_item = ""; //store the last menu item of final left menu to add submenu to this item 
            $parent_item_added_as_submenu = false;

            foreach ($custom_left_menu_items as $key => $value) {
                $item_value_array = $this->_get_item_array_value($value, $left_menu_items);

                $is_sub_menu = get_array_value($value, "is_sub_menu");
                if ($is_sub_menu) {
                    //this is a sub menu, move it to it's parent item                        
                    //since the parent item is also a standalone menu item, make a submenu of that too
                    //but if any other menu item which haven't any submenu, added as a submenu of a menu item which have submenu, that won't be added

                    $parent_item_array = $this->_get_item_array_value(get_array_value($custom_left_menu_items, $last_menu_item), $left_menu_items);
                    if (!$parent_item_added_as_submenu && !isset($parent_item_array["submenu"])) {
                        $final_left_menu_items[$last_final_menu_item]["submenu"][] = $parent_item_array;
                        $parent_item_added_as_submenu = true;
                    }

                    //add this item
                    array_push($final_left_menu_items[$last_final_menu_item]["submenu"], $item_value_array);
                } else {
                    $final_left_menu_items[] = $item_value_array;
                    $last_menu_item = $key;
                    $last_final_menu_item = end($final_left_menu_items);
                    $last_final_menu_item = key($final_left_menu_items);
                    $parent_item_added_as_submenu = false;
                }
            }
        }

        $view_data["show_devider"] = true;

        if (count($final_left_menu_items)) {
			 
			 
            $view_data["sidebar_menu"] = $final_left_menu_items;
			
			
            $view_data["show_devider"] = false;
        } else {
			 
			
            $view_data["sidebar_menu"] = $this->_get_sidebar_menu_items($type);
        }
		
		
		//arr( $view_data["sidebar_menu"]);
		$view_data["is_preview"] = $is_preview;
		return $this->ci->load->view("includes/left_menu", $view_data, true);
		
		if( $need == false  ) {
			
			//file_put_contents( 'menutest.txt', json_encode( $view_data["sidebar_menu"] ) ) ;
			
			
			
		} 
		else {
			
			
			
			//return get_active_menu( $view_data["sidebar_menu"] );
			
		
		}
		
    }
	

    function get_available_items($type = "default") {
        $items_array = $this->_prepare_sidebar_menu_items($type);

        //remove used items
        /* $default_left_menu_items = $this->_get_left_menu_from_setting($type);

        foreach ($default_left_menu_items as $default_item) {
            unset($items_array[get_array_value($default_item, "name")]);
        } */

        $items = "";
        foreach ($items_array as $item) {
            $items .= $this->_get_item_data($item, true);
        }
// var_dump($items);exit;
        return $items ? $items : "<span class='text-off empty-area-text'>" . lang('no_more_items_available') . "</span>";
    }

    public function _prepare_sidebar_menu_items($type = "") {
        $final_items_array = array();
        $items_array = $this->_get_sidebar_menu_items($type);
// var_dump($items_array);exit;
        foreach ($items_array as $item) {
            $main_menu_name = get_array_value($item, "name");

            if (isset($item["submenu"])) {
                //first add this menu removing the submenus
                if ($main_menu_name !== "finance" && $main_menu_name !== "help_and_support") {
                    $main_menu = $item;
                    unset($main_menu["submenu"]);
                    $final_items_array[$main_menu_name] = $main_menu;
                }

                $submenu = get_array_value($item, "submenu");
                foreach ($submenu as $key => $s_menu) {
                    //prepare help items differently
                    if ($main_menu_name == "help_and_support") {
                        $s_menu = $this->_make_customized_sub_menu_for_help_and_support($key, $s_menu);
                    }

                    if (get_array_value($s_menu, "class")) {
                        $final_items_array[get_array_value($s_menu, "name")] = $s_menu;
                    }
                }
            } else {
                $final_items_array[$main_menu_name] = $item;
            }
        }

        //add todo
        $final_items_array["todo"] = array("name" => "todo", "url" => "todo", "class" => "fa-check-square-o");
//arr($final_items_array);
        return $final_items_array;
    }

    private function _make_customized_sub_menu_for_help_and_support($key, $s_menu) {
        if ($key == 1) {
            $s_menu["name"] = "help_articles";
        } else if ($key == 2) {
            $s_menu["name"] = "help_categories";
        } else if ($key == 4) {
            $s_menu["name"] = "knowledge_base_articles";
        } else if ($key == 5) {
            $s_menu["name"] = "knowledge_base_categories";
        }

        return $s_menu;
    }

    public function _get_left_menu_from_setting_for_rander($is_preview = false, $type = "default") {
        $user_left_menu = get_setting("user_" . $this->ci->login_user->id . "_left_menu");
        $default_left_menu = ($type == "client_default" || $this->ci->login_user->user_type == "client") ? get_setting("default_client_left_menu") : get_setting("default_left_menu");
        $custom_left_menu = "";

        //for preview, show the edit type preview
        if ($is_preview) {
            $custom_left_menu = $default_left_menu; //default preview
            if ($type == "user") {
                $custom_left_menu = $user_left_menu ? $user_left_menu : $default_left_menu; //user level preview
            }
        } else {
            $custom_left_menu = $user_left_menu ? $user_left_menu : $default_left_menu; //page rander
        }

        return $custom_left_menu ? json_decode(json_encode(@unserialize($custom_left_menu)), true) : array();
    }

    private function _get_left_menu_from_setting($type) {
        if ($type == "client_default") {
            
            $default_left_menu = get_setting("default_client_left_menu");
        } else if ($type == "user") {
            $default_left_menu = get_setting("user_" . $this->ci->login_user->id . "_left_menu");
        } else {
            
            $default_left_menu = get_setting("default_left_menu");
            $a = @unserialize($default_left_menu);

            $keep = [];
            foreach( $a as $k => $v){
                
                $keep[] = $v;
                
            }
            // $keep[] =["name" => "accounting"];
            // $keep[] =["name" => "receipts"];
            // $keep[] =["name" => "resources"];
            // arr(serialize($keep));
            
        }
        // exit;
        return $default_left_menu ? json_decode(json_encode(@unserialize($default_left_menu)), true) : array();
    }

    public function _get_item_data($item, $is_default_item = false) {
        $name = get_array_value($item, "name");
        $url = get_array_value($item, "url");
        $is_sub_menu = get_array_value($item, "is_sub_menu");
        $open_in_new_tab = get_array_value($item, "open_in_new_tab");
        $icon = get_array_value($item, "icon");

        if ($name) {
            $sub_menu_class = "";
            if ($is_sub_menu) {
                $sub_menu_class = "ml20";
            }

            $extra_attr = "";
            $edit_button = "";
            $name_lang = "";
            if ($is_default_item || !$url) {
                $name_lang = lang($name);
            } else {
                //custom menu item
                $extra_attr = "data-url='$url' data-icon='$icon' data-custom_menu_item_id='" . rand(2000, 400000000) . "' data-open_in_new_tab='$open_in_new_tab'";
                $name_lang = $name;
                $edit_button = modal_anchor(get_uri("left_menus/add_menu_item_modal_form"), "<i class='fa fa-pencil'></i> ", array("title" => lang('edit'), "class" => "custom-menu-edit-button", "data-post-title" => $name, "data-post-url" => $url, "data-post-is_sub_menu" => $is_sub_menu, "data-post-icon" => $icon, "data-post-open_in_new_tab" => $open_in_new_tab));
            }

            return "<div data-value='" . $name . "' $extra_attr class='left-menu-item mb5 widget clearfix p10 bg-white $sub_menu_class'>
                        <span class='pull-left text-left'><i class='fa fa-arrows text-off pr5'></i> " . $name_lang . "</span>
                        <span class='pull-right invisible'>
                            <i class='fa fa-level-down clickable make-sub-menu font-14' title='" . lang("make_previous_items_sub_menu") . "'></i>
                            $edit_button
                            <i class='fa fa-times text-danger clickable delete-left-menu-item font-14' title=" . lang("delete") . "></i>
                        </span>
                    </div>";
        }
    }

    function get_sortable_items($type = "default") {
        $items = "<div id='menu-item-list-2' class='js-left-menu-scrollbar add-column-drop text-center p15 menu-item-list sortable-items-container'>";

        $default_left_menu_items = $this->_get_left_menu_from_setting($type);
        if (count($default_left_menu_items)) {
            foreach ($default_left_menu_items as $item) {
                $items .= $this->_get_item_data($item);
            }
        } else {
            $items .= "<span class='text-off empty-area-text'>" . lang('drag_and_drop_items_here') . "</span>";
        }

        $items .= "</div>";

        return $items;
    }

    function get_sortable_items_array($type = "default") {
        $items = [];

        $available_items = $this->_get_sidebar_menu_items();
    
        $default_left_menu_items = $this->_get_left_menu_from_setting($type);        

        // foreach ($available_items as $item){
        //     $items[] = $item;
            
        // }
        // exit;
        if (count($default_left_menu_items)) {
            foreach ($default_left_menu_items as $item) {
                // var_dump($item['name']);
                
                if(isset($available_items[$item['name']])){
                    $items[] = $available_items[$item['name']];
                }
            }   
        }

        return $items;
    }

    private function _get_item_array_value($data_array, $left_menu_items) {
        $name = get_array_value($data_array, "name");
        $url = get_array_value($data_array, "url");
        $icon = get_array_value($data_array, "icon");
        $open_in_new_tab = get_array_value($data_array, "open_in_new_tab");
        $item_value_array = array();

        if ($url) { //custom menu item
            $item_value_array = array("name" => $name, "url" => $url, "is_custom_menu_item" => true, "class" => "fa-$icon", "open_in_new_tab" => $open_in_new_tab);
        } else if (array_key_exists($name, $left_menu_items)) { //default menu items
            $item_value_array = get_array_value($left_menu_items, $name);
        }

        return $item_value_array;
    }

    public function _get_sidebar_menu_items($type = "") {
		$menus = [];
        $sql = "SELECT * FROM `left_menu` WHERE left_menu='1' ORDER BY order_number ASC;";
        $menu_list = $this->ci->dao->fetchAll( $sql );
        foreach($menu_list as $ml) {
            $json = json_decode($ml->detail);
            if($ml->class_name == "income_vs_expenses" || $ml->class_name == "invoice_payments" || $ml->class_name == "estimates" || $ml->class_name == "invoices" || $ml->class_name == "receipts") continue;

            if($json) {
                $menus[$json->name] = [];
                $menus[$json->name]["id"] = $ml->id;
                $menus[$json->name]["name"] = $json->name;
                $menus[$json->name]["url"] = $ml->url;
                $menus[$json->name]["class"] = $json->class;
                $menus[$json->name]["icon"] = $ml->icon;
            }else{
                $menus[$ml->title] = [];
                $menus[$ml->title]["id"] = $ml->id;
                $menus[$ml->title]["name"] = $ml->title;
                $menus[$ml->title]["url"] = $ml->url;
                $menus[$ml->title]["class"] = $ml->class_name;
                $menus[$ml->title]["icon"] = $ml->icon;
            }
        }

        // var_dump($menus);
        // exit;
        // return $menus;
        $dashboard_menu = array("name" => "dashboard", "url" => "dashboard", "class" => "fa-desktop dashboard-menu");

        $selected_dashboard_id = get_setting("user_" . $this->ci->login_user->id . "_dashboard");
        if ($selected_dashboard_id) {
            $dashboard_menu = array("name" => "dashboard", "url" => "dashboard/view/" . $selected_dashboard_id, "class" => "fa-desktop dashboard-menu");
        }

        if ($this->ci->login_user->user_type == "staff" && $type !== "client_default") {

            $sidebar_menu = array("dashboard" => $dashboard_menu);
            if (get_setting("module_purchaserequest") == "1") {
                $sidebar_menu["purchaserequests"] = array("name" => "purchaserequests", "url" => "purchaserequests", "class" => "fa-list-ul");
            }
            if (get_setting("module_purchaseorder") == "1") {
                $sidebar_menu["purchasereorder"] = array("name" => "purchasereorder", "url" => "purchasereorder", "class" => "fa-list-ul");
            }
            if (get_setting("module_payment_voucher") == "1") {
                $sidebar_menu["payment_vouchers"] = array("name" => "payment_vouchers", "url" => "payment_vouchers", "class" => "fa-list-ul");
            }

            $permissions = $this->ci->login_user->permissions;

            $access_expense = get_array_value($permissions, "expense");
            $access_invoice = get_array_value($permissions, "invoice");
            $access_ticket = get_array_value($permissions, "ticket");
            $access_client = get_array_value($permissions, "client");
            $access_lead = get_array_value($permissions, "lead");
            $access_timecard = get_array_value($permissions, "attendance");
            $access_leave = get_array_value($permissions, "leave");
            $access_estimate = get_array_value($permissions, "estimate");
            $access_order = get_array_value($permissions, "order");
            $access_purchaserequest = get_array_value($permissions, "purchaserequest");
            $access_payment_voucher = get_array_value($permissions, "payment_voucher");
            $access_items = ($this->ci->login_user->is_admin || $access_invoice || $access_estimate);

            $client_message_users = get_setting("client_message_users");
            $client_message_users_array = explode(",", $client_message_users);
            $access_messages = ($this->ci->login_user->is_admin || get_array_value($permissions, "message_permission") !== "no" || in_array($this->ci->login_user->id, $client_message_users_array));

            $manage_help_and_knowledge_base = ($this->ci->login_user->is_admin || get_array_value($permissions, "help_and_knowledge_base"));


            if (get_setting("module_timeline") == "1") {
                $sidebar_menu["timeline"] = array("name" => "timeline", "url" => "timeline", "class" => " fa-comments font-18");
            }

            if (get_setting("module_event") == "1") {
                $sidebar_menu["events"] = array("name" => "events", "url" => "events", "class" => "fa-calendar" );
            }

            if (get_setting("module_note") == "1") {
                $sidebar_menu["notes"] = array("name" => "notes", "url" => "notes", "class" => "fa-book font-16");
            }

            if (get_setting("module_message") == "1" && $access_messages) {
                $sidebar_menu["messages"] = array("name" => "messages", "url" => "messages", "class" => "fa-envelope", "devider" => true, "badge" => count_unread_message(), "badge_class" => "badge-secondary");
            }


            if ($this->ci->login_user->is_admin || $access_client) {
                $sidebar_menu["clients"] = array("name" => "clients", "url" => "clients", "class" => "fa-briefcase");
            }

            if (get_setting("module_lead") == "1" && ($this->ci->login_user->is_admin || $access_lead)) {
                $sidebar_menu["leads"] = array("name" => "leads", "url" => "leads", "class" => "fa-cubes");
            }

            $project_submenu = array(
                array("name" => "all_projects", "url" => "projects/all_projects"),
                array("name" => "tasks", "url" => "projects/all_tasks", "class" => "fa-tasks")
            );

            if (get_setting("module_gantt")) {
                $project_submenu[] = array("name" => "gantt", "url" => "projects/all_gantt", "class" => "fa-braille");
            }


            if (get_setting("module_project_timesheet")) {
                $project_submenu[] = array("name" => "timesheets", "url" => "projects/all_timesheets", "class" => "fa-clock-o");
            }

            $sidebar_menu["projects"] = array(
                "name" => "projects", "url" => "projects", "class" => "fa-th-large",
                "submenu" => $project_submenu
            );
            $sidebar_menu["projects_custom"] = array(
                "name" => "projects_custom", "url" => "projects/all_projects", "class" => "fa-th-large",
                "submenu" => $project_submenu
            );

            if (get_setting("module_estimate") && get_setting("module_estimate_request") && ($this->ci->login_user->is_admin || $access_estimate)) {
                $sidebar_menu["estimates"] = array("name" => "estimates", "url" => "estimates", "class" => "fa-file",
                    "submenu" => array(
                        array("name" => "estimate_list", "url" => "estimates"),
                        array("name" => "estimate_requests", "url" => "estimate_requests", "class" => "fa-file-text-o"),
                        array("name" => "estimate_forms", "url" => "estimate_requests/estimate_forms", "class" => "fa-file-o")
                    )
                );
            } else if (get_setting("module_estimate") && ($this->ci->login_user->is_admin || $access_estimate)) {
                $sidebar_menu["estimates"] = array("name" => "estimates", "url" => "estimates", "class" => "fa-file");
            }

            if (get_setting("module_invoice") == "1" && ($this->ci->login_user->is_admin || $access_invoice)) {
                $sidebar_menu["invoices"] = array("name" => "invoices", "url" => "invoices", "class" => "fa-file-text");
                $sidebar_menu["invoices_custom"] = array("name" => "invoices_custom", "url" => "invoices", "class" => "fa-file-text");
            }

            if ($access_items && (get_setting("module_invoice") == "1" || get_setting("module_estimate") == "1" )) {
                $sidebar_menu["items"] = array("name" => "items", "url" => "items", "class" => "fa-list-ul");
                $sidebar_menu["item_categories"] = array("name" => "item_categories_custom", "url" => "item_categories", "class" => "fa-list-ul");
            }

            if (get_setting("module_order") == "1" && ($this->ci->login_user->is_admin || $access_order)) {
                $order_submenu = array();
                $order_submenu[] = array("name" => "orders_list", "url" => "orders");
                $order_submenu[] = array("name" => "sales", "url" => "items/grid_view", "class" => "fa-list-ul");

                $sidebar_menu["orders"] = array("name" => "orders", "url" => "orders", "class" => "fa-ship", "submenu" => $order_submenu);
            }

            //if (get_setting("module_purchaserequest") == "1" && ($this->ci->login_user->is_admin || $access_purchaserequest)) {
            //}

            //if (get_setting("module_payment_voucher") == "1" && ($this->ci->login_user->is_admin || $access_payment_voucher)) {
            //}


            if ((get_setting("module_invoice") == "1" || get_setting("module_expense") == "1") && ($this->ci->login_user->is_admin || $access_expense || $access_invoice)) {
                $finance_submenu = array();
                $finance_url = "";
                $show_payments_menu = false;
                $show_expenses_menu = false;


                if (get_setting("module_invoice") == "1" && ($this->ci->login_user->is_admin || $access_invoice)) {
                    $finance_submenu[] = array("name" => "invoice_payments", "url" => "invoice_payments", "class" => "fa-money");
                    $finance_url = "invoice_payments";
                    $show_payments_menu = true;
                }
                if (get_setting("module_expense") == "1" && ($this->ci->login_user->is_admin || $access_expense)) {
                    $finance_submenu[] = array("name" => "expenses", "url" => "expenses", "class" => "fa-money");
                    $finance_url = "expenses";
                    $show_expenses_menu = true;
                }

                if ($show_expenses_menu && $show_payments_menu) {
                    $finance_submenu[] = array("name" => "income_vs_expenses", "url" => "expenses/income_vs_expenses", "class" => "fa-line-chart");
                }

                $sidebar_menu["finance"] = array("name" => "finance", "url" => $finance_url, "class" => "fa-money", "submenu" => $finance_submenu);
            }

            if (get_setting("module_ticket") == "1" && ($this->ci->login_user->is_admin || $access_ticket)) {

                $ticket_badge = 0;
                if ($this->ci->login_user->is_admin || $access_ticket === "all") {
                    $ticket_badge = count_new_tickets();
                } else if ($access_ticket === "specific") {
                    $specific_ticket_permission = get_array_value($permissions, "ticket_specific");
                    $ticket_badge = count_new_tickets($specific_ticket_permission);
                } else if ($access_ticket === "assigned_only") {
                    $ticket_badge = count_new_tickets("", $this->ci->login_user->id);
                }

                // 

                $sidebar_menu["tickets"] = array("name" => "tickets", "url" => "tickets", "class" => "fa-life-ring", "devider" => true, "badge" => $ticket_badge, "badge_class" => "badge-secondary");
            }


            if (get_array_value($this->ci->login_user->permissions, "hide_team_members_list") != "1") {
                $sidebar_menu["team_members"] = array("name" => "team_members", "url" => "team_members", "class" => "fa-user font-16");
            } else {
                unset($menus["team_members"]);
            }


            if (get_setting("module_attendance") == "1" && ($this->ci->login_user->is_admin || $access_timecard)) {
                $sidebar_menu["attendance"] = array("name" => "attendance", "url" => "attendance", "class" => "fa-clock-o font-16");
            } else if (get_setting("module_attendance") == "1") {
                $sidebar_menu["attendance"] = array("name" => "attendance", "url" => "attendance/attendance_info", "class" => "fa-clock-o font-16");
            }

            if (get_setting("module_leave") == "1" && ($this->ci->login_user->is_admin || $access_leave)) {
                $sidebar_menu["leaves"] = array("name" => "leaves", "url" => "leaves", "class" => "fa-sign-out font-16", "devider" => true);
            } else if (get_setting("module_leave") == "1") {
                $sidebar_menu["leaves"] = array("name" => "leaves", "url" => "leaves/leave_info", "class" => "fa-sign-out font-16", "devider" => true);
            }

            if (get_setting("module_announcement") == "1") {
                $sidebar_menu["announcements"] = array("name" => "announcements", "url" => "announcements", "class" => "fa-bullhorn");
            }

            // Stock Module
            if (get_setting("module_stock") == "1") {
                $sidebar_menu["stock_management"] = array(
                    "name" => "stock_management", "url" => "stock", "class" => "fa-building"
                );
            }


            $module_help = get_setting("module_help") == "1" ? true : false;
            $module_knowledge_base = get_setting("module_knowledge_base") == "1" ? true : false;

            //prepere the help and suppor menues
            if ($module_help || $module_knowledge_base) {

                $help_knowledge_base_menues = array();
                $main_url = "help";

                if ($module_help) {
                    $help_knowledge_base_menues[] = array("name" => "help", "url" => $main_url, "class" => "fa-question-circle");
                }

                //push the help manage menu if user has access
                if ($manage_help_and_knowledge_base && $module_help) {
                    $help_knowledge_base_menues[] = array("name" => "articles", "url" => "help/help_articles", "class" => "fa-question-circle");
                    $help_knowledge_base_menues[] = array("name" => "categories", "url" => "help/help_categories", "class" => "fa-question-circle");
                }

                if ($module_knowledge_base) {
                    $help_knowledge_base_menues[] = array("name" => "knowledge_base", "url" => "knowledge_base", "class" => "fa-question-circle-o");
                }

                //push the knowledge_base manage menu if user has access
                if ($manage_help_and_knowledge_base && $module_knowledge_base) {
                   
				   $help_knowledge_base_menues[] = array("name" => "articles", "category" => "help", "url" => "help/knowledge_base_articles", "class" => "fa-question-circle-o");
                    
					
					$help_knowledge_base_menues[] = array("name" => "categories", "category" => "help", "url" => "help/knowledge_base_categories", "class" => "fa-question-circle-o");
                }


                if (!$module_help) {
                    $main_url = "knowledge_base";
                }

                $sidebar_menu["help_and_support"] = array("name" => "help_and_support", "url" => $main_url, "class" => "fa-question-circle",
                    "submenu" => $help_knowledge_base_menues
                );
            }


            if ($this->ci->login_user->is_admin) {
                $sidebar_menu["settings"] = array("name" => "settings", "url" => "settings/general", "class" => "fa-wrench");
            }
        } else {
            //client menu
            //get the array of hidden menu
            $hidden_menu = explode(",", get_setting("hidden_client_menus"));

            $sidebar_menu[] = $dashboard_menu;
			
			
$sidebar_menu[] = array("name" => "purchaserequests", "url" => "purchaserequests", "class" => "fa-list-ul");

$sidebar_menu[] = array("name" => "payment_vouchers", "url" => "payment_vouchers", "class" => "fa-list-ul");

            if (get_setting("module_event") == "1" && !in_array("events", $hidden_menu)) {
                $sidebar_menu[] = array("name" => "events", "url" => "events", "class" => "fa-calendar");
            }

            //check message access settings for clients
            if (get_setting("module_message") && get_setting("client_message_users")) {
                $sidebar_menu[] = array("name" => "messages", "url" => "messages", "class" => "fa-envelope", "badge" => count_unread_message());
            }

            if (!in_array("projects", $hidden_menu)) {
                $sidebar_menu[] = array("name" => "projects", "url" => "projects/all_projects", "class" => "fa fa-th-large");
            }


            if (get_setting("module_estimate") && !in_array("estimates", $hidden_menu)) {
                $sidebar_menu[] = array("name" => "estimates", "url" => "estimates", "class" => "fa-file");
            }

            if (get_setting("module_invoice") == "1") {
                if (!in_array("invoices", $hidden_menu)) {
                    $sidebar_menu[] = array("name" => "invoices", "url" => "invoices", "class" => "fa-file-text");
                    $sidebar_menu[] = array("name" => "invoices_custom", "url" => "invoices", "class" => "fa-file-text");
                }
                if (!in_array("payments", $hidden_menu)) {
                    $sidebar_menu[] = array("name" => "invoice_payments", "url" => "invoice_payments", "class" => "fa-money");
                }
            }

            if (!in_array("store", $hidden_menu) && get_setting("client_can_access_store")) {
                $sidebar_menu[] = array("name" => "store", "url" => "items/grid_view", "class" => "fa-shopping-basket");
                $sidebar_menu[] = array("name" => "orders", "url" => "orders", "class" => "fa-ship");
            }

            if (get_setting("module_purchaserequest") == "1") {
                $sidebar_menu[] = array("name" => "purchaserequests", "url" => "purchaserequests", "class" => "fa-list-ul");
            }

            if (get_setting("module_purchaseorder") == "1") {
                $sidebar_menu[] = array("name" => "purchasereorder", "url" => "purchaseorder", "class" => "fa-list-ul");
            }

            if (get_setting("module_payment_voucher") == "1") {
                $sidebar_menu[] = array("name" => "payment_vouchers", "url" => "payment_vouchers", "class" => "fa-list-ul");
            }

            if (get_setting("module_ticket") == "1" && !in_array("tickets", $hidden_menu)) {
                $sidebar_menu[] = array("name" => "tickets", "url" => "tickets", "class" => "fa-life-ring");
            }

            if (get_setting("module_announcement") == "1" && !in_array("announcements", $hidden_menu)) {
                $sidebar_menu[] = array("name" => "announcements", "url" => "announcements", "class" => "fa-bullhorn");
            }

            $sidebar_menu[] = array("name" => "users", "url" => "clients/users", "class" => "fa-user");

            if (get_setting("client_can_view_files")) {
                $sidebar_menu[] = array("name" => "files", "url" => "clients/files/" . $this->ci->login_user->id . "/page_view", "class" => "fa-file-image-o");
            }

            $sidebar_menu[] = array("name" => "my_profile", "url" => "clients/contact_profile/" . $this->ci->login_user->id, "class" => "fa-cog");

            if (get_setting("module_knowledge_base") == "1" && !in_array("knowledge_base", $hidden_menu)) {
                $sidebar_menu[] = array("name" => "knowledge_base", "url" => "knowledge_base", "class" => "fa-question-circle");
            }
        }
		
        return $menus;
        // return $sidebar_menu;

        // var_dump(arr($menus)); exit;
    }

}
