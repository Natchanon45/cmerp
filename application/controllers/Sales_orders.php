<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Sales_orders extends MY_Controller {
    function __construct() {
        parent::__construct();
        
        if($this->Permission_m->accounting["sales_order"]["access"] != true){
            $this->session->set_flashdata('notice_error', lang('no_permissions'));
            redirect(get_uri("accounting/sell"));
        }

        $this->data["company_setting"] = $this->Settings_m->getCompany();
    }

    function index() {
        if($this->input->post("datatable") == true){
            jout(["data"=>$this->Sales_orders_m->indexDataSet()]);
            return;
        }elseif(isset($this->json->task)){
            if($this->json->task == "update_doc_status") jout($this->Sales_orders_m->updateStatus());
            return;    
        }

        redirect("/accounting/sell/sales-orders");
    }

    function addedit(){
        if(isset($this->json->task)){
            if($this->json->task == "save_doc") jout($this->Sales_orders_m->saveDoc());
            return;   
        }

        $data = $this->Sales_orders_m->getDoc($this->input->post("id"));

        $this->load->view('sales_orders/addedit', $data);
    }

    function view() {
        if(isset($this->json->task)){
            if($this->json->task == "load_items") jout($this->Sales_orders_m->items());
            if($this->json->task == "update_doc") jout($this->Sales_orders_m->updateDoc());
            if($this->json->task == "delete_item") jout($this->Sales_orders_m->deleteItem());
            return;
        }

        if(empty($this->uri->segment(3))){
            redirect(get_uri("/accounting/sell"));
            return;
        }

        $data = $this->Sales_orders_m->getDoc($this->uri->segment(3));
        if ($data["status"] != "success"){
            redirect(get_uri("/accounting/sell"));
            return;
        }

        $data["company_setting"] = $this->Settings_m->getCompany();
        $data["created"] = $this->Users_m->getInfo($data["created_by"]);
        $data["client"] = $this->Customers_m->getInfo($data["customer_id"]);
        $data["client_contact"] = $this->Customers_m->getContactInfo($data["client_id"]);
        $data["print_url"] = get_uri("sales-orders/print/".str_replace("=", "", base64_encode($data['doc_id'].':'.$data['doc_number'])));

        // var_dump(arr($data)); exit();
        $this->template->rander("sales_orders/view", $data);
    }

    function print(){
        $this->data["doc"] = $doc = $this->Sales_orders_m->getEdoc($this->uri->segment(3), null);
        if($doc["status"] != "success") redirect("forbidden");

        $this->data["docmode"] = "private_print";
        $this->load->view('edocs/sales_order', $this->data);
    }

    function delete_doc() {
        if($this->input->post('undo') == true){
            jout($this->Sales_orders_m->undoDoc());
            return;
        }

        jout($this->Sales_orders_m->deleteDoc());
    }

    function items(){
        jout($this->Sales_orders_m->items());
    }

    function item() {
        if(isset($this->json->task)){
            if($this->json->task == "choose_product") jout($this->Sales_orders_m->itemInfo($this->json->item_id));
            if($this->json->task == "save") jout($this->Sales_orders_m->saveItem());
            return;   
        }

        $suggestion = [];
        $fomulas = [];

        $task = $this->input->get("task");

        if($task != null){
            if($task == "suggest_products"){
                $sprows = $this->Products_m->getRows();
                if(!empty($sprows)){
                    foreach($sprows as $sprow){
                        $suggestion[] = ["id" => $sprow->id, "text" => $sprow->title, "description"=>$sprow->description, "unit"=>$sprow->unit_type, "price"=>$sprow->rate];
                    }
                }
                //$suggestion[] = array("id" => "+", "text" => "+ " . lang("create_new_item"));
                jout($suggestion);

            }
            
            return;
        }

        $data = $this->Sales_orders_m->item();

        $this->load->view('sales_orders/item', $data);
    }

    function share(){
        if(isset($this->json->task)){
            if($this->json->task == "gen_sharekey") jout($this->Sales_orders_m->genShareKey());
            return;   
        }
        
        $data = $this->Sales_orders_m->getDoc($this->input->post("doc_id"));
        $this->load->view('sales_orders/share', $data);
    }
}