<?php

class Test extends MY_Controller {
	function __construct() {
        parent::__construct();
    }

	function permissions(){
		$this->output->set_content_type('application/json')->set_output(json_encode($this->Permission_m->permissions));
	}
}