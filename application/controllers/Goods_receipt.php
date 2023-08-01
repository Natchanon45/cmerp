<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Goods_receipt extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        if (!$this->Permission_m->access_purchase_request) {
            $this->session->set_flashdata('notice_error', lang('no_permissions'));
            redirect(get_uri("accounting/buy"));
        }

        $this->load->model('Purchase_order_m');
        $this->load->model('Goods_receipt_m');
        $this->load->model('Bom_materials_model');
    }

    function example()
    {
        $suppliers = $this->Bom_suppliers_model->getSupplierForGoodsReceipt();
        var_dump(arr($suppliers));
    }

    function index()
    {
        if ($this->input->post("datatable") == true) {
            jout(["data" => $this->Goods_receipt_m->indexDataSet()]);
            return;
        } elseif (isset($this->json->task)) {
            if ($this->json->task == "update_doc_status") jout($this->Goods_receipt_m->updateStatus());
            return;
        }

        redirect("/accounting/buy/purchase_order");
    }

    function addedit()
    {
        if (isset($this->json->task)) {
            if ($this->json->task == "save_doc") jout($this->Goods_receipt_m->saveDoc());
            if ($this->json->task == "get_po_list") jout($this->Goods_receipt_m->getPurchaseOrderBySupplierId());
            return;
        }

        $data = $this->Goods_receipt_m->getDoc($this->input->post("id"));

        $this->load->view('goods_receipt/addedit', $data);
    }

    function view()
    {
        if (isset($this->json->task)) {
            if ($this->json->task == "load_items")
                jout($this->Purchase_order_m->items());
            if ($this->json->task == "update_doc")
                jout($this->Purchase_order_m->updateDoc());
            if ($this->json->task == "delete_item")
                jout($this->Purchase_order_m->deleteItem());
            return;
        }

        if (empty($this->uri->segment(3))) {
            redirect(get_uri("/accounting/buy"));
            return;
        }

        $data = $this->Goods_receipt_m->getDoc($this->uri->segment(3));
        if ($data["status"] != "success") {
            redirect(get_uri("/accounting/buy"));
            return;
        }

        $data["created"] = $this->Users_m->getInfo($data["created_by"]);
        $data["supplier"] = $this->Bom_suppliers_model->getInfo($data["supplier_id"]);
        $data["supplier_contact"] = $this->Bom_suppliers_model->getContactInfo($data["supplier_id"]);
        $data["print_url"] = get_uri("goods_receipt/print/" . str_replace("=", "", base64_encode($data['doc_id'] . ':' . $data['doc_number'])));

        $this->template->rander("goods_receipt/view", $data);
    }

    function print()
    {
        $this->data["doc"] = $doc = $this->Purchase_order_m->getEdoc($this->uri->segment(3), null);
        if ($doc["status"] != "success")
            redirect("forbidden");

        $this->data["docmode"] = "private_print";
        $this->load->view('edocs/purchase_order', $this->data);
    }

    function delete_doc()
    {
        if ($this->input->post('undo') == true) {
            jout($this->Purchase_order_m->undoDoc());
            return;
        }

        jout($this->Purchase_order_m->deleteDoc());
    }

    function items()
    {
        jout($this->Purchase_order_m->items());
    }

    function item()
    {
        if (isset($this->json->task)) {
            if ($this->json->task == "save")
                jout($this->Purchase_order_m->saveItem());
            return;
        }

        $suggestion = [];

        if ($this->input->get("task") != null) {
            if ($this->input->get("task") == "suggest_products") {
                if ($this->input->get("type") == "3") {
                    $sprows = $this->Products_m->getRows();
                    if (!empty($sprows)) {
                        foreach ($sprows as $sprow) {
                            $suggestion[] = ["id" => $sprow->id, "text" => $sprow->title, "description" => $sprow->description, "unit" => $sprow->unit_type, "price" => $sprow->rate];
                        }
                    }
                } elseif ($this->input->get("type") == "1") {
                    $sprows = $this->Bom_materials_model->getRows();
                    if (!empty($sprows)) {
                        foreach ($sprows as $sprow) {
                            $suggestion[] = ["id" => $sprow->id, "text" => $sprow->name . ' - ' . $sprow->production_name, "description" => $sprow->description, "unit" => $sprow->unit, "price" => 0];
                        }
                    }
                } else {
                    $suggestion[] = array("id" => "+", "text" => "+ " . lang("create_new_item"));
                }
                jout($suggestion);
            }
            return;
        }

        $data = $this->Purchase_order_m->item();

        $this->load->view('purchase_order/item', $data);
    }

    function share()
    {
        if (isset($this->json->task)) {
            if ($this->json->task == "gen_sharekey")
                jout($this->Purchase_order_m->genShareKey());
            return;
        }

        $data = $this->Purchase_order_m->getDoc($this->input->post("doc_id"));
        $this->load->view('purchase_order/share', $data);
    }

}