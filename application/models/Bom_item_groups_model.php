<?php

class Bom_item_groups_model extends Crud_model {

    private $table = null;

    function __construct() {
        $this->table = 'bom_item_groups';
        $this->table2 = 'bom_item_stocks';
        parent::__construct($this->table);
    }
    

    function get_details($options = array()) {
        $where = "";
        
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND bsg.id = $id";
        }
        $created_by = get_array_value($options, "created_by");
        if ($created_by) {
            $where .= " AND bsg.created_by = $created_by";
        }

        return $this->db->query("
            SELECT bsg.*, 
            u.id `user_id`, 
            u.first_name `user_first_name`, 
            u.last_name `user_last_name`, 
            u.image user_image 
            FROM bom_item_groups bsg 
            LEFT JOIN users u ON u.id = bsg.created_by 
            WHERE 1 $where 
            GROUP BY bsg.id 
        ");
        
    }

    function delete_one($id) {
        $this->db->query("DELETE FROM bom_item_groups WHERE id = $id");
        $this->db->query("DELETE FROM bom_items WHERE group_id = $id");
        return true;
    }


    function get_restocks($options = array()) {
        $where = "";
        
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND bs.id = $id";
        }
        $item_id = get_array_value($options, "item_id");
        if ($item_id) {
            $where .= " AND bs.item_id = $item_id";
        }
        $group_id = get_array_value($options, "group_id");
        if ($group_id) {
            $where .= " AND bs.group_id = $group_id";
        }
        $created_by = get_array_value($options, "created_by");
        if ($created_by) {
            $where .= " AND bsg.created_by = $created_by";
        }
        $sql = "
            SELECT bs.*, 
            it.title `item_name`, it.unit_type `item_unit`, it.noti_threshold, 
            bsg.name `group_name`, bsg.created_date, 
            bs.price,
            u.id `user_id`, 
            u.first_name `user_first_name`, 
            u.last_name `user_last_name`,
            u.image `user_image` 
            FROM bom_item_stocks bs
            INNER JOIN items it ON it.id = bs.item_id 
            INNER JOIN bom_item_groups bsg ON bsg.id = bs.group_id 
            LEFT JOIN users u ON u.id = bsg.created_by 
            WHERE 1 $where 
            GROUP BY bs.id
            
        ";
        
        return $this->db->query($sql);

    }

    function get_restocks2($options = array()) {
         $where = "";
        
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND bs.id = $id";
        }
        $item_id = get_array_value($options, "item_id");
        if ($item_id) {
            $where .= " AND bs.item_id = $item_id";
        }
        $group_id = get_array_value($options, "group_id");
        if ($group_id) {
            $where .= " AND bs.group_id = $group_id";
        }
        $created_by = get_array_value($options, "created_by");
        if ($created_by) {
            $where .= " AND bsg.created_by = $created_by";
        }
        $is_zero = get_array_value($options, "is_zero");
        if ($is_zero == 0) {
            $where .= " AND bs.remaining > 0";
        }
        $sql = "
            SELECT bs.*, 
            bm.title `item_name`, bm.description `item_desc`, bm.unit_type `item_unit`, bm.noti_threshold, 
            bsg.name `group_name`, bsg.created_date, 
            IF(bmp.price IS NULL,'0',bmp.price) as bmpprice,
            IF(sup.id IS NULL,0, sup.id) as supplier_id,
            IF(sup.company_name IS NULL,'', sup.company_name) as supplier_name,
            IF(sup.currency IS NULL,'THB',sup.currency) as currency,
            IF(sup.currency_symbol IS NULL,'฿',sup.currency_symbol) as currency_symbol,
            u.id `user_id`, 
            u.first_name `user_first_name`, 
            u.last_name `user_last_name`,
            u.image `user_image` 
            FROM bom_item_stocks bs 
            INNER JOIN items bm ON bm.id = bs.item_id 
            INNER JOIN bom_item_groups bsg ON bsg.id = bs.group_id 
            LEFT JOIN bom_item_pricings as bmp ON bmp.item_id=bs.item_id
            LEFT JOIN bom_suppliers as sup ON bmp.supplier_id=sup.id
            LEFT JOIN users u ON u.id = bsg.created_by 
            WHERE 1 $where 
            GROUP BY bs.id 
        ";

        
        return $this->db->query($sql);
    }
    
    function restock_item_save($group_id = 0, $restock_ids = [], $item_ids = [], 
    $stocks = [], $prices = []) {
        $except_ids = array_filter($restock_ids, function($var){ return !empty($var); });
        $where = "";
        if (sizeof($except_ids)) {
            $where .= " AND id NOT IN (".implode(',', $except_ids).")";
        }
        $this->db->query("DELETE FROM {$this->table2} WHERE group_id = $group_id $where");
        if(!empty($item_ids) && sizeof($item_ids)) {
            foreach($item_ids as $i=>$d) {
                if (empty($restock_ids[$i]) || !$restock_ids[$i]) {
                    if (empty($prices)) {
                        $this->db->insert($this->table2, [
                            'group_id' => $group_id,
                            'item_id' => $d,
                            'stock' => $stocks[$i],
                            'remaining' => $stocks[$i]
                        ]);
                    } else {
                        $this->db->insert($this->table2, [
                            'group_id' => $group_id,
                            'item_id' => $d,
                            'stock' => $stocks[$i],
                            'remaining' => $stocks[$i],
                            'price' => $prices[$i]
                        ]);
                    }
                } else {
                    $this->db->query("UPDATE {$this->table2} 
                        SET item_id = '$d', stock = '$stocks[$i]', price = '$prices[$i]' 
                        WHERE id = '$restock_ids[$i]'");
                }
            }
        }
    }

}