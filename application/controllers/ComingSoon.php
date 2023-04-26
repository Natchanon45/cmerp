<?php

class Comingsoon extends CI_Controller
{
	public function index()
	{
		$view_data["heading"] = "Coming Soon";	
		$view_data["message"] = "This feature will be available soon.";
		if ($this->input->is_ajax_request()) {
			$view_data["no_css"] = true;
		}
		$this->load->view("errors/html/error_comingsoon", $view_data);
	}
}
