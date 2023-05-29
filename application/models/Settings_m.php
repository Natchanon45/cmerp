<?php

class Settings_m extends MY_Model {
    function __construct() {
        parent::__construct();
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
