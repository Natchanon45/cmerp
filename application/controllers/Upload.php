<?php

class Upload extends CI_Controller {

        public function __construct()
        {
                parent::__construct();
                $this->load->helper(array('form', 'url'));
                $this->load->model('Db_model');
        }

        public function index()
        {
                $this->load->view('\team_members\files\modal_form_signature', array('error' => ' ' ));
        }

        public function do_upload()
        {       $user_id = $this->input->post('user_id');
                //var_dump($user_id);
                
                $config['upload_path']          = './assets/signature/';                
                $config['allowed_types']        = 'gif|jpg|png';
                $config['overwrite']          	= TRUE;
                // $config['max_size']             = 2048;
                // $config['max_width']            = 1024;
                // $config['max_height']           = 768;

                $this->load->library('upload', $config);
                
                if ( ! $this->upload->do_upload('userfile'))
                {       
                        $file_data = $this->upload->data();
                        // print_r($this->upload->display_errors());
                        // exit;
                        redirect('team_members/view/'.$user_id.'/general');
                }
                else
                {
                        $file_data = $this->upload->data();
                        $data = array('upload_data' => $this->upload->data());
                        $path = $config['upload_path'].$file_data['file_name'];
                        // var_dump($config['upload_path'].$file_data['file_name']);
                        $sql = "UPDATE `users` SET `signature` = '$path' WHERE `users`.`id` = $user_id";
                        $this->db->query( $sql );
                        //var_dump($query);
                        redirect('team_members/view/'.$user_id.'/general');
                }
        }
}
?>