<?php

class Products_m extends MY_Model {
    function __construct() {
        parent::__construct();
    }

    function  getRows(){
        $db = $this->db;
        
        $db->select("*")
            ->from("items")
            ->where("deleted", 0);

        if ($this->input->post("keyword")){
            $keyword = $this->input->post("keyword");
            $db->like("title", $keyword);
            $db->or_like("barcode", $keyword);
        }

        if ($this->input->get("keyword")){
            $keyword = $this->input->get("keyword");
            $db->like("title", $keyword);
            $db->or_like("barcode", $keyword);
        }

        //$db->limit(20);

        return $db->get()->result();
    }
}
