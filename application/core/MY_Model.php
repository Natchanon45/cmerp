<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Model extends CI_Model{
    protected $data;
	
    function __construct(){
        parent::__construct();
        $this->data["status"] = "fail";
        $this->data["message"] = "fail";
        $this->data["messages"] = [];
    }
}
