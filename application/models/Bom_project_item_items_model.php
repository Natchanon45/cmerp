<?php

class Bom_project_item_items_model extends Crud_model {

    private $table = null;

    function __construct() {
        $this->table = 'bom_project_item_items';
        parent::__construct($this->table);
    }


    function get_details($options = array()) {
        $where = "";
        
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND bpim.id = $id";
        }
        $item_id = get_array_value($options, "item_id");
        if ($item_id) {
            $where .= " AND bpim.item_id = $item_id";
        }
        $restock_id = get_array_value($options, "restock_id");
        if ($restock_id) {
            $where .= " AND bsg.id = $restock_id";
        }
        $created_by = get_array_value($options, "created_by");
        if ($created_by) {
            $where .= " AND bsg.created_by = $created_by";
        }

        return $this->db->query("
            SELECT bpim.*, 
            bm.title item_name, bm.unit_type item_unit, bm.item_code, 
            p.id project_id, p.title project_title, 
            bsg.id group_id, bsg.name stock_name, 
            bs.price, bs.stock 
            FROM bom_project_item_items bpim 
            INNER JOIN items bm ON bm.id = bpim.item_id 
            INNER JOIN bom_item_stocks bs ON bs.id = bpim.stock_id 
            INNER JOIN bom_item_groups bsg ON bsg.id = bs.group_id 
            LEFT JOIN bom_project_items bpi ON bpi.id = bpim.project_item_id 
            LEFT JOIN projects p ON p.id = bpi.project_id 
            WHERE 1 $where 
            GROUP BY bpim.id 
        ");
    }

    public function getCountStockUsedById($id)
    {
        $query = $this->db->get_where('bom_project_item_items', array('stock_id' => $id));
        return $query->num_rows();
    }

}
