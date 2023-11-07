<?php

class Bom_item_mixing_groups_model extends Crud_model {

    private $table = null;

    function __construct() {
        $this->table = 'bom_item_mixing_groups';
        parent::__construct($this->table);
    }


    function get_details($options = array()) {
        $where = "";
        
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND bimg.id = $id";
        }
        $item_id = get_array_value($options, "item_id");
        if ($item_id) {
            $where .= " AND bimg.item_id = $item_id";
        }
        $cat_id = get_array_value($options, "cat_id");
        if ($cat_id) {
            $where .= " AND bimg.cat_id = $cat_id";
        }
        $for_client_id = get_array_value($options, "for_client_id");
        if ($for_client_id) {
            $where .= " AND ( bimg.for_client_id = $for_client_id OR bimg.is_public = 1 )";
        }

        return $this->db->query("
            SELECT bimg.*, 
            i.title, i.description, i.unit_type,
            c.company_name
            FROM bom_item_mixing_groups bimg 
            INNER JOIN items i ON i.id = bimg.item_id 
            LEFT JOIN clients c ON c.id = bimg.for_client_id 
            WHERE 1 $where 
            GROUP BY bimg.id 
        ");
    }

    function get_category_details($options = array()) {
        $where = "";
        
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND bmc.id = $id";
        }

        $type = get_array_value($options, "type");
        if ($type) {
            $where .= " AND bmc.item_type = '" . $type . "'";
        }

        $sql = "SELECT bmc.* FROM material_categories bmc WHERE 1 $where";
        return $this->db->query($sql);
    }

    function get_file_details($options = array()) {
        $where = "";
        
        $id = get_array_value($options, "id");
        if (isset($options['id'])) {
            $where .= ($where?' AND ':'')." f.id = '$id'";
        }

        $ref_id = get_array_value($options, "ref_id");
        if (isset($options['ref_id']) && $ref_id) {
            $where .= ($where?' AND ':'')." f.ref_id = '$ref_id'";
        }

        $tablename = get_array_value($options, "tablename");
        if (isset($options['tablename']) && $tablename) {
            $where .= ($where?' AND ':'')." f.tablename = '$tablename'";
        }
        
        $sql = "SELECT f.* FROM files as f ".($where?' WHERE '.$where:'');
        return $this->db->query($sql);
    }

    function get_detail_items($options = array()) {
        $where = "";
        
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND bimg.id = $id";
        }
        $item_id = get_array_value($options, "item_id");
        if ($item_id) {
            $where .= " AND bimg.item_id = $item_id";
        }
        $for_client_id = get_array_value($options, "for_client_id");
        if ($for_client_id) {
            $where .= " AND ( bimg.for_client_id = $for_client_id OR bimg.is_public = 1 )";
        }

        return $this->db->query("
            SELECT bimg.*, 
            i.title, i.unit_type, 
            c.company_name 
            FROM bom_item_mixing_groups bimg 
            INNER JOIN items i ON i.id = bimg.item_id 
            LEFT JOIN clients c ON c.id = bimg.for_client_id 
            WHERE 1 $where 
            GROUP BY bimg.id
        ");
    }

    function savecategorry($data=[], $id=0) {
        $table = $this->table;
        $this->setTable('bom_mixing_categories');
        $save_id = $this->save($data, $id);
        $this->setTable($table );
        return $save_id;
    }

    function save_file($data=[], $id=0) {
        $table = $this->table;
        $this->setTable('files');
        $save_id = $this->save($data, $id);
        $this->setTable($table );
        return $save_id;
    }

    function delete_mixing($group_id) {
        $this->db->query("DELETE FROM bom_item_mixing_groups WHERE id = $group_id");
        $this->db->query("DELETE FROM bom_item_mixings WHERE group_id = $group_id");
        return true;
    }

    function delete_mixingcategory($id) {
        $sql = "SELECT * FROM bom_item_mixings WHERE cat_id='{$id}'";
        $rows = $this->db->query($sql)->result();
        if(count($rows)>0) {
            return false;
        }
        $this->db->query("DELETE FROM bom_mixing_categories WHERE id = $id");
        return true;
    }

    function get_categories_list() {
        $rows = $this->get_category_details(["type" => "RM"])->result();
        $options = [];
        foreach($rows as $row) {
            $options[$row->id] = $row->title;
        }
        return $options;
    }

    function get_categories_list_sfg() {
        $rows = $this->get_category_details(["type" => "SFG"])->result();
        $options = [];
        foreach($rows as $row) {
            $options[$row->id] = $row->title;
        }
        return $options;
    }

    function get_mixings($options = array())
    {
        $where = "";
        
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND bim.id = $id";
        }

        $group_id = get_array_value($options, "group_id");
        if ($group_id) {
            $where .= " AND bim.group_id = $group_id";
        }

        $material_id = get_array_value($options, "material_id");
        if ($material_id) {
            $where .= " AND bim.material_id = $material_id";
        }

        $sql = "
            SELECT bim.*, bm.name material_name, bm.unit material_unit 
            FROM bom_item_mixings bim 
            INNER JOIN bom_materials bm ON bm.id = bim.material_id 
            WHERE 1 AND bim.item_type = 'RM' $where 
            GROUP BY bim.id 
        ";
        return $this->db->query($sql);
    }

    function get_mixings_sfg($options = array())
    {
        $where = "";
        
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND bim.id = $id";
        }

        $group_id = get_array_value($options, "group_id");
        if ($group_id) {
            $where .= " AND bim.group_id = $group_id";
        }

        $material_id = get_array_value($options, "material_id");
        if ($material_id) {
            $where .= " AND bim.material_id = $material_id";
        }

        $sql = "
            SELECT bim.*, bm.title material_name, bm.unit_type material_unit 
            FROM bom_item_mixings bim 
            INNER JOIN items bm ON bm.id = bim.material_id AND bm.item_type = 'SFG' 
            WHERE 1 AND bim.item_type = 'SFG' $where 
            GROUP BY bim.id 
        ";
        return $this->db->query($sql);
    }

    function mixing_save($group_id = 0, $material_ids = [], $cat_ids = [], $ratios = [], $item_types = []) {
        $this->db->query("DELETE FROM bom_item_mixings WHERE group_id = $group_id");
        if(!empty($cat_ids) && sizeof($cat_ids)){
            foreach($cat_ids as $cat_temp_id=>$cat_id) {
                if(!empty($material_ids[$cat_temp_id]) && sizeof($material_ids[$cat_temp_id])){
                    foreach($material_ids[$cat_temp_id] as $i=>$material_id) {
                        if (!empty($ratios[$cat_temp_id]) && $ratios[$cat_temp_id] > 0) {
                            $data = [
                                'group_id' => $group_id,
                                'material_id' => $material_id,
                                'cat_id' => $cat_id,
                                'item_type' => $item_types[$cat_temp_id][$i],
                                'ratio' => $ratios[$cat_temp_id][$i]
                            ];
                            //var_dump($data);
                            $this->db->insert('bom_item_mixings', $data);
                        }
                    }
                }
            }
        }
    }

    function project_items_save($project_id , $item_ids = [], $item_mixings = [], $quantities = [], $mr_id = 0) {
        $possible = true;
        $project = $this->db->query("SELECT p.id, p.title FROM projects p WHERE p.id = $project_id ")->row();
        
        if(!empty($item_ids) && sizeof($item_ids)){ 
            foreach($item_ids as $i=>$d) {
                // var_dump($d);
                if ($quantities[$i] > 0) {
                    $group_id = !empty($item_mixings[$i])? $item_mixings[$i]: null;
                    $quantity = floatval($quantities[$i]);
                    $this->db->insert('bom_project_items', [
                        'project_id' => $project_id,
                        'item_id' => $d,
                        'mixing_group_id' => $group_id,
                        'quantity' => $quantity
                    ]);
                    $save_id = $this->db->insert_id();
                    // If the item has mixings
                    if($group_id) {
                        $materials = $this->db->query("
                            SELECT bim.id, bim.material_id, bim.ratio, bm.*
                            FROM bom_item_mixings bim 
                            INNER JOIN bom_materials bm ON bm.id = bim.material_id 
                            WHERE bim.group_id = $group_id 
                        ")->result();
                        
                        if(sizeof($materials)){ 
                            foreach($materials as $m){
                                
                                $stocks = $this->db->query("
                                    SELECT bs.id, bs.group_id, bs.material_id, bs.remaining 
                                    FROM bom_stocks bs 
                                    INNER JOIN bom_stock_groups bsg ON bsg.id = bs.group_id 
                                    WHERE bs.material_id = $m->material_id AND bs.remaining > 0 
                                    ORDER BY bsg.created_date ASC 
                                ")->result();
                                // If a material in stocks
                                $total = $quantity * floatval($m->ratio);
                                $mr_quantity = $total;
                                if(sizeof($stocks)){ 
                                    $flag = true;
                                    foreach($stocks as $s){
                                        if($total > 0) {
                                            $remaining = floatval($s->remaining);
                                            $mr_quantity = $used = min($total, $remaining);
                                            $total -= $used;
                                            $remaining -= $used;

                                            /* $this->db->query("UPDATE bom_stocks SET remaining = $remaining WHERE id = $s->id"); */
                                            $this->db->insert('bom_project_item_materials', [
                                                'project_item_id' => $save_id,
                                                'material_id' => $m->material_id,
                                                'stock_id' => $s->id,
                                                'ratio' => $used
                                            ]);

                                            if($mr_quantity > 0){
                                                $sql = "
                                                    INSERT INTO `mr_items` 
                                                    (`mr_id`, `project_id`, `project_name`, `code`, `title`, `description`, `item_type`, `quantity`, `unit_type`, `rate`, `total`, `currency_symbol`, `created_by`, `item_id`,`material_id`) 
                                                        VALUES 
                                                    ( ".$mr_id." ,".$project->id.",'".$project->title."','".$m->name."','".$m->production_name."','".$m->description."','".$m->type."','".$mr_quantity."','".$m->unit."','0','0','0','".$this->login_user->id."','0','".$m->material_id."'); 
                                                    ";
                                                $this->dao->execDatas($sql);
                                                $flag = false;
                                            }
                                        }
                                    }
                                }
                                if($total > 0){
                                    $this->db->insert('bom_project_item_materials', [
                                        'project_item_id' => $save_id,
                                        'material_id' => $m->material_id,
                                        'ratio' => $total * -1
                                    ]);
                                    $possible = false;
                                }
                            }
                            //If all passed, Prove the MR
                            
                        }
                        
                    }
                }
            }
            return $possible;
        }
    }

    function dev2_get_project_item_for_mr($project_id)
    {
        $sql = "SELECT bpim.id, bpim.stock_id, bpim.material_id, bm.name, bm.production_name, bm.description, bm.unit, bpim.ratio 
        FROM bom_project_item_materials AS bpim 
        LEFT JOIN bom_project_items AS bpi ON bpi.id = bpim.project_item_id 
        LEFT JOIN bom_materials AS bm ON bpim.material_id = bm.id 
        WHERE bpi.project_id = '" . $project_id . "' AND bpim.ratio > 0 AND bpim.mr_id IS NULL";
        
        $query = $this->db->query($sql);
        return $query->result();
    }

    function dev2_getProjectItemForRecalcByProjectId($project_id)
    {
        $sql = "SELECT bpim.id, bpim.stock_id, bpim.material_id, bm.name, bm.production_name, bm.description, bm.unit, bpim.ratio, bpim.mr_id 
        FROM bom_project_item_materials AS bpim 
        LEFT JOIN bom_project_items AS bpi ON bpi.id = bpim.project_item_id 
        LEFT JOIN bom_materials AS bm ON bpim.material_id = bm.id 
        WHERE bpi.project_id = '" . $project_id . "' AND bpim.mr_id IS NULL";

        $query = $this->db->query($sql);
        return $query->result();
    }

    function dev2_getStockRemainingByMaterialId($material_id)
    {
        $stock_sql = "
        SELECT bs.id, bs.group_id, bs.material_id, bs.stock, bs.remaining, 
        IFNULL(bpim.used, 0) AS used, bs.stock - IFNULL(bpim.used, 0) AS actual_remain 
        FROM bom_stocks bs 
        INNER JOIN bom_stock_groups bsg ON bsg.id = bs.group_id 
        LEFT JOIN(
            SELECT stock_id, SUM(ratio) AS used 
            FROM bom_project_item_materials 
            WHERE material_id = '" . $material_id . "' 
            GROUP BY stock_id
        ) AS bpim ON bs.id = bpim.stock_id 
        WHERE bs.material_id = '" . $material_id . "' AND bs.remaining > 0 AND bs.stock - IFNULL(bpim.used, 0) > 0 
        ORDER BY bsg.created_date ASC
        ";

        $stocks = $this->db->query($stock_sql)->result();
        return $stocks;
    }

    function dev2_save_project_item($project_id, $item_ids = [], $item_mixings = [], $quantities = [])
    {
        if (!empty($item_ids) && sizeof($item_ids)) {
            foreach ($item_ids as $i => $d) {
                if ($quantities[$i] > 0) {
                    $group_id = !empty($item_mixings[$i])? $item_mixings[$i]: null;
                    $quantity = floatval($quantities[$i]);

                    $this->db->insert('bom_project_items', [
                        'project_id' => $project_id,
                        'item_id' => $d,
                        'mixing_group_id' => $group_id,
                        'quantity' => $quantity
                    ]);
                    $save_bom_project_items_id = $this->db->insert_id();

                    if ($group_id) {
                        $materials_sql = "SELECT bim.material_id, bim.ratio, bm.* FROM bom_materials bm RIGHT JOIN(SELECT material_id, SUM(ratio) AS ratio FROM bom_item_mixings WHERE group_id = '" . $group_id . "' GROUP BY material_id) AS bim ON bm.id = bim.material_id";
                        $materials = $this->db->query($materials_sql)->result();

                        if (sizeof($materials)) {
                            foreach ($materials as $m) {
                                $total_ratio = $quantity * floatval($m->ratio);

                                // $stock_sql = "SELECT bs.id, bs.group_id, bs.material_id, bs.remaining FROM bom_stocks bs INNER JOIN bom_stock_groups bsg ON bsg.id = bs.group_id WHERE bs.material_id = '" . $m->material_id . "' AND bs.remaining > 0 ORDER BY bsg.created_date ASC";
                                $stock_sql = "
                                SELECT bs.id, bs.group_id, bs.material_id, bs.stock, bs.remaining, 
                                IFNULL(bpim.used, 0) AS used, bs.stock - IFNULL(bpim.used, 0) AS actual_remain 
                                FROM bom_stocks bs 
                                INNER JOIN bom_stock_groups bsg ON bsg.id = bs.group_id 
                                LEFT JOIN(
                                    SELECT stock_id, SUM(ratio) AS used 
                                    FROM bom_project_item_materials 
                                    WHERE material_id = '" . $m->material_id . "' 
                                    GROUP BY stock_id
                                ) AS bpim ON bs.id = bpim.stock_id 
                                WHERE bs.material_id = '" . $m->material_id . "' AND bs.remaining > 0 AND bs.stock - IFNULL(bpim.used, 0) > 0 
                                ORDER BY bsg.created_date ASC
                                ";
                                $stocks = $this->db->query($stock_sql)->result();

                                if (sizeof($stocks)) {
                                    foreach ($stocks as $s) {
                                        if ($total_ratio > 0) {
                                            $remaining = floatval(min($s->remaining, $s->actual_remain));
                                            $used = min($total_ratio, $remaining);
                                            $total_ratio -= $used;

                                            $this->db->insert('bom_project_item_materials', [
                                                'project_item_id' => $save_bom_project_items_id,
                                                'material_id' => $m->material_id,
                                                'stock_id' => $s->id,
                                                'ratio' => $used
                                            ]);
                                        }
                                    }
                                }

                                if ($total_ratio > 0) {
                                    $this->db->insert('bom_project_item_materials', [
                                        'project_item_id' => $save_bom_project_items_id,
                                        'material_id' => $m->material_id,
                                        'ratio' => $total_ratio * -1
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    function calculate($item_ids = [], $item_mixings = [], $quantities = []) {
        $result = [];
        if(!empty($item_ids) && sizeof($item_ids)){ foreach($item_ids as $i=>$d) {
            if ($quantities[$i] > 0) {
                $item_id = $item_ids[$i];
                $item = $this->db->query("
                    SELECT id, title, description, unit_type, rate 
                    FROM items WHERE id = $item_id LIMIT 1 
                ")->row();
                
                $quantity = floatval($quantities[$i]);
                $item->quantity = $quantity;

                $group_id = !empty($item_mixings[$i])? $item_mixings[$i]: null;
                if($group_id) {
                    $group = $this->db->query("
                        SELECT id, name, ratio 
                        FROM bom_item_mixing_groups 
                        WHERE id = $group_id AND item_id = $item_id 
                        LIMIT 1 
                    ")->row();
                    $item->mixing_id = $group_id;
                    $item->mixing_name = $group->name;
                    $item->result = [];

                    $materials = $this->db->query("
                        SELECT bim.id, bim.material_id, bim.ratio, bm.type, 
                        bm.name material_name, bm.unit material_unit 
                        FROM bom_item_mixings bim 
                        INNER JOIN bom_materials bm ON bm.id = bim.material_id 
                        WHERE bim.group_id = $group_id 
                    ")->result();
                    // Mateials for mixings
                    if(sizeof($materials)){ foreach($materials as $m){
                        $stocks = $this->db->query("
                            SELECT bs.id, bs.group_id, bs.material_id, 
                            bs.remaining, bs.stock, bs.price, 
                            bsg.name stock_name 
                            FROM bom_stocks bs 
                            INNER JOIN bom_stock_groups bsg ON bsg.id = bs.group_id 
                            WHERE bs.material_id = $m->material_id AND bs.remaining > 0 
                            ORDER BY bsg.created_date ASC 
                        ")->result();
                        // If a material in stocks
                        $total = $quantity * floatval($m->ratio);
                        if(sizeof($stocks)){ foreach($stocks as $s){
                            if($total > 0) {
                                $remaining = floatval($s->remaining);
                                $used = min($total, $remaining);
                                $total -= $used;

                                $value = 0;
                                if($s->stock > 0){
                                    $value = $used * $s->price / $s->stock;
                                }
                                $s->value = $value;

                                $s->ratio = strval($used)."asdasd";
                                $s->material_name = $m->material_name;
                                $s->material_unit = $m->material_unit;
                                $item->result[] = $s;
                            }
                        }}
                        if($total > 0){
                            $item->result[] = (object) [
                                'ratio' => strval($total * -1)."sadasd",
                                'material_name' => $m->material_name,
                                'material_unit' => $m->material_unit 
                            ];
                        }
                    }}
                }

                $result[] = $item;
            }
        }}
        return $result;
    }

    function restock_process($project_id = 0) {
        $project_materials = $this->db->query("
            SELECT bpim.* 
            FROM bom_project_item_materials bpim 
            INNER JOIN bom_project_items bpi ON bpi.id = bpim.project_item_id 
                AND bpi.project_id = $project_id 
            WHERE bpim.ratio < 0 
            GROUP BY bpim.id 
        ")->result();
        if(sizeof($project_materials)){ foreach($project_materials as $m){
            $stocks = $this->db->query("
                SELECT bs.id, bs.group_id, bs.material_id, bs.remaining 
                FROM bom_stocks bs 
                INNER JOIN bom_stock_groups bsg ON bsg.id = bs.group_id 
                WHERE bs.material_id = $m->material_id AND bs.remaining > 0 
                ORDER BY bsg.created_date ASC 
            ")->result();
            // If a material in stocks
            $total = floatval($m->ratio);
            $total *= -1;
            if(sizeof($stocks)){
                $this->db->query("DELETE FROM bom_project_item_materials WHERE id = $m->id");
                foreach($stocks as $s){
                    if($total > 0) {
                        $remaining = floatval($s->remaining);
                        $used = min($total, $remaining);
                        $total -= $used;
                        $remaining -= $used;
                        $this->db->query("UPDATE bom_stocks SET remaining = $remaining WHERE id = $s->id");
                        $this->db->insert('bom_project_item_materials', [
                            'project_item_id' => $m->project_item_id,
                            'material_id' => $m->material_id,
                            'stock_id' => $s->id,
                            'ratio' => $used
                        ]);
                    }
                }
                if($total > 0){
                    $this->db->insert('bom_project_item_materials', [
                        'project_item_id' => $m->project_item_id,
                        'material_id' => $m->material_id,
                        'ratio' => $total * -1
                    ]);
                }
            }
        }}
    }

    function get_project_items($options = array()) {
        $where = "";
        
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND bpi.id = $id";
        }
        $project_id = get_array_value($options, "project_id");
        if ($project_id) {
            $where .= " AND bpi.project_id = $project_id";
        }

        return $this->db->query("
            SELECT bpi.*, 
            i.title, i.unit_type, 
            bimg.name mixing_name 
            FROM bom_project_items bpi 
            INNER JOIN items i ON i.id = bpi.item_id 
            LEFT JOIN bom_item_mixing_groups bimg ON bimg.id = bpi.mixing_group_id 
            WHERE 1 $where 
            GROUP BY bpi.id 
        ");
    }

    function get_project_materials($project_items) {
        $result = [];
        foreach($project_items as $pi) {
            $temp = $pi;
            $sql = "
                SELECT bm.id,bpim.from_mixing,bpim.ratio,bpim.mr_id, 
                bsg.name stock_name, bsg.created_date stock_created_date, 
                bm.name material_name, bm.production_name material_desc, bm.unit material_unit, bm.noti_threshold,
                bs.stock, bs.price, bs.remaining,bpim.project_item_id as bpim_Pid,
                IF(bmp.price IS NULL,'0',bmp.price) as price2,
                IF(sup.id IS NULL, 0, sup.id) as supplier_id,
                IF(sup.company_name IS NULL,'', sup.company_name) as supplier_name,
                IF(sup.currency IS NULL,'THB',sup.currency) as currency,
                IF(sup.currency_symbol IS NULL,'฿',sup.currency_symbol) as currency_symbol
                FROM bom_project_item_materials bpim 
                INNER JOIN bom_project_items bpi ON bpi.id = bpim.project_item_id 
                INNER JOIN bom_materials bm ON bm.id = bpim.material_id 
                LEFT JOIN bom_stocks bs ON bs.id = bpim.stock_id 
                LEFT JOIN bom_stock_groups bsg ON bsg.id = bs.group_id
                LEFT JOIN (SELECT material_id, supplier_id,price FROM bom_material_pricings WHERE 1 ORDER BY price ASC) as bmp ON bmp.material_id=bm.id
                LEFT JOIN bom_suppliers as sup ON bmp.supplier_id=sup.id
                WHERE bpim.project_item_id = '{$pi->id}' 
                GROUP BY bpim.id 
                ORDER BY bm.id ASC, stock_created_date ASC  
            ";
            
            $res = $this->db->query($sql)->result();
            if (sizeof($res)) {
                foreach ($res as $s) {
                    // setting mr_doc
                    $mr_doc = "";
                    if (isset($s->mr_id) && !empty($s->mr_id)) {
                        $mr_doc = $this->dev2_getMaterialRequestDocById($s->mr_id);
                    }
                    $s->mr_doc = $mr_doc;
                    
                    // setting value
                    $value = 0;
                    if ($s->stock > 0) {
                        $value = $s->ratio * $s->price / $s->stock;
                    }
                    $s->value = $value;
                }
                $temp->result = $res;
            }
            $result[] = $temp;
        }
        return $result;
    }

    function dev2_getCountMixingByMaterialId($id)
    {
        $query = $this->db->get_where('bom_item_mixings', ['material_id' => $id]);
        return $query->num_rows();
    }

    function dev2_getCountMixingByItemId($id)
    {
        $query = $this->db->get_where('bom_item_mixing_groups', ['item_id' => $id]);
        return $query->num_rows();
    }

    function dev2_getMaterialRequestDocById($id)
    {
        $this->db->select('doc_no')->from('materialrequests')->where('id', $id);

        $query = $this->db->get();
        return $query->row()->doc_no;
    }

}
