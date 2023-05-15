<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Model extends CI_Model{
    protected $data;
    protected $json;
	
    function __construct(){
        parent::__construct();
        $this->data["status"] = "fail";
        $this->data["message"] = lang('error_occurred');
        $this->json = json_decode(file_get_contents('php://input'));
    }
}
