<?php

class Products_m extends MY_Model
{
    function __construct()
    {
        parent::__construct();
    }

    function getRows()
    {
        $db = $this->db;

        $db->select("*")
            ->from("items")
            ->where("deleted", 0);

        if ($this->input->post("keyword")) {
            $keyword = $this->input->post("keyword");
            $db->like("title", $keyword);
            $db->or_like("barcode", $keyword);
        }

        if ($this->input->get("keyword")) {
            $keyword = $this->input->get("keyword");
            $db->like("title", $keyword);
            $db->or_like("barcode", $keyword);
        }

        //$db->limit(20);

        return $db->get()->result();
    }

    function getFomulasByItemId($item_id)
    {
        $db = $this->db;

        $bimgrows = $db->select("*")
            ->from("bom_item_mixing_groups")
            ->where("item_id", $item_id)
            ->get()->result();

        $formulas = [];

        if (!empty($bimgrows)) {
            foreach ($bimgrows as $bimgrow) {
                $f = [];
                $f["id"] = $bimgrow->id;
                $f["name"] = $bimgrow->name;
                $formulas[] = $f;
            }
        }

        return $formulas;
    }

    function dev2_getItemsDropdownByKeyword()
    {
        $db = $this->db;
        $getKeyword = $this->input->get("keyword");
        $postKeyword = $this->input->post("keyword");

        $db->select("*")->from("items")->where("deleted", 0);

        if (isset($postKeyword) && !empty($postKeyword)) {
            $db->like("item_code", $postKeyword);
            $db->or_like("title", $postKeyword);
            $db->or_like("description", $postKeyword);
        }

        if (isset($getKeyword) && !empty($getKeyword)) {
            $db->like("item_code", $getKeyword);
            $db->or_like("title", $getKeyword);
            $db->or_like("description", $getKeyword);
        }

        return $db->get()->result();
    }

}
