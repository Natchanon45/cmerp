<?php

class Bom_stock_groups_model extends Crud_model {

    private $table = null;

    function __construct() {
        $this->table = 'bom_stock_groups';
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
            FROM bom_stock_groups bsg 
            LEFT JOIN users u ON u.id = bsg.created_by 
            WHERE 1 $where 
            GROUP BY bsg.id 
        ");
    }

    function delete_one($id) {
        $this->db->query("DELETE FROM bom_stock_groups WHERE id = $id");
        $this->db->query("DELETE FROM bom_stocks WHERE group_id = $id");
        return true;
    }

    function get_restocks($options = array()) {
        $where = "";
        
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND bs.id = $id";
        }
        $material_id = get_array_value($options, "material_id");
        if ($material_id) {
            $where .= " AND bs.material_id = $material_id";
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
            bm.name `material_name`, bm.unit `material_unit`, bm.noti_threshold, bm.production_name,
            bsg.name `group_name`, bsg.created_date, 
            bs.price,
            u.id `user_id`, 
            u.first_name `user_first_name`, 
            u.last_name `user_last_name`,
            u.image `user_image` 
            FROM bom_stocks bs 
            INNER JOIN bom_materials bm ON bm.id = bs.material_id 
            INNER JOIN bom_stock_groups bsg ON bsg.id = bs.group_id 
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
        $material_id = get_array_value($options, "material_id");
        if ($material_id) {
            $where .= " AND bs.material_id = $material_id";
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
        $warehouse_id = get_array_value($options, "warehouse_id");
        if ($warehouse_id) {
            $where .= " AND bm.warehouse_id = $warehouse_id";
        }
        $start_date = get_array_value($options, "start_date");
        $end_date = get_array_value($options, "end_date");
        if ((isset($start_date) && !empty($start_date)) && (isset($end_date) && !empty($end_date))) {
            $where .= " AND bsg.created_date BETWEEN '$start_date' AND '$end_date'";
        }
        $sql = "
            SELECT bs.*,
            bm.name `material_name`, bm.unit `material_unit`, bm.noti_threshold, bm.production_name, 
            bsg.name `group_name`, bsg.created_date as bsg_created_date, 
            IF(bmp.price IS NULL,'0',bmp.price) as bmpprice, 
            IF(sup.id IS NULL,0, sup.id) as supplier_id, 
            IF(sup.company_name IS NULL,'', sup.company_name) as supplier_name, 
            IF(sup.currency IS NULL,'THB',sup.currency) as currency, 
            IF(sup.currency_symbol IS NULL,'฿',sup.currency_symbol) as currency_symbol, 
            u.id `user_id`, 
            u.first_name `user_first_name`, 
            u.last_name `user_last_name`, 
            u.image `user_image` 
            FROM bom_stocks bs 
            INNER JOIN bom_materials bm ON bm.id = bs.material_id 
            INNER JOIN bom_stock_groups bsg ON bsg.id = bs.group_id 
            LEFT JOIN bom_material_pricings as bmp ON bmp.material_id=bs.material_id 
            LEFT JOIN bom_suppliers as sup ON bmp.supplier_id=sup.id 
            LEFT JOIN users u ON u.id = bsg.created_by 
            WHERE 1 $where 
            GROUP BY bs.id 
        ";

        // var_dump(arr($sql)); exit;
        return $this->db->query($sql);
    }
    
    function restock_save($group_id = 0, $restock_ids = [], $material_ids = [], $expire_date = [], $stocks = [], $prices = [], $serial_numbers = []) {
        $except_ids = array_filter($restock_ids, function($var){ return !empty($var); });
        $where = "";
        if (sizeof($except_ids)) {
            $where .= " AND id NOT IN (".implode(',', $except_ids).")";
        }
        
        $this->db->query("DELETE FROM bom_stocks WHERE group_id = $group_id $where");
        if(!empty($material_ids) && sizeof($material_ids)) {
            foreach($material_ids as $i=>$d) {

                if (empty($restock_ids[$i])||!$restock_ids[$i]) {
                    if (empty($prices)) {
                        $this->db->insert('bom_stocks', [
                            'group_id' => $group_id,
                            'material_id' => $d,
                            'expiration_date' => $expire_date[$i],
                            'stock' => $stocks[$i],
                            'remaining' => $stocks[$i],
                            'serial_number' => $serial_numbers[$i] 
                        ]);
                    } else {
                        $this->db->insert('bom_stocks', [
                            'group_id' => $group_id,
                            'material_id' => $d,
                            'expiration_date' => $expire_date[$i],
                            'stock' => $stocks[$i],
                            'remaining' => $stocks[$i],
                            'price' => $prices[$i],
                            'serial_number' => $serial_numbers[$i] 
                        ]);
                    }
                } else {
                    $this->db->set("material_id", $d)
                        ->set("expiration_date", $expire_date[$i])
                        ->set("stock", $stocks[$i])
                        ->set("price", $prices[$i])
                        ->set("serial_number", $serial_numbers[$i])
                        ->where("id", $restock_ids[$i])
                        ->update("bom_stocks");
                }

            }
        }
    }

    function dev2_deleteRestockingItemById($id)
    {
        $this->db->delete('bom_stocks', array('id' => $id));
    }

}
