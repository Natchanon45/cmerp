<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Quotations extends MY_Controller {
    function __construct() {
        parent::__construct();
        $this->json = json_decode(file_get_contents('php://input'));
    }

    function index() {
        if($this->uri->segment(3) == "source"){
            echo json_encode(["data"=>$this->Quotations_m->source()]);
            return;
        }

        
        $this->template->rander("quotations/index");
    }

    function doc(){
        if(isset($this->json->task)){
            if($this->json->task == "save") echo json_encode($this->Quotations_m->saveDoc());
            return;   
        }

        $data = $this->Quotations_m->doc($this->input->post("id"));

        $this->load->view( 'quotations/doc', $data);
    }

    function modal_form() {
        $project_id = $this->input->post('project_id');
        $client_id = $this->input->post('client_id');

        $data = $this->Quotations_m->doc($this->input->post('id'));
        $order_id = $this->input->post('order_id');

        /*if ($project_id) {
            $client_id = $this->Projects_model->get_one($project_id)->client_id;
            $data->client_id = $client_id;
        }

        $project_client_id = $client_id;
        if ($data["qrow"]->client_id) {
            $project_client_id = $data->client_id;
        }*/


        
        /*validate_submitted_data(array(
            "id" => "numeric",
            "client_id" => "numeric",
            "project_id" => "numeric"
        ));


        $project_id = $this->input->post('project_id');
        $client_id = $this->input->post('client_id');
        $model_info = $this->Estimates_model->get_one($this->input->post('id'));


        //check if order_id posted. if found order_id, so, we'll show the order info to copy the order 
        $order_id = $this->input->post('order_id');
        $view_data['order_id'] = $order_id;
        if ($order_id) {
            $order_info = $this->Orders_model->get_one($order_id);
            $now = get_my_local_time("Y-m-d");
            $model_info->estimate_date = $now;
            $model_info->valid_until = $now;
            $model_info->client_id = $order_info->client_id;
            $model_info->tax_id = $order_info->tax_id;
            $model_info->tax_id2 = $order_info->tax_id2;
        }

        if ($project_id) {
            $client_id = $this->Projects_model->get_one($project_id)->client_id;
            $model_info->client_id = $client_id;
        }

        $project_client_id = $client_id;
        if ($model_info->client_id) {
            $project_client_id = $model_info->client_id;
        }

        $view_data['model_info'] = $model_info;

        $estimate_request_id = $this->input->post('estimate_request_id');
        $view_data['estimate_request_id'] = $estimate_request_id;

        //make the drodown lists
        $view_data['taxes_dropdown'] = array("" => "-") + $this->Taxes_model->get_dropdown_list(array("title"));
        $view_data['clients_dropdown'] = $this->get_clients_and_leads_dropdown();*/

        //don't show clients dropdown for lead's estimate editing
        /*$client_info = $this->Clients_model->get_one($view_data['model_info']->client_id);
        if ($client_info->is_lead) {
            $client_id = $client_info->id;
        }*/

        /*$projects = $this->Projects_model->get_dropdown_list(array("title"), "id");
        $suggestion = array(array("id" => "", "text" => "-"));
        
        foreach ($projects as $key => $value) {
            $suggestion[] = array("id" => $key, "text" => $value);
        }

        $view_data['client_id'] = $client_id;

        //clone estimate data
        $is_clone = $this->input->post('is_clone');
        $view_data['is_clone'] = $is_clone;

        $view_data['projects_suggestion'] = $suggestion;

        $view_data['project_id'] = $project_id;

        $view_data["custom_fields"] = $this->Custom_fields_model->get_combined_details("estimates", $view_data['model_info']->id, $this->login_user->is_admin, $this->login_user->user_type )->result();
        
        
        
        
        ///$view_data["estimate_total_summary"] = $this->Estimates_model->get_estimate_total_summary($estimate_id );
        
        $pay_sp = !empty( $model_info->pay_sp )? $model_info->pay_sp: 100;
        $pay_type = !empty( $model_info->pay_type )? $model_info->pay_type: 'fixed_amount';
        
        
        $options = array( "percentage" => '%', 'time' => 'งวด', 'fixed_amount' => 'บาท' );
        
        $options2 = array( "percentage" => '%', 'fixed_amount' => 'บาท' );
        $keep = array();
        if (!empty($model_info->pay_sps)){
            
            $val_json = json_decode($model_info->pay_sps);
            foreach($val_json as $k => $v){ 
                $keep[$k] = $v; 
            }
            
            // arr($val_json);
        }

        for($i=1; $i<=6;$i++){
            $val = 0;
            $val2 = "percentage";
            if(isset($keep[$i])){
                $val = $keep[$i]->pay_sps;
                $val2 = $keep[$i]->pay_types;
            }
            $data[] = '
                            <label for="invoice_bill_date" class="col-md-3">
                            การชำระงวดที่ '.$i.'
                            </label>
                            <div class="col-md-9" style="margin-top: 5px;display: grid; grid-template-columns: auto auto;">

                                <input name="pay_sps['.$i.']" type="number" value="'. $val .'" class="form-control">

                                '. form_dropdown( "pay_types[".$i."]", $options2, array( $val2 ), "class='select2 tax-select2'" ) .'

                        </div>
            ';

        }

        
        if(!empty(json_decode($model_info->pay_sps))){
            $checked = 'checked';
        }else{
            $checked = '';
        }

        // var_dump($checked);
              
        */
        
        //$credit = !empty($model_info->credit) ? $model_info->credit : 0;
        //$deposit = !empty($model_info->deposit) ? $model_info->deposit : 0;
        /*$view_data["gogo"] = '
            <div class="form-group">
                <label for="estimate_date" class=" col-md-3">'. lang('estimate_date') .'</label>
                <div class="col-md-9">
                    '. form_input(array(
                        "id" => "estimate_date",
                        "name" => "estimate_date",
                        "value" => $model_info->estimate_date,
                        "class" => "form-control",
                        "placeholder" => lang('estimate_date'),
                        "autocomplete" => "off",
                        "data-rule-required" => true,
                        "data-msg-required" => lang("field_required"),
                    )) .'
                </div>
            </div>

            <div class="form-group">
                <label for="invoice_bill_date" class=" col-md-3">
                
                    การชำระ
                </label>
                <div class="col-md-9" style="display: grid;grid-template-columns: auto auto;">

                    <input style="" id="pay_sp" name="pay_sp" type="number" value="'. $pay_sp .'" style="text-align: right;" class="form-control">

                    '. form_dropdown( "pay_type", $options, array( $pay_type ), "id='pay_type' class='select2 tax-select2'" ) .'
                    <span id="timeChecker" style="color: red"></span>
                </div>
            </div>

                <div class="form-group" id="check_show" style="display: none;">
                    <label class=" col-md-3"></label>
                    <div class="col-md-9">                     
                        <input id="sp_deposit" type="checkbox" name="chckDes" style="padding-right: 5px;" '.$checked.'> <label for="sp_deposit"> แบ่งชำระแต่ละงวด</label>
                    </div>
                    
                </div>
                <div class="form-group" id="form_show" style="display: none;">                        
                    '.implode("",$data).'
                </div>
            

            <div class="form-group">
                <label for="invoice_due_date" class=" col-md-3">เครดิต</label>
                
                <div class="col-md-9" style="display: grid;grid-template-columns: auto auto;align-items: center; justify-items: center;justify-content: start;">

                    <input type="number" name="credit" value="'. $credit .'"   class="form-control" placeholder="กรอกเลข 0 หากชำระเงินสด" autocomplete="off" >
                     
                    
                    <div style="padding-left: 5px;"> วันหลังออกใบแจ้งหนี้</div>
                </div>
                
            </div>

            <div class="form-group">
                <label for="invoice_due_date" class=" col-md-3">วางเงินมัดจำ</label>
                
                <div class="col-md-9">

                    <input type="number" name="deposit" value="'. $deposit .'"   class="form-control" placeholder="กรอกเลข 0 หากไม่วางเงินมัดจำ" autocomplete="off" >                    
                </div>
                
            </div>           
            
        ';*/
        
        
        
        

        $this->load->view( 'quotations/modal_form', $data);
    }

    function view($docId) {

        $data = $this->Quotations_m->doc($docId);
        if ($data["status"] == "success") {
            $data["created"] = $this->Users_m->getInfo($data["qrow"]->created_by);
            $data["client"] = $this->Clients_m->getInfo($data["qrow"]->client_id);
            $data["client_contact"] = $this->Clients_m->getContactInfo($data["qrow"]->client_id);
            if($data["client"] != null) $data["client_contact"] = $this->Clients_m->getContactInfo($data["qrow"]->client_id);

            $this->template->rander("quotations/view", $data );
            return;
        }

        redirect('/quotations');
    }


    function delete() {
        if($this->input->post('undo') == true){
            echo json_encode($this->Quotations_m->undoDoc());
            return;
        }

        echo json_encode($this->Quotations_m->deleteDoc());
        return;
    }

    function jdoc(){
        echo json_encode($this->Estimate_m->jDoc());
    }

    function jitems(){
        echo $this->Estimate_m->jItems();
    }

    function save_item(){
        echo json_encode($this->Estimate_m->saveItem());
    }

    function jdelete_item(){
        echo json_encode($this->Estimate_m->deleteItem());
    }
	
	
}