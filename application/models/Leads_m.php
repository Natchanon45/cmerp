<?php

class Leads_m extends CI_Model {

    function __construct() {}

    function indexHeader(){
    	$header = [];

        $lcfrows = $this->db->select("title")
		        			->from("leads_custom_field")
		        			->where("show_in_table", "Y")
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

        $lcfrows = $this->db->select("code, show_in_table, status")
                            ->from("leads_custom_field")
                            ->order_by("sort", "ASC")
                            ->get()->result();

        $custom_fields = null;

        if(!empty($lcfrows)){
            foreach($lcfrows as $lcfrow){
                $custom_fields[$lcfrow->code]["show_in_table"] = $lcfrow->show_in_table;
                $custom_fields[$lcfrow->code]["status"] = $lcfrow->status;
            }
        }

        $this->db->select("id, company_name, address, phone, lead_status_id, owner_id, cf1, cf2, cf3, cf4, cf5, cf6, cf7, cf8, cf9, cf10, cf11, cf12")
                    ->from("leads")
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
                if($custom_fields["cf".$i]["show_in_table"] == "Y" && $custom_fields["cf".$i]["status"] == "E"){
                    $data[] = $lrow->{"cf".$i};
                }
            }

            $data[] = "<a class='edit' title='แก้ไขโอกาสในการขาย' data-post-id='".$lrow->id."' data-act='ajax-modal' data-title='แก้ไขโอกาสในการขาย' data-action-url='".get_uri("leads/modal_form")."'><i class='fa fa-pencil'></i></a><a title='ลบโอกาสในการขาย' class='delete' data-id='".$lrow->id."' data-action-url='".get_uri("leads/delete")."' data-action='delete-confirmation'><i class='fa fa-times fa-fw'></i></a>";

            

            $dataset[] = $data;
        }

        return $dataset;
    }

    function row($id){
        $row = $this->db->select("*")
                        ->from("leads")
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
                if($this->db->count_all_results("leads") > 0) return ["success"=>false, "message"=>"ไม่สามารถทำรายการได้ เนื่องจากหมายเลขภาษี ".$vat_number." ได้ถูกลงทะเบียนไว้แล้ว"];
            }

            $this->db->where("id", $id);
            $this->db->update("leads", $data);

        }else{
            if($vat_number != ""){
                $this->db->where("deleted", 0);
                $this->db->where("vat_number", $vat_number);
                if($this->db->count_all_results("leads") > 0) return ["success"=>false, "message"=>"ไม่สามารถทำรายการได้ เนื่องจากหมายเลขภาษี ".$vat_number." ได้ถูกลงทะเบียนไว้แล้ว"];
            }

            $data["created_date"] = date("Y-m-d");
            $data["created_by"] = $this->login_user->id;

            $this->db->insert("leads", $data);

            $id = $this->db->insert_id();
        }

        return ["success"=>true, "id"=>$id];
        
    }

    function deleteRow($id) {
        $this->db->trans_begin();

        $this->db->where("id", $id);
        $this->db->update("leads", ["deleted"=>1]);

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
                            ->where("status", "E")
                            ->order_by("sort", "ASC")
                            ->get()->result();

        return $lcfrows;
    }

    

}
