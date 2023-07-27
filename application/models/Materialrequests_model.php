<?php

class MaterialRequests_model extends Crud_model
{

	private $table = null;

	function __construct()
	{
		$this->table = 'materialrequests';
		parent::__construct($this->table);

		$this->load->model('Projects_model');
	}

	function get_details($options = array())
	{
		$mr_table = $this->db->dbprefix($this->table);
		//$cats_table = $this->db->dbprefix('pr_categories');
		$requester_table = $this->db->dbprefix('users');
		$taxes_table = $this->db->dbprefix('taxes');
		$mr_items_table = $this->db->dbprefix('mr_items');
		$mr_status_table = $this->db->dbprefix('mr_status');
		$users_table = $this->db->dbprefix('users');

		$view_row = intVal(get_array_value($options, "view_row"));
		$where = "";

		$id = get_array_value($options, "id");
		if (isset($options['id'])) {
			$where .= " AND $mr_table.id=$id";
		}

		$project_id = get_array_value($options, "project_id");
		if (isset($options['project_id'])) {
			$where .= " AND $mr_table.project_id='$project_id'";
		}

		/* $pr_date = get_array_value($options, "pr_date");
		$deadline = get_array_value($options, "deadline");
		if ($pr_date && $deadline) {
		$where .= " AND ($purchaserequests_table.pr_date BETWEEN '$pr_date' AND '$deadline') ";
		} */
		// $after_tax_1 = "(IFNULL(tax_table.percentage,0)/100*IFNULL(items_table.pr_value,0))";
		// $after_tax_2 = "(IFNULL(tax_table2.percentage,0)/100*IFNULL(items_table.pr_value,0))";
		//$discountable_pr_value = "IF($mr_table.discount_type='after_tax', (IFNULL(items_table.pr_value,0) + $after_tax_1 + $after_tax_2), IFNULL(items_table.pr_value,0) )";
		//$discount_amount = "IF($mr_table.discount_amount_type='percentage', IFNULL($mr_table.discount_amount,0)/100* $discountable_pr_value, $mr_table.discount_amount)";
		// $before_tax_1 = "(IFNULL(tax_table.percentage,0)/100* (IFNULL(items_table.pr_value,0)- $discount_amount))";
		// $before_tax_2 = "(IFNULL(tax_table2.percentage,0)/100* (IFNULL(items_table.pr_value,0)- $discount_amount))";
		// $pr_value_calculation = "(
		//     IFNULL(items_table.pr_value,0)+
		//     IF($mr_table.discount_type='before_tax',  ($before_tax_1+ $before_tax_2), ($after_tax_1 + $after_tax_2))
		//     - $discount_amount
		//    )";

		$status_id = get_array_value($options, "status_id");
		if (isset($options['status_id'])) {
			$where .= " AND $mr_table.status_id='$status_id'";
		}

		if ($view_row == 1) {
			$where .= " AND $mr_table.created_by='{$this->login_user->id}'";
		}

		// prepare custom fild binding query
		$custom_fields = get_array_value($options, "custom_fields");
		$custom_field_query_info = $this->prepare_custom_field_query_string("materialrequests", $custom_fields, $mr_table);
		$select_custom_fieds = get_array_value($custom_field_query_info, "select_string");
		$join_custom_fieds = get_array_value($custom_field_query_info, "join_string");

		$innerjoin = "";
		$supplier_id = get_array_value($options, "supplier_id");
		if ($supplier_id) {
			$innerjoin .= " INNER JOIN (SELECT mr_id FROM $mr_items_table WHERE $mr_items_table.supplier_id='$supplier_id' GROUP BY mr_id) as items_table2 ON items_table2.mr_id=$mr_table.id";
		}

		/*IF( pt.id IS NOT NULL, 3, $mr_table.status_id ) as  status_id, 
		concat( requester.first_name,' ',requester.last_name ) as requester_name,
		concat( requester.first_name,' ',requester.last_name ) as buyer_name,
		IF( pt.id IS NOT NULL, 'Approved', 'New' ) AS pr_status_title, 
		IF( pt.id IS NOT NULL, '#83c340', $mr_status_table.color ) AS pr_status_color, */

		$sql = "SELECT $mr_table.status_id, $mr_table.id, $mr_table.doc_no, $mr_table.requester_id, $mr_table.mr_date, $mr_table.note, 
			$mr_table.payment, '' AS category_name, $mr_table.project_name, items_table.mr_value, 'บาท' AS currency_symbol, 
			CONCAT( requester.first_name,' ',requester.last_name ) AS requester_name, 
			CONCAT( requester.first_name,' ',requester.last_name ) AS buyer_name, 
			CONCAT($users_table.first_name, ' ', $users_table.last_name) AS created_by_user, $users_table.user_type AS created_by_user_type $select_custom_fieds 
			FROM $mr_table $innerjoin 
			LEFT JOIN $requester_table as requester ON requester.id = $mr_table.requester_id 
			LEFT JOIN $mr_status_table ON $mr_table.status_id = $mr_status_table.id 
			LEFT JOIN (SELECT mr_id, COUNT(id) AS mr_value FROM $mr_items_table WHERE deleted=0 GROUP BY mr_id) AS items_table ON items_table.mr_id = $mr_table.id 
			LEFT JOIN $users_table ON $users_table.id=$mr_table.created_by $join_custom_fieds WHERE $mr_table.deleted = 0 $where";

		// var_dump(arr($sql)); exit;
		return $this->db->query($sql);
	}

	function getNewItems()
	{
		$sql = "SELECT * FROM {$this->table} WHERE status_id = '1' ";
		return $this->db->query($sql);
	}

	// Get order last id
	function get_pr_last_id()
	{
		$mr_table = $this->db->dbprefix($this->table);

		$sql = "SELECT MAX($mr_table.id) AS last_id FROM $mr_table";

		return $this->db->query($sql)->row()->last_id;
	}

	// Save initial number of order
	function save_initial_number_of_order($value)
	{
		$mr_table = $this->db->dbprefix($this->table);

		$sql = "ALTER TABLE $mr_table AUTO_INCREMENT = $value;";

		return $this->db->query($sql);
	}

	function get_mr_dropdown_list()
	{
		$mr_table = $this->db->dbprefix($this->table);

		// if (true) {
		// 	$sql = "SELECT $mr_table.id FROM $mr_table WHERE $mr_table.deleted = 0 ORDER BY $mr_table.id DESC";
		// } else {
		// 	$sql = "SELECT $mr_table.id FROM $mr_table WHERE $mr_table.deleted = 0 ORDER BY $mr_table.id DESC ";
		// }
		// $sql = "SELECT $invoices_table.id FROM $invoices_table WHERE $invoices_table.deleted = 0 ORDER BY $invoices_table.id DESC";

		$sql = "SELECT $mr_table.id FROM $mr_table WHERE $mr_table.deleted = 0 ORDER BY $mr_table.id DESC";
		return $this->db->query($sql);
	}

	function get_mr_total_summary($mr_id = 0, $supplier = 'all')
	{
		$mr_items_table = $this->db->dbprefix('mr_items');
		$Materialrequests_table = $this->db->dbprefix('materialrequests');
		$clients_table = $this->db->dbprefix('clients');
		$taxes_table = $this->db->dbprefix('taxes');

		$and_where = "";
		if ($supplier != 'all') {
			$and_where = " AND $mr_items_table.supplier_name = '{$supplier}' ";
		}

		$item_sql = "SELECT SUM($mr_items_table.total) AS mr_subtotal, currency, currency_symbol 
			FROM $mr_items_table 
			LEFT JOIN $Materialrequests_table ON $Materialrequests_table.id = $mr_items_table.mr_id 
			WHERE $mr_items_table.deleted = 0 $and_where AND $mr_items_table.mr_id = $mr_id AND $Materialrequests_table.deleted = 0";
		$item = $this->db->query($item_sql)->row();

		$mr_sql = "SELECT $Materialrequests_table.*, tax_table.percentage AS tax_percentage, tax_table.title AS tax_name, 
			tax_table2.percentage AS tax_percentage2, tax_table2.title AS tax_name2 
			FROM $Materialrequests_table 
			LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table ON tax_table.id = $Materialrequests_table.tax_id 
			LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table2 ON tax_table2.id = $Materialrequests_table.tax_id2 
			WHERE $Materialrequests_table.deleted = 0 AND $Materialrequests_table.id = $mr_id";
		$order = $this->db->query($mr_sql)->row();
		// arr($mr_sql); exit;
		// $client_sql = "SELECT $clients_table.currency_symbol, $clients_table.currency FROM $clients_table WHERE $clients_table.id = $order->buyer_id";
		// $client = $this->db->query($client_sql)->row();

		$result = new stdClass();
		$result->mr_subtotal = $item->mr_subtotal;
		$result->tax_percentage = isset($order->tax_percentage) ? $order->tax_percentage : 0;
		$result->tax_percentage2 = isset($order->tax_percentage2) ? $order->tax_percentage2 : 0;
		$result->tax_name = isset($order->tax_name) ? $order->tax_name : "-";
		$result->tax_name2 = isset($order->tax_name2) ? $order->tax_name2 : "-";
		$result->tax = 0;
		$result->tax2 = 0;

		$mr_subtotal = $result->mr_subtotal;
		$mr_subtotal_for_taxes = $mr_subtotal;

		if ($order->discount_type == "before_tax") {
			$mr_subtotal_for_taxes = $mr_subtotal - ($order->discount_amount_type == "percentage" ? ($mr_subtotal * ($order->discount_amount / 100)) : $order->discount_amount);
		}
		if ($order->tax_percentage) {
			$result->tax = $mr_subtotal_for_taxes * ($order->tax_percentage / 100);
		}
		if ($order->tax_percentage2) {
			$result->tax2 = $mr_subtotal_for_taxes * ($order->tax_percentage2 / 100);
		}

		$mr_total = $item->mr_subtotal + $result->tax + $result->tax2;

		// Get discount total
		$result->discount_total = 0;
		if ($order->discount_type == "after_tax") {
			$mr_subtotal = $mr_total;
		}

		$result->discount_total = $order->discount_amount_type == "percentage" ? ($mr_subtotal * ($order->discount_amount / 100)) : $order->discount_amount;
		$result->discount_type = $order->discount_type;
		$result->mr_total = $mr_total - number_format($result->discount_total, 2, ".", "");
		// $result->currency_symbol = ($client&&$client->currency_symbol) ? $client->currency_symbol : get_setting("currency_symbol");
		// $result->currency = ($client&&$client->currency) ? $client->currency : get_setting("default_currency");
		$result->currency_symbol = $item->currency_symbol;
		$result->currency = $item->currency;
		return $result;
	}

	function get_processing_mr_total_summary($user_id)
	{
		$mr_items_table = $this->db->dbprefix('mr_items');
		$purchaserequests_table = $this->db->dbprefix('materialrequests');
		$clients_table = $this->db->dbprefix('users');
		$users_table = $this->db->dbprefix('users');
		$taxes_table = $this->db->dbprefix('taxes');

		$where = "";
		if ($user_id) {
			$where = " AND $mr_items_table.created_by=$user_id";
		}

		$mr_tax_id = get_setting('mr_tax_id') ? get_setting('mr_tax_id') : 0;
		$mr_tax_id2 = get_setting('mr_tax_id2') ? get_setting('mr_tax_id2') : 0;
		$item_sql = "SELECT SUM($mr_items_table.total) AS mr_subtotal, tax_table.percentage AS tax_percentage, 
			tax_table.title AS tax_name, tax_table2.percentage AS tax_percentage2, tax_table2.title AS tax_name2 
			FROM $mr_items_table 
			LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table ON tax_table.id = $mr_tax_id 
			LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table2 ON tax_table2.id = $mr_tax_id2 
			WHERE $mr_items_table.deleted=0 AND $mr_items_table.mr_id=0 $where";
		
		$item = $this->db->query($item_sql)->row();
		// var_dump($item); exit;

		// $select_mr_buyer_id = $user_id;
		// $select_mr_buyer_id = "";
		// if ($user_id) {
		// 	$select_mr_buyer_id = "(SELECT $users_table.buyer_id FROM $users_table WHERE $users_table.id=$user_id)";
		// } else {
		// 	$select_mr_buyer_id = "(SELECT $purchaserequests_table.buyer_id FROM $purchaserequests_table WHERE $purchaserequests_table.id = 0)";
		// }

		// $client_sql = "SELECT '฿' as currency_symbol, 'THB' as currency FROM $clients_table WHERE $clients_table.id=$select_mr_buyer_id";
		// $client_sql = "SELECT '฿' as currency_symbol, 'THB' as currency FROM $clients_table WHERE $clients_table.id=$select_mr_buyer_id";
		// $client = $this->db->query($client_sql)->row();

		$result = new stdClass();
		$result->pr_subtotal = $item->mr_subtotal;
		$result->tax_percentage = $item->tax_percentage;
		$result->tax_percentage2 = $item->tax_percentage2;
		$result->tax_name = $item->tax_name;
		$result->tax_name2 = $item->tax_name2;
		$result->tax = 0;
		$result->tax2 = 0;

		$mr_subtotal = $result->pr_subtotal;
		if ($item->tax_percentage) {
			$result->tax = $mr_subtotal * ($item->tax_percentage / 100);
		}
		if ($item->tax_percentage2) {
			$result->tax2 = $mr_subtotal * ($item->tax_percentage2 / 100);
		}
		$result->pr_total = $item->mr_subtotal + $result->tax + $result->tax2;
		
		// $result->currency_symbol = isset($client->currency_symbol) ? $client->currency_symbol : get_setting("currency_symbol");
		// $result->currency = isset($client->currency) ? $client->currency : get_setting("default_currency");

		$result->currency_symbol = get_setting("currency_symbol");
		$result->currency = get_setting("default_currency");
		return $result;
	}

	function get_meterial_request_list($options = array())
	{
		// ALTER TABLE `materialrequests` CHANGE `catid` `catid` INT(11) NOT NULL DEFAULT '1';
		// UPDATE `materialrequests` SET `catid` = 1 WHERE `catid` = 0;

		$this->db->select('materialrequests.id, materialrequests.doc_no, materialrequests.catid, pr_categories.title, materialrequests.project_id, materialrequests.project_name, projects.title as project_names, clients.company_name, users.first_name, users.last_name, materialrequests.mr_date, materialrequests.status_id');
		$this->db->from(' materialrequests');
		$this->db->join('pr_categories', 'materialrequests.catid = pr_categories.id', 'left');
		$this->db->join('projects', 'materialrequests.project_id = projects.id', 'left');
		$this->db->join('clients', 'projects.client_id = clients.id', 'left');
		$this->db->join('users', 'materialrequests.created_by = users.id', 'left');
		$this->db->where('materialrequests.deleted', 0);
		if (!empty($options["status_id"])) {
			$this->db->where('materialrequests.status_id', $options["status_id"]);
		}
		$this->db->where('materialrequests.mr_date >=', $options["start_date"]);
		$this->db->where('materialrequests.mr_date <=', $options["end_date"]);
		
		$query = $this->db->get();
		return $query->result();
	}

	function save_header($options = array())
	{
		$this->db->set("catid", $options["catid"]);
		$this->db->set("payment", $options["payment"]);
		$this->db->set("credit", $options["credit"]);
		$this->db->set("expired", $options["expired"]);
		$this->db->set("mr_date", $options["mr_date"]);
		$this->db->set("note", $options["note"]);
		$this->db->set("tax_id", $options["tax_id"]);
		$this->db->set("tax_id2", $options["tax_id2"]);
		$this->db->where("id", $options["id"]);
		$this->db->update("materialrequests");

		return $this->db->affected_rows();
	}

	public function get_materialrequest_by_id($id = 0)
	{
		$this->db->select('*')
		->from("materialrequests")
		->where("id", $id);

		$query = $this->db->get();
		return $query->row();
	}

	function get_materialrequest_item_by_id($item_id = 0)
	{
		$query = $this->db->get_where('mr_items', array('id' => $item_id));
		return $query->row();
	}

	function dev2_updateApprovalStatus($id, $status_id, $approved_by)
	{
		$this->db->where('id', $id);
		$this->db->update('materialrequests', array(
			'status_id' => $status_id,
			'approved_by' => $approved_by,
			'approved_date' => date("Y-m-d H:i:s")
		));
	}

	function dev2_getProjectIdFromMaterialRequestByStatusId($id)
	{
		$this->db->select('project_id, id')->from('materialrequests')->where('status_id', $id);
		
		$query = $this->db->get();
		return $query->result();
	}

	function dev2_getMrStatusByProjectId($project_id)
	{
		$query = $this->db->get_where('materialrequests', array('project_id' => $project_id, 'status_id !=' => 4))->result();
		return $query;
	}

	function dev2_postMaterialRequestHeader($data)
	{
		$param = array(
            'prefix' => 'MR',
            'LPAD' => 4,
            'column' => 'doc_no',
            'table' => 'materialrequests'
        );
        $data['doc_no'] = $this->Db_model->genDocNo($param);
		$data['project_name'] = $this->Projects_model->getProjectNameById($data['project_id']);
		$this->db->insert('materialrequests', $data);

		return $this->db->insert_id();
	}

	function dev2_putMaterialRequestHeader($data, $id)
	{
		$this->db->set('mr_type', $data['mr_type']);
		$this->db->set('mr_date', $data['mr_date']);
		$this->db->set('catid', $data['catid']);
		$this->db->set('project_id', $data['project_id']);
		$this->db->set('project_name', $this->Projects_model->getProjectNameById($data['project_id']));
		$this->db->set('requester_id', $data['requester_id']);
		$this->db->set('note', $data['note']);
		$this->db->where('id', $id);
		$this->db->update('materialrequests');

		return $this->db->affected_rows();
	}

	private function getStockNameByStockId($stock_id, $bpim_id)
	{
		$sql = "SELECT bs.id AS stock_id, bsg.id, bsg.name, bs.material_id, bs.stock, (bs.price / bs.stock) AS price, bs.price AS price_total, bs.remaining, bpim.actual_used, (bs.stock - bpim.actual_used) AS actual_remaining ";
		$sql .= "FROM bom_stocks AS bs LEFT JOIN bom_stock_groups AS bsg ON bs.group_id = bsg.id ";
		$sql .= "LEFT JOIN (SELECT stock_id, SUM(ratio) AS actual_used FROM bom_project_item_materials WHERE stock_id = " . $stock_id . " AND id <> " . $bpim_id . ") AS bpim ON bs.id = bpim.stock_id ";
		$sql .= "WHERE bs.id = " . $stock_id;

		if (empty($stock_id) && empty($bpim_id)) return null;
		$query = $this->db->query($sql);

		if (empty($query)) return null;
		return $query->row();
	}

	function dev2_getItemListByMaterialRequestId($mr_id)
	{
		$datas = array();
		if (!empty($mr_id)) {
			$datas = $this->db->get_where('mr_items', array('mr_id' => $mr_id))->result();
			if (sizeof($datas)) {
				foreach ($datas as $data) {
					$data->description = mb_strimwidth($data->description, 1, 50, '...');
					$data->quantity = number_format($data->quantity, $this->Settings_m->getDecimalPlacesNumber());
					$data->stocks = $this->getStockNameByStockId($data->stock_id, $data->bpim_id);
				}
			}
		}

		return $datas;
	}

	function dev2_deleteMaterialRequestItem($data = array())
	{
		$info = $this->db->get_where('mr_items', $data)->row();

		if ($info->bpim_id) {
			$this->deleteBomProjectItemMaterialById($info->bpim_id);
		}

		if ($info->id) {
			$this->deleteMaterialRequestItemById($info->id);
		}

		return $info;
	}

	private function deleteBomProjectItemMaterialById($bpim_id)
	{
		$this->db->where('id', $bpim_id);
		$this->db->delete('bom_project_item_materials');
	}

	private function deleteMaterialRequestItemById($id)
	{
		$this->db->where('id', $id);
		$this->db->delete('mr_items');
	}

	function postMaterialRequestItemFromMaterialRequest($data)
	{
		$this->db->insert('mr_items', $data);
        return $this->db->insert_id();
	}

	function patchMaterialRequestItemFromMaterialRequest($data)
	{
		$this->db->set('stock_id', $data['stock_id']);
        $this->db->set('quantity', $data['quantity']);
        $this->db->where('id', $data['id']);
        $this->db->update('mr_items');
        return $this->db->affected_rows();
	}

	function getMaterialListByRequestType($type = 1)
	{
		$sql1 = "
		SELECT 
			bm.id, 
			CONCAT(bm.name, ' - ', bm.production_name) AS material_name 
		FROM bom_materials AS bm 
		LEFT JOIN (
			SELECT material_id, SUM(stock) AS stock 
			FROM bom_stocks 
			WHERE material_id <> 0 
			GROUP BY material_id
		) AS bs ON bm.id = bs.material_id 
		LEFT JOIN (
			SELECT material_id, SUM(ratio) AS ratio 
			FROM bom_project_item_materials 
			WHERE ratio > 0 
			GROUP BY material_id
		) AS bpim ON bm.id = bpim.material_id 
		WHERE 
			IFNULL(bs.stock, 0) > 0 AND IFNULL(bpim.ratio, 0) > 0 AND IFNULL(bs.stock - bpim.ratio, 0) > 0 
		ORDER BY `bm`.`id` ASC
		";

		$data = array();
		$query = $this->db->query($sql1)->result();
		foreach ($query as $item) {
			$data[] = array(
				"id" => $item->id, "text" => $item->material_name
			);
		}

		return $data;
	}

	function getStockMaterialListByMaterialId($material_id)
	{
		$sql = "
		SELECT
			bs.id, bs.group_id, bsg.name,
			bs.stock - IFNULL(bpim.actual_used, 0) AS actual_remain 
		FROM bom_stocks AS bs 
		LEFT JOIN bom_stock_groups bsg ON bs.group_id = bsg.id 
		LEFT JOIN (
			SELECT stock_id, SUM(ratio) AS actual_used 
			FROM bom_project_item_materials 
			WHERE ratio > 0 AND material_id = " . $material_id . " 
			GROUP BY stock_id
		) AS bpim ON bs.id = bpim.stock_id 
		WHERE 1 AND bs.material_id = " . $material_id . " 
		AND bs.stock - IFNULL(bpim.actual_used, 0) > 0 
		ORDER BY bs.id
		";

		$query = $this->db->query($sql)->result();
		foreach ($query as $item) {
			$item->remaining = number_format($item->actual_remain, 2);
		}

		return $query;
	}

}