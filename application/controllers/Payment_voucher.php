<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Payment_voucher extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        if ($this->Permission_m->access_purchase_request != true) {
            $this->session->set_flashdata('notice_error', lang('no_permissions'));
            redirect(get_uri("accounting/buy"));
        }

        $this->load->model('Purchase_request_m');
        $this->load->model('Purchase_order_m');
        $this->load->model('Payment_voucher_m');
        $this->load->model('Bom_materials_model');
    }

    function index()
    {
        if ($this->input->post("datatable") == true) {
            jout(["data" => $this->Payment_voucher_m->indexDataSet()]);
            return;
        } elseif (isset($this->json->task)) {
            if ($this->json->task == "update_doc_status")
                jout($this->Payment_voucher_m->updateStatus());
            return;
        }

        redirect("accounting/buy/payment_voucher");
    }

    function test()
    {
        jout(["data" => $this->Payment_voucher_m->indexDataSet()]);
        return;
    }

    function addedit()
    {
        if (isset($this->json->task)) {
            if ($this->json->task == "save_doc")
                jout($this->Payment_voucher_m->saveDoc());
            return;
        }

        $data = $this->Payment_voucher_m->getDoc($this->input->post("id"));

        $this->load->view('payment_voucher/addedit', $data);
    }

    function partial_payment_type()
    {
        if (isset($this->json->task)) {
            if ($this->json->task == "update_doc_status")
                jout($this->Payment_voucher_m->updateStatus());
            return;
        }

        $data = $this->Payment_voucher_m->getDoc($this->uri->segment(3));
        if ($data["status"] != "success")
            return;

        $this->load->view('payment_voucher/partial_payment_type', $data);
    }

    function view()
    {
        if (isset($this->json->task)) {
            if ($this->json->task == "load_items") {
                jout($this->Payment_voucher_m->items());
            }
                
            if ($this->json->task == "update_doc") {
                jout($this->Payment_voucher_m->updateDoc());
            }
                
            if ($this->json->task == "delete_item") {
                jout($this->Payment_voucher_m->deleteItem());
            }
            return;
        }

        if (empty($this->uri->segment(3))) {
            redirect(get_uri("accounting/buy"));
            return;
        }

        $data = $this->Payment_voucher_m->getDoc($this->uri->segment(3));
        if ($data["status"] != "success") {
            redirect(get_uri("accounting/buy"));
            return;
        }

        $data["created"] = $this->Users_m->getInfo($data["created_by"]);
        $data["supplier"] = $this->Suppliers_m->getInfo($data["supplier_id"]);
        $data["supplier_contact"] = $this->Suppliers_m->getContactInfo($data["supplier_id"]);
        $data["print_url"] = get_uri("payment_voucher/print/" . str_replace("=", "", base64_encode($data['doc_id'] . ':' . $data['doc_number'])));

        // var_dump(arr($data)); exit();
        $this->template->rander("payment_voucher/view", $data);
    }

    function print()
    {
        $this->data["doc"] = $doc = $this->Payment_voucher_m->getEdoc($this->uri->segment(3), null);
        if ($doc["status"] != "success")
            redirect("forbidden");

        $this->data["docmode"] = "private_print";
        $this->load->view('edocs/payment_voucher', $this->data);
    }

    function delete_doc()
    {
        if ($this->input->post('undo') == true) {
            jout($this->Payment_voucher_m->undoDoc());
            return;
        }

        jout($this->Payment_voucher_m->deleteDoc());
    }

    function items()
    {
        jout($this->Payment_voucher_m->items());
    }

    function item()
    {
        if (isset($this->json->task)) {
            if ($this->json->task == "save")
                jout($this->Payment_voucher_m->saveItem());
            return;
        }

        $suggestion = [];

        if ($this->input->get("task") != null) {
            if ($this->input->get("task") == "suggest_products") {
                $sprows = $this->Products_m->getRows();
                if (!empty($sprows)) {
                    foreach ($sprows as $sprow) {
                        $suggestion[] = ["id" => $sprow->id, "text" => $sprow->title, "description" => $sprow->description, "unit" => $sprow->unit_type, "price" => $sprow->rate];
                    }
                }
                $suggestion[] = array("id" => "+", "text" => "+ " . lang("create_new_item"));
                jout($suggestion);
            }
            return;
        }

        $data = $this->Payment_voucher_m->item();

        $this->load->view('payment_voucher/item', $data);
    }

    function share()
    {
        if (isset($this->json->task)) {
            if ($this->json->task == "gen_sharekey")
                jout($this->Payment_voucher_m->genShareKey());
            return;
        }

        $data = $this->Payment_voucher_m->getDoc($this->input->post("doc_id"));
        $this->load->view('payment_voucher/share', $data);
    }
}