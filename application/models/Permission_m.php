<?php
class Permission_m extends MY_Model {
	public $permissions = null;

	public $access_note = "assigned_only";
	public $access_note_specific = null;
	public $update_note = false;

	public $access_product_item_formula = false;
	public $create_product_item = false;

	public $accounting_quotation_access = false;
	public $accounting_billing_note_access = false;
	public $accounting_invoice_access = false;
	public $accounting_receipt_access = false;

	public $access_material_request = false;
	public $create_material_request = false;
	public $update_material_request = false;
	public $delete_material_request = false;
	public $approve_material_request = false;

	public $access_purchase_request = false;
	public $create_purchase_request = false;
	public $update_purchase_request = false;
	public $delete_purchase_request = false;
	public $approve_purchase_request = false;

	function __construct() {		
		$urow = $this->db->select("is_admin, role_id")
								->from("users")
								->where("id", $this->session->userdata("user_id"))
								->get()->row();

		if(empty($urow)) return;

		if($urow->is_admin == 1){
			$this->setAdmin();
		}else{
	        $prow = $this->db->select("permissions")
						        			->from("roles")
						        			->where("id", $urow->role_id)
						        			->where("deleted", 0)
						        			->get()->row();
						        			
			if(!empty($prow)){
				$this->permissions = json_decode(json_encode(unserialize($prow->permissions)));
				$this->setPermission();
			}
		}
	}

	function setAdmin(){
		$this->access_note = "all";
		$this->access_note_specific = null;
		$this->update_note = true;

		$this->access_product_item_formula = true;
		$this->create_product_item = true;

		$this->accounting_quotation_access = true;
		$this->accounting_billing_note_access = true;
		$this->accounting_invoice_access = true;
		$this->accounting_receipt_access = true;

		$this->access_material_request = true;
		$this->create_material_request = true;
		$this->update_material_request = true;
		$this->delete_material_request = true;
		$this->approve_material_request = true;

		$this->access_purchase_request = true;
		$this->create_purchase_request = true;
		$this->update_purchase_request = true;
		$this->delete_purchase_request = true;
		$this->approve_purchase_request = true;
	}

	function setPermission(){
		$p = $this->permissions;

		//Note
		if(isset($p->access_note)) $this->access_note = $p->access_note;
		if(isset($p->access_note_specific)) $this->access_note_specific = $p->access_note_specific;
		if(isset($p->update_note)) $this->update_note = $p->update_note;

		//Product Item
		if(isset($p->access_product_item_formula)) $this->access_product_item_formula = $p->access_product_item_formula;
		if(isset($p->create_product_item)) $this->create_product_item = $p->create_product_item;

		//Accounting
		if(isset($p->accounting->quotation->access)) $this->accounting_quotation_access = $p->accounting->quotation->access;
		if(isset($p->accounting->billing_note->access)) $this->accounting_billing_note_access = $p->accounting->billing_note->access;
		if(isset($p->accounting->invoice->access)) $this->accounting_invoice_access = $p->accounting->invoice->access;
		if(isset($p->accounting->receipt->access)) $this->accounting_receipt_access = $p->accounting->receipt->access;

		//Material Request
		if(isset($p->access_material_request)) $this->access_material_request = $p->access_material_request;

		if(isset($p->create_material_request)){
			$this->create_material_request = $p->create_material_request;
			if($this->access_material_request == false) $this->create_material_request = false;
		}

		if(isset($p->update_material_request)){
			$this->update_material_request = $p->update_material_request;
			if($this->access_material_request == false) $this->update_material_request = false;
		}

		if(isset($p->delete_material_request)){
			$this->delete_material_request = $p->delete_material_request;
			if($this->access_material_request == false) $this->delete_material_request = false;
		}

		if(isset($p->approve_material_request)){
			$this->approve_material_request = $p->approve_material_request;
			if($this->access_material_request == false) $this->approve_material_request = false;
		}

		//Purchase Request
		if(isset($p->access_purchase_request)) $this->access_purchase_request = $p->access_purchase_request;

		if(isset($p->create_purchase_request)){
			$this->create_purchase_request = $p->create_purchase_request;
			if($this->access_purchase_request == false) $this->create_purchase_request = false;
		}

		if(isset($p->update_purchase_request)){
			$this->update_purchase_request = $p->update_purchase_request;
			if($this->access_purchase_request == false) $this->update_purchase_request = false;
		}

		if(isset($p->delete_purchase_request)){
			$this->delete_purchase_request = $p->delete_purchase_request;
			if($this->access_purchase_request == false) $this->delete_purchase_request = false;
		}

		if(isset($p->approve_purchase_request)){
			$this->approve_purchase_request = $p->approve_purchase_request;
			if($this->access_purchase_request == false) $this->approve_purchase_request = false;
		}

	}

	function canAccessAccounting(){
		if($this->accounting_quotation_access == true) return true;
		if($this->accounting_billing_note_access == true) return true;
		if($this->accounting_invoice_access == true) return true;
		if($this->accounting_receipt_access == true) return true;

		return false;
	}

	function login_user_test()
	{
		return $this->login_user;
	}

}