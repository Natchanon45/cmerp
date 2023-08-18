<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Share extends PublicController
{
    function __construct()
    {
        parent::__construct();
    }

    function quotation()
    {
        $this->data["doc"] = $doc = $this->Quotations_m->getEdoc(null, $this->uri->segment(5));
        $this->data["og_title"] = get_setting("company_name") . " - " . $doc["doc_number"];

        if ($doc["status"] != "success") redirect("forbidden");
        $this->load->view('edocs/quotation', $this->data);
    }

    function billing_note()
    {
        $this->data["doc"] = $doc = $this->Billing_notes_m->getEdoc(null, $this->uri->segment(5));
        $this->data["og_title"] = get_setting("company_name") . " - " . $doc["doc_number"];

        if ($doc["status"] != "success") redirect("forbidden");
        $this->load->view('edocs/billing_note', $this->data);
    }

    function invoice()
    {
        $this->data["doc"] = $doc = $this->Invoices_m->getEdoc(null, $this->uri->segment(5));
        $this->data["og_title"] = get_setting("company_name") . " - " . $doc["doc_number"];

        if ($doc["status"] != "success") redirect("forbidden");
        $this->load->view('edocs/invoice', $this->data);
    }

    function tax_invoice()
    {
        $this->data["doc"] = $doc = $this->Tax_invoices_m->getEdoc(null, $this->uri->segment(5));
        $this->data["og_title"] = get_setting("company_name") . " - " . $doc["doc_number"];

        if ($doc["status"] != "success") redirect("forbidden");
        $this->load->view('edocs/tax_invoice', $this->data);
    }

    function receipt()
    {
        $this->data["doc"] = $doc = $this->Receipts_m->getEdoc(null, $this->uri->segment(5));
        $this->data["og_title"] = get_setting("company_name") . " - " . $doc["doc_number"];

        if ($doc["status"] != "success") redirect("forbidden");
        $this->load->view('edocs/receipt', $this->data);
    }

    function purchase_request()
    {
        $this->data["doc"] = $doc = $this->Purchase_request_m->getEdoc(null, $this->uri->segment(5));
        $this->data["og_title"] = get_setting("company_name") . " - " . $doc["doc_number"];

        if ($doc["status"] != "success") redirect("forbidden");
        $this->load->view('edocs/purchase_request', $this->data);
    }

    function credit_note()
    {
        $this->data["doc"] = $doc = $this->Credit_notes_m->getEdoc(null, $this->uri->segment(5));
        $this->data["og_title"] = get_setting("company_name") . " - " . $doc["doc_number"];

        if ($doc["status"] != "success") redirect("forbidden");
        $this->load->view('edocs/credit_note', $this->data);
    }

    function debit_note()
    {
        $this->data["doc"] = $doc = $this->Debit_notes_m->getEdoc(null, $this->uri->segment(5));
        $this->data["og_title"] = get_setting("company_name") . " - " . $doc["doc_number"];

        if ($doc["status"] != "success") redirect("forbidden");
        $this->load->view('edocs/debit_note', $this->data);
    }

    function purchase_order()
    {
        $this->data["doc"] = $doc = $this->Purchase_order_m->getEdoc(null, $this->uri->segment(5));
        $this->data["og_title"] = get_setting("company_name") . " - " . $doc["doc_number"];
        
        if ($doc["status"] != "success") redirect("forbidden");
        $this->load->view('edocs/purchase_order', $this->data);
    }

}