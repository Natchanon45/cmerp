<?php

class Settings_m extends MY_Model {
    function __construct() {
        parent::__construct();
    }

    function getCompany(){
        $company_setting = [
                                "company_name"=>"",
                                "company_vat_number"=>"",
                                "company_vat_registered"=>"Y",
                                "company_billing_type"=>"",
                                "company_stock_type"=>"",
                                "company_address"=>"",
                                "company_phone"=>"",
                                "company_email"=>"",
                                "company_website"=>"",
                                "company_stamp"=>null
                            ];


        foreach($company_setting as $i => $v){
            $srow = $this->db->select("setting_value")
                                ->from("settings")
                                ->where("setting_name", $i)
                                ->where("type", "app")
                                ->where("deleted", 0)
                                ->get()->row();

            if(!empty($srow)){
                if($i == "company_stamp"){
                    if(@unserialize($srow->setting_value) != false){
                        $company_setting[$i] = "files/system/".unserialize($srow->setting_value)["file_name"];
                    }else{
                        $company_setting[$i] = null;
                    }
                }else{
                    $company_setting[$i] = $srow->setting_value;
                }
            }
        }

        return $company_setting;
    }

    function getDecimalPlacesNumber(){
        $dpnrow = $this->db->select("setting_value")
                            ->from("settings")
                            ->where("setting_name", "no_of_decimals")
                            ->get()->row();
                            
        if(empty($dpnrow)) return 2;
        return $dpnrow->setting_value;
    }
}
