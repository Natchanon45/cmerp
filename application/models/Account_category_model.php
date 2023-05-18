<?php

class Account_category_model extends Crud_model
{
    function get($id = 0)
    {
        $this->db->select("*");
        $this->db->from("account_category");

        if ($id !== 0) {
            $this->db->where("id", $id);
        }

        $query = $this->db->get();
        return $query->result();
    }

    function created_by(&$id)
    {
        $this->db->select("CONCAT(`first_name`, ' ', `last_name`) AS `full_name`");
        $this->db->from("users");
        $this->db->where("id", $id);

        $query = $this->db->get();
        return $query->row()->full_name;
    }

    function set($data = array())
    {
        $this->db->insert("account_category", $data);

        $insert_id = $this->db->insert_id();
        return $insert_id;
    }
}

?>