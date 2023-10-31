<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Purchase_request extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        if (!$this->Permission_m->access_purchase_request) {
            $this->session->set_flashdata('notice_error', lang('no_permissions'));
            redirect(get_uri('accounting/buy'));
        }

        $this->load->model('Purchase_request_m');
        $this->load->model('Bom_materials_model');
    }

    function index()
    {
        if ($this->input->post('datatable') == true) {
            jout(["data" => $this->Purchase_request_m->indexDataSet()]);
            return;
        } elseif (isset($this->json->task)) {
            if ($this->json->task == "update_doc_status") jout($this->Purchase_request_m->updateStatus());
            return;
        }

        redirect("accounting/buy/purchase_request");
    }

    function dev2_IndexDataSet()
    {
        jout(['data' => $this->Purchase_request_m->indexDataSet()]);
    }

    function addedit()
    {
        if (isset($this->json->task)) {
            if ($this->json->task == 'save_doc') jout($this->Purchase_request_m->saveDoc());
            return;
        }

        $data = $this->Purchase_request_m->getDoc($this->input->post('id'));

        // var_dump(arr($data)); exit();
        $this->load->view('purchase_request/addedit', $data);
    }

    function view()
    {
        if (isset($this->json->task)) {
            if ($this->json->task == 'load_items') jout($this->Purchase_request_m->items());
            if ($this->json->task == 'update_doc') jout($this->Purchase_request_m->updateDoc());
            if ($this->json->task == 'delete_item') jout($this->Purchase_request_m->deleteItem());
            return;
        }

        if (empty($this->uri->segment(3))) {
            redirect(get_uri('accounting/buy'));
            return;
        }

        $data = $this->Purchase_request_m->getDoc($this->uri->segment(3));
        if ($data['status'] != 'success') {
            redirect(get_uri('accounting/buy'));
            return;
        }

        if ($data['project_id'] != 0) {
            $data['project_info'] = $this->Projects_model->get_one($data['project_id']);
        }

        $data['active_module'] = 'purchase_request';
        $data['modal_header'] = $this->Purchase_request_m->modal_header();
        $data['created'] = $this->Users_m->getInfo($data['created_by']);
        $data['supplier'] = $this->Bom_suppliers_model->getInfo($data['supplier_id']);
        $data['supplier_contact'] = $this->Bom_suppliers_model->getContactInfo($data['supplier_id']);
        $data['print_url'] = get_uri('purchase_request/print/' . str_replace('=', '', base64_encode($data['doc_id'] . ':' . $data['doc_number'])));

        // var_dump(arr($data)); exit();
        $this->template->rander('purchase_request/view', $data);
    }

    function print()
    {
        $this->data['doc'] = $doc = $this->Purchase_request_m->getEdoc($this->uri->segment(3), null);
        if ($doc['status'] != 'success') redirect('forbidden');

        $this->data['docmode'] = 'private_print';
        
        // var_dump(arr($this->data)); exit();
        $this->load->view('edocs/purchase_request', $this->data);
    }

    function delete_doc()
    {
        if ($this->input->post('undo') == true) {
            jout($this->Purchase_request_m->undoDoc());
            return;
        }

        jout($this->Purchase_request_m->deleteDoc());
    }

    function items()
    {
        jout($this->Purchase_request_m->items());
    }

    function item()
    {
        if (isset($this->json->task)) {
            if ($this->json->task == 'save') jout($this->Purchase_request_m->saveItem());
            return;
        }

        $suggestion = [];

        if ($this->input->get('task') != null) {
            if ($this->input->get('task') == 'suggest_products') {
                if ($this->input->get('type') == '3') {
                    $sprows = $this->Products_m->dev2_getItemsDropdownByKeyword();
                    if (isset($sprows) && !empty($sprows)) {
                        foreach ($sprows as $sprow) {
                            $suggestion[] = ['id' => $sprow->id, 'text' => $sprow->title, 'description' => $sprow->description, 'unit' => $sprow->unit_type, 'price' => $sprow->rate];
                        }
                    }
                } elseif ($this->input->get('type') == '1') {
                    $sprows = $this->Bom_materials_model->dev2_getMaterialsDropdownByKeyword();
                    if (isset($sprows) && !empty($sprows)) {
                        foreach ($sprows as $sprow) {
                            $suggestion[] = ['id' => $sprow->id, 'text' => $sprow->name . ' - ' . $sprow->production_name, 'description' => $sprow->description, 'unit' => $sprow->unit, 'price' => 0];
                        }
                    }
                } else {
                    $suggestion[] = array('id' => '+', 'text' => '+ ' . lang('create_new_item'));
                }
                jout($suggestion);
            }
            return;
        }

        $data = $this->Purchase_request_m->item();

        $this->load->view('purchase_request/item', $data);
    }

    function share()
    {
        if (isset($this->json->task)) {
            if ($this->json->task == 'gen_sharekey') jout($this->Purchase_request_m->genShareKey());
            return;
        }

        $data = $this->Purchase_request_m->getDoc($this->input->post('doc_id'));

        $this->load->view('purchase_request/share', $data);
    }

}