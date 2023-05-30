<?php

class Bom_item_model extends Crud_model {

    private $table = null;

    function __construct() {
        $this->table = 'items';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $where = "";
        
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND bm.id = $id";
        }
        $category_id = get_array_value($options, "category_id");
        if ($category_id) {
            $where .= " AND bm.category_id = $category_id";
        }
        $exceptId = get_array_value($options, "except_id");
        if ($exceptId) {
            $where .= " AND bm.id != $exceptId";
        }

        return $this->db->query("
            SELECT bm.*, 
            bmc.title category, 
            SUM(bs.remaining) remaining 
            FROM items bm 
            LEFT JOIN item_categories bmc ON bmc.id = bm.category_id 
            LEFT JOIN bom_item_stocks bs ON bs.item_id = bm.id AND bs.remaining > 0 
            WHERE 1 $where 
            GROUP BY bm.id 
        ");
    }
    
    function delete_material_and_sub_items($item_id) {
        $this->db->query("DELETE FROM items WHERE id = $item_id");
        $this->db->query("DELETE FROM bom_item_mixings WHERE item_id = $item_id");
        $this->db->query("DELETE FROM bom_item_mixings WHERE item_id = $item_id");
        return true;
    }
    
    function duplicated_name($name) {
        $temp = $this->db->query("SELECT id FROM items WHERE title = '$name'");
        $temp = $temp->row();
        if($temp) return true;
        else return false;
    }

    function duplicated_code($code)
    {
        $temp = $this->db->query("SELECT id FROM items WHERE item_code = '" . $code . "'");
        $temp = $temp->row();

        if ($temp): return true; else: return false; endif;
    }

    function get_categories($options = array()) {
        $where = "";
        
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND bmc.id = $id";
        }

        return $this->db->query("
            SELECT bmc.* 
            FROM item_categories bmc 
            WHERE 1 $where 
        ");
    }
    function category_create($data) {
        $this->db->insert('item_categories', $data);
        return $this->db->insert_id();
    }
    function category_update($data) {
        $this->db->replace('item_categories', $data);
        return $data['id'];
    }
    function category_delete($id = 0) {
        $this->db->query("UPDATE items SET category_id = NULL WHERE category_id = $id");
        $this->db->delete('item_categories', [ 'id' => $id ]);
        return true;
    }
    function get_category_dropdown($options = array()) {
        $data = $this->get_categories($options)->result();
        $result = [
            [ 'id' => '', 'text' => '- '.lang('stock_material_category').' -' ]
        ];
        foreach($data as $d){
            $result[] = [ 'id' => $d->id, 'text' => $d->title ];
        }
        return $result;
    }
    function get_mixings($options = array()) {
        $where = "";
        
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND bmm.material_id = $id";
        }

        return $this->db->query("
            SELECT bmm.*, 
            bm.name using_material_name, bm.unit using_material_unit 
            FROM bom_material_mixings bmm 
            INNER JOIN bom_materials bm ON bm.id = bmm.using_material_id 
            WHERE 1 $where 
            GROUP BY bmm.id 
        ");
    }
    function mixing_save($material_id = 0, $ratio = 0, $using_material_ids = [], $using_ratios = []) {
        $this->db->query("DELETE FROM bom_material_mixings WHERE material_id = $material_id");
        if(!empty($using_material_ids) && sizeof($using_material_ids)){
            foreach($using_material_ids as $i=>$d) {
                if (!empty($ratio) && $ratio > 0 && !empty($using_ratios[$i]) && $using_ratios[$i] > 0) {
                    $this->db->insert('bom_material_mixings', [
                        'material_id' => $material_id,
                        'ratio' => $ratio,
                        'using_material_id' => $d,
                        'using_ratio' => $using_ratios[$i]
                    ]);
                }
            }
        }
    }
    function get_item_suggestion($keyword = "") {
        $item_table = $this->db->dbprefix('items');

        $keyword = $this->db->escape_str($keyword);

        $sql = "SELECT $item_table.`id`,concat($item_table.`item_code`,' ',$item_table.`title`) as `text`
        FROM $item_table
        WHERE $item_table.`item_code` LIKE '%$keyword%' OR $item_table.`title` LIKE '%$keyword%'
        LIMIT 10 
        ";
        return $this->db->query($sql)->result();
    }

    function get_item_info_suggestion($item_id) {
        $item_table = $this->db->dbprefix('items');

        $item_id = $this->db->escape_str($item_id);

        $sql = "SELECT $item_table.*
        FROM $item_table
        WHERE $item_table.`id` = '$item_id'
        ";

        $result = $this->db->query($sql);

        if ($result->num_rows()) {
            return $result->row();
        }
    }

    function get_low_quality_materials() {
        return $this->db->query("
            SELECT * 
            FROM (
                SELECT SUM(bs.remaining) AS total, 
                bm.id, bm.title, bm.noti_threshold, bm.unit 
                FROM bom_item_stocks AS bs 
                INNER JOIN items AS bm ON bm.id = bs.item_id 
                    AND bm.noti_threshold IS NOT NULL AND bm.noti_threshold > 0 
                GROUP BY bs.item_id 
            ) AS db 
            WHERE db.total < db.noti_threshold 
        ");
    }

}
