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

    function account_by(&$id)
    {
        $this->db->select("CONCAT(`account_code`, ' - ', `account_name`) AS `full_name`");
        $this->db->from("account_category");
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

    function get_list_dropdown()
    {
        $this->db->select("*");
        $this->db->from("account_category");

        $result = $this->db->get()->result();
        $data[] = array(
            "id" => "", "text" => "- " . lang("account_category") . " -"
        );

        foreach ($result as $item) {
            $data[] = array(
                "id" => $item->id, "text" => $item->account_code . " - " . $item->account_name
            );
        }

        return $data;
    }
}

?>