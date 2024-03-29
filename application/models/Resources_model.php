<?php
class Resources_model extends Crud_model {

    private $table = null;

    function __construct() {
        $this->table = 'resources';
        parent::__construct($this->table);
    }
    
    function get_details($options = array()) {
        $items_table = $this->db->dbprefix('resources');
        $order_items_table = $this->db->dbprefix('order_items');
        $item_categories_table = $this->db->dbprefix('item_categories');

        $where = "";
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND $items_table.id=$id";
        }

        $search = get_array_value($options, "search");
        if ($search) {
            $search = $this->db->escape_str($search);
            $where .= " AND ($items_table.title LIKE '%$search%' OR $items_table.description LIKE '%$search%')";
        }

        $show_in_client_portal = get_array_value($options, "show_in_client_portal");
        if ($show_in_client_portal) {
            $where .= " AND $items_table.show_in_client_portal=1";
        }

        $category_id = get_array_value($options, "category_id");
        if ($category_id) {
            $where .= " AND $items_table.category_id=$category_id";
        }

        $extra_select = "";
        $login_user_id = get_array_value($options, "login_user_id");
        if ($login_user_id) {
            $extra_select = ", (SELECT COUNT($order_items_table.id) FROM $order_items_table WHERE $order_items_table.deleted=0 AND $order_items_table.order_id=0 AND $order_items_table.created_by=$login_user_id AND $order_items_table.item_id=$items_table.id) AS added_to_cart";
        }

        $limit_query = "";
        $limit = get_array_value($options, "limit");
        if ($limit) {
            $offset = get_array_value($options, "offset");
            $limit_query = "LIMIT $offset, $limit";
        }

        $sql = "SELECT $items_table.*, $item_categories_table.title as category_title $extra_select
        FROM $items_table
        LEFT JOIN $item_categories_table ON $item_categories_table.id= $items_table.category_id
        WHERE $items_table.deleted=0 $where
        ORDER BY $items_table.title ASC
        $limit_query";
        return $this->db->query($sql);
    }

    function get_items($options = array()) {
        $items_table = $this->db->dbprefix('resources');
        $order_items_table = $this->db->dbprefix('order_items');
        $item_categories_table = $this->db->dbprefix('item_categories');

        $where = "";
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND $items_table.id=$id";
        }

        return $this->db->query(
            "SELECT $items_table.id, $items_table.title, $items_table.unit_type 
            FROM $items_table 
            WHERE $items_table.deleted=0 $where 
            ORDER BY $items_table.title ASC"
        );
    }

}
