<?php

class Mr_items_model extends Crud_model {

    private $table = null;
    private $table2 = null;

    function __construct() {
        $this->table = 'mr_items';
        $this->table2 = 'bom_materials';
        parent::__construct($this->table);
        $this->load->model('Db_model');
    }

    function get_details($options = array()) {
        $mr_items_table = $this->db->dbprefix($this->table);
        $materials_table = $this->db->dbprefix($this->table2);
        //$clients_table = $this->db->dbprefix('clients');
        //$users_table = $this->db->dbprefix('users');

        $where = "";
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND $mr_items_table.id=$id";
        }

        $pid = get_array_value($options, "pid");
        if ($pid) {
            $where .= " AND $mr_items_table.project_id=$pid";
        }

        $po_no = get_array_value($options, "po_no");
        if (!empty($po_no)) {
            $where .= " AND $mr_items_table.po_no='".$po_no."' ";
        }

        $mr_id = get_array_value($options, "mr_id");
            if ($mr_id) {
                $where .= " AND $mr_items_table.mr_id=$mr_id";
            }


        $created_by = get_array_value($options, "created_by");
        if ($created_by) {
            $where .= " AND $mr_items_table.created_by=$created_by";
        }



        $processing = get_array_value($options, "processing");
        if ($processing && $created_by) {
            $where .= " AND $mr_items_table.mr_id=0";
        }

        $item_type = get_array_value($options, "item_type");
        $item_type = $item_type?$item_type:'itm';
        $and_sub = "";
        if($item_type!='all') {
            $and_sub = " AND $mr_items_table.item_type='$item_type' ";
        }

        $mrAllow = get_array_value($options,'mrAllow');
        if($mrAllow == 1){

            $sqlMr = "SELECT project_id FROM `materialrequests` WHERE id=".$id." ";
            $pid = 0;
            foreach($this->db->query($sqlMr)->result() as $k => $v){
                $pid = $v->project_id;
            }

            $sql = "
                SELECT pi.*,pim.ratio,bm.production_name as title,bm.name as code,bm.description, bm.unit as unit_type, 0 as sort,bm.id as material_id,pii.item_id as part_item_id,
                p.title as project_name, '' as supplier_name, 0 as rate, 0 as currency_symbol, 0 as total, ".$pid." as mr_id, 0 as item_type
                FROM bom_project_items pi
                LEFT JOIN bom_project_item_materials pim ON pim.project_item_id = pi.id
                LEFT JOIN bom_project_item_items pii ON pii.project_item_id = pi.id
                LEFT JOIN bom_materials bm ON bm.id = pim.material_id
                LEFT JOIN projects p ON p.id = pi.project_id
                
                WHERE pi.project_id = ".$pid."
            ";

            //arr($sql);exit;

            
            // exit;

        }else{
            $sql = "SELECT $mr_items_table.*
            FROM $mr_items_table
            LEFT JOIN $materials_table ON $materials_table.id=$mr_items_table.item_id AND $mr_items_table.item_type='itm'
            LEFT JOIN bom_materials as bm ON bm.id=$mr_items_table.material_id AND $mr_items_table.item_type='mtr'
            WHERE $mr_items_table.deleted=0 $and_sub $where
            ORDER BY $mr_items_table.supplier_name ASC,$mr_items_table.id ASC";
        }

        

        // $sql = "SELECT $mr_items_table.*, $materials_table.files
        // FROM $mr_items_table
        // LEFT JOIN $materials_table ON $materials_table.id=$mr_items_table.item_id AND $mr_items_table.item_type='itm'
        // LEFT JOIN bom_materials as bm ON bm.id=$mr_items_table.material_id AND $mr_items_table.item_type='mtr'
        // WHERE $mr_items_table.deleted=0 $and_sub $where
        // ORDER BY $mr_items_table.supplier_name ASC,$mr_items_table.id ASC";

        //arr($sql);exit;
        //arr($sql);

          
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

    function get_mr_item($mr_id, $material_id, $item_id) {
        $sql = "";
       if($material_id>0)
           $sql = "SELECT po_no from mr_items WHERE mr_id='$mr_id' AND material_id='{$material_id}';";
       else if($item_id>0)
           $sql = "SELECT po_no from mr_items WHERE mr_id='$mr_id' AND item_id='{$item_id}';";
       
       if(!$sql) return null;
       $q = $this->db->query($sql);
       return $q?$q->row():null;
   }

    public function get_materialrequest_item_by_id($mr_id = 0)
    {
        $this->db->select("*")->from("mr_items")->where("mr_id", $mr_id);
        $query = $this->db->get();

        $list_data = $query->result();
        foreach ($list_data as $data) {
            $stock_group = $this->Bom_stocks_model->dev2_getRestockGroupNameByStockId($data->stock_id);

            $data->stock_group_id = isset($stock_group->id) && $stock_group->id ? $stock_group->id : null;
            $data->stock_group_name = isset($stock_group->name) && $stock_group->name ? $stock_group->name : null;
        }
        return $list_data;
    }

    function dev2_clearProjectMaterialStockId($id)
    {
        $this->db->where('mr_id', $id);
        $this->db->update('mr_items', array('bpim_id' => null, 'stock_id' => null));
    }

}
