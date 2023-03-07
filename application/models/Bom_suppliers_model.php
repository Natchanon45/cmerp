<?php

class Bom_suppliers_model extends Crud_model {

    private $table = null;

    function __construct() {
        $this->table = 'bom_suppliers';
        parent::__construct($this->table);
    }


    function get_details($options = array()) {
        $where = "";
        
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND bs.id = $id";
        }
        $owner_id = get_array_value($options, "owner_id");
        if ($owner_id) {
            $where .= " AND bs.owner_id = $owner_id";
        }

        return $this->db->query("
            SELECT bs.*, 
            bsc.first_name contact_first_name, 
            bsc.last_name contact_last_name, 
            bsc.phone contact_phone, bsc.email contact_email, 
            IFNULL(bmp.material_id, 0) material_pricing_id 
            FROM bom_suppliers bs 
            LEFT JOIN bom_supplier_contacts bsc ON bsc.supplier_id = bs.id 
                AND bsc.is_primary = 1 
            LEFT JOIN bom_material_pricings bmp ON bmp.supplier_id = bs.id 
            WHERE 1 $where 
            GROUP BY bs.id 
        ");
    }

    function get_options($options = array()) {
        $where = "";
        
        $owner_id = get_array_value($options, "owner_id");
        if ($owner_id) {
            $where .= " AND bs.owner_id = $owner_id";
        }

        return $this->db->query("
            SELECT bs.id, bs.company_name as `title`
            FROM bom_suppliers bs 
            WHERE 1 $where 
        ");
    }
    
    function is_duplicate_company_name($company_name, $id = 0) {
        $result = $this->get_all_where(array("company_name" => $company_name));
        if ($result->num_rows() && $result->row()->id != $id) {
            return $result->row();
        } else {
            return false;
        }
    }
    function delete_supplier_and_sub_items($supplier_id) {
        $this->db->query("DELETE FROM bom_suppliers WHERE id = $supplier_id");
        $this->db->query("DELETE FROM bom_supplier_contacts WHERE supplier_id = $supplier_id");
        $this->db->query("DELETE FROM bom_material_pricings WHERE supplier_id = $supplier_id");
        return true;
    }
    
    
    function get_supplier_pricing_dropdown($material_id = 0) {
        $where = "";
        if($material_id) {
            $where = " AND db.material_pricing_id != $material_id";
        }
        $query = $this->db->query("
            SELECT db.* 
            FROM (
                SELECT bs.*, 
                bsc.first_name contact_first_name, 
                bsc.last_name contact_last_name, 
                bsc.phone contact_phone, bsc.email contact_email, 
                IFNULL(bmp.material_id, 0) material_pricing_id, 
                IFNULL(bmp.supplier_id, 0) supplier_pricing_id 
                FROM bom_suppliers bs 
                LEFT JOIN bom_supplier_contacts bsc ON bsc.supplier_id = bs.id 
                    AND bsc.is_primary = 1 
                LEFT JOIN bom_material_pricings bmp ON bmp.supplier_id = bs.id 
                    AND bmp.material_id = $material_id 
                WHERE 1 
                GROUP BY bs.id 
            ) db 
            WHERE 1 $where 
        ");
        $data = $query->result();

        $result = [
            [ 'id' => '', 'text' => '- '.lang('stock_supplier').' -' ]
        ];
        foreach($data as $d){
            $result[] = [ 'id' => $d->id, 'text' => $d->company_name ];
        }
        return $result;
    }
    function get_material_pricing_dropdown($supplier_id = 0) {
        $where = "";
        if($supplier_id) {
            $where = " AND db.supplier_pricing_id != $supplier_id";
        }
        $query = $this->db->query("
            SELECT db.* 
            FROM (
                SELECT bm.*, 
                IFNULL(bmp.material_id, 0) material_pricing_id, 
                IFNULL(bmp.supplier_id, 0) supplier_pricing_id 
                FROM bom_materials bm 
                LEFT JOIN bom_material_pricings bmp ON bmp.material_id = bm.id 
                    AND bmp.supplier_id = $supplier_id 
                WHERE 1 
                GROUP BY bm.id 
            ) db 
            WHERE 1 $where 
        ");
        $data = $query->result();

        $result = [
            [ 'id' => '', 'text' => '- '.lang('stock_material').' -', 'unit' => '' ]
        ];
        foreach($data as $d){
            $result[] = [ 
                'id' => $d->id, 
                'text' => $d->name,
                'unit' => $d->unit
            ];
        }
        return $result;
    }

}