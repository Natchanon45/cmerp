<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Accounting extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        /*if($this->Permission_m->canAccessAccounting() != true){
            $this->session->set_flashdata('notice_error', lang('no_permissions'));
            redirect("/");
        }*/

        $this->load->model('Purchaserequest_m');
        $this->data["company_setting"] = $this->Settings_m->getCompany();
    }

    function index()
    {
        redirect("/accounting/sell");
    }

    // ผังบัญชี
    function chart()
    {
        $this->template->rander("accounting/chart");
    }

    // บัญชีขาย
    function sell()
    {
        if ($this->Permission_m->accounting["sales_order"]["access"] == true) {
            $this->data["module"] = "sales-orders";
        } elseif ($this->Permission_m->accounting["quotation"]["access"] == true) {
            $this->data["module"] = "quotations";
        } elseif ($this->Permission_m->accounting["invoice"]["access"] == true) {
            $this->data["module"] = "invoices";
        } elseif ($this->Permission_m->accounting["tax_invoice"]["access"] == true) {
            $this->data["module"] = "tax-invoices";
        } elseif ($this->Permission_m->accounting["billing_note"]["access"] == true) {
            $this->data["module"] = "billing-notes";
        } elseif ($this->Permission_m->accounting["receipt"]["access"] == true) {
            $this->data["module"] = "receipts";
        } elseif ($this->Permission_m->accounting["credit_note"]["access"] == true) {
            $this->data["module"] = "credit-notes";
        } else {
            $this->session->set_flashdata('notice_error', lang('no_permissions'));
            redirect("accounting/buy");
            return;
        }

        if ($this->uri->segment(3) != null) {
            $this->data["module"] = $this->uri->segment(3);
        } else {
            if ($this->Permission_m->accounting["sales_order"]["access"] == true)
                $this->data["module"] = "sales-orders";
            elseif ($this->Permission_m->accounting["quotation"]["access"] == true)
                $this->data["module"] = "quotations";
            elseif ($this->Permission_m->accounting["invoice"]["access"] == true)
                $this->data["module"] = "invoices";
            elseif ($this->Permission_m->accounting["tax_invoice"]["access"] == true)
                $this->data["module"] = "tax-invoices";
            elseif ($this->Permission_m->accounting["billing_note"]["access"] == true)
                $this->data["module"] = "billing-notes";
            elseif ($this->Permission_m->accounting["receipt"]["access"] == true)
                $this->data["module"] = "receipts";
            elseif ($this->Permission_m->accounting["credit_note"]["access"] == true)
                $this->data["module"] = "credit-notes";
        }

        $cusrows = $this->Customers_m->getRows(["id", "company_name"]);
        $client_ids[] = ["id" => "", "text" => "-- " . lang("account_customer") . " --"];
        if (!empty($cusrows)) {
            foreach ($cusrows as $cusrow) {
                $client_ids[] = ["id" => $cusrow->id, "text" => $cusrow->company_name];
            }
        }

        $cusgrows = $this->Customers_m->getGroupRows();
        $client_group_ids[] = ["id" => "", "text" => "-- " . lang("account_customer_group") . " --"];
        if (!empty($cusgrows)) {
            foreach ($cusgrows as $cusgrow) {
                $client_group_ids[] = ["id" => "$cusgrow->id", "text" => $cusgrow->title];
            }
        }

        $this->data["client_ids"] = json_encode($client_ids);
        $this->data["client_group_ids"] = json_encode($client_group_ids);
        $this->data["billing_type"] = $this->data["company_setting"]["company_billing_type"];

        $this->data["auth_sell"] = $this->auth_accounting_sell();
        $this->data["auth_buy"] = $this->auth_accounting_buy();

        $this->template->rander("accounting/sell", $this->data);
    }

    // บัญชีซื้อ
    function buy()
    {
        $permissions = (object) $this->Permission_m->accounting;
        // var_dump(arr($permissions)); exit();

        // Permission Check
        if ($permissions->purchase_request["access"]) {
            $this->data["module"] = "purchase_request";
        } elseif ($permissions->purchase_order["access"]) {
            $this->data["module"] = "purchase_order";
        } elseif ($permissions->payment_voucher["access"]) {
            $this->data["module"] = "payment_voucher";
        } elseif ($permissions->goods_receipt["access"]) {
            $this->data["module"] = "goods_receipt";
        } else {
            $this->session->set_flashdata("notice_error", lang("no_permissions"));
            redirect("/");
            return;
        }

        // Current Module
        if ($this->uri->segment(3) != null) {
            $this->data["module"] = $this->uri->segment(3);
        }

        // Define Modal Header
        $modal_header = str_replace("https:", "", str_replace("http:", "", str_replace("/", "", base_url())));
        $this->data["modal_header"] = strtoupper($modal_header);

        // Prepare dropdown status
        $this->data["supplier_dropdown"] = json_encode($this->Bom_suppliers_model->dev2_getSupplierDropdownWithCode());
        $this->data["pr_status_dropdown"] = json_encode($this->Purchaserequest_m->dev2_getPrStatusDropdown());
        $this->data["po_status_dropdown"] = json_encode($this->Purchaserequest_m->dev2_getPoStatusDropdown());
        $this->data["pv_status_dropdown"] = json_encode($this->Purchaserequest_m->dev2_getPvStatusDropdown());
        $this->data["gr_status_dropdown"] = json_encode($this->Purchaserequest_m->dev2_getGrStatusDropdown());
        $this->data["type_dropdown"] = json_encode($this->Purchaserequest_m->dev2_getPrTypeDropdown());

        $this->data["user_permissions"] = $permissions;
        if (isset($this->Permission_m->bom_supplier_read) && $this->Permission_m->bom_supplier_read) {
            $this->data["user_permissions"]->supplier["access"] = true;
        } else {
            $this->data["user_permissions"]->supplier["access"] = false;
        }
        $this->data["auth_sell"] = $this->auth_accounting_sell();
        $this->data["auth_buy"] = $this->auth_accounting_buy();

        // var_dump(arr($this->data)); exit();
        $this->template->rander("accounting/buy", $this->data);
    }

    private function auth_accounting_sell(): bool
    {
        $auth = false;

        $auth_size = 0;
        if ($this->Permission_m->accounting["sales_order"]["access"])
            $auth_size++;
        if ($this->Permission_m->accounting["quotation"]["access"])
            $auth_size++;
        if ($this->Permission_m->accounting["invoice"]["access"])
            $auth_size++;
        if ($this->Permission_m->accounting["tax_invoice"]["access"])
            $auth_size++;
        if ($this->Permission_m->accounting["billing_note"]["access"])
            $auth_size++;
        if ($this->Permission_m->accounting["receipt"]["access"])
            $auth_size++;
        if ($this->Permission_m->accounting["credit_note"]["access"])
            $auth_size++;
        if ($this->Permission_m->accounting["debit_note"]["access"])
            $auth_size++;

        if ($auth_size > 0) {
            $auth = true;
        }

        return $auth;
    }

    private function auth_accounting_buy(): bool
    {
        $auth = false;

        $auth_size = 0;
        if ($this->Permission_m->accounting["purchase_request"]["access"])
            $auth_size++;
        if ($this->Permission_m->accounting["purchase_order"]["access"])
            $auth_size++;
        if ($this->Permission_m->accounting["payment_voucher"]["access"])
            $auth_size++;
        if ($this->Permission_m->accounting["goods_receipt"]["access"])
            $auth_size++;

        if ($auth_size > 0) {
            $auth = true;
        }

        return $auth;
    }

}
