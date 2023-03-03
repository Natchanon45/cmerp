<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Action extends MY_Controller {

 

    function __construct() {
        parent::__construct();
		$this->load->helper('notifications');
        $this->load->model('Notifications_model');
		$this->load->model('MaterialRequests_model');
		$this->load->model('Mr_items_model');
        $this->load->model('Bom_item_stock_groups_model');
        $this->load->model('Bom_item_stocks_model');
    }

    public function index() {
      echo 'sdafadsfsd';
    }
	
	function proveDoc( $tbName, $doc_id = NULL, $status_id = 1 ) {

		//var_dump($tbName);exit;
		$real_status_id = intval( $status_id );
		
		/*$sql = "
			SELECT 
				* 
			FROM prove_table 
			WHERE doc_id = ". $doc_id ." 
			AND tbName = '". $tbName ."'
		";
		
		$insert = false;
		foreach( $this->dao->fetchAll( $sql ) as $ka => $va ) {
			
			$insert = true;
			$real_status_id = 0;
			$sql = "
				DELETE FROM prove_table
				WHERE doc_id = ". $doc_id ." 
				AND tbName = '". $tbName ."'
			";
		}
		$approve_status = !$insert;*/


		if( $insert == false ) {
			
			/*if( $tbName == 'invoices' ){
				
				$sql1 = "UPDATE invoices SET prove = 1 WHERE id = $doc_id ";

				$this->dao->execDatas( $sql1 );
			}*/

			if(true && $doc_id && ($tbName == 'materialrequests') ) {
				/*log_message("error", $_SERVER['HTTP_REFERER']);
				$_SESSION['error'] = lang('OOS');
				redirect( $_SERVER['HTTP_REFERER'] );
				return;*/

				$items = $this->Mr_items_model->get_details(['mr_id'=>$doc_id,"item_type"=>"all"])->result();
				//var_dump($items);exit;
				$resources = [];
				$restock_item_ids = [];
				$item_ids = [];
				$item_stocks = [];
				$item_prices = [];
	
				$restock_ids = [];
				$material_ids = [];
				$stocks = [];
				$prices = [];
				$mr_used = false;
				$mr_posible =false;
				//var_dump($items);exit;

				//check if possible to reduce mat/item
				foreach($items as $item) {
					if($item->item_id >0) {
						$mr_posible = $this->Bom_item_stocks_model->check_posibility($item->item_id, $item->quantity);
					}else if($item->material_id >0) {
						$mr_posible = $this->Bom_stocks_model->check_posibility($item->material_id, $item->quantity);
					}
				}

				//if possible, reduce the stock
				if($mr_posible){
					foreach($items as $item) {
						if($item->item_id >0) {
							$mr_used = $this->Bom_item_stocks_model->reduce_item_of_group($item->item_id, $item->quantity);
						}else if($item->material_id >0) {
							$mr_used = $this->Bom_stocks_model->reduce_material_of_group($item->material_id, $item->quantity);
						}
					}
				}
				//if not, aboard prove.
				else{
					$_SESSION['error'] = lang('OOS');
					return redirect( $_SERVER['HTTP_REFERER'] );
				}
				//var_dump($mr_posible);exit;
				
			}

			
			
			/*$sql = "
				INSERT INTO prove_table ( doc_id, tbName, user_id, doc_date, status_id ) 
				
				SELECT
				". $doc_id ." as doc_id, 
				'". $tbName ."' as tbName,
				
				". $_SESSION['user_id'] ." user_id, 
				NOW() as doc_date,
				". $status_id ." as status_id
			";*/
			
		 
		}
		$this->dao->execDatas( $sql );
		if($approve_status && ($tbName == 'purchaserequests')) {
			$this->dao->runPoNo($doc_id);
		}
		if(true && $doc_id && ($tbName == 'receipts') ) {
			$receipt = $this->Receipts_model->get_details(['id'=>$doc_id])->row();
			$items = $this->Receipt_items_model->get_details(['receipt_id'=>$doc_id])->result();
			$resources = [];
			$restock_item_ids = [];
			$item_ids = [];
			$item_stocks = [];
			$item_prices = [];

			$restock_ids = [];
			$material_ids = [];
			$stocks = [];
			$prices = [];
			foreach($items as $item) {
				list($pr_id, $supplier_id) = explode('-', $item->po_id);
				$pr_item = $this->Receipt_items_model->get_pr_item($pr_id, $supplier_id, $item->material_id, $item->item_id);
				$data = array(
					"name" => 'Receipt: '.$receipt->doc_no,
					"created_by" => $this->login_user->id,
					"created_date" => date('Y-m-d'),
					"po_no" => $pr_item?$pr_item->po_no:''
				);
				$data = clean_data($data);
				if($item->item_id >0) {
					if($receipt->item_stock_group_id>0 && !isset($resources['item'])) {
						$resources['item'] = $this->Bom_item_stock_groups_model->get_details(['id'=>$item->item_stock_group_id])->row();
					}
					if(!isset($resources['item'])) {
						$data['id'] = $this->Bom_item_stock_groups_model->save($data, 0);
						$resources['item'] = (object)$data;
					}
					if(isset($resources['item'])) {
						$oitem_stock = $this->Bom_item_stocks_model->get_details(['item_id'=>$item->item_id,'group_id'=>$resources['item']->id])->row();
						$restock_item_ids[] = $oitem_stock?$oitem_stock->id:0;
						$item_ids[] = $item->item_id;
						$item_stocks[] = $approve_status?$item->quantity:0;
						$item_prices[] = $item->total;
					}
				}elseif($item->material_id >0) {
					if($receipt->material_stock_group_id>0 && !isset($resources['material'])) {
						$resources['material'] = $this->Bom_stock_groups_model->get_details(['id'=>$item->material_stock_group_id])->row();
					}
					if(!isset($resources['material'])) {
						$data['id'] = $this->Bom_stock_groups_model->save($data, 0);
						$resources['material'] = (object)$data;
					}
					if(isset($resources['material'])) {
						$omaterial_stock = $this->Bom_stocks_model->get_details(['material_id '=>$item->material_id ,'group_id'=>$resources['material']->id])->row();
						$restock_ids[] = $omaterial_stock?$omaterial_stock->id:0;
						$material_ids[] = $item->material_id;
						$stocks[] = $approve_status?$item->quantity:0;
						$prices[] = $item->total;
					}
				}
			}
			if(isset($resources['item'])) {
				$this->Bom_item_stock_groups_model->restock_save(
					$resources['item']->id, $restock_item_ids, $item_ids, $item_stocks, $item_prices
				);
				$data = ['item_stock_group_id'=>$receipt->item_stock_group_id];
				$this->Receipt_items_model->save($data, $resources['item']->id);
			}
			if(isset($resources['material'])) {
				$this->Bom_stock_groups_model->restock_save(
					$resources['material']->id, $restock_ids, $material_ids, $stocks, $prices
				);
				$data = ['material_stock_group_id'=>$receipt->material_stock_group_id];
				$this->Receipt_items_model->save($data, $resources['material']->id);
			}
		}//end receipts

		$status=[
			0=>'unapproved',
			1=>'approved',
			2=>'rejected'
		];
		
	
		if( in_array( 'doc_no', $this->dao->showColumns( $tbName ) ) ) {
			$sql = "
				SELECT 
					created_by,
					doc_no
				FROM `". $tbName ."`
				WHERE id = '". $doc_id ."'
				;
			";
			$row = $this->db->query($sql)->row();
			$doc_no = $row->doc_no;
		}
		else {
			
			$sql = "
				SELECT 
					created_by
				FROM `". $tbName ."`
				WHERE id = '". $doc_id ."'
				;
			";
			$row = $this->db->query($sql)->row();
			$doc_no = $doc_id;
		}
		

		 
		$this->sendMessage(
			$tbName,
			$doc_id,
			$row->created_by,
			lang($tbName.'_status_was_changed').'['.lang(@$status[$real_status_id]).']',
			lang($tbName.'_status_was_changed_please_check').'['.lang(@$status[$real_status_id]).']'.' <a href="'.get_uri( "$tbName/view/" . $doc_id).'">'.lang('docnumber').':'. $doc_no .'</a>'
		);
	
		 
		
		
		redirect( $_SERVER['HTTP_REFERER'] );
	}

	private function sendMessage($tbname, $pkid, $receiver_id, $subject, $message) {
		$param = [];
		$param['url'] = $tbname;
		$param['table_name'] = $tbname;
		$param['user_id'] = $receiver_id;
		$this->getRolePermission = $this->dao->getRolePermission( $param  );
		
		
		$param['needToknowCanprove'] = 1;
		$param['id'] =  $pkid;
		$param['tbName']  = $tbname;
			
		
		/*if( !$this->dao->getProveButton( $param ) ) {
			return;
		}*/
	
		$message_data = array(
			"from_user_id" => $this->login_user->id,
			"to_user_id" => $receiver_id,
			"subject" => $subject,
			"message" => $message,
			"created_at" => get_current_utc_time(),
			"deleted_by_users" => "",
		);
		$message_data = clean_data( $message_data );
		$this->Messages_model->save($message_data);
	}
	
	function requestApprove( $tbname, $pkid ) {

		$setting['purchaserequests'] = array(
			'event' => 'new_purchaserequest_created',
			'pk_feld' => 'pr_id',
		);
		
		/* $setting['orders'] = array(
			'event' => 'new_purchaserequest_created',
			'pk_feld' => 'pr_id',
		); */

		$setting['receipts'] = array(
			'event' => 'new_purchaserequest_created',
			'pk_feld' => 'receipt_id',
		);
 	 
		$setting['invoices'] = array(
			'event' => 'new_invoice_created',
			'pk_feld' => 'invoice_id',
		);
		
		$setting['estimates'] = array(
			'event' => 'new_estimate_created',
			'pk_feld' => 'estimate_id',
		);
		
		$setting['payment_vouchers'] = array(
			'event' => 'new_payment_voucher_created',
			'pk_feld' => 'pv_id',
		);
		
		if( !isset( $setting[$tbname] ) ) {
			
			redirect( $_SERVER['HTTP_REFERER'] );
			
			exit;
		}
		
 	 	 
		$sql = "
			SELECT 
				u.id as user_id
			FROM users u
			
			WHERE u.role_id IN (
				SELECT 
					role_permission.role_id
				FROM role_permission
				WHERE role_permission.table_name = '". $tbname ."'
				AND role_permission.prove_row > 0
		)
		";
		

		$haveTocreate_notification = false;
		foreach( $this->dao->fetchAll( $sql ) as $ku => $vu ) {	
			$this->sendMessage(
				$tbname,
				$pkid,
				$vu->user_id,
				lang($setting[$tbname]['event']),
				lang($setting[$tbname]['event']).' <a href="'.get_uri( "$tbname/view/" . $pkid).'">'.lang('docnumber').':'.$pkid.'</a>'
			);
			/*$param['url'] = $tbname;
			$param['table_name'] = $tbname;
			$param['user_id'] = $vu->user_id;
			$this->getRolePermission = $this->dao->getRolePermission( $param  );
			
			
			$param['needToknowCanprove'] = 1;
			$param['id'] =  $pkid;
			$param['tbName']  = $tbname;
			 
			
			if( !$this->dao->getProveButton( $param ) ) {
				
				continue;
			}
		
			$message_data = array(
				"from_user_id" => $this->login_user->id,
				"to_user_id" => $vu->user_id,
				"subject" => lang($setting[$tbname]['event']),
				"message" => lang($setting[$tbname]['event']).' <a href="'.get_uri( "$tbname/view/" . $pkid).'">'.lang('docnumber').':'.$pkid.'</a>',
				"created_at" => get_current_utc_time(),
				"deleted_by_users" => "",
			);
			$message_data = clean_data( $message_data );
			$this->Messages_model->save($message_data);
			*/
			
			$haveTocreate_notification = true;
		}
		
		if( $haveTocreate_notification == true ) {
			
			$this->Notifications_model->create_notification( $setting[$tbname]['event'], $this->login_user->id, [$setting[$tbname]['pk_feld']=>$pkid]);
		}

		redirect( $_SERVER['HTTP_REFERER'] );
 
	}
	
	
	function requestApprove__________( $tbname, $pkid ) {
		
		
		$setting['purchaserequests'] = array(
			'event' => 'new_purchaserequest_created',
			'pk_feld' => 'pr_id',
		);
 	 
		$setting['invoices'] = array(
			'event' => 'new_invoice_created',
			'pk_feld' => 'invoice_id',
		);
		
		$setting['estimates'] = array(
			'event' => 'new_estimate_created',
			'pk_feld' => 'estimate_id',
		);

		$setting['payment_vouchers'] = array(
			'event' => 'new_payment_voucher_created',
			'pk_feld' => 'pv_id',
		);
 	 
		
  
		 
		$sql = "
			SELECT 
				u.id 
			FROM users u
			
			WHERE u.role_id IN (
				SELECT 
					role_permission.role_id
				FROM role_permission
				WHERE role_permission.table_name = '". $tbname ."'
				AND role_permission.prove_row > 0
		)
		";
		

		///$user_ids = $this->db->query($sql)->row_array();
		//$user_ids = $user_ids?$user_ids:[];
		//foreach( $user_ids as $to_user_id ) {
		$haveTocreate_notification = false;
		foreach( $this->dao->fetchAll( $sql ) as $ku => $vu ) {	
		//arr( $vu );	
			$message_data = array(
				"from_user_id" => $this->login_user->id,
				"to_user_id" => $vu->id,
				"subject" => lang($setting[$tbname]['event']),
				"message" => lang($setting[$tbname]['event']).' <a href="'.get_uri("$tbname/view/" . $pkid).'">'.lang('docnumber').':'.$pkid.'</a>',
				"created_at" => get_current_utc_time(),
				"deleted_by_users" => "",
			);
			$message_data = clean_data($message_data);
			$this->Messages_model->save($message_data);
			
			
			$haveTocreate_notification = true;
		}
		
		if( $haveTocreate_notification == true ) {
			
			$this->Notifications_model->create_notification( $setting[$tbname]['event'], $this->login_user->id, [$setting[$tbname]['pk_feld']=>$pkid]);
		}
//arr( $sql );

///exit;
		redirect( $_SERVER['HTTP_REFERER'] );
 
	}
}







