<?php

class Test extends MY_Controller {
	function permissions(){
		$this->load->model("Permission_m");
		$this->output->set_content_type('application/json')->set_output(json_encode($this->Permission_m->get()));
	}
}