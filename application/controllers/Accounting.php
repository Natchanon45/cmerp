<?php

if (!defined('BASEPATH'))
  exit('No direct script access allowed');

class Accounting extends MY_Controller {
  function __construct() {
    parent::__construct();
    //$this->access_only_team_members();
    $this->load->model("Permission_m");
  }

  function index() {

    // TESTING: Alert low quantity materials
    // $low_materials = $this->Bom_materials_model->get_low_quality_materials()->result();
    // if(sizeof($low_materials)){
    //   $noti_info = $this->Notification_settings_model->get_details(
    //     array('event' => 'stock_minimum_notice')
    //   )->row();
    //   if($noti_info && $noti_info->enable_email == 1 && !empty($noti_info->notify_to_team_members)){
    //     $users = $this->Users_model->get_details(
    //       array('string_ids' => $noti_info->notify_to_team_members)
    //     )->result();
    //     if(sizeof($users)){
    //       $emails = array_column($users, 'email');
          
    //     }
    //   }
    // }

    //$this->check_module_availability("module_stock");
      $sql = "
        SELECT 
          u.is_admin,
          u.role_id as rid,
          rp.*
        FROM users u
        INNER JOIN role_permission rp ON rp.role_id = u.role_id
        WHERE u.id = '".$_SESSION['user_id']."'
      ";

      $accounting = array(
        'orders','estimates','invoices','purchaserequests','payment_vouchers','receipt_taxinvoices','payment_vouchers'
      );
      $view_data['view_rows'] = [];
      foreach($this->dao->fetchAll($sql) as $k => $v){
        
        if(in_array($v->table_name,$accounting)){
          
          if($v->view_row != 0 && $v->is_admin == 0){
            $view_data['view_rows'][$v->table_name] = $v->view_row;            
          }else if($v->is_admin == 1){
            $view_data['view_rows'][$v->table_name] = $v->view_row; 
          }
          
        }
        // var_dump($v->table_name); 
      }
      
	//exit;
    $view_data['access_supplier'] = $this->bom_can_access_supplier();
    $view_data['access_material'] = $this->bom_can_access_material();
    $view_data['access_restock'] = $this->bom_can_access_restock();
    $view_data['access_calculator'] = $this->bom_can_access_calculator();
    $this->template->rander("accounting/index", $view_data);
  }
}