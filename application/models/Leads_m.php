<?php

class Leads_m extends CI_Model {

    function __construct() {}

    function indexHeader(){
    	$header = [];

        $lcfrows = $this->db->select("title")
		        			->from("leads_custom_field")
		        			->where("show_in_table", "Y")
                            ->where("show_in_lead", "Y")
		        			->where("status", "E")
		        			->order_by("sort", "ASC")
		        			->get()->result();

		if(empty($lcfrows)) return null;

		foreach($lcfrows as $lcfrow){
			$header[] = ["title"=>$lcfrow->title];
		}

		return ",".str_replace("]", "", str_replace("[", "", json_encode($header)));
    }

    function indexDataSet($id = null){
    	$status_ids = $status_info = [];

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

        $lcfrows = $this->db->select("code, show_in_table, show_in_lead, status")
                            ->from("leads_custom_field")
                            ->order_by("sort", "ASC")
                            ->get()->result();

        $custom_fields = null;

        if(!empty($lcfrows)){
            foreach($lcfrows as $lcfrow){
                $custom_fields[$lcfrow->code]["show_in_table"] = $lcfrow->show_in_table;
                $custom_fields[$lcfrow->code]["show_in_lead"] = $lcfrow->show_in_lead;
                $custom_fields[$lcfrow->code]["status"] = $lcfrow->status;
            }
        }

        $this->db->select("id, company_name, address, phone, lead_status_id, owner_id, cf1, cf2, cf3, cf4, cf5, cf6, cf7, cf8, cf9, cf10, cf11, cf12")
                    ->from("clients")
                    ->where("is_lead", 1)
                    ->where("deleted", 0)
                    ->where_in("lead_status_id", $status_ids);

        if($this->input->post("status")) $this->db->where("lead_status_id", $this->input->post("status"));
        if($this->input->post("source")) $this->db->where("lead_source_id", $this->input->post("source"));
        if($this->input->post("owner_id")) $this->db->where("owner_id", $this->input->post("owner_id"));

        if($id != null){
            $this->db->where("id", $id);
        }

        $lrows = $this->db->get()->result();

        $dataset = [];

        foreach($lrows as $lrow){
            $owner_info = $contact_info = "";
            $owner_image = $contact_image = "/assets/images/avatar.jpg";

            $u = $this->Users_m->getInfoByLeadId($lrow->id);
            if($u != null){
                $contact_info = $u["first_name"]." ".$u["last_name"];
                $image_data =  @unserialize($u["image"]);
                if($u["image"] === 'b:0;' || $image_data !== false){
                    $contact_image = $image_data["file_name"];
                }   
            }

            $u = $this->Users_m->getInfo($lrow->owner_id);
            if($u != null){
                $owner_info = $u["first_name"]." ".$u["last_name"];
                $image_data =  @unserialize($u["image"]);
                if($u["image"] === 'b:0;' || $image_data !== false){
                    $owner_image = "/files/profile_images/".unserialize($u["image"])["file_name"];
                }   
            }

            $data = [
                    "<a href='".get_uri("leads/view/" . $lrow->id)."'>".$lrow->company_name."</a>",
                    $lrow->address,
                    $lrow->phone,
                    "<a href='".get_uri("leads/contact_profile/". $lrow->id)."'><span class='avatar avatar-xs mr10'><img src='".$contact_image."'></span>".$contact_info."</a>",
                    "<a href='".get_uri("leads/team_members/view/". $lrow->id)."'><span class='avatar avatar-xs mr10'><img src='".$owner_image."'></span>".$owner_info."</a>",
                    "<a style='background-color: ".$status_info[$lrow->lead_status_id]['color']."' class='label' data-id='10' data-value='6' data-act='update-lead-status'>".$status_info[$lrow->lead_status_id]['title']."</a>"
                ];

            for($i = 1; $i <= 12; $i++){
                if($custom_fields["cf".$i]["show_in_table"] == "Y" && $custom_fields["cf".$i]["show_in_lead"] == "Y" && $custom_fields["cf".$i]["status"] == "E"){
                    $data[] = $lrow->{"cf".$i};
                }
            }

            $data[] = "<a class='edit' title='แก้ไขโอกาสในการขาย' data-post-id='".$lrow->id."' data-act='ajax-modal' data-title='แก้ไขโอกาสในการขาย' data-action-url='".get_uri("leads/modal_form")."'><i class='fa fa-pencil'></i></a><a title='ลบโอกาสในการขาย' class='delete' data-id='".$lrow->id."' data-action-url='".get_uri("leads/delete")."' data-action='delete-confirmation'><i class='fa fa-times fa-fw'></i></a>";

            

            $dataset[] = $data;
        }

        return $dataset;
    }

    function getRow($id){
        $row = $this->db->select("*")
                        ->from("clients")
                        ->where("id", $id)
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
            "vat_number" => $this->input->post('vat_number'),
            "currency_symbol" => $this->input->post('currency_symbol') ? $this->input->post('currency_symbol') : "",
            "currency" => $this->input->post('currency') ? $this->input->post('currency') : "",
            "is_lead" => 1,
            "lead_status_id" => $this->input->post('lead_status_id'),
            "lead_source_id" => $this->input->post('lead_source_id'),
            "owner_id" => $this->input->post('owner_id') ? $this->input->post('owner_id') : $this->login_user->id
        );

        for($i = 1; $i <= 12; $i++){
            $lcfrow = $this->db->select("code, status")
                            ->from("leads_custom_field")
                            ->where("code", "cf".$i)
                            ->get()->row();

            if($lcfrow->status == "E"){
                $data["cf".$i] = $this->input->post("custom_field_".$lcfrow->code);
            }else{
                $data["cf".$i] = NULL;
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

    function deleteRow($id) {
        $this->db->trans_begin();

        $this->db->where("id", $id);
        $this->db->update("clients", ["deleted"=>1]);

        $this->db->where("client_id", $id);
        $this->db->update("users", ["deleted"=>1]);

        $this->db->where("client_id", $id);
        $this->db->update("general_files", ["deleted"=>1]);

        if($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
        }

        $this->db->trans_commit();

        $gfrows = $this->db->select()
                            ->from("general_files")
                            ->where("deleted", 0)
                            ->where("client_id", $id)
                            ->get()->result();

        $file_path = get_general_file_path("client", $id);
        foreach ($gfrows as $gfrow) {
            delete_app_files($file_path, array(make_array_of_file($gfrow)));
        }

        return true;

    }

    function customFields(){
        $lcfrows = $this->db->select("*")
                            ->from("leads_custom_field")
                            ->where("show_in_lead", "Y")
                            ->where("status", "E")
                            ->order_by("sort", "ASC")
                            ->get()->result();

        return $lcfrows;
    }

    function getStatusTitle($status_id){
        $lsrow = $this->db->select("title")
                            ->from("lead_status")
                            ->where("id", $status_id)
                            ->get()->row();

        if(empty($lsrow)) return "";

        return $lsrow->title;
    }

    function kanban($options = array()) {
        $clients_table = $this->db->dbprefix('clients');
        $lead_source_table = $this->db->dbprefix('lead_source');
        $users_table = $this->db->dbprefix('users');
        $events_table = $this->db->dbprefix('events');
        $notes_table = $this->db->dbprefix('notes');
        $estimates_table = $this->db->dbprefix('estimates');
        $general_files_table = $this->db->dbprefix('general_files');
        $estimate_requests_table = $this->db->dbprefix('estimate_requests');

        $where = "";

        $status = get_array_value($options, "status");
        if ($status) {
            $where .= " AND $clients_table.lead_status_id='$status'";
        }

        $owner_id = get_array_value($options, "owner_id");
        if ($owner_id) {
            $where .= " AND $clients_table.owner_id='$owner_id'";
        }

        $source = get_array_value($options, "source");
        if ($source) {
            $where .= " AND $clients_table.lead_source_id='$source'";
        }

        $search = get_array_value($options, "search");
        if ($search) {
            $search = $this->db->escape_str($search);
            $where .= " AND $clients_table.company_name LIKE '%$search%'";
        }

        $users_where = "$users_table.client_id=$clients_table.id AND $users_table.deleted=0 AND $users_table.user_type='lead'";

        $this->db->query('SET SQL_BIG_SELECTS=1');

        $sql = "SELECT $clients_table.id, $clients_table.company_name, $clients_table.sort, IF($clients_table.sort!=0, $clients_table.sort, $clients_table.id) AS new_sort, $clients_table.lead_status_id, $clients_table.owner_id,
                (SELECT $users_table.image FROM $users_table WHERE $users_where AND $users_table.is_primary_contact=1) AS primary_contact_avatar,
                (SELECT COUNT($users_table.id) FROM $users_table WHERE $users_where) AS total_contacts_count,
                (SELECT COUNT($events_table.id) FROM $events_table WHERE $events_table.deleted=0 AND $events_table.client_id=$clients_table.id) AS total_events_count,
                (SELECT COUNT($notes_table.id) FROM $notes_table WHERE $notes_table.deleted=0 AND $notes_table.client_id=$clients_table.id) AS total_notes_count,
                (SELECT COUNT($estimates_table.id) FROM $estimates_table WHERE $estimates_table.deleted=0 AND $estimates_table.client_id=$clients_table.id) AS total_estimates_count,
                (SELECT COUNT($general_files_table.id) FROM $general_files_table WHERE $general_files_table.deleted=0 AND $general_files_table.client_id=$clients_table.id) AS total_files_count,
                (SELECT COUNT($estimate_requests_table.id) FROM $estimate_requests_table WHERE $estimate_requests_table.deleted=0 AND $estimate_requests_table.client_id=$clients_table.id) AS total_estimate_requests_count,
                $lead_source_table.title AS lead_source_title,
                CONCAT($users_table.first_name, ' ', $users_table.last_name) AS owner_name
        FROM $clients_table 
        LEFT JOIN $lead_source_table ON $clients_table.lead_source_id = $lead_source_table.id 
        LEFT JOIN $users_table ON $users_table.id = $clients_table.owner_id AND $users_table.deleted=0 AND $users_table.user_type='staff' 
        WHERE $clients_table.deleted=0 AND $clients_table.is_lead=1 $where 
        ORDER BY new_sort ASC";

        return $this->db->query($sql);
    }

    function changeToClient(){
        $db = $this->db;
        $lead_id = $this->input->post('lead_id');
        $company_name = $this->input->post('company_name');

        $lrow = $db->select("*")
                    ->from("clients")
                    ->where("id", $lead_id)
                    ->where("deleted", 0)
                    ->get()->row();


        if(empty($lsrow)){

        }

        $this->db->trans_begin();

        $data = array(
                "company_name" => $company_name,
                "address" => $this->input->post('address'),
                "city" => $this->input->post('city'),
                "state" => $this->input->post('state'),
                "zip" => $this->input->post('zip'),
                "country" => $this->input->post('country'),
                "created_date"=>$lsrow->created_date,
                "website" => $this->input->post('website'),
                "phone" => $this->input->post('phone'),
                "currency_symbol" => $lsrow->currency_symbol,
                "starred_by" => $lsrow->starred_by,
                "group_ids" => $this->input->post('group_ids') ? $this->input->post('group_ids') : "",
                "deleted" => 0,
                "lead_status_id" => $lsrow->lead_status_id,
                "owner_id" => $lsrow->owner_id,
                "created_by" => $this->input->post('created_by') ? $this->input->post('created_by') : $lsrow->owner_id,
                "sort" => $lsrow->sort,
                "lead_source_id" => $lsrow->lead_source_id,
                "last_lead_status" => $lsrow->last_lead_status,
                "client_migration_date"=>date("Y-m-d"),
                "vat_number" => $this->input->post('vat_number'),
                "currency" => $lsrow->currency,
                "disable_online_payment" => $lsrow->disable_online_payment
            );

        if ($this->login_user->is_admin) {
            $data["currency_symbol"] = $this->input->post('currency_symbol') ? $this->input->post('currency_symbol') : "";
            $data["currency"] = $this->input->post('currency') ? $this->input->post('currency') : "";
            $data["disable_online_payment"] = $this->input->post('disable_online_payment') ? $this->input->post('disable_online_payment') : 0;
        }

        //ตรวจสอบชื่อบริษัทซ้ำหรือไม่, เปลี่ยนเป็นตรวจสอบจากหมายเลขภาษี
        /*if (get_setting("disallow_duplicate_client_company_name") == "1" && $this->Clients_model->is_duplicate_company_name($company_name, $client_id)) {
                echo json_encode(array("success" => false, 'message' => lang("account_already_exists_for_your_company_name")));
                exit();
        }*/

        if ($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
        }

        //$this->db->trans_commit();



    }

    

}
