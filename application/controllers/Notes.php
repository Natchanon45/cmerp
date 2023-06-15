<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Notes extends MY_Controller {

    function __construct() {
        parent::__construct();

        //$this->access_only_team_members();
        if($this->Permission_m->access_note == false){
            redirect("forbidden");
            return;
        }
		
		$this->type = 'note';
		
		$this->load->model( 'Db_model' );
        $this->load->model('Note_types_model');


		
		$this->dao = $this->Db_model;
		$param['table_name'] = $this->type; 
    }

    protected function validate_access_to_note($note_info, $edit_mode = false) {
		
		return true;
        if ($note_info->is_public) {
            //it's a public note. visible to all available users
            if ($edit_mode) {
                //for edit mode, only creator and admin can access
                if ($this->login_user->id !== $note_info->created_by && !$this->login_user->is_admin) {
                    redirect("forbidden");
                }
            }
        } else {
            if ($note_info->client_id) {
                //this is a client's note. check client access permission
                $access_info = $this->get_access_info("client");
                if ($access_info->access_type != "all") {
                    redirect("forbidden");
                }
            } else if ($note_info->user_id) {
                //this is a user's note. check user's access permission.
                redirect("forbidden");
            } else {
                //this is a private note. only available to creator
                if ($this->login_user->id !== $note_info->created_by) {
                    redirect("forbidden");
                }
            }
        }
    }


    function modal_form() {

		$id = !empty( $this->input->post('id') )? $this->input->post('id'): NULL;
		
        $view_data['model_info'] = $this->Notes_model->get_one( $id );
		
        $view_data['project_id'] = $this->input->post('project_id') ? (int)$this->input->post('project_id') : (int)$view_data['model_info']->project_id;
        $view_data['client_id'] = $this->input->post('client_id') ? $this->input->post('client_id') : $view_data['model_info']->client_id;
        $view_data['user_id'] = $this->input->post('user_id') ? $this->input->post('user_id') : $view_data['model_info']->user_id;

        $view_data['label_suggestions'] = $this->make_labels_dropdown("note", $view_data['model_info']->labels, false);

        //$view_data['note_types_dropdown'] = array(0 => "-") + $this->Note_types_model->get_dropdown_list(array("title"), "id", []);

        $view_data['note_types_dropdown'] = array(null => "-");

        $note_types_dropdown = [];
        $note_types = $this->Note_types_model->get_dropdown_list(array("title"), "id");

        foreach ($note_types as $id => $name) {
            $view_data['note_types_dropdown'][$id] = $name;
        }

        $this->load->view('notes/modal_form', $view_data);
    }


    function delete() {
        if($this->login_user->is_admin != "1") redirect("/notes");
        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->input->post('id');

        $note_info = $this->Notes_model->get_one($id);
        $this->validate_access_to_note($note_info, true);

        if ($this->Notes_model->delete($id)) {
            //delete the files
            $file_path = get_setting("timeline_file_path");
            if ($note_info->files) {
                $files = unserialize($note_info->files);

                foreach ($files as $file) {
                    delete_app_files($file_path, array($file));
                }
            }

            echo json_encode(array("success" => true, 'message' => lang('record_deleted')));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('record_cannot_be_deleted')));
        }
    }


    private function _row_data($id, $project_id) {
        $data = null;
        if($project_id == 0){
            $options = array("id" => $id);
            $data = $this->Notes_model->get_details($options)->row();
        }else{
            $data = $this->Notes_model->get_note_in_project($project_id, $id)->row();
        }

        return $this->_make_row($data, $project_id);
        
        
    }

	function view() {
        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $model_info = $this->Notes_model->get_details(array("id" => $this->input->post('id')))->row();

        $this->validate_access_to_note($model_info);
        
        $view_data['model_info'] = $model_info;

        $this->load->view('notes/view', $view_data);
    }

    function view_note_in_project($project_id, $note_id) {
        validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $model_info = $this->Notes_model->get_note_in_project($project_id, $note_id)->row();
        
        $this->validate_access_to_note($model_info);
        
        $view_data['model_info'] = $model_info;

        $this->load->view('notes/view', $view_data);
    }

    function file_preview($id = "", $key = "") {
        if ($id) {
            $note_info = $this->Notes_model->get_one($id);
            $files = unserialize($note_info->files);
            $file = get_array_value($files, $key);

            $file_name = get_array_value($file, "file_name");
            $file_id = get_array_value($file, "file_id");
            $service_type = get_array_value($file, "service_type");

            $view_data["file_url"] = get_source_url_of_file($file, get_setting("timeline_file_path"));
            $view_data["is_image_file"] = is_image_file($file_name);
            $view_data["is_google_preview_available"] = is_google_preview_available($file_name);
            $view_data["is_viewable_video_file"] = is_viewable_video_file($file_name);
            $view_data["is_google_drive_file"] = ($file_id && $service_type == "google") ? true : false;

            $this->load->view("notes/file_preview", $view_data);
        } else {
            show_404();
        }
    }

    /* upload a file */

    function upload_file() {
        upload_file_to_temp();
    }

    /* check valid file for notes */

    function validate_notes_file() {
        return validate_post_file($this->input->post("file_name"));
    }
	
    function list_data($type = "", $project_id = 0) {
		$options = array();
        $result = array();

        if($type == "project"){
            $list_data = [];
            if($this->Notes_model->has_permission($project_id)) $list_data = $this->Notes_model->get_note_in_project($project_id)->result();
            
        }else{
            if($this->Permission_m->access_note == false){
                echo json_encode(["data"=>[]]);
                return;
            }elseif($this->Permission_m->access_note == "assigned_only"){
                $options['created_by'] =  $this->login_user->id;
            }elseif($this->Permission_m->access_note == "specific"){
                $options['note_type_ids'] =  "0,".$this->Permission_m->access_note_specific;
            }

            if( !empty( $_POST['label'] ) ) {
                $options['label'] = $_POST['label'];
            }

            if(!empty( $_POST['note_type_id'] )) {
                $options['note_type_id'] = $_POST['note_type_id'];
            }

            if(!empty( $_POST['created_by'] )) {
                $options['created_by'] = $_POST['created_by'];
            }

            $list_data = $this->Notes_model->get_details( $options )->result();
        }

        foreach( $list_data as $data ) {
            $result[] = $this->_make_row($data, $project_id);
        }

        echo json_encode(array("data" => $result));

    }

    function list_data_leads($id) // dev2 : use for notes in lead and client only
    {
        $sql = "SELECT notes.*, CONCAT(users.first_name, ' ', users.last_name) AS created_by_user_name, 
        (SELECT GROUP_CONCAT(labels.id, '--::--', labels.title, '--::--', labels.color) FROM labels WHERE FIND_IN_SET(labels.id, notes.labels)) AS labels_list 
        FROM notes LEFT JOIN users ON users.id = notes.created_by 
        WHERE notes.deleted = 0 AND notes.client_id = $id";

        $list_data = $this->Notes_model->sql_query($sql)->result();

        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, 0);
        }

        echo json_encode(array("data" => $result));
    }

    public function labeltest()
    {
        $labels_where["context"] = "note";
        $labels_where["user_id"] = $this->login_user->id;
        $view_data['label'] = $this->Labels_model->get_details($labels_where)->result();
        // $this->load->view("notes/note_label", $view_data);
    }
	
    private function _make_row($data, $project_id=0) {
		
        $public_icon = "";
        if ($data->is_public) {
            $public_icon = "<i class='fa fa-globe'></i> ";
        }

        $view_target = get_uri("notes/view/" . $data->id);
        if($project_id != "0") $view_target = get_uri("notes/view_note_in_project/" . $project_id."/".$data->id);

        $title = modal_anchor($view_target, $public_icon . $data->title, array("title" => lang('note'), "data-post-id" => $data->id));

        if(isset($data->labels_list)){
            if ($data->labels_list) {
                $note_labels = make_labels_view_data($data->labels_list, true);
                $title .= "<br />" . $note_labels;
            }    
        }

        $files_link = "";
        if ($data->files) {
            $files = unserialize($data->files);
            if (count($files)) {
                foreach ($files as $key => $value) {
                    $file_name = get_array_value($value, "file_name");
                    $link = " fa fa-" . get_file_icon(strtolower(pathinfo($file_name, PATHINFO_EXTENSION)));
                    $files_link .= js_anchor(" ", array('title' => "", "data-toggle" => "app-modal", "data-sidebar" => "0", "class" => "pull-left font-22 mr10 $link", "title" => remove_file_prefix($file_name), "data-url" => get_uri("notes/file_preview/" . $data->id . "/" . $key)));
                }
            }
        }

        //only creator and admin can edit/delete notes
        
        $actions = "";
		
		
		if ($this->login_user->is_admin == "1" || $this->Permission_m->update_note == true) {

            $actions = modal_anchor($view_target, "<i class='fa fa-bolt'></i>", array("class" => "edit", "title" => lang('note_details'), "data-modal-title" => lang("note"), "data-post-id" => $data->id));
		
			$buttons[] = modal_anchor( get_uri("notes/modal_form"), "<i class='fa fa-pencil'></i>", array("class" => "edit", "title" => lang('edit_note'), "data-post-id" => $data->id ));
		
            if($this->login_user->is_admin == "1"){
                $buttons[] = js_anchor( "<i class='fa fa-times fa-fw'></i>", array('title' => lang('delete_note'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("notes/delete"), "data-action" => "delete-confirmation" ));
            }
			
			//$buttons[] = '<a href="#" class="list-group-item" title="ส่งข้อความ" data-act="ajax-modal" data-title="ส่งข้อความ" data-action-url="'. base_url( ''. ex( 1 ) .'/form_mail/'. $data->id .'' ) .'"><i class="fa fa-envelope"></i></a>';

            $actions = implode( '', $buttons );

        }
			
                    
		$by = $data->created_by_user_name;


        return array(           
            format_to_relative_time($data->created_at),
            $title,
            $by,
            $files_link,
            $actions
        );
    }

    //load note list view
	
	
    function save() {
        if($this->login_user->is_admin != "1" || $this->Permission_m->update_note != true) redirect("/notes");
        validate_submitted_data(array(
            "id" => "numeric",
            "title" => "required",
            "project_id" => "numeric",
            "client_id" => "numeric",
            "user_id" => "numeric"
        ));

        $id = $this->input->post('id');

        $project_id = $this->input->post('project_id') ? $this->input->post('project_id') : 0;
        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "note");
        $new_files = unserialize($files_data);

        $data = array(
            "title" => $this->input->post('title'),
            "description" => $this->input->post('description'),
            "created_by" => $this->login_user->id,
            "labels" => $this->input->post('labels'),
            "project_id" => $this->input->post('project_id') ? $this->input->post('project_id') : 0,
            "note_type_id" => $this->input->post('note_type_id') ? $this->input->post('note_type_id') : 0,
            "client_id" => $this->input->post('client_id') ? $this->input->post('client_id') : 0,
            "user_id" => $this->input->post('user_id') ? $this->input->post('user_id') : 0,
            "is_public" => $this->input->post('is_public') ? $this->input->post('is_public') : 0
        );

        if ( $id ) {
            $note_info = $this->Notes_model->get_one($id);
            $timeline_file_path = get_setting("timeline_file_path");

            $new_files = update_saved_files($timeline_file_path, $note_info->files, $new_files);
        }

        $data["files"] = serialize($new_files);

        if ($id) {
            //saving existing note. check permission
            $note_info = $this->Notes_model->get_one($id);
            $data['created_by'] = $note_info->created_by;

            $this->validate_access_to_note($note_info, true);
        } else {
            $data['created_by'] = $this->login_user->id;
            $data['created_at'] = get_current_utc_time();
        }

        

        $data = clean_data($data);


        $save_id = $this->Notes_model->save($data, $id);

        if ($save_id) {
            echo json_encode(array("success" => true, "data" => $this->_row_data($save_id, $project_id), 'id' => $save_id, 'message' => lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => lang('error_occurred')));
        }
    }
	
	
	
    function form_mail( $note_id = 0 ) {
		
		$sql = "
			SELECT 
				* 
			FROM notes 
			WHERE id = ". $note_id ."
		";
		
		foreach( $this->dao->fetchAll( $sql ) as $kn => $vn ) {
			

			$view_data['note'] = $vn;
			//arr(  );
			$files = array();
			foreach( unserialize( $vn->files ) as $kf => $vf ) {
			 	
			//	arr( $vf );
				if( !file_exists( $_SERVER['DOCUMENT_ROOT'] . '/files/timeline_files/'. $vf['file_name'] ) ) {
					continue;
				}
				
				//C:\wamp64\www\Cosmatch-ERP\files\timeline_files
				
				// arr( $vf );
				
				$ex = explode( '.', $vf['file_name'] );
				
				$extension = $ex[count($ex)-1];
				$param['path'] = base_url( 'files/timeline_files/'. $vf['file_name'] );
				
				if( in_array( $extension, array( 'jpg', 'dssdsdds', 'ggdggg' ) ) ) {
	 
					$files[] = '<img src="'. getBase64( $param ) .'" data-dz-thumbnail="" class="upload-thumbnail-sm" alt="'. $vf['file_name'] .'" />';
				}
				else if( in_array( $extension, array( 'pdf', 'dssdsdds', 'ggdggg' ) ) ) {
	 
					$files[] = '<a target="blank_" href="'. $param['path'] .'" title=""  class="pull-left font-22 mr10  fa fa-file-pdf-o"> </a>';
				}
				
				else {
					
					$files[] = '<a target="blank_" href="'. $param['path'] .'" title=""  class="pull-left font-22 mr10  fa fa-file-excel-o"> </a>';
				}
				
			}
			
			$view_data['files'] = '<input type="hidden" name="note_id" value="'. $note_id .'" />';
			if( !empty( $files ) ) {
				
				$view_data['files'] .= 'ไฟล์แนบ  
				<div style="display: grid; grid-template-columns: auto auto auto auto auto; justify-content: space-evenly; align-items: center;">'. implode( '', $files ) .'</div>';
			}
			

			$view_data['toUserBox'] = $this->dao->toUserBlock();
			
			$this->load->view( 'notes/form_mail', $view_data );
		}
		

    }
	
	
    function index() {
        $this->check_module_availability("module_note");
		
		$this->buttonTop = array();
		
		$this->buttonTop[] = modal_anchor(get_uri("labels/modal_form"), "<i class='fa fa-tags'></i> " . lang('manage_labels'), array("class" => "btn btn-default", "title" => lang('manage_labels'), "data-post-type" => "note"));
			
		$this->buttonTop[] = modal_anchor(get_uri("notes/modal_form"), "<i class='fa fa-plus-circle'></i> " . lang('add_note'), array("class" => "btn btn-default", "title" => lang('add_note'), "data-post-project_id" => 0));

        //data-post-project_id="1"
        
        $this->labeltest();
        
        $labels_where["context"] = "note";
        $labels_where["user_id"] = $this->login_user->id;
		
		$sql = "
			SELECT
				id,
				title
			FROM labels WHERE context = 'note'
			AND deleted = 0
		";
		
        $view_data['label'] = $this->dao->fetchAll( $sql );

        // if($this->login_user->is_admin == 1 || $this->getRolePermission['view_row'] == "2"){
        if($this->login_user->is_admin == 1){
            $view_data['created_by'] = $this->dao->fetchAll("SELECT id, first_name, last_name FROM users WHERE user_type = 'staff' AND deleted = 0");
        }

		$this->buttonTop = implode( '', $this->buttonTop );
        $view_data["available_note_types"] = explode(",", $this->Permission_m->access_note_specific);

        $this->template->rander("notes/index",$view_data);
    }

}















