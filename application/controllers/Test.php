<?php

class Test extends MY_Controller {
	function __construct() {
        parent::__construct();
    }

	function permissions(){
		$this->output->set_content_type('application/json')->set_output(json_encode($this->Permission_m->permissions));
	}

	//check permisison from object's poperty
	function permissions2(){
		unset($this->Permission_m->permissions);
		jout($this->Permission_m);
	}
}