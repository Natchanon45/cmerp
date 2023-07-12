<?php

class PurchaseRequests_model extends Crud_model
{

    private $table = null;

    function __construct()
    {
        $this->table = 'purchaserequests';
        parent::__construct($this->table);

        $this->load->model("Db_model");
    }

    function get_details($options = array())
    {
        $purchaserequests_table = $this->db->dbprefix('purchaserequests');
        $cats_table = $this->db->dbprefix('pr_categories');
        $clients_table = $this->db->dbprefix('users');
        $taxes_table = $this->db->dbprefix('taxes');
        $pr_items_table = $this->db->dbprefix('pr_items');
        $pr_status_table = $this->db->dbprefix('pr_status');
        $users_table = $this->db->dbprefix('users');


        $view_row = intVal(get_array_value($options, "view_row"));
        $where = "";
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND $purchaserequests_table.id=$id";
        }
        $buyer_id = get_array_value($options, "buyer_id");
        if ($buyer_id) {
            $where .= " AND $purchaserequests_table.buyer_id=$buyer_id";
        }


        $pr_date = get_array_value($options, "pr_date");
        $deadline = get_array_value($options, "deadline");
        if ($pr_date && $deadline) {
            $where .= " AND ($purchaserequests_table.pr_date BETWEEN '$pr_date' AND '$deadline') ";
        }

        $after_tax_1 = "(IFNULL(tax_table.percentage,0)/100*IFNULL(items_table.pr_value,0))";
        $after_tax_2 = "(IFNULL(tax_table2.percentage,0)/100*IFNULL(items_table.pr_value,0))";

        $discountable_pr_value = "IF($purchaserequests_table.discount_type='after_tax', (IFNULL(items_table.pr_value,0) + $after_tax_1 + $after_tax_2), IFNULL(items_table.pr_value,0) )";

        $discount_amount = "IF($purchaserequests_table.discount_amount_type='percentage', IFNULL($purchaserequests_table.discount_amount,0)/100* $discountable_pr_value, $purchaserequests_table.discount_amount)";

        $before_tax_1 = "(IFNULL(tax_table.percentage,0)/100* (IFNULL(items_table.pr_value,0)- $discount_amount))";
        $before_tax_2 = "(IFNULL(tax_table2.percentage,0)/100* (IFNULL(items_table.pr_value,0)- $discount_amount))";

        $pr_value_calculation = "(
            IFNULL(items_table.pr_value,0)+
            IF($purchaserequests_table.discount_type='before_tax',  ($before_tax_1+ $before_tax_2), ($after_tax_1 + $after_tax_2))
            - $discount_amount
           )";

        $status_id = get_array_value($options, "status_id");
        if ($status_id) {
            $where .= " AND $purchaserequests_table.status_id='$status_id'";
        }

        if ($view_row == 1) {
            $where .= " AND $purchaserequests_table.created_by='{$this->login_user->id}'";
        }

        //prepare custom fild binding query
        $custom_fields = get_array_value($options, "custom_fields");
        $custom_field_query_info = $this->prepare_custom_field_query_string("purchaserequests", $custom_fields, $purchaserequests_table);
        $select_custom_fieds = get_array_value($custom_field_query_info, "select_string");
        $join_custom_fieds = get_array_value($custom_field_query_info, "join_string");

        $innerjoin = "";
        $supplier_id = get_array_value($options, "supplier_id");
        if ($supplier_id) {
            $innerjoin .= " INNER JOIN (SELECT pr_id FROM $pr_items_table WHERE $pr_items_table.supplier_id='$supplier_id' GROUP BY pr_id) as items_table2 ON items_table2.pr_id=$purchaserequests_table.id";
        }

        $sql = "
			SELECT
				$purchaserequests_table.doc_no,
				$purchaserequests_table.id,
                $purchaserequests_table.payment, 
                $purchaserequests_table.project_name, 
				$purchaserequests_table.user_id, 
				$purchaserequests_table.buyer_id, 
				$purchaserequests_table.pr_date, 
				$purchaserequests_table.note, 
				$cats_table.title as category_name,
                IF( pt.id IS NOT NULL, 3, $purchaserequests_table.status_id ) as  status_id, 
			
				$purchaserequests_table.tax_id, 
				$purchaserequests_table.tax_id2, 
				$purchaserequests_table.discount_amount, 
				$purchaserequests_table.discount_amount_type, $purchaserequests_table.discount_type, $purchaserequests_table.created_by, $purchaserequests_table.deleted, $purchaserequests_table.order_id,

				'THB' as currency, '฿' as currency_symbol, 
				concat( buyer.first_name,' ',buyer.last_name ) as buyer_name,
				$pr_value_calculation AS pr_value, 
				tax_table.percentage AS tax_percentage, 
				tax_table2.percentage AS tax_percentage2, 
				
				IF( pt.id IS NOT NULL, 'Approved', 'New' ) AS pr_status_title, 
               
                IF( pt.id IS NOT NULL, '#83c340', $pr_status_table.color ) AS pr_status_color, 
               
                
                CONCAT($users_table.first_name, ' ', $users_table.last_name) AS created_by_user, $users_table.user_type AS created_by_user_type $select_custom_fieds
                FROM $purchaserequests_table
                $innerjoin
                LEFT JOIN $cats_table ON $purchaserequests_table.catid=$cats_table.id
                LEFT JOIN prove_table pt ON $purchaserequests_table.id = pt.doc_id AND pt.tbName = 'purchaserequests'
                LEFT JOIN $clients_table as buyer ON buyer.id = $purchaserequests_table.buyer_id
                LEFT JOIN $pr_status_table ON $purchaserequests_table.status_id = $pr_status_table.id 
                LEFT JOIN ( SELECT $taxes_table.* FROM $taxes_table) AS tax_table ON tax_table.id = $purchaserequests_table.tax_id
                LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table2 ON tax_table2.id = $purchaserequests_table.tax_id2 
                LEFT JOIN (
                SELECT 
                    pr_id, 
                    SUM(total) AS pr_value 
                    FROM $pr_items_table 
                    WHERE deleted=0 GROUP BY pr_id
                ) AS items_table ON items_table.pr_id = $purchaserequests_table.id
            LEFT JOIN $users_table ON $users_table.id=$purchaserequests_table.created_by
            $join_custom_fieds
            WHERE $purchaserequests_table.deleted=0 $where";
        return $this->db->query($sql);
    }

    function getNewItems()
    {
        $sql = 'SELECT * FROM purchaserequests WHERE status_id=1;';
        return $this->db->query($sql);
    }

    function get_categories_details($options = array())
    {
        $categories_table = $this->db->dbprefix('pr_categories');
        $users_table = $this->db->dbprefix('users');

        $where = "";
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND cat.id=$id";
        }
        $created_by = get_array_value($options, "created_by");
        if ($created_by) {
            $where .= " AND cat.created_by=$created_by";
        }

        $sql = "SELECT cat.*, concat(creator.first_name, ' ', creator.last_name) as creator_name FROM $categories_table as cat
            LEFT JOIN $users_table as creator ON creator.id = cat.created_by
            WHERE 1 $where";
        return $this->db->query($sql);
    }

    function get_processing_pr_total_summary($user_id)
    {
        $pr_items_table = $this->db->dbprefix('pr_items');
        $purchaserequests_table = $this->db->dbprefix('purchaserequests');
        $clients_table = $this->db->dbprefix('users');
        $users_table = $this->db->dbprefix('users');
        $taxes_table = $this->db->dbprefix('taxes');
        $where = " AND $pr_items_table.created_by=$user_id";

        $pr_tax_id = get_setting('pr_tax_id') ? get_setting('pr_tax_id') : 0;
        $pr_tax_id2 = get_setting('pr_tax_id2') ? get_setting('pr_tax_id2') : 0;

        $item_sql = "SELECT SUM($pr_items_table.total) AS pr_subtotal, tax_table.percentage AS tax_percentage, tax_table.title AS tax_name,
            tax_table2.percentage AS tax_percentage2, tax_table2.title AS tax_name2
        FROM $pr_items_table
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table ON tax_table.id = $pr_tax_id
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table2 ON tax_table2.id = $pr_tax_id2
        WHERE $pr_items_table.deleted=0 AND $pr_items_table.pr_id=0 $where";
        $item = $this->db->query($item_sql)->row();

        $select_pr_buyer_id = $user_id;
        $result = new stdClass();
        $result->pr_subtotal = $item->pr_subtotal;
        $result->tax_percentage = $item->tax_percentage;
        $result->tax_percentage2 = $item->tax_percentage2;
        $result->tax_name = $item->tax_name;
        $result->tax_name2 = $item->tax_name2;
        $result->tax = 0;
        $result->tax2 = 0;

        $pr_subtotal = $result->pr_subtotal;
        if ($item->tax_percentage) {
            $result->tax = $pr_subtotal * ($item->tax_percentage / 100);
        }
        if ($item->tax_percentage2) {
            $result->tax2 = $pr_subtotal * ($item->tax_percentage2 / 100);
        }

        $result->pr_total = $item->pr_subtotal + $result->tax + $result->tax2;
        $result->currency_symbol = get_setting("currency_symbol");
        $result->currency = get_setting("default_currency");
        return $result;
    }

    function get_pr_total_summary($pr_id = 0, $supplier = 'all')
    {
        $pr_items_table = $this->db->dbprefix('pr_items');
        $purchaserequests_table = $this->db->dbprefix('purchaserequests');
        $clients_table = $this->db->dbprefix('clients');
        $taxes_table = $this->db->dbprefix('taxes');

        $and_where = "";
        if ($supplier != 'all')
            $and_where = " AND $pr_items_table.supplier_name='{$supplier}'";
        $item_sql = "SELECT SUM($pr_items_table.total) AS pr_subtotal,currency,currency_symbol
        FROM $pr_items_table
        LEFT JOIN $purchaserequests_table ON $purchaserequests_table.id= $pr_items_table.pr_id    
        WHERE $pr_items_table.deleted=0 $and_where AND $pr_items_table.pr_id=$pr_id AND $purchaserequests_table.deleted=0";
        $item = $this->db->query($item_sql)->row();

        $pr_sql = "SELECT $purchaserequests_table.*, tax_table.percentage AS tax_percentage, tax_table.title AS tax_name,
            tax_table2.percentage AS tax_percentage2, tax_table2.title AS tax_name2
        FROM $purchaserequests_table
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table ON tax_table.id = $purchaserequests_table.tax_id
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table2 ON tax_table2.id = $purchaserequests_table.tax_id2
        WHERE $purchaserequests_table.deleted=0 AND $purchaserequests_table.id=$pr_id";
        $order = $this->db->query($pr_sql)->row();

        $result = new stdClass();
        $result->pr_subtotal = $item->pr_subtotal;
        $result->tax_percentage = isset($order->tax_percentage) ? $order->tax_percentage : 0;
        $result->tax_percentage2 = isset($order->tax_percentage2) ? $order->tax_percentage2 : 0;
        $result->tax_name = isset($order->tax_name) ? $order->tax_name : "-";
        $result->tax_name2 = isset($order->tax_name2) ? $order->tax_name2 : "-";
        $result->tax = 0;
        $result->tax2 = 0;

        $pr_subtotal = $result->pr_subtotal;
        $pr_subtotal_for_taxes = $pr_subtotal;

        if ($order->discount_type == "before_tax") {
            $pr_subtotal_for_taxes = $pr_subtotal - ($order->discount_amount_type == "percentage" ? ($pr_subtotal * ($order->discount_amount / 100)) : $order->discount_amount);
        }
        if ($order->tax_percentage) {
            $result->tax = $pr_subtotal_for_taxes * ($order->tax_percentage / 100);
        }
        if ($order->tax_percentage2) {
            $result->tax2 = $pr_subtotal_for_taxes * ($order->tax_percentage2 / 100);
        }

        $pr_total = $item->pr_subtotal + $result->tax + $result->tax2;

        //get discount total
        $result->discount_total = 0;
        if ($order->discount_type == "after_tax") {
            $pr_subtotal = $pr_total;
        }
        $result->discount_total = $order->discount_amount_type == "percentage" ? ($pr_subtotal * ($order->discount_amount / 100)) : $order->discount_amount;
        $result->discount_type = $order->discount_type;
        $result->pr_total = $pr_total - number_format($result->discount_total, 2, ".", "");
        $result->currency_symbol = $item->currency_symbol;
        $result->currency = $item->currency;
        return $result;
    }

    //get order last id
    function get_pr_last_id()
    {
        $purchaserequests_table = $this->db->dbprefix('purchaserequests');
        $sql = "SELECT MAX($purchaserequests_table.id) AS last_id FROM $purchaserequests_table";
        return $this->db->query($sql)->row()->last_id;
    }

    //save initial number of order
    function save_initial_number_of_order($value)
    {
        $purchaserequests_table = $this->db->dbprefix('purchaserequests');
        $sql = "ALTER TABLE $purchaserequests_table AUTO_INCREMENT=$value;";
        return $this->db->query($sql);
    }

    function get_PO_details($options = array())
    {
        $purchaserequests_table = $this->db->dbprefix('purchaserequests');
        $cats_table = $this->db->dbprefix('pr_categories');
        $clients_table = $this->db->dbprefix('users');
        $taxes_table = $this->db->dbprefix('taxes');
        $pr_items_table = $this->db->dbprefix('pr_items');
        $pr_status_table = $this->db->dbprefix('pr_status');
        $users_table = $this->db->dbprefix('users');

        // var_dump($options);

        $view_row = intVal(get_array_value($options, "view_row"));
        $where = "";
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND pr.po_no = '" . get_array_value($options, "id") . "' ";
        }
        $buyer_id = get_array_value($options, "buyer_id");
        if ($buyer_id) {
            $where .= " AND $purchaserequests_table.buyer_id=$buyer_id";
        }


        $pr_date = get_array_value($options, "pr_date");
        $deadline = get_array_value($options, "deadline");
        if ($pr_date && $deadline) {
            $where .= " AND ($purchaserequests_table.pr_date BETWEEN '$pr_date' AND '$deadline') ";
        }

        $after_tax_1 = "(IFNULL(tax_table.percentage,0)/100*IFNULL(items_table.pr_value,0))";
        $after_tax_2 = "(IFNULL(tax_table2.percentage,0)/100*IFNULL(items_table.pr_value,0))";

        $discountable_pr_value = "IF($purchaserequests_table.discount_type='after_tax', (IFNULL(items_table.pr_value,0) + $after_tax_1 + $after_tax_2), IFNULL(items_table.pr_value,0) )";

        $discount_amount = "IF($purchaserequests_table.discount_amount_type='percentage', IFNULL($purchaserequests_table.discount_amount,0)/100* $discountable_pr_value, $purchaserequests_table.discount_amount)";

        $before_tax_1 = "(IFNULL(tax_table.percentage,0)/100* (IFNULL(items_table.pr_value,0)- $discount_amount))";
        $before_tax_2 = "(IFNULL(tax_table2.percentage,0)/100* (IFNULL(items_table.pr_value,0)- $discount_amount))";

        $pr_value_calculation = "(
            IFNULL(items_table.pr_value,0)+
            IF($purchaserequests_table.discount_type='before_tax',  ($before_tax_1+ $before_tax_2), ($after_tax_1 + $after_tax_2))
            - $discount_amount
           )";

        $status_id = get_array_value($options, "status_id");
        if ($status_id) {
            $where .= " AND $purchaserequests_table.status_id='$status_id'";
        }

        if ($view_row == 1) {
            $where .= " AND $purchaserequests_table.created_by='{$this->login_user->id}'";
        }

        //prepare custom fild binding query
        $custom_fields = get_array_value($options, "custom_fields");
        $custom_field_query_info = $this->prepare_custom_field_query_string("purchaserequests", $custom_fields, $purchaserequests_table);
        $select_custom_fieds = get_array_value($custom_field_query_info, "select_string");
        $join_custom_fieds = get_array_value($custom_field_query_info, "join_string");

        $innerjoin = "";
        $supplier_id = get_array_value($options, "supplier_id");
        if ($supplier_id) {
            $innerjoin .= " INNER JOIN (SELECT pr_id FROM $pr_items_table WHERE $pr_items_table.supplier_id='$supplier_id' GROUP BY pr_id) as items_table2 ON items_table2.pr_id=$purchaserequests_table.id";
        }

        $sql = "
			SELECT
                $purchaserequests_table.doc_no as prID,
				pr.po_no as doc_no,
				pr.pr_id as id,
                $purchaserequests_table.payment, 
                $purchaserequests_table.project_name, 
				$purchaserequests_table.user_id, 
				$purchaserequests_table.buyer_id, 
				$purchaserequests_table.pr_date, 
				$purchaserequests_table.note, 
				$cats_table.title as category_name,
                # IF( pt.id IS NOT NULL, 3, $purchaserequests_table.status_id ) as  status_id, 
				$purchaserequests_table.status_id,
				$purchaserequests_table.tax_id, 
				$purchaserequests_table.tax_id2, 
				$purchaserequests_table.discount_amount, 
				$purchaserequests_table.discount_amount_type, $purchaserequests_table.discount_type, $purchaserequests_table.created_by, 
				$purchaserequests_table.deleted, 
				$purchaserequests_table.order_id,

				'THB' as currency, '฿' as currency_symbol, 
				concat( buyer.first_name,' ',buyer.last_name ) as buyer_name,
				(
					IFNULL( SUM( pr.total ), 0 )+
					IF( $purchaserequests_table.discount_type='before_tax',  ( $before_tax_1 + $before_tax_2 ), ($after_tax_1 + $after_tax_2 ) )
					- $discount_amount
				) AS pr_value, 
				tax_table.percentage AS tax_percentage, 
				tax_table2.percentage AS tax_percentage2, 
				
				# IF( pt.id IS NOT NULL, 'Approved', $pr_status_table.title ) AS pr_status_title, 
                $pr_status_table.title as pr_status_title,
                # IF( pt.id IS NOT NULL, '#83c340', $pr_status_table.color ) AS pr_status_color, 
                $pr_status_table.color as pr_status_color,
                
                CONCAT($users_table.first_name, ' ', $users_table.last_name) AS created_by_user, $users_table.user_type AS created_by_user_type $select_custom_fieds
			FROM pr_items pr
			LEFT JOIN $purchaserequests_table ON pr.pr_id = $purchaserequests_table.id
            
			$innerjoin
			LEFT JOIN $cats_table ON $purchaserequests_table.catid=$cats_table.id
			INNER JOIN prove_table pt ON $purchaserequests_table.id = pt.doc_id AND pt.tbName = 'purchaserequests'
			LEFT JOIN $clients_table as buyer ON buyer.id = $purchaserequests_table.buyer_id
			LEFT JOIN $pr_status_table ON $purchaserequests_table.status_id = $pr_status_table.id 
			LEFT JOIN ( SELECT $taxes_table.* FROM $taxes_table) AS tax_table ON tax_table.id = $purchaserequests_table.tax_id
			LEFT JOIN (
				SELECT 
					$taxes_table.* 
				FROM $taxes_table
			) AS tax_table2 ON tax_table2.id = $purchaserequests_table.tax_id2 
			LEFT JOIN (
				SELECT 
					pr_id, 
					SUM(total) AS pr_value 
				FROM $pr_items_table 
				WHERE deleted=0 GROUP BY pr_id
			) AS items_table ON items_table.pr_id = $purchaserequests_table.id
            LEFT JOIN $users_table ON $users_table.id=$purchaserequests_table.created_by
            $join_custom_fieds
            WHERE $purchaserequests_table.deleted=0 $where
            GROUP BY pr.po_no";
        // arr($sql);exit;
        return $this->db->query($sql);
    }

    function get_po_total_summary($pr_id = 0, $po_no = 0, $supplier = 'all')
    {
        $pr_items_table = $this->db->dbprefix('pr_items');
        $purchaserequests_table = $this->db->dbprefix('purchaserequests');
        $clients_table = $this->db->dbprefix('clients');
        $taxes_table = $this->db->dbprefix('taxes');

        $and_where = "";
        $where_po_no = "";
        if ($po_no) {
            $where_po_no = "AND pr_items.po_no = '" . $po_no . "'";
        }
        if ($supplier != 'all')
            $and_where = " AND $pr_items_table.supplier_name='{$supplier}'";
        $item_sql = "SELECT SUM($pr_items_table.total) AS pr_subtotal,currency,currency_symbol
        FROM $pr_items_table
        LEFT JOIN $purchaserequests_table ON $purchaserequests_table.id= $pr_items_table.pr_id    
        WHERE $pr_items_table.deleted=0 $and_where AND $pr_items_table.pr_id=$pr_id AND $purchaserequests_table.deleted=0 $where_po_no";
        $item = $this->db->query($item_sql)->row();
        // arr($item_sql);exit;

        $pr_sql = "SELECT $purchaserequests_table.*, tax_table.percentage AS tax_percentage, tax_table.title AS tax_name,
            tax_table2.percentage AS tax_percentage2, tax_table2.title AS tax_name2
        FROM $purchaserequests_table
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table ON tax_table.id = $purchaserequests_table.tax_id
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table2 ON tax_table2.id = $purchaserequests_table.tax_id2
        WHERE $purchaserequests_table.deleted=0 AND $purchaserequests_table.id=$pr_id";
        $order = $this->db->query($pr_sql)->row();

        $result = new stdClass();
        $result->pr_subtotal = $item->pr_subtotal;
        $result->tax_percentage = $order->tax_percentage ? $order->tax_percentage : 0;
        $result->tax_percentage2 = $order->tax_percentage2 ? $order->tax_percentage2 : 0;
        $result->tax_name = $order->tax_name ? $order->tax_name : "-";
        $result->tax_name2 = $order->tax_name2 ? $order->tax_name2 : "-";
        $result->tax = 0;
        $result->tax2 = 0;

        $pr_subtotal = $result->pr_subtotal;
        $pr_subtotal_for_taxes = $pr_subtotal;
        if ($order->discount_type == "before_tax") {
            $pr_subtotal_for_taxes = $pr_subtotal - ($order->discount_amount_type == "percentage" ? ($pr_subtotal * ($order->discount_amount / 100)) : $order->discount_amount);
        }
        if ($order->tax_percentage) {
            $result->tax = $pr_subtotal_for_taxes * ($order->tax_percentage / 100);
        }
        if ($order->tax_percentage2) {
            $result->tax2 = $pr_subtotal_for_taxes * ($order->tax_percentage2 / 100);
        }
        $pr_total = $item->pr_subtotal + $result->tax + $result->tax2;

        //get discount total
        $result->discount_total = 0;
        if ($order->discount_type == "after_tax") {
            $pr_subtotal = $pr_total;
        }
        $result->discount_total = $order->discount_amount_type == "percentage" ? ($pr_subtotal * ($order->discount_amount / 100)) : $order->discount_amount;
        $result->discount_type = $order->discount_type;
        $result->pr_total = $pr_total - number_format($result->discount_total, 2, ".", "");
        $result->currency_symbol = $item->currency_symbol;
        $result->currency = $item->currency;
        return $result;
    }

    function get_purchaserequests_dropdown_list()
    {
        $orders_table = $this->db->dbprefix('purchaserequests');
        if (true) {
            $sql = "SELECT $orders_table.id FROM $orders_table
            WHERE $orders_table.deleted=0 
            ORDER BY $orders_table.id DESC";
        }
        return $this->db->query($sql);
    }

    function save_initial_number_of_po($value)
    {
        $po_table = $this->db->dbprefix('purchaserequests');
        $sql = "ALTER TABLE $po_table AUTO_INCREMENT=$value;";

        return $this->db->query($sql);
    }

    function dev2_prGetHeaderAll()
    {
        $query = $this->db->get('pr_header');
        return $query->result();
    }

    function dev2_prGetHeaderById($id)
    {
        $query = $this->db->get_where('pr_header', array('id' => $id));
        return $query->row();
    }

    function dev2_prPostHeader($data)
    {
        $param = array(
            'prefix' => 'PR',
            'LPAD' => 5,
            'column' => 'pr_no',
            'table' => 'pr_header'
        );
        $data['pr_no'] = $this->Db_model->genDocNo($param);
        
        $this->db->insert('pr_header', $data);
        $insert_id = $this->db->insert_id();

        return $insert_id;
    }

    function dev2_prPutHeader($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update('pr_header', $data);
    }

    function dev2_prDeleteHeaderById($id)
    {
        $this->db->where('id', $id);
        $this->db->update('pr_header', array(
            'deleted_flag' => 1,
            'deleted_by' => $this->login_user->id,
            'deleted_date' => date("Y-m-d h:i:s")
        ));
    }

    function dev2_prApproveHeaderById($id)
    {
        $this->db->where('id', $id);
        $this->db->update('pr_header', array(
            'status' => 2,
            'approved_by' => $this->login_user->id,
            'approved_date' => date("Y-m-d h:i:s")
        ));
    }

    function dev2_prRejectHeaderById($id)
    {
        $this->db->where('id', $id);
        $this->db->update('pr_header', array(
            'status' => 3,
            'rejected_by' => $this->login_user->id,
            'rejected_date' => date("Y-m-d h:i:s")
        ));
    }

    function dev2_prGetDetailAllByHeaderId($pr_id)
    {
        $query = $this->db->get_where('pr_detail', array('pr_id' => $pr_id));
        return $query->result();
    }

    function dev2_prPostDetail($data)
    {
        $this->db->insert('pr_detail', $data);
        $insert_id = $this->db->insert_id();
        
        return $insert_id;
    }

    function dev2_prPutDetail($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update('pr_detail', $data);
    }

    function dev2_prDeleteDetailById($id)
    {
        $this->db->where('id', $id);
        $this->db->update('pr_detail', array(
            'deleted_flag' => 1,
            'deleted_by' => $this->login_user->id,
            'deleted_date' => date("Y-m-d h:i:s")
        ));
    }

    function dev2_prCountDetailByHeaderId($pr_id)
    {
        $query = $this->db->get_where('pr_detail', array('pr_id' => $pr_id));
        return $query->num_rows();
    }

}
