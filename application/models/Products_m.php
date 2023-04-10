<?php

class Products_m extends MY_Model {
    function __construct() {
        parent::__construct();
    }

    function  getRows(){
        $db = $this->db;
        $keyword = $this->input->post("keyword");

        $db->select("*")
            ->from("items")
            ->where("deleted", 0);
            

        if($keyword != ""){
            $db->like("title", $keyword);
        }

        //$db->limit(20);

        return $db->get()->result();
    }
}
