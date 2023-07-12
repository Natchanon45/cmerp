<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Purchase_order extends MY_Controller {
    function __construct() {
        parent::__construct();
        
        if($this->Permission_m->accounting["quotation"]["access"] != true){
            $this->session->set_flashdata('notice_error', lang('no_permissions'));
            redirect(get_uri("accounting/buy"));
        }

        $this->load->model('Purchase_order_m');
    }

    function index() {
        if($this->input->post("datatable") == true){
            jout(["data"=>$this->Purchase_order_m->indexDataSet()]);
            return;
        }elseif(isset($this->json->task)){
            if($this->json->task == "update_doc_status") jout($this->Purchase_order_m->updateStatus());
            if($this->json->task == "get_partial_billing_note") jout($this->Purchase_order_m->getTotalDocPartialBillingNote());
            return;    
        }

        redirect("/accounting/sell/purchase_order");
    }

    function addedit(){
        if(isset($this->json->task)){
            if($this->json->task == "save_doc") jout($this->Purchase_order_m->saveDoc());
            return;   
        }

        $data = $this->Purchase_order_m->getDoc($this->input->post("id"));

        $this->load->view( 'purchase_order/addedit', $data);
    }

    function partial_payment_type(){
        if(isset($this->json->task)){
            if($this->json->task == "update_doc_status") jout($this->Purchase_order_m->updateStatus());
            return;   
        }

        $data = $this->Purchase_order_m->getDoc($this->uri->segment(3));
        if($data["status"] != "success") return;

        $this->load->view( 'purchase_order/partial_payment_type', $data);
    }

    function view() {
        if(isset($this->json->task)){
            if($this->json->task == "load_items") jout($this->Purchase_order_m->items());
            if($this->json->task == "update_doc") jout($this->Purchase_order_m->updateDoc());
            if($this->json->task == "delete_item") jout($this->Purchase_order_m->deleteItem());
            return;
        }

        if(empty($this->uri->segment(3))){
            redirect(get_uri("/accounting/sell"));
            return;
        }

        $data = $this->Purchase_order_m->getDoc($this->uri->segment(3));
        if ($data["status"] != "success"){
            redirect(get_uri("/accounting/sell"));
            return;
        }

        $data["created"] = $this->Users_m->getInfo($data["created_by"]);
        $data["client"] = $this->Customers_m->getInfo($data["customer_id"]);
        $data["client_contact"] = $this->Customers_m->getContactInfo($data["client_id"]);
        $data["print_url"] = get_uri("purchase_order/print/".str_replace("=", "", base64_encode($data['doc_id'].':'.$data['doc_number'])));

        // var_dump(arr($data)); exit();
        $this->template->rander("purchase_order/view", $data);
    }

    function print(){
        $this->data["doc"] = $doc = $this->Purchase_order_m->getEdoc($this->uri->segment(3), null);
        if($doc["status"] != "success") redirect("forbidden");

        $this->data["docmode"] = "private_print";
        $this->load->view('edocs/purchase_order', $this->data);
    }

    function delete_doc() {
        if($this->input->post('undo') == true){
            jout($this->Purchase_order_m->undoDoc());
            return;
        }

        jout($this->Purchase_order_m->deleteDoc());
    }

    function items(){
        jout($this->Purchase_order_m->items());
    }

    function item() {
        if(isset($this->json->task)){
            if($this->json->task == "save") jout($this->Purchase_order_m->saveItem());
            return;   
        }

        $suggestion = [];

        if($this->input->get("task") != null){
            if($this->input->get("task") == "suggest_products"){
                $sprows = $this->Products_m->getRows();
                if(!empty($sprows)){
                    foreach($sprows as $sprow){
                        $suggestion[] = ["id" => $sprow->id, "text" => $sprow->title, "description"=>$sprow->description, "unit"=>$sprow->unit_type, "price"=>$sprow->rate];
                    }
                }
                $suggestion[] = array("id" => "+", "text" => "+ " . lang("create_new_item"));
                jout($suggestion);
            }
            return;
        }

        $data = $this->Purchase_order_m->item();

        $this->load->view('purchase_order/item', $data);
    }

    function share(){
        if(isset($this->json->task)){
            if($this->json->task == "gen_sharekey") jout($this->Purchase_order_m->genShareKey());
            return;   
        }
        
        $data = $this->Purchase_order_m->getDoc($this->input->post("doc_id"));
        $this->load->view('purchase_order/share', $data);
    }
}