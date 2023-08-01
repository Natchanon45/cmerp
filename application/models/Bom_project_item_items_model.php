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

    function postProjectItemItemFromMaterialRequest($data)
    {
        $verify = $this->db->get_where('bom_project_item_items', array(
            'mr_id' => $data['mr_id'],
            'item_id' => $data['item_id'],
            'stock_id' => $data['stock_id']
        ));

        if ($verify->num_rows() > 0) {
            return $verify->row()->id;
        } else {
            $this->db->insert('bom_project_item_items', $data);
            return $this->db->insert_id();
        }
    }

    function patchProjectItemItemFromMaterialRequest($data)
    {
        $this->db->set('stock_id', $data['stock_id']);
        $this->db->set('ratio', $data['ratio']);
        $this->db->where('id', $data['id']);
        $this->db->update('bom_project_item_items');
        return $this->db->affected_rows();
    }

    function dev2_rejectMaterialRequestById($id)
    {
        $this->db->where('id', $id)->where('entry_flag', 1)->delete('bom_project_item_items');
        
        if ($this->db->affected_rows() == 0) {
            $this->db->where('id', $id);
            $this->db->update('bom_project_item_items', array('mr_id' => null));
        }
    }

    function dev2_updateUsedStatusById($id, $status_id)
    {
        $this->db->where('id', $id);
        $this->db->update('bom_project_item_items', array('used_status' => $status_id));
    }

}
