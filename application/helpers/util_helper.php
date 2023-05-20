<?php
if (!function_exists('jout')){
    function jout($data){
        $ci = get_instance();
        $ci->output->set_content_type('application/json', "UTF-8")->set_output(json_encode($data));
    }   
}

if (!function_exists('convertDate')){
    function convertDate($date, $cm_format = false){
        if($date == "" || $date == null) return "";

        if($cm_format == false){
            list($dd, $mm, $yy) = explode("/", $date);
            return $yy."-".$mm."-".$dd;
        }

        $date = explode(" ", $date)[0];
        list($yy, $mm, $dd) = explode("-", $date);
        return $dd."/".$mm."/".$yy;
        
    }   
}

if (!function_exists('roundUp')){
    function roundUp($num, $digit=2){
        return ceil(floor($num * 1000) / 10) / 100;
    }   
}

if (!function_exists('getNumber')){
    function getNumber($number){
        $cleanString = preg_replace('/([^0-9\.,])/i', '', $number);
        $onlyNumbersString = preg_replace('/([^0-9])/i', '', $number);
        $separatorsCountToBeErased = strlen($cleanString) - strlen($onlyNumbersString) - 1;
        $stringWithCommaOrDot = preg_replace('/([,\.])/', '', $cleanString, $separatorsCountToBeErased);
        $removedThousandSeparator = preg_replace('/(\.|,)(?=[0-9]{3,}$)/', '',  $stringWithCommaOrDot);

        return (float) str_replace(',', '.', $removedThousandSeparator);
    }
}

if ( ! function_exists('number_format_drop_zero_decimals')){
    function number_format_drop_zero_decimals($number, $decimals){
        return ((floor($number) == round($number, $decimals)) ? number_format($number) : number_format($number, $decimals));
    }
}

if (!function_exists('numberToText')) {
    function numberToText($amount_number){
        function sub($number){
            $position_call = array("แสน", "หมื่น", "พัน", "ร้อย", "สิบ", "");
            $number_call = array("", "หนึ่ง", "สอง", "สาม", "สี่", "ห้า", "หก", "เจ็ด", "แปด", "เก้า");
            $number = $number + 0;
            $ret = "";
            if ($number == 0) return $ret;
            if ($number > 1000000)
            {
                $ret .= sub(intval($number / 1000000)) . "ล้าน";
                $number = intval(fmod($number, 1000000));
            }
            
            $divider = 100000;
            $pos = 0;
            while($number > 0)
            {
                $d = intval($number / $divider);
                $ret .= (($divider == 10) && ($d == 2)) ? "ยี่" : 
                    ((($divider == 10) && ($d == 1)) ? "" :
                    ((($divider == 1) && ($d == 1) && ($ret != "")) ? "เอ็ด" : $number_call[$d]));
                $ret .= ($d ? $position_call[$pos] : "");
                $number = $number % $divider;
                $divider = $divider / 10;
                $pos++;
            }
            return $ret;
        }

        if($amount_number == 0) return "ศูนย์บาทถ้วน";


        $amount_number = number_format($amount_number, 2, ".","");
        $pt = strpos($amount_number , ".");
        $number = $fraction = "";
        if ($pt === false){
            $number = $amount_number;
        }else{
            $number = substr($amount_number, 0, $pt);
            $fraction = substr($amount_number, $pt + 1);
        }
        
        $ret = "";
        $baht = sub($number);
        if ($baht != "")
            $ret .= $baht . "บาท";
        
        $satang = sub($fraction);
        if ($satang != "")
            $ret .=  $satang . "สตางค์";
        else 
            $ret .= "ถ้วน";
        return $ret;
    }
}