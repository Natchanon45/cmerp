<?php

class Delivery_items_model extends Crud_model
{

    private $table = null;

    function __construct()
    {
        $this->table = 'delivery_items';
        parent::__construct($this->table);
    }

    function get_details($options = array())
    {
        $delivery_items_table = $this->db->dbprefix('delivery_items');
        $deliverys_table = $this->db->dbprefix('deliverys');
        $clients_table = $this->db->dbprefix('clients');
        $where = "";
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND $delivery_items_table.id=$id";
        }
        $delivery_id = get_array_value($options, "delivery_id");
        if ($delivery_id) {
            $where .= " AND $delivery_items_table.delivery_id=$delivery_id";
        }

        $sql = "SELECT $delivery_items_table.*, (SELECT $clients_table.currency_symbol FROM $clients_table WHERE $clients_table.id=$deliverys_table.client_id limit 1) AS currency_symbol
        FROM $delivery_items_table
        LEFT JOIN $deliverys_table ON $deliverys_table.id=$delivery_items_table.delivery_id
        WHERE $delivery_items_table.deleted=0 $where
        ORDER BY $delivery_items_table.sort ASC";
        return $this->db->query($sql);
    }
}
