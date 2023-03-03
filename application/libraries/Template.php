<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Template {

    public function rander($view, $data = array()) {
		//echo 'aaaaaaaaaaaaaaaaaaaaaa';
		//arr( $_SESSION);
		//exit;
        $ci = get_instance();

        $view_data['content_view'] = $view;
        $view_data['topbar'] = "includes/topbar";

        if (!isset($data["left_menu"])) {
		// arr($this->login_user);

	// exit;
			
            $ci->load->library("left_menu");
			
		//$view_data['left_menu'] = $ci->left_menu->rander_left_menu();
        
//http://cosmatch/index.php/expenses/income_vs_expenses

		$view_data['left_menu'] = $ci->left_menu->gogo_left_menu();
			
			//arr( $view_data['left_menu']);
			
			//exit;
        }
 
        $view_data = array_merge($view_data, $data);

        $ci->load->view('layout/index', $view_data );
    }

}
