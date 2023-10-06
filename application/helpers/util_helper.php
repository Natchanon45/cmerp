<?php
if (!function_exists('jout')){
    function jout($data){
        $ci = get_instance();
        $ci->output->set_content_type('application/json', "UTF-8")->set_output(json_encode($data));
    }   
}

if (!function_exists('convertDate')){
    function convertDate($date, $cm_format = false){
        if($date == "" || $date == null) return null;

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
        //$removedThousandSeparator = preg_replace('/(\.|,)(?=[0-9]{3,}$)/', '',  $stringWithCommaOrDot);
        return (float) str_replace(',', '.', $stringWithCommaOrDot);
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

if (!function_exists('numberToBahtEng')) {
    function numberToBahtEng($total_amount) {
        $firstPhases = [
            '01' => "One ",
            '02' => "Two ",
            '03' => "Three ",
            '04' => "Four ",
            '05' => "Five ",
            '06' => "Six ",
            '07' => "Seven ",
            '08' => "Eight ",
            '09' => "Nine ",
            '10' => "Ten ",
            '11' => "Eleven ",
            '12' => "Twelve ",
            '13' => "Thirteen ",
            '14' => "Fourteen ",
            '15' => "Fifteen ",
            '16' => "Sixteen ",
            '17' => "Seventeen ",
            '18' => "Eighteen ",
            '19' => "Nineteen "
        ];

        $secondPhases = [
            0 => "",
            1 => "One ",
            2 => "Two ",
            3 => "Three ",
            4 => "Four ",
            5 => "Five ",
            6 => "Six ",
            7 => "Seven ",
            8 => "Eight ",
            9 => "Nine ",
            10 => "Ten ",
            11 => "Eleven ",
            12 => "Twelve ",
            13 => "Thirteen ",
            14 => "Fourteen ",
            15 => "Fifteen ",
            16 => "Sixteen ",
            17 => "Seventeen ",
            18 => "Eighteen ",
            19 => "Nineteen "
        ];

        $thirdPhases = [
            0 => "",
            2 => "Twenty ",
            3 => "Thirty ",
            4 => "Forty ",
            5 => "Fifty ",
            6 => "Sixty ",
            7 => "Seventy ",
            8 => "Eighty ",
            9 => "Ninety "
        ];

        $fourthPhases = [
            "Hundred ",
            "Thousand ",
            "Million ",
            "Billion ",
            "Trillion ",
            "Quadrillion "
        ];

        $num = number_format($total_amount, 2, ".", ",");
        $num_arr = explode(".", $num);
        $wholenum = $num_arr[0];
        $decnum = $num_arr[1];
        $whole_arr = array_reverse(explode(",", $wholenum));
        
        $bahtEng = "";
        krsort($whole_arr);
        
        foreach ($whole_arr as $key => $i) {
            if ($i < 20) {
                $bahtEng .= $secondPhases[$i];
            } elseif ($i < 100) {
                $bahtEng .= $thirdPhases[substr($i, 0, 1)];
                $bahtEng .= $secondPhases[substr($i, 1, 1)];
            } else {
                $bahtEng .= $secondPhases[substr($i, 0, 1)] . $fourthPhases[0];
                $subStr = intval(substr($i, 1, 2));
                if ($subStr > 10 && $subStr < 20) {
                    $bahtEng .= $secondPhases[substr($i, 1, 2)];
                } else {
                    $bahtEng .= $thirdPhases[substr($i, 1, 1)];
                    $bahtEng .= $secondPhases[substr($i, 2, 1)];
                }
            }

            if ($key > 0) {
                $bahtEng .= $fourthPhases[$key];
            }
        }
        
        $bahtEng = $bahtEng . "Baht ";

        if ($decnum > 0) {
            $bahtEng .= "and ";
            if ($decnum < 20) {
                $bahtEng .= $firstPhases[$decnum];
            } elseif ($decnum < 100) {
                $bahtEng .= $thirdPhases[substr($decnum, 0, 1)];
                $bahtEng .= $secondPhases[substr($decnum, 1, 1)];
            }

            $bahtEng = $bahtEng . "Satang";
        } else {
            $bahtEng = $bahtEng . "Only";
        }

        return $bahtEng;
    }
}