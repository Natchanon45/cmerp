<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Team extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->access_only_admin();
        $this->load->model('Db_model');
    }

    function index() {
        $this->template->rander("team/index");
    }

    /* load team add/edit modal */
    function modal_form() {
        validate_submitted_data(array(
            "id" => "numeric"
        ));

        $sql = "SELECT * FROM users";
        $keep = array();
        $keep = $this->Db_model->fetchAll($sql);
        
        $team_members = $this->Users_model->get_all_where(array("deleted" => 0, "user_type" => "staff"))->result();
        $members_dropdown = array();

        
        $view_data['team_edit'] = $keep;
        $view_data['members_dropdown'] = json_encode($members_dropdown);
        $view_data['members_check'] = $team_members;
        $view_data['model_info'] = $this->Team_model->get_one($this->input->post('id'));
        $this->load->view('team/modal_form', $view_data);
    }

    function modal_form_edit() {
        validate_submitted_data(array(
            "id" => "numeric"
        ));

        $db = $this->db;

        $view_data["urows"] = $db->select("*")
                    ->from("users")
                    ->where("deleted", 0)
                    ->where("user_type", "staff")
                    ->get()->result();



        $view_data["model_info"] = $db->select("*")
                    ->from("team")
                    ->where("id", $this->input->post('id'))
                    ->get()->row();


        $view_data["members"] = explode(",", $view_data["model_info"]->members);


        /*$sql = "SELECT * FROM users";
        $keep = array();
        $keep = $this->Db_model->fetchAll($sql);*/


        
        /*$team_members = $this->Users_model->get_all_where(array("deleted" => 0, "user_type" => "staff"))->result();
        $user = array();
        $member_name = array();
        foreach($team_members as $kt){
            $user[] =  $kt->id;
            $member_name[] = $kt->first_name.' '.$kt->last_name;
        }
        //var_dump($user);
                
        $view_data['member_name'] =  json_encode($member_name);
        $view_data['team_edit'] = $keep;
        $view_data['members_check'] = $team_members;
        $view_data['model_info'] = $this->Team_model->get_one($this->input->post('id'));*/
        $this->load->view('team/modal_form_edit', $view_data);
    }

    /* add/edit a team */

    function save() {
        validate_submitted_data(array(
            "id" => "numeric",
            "title" => "required",
            //"member" => "required"
        ));
        
        $id = $this->input->post('id');
        $member = "";
        
        if(!empty($this->input->post('memberids'))){
            $member = implode(",", $this->input->post('memberids'));    
        }
        
        $data = array(
            "title" => $this->input->post('title'),
            "members" => $member
        );

        $save_id = $this->Team_model->save($data, $id);        
                
        if ($save_id) {
            echo json_encode(array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, 'message' => lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }

    /* delete/undo a team */

    function delete() {
        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->input->post('id');
        if ($this->input->post('undo')) {
            if ($this->Team_model->delete($id, true)) {
                echo json_encode(array("success" => true, "data" => $this->_row_data($id), "message" => lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, lang('error_occurred')));
            }
        } else {
            if ($this->Team_model->delete($id)) {
                echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
            }
        }
    }

    /* list of team prepared for datatable */

    function list_data() {
        $list_data = $this->Team_model->get_details()->result();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    /* reaturn a row of team list table */

    private function _row_data($id) {
        $options = array("id" => $id);
        $data = $this->Team_model->get_details($options)->row();
        return $this->_make_row($data);
    }

    /* prepare a row of team list table */

    private function _make_row($data) {
        $t = 0;
        if($data->members != "") $t = count(explode(",", $data->members));

        $total_members = "<span class='label label-light w100'><i class='fa fa-users'></i> " .$t. "</span>";
        return array($data->title,
            modal_anchor(get_uri("team/members_list"), $total_members, array("title" => lang('team_members'), "data-post-members" => $data->members)),
            modal_anchor(get_uri("team/modal_form_edit"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_team'), "data-post-id" => $data->id))
            . js_anchor("<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete_team'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("team/delete"), "data-action" => "delete"))
        );
    }

    function members_list() {
        $view_data['team_members'] = $this->Users_model->get_team_members($this->input->post('members'))->result();
        // var_dump($view_data);
        $this->load->view('team/members_list', $view_data);
    }

}

/* End of file team.php */
/* Location: ./application/controllers/team.php */