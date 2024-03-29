<?php
class Clients_m extends MY_Model {

    function __construct() {
        parent::__construct();
    }

    function indexHeader(){
        $header = [];

        $cfrows = $this->db->select("title")
                            ->from("leads_custom_field")
                            ->where("show_in_table", "Y")
                            ->where("show_in_client", "Y")
                            ->where("status", "E")
                            ->order_by("sort", "ASC")
                            ->get()->result();

        if(empty($cfrows)) return null;

        foreach($cfrows as $cfrows){
            $header[] = ["title"=>$cfrows->title];
        }

        return ",".str_replace("]", "", str_replace("[", "", json_encode($header)));
    }

    function indexDataSet($id = null){
        $status_ids = $status_info = [];
        $permissions = $this->login_user->permissions;//จัดการเรื่อง permission ชั่วคราวโดยดึงสิทธมาตรงๆ

        $lsrows = $this->db->select("id, title, color")
                            ->from("lead_status")
                            ->where("deleted", 0)
                            ->get()->result();
        if(!empty($lsrows)){
            foreach($lsrows as $lsrow){
                $status_ids[] = $lsrow->id;
                $status_info[$lsrow->id]["title"] = $lsrow->title;
                $status_info[$lsrow->id]["color"] = $lsrow->color;
            }
        }

        $this->db->select("id, company_name, address, phone, lead_status_id, group_ids, owner_id, cf1, cf2, cf3, cf4, cf5, cf6, cf7, cf8, cf9, cf10, cf11, cf12")
                    ->from("clients")
                    ->where("is_lead", 0)
                    ->where("deleted", 0);

        if($id != null) {
            $this->db->where("id", $id);
        }else{
            if($this->input->post("created_by")) $this->db->where("created_by", $this->input->post("created_by"));
            if($this->input->post("group_id")) $this->db->where("FIND_IN_SET('".$this->input->post("group_id")."', group_ids)");    
        }


        if($this->login_user->is_admin != 1){
            if($permissions["client"] == "own"){
                $this->db->where("owner_id",  $this->login_user->id);
            }
        }

        $crows = $this->db->get()->result();

        $dataset = [];

        foreach($crows as $crow){
            $owner_info = $contact_info = "";
            $owner_image = $contact_image = "/assets/images/avatar.jpg";

            $u = $this->Users_m->getInfoByLeadId($crow->id);
            if($u != null){
                $contact_info = $u["first_name"]." ".$u["last_name"];
                $image_data =  @unserialize($u["image"]);
                if($u["image"] === 'b:0;' || $image_data !== false){
                    $contact_image = $image_data["file_name"];
                }   
            }

            $u = $this->Users_m->getInfo($crow->owner_id);
            if($u != null){
                $owner_info = $u["first_name"]." ".$u["last_name"];
                $image_data =  @unserialize($u["image"]);
                if($u["image"] === 'b:0;' || $image_data !== false){
                    $owner_image = "/files/profile_images/".unserialize($u["image"])["file_name"];
                }   
            }


            $group_ids = trim($crow->group_ids);
            $group_list = "";
            if($group_ids != ""){
                $group_titles = [];
                $cgrows = $this->db->select("id, title")
                                    ->from("client_groups")
                                    ->where("deleted", 0)
                                    ->get()->result();

                if(!empty($cgrows)){
                    foreach($cgrows as $cgrow){
                        $group_titles[$cgrow->id] = $cgrow->title;
                    }
                }

                $group_ids = explode(",", $crow->group_ids);

                if(!empty($group_ids)){
                    $group_list = "<ul class='pl15'>";
                    foreach ($group_ids as $group_id) {
                        if(isset($group_titles[$group_id])) $group_list .= "<li>".$group_titles[$group_id]."</li>";
                        else $group_list .= "<li>".$group_id."</li>";
                    }
                    $group_list .= "</ul>";
                }
            }

            $data = [
                    $crow->id,
                    "<a href='".get_uri("clients/view/" . $crow->id)."'>".$crow->company_name."</a>",
                    $this->getPrimaryContactName($crow->id),
                    $group_list,
                    $this->getTotalProjects($crow->id),
                    number_format($this->Clients_m->getTotalInvoiceAmounts($crow->id), 2),
                    number_format($this->Clients_m->getTotalPaymentReceives($crow->id, true), 2),
                    number_format($this->Clients_m->getTotalInvoiceAmounts($crow->id, true), 2),
                    isset($crow->currency) ? lang($data->currency) : lang("THB")
                ];


            $cfrows = $this->db->select("code")
                                ->from("leads_custom_field")
                                ->where("show_in_table", "Y")
                                ->where("show_in_client", "Y")
                                ->where("status", "E")
                                ->order_by("sort", "ASC")
                                ->get()->result();

            if(!empty($cfrows)){
                foreach($cfrows as $cfrow){
                    $data[] = mb_strimwidth($crow->{$cfrow->code}, 0, 50, "...");
                }
            }

            if($this->login_user->is_admin == 1){
                $data[] = "<a class='edit' title='แก้ไขลูกค้า' data-post-id='".$crow->id."' data-act='ajax-modal' data-title='แก้ไขลูกค้า' data-action-url='".get_uri("clients/modal_form")."'><i class='fa fa-pencil'></i></a><a title='ลบลูกค้า' class='delete' data-id='".$crow->id."' data-action-url='".get_uri("clients/delete")."' data-action='delete-confirmation'><i class='fa fa-times fa-fw'></i></a>";
            }else{
                if($permissions["client"] == "read_only"){
                    $data[] = "";
                }else{
                    $data[] = "<a class='edit' title='แก้ไขลูกค้า' data-post-id='".$crow->id."' data-act='ajax-modal' data-title='แก้ไขลูกค้า' data-action-url='".get_uri("clients/modal_form")."'><i class='fa fa-pencil'></i></a><a title='ลบลูกค้า' class='delete' data-id='".$crow->id."' data-action-url='".get_uri("clients/delete")."' data-action='delete-confirmation'><i class='fa fa-times fa-fw'></i></a>";
                }
            }

            

            $dataset[] = $data;
        }

        return $dataset;
    }

    function getRows(){
        $db = $this->db;

        $crows = $db->select("*")
                    ->from("clients")
                    ->where("deleted", 0)
                    ->where("is_lead", 0)
                    ->get()->result();

        return $crows;
    }

    function getRow($id){
        $row = $this->db->select("*")
                        ->from("clients")
                        ->where("id", $id)
                        ->where("is_lead", 0)
                        ->get()->row();
        return $row;
    }

    function saveRow(){
        $id = $this->input->post('id');
        $vat_number = trim($this->input->post('vat_number'));
      
        $data = array(
            "company_name" => $this->input->post('company_name'),
            "address" => $this->input->post('address'),
            "city" => $this->input->post('city'),
            "state" => $this->input->post('state'),
            "zip" => $this->input->post('zip'),
            "country" => $this->input->post('country'),
            "phone" => $this->input->post('phone'),
            "website" => $this->input->post('website'),
            "vat_number" => $vat_number,
            "currency_symbol" => $this->input->post('currency_symbol') ? $this->input->post('currency_symbol') : "",
            "currency" => $this->input->post('currency') ? $this->input->post('currency') : "",
            "is_lead" => 0,
            "lead_status_id" => $this->input->post('lead_status_id') ? $this->input->post('lead_status_id') : '0',
            "lead_source_id" => $this->input->post('lead_source_id') ? $this->input->post('lead_source_id') : '0',
            "owner_id" => $this->input->post('owner_id') ? $this->input->post('owner_id') : $this->login_user->id
        );

        if ($this->login_user->user_type === "staff") {
            $data["group_ids"] = $this->input->post('group_ids') ? $this->input->post('group_ids') : "";
        }

        $cfrows = $this->db->select("code")
                            ->from("leads_custom_field")
                            ->where("show_in_client", "Y")
                            ->where("status", "E")
                            ->get()->result();

        if(!empty($cfrows)){
            foreach($cfrows as $cfrow){
                $data[$cfrow->code] = $this->input->post("custom_field_".$cfrow->code);
            }
        }

        if ($id != false) {
            if($vat_number != ""){
                $this->db->where("deleted", 0);
                $this->db->where("id !=", $id);
                $this->db->where("vat_number", $vat_number);
                if($this->db->count_all_results("clients") > 0) return ["success"=>false, "message"=>"ไม่สามารถทำรายการได้ เนื่องจากหมายเลขภาษี ".$vat_number." ได้ถูกลงทะเบียนไว้แล้ว"];
            }

            $this->db->where("id", $id);
            $this->db->update("clients", $data);

        }else{
            if($vat_number != ""){
                $this->db->where("deleted", 0);
                $this->db->where("vat_number", $vat_number);
                if($this->db->count_all_results("clients") > 0) return ["success"=>false, "message"=>"ไม่สามารถทำรายการได้ เนื่องจากหมายเลขภาษี ".$vat_number." ได้ถูกลงทะเบียนไว้แล้ว"];
            }

            $data["created_date"] = date("Y-m-d");
            $data["created_by"] = $this->login_user->id;

            $this->db->insert("clients", $data);

            $id = $this->db->insert_id();
        }

        return ["success"=>true, "id"=>$id];
    }

    function customFields(){
        $cfrows = $this->db->select("*")
                            ->from("leads_custom_field")
                            ->where("show_in_client", "Y")
                            ->where("status", "E")
                            ->order_by("sort", "ASC")
                            ->get()->result();

        return $cfrows;
    }

    function getPrimaryContactName($client_id){
        $pcnrow = $this->db->select("first_name, last_name")
                            ->from("users")
                            ->where("client_id", $client_id)
                            ->where("is_primary_contact", 1)
                            ->where("deleted", 0)
                            ->get()->row();

        if(empty($pcnrow)) return null;

        return $pcnrow->first_name." ".$pcnrow->last_name;

    }

    function getCompanyName($client_id){
        $db = $this->db;

        $crow = $db->select("company_name")
                        ->from("clients")
                        ->where("id", $client_id)
                        ->get()->row();

        if(empty($crow)) return null;

        return $crow->company_name;
    }

    function getTotalProjects($client_id){
        $this->db->where("deleted", 0);
        $this->db->where("client_id", $client_id);
        return $this->db->count_all_results("projects");
    }

    function getTotalInvoiceAmounts($client_id, $overdue_checking = false){
        $company_setting = $this->Settings_m->getCompany();

        $this->db->select("SUM(total) AS TOTAL_INVOICE_AMOUNTS")
                                            ->from("invoice")
                                            ->where("billing_type", $company_setting["company_billing_type"])
                                            ->where("deleted", 0)
                                            ->where("client_id", $client_id)
                                            ->where_in("status", ["O", "P"]);

        if($overdue_checking == true) $this->db->where("DATE(due_date) >", date("Y-m-d"));

        $total_invoice_amounts = $this->db->get()->row()->TOTAL_INVOICE_AMOUNTS;

        if($total_invoice_amounts == null) $total_invoice_amounts = 0;

        return $total_invoice_amounts - $this->getTotalPaymentReceives($client_id, false, $overdue_checking);
    }

    function getTotalPaymentReceives($client_id, $include_on_cash = false, $overdue_checking = false){
        $company_setting = $this->Settings_m->getCompany();
        $total_payment_amount = 0;

        $this->db->select("SUM(money_payment_receive) AS TOTAL_PAYMENT_RECEIVES")
                    ->from("invoice")
                    ->join("invoice_payment", "invoice.id = invoice_payment.invoice_id")
                    ->where("billing_type", $company_setting["company_billing_type"])
                    ->where("deleted", 0)
                    ->where_in("status", ["O", "P"])
                    ->where("client_id", $client_id);

        if($overdue_checking == true) $this->db->where("DATE(due_date) >", date("Y-m-d"));
        $total_payment_receives = $this->db->get()->row()->TOTAL_PAYMENT_RECEIVES;

        if($total_payment_receives == null) $total_payment_receives = 0;

        if($include_on_cash == true){
            $total_payment_amount = $this->db->select("SUM(payment_amount) AS TOTAL_PAYMENT_AMOUNT")
                                            ->from("receipt")
                                            ->where("billing_type", $company_setting["company_billing_type"])
                                            ->where("deleted", 0)
                                            ->where("client_id", $client_id)
                                            ->where("invoice_payment_id IS NULL")
                                            ->get()->row()->TOTAL_PAYMENT_AMOUNT;

            if($total_payment_amount == null) $total_payment_amount = 0;
        }

        return $total_payment_receives + $total_payment_amount;
    }
}
