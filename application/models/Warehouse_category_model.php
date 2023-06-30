<?php

class Warehouse_category_model extends Crud_model
{
    function get($id = 0)
    {
        $this->db->select('*');
        $this->db->from('store_location');

        if ($id !== 0) {
            $this->db->where('id', $id);
        }

        $query = $this->db->get();
        return $query->result();
    }

    function put($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update('store_location', $data);
    }

    function post($data)
    {
        $this->db->insert('store_location', $data);
        $insert_id = $this->db->insert_id();

        return $insert_id;
    }

    function dropdown()
    {
        $this->db->select("*");
        $this->db->from("store_location");

        $result = $this->db->get()->result();
        $data[] = array(
            "id" => "", "text" => "- " . lang("warehouse_category") . " -"
        );

        foreach ($result as $item) {
            $data[] = array(
                "id" => $item->id, "text" => $item->location_code . " - " . $item->location_name
            );
        }

        return $data;
    }

    function dev2_getCountWarehouseCateByCode($code = 0)
    {
        $rows = 0;
        $sql = "SELECT `id` FROM `store_location` WHERE `location_code` = '" . strtolower($code) . "'";

        if (isset($code) && $code != 0) {
            $query = $this->db->query($sql);
            $rows = $query->num_rows();
        }
        return $rows;
    }

    function dev2_getCountWarehouseCateByCodeWithId($code = 0, $id = 0)
    {
        $rows = 0;
        $sql = "SELECT `id` FROM `store_location` WHERE `location_code` = '" . strtolower($code) . "' AND `id` != '" . $id . "'";

        if (isset($code) && $code != 0) {
            $query = $this->db->query($sql);
            $rows = $query->num_rows();
        }
        return $rows;
    }
    
}

?>