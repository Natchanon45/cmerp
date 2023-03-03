<?php

class Pr_items_model extends Crud_model {

    private $table = null;

    function __construct() {
        $this->table = 'pr_items';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $pr_items_table = $this->db->dbprefix('pr_items');
        $items_table = $this->db->dbprefix('items');
        $clients_table = $this->db->dbprefix('clients');
        $users_table = $this->db->dbprefix('users');

        $where = "";
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND $pr_items_table.id=$id";
        }

        $po_no = get_array_value($options, "po_no");
        if (!empty($po_no)) {
            $where .= " AND $pr_items_table.po_no='".$po_no."' ";
        }

        $pr_id = get_array_value($options, "pr_id");
            if ($pr_id) {
                $where .= " AND $pr_items_table.pr_id=$pr_id";
            }


        $created_by = get_array_value($options, "created_by");
        if ($created_by) {
            $where .= " AND $pr_items_table.created_by=$created_by";
        }

        

        $processing = get_array_value($options, "processing");
        if ($processing && $created_by) {
            $where .= " AND $pr_items_table.pr_id=0";
        }

        $item_type = get_array_value($options, "item_type");
        $item_type = $item_type?$item_type:'itm';
        $and_sub = "";
        if($item_type!='all') {
            $and_sub = " AND $pr_items_table.item_type='$item_type' ";
        }

        $sql = "SELECT $pr_items_table.*, $items_table.files
        FROM $pr_items_table
        LEFT JOIN $items_table ON $items_table.id=$pr_items_table.item_id AND $pr_items_table.item_type='itm'
        LEFT JOIN bom_materials as bm ON bm.id=$pr_items_table.material_id AND $pr_items_table.item_type='mtr'
        WHERE $pr_items_table.deleted=0 $and_sub $where
        ORDER BY $pr_items_table.supplier_name ASC,$pr_items_table.id ASC";

        //arr($sql);exit;
        return $this->db->query($sql);
    }

    function get_one_material_in_cart($material_id, $created_by) {
        //$sql = "SELECT * from pr_items WHERE item_type='mtr' AND material_id='".$material_id."' AND supplier_id='".$supplier_id."' AND pr_id='0' AND created_by='".$created_by."'";
        $sql = "SELECT * from pr_items WHERE item_type='mtr' AND material_id='".$material_id."' AND pr_id='0' AND created_by='".$created_by."'";
        return $this->db->query($sql)->row_array();
    }

    function get_one_material($material_id) {
        $sql = "SELECT * from bom_materials WHERE id='".$material_id."'";
        return $this->db->query($sql)->row();
    }
    function update_material($material_id) {
        $sql = "UPDATE pr_items SET deleted = 1 WHERE id='".$material_id."'";
        return $this->db->query($sql);
        //arr($sql);exit;
    }
    function get_one_item_in_cart($item_id, $created_by) {
        //$sql = "SELECT * from pr_items WHERE item_type='mtr' AND material_id='".$material_id."' AND supplier_id='".$supplier_id."' AND pr_id='0' AND created_by='".$created_by."'";
        $sql = "SELECT * from pr_items WHERE item_type='itm' AND item_id='".$item_id."' AND pr_id='0' AND created_by='".$created_by."'";
        return $this->db->query($sql)->row_array();
    }

    function get_one_item($item_id) {
        $sql = "SELECT * from items WHERE id='".$item_id."'";
        return $this->db->query($sql)->row();
    }


    function get_supplier_suggestion($material_id, $keyword = "") {
        $keyword = $this->db->escape_str($keyword);

        $and_where = '';
        if($keyword)
            $and_where = " AND bs.company_name LIKE '%{$keyword}%'";
        $if = "";
        $left_join = "";
        $and_join = "";
        if($material_id) {
            $if = ",IF(bmp.price IS NULL,0,bmp.price/bmp.ratio) as price";
            $left_join = "LEFT JOIN bom_material_pricings as bmp ON bmp.supplier_id=bs.id ";
            $and_join .= " AND bmp.material_id='{$material_id}' ";
        }
        $sql = "SELECT bs.*,bs.company_name as supplier_name,bs.currency,bs.currency_symbol
            {$if}
            FROM bom_suppliers as bs
            {$left_join}
            {$and_join}
            WHERE 1 {$and_where}
        ";
        //echo $sql;
        return $this->db->query($sql)->result();
    }

    function get_supplier_info_suggestion($supplier_id, $material_id) {
        //$suppliers_table = $this->db->dbprefix('bom_suppliers');

        //$supplier_name = $this->db->escape_str($supplier_name);

        $and_where = '';
        // if($supplier_name)
        //     $and_where = " AND bs.company_name LIKE '%{$supplier_name}%'";
        if($supplier_id)
            $and_where = " AND bs.id = '{$supplier_id}'";
        $if = "";
        $left_join = "";
        $and_join = "";
        if($material_id) {
            $if = ",IF(bmp.price IS NULL,0,bmp.price) as price,IF(bmp.ratio IS NULL,0,bmp.ratio) as ratio";
            $left_join = "LEFT JOIN bom_material_pricings as bmp ON bmp.supplier_id=bs.id ";
            $and_join .= " AND bmp.material_id='{$material_id}' ";
        }
        $sql = "SELECT bs.*,bs.company_name as supplier_name,bs.currency,bs.currency_symbol
            {$if}
            FROM bom_suppliers as bs
            {$left_join}
            {$and_join}
            WHERE 1 {$and_where}
            ORDER BY id DESC LIMIT 1
        ";

        $result = $this->db->query($sql);

        if ($result->num_rows()) {
            return $result->row();
        }
    }

    public function getPo_Item_suggestion( $keyword = "", $id = null ) {
		
      //  $keyword = $this->db->escape_str($keyword);
        
    
		$sql = "
			SELECT 
				concat( pr.pr_id ,'-', pr.supplier_id ) as pr_supplier_id ,
                pr.po_no,
				concat( pr.po_no ,' ', pr.supplier_name ) as tname ,
				sum(pr.quantity) as needQuality,
				IFNULL(
					( 
						SELECT 
							sum( ot.quantity ) 
						FROM receipt_items ot
						INNER JOIN receipts o ON ot.receipt_id = o.id
						[WHERE]
					)
					,0
				) as recetpQty 
			FROM pr_items pr 
			INNER JOIN prove_table ptt ON ptt.tbName = 'purchaserequests' AND pr.pr_id = ptt.doc_id
			GROUP BY 
				pr_supplier_id
			HAVING needQuality > recetpQty 
		
		";
			
		
		$filters = array();
		$filters['WHERE'][] = "
			ot.po_id = concat( pr.pr_id ,'-', pr.supplier_id ) 
			AND ot.deleted = 0
			AND o.deleted = 0
		";
		
        if( $id != '' ){
			$filters['WHERE'][] = "ot.order_id != ". $id ."";
		}
		
		$sql = gencond_( $sql, $filters );
		
        // HAVING needQuality > recetpQty 
        return $this->db->query($sql)->result();
        

    }
}
