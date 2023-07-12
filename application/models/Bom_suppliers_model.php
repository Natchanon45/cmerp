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
        $permissions = json_decode(json_encode($this->login_user->permissions));
        // var_dump(arr($this->login_user->is_admin)); exit;

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

        $i = 0;
        foreach($data as $d){
            $material_name = $d->name;

            if ($this->login_user->is_admin && $d->production_name != "") {
                $material_name .= " - ".str_replace("\"", "`", $d->production_name);
            } elseif ($permissions->bom_material_read_production_name == "1" && $d->production_name != "") {
                $material_name .= " - ".str_replace("\"", "`", $d->production_name);
            }

            $result[] = [ 
                'id' => $d->id, 
                'text' => $material_name,
                'unit' => $d->unit
            ];
        }
        return $result;
    }

    function dev2_getSupplierDropdown()
    {
        $this->db->select('id, company_name');
        $this->db->from('bom_suppliers');

        $result = $this->db->get()->result();
        $data[] = array(
            "id" => "", "text" => "- " . lang("select_supplier") . " -"
        );

        foreach ($result as $item) {
            $data[] = array(
                "id" => $item->id, "text" => $item->company_name
            );
        }

        return $data;
    }

    function dev2_getSupplierDropdownWithCode()
    {
        $this->db->select('id, company_name, code_supplier');
        $this->db->from('bom_suppliers');

        $result = $this->db->get()->result();
        $data[] = array(
            "id" => "", "text" => "-- " . lang("select_supplier") . " --"
        );

        foreach ($result as $item) {
            if ($item->code_supplier) {
                $data[] = array(
                    "id" => $item->id, "text" => $item->code_supplier . ' - ' . $item->company_name
                );
            } else {
                $data[] = array(
                    "id" => $item->id, "text" => $item->company_name
                );
            }
        }

        return $data;
    }

    function dev2_getBomSupplierByMaterialId($material_id)
    {
        $result = array();
        $sql = "SELECT bs.id, bs.company_name, bmp.ratio, bmp.price FROM bom_suppliers bs RIGHT JOIN bom_material_pricings bmp ON 
        bs.id = bmp.supplier_id WHERE bmp.material_id = '" . $material_id . "' ORDER BY bs.id ASC";

        if (isset($material_id) && $material_id) {
            $query = $this->db->query($sql);
            $result = $query->row();
        }

        return $result;
    }

    function dev2_getSupplierNameById($supplier_id)
    {
        $supplier_name = "";
        $query = $this->db->get_where('bom_suppliers', array('id' => $supplier_id));

        if ($query) {
            $supplier_name = $query->row()->company_name;
        }
        return $supplier_name;
    }

    function getInfo($supplier_id)
    {
        $query = $this->db->get_where('bom_suppliers', array('id' => $supplier_id))->row();
        if (empty($query)) {
            return null;
        }

        return array(
            "company_name" => $query->company_name,
            "address" => $query->address,
            "city" => $query->city,
            "state" => $query->state,
            "zip" => $query->zip,
            "country" => $query->country,
            "website" => $query->website,
            "phone" => $query->phone,
            "vat_number" => $query->vat_number
        );
    }

    function getContactInfo($supplier_id)
    {
        $query = $this->db->get_where('bom_supplier_contacts', array('supplier_id' => $supplier_id, 'is_primary' => 1))->row();
        if (empty($query)) {
            return null;
        }

        return array(
            "id" => $query->id,
            "first_name" => $query->first_name,
            "last_name" => $query->last_name,
            "phone" => $query->phone,
            "email" => $query->email
        );
    }

}
