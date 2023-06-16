<?php

class Item_categories_model extends Crud_model
{

    private $table = null;

    function __construct()
    {
        $this->table = 'item_categories';
        parent::__construct($this->table);
    }

    function get_details($options = array())
    {
        $item_categories_table = $this->db->dbprefix('item_categories');
        $where = "";
        $id = get_array_value($options, "id");
        if ($id) {
            $where = " AND $item_categories_table.id=$id";
        }

        $sql = "SELECT $item_categories_table.*
        FROM $item_categories_table
        WHERE $item_categories_table.deleted=0 $where";
        return $this->db->query($sql);
    }

    function dev2_getItemCateByName($name)
    {
        $rows = 0;
        $sql = "SELECT `id` FROM `item_categories` WHERE 1 AND `deleted` = 0 AND LOWER(`title`) = '" . strtolower($name) . "'";

        if (isset($name) && strlen($name) > 0) {
            $query = $this->db->query($sql);
            $rows = $query->num_rows();
        }
        return $rows;
    }

    function dev2_getItemCateByNameWithId($name, $id)
    {
        $rows = 0;
        $sql = "SELECT `id` FROM `item_categories` WHERE 1 AND `deleted` = 0 AND LOWER(`title`) = '" . strtolower($name) . "' AND `id` != '" . $id . "'";

        if (isset($name) && strlen($name) > 0) {
            $query = $this->db->query($sql);
            $rows = $query->num_rows();
        }
        return $rows;
    }

    function dev2_getCountItemCateById($id)
    {
        $rows = 0;
        $sql = "SELECT `id` FROM `items` WHERE `category_id` = '" . $id . "'";

        if (isset($id) && $id != "0") {
            $query = $this->db->query($sql);
            $rows = $query->num_rows();
        }
        return $rows;
    }

}

?>