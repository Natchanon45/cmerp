<?php
class Permission_m extends MY_Model {
	public $permissions = null;

	public $access_note = "assigned_only";
	public $access_note_specific = null;
	public $add_note = false;
	public $update_note = false;

	public $access_product_item = false;
	public $access_product_item_formula = false;
	public $create_product_item = false;

	public $access_expense = false;

	public $accounting = [
							"sales_order"=>["access"=>false],
							"quotation"=>["access"=>false],
							"invoice"=>["access"=>false],
							"tax_invoice"=>["access"=>false],
							"billing_note"=>["access"=>false],
							"receipt"=>["access"=>false],
							"credit_note"=>["access"=>false],
							"debit_note"=>["access"=>false]
						];

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

			if(!empty($prow)) $this->setPermission(json_decode(json_encode(unserialize($prow->permissions))));
		}
	}

	function setAdmin(){
		$permissions["access_note"] = $this->access_note = "all";
		$permissions["access_note_specific"] = $this->access_note_specific = null;
		$permissions["add_note"] = $this->add_note = true;
		$permissions["update_note"] = $this->update_note = true;

		$permissions["access_product_item"] = $this->access_product_item = "all";
		$permissions["access_product_item_formula"] = $this->access_product_item_formula = true;
		$permissions["create_product_item"] = $this->create_product_item = true;

		$permissions["access_expense"] = $this->access_expense = "all";

		$permissions["accounting"] = $this->accounting = [
													"sales_order"=>["access"=>true],
													"quotation"=>["access"=>true],
													"invoice"=>["access"=>true],
													"tax_invoice"=>["access"=>true],
													"billing_note"=>["access"=>true],
													"receipt"=>["access"=>true],
													"credit_note"=>["access"=>true],
													"debit_note"=>["access"=>true]
												];

		$permissions["access_material_request"] = $this->access_material_request = true;
		$permissions["create_material_request"] = $this->create_material_request = true;
		$permissions["update_material_request"] = $this->update_material_request = true;
		$permissions["delete_material_request"] = $this->delete_material_request = true;
		$permissions["approve_material_request"] = $this->approve_material_request = true;

		$permissions["access_purchase_request"] = $this->access_purchase_request = true;
		$permissions["create_purchase_request"] = $this->create_purchase_request = true;
		$permissions["update_purchase_request"] = $this->update_purchase_request = true;
		$permissions["delete_purchase_request"] = $this->delete_purchase_request = true;
		$permissions["approve_purchase_request"] = $this->approve_purchase_request = true;

		$this->permissions = $permissions;
	}

	function setPermission($permissions){
		//Note
		if(isset($permissions->access_note)) $this->access_note = $permissions->access_note;
		if(isset($permissions->access_note_specific)) $this->access_note_specific = $permissions->access_note_specific;
		if(isset($permissions->add_note)) $this->add_note = $permissions->add_note;
		if(isset($permissions->update_note)) $this->update_note = $permissions->update_note;

		//Product Item
		if(isset($permissions->access_product_item)) $this->access_product_item = $permissions->access_product_item;
		if(isset($permissions->access_product_item_formula)) $this->access_product_item_formula = $permissions->access_product_item_formula;
		if(isset($permissions->create_product_item)) $this->create_product_item = $permissions->create_product_item;

		//Expenses
		if(isset($permissions->access_expenses)) $this->access_expenses = $permissions->access_expenses;

		//Accounting
		if(isset($permissions->accounting->sales_order->access)) $this->accounting["sales_order"]["access"] = $permissions->accounting->sales_order->access;
		if(isset($permissions->accounting->quotation->access)) $this->accounting["quotation"]["access"] = $permissions->accounting->quotation->access;
		if(isset($permissions->accounting->invoice->access)) $this->accounting["invoice"]["access"] = $permissions->accounting->invoice->access;
		if(isset($permissions->accounting->tax_invoice->access)) $this->accounting["tax_invoice"]["access"] = $permissions->accounting->tax_invoice->access;
		if(isset($permissions->accounting->billing_note->access)) $this->accounting["billing_note"]["access"] = $permissions->accounting->billing_note->access;
		if(isset($permissions->accounting->receipt->access)) $this->accounting["receipt"]["access"] = $permissions->accounting->receipt->access;
		if(isset($permissions->accounting->credit_note->access)) $this->accounting["credit_note"]["access"] = $permissions->accounting->credit_note->access;
		if(isset($permissions->accounting->debit_note->access)) $this->accounting["debit_note"]["access"] = $permissions->accounting->debit_note->access;

		//Material Request
		if(isset($permissions->access_material_request)) $this->access_material_request = $permissions->access_material_request;

		if(isset($permissions->create_material_request)){
			$this->create_material_request = $permissions->create_material_request;
			if($this->access_material_request == false) $this->create_material_request = false;
		}

		if(isset($permissions->update_material_request)){
			$this->update_material_request = $permissions->update_material_request;
			if($this->access_material_request == false) $this->update_material_request = false;
		}

		if(isset($permissions->delete_material_request)){
			$this->delete_material_request = $permissions->delete_material_request;
			if($this->access_material_request == false) $this->delete_material_request = false;
		}

		if(isset($permissions->approve_material_request)){
			$this->approve_material_request = $permissions->approve_material_request;
			if($this->access_material_request == false) $this->approve_material_request = false;
		}

		//Purchase Request
		if(isset($permissions->access_purchase_request)) $this->access_purchase_request = $permissions->access_purchase_request;

		if(isset($permissions->create_purchase_request)){
			$this->create_purchase_request = $permissions->create_purchase_request;
			if($this->access_purchase_request == false) $this->create_purchase_request = false;
		}

		if(isset($permissions->update_purchase_request)){
			$this->update_purchase_request = $permissions->update_purchase_request;
			if($this->access_purchase_request == false) $this->update_purchase_request = false;
		}

		if(isset($permissions->delete_purchase_request)){
			$this->delete_purchase_request = $permissions->delete_purchase_request;
			if($this->access_purchase_request == false) $this->delete_purchase_request = false;
		}

		if(isset($permissions->approve_purchase_request)){
			$this->approve_purchase_request = $permissions->approve_purchase_request;
			if($this->access_purchase_request == false) $this->approve_purchase_request = false;
		}

		$this->permissions = $permissions;

	}

	function canAccessAccounting(){
		if($this->accounting["sales_order"]["access"] == true) return true;
		if($this->accounting["quotation"]["access"] == true) return true;
		if($this->accounting["invoice"]["access"] == true) return true;
		if($this->accounting["tax_invoice"]["access"] == true) return true;
		if($this->accounting["billing_note"]["access"] == true) return true;
		if($this->accounting["receipt"]["access"] == true) return true;
		if($this->accounting["credit_note"]["access"] == true) return true;
		if($this->accounting["debit_note"]["access"] == true) return true;

		return false;
	}
}