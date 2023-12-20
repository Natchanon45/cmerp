<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');


use Dompdf\Dompdf;


class Pdf_export extends CI_Controller
{


    public function __construct()
    {

        parent::__construct();

        // Load model
        $this->load->model('Db_model');
        $this->load->model('Estimates_model');
        $this->load->model('Payment_vouchers_model');
        $this->load->model('Invoices_model');
        $this->load->model('Provetable_model');
        $this->load->model('Purchaserequests_model');
        $this->load->model('Orders_model');
        $this->load->model('Receipts_model');
        $this->load->model('Receipt_taxinvoices_model');
        $this->load->model('Materialrequests_model');
        $this->load->model('Mr_items_model');
        $this->load->model('Deliverys_model');
    }

    public function ReadNumber($number)
    {
        $position_call = array("แสน", "หมื่น", "พัน", "ร้อย", "สิบ", "");
        $number_call = array("", "หนึ่ง", "สอง", "สาม", "สี่", "ห้า", "หก", "เจ็ด", "แปด", "เก้า");
        $number = $number + 0;
        $ret = "";
        if ($number == 0)
            return $ret;
        if ($number > 1000000) {
            $ret .= $this->ReadNumber(intval($number / 1000000)) . "ล้าน";
            $number = intval(fmod($number, 1000000));
        }

        $divider = 100000;
        $pos = 0;
        while ($number > 0) {
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

    public function Convert($amount_number)
    {
        $amount_number = number_format($amount_number, 2, ".", "");
        $pt = strpos($amount_number, ".");
        $number = $fraction = "";
        if ($pt === false)
            $number = $amount_number;
        else {
            $number = substr($amount_number, 0, $pt);
            $fraction = substr($amount_number, $pt + 1);
        }

        $ret = "";
        $baht = $this->ReadNumber($number);
        if ($baht != "")
            $ret .= $baht . "บาท";

        $satang = $this->ReadNumber($fraction);
        if ($satang != "")
            $ret .= $satang . "สตางค์";
        else
            $ret .= "ถ้วน";
        return $ret;
    }

    public function index($estimate_id = 0)
    {

        echo 'Hello World! <br/>';
        // $user_id = 3;
        // $sql = "SELECT * FROM `invoices` WHERE id = 6";
        // $data = $this->Db_model->fetchAll($sql);
        // foreach($data as $d){
        //     $da = explode("-",$d->note);
        //     echo implode("<br/>",$da);
        // }

    }

    public function DateConvert($strDate = 0)
    {
        $date = date_create($strDate);
        return date_format($date, "d / m / Y");
    }



    public function estimates_pdf($estimate_id = 0)
    {
        $template = '<div style="text-align: [align]; position:absolute; top:[top]px; left:[left]px; width:[w]px; height:[h]px;"><span>[val]</span></div>';

        $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];


        $mpdf = new \Mpdf\Mpdf([
            'fontDir' => array_merge($fontDirs, [
                __DIR__ . '/fonts',
            ]),
            'fontdata' => $fontData + [
                'def' => [
                    'R' => 'THSarabun_Bold.ttf',
                    //'I' => 'THSarabunNew Italic.ttf',
                    //'B' => 'THSarabunNew Bold.ttf',
                ]
            ],
            'default_font' => 'def',
            'tempDir' => '/tmp'
        ]);
        $mpdf->charset_in = 'UTF-8';


        $keep = array();
        $html = '';
        $company_address = nl2br(get_setting("company_address"));
        $company_phone = get_setting("company_phone");
        $company_website = get_setting("company_website");
        $company_vat_number = get_setting("company_vat_number");
        $company_name = get_setting("company_name");


        $sql = "SELECT *,estimates.id as esId,estimate_items.id as itemId, clients.id as clientId , estimates.note as es_note, projects.title as protitle, estimate_items.title as esITitle, estimate_items.description as esDescription,
            clients.company_name as company_name, clients.address as address, clients.city as city, clients.state as state, clients.zip as zip, clients.country as country, clients.vat_number as vat_number
            FROM `estimates` 
            LEFT JOIN estimate_items ON estimate_items.estimate_id = estimates.id AND estimate_items.deleted = 0
            LEFT JOIN clients ON estimates.client_id = clients.id            
            LEFT JOIN users ON clients.id = users.client_id AND users.is_primary_contact = 1
            LEFT JOIN projects ON projects.id = estimates.project_id
            WHERE estimates.id = $estimate_id;";


        $data = $this->Db_model->creatBy($estimate_id, "estimates");
        $fname = $data->first_name;
        $lname = $data->last_name;


        $cal_payment = $this->Estimates_model->get_estimate_total_summary($estimate_id);
        // var_dump($cal_payment);exit;
        if ($cal_payment->tax_name) {
            $tax_name = $cal_payment->tax_name;
        } else {
            $tax_name = "-";
        }

        $tax_name2 = isset($cal_payment->tax_name2) ? $cal_payment->tax_name2 : "-";
        if ($cal_payment->tax_id == 1) {
            $tax_val = ($cal_payment->estimate_subtotal - $cal_payment->discount_total) + $cal_payment->tax;
        } else {
            $tax_val = ($cal_payment->estimate_subtotal - $cal_payment->discount_total) - $cal_payment->tax;
        }

        if ($cal_payment->tax2 != NULL) {
            $trs_tax2 = '
                    
                    <tr>
                        <td colspan="3"></td> 
                        <td colspan="3" style="border-top: 2px solid #999; height:10px;" ></td> 
                        
                    </tr>

                    <tr>
                        <td colspan="2"></td> 
                        <td colspan="3" style="text-align: right;"><span class="label">' . $tax_name2 . '</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->tax2, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                        <td colspan="2"></td> 
                        
                        <td colspan="3" style="text-align: right;"><span class="label" >ยอดชำระ</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->estimate_total, 2, '.', ',') . ' บาท</td>
                    </tr>

                ';
        }

        if ($cal_payment->discount_total == '') {

            $trs_total[] = '
                        <tr>
                            <td colspan="2"></td>                            
                            <td colspan="3" style="text-align: right; margin-top: 4%"><span class="label">รวมเป็นเงิน</span></td>                            
                            <td style="text-align: right;">' . number_format($cal_payment->estimate_subtotal, 2, '.', ',') . ' บาท</td>
                        </tr>                        
                        <tr>
                            <td colspan="2"></td> 
                            <td colspan="3" style="text-align: right;"><span class="label">' . $tax_name . '</span></td>
                            <td style="text-align: right;">' . number_format($cal_payment->tax, 2, '.', ',') . ' บาท</td>
                        </tr>
                        <tr>
                            
                            <td colspan="2" style="text-align: left;"><span>(' . $this->Convert($cal_payment->estimate_total) . ')</span></td> 
                            
                            <td colspan="3" style="text-align: right;"><span class="label" >จำนวนเงินรวมทั้งสิ้น</span></td>
                            <td style="text-align: right;">' . number_format($tax_val, 2, '.', ',') . ' บาท</td>
                        </tr>
                        ' . $trs_tax2 . '

                    ';
        } else if ($cal_payment->discount_type == "before_tax") {



            $trs_total[] = '
                    <tr>
                        <td colspan="2"></td>                            
                        <td colspan="3" style="text-align: right;"><span class="label">ราคาก่อนหักส่วนลด</span></td>                            
                        <td style="text-align: right;">' . number_format($cal_payment->estimate_subtotal, 2, '.', ',') . '</td>
                    </tr>
                    <tr>
                        <td colspan="2"></td> 
                        <td colspan="3" style="text-align: right;"><span class="label"><u>หัก</u> ส่วนลด</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->discount_total, 2, '.', ',') . '</td>
                    </tr>
                    <tr>
                        <td colspan="2"></td> 
                        <td colspan="3" style="text-align: right;"><span class="label">ราคาหลังส่วนลด</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->estimate_subtotal - $cal_payment->discount_total, 2, '.', ',') . '</td>
                    </tr>
                    <tr>
                        <td colspan="2"></td> 
                        <td colspan="3" style="text-align: right;"><span class="label">' . $tax_name . '</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->tax, 2, '.', ',') . '</td>
                    </tr>
                                        
                    <tr>
                        
                        <td colspan="2" style="text-align: left;"><span>' . $this->Convert($cal_payment->estimate_total) . '</span></td> 
                        <td colspan="3" style="text-align: right;"><span class="label" >จำนวนเงินรวมทั้งสิ้น</span></td>
                        <td style="text-align: right;">' . number_format($tax_val, 2, '.', ',') . '</td>
                    </tr>
                    ' . $trs_tax2 . '

                ';

        } else if ($cal_payment->discount_type == "after_tax") {

            $trs_total[] = '
                    <tr>
                        <td colspan="2"></td>                            
                        <td colspan="3" style="text-align: right;"><span class="label">ราคาก่อนหักส่วนลด</span></td>                            
                        <td style="text-align: right;">' . number_format($cal_payment->estimate_subtotal, 2, '.', ',') . '</td>
                    </tr>
                    <tr>
                        <td colspan="2"></td> 
                        <td colspan="3" style="text-align: right;"><span class="label">' . $tax_name . '</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->tax, 2, '.', ',') . '</td>
                    </tr>
                    <tr>
                        <td colspan="2"></td> 
                        <td colspan="3" style="text-align: right;"><span class="label"><u>หัก</u> ส่วนลด</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->discount_total, 2, '.', ',') . '</td>
                    </tr>
                    
                    <tr>
                        <td colspan="2"></td> 
                        <td colspan="3" style="text-align: right;"><span class="label">ราคาหลังส่วนลด</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->estimate_subtotal - $cal_payment->discount_total, 2, '.', ',') . '</td>
                    </tr>
                    
                    <tr>                        
                        <td colspan="2" style="text-align: left;"><span>' . $this->Convert($cal_payment->estimate_total) . '</span></td>                         
                        <td colspan="3" style="text-align: right;"><span class="label" >จำนวนเงินรวมทั้งสิ้น</span></td>
                        <td style="text-align: right;">' . number_format($tax_val, 2, '.', ',') . '</td>
                    </tr>
                    ' . $trs_tax2 . '

                ';

        }








        $marks[1] = array();
        $left = 460;
        $user_signature = $this->Db_model->signature_approve($estimate_id, "estimates");
        // var_dump($user_signature);exit;
        if ($user_signature) {
            if ($user_signature->signature == '') {
                $top = 1010;
                $marks[1][] = array('key' => 'signauture', '[left]' => $left, '[w]' => 130, '[top]' => $top, '[val]' => $user_signature->first_name . ' ' . $user_signature->last_name, '[align]' => 'center');
            } else {
                $top = 1000;
                $marks[1][] = array('key' => 'signauture', '[left]' => $left, '[w]' => 130, '[top]' => $top, '[val]' => '<img src=' . base_url() . $user_signature->signature . '>', '[align]' => 'center');
            }
            $top = 1040;
            $left += 165;
            $marks[1][] = array('key' => 'date_approved', '[left]' => $left, '[w]' => 130, '[top]' => $top, '[val]' => $this->DateConvert($user_signature->doc_date), '[align]' => 'center');
        }

        //End Item-List------------------------------------------------------------------------------------------------------------


        $rangItem = 9;

        foreach ($this->Db_model->fetchAll($sql) as $parentKey => $child) {

            if (!isset($keep[$child->esId])) {
                $page = 1;
            }

            $keep[$child->esId][$page][] = $child;

            if (count($keep[$child->esId][$page]) == $rangItem) {
                $page += 1;
            }

        }

        foreach ($keep as $kd => $kv) {

            $i = 1;
            foreach ($kv as $kpage => $vpage) {
                $dbs = convertObJectToArray($vpage[0]);
                $dbs = array_merge($dbs, [
                    'estimate_date' => $this->DateConvert($vpage[0]->estimate_date),
                    'valid_until' => $this->DateConvert($vpage[0]->valid_until),
                    'price_before_dis' => '<span class="label">ราคาก่อนหักส่วนลด</span>',
                    'discount_name' => '<span class="label"><u>หัก</u> ส่วนลด</span>',
                    'price_after_dis' => '<span class="label">ราคาหลังส่วนลด</span>',
                    'vat_name' => '<span class="label">' . isset($cal_payment->tax_name) ? '<span class="label">' . $cal_payment->tax_name . '</span>' : "-" . '</span>',
                    'total_name' => '<span class="label">รวมเงินทั้งสิ้น</span>',

                ]);

                $divs = array();

                foreach ($marks[1] as $km => $vm) {
                    $vm['[align]'] = isset($vm['[align]']) ? $vm['[align]'] : 'left';
                    $vm['[val]'] = isset($dbs[$vm['key']]) ? $dbs[$vm['key']] : $vm['[val]'];
                    $divs[] = str_replace(array_keys($vm), $vm, $template);
                }
                $top = 326;
                $trs = array();

                foreach ($vpage as $vkpage => $vvpage) {


                    if ($vvpage->esITitle) {
                        $trs[] = '
                            <tr>
                                <td style="width: 5%; border-bottom: 1px solid #999; vertical-align: top;">' . $i++ . '</td>
                                <td style="text-align: left; width: 55%; border-bottom: 1px solid #999;">' . $vvpage->esITitle . '<br/>' . $vvpage->esDescription . '</td>
                                <td style=" min-width: 6%; border-bottom: 1px solid #999; text-align: right; vertical-align: top;">' . number_format($vvpage->quantity, 0, '.', ',') . '</td>
                                <td style=" width: 6%; border-bottom: 1px solid #999; text-align: right; vertical-align: top;">' . $vvpage->unit_type . '</td>                                
                                <td style=" width: 12%; text-align: right; border-bottom: 1px solid #999; vertical-align: top;">' . number_format($vvpage->rate, 2, '.', ',') . '</td>
                                <td style="text-align: right; border-bottom: 1px solid #999; vertical-align: top;">' . number_format($vvpage->total, 2, '.', ',') . '</td>
                            </tr>
                            ';
                    } else {
                        $trs[] = '
                            <tr>
                                <td style="width: 3%;"></td>
                                <td style="width: 63%;"></td>
                                <td style=" width: 6%;"></td>
                                <td style=" width: 6%;"></td>                                
                                <td ></td>
                                <td ></td>
                            </tr>
                            ';
                    }


                    $marks[2] = array(); //สร้าง array แยกแต่ละรายการ

                    $dbs = convertObJectToArray($vvpage);

                    foreach ($marks[2] as $km => $vm) {
                        $vm['[align]'] = isset($vm['[align]']) ? $vm['[align]'] : 'center';
                        $vm['[val]'] = isset($dbs[$vm['key']]) ? $dbs[$vm['key']] : $vm['[val]'];
                        $divs[] = str_replace(array_keys($vm), $vm, $template);
                    }

                }

                $trs_note = array();
                // var_dump($vpage[0]->es_note);exit;

                if ($vpage[0]->es_note) {
                    $trs_note[] = '
                        <tr>
                            
                            <td colspan="6" style="text-align: left;"><br/><p class="label">หมายเหตุ : </p>' . nl2br($vpage[0]->es_note) . '</td>
                        </tr>
                    ';
                }

                $divTop = 110;
                $divleft = 27;
                $trs_client = '
                    <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 390px; height: 190px;">
                        <table style="width: 100%; ">
                            <tr>
                                <td style="text-align: left;">' . $company_name . '<br/>' . $company_address . '<br/>หมายเลขประจำตัวผู้เสียภาษี ' . $company_vat_number . '<br/>' . $company_phone . '</td>
                            </tr>
                            <tr>
                                <td style="text-align: left;"><span class="label"><br/>ลูกค้า</span></td>
                            </tr>
                            <tr>
                                <td style="text-align: left;">' . $vpage[0]->company_name . '<br/>' . $vpage[0]->address . ' ' . $vpage[0]->city . ' ' . $vpage[0]->state . ' ' . $vpage[0]->zip . ' ' . $vpage[0]->country . '<br/>' . 'เลขประจำตัวผู้เสียภาษี ' . $vpage[0]->vat_number . '</td>
                            </tr>
                        </table>
                    </div>
                    ';

                $divTop += 190;
                $divleft = 27;
                $tabletemplate = '
                    <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 1000px">
                        <table>
                            <tr>
                                <td class="thStyle">#</td>
                                <td class="thStyle">รายละเอียด</td>
                                <td class="thStyle">จำนวน</td>
                                <td class="thStyle"></td>
                                <td class="thStyle">ราคาต่อหน่วย</td>
                                <td class="thStyle" style="text-align: right;">ยอดรวม</td>
                            </tr>
                            ' . implode("", $trs) . '
                            ' . implode("", $trs_total) . '
                            ' . implode("", $trs_note) . '
                        </table>
                    </div>';
                $info_contact = array();

                $project_title = isset($vpage[0]->protitle) ? $vpage[0]->protitle : "-";

                $info_contact[] = '
                         <tr>
                            <td style="text-align: left; padding-left: 5px;"><span class="label">ชื่องาน</span></td>
                            <td style="text-align: left;"><span>' . $project_title . '</span></td>
                            <td colspan="2"></td>
                        </tr>
                    ';


                if ($vpage[0]->first_name) {
                    $info_contact[] = '
                        
                        <tr>
                            <td style="text-align: left; padding-left: 5px;"><span class="label">ผู้ติดต่อ</span></td>
                            <td style="text-align: left;"><span>' . $vpage[0]->first_name . ' ' . $vpage[0]->last_name . '</span></td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td style="text-align: left; padding-left: 5px;"><span class="label">เบอร์โทร</span></td>
                            <td style="text-align: left;"><span>' . $vpage[0]->phone . '</span></td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td style="text-align: left; padding-left: 5px;"><span class="label">อีเมล</span></td>
                            <td style="text-align: left;"><span>' . $vpage[0]->email . '</span></td>
                            <td colspan="2"></td>
                        </tr>';
                }


                // <tr>
                //                 <td style="text-align: left; width: 50px;"><span class="label">อ้างอิง</span></td>
                //                 <td style="text-align: left;"><span>(free text)</span></td>
                //             </tr>

                if ($vpage[0]->credit != 0) {
                    $pay_c = $vpage[0]->credit . " วัน";
                    if ($vpage[0]->pay_type == "percentage") {
                        $pay = $vpage[0]->pay_sp . " %";
                    } else {
                        $pay = $vpage[0]->pay_sp . " งวด";
                    }
                    $pay_detail = '
                        <td style="text-align: left; width: 50px; border-bottom: 1.5px solid #999;"><span class="label">การชำระ</span></td>
                        <td style="text-align: left; border-bottom: 1.5px solid #999;">' . $pay . '</td>
                        ';
                } else {
                    $pay_c = "จ่ายเป็นเงินสด";
                    $pay_detail = '';
                }


                // var_dump($pay);
                // var_dump($vpage[0]);exit;

                $divTop = 50;
                $divleft = 420;
                $info_table = '
                        <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 340px">
                            <table style="width: 100%">
                                <tr>
                                    <th colspan="4" style="text-align: center; border-bottom: 1.5px solid #999; font-size: 30px;"><span class="label">ใบเสนอราคา</span></th>
                                    
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding-left: 5px; width: 70px;"><span class="label" >เลขที่</span></td>
                                    <td style="text-align: left;"><span>' . $vpage[0]->doc_no . '</span></td>
                                    <td colspan="2"></td>
                                </tr>
                                
                                
                                <tr>
                                    <td style="text-align: left; padding-left: 5px;"><span class="label">วันที่</span></td>
                                    <td style="text-align: left;"><span>' . $this->DateConvert($vpage[0]->estimate_date) . '</span></td>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding-left: 5px;"><span class="label">ผู้ขาย</span></td>
                                    <td style="text-align: left; "><span>' . $fname . ' ' . $lname . '</span></td>
                                    <td colspan="2"></td>
                                </tr>

                                <tr>
                                    <td style="text-align: left; padding-left: 5px; border-bottom: 1.5px solid #999;"><span class="label">เครดิต</span></td>
                                    <td style="text-align: left; border-bottom: 1.5px solid #999;">' . $pay_c . '</td>
                                    ' . $pay_detail . '
                                </tr>

                                <tr>
                                    <td></td>
                                </tr>
                                
                                ' . implode("", $info_contact) . '
                            </table>
                        </div>';

                // <tr>
                //             <td style="text-align: left; border-bottom: 1.5px solid #999; padding-left: 5px;"><span class="label">อ้างอิงใบสั่งซื้อ</span></td>
                //             <td style="text-align: left; border-bottom: 1.5px solid #999;"><span> - </span></td>
                //         </tr>

                $divTop = 1020;
                $divleft = 27;
                $trs_approve = '<div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 750px; ">
                        <table>
                            <tr>
                                <td colspan="2">ในนาม ' . $vpage[0]->company_name . '</td>
                                <td></td>
                                <td colspan="2">ในนาม ' . $company_name . '</td>
                            </tr>
                            <tr>
                                <td style="height: 100px;">___________________________<p>ผู้สั่งซื้อสินค้า</p></td>
                                <td>___________________________<p>วันที่</p></td>
                                <td style="width: 10%;"></td>
                                <td>___________________________<p>ผู้อนุมัติ</p></td>
                                <td>___________________________<p>วันที่</p></td>
                            </tr>                            
                        </table>
                    </div>';

                $divTop = 5;
                $divleft = -60;
                $estimate_logo = "estimate_logo";
                $logos = '<img src="' . get_file_from_setting($estimate_logo, get_setting('only_file_path')) . '" style="width: 300px"/>';

                $logo_es = '<div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; ">
                        ' . $logos . '
                    </div>';


                $html = ' 
                            <style>
                            div{
                                font-size: 17px;
                                // border: solid 1px #000;
                                // font-weight:bold;
                                text-align: center;
                            }
                            img{
                                width: 200px;
                                height: 100px;
                            }
                            .label{
                                color: #CD853F  ;
                                
                            }

                            table {
                                font-size: 17px;
                                // border: solid 1px #000;
                                width: 735px; 
                                border-collapse: collapse;
                            }
                            th,td{
                                text-align: center;
                                // border: solid 1px #000;
                            }
                            .thA{
                                width: 10%;
                            }

                            .thStyle{
                                border-top: 2px solid #999;
                                border-bottom: 2px solid #999;
                            }

                            .text_info{
                                padding-left: 5px; 
                            }
                           
                            </style>
                            ' . $logo_es . '
                            ' . $trs_client . '
                            ' . $info_table . '
                            ' . $tabletemplate . '
                            ' . $trs_approve . '
                            ' . implode('', $divs) . '
                        ';

                //  . implode('', $divs) . 
                $mpdf->SetTitle($vpage[0]->doc_no);
                $mpdf->AddPage('P');
                $pagecount = $mpdf->SetSourceFile('pdf_Template/template.pdf');
                $tplId = $mpdf->importPage(1);
                $mpdf->useTemplate($tplId);
                $mpdf->WriteHTML($html);
            }


        }

        $mpdf->Output();
        // $mpdf->Output($vpage[0]->doc_no.'.pdf', \Mpdf\Output\Destination::DOWNLOAD);       

    }



    public function order_pdf($order_id = 0)
    {
        $template = '<div style="text-align: [align]; position:absolute; top:[top]px; left:[left]px; width:[w]px; height:[h]px;"><span>[val]</span></div>';

        $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];


        $mpdf = new \Mpdf\Mpdf([
            'fontDir' => array_merge($fontDirs, [
                __DIR__ . '/fonts',
            ]),
            'fontdata' => $fontData + [
                'def' => [
                    'R' => 'THSarabun_Bold.ttf',
                    //'I' => 'THSarabunNew Italic.ttf',
                    //'B' => 'THSarabunNew Bold.ttf',
                ]
            ],
            'default_font' => 'def',
            'tempDir' => '/tmp'
        ]);
        $mpdf->charset_in = 'UTF-8';


        $keep = array();
        $html = '';
        $company_address = nl2br(get_setting("company_address"));
        $company_phone = get_setting("company_phone");
        $company_website = get_setting("company_website");
        $company_vat_number = get_setting("company_vat_number");
        $company_name = get_setting("company_name");


        $sql = "SELECT *,orders.id as orderId,order_items.id as itemId, clients.id as clientId , orders.note as order_note, order_items.title as orderITitle
            FROM `orders` 
            LEFT JOIN order_items ON order_items.order_id = orders.id AND order_items.deleted = 0
            LEFT JOIN clients ON orders.client_id = clients.id            
            LEFT JOIN users ON clients.id = users.client_id AND users.is_primary_contact = 1
            WHERE orders.id = $order_id;";

        //var_dump($order_id,$this->Db_model->fetchAll($sql));exit;


        $data = $this->Db_model->creatBy($order_id, "orders");
        $fname = $data->first_name;
        $lname = $data->last_name;


        $cal_payment = $this->Orders_model->get_order_total_summary($order_id);
        //var_dump($cal_payment);exit;
        if ($cal_payment->tax_name) {
            $tax_name = $cal_payment->tax_name;
        } else {
            $tax_name = "-";
        }
        $trs_tax2 = '';

        $tax_name2 = isset($cal_payment->tax_name2) ? $cal_payment->tax_name2 : "-";
        /* if($cal_payment->tax == 1){
            $tax_val = ($cal_payment->order_total - $cal_payment->discount_total) + $cal_payment->tax;
        }else{
            $tax_val = ($cal_payment->order_total - $cal_payment->discount_total) - $cal_payment->tax;
        } */

        if ($cal_payment->tax2 != NULL) {
            $trs_tax2 = '
                    
                    <tr>
                        
                        <td colspan="3"></td> 
                        <td colspan="3" style="border-top: 2px solid #999; height:10px;" ></td> 
                        
                    </tr>

                    <tr>
                        <td colspan="2"></td> 
                        <td colspan="3" style="text-align: right;"><span class="label">' . $tax_name2 . '</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->tax2, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                        <td colspan="2"></td> 
                        
                        <td colspan="3" style="text-align: right;"><span class="label" >ยอดชำระ</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->order_total, 2, '.', ',') . ' บาท</td>
                    </tr>

                ';
        }

        //var_dump($trs_tax2);exit;

        if ($cal_payment->discount_total == '' || $cal_payment->discount_total == 0) {
            //var_dump(number_format($cal_payment->order_subtotal,2,'.',','));exit;
            $trs_total[] = '
                        <tr>
                            <td colspan="2"></td>                            
                            <td colspan="3" style="text-align: right; margin-top: 4%"><span class="label">รวมเป็นเงิน</span></td>                            
                            <td style="text-align: right;">' . number_format($cal_payment->order_subtotal, 2, '.', ',') . ' บาท</td>
                        </tr>                        
                        <tr>
                            <td colspan="2"></td> 
                            <td colspan="3" style="text-align: right;"><span class="label">' . $tax_name . '</span></td>
                            <td style="text-align: right;">' . number_format($cal_payment->tax, 2, '.', ',') . ' บาท</td>
                        </tr>
                        <tr>
                            
                            <td colspan="2" style="text-align: left;"><span>(' . $this->Convert($cal_payment->order_total) . ')</span></td> 
                            
                            <td colspan="3" style="text-align: right;"><span class="label" >จำนวนเงินรวมทั้งสิ้น</span></td>
                            <td style="text-align: right;">' . number_format($cal_payment->order_total, 2, '.', ',') . ' บาท</td>
                        </tr>
                        ' . $trs_tax2 . '

                    ';
        } else if ($cal_payment->discount_type == "before_tax") {



            $trs_total[] = '
                    <tr>
                        <td colspan="2"></td>                            
                        <td colspan="3" style="text-align: right;"><span class="label">ราคาก่อนหักส่วนลด</span></td>                            
                        <td style="text-align: right;">' . number_format($cal_payment->order_subtotal, 2, '.', ',') . '</td>
                    </tr>
                    <tr>
                        <td colspan="2"></td> 
                        <td colspan="3" style="text-align: right;"><span class="label"><u>หัก</u> ส่วนลด</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->discount_total, 2, '.', ',') . '</td>
                    </tr>
                    <tr>
                        <td colspan="2"></td> 
                        <td colspan="3" style="text-align: right;"><span class="label">ราคาหลังส่วนลด</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->order_total - $cal_payment->discount_total, 2, '.', ',') . '</td>
                    </tr>
                    <tr>
                        <td colspan="2"></td> 
                        <td colspan="3" style="text-align: right;"><span class="label">' . $tax_name . '</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->tax, 2, '.', ',') . '</td>
                    </tr>
                                        
                    <tr>
                        
                        <td colspan="2" style="text-align: left;"><span>' . $this->Convert($cal_payment->order_total) . '</span></td> 
                        <td colspan="3" style="text-align: right;"><span class="label" >จำนวนเงินรวมทั้งสิ้น</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->order_total, 2, '.', ',') . '</td>
                    </tr>
                    ' . $trs_tax2 . '

                ';

        } else if ($cal_payment->discount_type == "after_tax") {

            $trs_total[] = '
                    <tr>
                        <td colspan="2"></td>                            
                        <td colspan="3" style="text-align: right;"><span class="label">ราคาก่อนหักส่วนลด</span></td>                            
                        <td style="text-align: right;">' . number_format($cal_payment->order_subtotal, 2, '.', ',') . '</td>
                    </tr>
                    <tr>
                        <td colspan="2"></td> 
                        <td colspan="3" style="text-align: right;"><span class="label">' . $tax_name . '</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->tax, 2, '.', ',') . '</td>
                    </tr>
                    <tr>
                        <td colspan="2"></td> 
                        <td colspan="3" style="text-align: right;"><span class="label"><u>หัก</u> ส่วนลด</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->discount_total, 2, '.', ',') . '</td>
                    </tr>
                    
                    <tr>
                        <td colspan="2"></td> 
                        <td colspan="3" style="text-align: right;"><span class="label">ราคาหลังส่วนลด</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->order_total - $cal_payment->discount_total, 2, '.', ',') . '</td>
                    </tr>
                    
                    <tr>                        
                        <td colspan="2" style="text-align: left;"><span>' . $this->Convert($cal_payment->order_total) . '</span></td>                         
                        <td colspan="3" style="text-align: right;"><span class="label" >จำนวนเงินรวมทั้งสิ้น</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->order_total, 2, '.', ',') . '</td>
                    </tr>
                    ' . $trs_tax2 . '

                ';

        }







        /* <img src="<?php echo get_file_from_setting($estimate_logo, get_setting('only_file_path')); ?>" />*/
        $marks[1] = array();
        $left = 460;
        $user_signature = $this->Db_model->signature_approve($order_id, "orders");
        // var_dump($user_signature);exit;
        if ($user_signature) {
            if ($user_signature->signature == '') {
                $top = 1010;
                $marks[1][] = array('key' => 'signauture', '[left]' => $left, '[w]' => 130, '[top]' => $top, '[val]' => $user_signature->first_name . ' ' . $user_signature->last_name, '[align]' => 'center');
            } else {
                $top = 1000;
                $marks[1][] = array('key' => 'signauture', '[left]' => $left, '[w]' => 130, '[top]' => $top, '[val]' => '<img src=' . base_url() . $user_signature->signature . '>', '[align]' => 'center');
            }
            $top = 1040;
            $left += 165;
            $marks[1][] = array('key' => 'date_approved', '[left]' => $left, '[w]' => 130, '[top]' => $top, '[val]' => $this->DateConvert($user_signature->doc_date), '[align]' => 'center');
        }

        //End Item-List------------------------------------------------------------------------------------------------------------


        $rangItem = 9;

        foreach ($this->Db_model->fetchAll($sql) as $parentKey => $child) {

            if (!isset($keep[$child->orderId])) {
                $page = 1;
            }

            $keep[$child->orderId][$page][] = $child;

            if (count($keep[$child->orderId][$page]) == $rangItem) {
                $page += 1;
            }

        }

        //arr($keep);exit;

        foreach ($keep as $kd => $kv) {

            $i = 1;
            foreach ($kv as $kpage => $vpage) {
                //arr($vpage);exit;
                $dbs = convertObJectToArray($vpage[0]);
                $dbs = array_merge($dbs, [
                    'estimate_date' => $this->DateConvert($vpage[0]->order_date),
                    'price_before_dis' => '<span class="label">ราคาก่อนหักส่วนลด</span>',
                    'discount_name' => '<span class="label"><u>หัก</u> ส่วนลด</span>',
                    'price_after_dis' => '<span class="label">ราคาหลังส่วนลด</span>',
                    'vat_name' => '<span class="label">' . isset($cal_payment->tax_name) ? '<span class="label">' . $cal_payment->tax_name . '</span>' : "-" . '</span>',
                    'total_name' => '<span class="label">รวมเงินทั้งสิ้น</span>',

                ]);

                $divs = array();

                foreach ($marks[1] as $km => $vm) {
                    $vm['[align]'] = isset($vm['[align]']) ? $vm['[align]'] : 'left';
                    $vm['[val]'] = isset($dbs[$vm['key']]) ? $dbs[$vm['key']] : $vm['[val]'];
                    $divs[] = str_replace(array_keys($vm), $vm, $template);
                }
                $top = 326;
                $trs = array();

                foreach ($vpage as $vkpage => $vvpage) {


                    if ($vvpage->orderITitle) {
                        $trs[] = '
                            <tr>
                                <td style="width: 5%; border-bottom: 1px solid #999; vertical-align: top;">' . $i++ . '</td>
                                <td style="text-align: left; width: 55%; border-bottom: 1px solid #999;">' . $vvpage->orderITitle . '<br/></td>
                                <td style=" width: 6%; border-bottom: 1px solid #999; text-align: right; vertical-align: top;">' . number_format($vvpage->quantity, 0, '.', ',') . '</td>
                                <td style=" width: 6%; border-bottom: 1px solid #999; text-align: right; vertical-align: top;">' . $vvpage->unit_type . '</td>                                
                                <td style=" width: 12%; text-align: right; border-bottom: 1px solid #999; vertical-align: top;">' . number_format($vvpage->rate, 2, '.', ',') . '</td>
                                <td style="text-align: right; border-bottom: 1px solid #999; vertical-align: top;">' . number_format($vvpage->total, 2, '.', ',') . '</td>
                            </tr>
                            ';
                    } else {
                        $trs[] = '
                            <tr>
                                <td style="width: 3%;"></td>
                                <td style="width: 63%;"></td>
                                <td style=" width: 6%;"></td>
                                <td style=" width: 6%;"></td>                                
                                <td ></td>
                                <td ></td>
                            </tr>
                            ';
                    }


                    $marks[2] = array(); //สร้าง array แยกแต่ละรายการ

                    $dbs = convertObJectToArray($vvpage);

                    foreach ($marks[2] as $km => $vm) {
                        $vm['[align]'] = isset($vm['[align]']) ? $vm['[align]'] : 'center';
                        $vm['[val]'] = isset($dbs[$vm['key']]) ? $dbs[$vm['key']] : $vm['[val]'];
                        $divs[] = str_replace(array_keys($vm), $vm, $template);
                    }

                }

                $trs_note = array();
                // var_dump($vpage[0]->es_note);exit;

                if ($vpage[0]->order_note) {
                    $trs_note[] = '
                        <tr>
                            
                            <td colspan="6" style="text-align: left;"><br/><p class="label">หมายเหตุ : </p>' . nl2br($vpage[0]->order_note) . '</td>
                        </tr>
                    ';
                }

                $divTop = 110;
                $divleft = 27;
                $trs_client = '
                    <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 390px">
                        <table style="width: 100%; ">
                            <tr>
                                <td style="text-align: left;">' . $company_name . '<br/>' . $company_address . '<br/>หมายเลขประจำตัวผู้เสียภาษี ' . $company_vat_number . '<br/>' . $company_phone . '</td>
                            </tr>
                            <tr>
                                <td style="text-align: left;"><span class="label"><br/>ลูกค้า</span></td>
                            </tr>
                            <tr>
                                <td style="text-align: left;">' . $vpage[0]->company_name . '<br/>' . $vpage[0]->address . ' ' . $vpage[0]->city . ' ' . $vpage[0]->state . ' ' . $vpage[0]->zip . ' ' . $vpage[0]->country . '<br/>' . 'เลขประจำตัวผู้เสียภาษี ' . $vpage[0]->vat_number . '</td>
                            </tr>
                        </table>
                    </div>
                    ';

                $divTop += 190;
                $divleft = 27;
                $tabletemplate = '
                    <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 1000px">
                        <table>
                            <tr>
                                <td class="thStyle">#</td>
                                <td class="thStyle">รายละเอียด</td>
                                <td class="thStyle">จำนวน</td>
                                <td class="thStyle"></td>
                                <td class="thStyle">ราคาต่อหน่วย</td>
                                <td class="thStyle" style="text-align: right;">ยอดรวม</td>
                            </tr>
                            ' . implode("", $trs) . '
                            ' . implode("", $trs_total) . '
                            ' . implode("", $trs_note) . '
                        </table>
                    </div>';
                $info_contact = array();

                $project_title = isset($vpage[0]->protitle) ? $vpage[0]->protitle : "-";

                /* $info_contact[] = '
                     <tr>
                        <td style="text-align: left; padding-left: 5px;"><span class="label">ชื่องาน</span></td>
                        <td style="text-align: left;"><span>'.$project_title.'</span></td>
                        <td colspan="2"></td>
                    </tr>
                '; */


                if ($vpage[0]->first_name) {
                    $info_contact[] = '
                        
                        <tr>
                            <td style="text-align: left; padding-left: 5px;"><span class="label">ผู้ติดต่อ</span></td>
                            <td style="text-align: left;"><span>' . $vpage[0]->first_name . ' ' . $vpage[0]->last_name . '</span></td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td style="text-align: left; padding-left: 5px;"><span class="label">เบอร์โทร</span></td>
                            <td style="text-align: left;"><span>' . $vpage[0]->phone . '</span></td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td style="text-align: left; padding-left: 5px;"><span class="label">อีเมล</span></td>
                            <td style="text-align: left;"><span>' . $vpage[0]->email . '</span></td>
                            <td colspan="2"></td>
                        </tr>';
                }
                if ($vpage[0]->credit != 0) {
                    $pay_c = $vpage[0]->credit . " วัน";
                    if ($vpage[0]->pay_type == "percentage") {
                        $pay = $vpage[0]->pay_sp . " %";
                    } else {
                        $pay = $vpage[0]->pay_sp . " งวด";
                    }
                    $pay_detail = '
                        <td style="text-align: left; width: 50px; border-bottom: 1.5px solid #999;"><span class="label">การชำระ</span></td>
                        <td style="text-align: left; border-bottom: 1.5px solid #999;">' . $pay . '</td>
                        ';
                } else {
                    $pay_c = "จ่ายเป็นเงินสด";
                    $pay_detail = '';
                }
                $divTop = 50;
                $divleft = 420;
                $info_table = '
                        <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 340px">
                            <table style="width: 100%">
                                <tr>
                                    <th colspan="4" style="text-align: center; border-bottom: 1.5px solid #999; font-size: 30px;"><span class="label">คำสั่งซื้อ</span></th>
                                    
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding-left: 5px; width: 70px;"><span class="label" >เลขที่</span></td>
                                    <td style="text-align: left;"><span>' . $vpage[0]->doc_no . '</span></td>
                                    <td colspan="2"></td>
                                </tr>
                                
                                
                                <tr>
                                    <td style="text-align: left; padding-left: 5px;"><span class="label">วันที่</span></td>
                                    <td style="text-align: left;"><span>' . $this->DateConvert($vpage[0]->order_date) . '</span></td>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding-left: 5px;"><span class="label">ผู้ขาย</span></td>
                                    <td style="text-align: left; "><span>' . $fname . ' ' . $lname . '</span></td>
                                    <td colspan="2"></td>
                                </tr>

                                <tr>
                                    <td style="text-align: left; padding-left: 5px;"><span class="label">เครดิต</span></td>
                                    <td style="text-align: left;">' . $pay_c . '</td>
                                    ' . $pay_detail . '
                                </tr>

                                <tr>
                                    <td></td>
                                </tr>
                                
                                ' . implode("", $info_contact) . '
                            </table>
                        </div>';
                $divTop = 990;
                $divleft = 27;
                $trs_approve = '<div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 750px; ">
                        <table>
                            <tr>
                                <td colspan="2">ในนาม ' . $vpage[0]->company_name . '</td>
                                <td></td>
                                <td colspan="2">ในนาม ' . $company_name . '</td>
                            </tr>
                            <tr>
                                <td style="height: 100px;">___________________________<p>ผู้สั่งซื้อสินค้า</p></td>
                                <td>___________________________<p>วันที่</p></td>
                                <td style="width: 10%;"></td>
                                <td>___________________________<p>ผู้อนุมัติ</p></td>
                                <td>___________________________<p>วันที่</p></td>
                            </tr>                            
                        </table>
                    </div>';

                $divTop = 5;
                $divleft = -60;
                $estimate_logo = "estimate_logo";
                $logos = '<img src="' . get_file_from_setting($estimate_logo, get_setting('only_file_path')) . '" style="width: 300px"/>';

                $logo_es = '<div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; ">
                        ' . $logos . '
                    </div>';


                $html = ' 
                            <style>
                            div{
                                font-size: 17px;
                                // border: solid 1px #000;
                                // font-weight:bold;
                                text-align: center;
                            }
                            img{
                                width: 200px;
                                height: 100px;
                            }
                            .label{
                                color: #CD853F  ;
                                
                            }

                            table {
                                font-size: 17px;
                                // border: solid 1px #000;
                                width: 735px; 
                                border-collapse: collapse;
                            }
                            th,td{
                                text-align: center;
                                // border: solid 1px #000;
                            }
                            .thA{
                                width: 10%;
                            }

                            .thStyle{
                                border-top: 2px solid #999;
                                border-bottom: 2px solid #999;
                            }

                            .text_info{
                                padding-left: 5px; 
                            }
                           
                            </style>
                            ' . $logo_es . '
                            ' . $trs_client . '
                            ' . $info_table . '
                            ' . $tabletemplate . '
                            ' . $trs_approve . '
                            ' . implode('', $divs) . '
                        ';

                $mpdf->SetTitle($vpage[0]->doc_no);
                $mpdf->AddPage('P');
                $pagecount = $mpdf->SetSourceFile('pdf_Template/template.pdf');
                $tplId = $mpdf->importPage(1);
                $mpdf->useTemplate($tplId);
                $mpdf->WriteHTML($html);
            }
        }
        $mpdf->Output();
    }






    protected function dump($a, $exit = false)
    {
        echo "<xmp>";
        var_dump($a);
        echo "</xmp>";
        if ($exit)
            exit;
    }

    public function pr_pdf($pr_id = 0)
    {
        //$pr_info = $this->Purchaserequests_model->get_details(array("id" => $pr_id))->row();
        $pr_data = get_pr_making_data($pr_id);
        $pr_items = $pr_data['pr_items'];
        $buyer = $pr_data['client_info'];
        $pr_info = $pr_data['pr_info'];
        //$this->dump($pr_data, true);
        // var_dump($pr_data);exit;

        $template = '<div style="text-align: [align]; position:absolute; top:[top]px; left:[left]px; width:[w]px; height:[h]px;"><span>[val]</span></div>';

        $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'fontDir' => array_merge($fontDirs, [
                __DIR__ . '/fonts',
            ]),
            'fontdata' => $fontData + [
                'def' => [
                    'R' => 'THSarabun_Bold.ttf',
                    //'I' => 'THSarabunNew Italic.ttf',
                    //'B' => 'THSarabunNew Bold.ttf',
                ]
            ],
            'default_font' => 'def',
            'tempDir' => '/tmp'
        ]);
        $mpdf->charset_in = 'UTF-8';
        //$mpdf->SetTitle(get_pr_id($pr_id));
        $mpdf->SetTitle($pr_info->doc_no ? $pr_info->doc_no : lang('no_have_doc_no') . ':' . $pr_info->id);

        $keep = array();
        $html = '';
        $company_address = nl2br(get_setting("company_address"));
        $company_phone = get_setting("company_phone");
        $company_website = get_setting("company_website");
        $company_vat_number = get_setting("company_vat_number");
        $company_name = get_setting("company_name");


        /*$sql = "SELECT *,pr.id as pr_id,estimate_items.id as itemId, clients.id as clientId
        FROM `estimates` 
        LEFT JOIN estimate_items ON estimate_items.estimate_id = estimates.id AND estimate_items.deleted = 0
        LEFT JOIN clients ON estimates.client_id = clients.id
        WHERE estimates.id = $pr_id";*/
        //$pr_data = get_pr_making_data($pr_id);
        //$pr_items = $pr_data['pr_items'];
        // AND estimate_items.deleted = 0 $this->session->user_id
        //หัวตารากระดาษ------------------------------------------------------------------------------------------------------------
        // var_dump($buyer);exit;

        $cal_payment = $this->Purchaserequests_model->get_pr_total_summary($pr_id);
        $vat_name = $cal_payment->tax_name != null ? $cal_payment->tax_name : "-";

        if ($cal_payment->discount_total == '') {
            $trs_total[] = '                
                    <tr>
                    <td colspan="5" style="border-top: 1.5px solid #999;"></td>                            
                        <td colspan="2" style="text-align: right; margin-top: 4%; border-top: 1.5px solid #999;"><span class="label">รวมเป็นเงิน</span></td>                            
                        <td style="text-align: right; border-top: 1.5px solid #999;">' . number_format($cal_payment->pr_subtotal, 2, '.', ',') . ' บาท</td>
                    </tr>
                    
                    <tr>
                        <td colspan="5"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label">' . $vat_name . '</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->tax, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                        
                        <td colspan="5" style="text-align: left;"><span>(' . $this->Convert($cal_payment->pr_total) . ')</span></td> 
                                              
                        <td colspan="2" style="text-align: right;"><span class="label" >จำนวนเงินรวมทั้งสิ้น</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->pr_total, 2, '.', ',') . ' บาท</td>
                    </tr>

                ';
        } else

            if ($cal_payment->discount_type == "before_tax") {
                $trs_total[] = '
                    <tr>
                        <td colspan="5" style="border-top: 1.5px solid #999;"></td>                            
                        <td colspan="2" style="text-align: right; margin-top: 4%; border-top: 1.5px solid #999;"><span class="label">รวมเป็นเงิน</span></td>                            
                        <td style="text-align: right; border-top: 1.5px solid #999;">' . number_format($cal_payment->pr_subtotal, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                        <td colspan="5"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label"><u>หัก</u> ส่วนลด</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->discount_total, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                        <td colspan="5"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label">ราคาหลังส่วนลด</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->pr_subtotal - $cal_payment->discount_total, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                        <td colspan="5"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label">' . $vat_name . '</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->tax, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                    
                        <td colspan="5" style="text-align: left;"><span>(' . $this->Convert($cal_payment->pr_total) . ')</span></td> 
                        <td colspan="2" style="text-align: right;"><span class="label" >จำนวนเงินรวมทั้งสิ้น</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->pr_total, 2, '.', ',') . ' บาท</td>
                    </tr>

                ';
                // <td ><span>'.$this->Convert($cal_payment->estimate_total).'</span></td> 


            } else if ($cal_payment->discount_type == "after_tax") {

                $trs_total[] = '
                    <tr>
                        <td colspan="3" style="border-top: 1.5px solid #999;"></td>                            
                        <td colspan="2" style="text-align: right; border-top: 1.5px solid #999;"><span class="label">ราคาก่อนหักส่วนลด</span></td>                            
                        <td style="text-align: right; border-top: 1.5px solid #999;">' . number_format($cal_payment->pr_subtotal, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                        <td colspan="3"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label"><u>หัก</u> ส่วนลด</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->discount_total, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                        <td colspan="3"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label">' . $vat_name . '</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->tax, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                        <td colspan="3"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label">ราคาหลังส่วนลด</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->pr_subtotal - $cal_payment->discount_total, 2, '.', ',') . ' บาท</td>
                    </tr>
                    
                    <tr>
                   
                        <td colspan="3" style="text-align: left;"><span>(' . $this->Convert($cal_payment->pr_total) . ')</span></td> 
                        
                        <td colspan="2" style="text-align: right;"><span class="label" >จำนวนเงินรวมทั้งสิ้น</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->pr_total, 2, '.', ',') . ' บาท</td>
                    </tr>

                ';

            }


        $marks[1] = array();
        $user_signature = $this->Db_model->userSignature($pr_info->buyer_id);
        $top = 1020;
        $left = 65;
        if ($user_signature) {
            if ($user_signature->signature == '') {
                $top = 990;
                $marks[1][] = array('key' => 'buyer_signauture', '[left]' => $left, '[w]' => 170, '[top]' => $top, '[val]' => $user_signature->first_name . ' ' . $user_signature->last_name, '[align]' => 'center');
            } else {
                $top = 930;
                $marks[1][] = array('key' => 'buyer_signauture', '[left]' => $left, '[w]' => 170, '[top]' => $top, '[val]' => '<img src=' . base_url() . $user_signature->signature . '>', '[align]' => 'center');
            }
            $top = 1035;
            $marks[1][] = array('key' => 'buyer_date_approved', '[left]' => $left, '[w]' => 170, '[top]' => $top, '[val]' => $this->DateConvert($pr_info->pr_date), '[align]' => 'center');
        }


        $left = 555;
        $user_signature = $this->Db_model->signature_approve($pr_id, "purchaserequests");
        // var_dump($user_signature);exit;
        if ($user_signature) {
            if ($user_signature->signature == '') {
                $top = 990;
                $marks[1][] = array('key' => 'signauture', '[left]' => $left, '[w]' => 170, '[top]' => $top, '[val]' => $user_signature->first_name . ' ' . $user_signature->last_name, '[align]' => 'center');
            } else {
                $top = 930;
                $marks[1][] = array('key' => 'signauture', '[left]' => $left, '[w]' => 170, '[top]' => $top, '[val]' => '<img src=' . base_url() . $user_signature->signature . '>', '[align]' => 'center');
            }
            $top = 1035;

            $marks[1][] = array('key' => 'date_approved', '[left]' => $left, '[w]' => 170, '[top]' => $top, '[val]' => $this->DateConvert($user_signature->doc_date), '[align]' => 'center');
        }

        //End Item-List------------------------------------------------------------------------------------------------------------


        $rangItem = 13;

        foreach ($pr_items as $parentKey => $child) {

            if (!isset($keep[$child->pr_id])) {
                $page = 1;
            }

            $keep[$child->pr_id][$page][] = $child;

            if (count($keep[$child->pr_id][$page]) == $rangItem) {
                $page += 1;
            }

        }

        foreach ($keep as $kd => $kv) {
            //  var_dump($kv);exit;
            $i = 1;
            foreach ($kv as $kpage => $vpage) {
                $dbs = convertObJectToArray($vpage[0]);
                $dbs = array_merge($dbs, [
                    'pr_date' => $this->DateConvert($pr_info->pr_date),
                    'price_before_dis' => '<span class="label">ราคาก่อนหักส่วนลด</span>',
                    'discount_name' => '<span class="label"><u>หัก</u> ส่วนลด</span>',
                    'price_after_dis' => '<span class="label">ราคาหลังส่วนลด</span>',
                    'vat_name' => '<span class="label">' . isset($cal_payment->tax_name) ? '<span class="label">' . $cal_payment->tax_name . '</span>' : "-" . '</span>',
                    'total_name' => '<span class="label">รวมเงินทั้งสิ้น</span>'

                    //,'valid_until' => $this->DateConvert($vpage[0]->valid_until)
                ]);

                $divs = array();

                foreach ($marks[1] as $km => $vm) {
                    $vm['[align]'] = isset($vm['[align]']) ? $vm['[align]'] : 'left';
                    $vm['[val]'] = isset($dbs[$vm['key']]) ? $dbs[$vm['key']] : $vm['[val]'];
                    $divs[] = str_replace(array_keys($vm), $vm, $template);
                }
                $top = 315;

                $trs = array();
                foreach ($vpage as $vkpage => $vvpage) {

                    $marks[2] = array(); //สร้าง array แยกแต่ละรายการ
                    if ($vvpage->title) {
                        $trs[] = '
                            <tr>
                                <td style="width: 5%; ">' . $i++ . '</td>
                                <td style="text-align: left; width: 20%; ">' . $vvpage->title . '<br/>' . $vvpage->description . '</td>
                                <td style="text-align: left; width: 15%; ">' . $vvpage->project_name . '</td>
                                <td style="text-align: left; width: 15%; ">' . $vvpage->supplier_name . '</td>
                                <td style=" width: 6%; ">' . number_format($vvpage->quantity, 0, '.', ',') . '</td>
                                <td style=" width: 6%; text-align: right;">' . $vvpage->unit_type . '</td>                                
                                <td style="text-align: right;">' . number_format($vvpage->rate, 2, '.', ',') . '</td>
                                <td style="text-align: right; width: 15%;">' . number_format($vvpage->total, 2, '.', ',') . '</td>
                            </tr>
                            ';
                    } else {
                        $trs[] = '
                            <tr>
                                <td style="width: 5%;"></td>
                                <td style="width: 20%;"></td>
                                <td style="width: 15%;"></td>
                                <td style="width: 15%;"></td>
                                <td style=" width: 6%;"></td>                                
                                <td style=" width: 6%;"></td>
                                <td ></td>
                                <td ></td>
                            </tr>
                            ';
                    }


                    $dbs = convertObJectToArray($vvpage);

                    foreach ($marks[2] as $km => $vm) {
                        $vm['[align]'] = isset($vm['[align]']) ? $vm['[align]'] : 'center';
                        $vm['[val]'] = isset($dbs[$vm['key']]) ? $dbs[$vm['key']] : $vm['[val]'];
                        $divs[] = str_replace(array_keys($vm), $vm, $template);
                    }

                }
                $trs_note = array();
                $str_text = explode("-", $pr_info->note);
                if ($pr_info->note) {
                    $trs_note[] = '
                        <tr>
                            <td colspan="3" style="text-align: left;"><br/><p class="label">หมายเหตุ : </p>' . nl2br($pr_info->note) . '</td>
                        </tr>
                    ';
                }


                $data = $this->Db_model->creatBy($pr_id, "purchaserequests");
                // var_dump($buyer);exit;

                $divTop = 110;
                $divleft = 30;
                $trs_client = '
                    <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 370px">
                        <table style="width: 100%;">
                            <tr>
                                <td style="text-align: left;">' . $company_name . '<br/>' . $company_address . '<br/>หมายเลขประจำตัวผู้เสียภาษี ' . $company_vat_number . '<br/>' . $company_phone . '</td>
                            </tr>
                            <tr>
                            
                                <td style="text-align: left;"><span class="label"><br/>ผู้ขอซื้อ</span></td>
                            </tr>
                            <tr>
                                <td style="text-align: left;">' . $buyer->first_name . ' ' . $buyer->last_name . '<br/> ตำแหน่ง : ' . $buyer->job_title . '<br/> อีเมล : ' . $buyer->email . '<br/> เบอร์โทร : ' . $buyer->phone . '</td>
                            </tr>
                        </table>
                    </div>
                    ';

                $trs_ref = array();
                // var_dump($pr_info);exit;

                if ($pr_info->project_name != '') {
                    $project_ref = $pr_info->project_name;
                } else {
                    $project_ref = "-";
                }

                if ($pr_info->category_name != '') {
                    $category_name_ref = $pr_info->category_name;
                } else {

                    $category_name_ref = "-";
                }
                $trs_ref[] = '                        
                        <tr>
                            <td style="text-align: left; padding-left: 5px;"><span class="label">หมวดหมู่ใบขอซื้อ</span></td>
                            <td style="text-align: left;">' . $category_name_ref . '</td>
                        </tr>
                        <tr>
                            <td style="text-align: left; padding-left: 5px;"><span class="label">ชื่องาน</span></td>
                            <td style="text-align: left;">' . $project_ref . '</td>
                        </tr>
                    ';

                $divTop = 60;
                $divleft = 410;
                if ($pr_info->payment) {
                    $payment = $pr_info->payment;
                } else {
                    $payment = "-";
                }
                $info_table = '
                        <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 340px">
                            <table style="width: 100%;">
                                <tr>
                                    <th colspan="2" style="text-align: center; padding-left: 5px; border-bottom: 1.5px solid #999; font-size: 30px;"><span class="label">ใบขอซื้อ</span></th>
                                    
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding-left: 5px;"><span class="label">เลขที่</span></td>
                                    <td style="text-align: left; "><span>' . $pr_info->doc_no . '</span></td>
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding-left: 5px;"><span class="label">วันที่</span></td>
                                    <td style="text-align: left;"><span>' . $this->DateConvert($pr_info->pr_date) . '</span></td>
                                </tr>
                                
                                <tr>
                                    <td style="text-align: left; border-bottom: 1.5px solid #999; padding-left: 5px;"><span class="label">' . lang('payment_type') . ' </span></td>
                                    <td style="text-align: left; border-bottom: 1.5px solid #999;"><span>' . $payment . '</span></td>
                                </tr>

                                <tr>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td></td>
                                </tr>
                                ' . implode("", $trs_ref) . '

                            </table>
                        </div>';



                $divTop = 310;
                $divleft = 30;
                $tabletemplate = '
                    <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 720px">
                        <table>
                            <tr>
                                <td class="thStyle">#</td>
                                <td class="thStyle">รายละเอียด</td>
                                <td class="thStyle">ชื่อโครงการ</td>
                                <td class="thStyle">ผู้จัดจำหน่าย</td>
                                <td class="thStyle">จำนวน</td>
                                <td class="thStyle"></td>
                                <td class="thStyle" style="text-align: right;">ราคาต่อหน่วย</td>
                                <td class="thStyle" style="text-align: right;">ยอดรวม</td>
                            </tr>
                            ' . implode("", $trs) . '
                            ' . implode("", $trs_total) . '
                            ' . implode("", $trs_note) . '
                        </table>
                    </div>';

                $divTop = 990;
                $divleft = 27;
                $trs_approve = '<div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 750px; ">
                        <table>
                            <tr>
                                <td>___________________________<br/>ผู้จัดทำ</td>
                                <td>___________________________<br/>ผู้ตวจสอบ</td>
                                <td>___________________________<br/>ผู้อนุมัติ</td>
                            </tr>
                            <tr>
                                <td>___________________________<br/>วันที่</td>
                                <td>___________________________<br/>วันที่</td>
                                <td>___________________________<br/>วันที่</td>
                            </tr>                            
                        </table>
                    </div>';

                $html = ' 
                            <style>
                            div{
                                font-size: 17px;
                                // border: solid 1px #000;
                                // font-weight:bold;
                                text-align: center;
                            }
                            img{
                                width: 200px;
                                height: 100px;
                            }
                            .label{
                                color: blue;
                                
                            }

                            table {
                                font-size: 17px;
                                // border: solid 1px #000;
                                width: 735px; 
                                border-collapse: collapse;
                            }
                            th,td{
                                text-align: center;
                                // border: solid 1px #000;
                            }
                            .thA{
                                width: 10%;
                            }

                            .thStyle{
                                border-top: 2px solid #999;
                                border-bottom: 2px solid #999;
                            }

                            </style>
    
                            
                            ' . $info_table . '
                            ' . $tabletemplate . '
                            ' . $trs_approve . '
                            ' . $trs_client . '
                            ' . implode('', $divs) . '
                            
                        ';
                $mpdf->AddPage('P');
                $pagecount = $mpdf->SetSourceFile('pdf_Template/main_template.pdf');
                $tplId = $mpdf->importPage(1);
                $mpdf->useTemplate($tplId);
                $mpdf->WriteHTML($html);
            }


        }

        // $mpdf->Output();
        $mpdf->Output($pr_info->doc_no . '.pdf', \Mpdf\Output\Destination::DOWNLOAD);
    }

    public function po_pdf($pr_id = 0, $po_no = 0)
    {
        // var_dump($po_no);exit;
        $template = '<div style="text-align: [align]; position:absolute; top:[top]px; left:[left]px; width:[w]px; height:[h]px;"><span>[val]</span></div>';

        $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'fontDir' => array_merge($fontDirs, [
                __DIR__ . '/fonts',
            ]),
            'fontdata' => $fontData + [
                'def' => [
                    'R' => 'THSarabun_Bold.ttf',
                    //'I' => 'THSarabunNew Italic.ttf',
                    //'B' => 'THSarabunNew Bold.ttf',
                ]
            ],
            'default_font' => 'def',
            'tempDir' => '/tmp'
        ]);
        $mpdf->charset_in = 'UTF-8';

        $marks = array();
        $keep = array();
        $html = '';
        $company_address = nl2br(get_setting("company_address"));
        $company_phone = get_setting("company_phone");
        $company_website = get_setting("company_website");
        $company_vat_number = get_setting("company_vat_number");
        $company_name = get_setting("company_name");


        //$approver = $this->Provetable_model->getAprover($pr_id, "purchaserequests");

        //$pr_info = $this->Purchaserequests_model->get_details(array("id" => $pr_id))->row();
        $pr_data = get_po_making_data($pr_id);

        /*$items_per_page = 15;
            $pr_data['items_per_page'] = $items_per_page;
            $num_rows = count($po_items);
            $pages = ceil($num_rows/$items_per_page);
            
            $pr_data['supplier'] = $supplier;*/


        $sql = "SELECT pr.*,pr_items.*,pr.id as pr_id,pr_items.id as itemId
            FROM `purchaserequests` as pr 
            LEFT JOIN pr_items ON pr_items.pr_id = pr.id AND pr_items.deleted = 0 
            WHERE pr.id = $pr_id";
        // AND estimate_items.deleted = 0 $this->session->user_id
        //หัวตารากระดาษ------------------------------------------------------------------------------------------------------------                 

        $left = 460;
        $user_signature = $this->Db_model->signature_approve($pr_id, "purchaserequests");

        if ($user_signature) {
            if ($user_signature->signature == '') {
                $top = 1010;
                $marks[1][] = array('key' => 'signauture', '[left]' => $left, '[w]' => 130, '[top]' => $top, '[val]' => $user_signature->first_name . ' ' . $user_signature->last_name, '[align]' => 'center');
            } else {
                $top = 1000;
                $marks[1][] = array('key' => 'signauture', '[left]' => $left, '[w]' => 130, '[top]' => $top, '[val]' => '<img src=' . base_url() . $user_signature->signature . '>', '[align]' => 'center');
            }
            $top = 1040;
            $left += 155;
            $marks[1][] = array('key' => 'date_approved', '[left]' => $left, '[w]' => 130, '[top]' => $top, '[val]' => $this->DateConvert($user_signature->doc_date), '[align]' => 'center');
        }
        //End Item-List------------------------------------------------------------------------------------------------------------


        $items_per_page = 14;
        $p_po_items = $pr_data['po_items'];
        $approver = $pr_data['approver'];
        $page = 0;



        $c_mark1 = count($marks[1]);
        foreach ($p_po_items as $supplier => $po_items) {
            $keep = [];
            // var_dump($po_items[0]->po_no);exit;
            if (isset($po_no)) {
                if ($po_items[0]->po_no == $po_no) {


                    $num_rows = count($po_items);
                    $pages = ceil($num_rows / $items_per_page);
                    if ($pages == 1) {
                        $page++;
                        $keep[$pr_id][$page . '_1'] = $po_items;
                    } else {
                        for ($p = 1; $p <= $pages; $p++) {
                            $page++;
                            $keep[$pr_id][$page . '_' . $p] = array_slice($po_items, ((($p - 1) * $pages)), $items_per_page);
                        }
                    }
                }
            } else {



                $num_rows = count($po_items);
                $pages = ceil($num_rows / $items_per_page);
                if ($pages == 1) {
                    $page++;
                    $keep[$pr_id][$page . '_1'] = $po_items;
                } else {
                    for ($p = 1; $p <= $pages; $p++) {
                        $page++;
                        $keep[$pr_id][$page . '_' . $p] = array_slice($po_items, ((($p - 1) * $pages)), $items_per_page);
                    }
                }

            }

            $cal_payment = $this->Purchaserequests_model->get_pr_total_summary($pr_id, $supplier);
            // var_dump($cal_payment);exit;
            $trs_total = array();
            $vat_name = isset($cal_payment->tax_name) ? $cal_payment->tax_name : "ภาษีมูลค่าเพิ่ม 0%";
            if ($cal_payment->discount_total == '') {
                $trs_total[] = '
                        <tr>
                            <td colspan="3"></td>                            
                            <td colspan="2" style="text-align: right; margin-top: 4%"><span class="label">รวมเป็นเงิน</span></td>                            
                            <td style="text-align: right;">' . number_format($cal_payment->pr_subtotal, 2, '.', ',') . ' บาท</td>
                        </tr>
                        <tr>
                            <td colspan="3"></td> 
                            <td colspan="2" style="text-align: right;"><span class="label">' . $vat_name . '</span></td>
                            <td style="text-align: right;">' . number_format($cal_payment->tax, 2, '.', ',') . ' บาท</td>
                        </tr>
                        <tr>
                            
                            <td colspan="3" style="text-align: left;"><span>(' . $this->Convert($cal_payment->pr_total) . ')</span></td> 
                            
                            <td colspan="2" style="text-align: right;"><span class="label" >จำนวนเงินรวมทั้งสิ้น</span></td>
                            <td style="text-align: right;">' . number_format($cal_payment->pr_total, 2, '.', ',') . ' บาท</td>
                        </tr>

                    ';
            } else if ($cal_payment->discount_type == "before_tax") {
                $trs_total[] = '
                    <tr>
                        <td colspan="3" style="border-top: 1.5px solid #999;"></td>                            
                        <td colspan="2" style="text-align: right; margin-top: 4%; border-top: 1.5px solid #999;"><span class="label">รวมเป็นเงิน</span></td>                            
                        <td style="text-align: right; border-top: 1.5px solid #999;">' . number_format($cal_payment->pr_subtotal, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                        <td colspan="3"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label"><u>หัก</u> ส่วนลด</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->discount_total, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                        <td colspan="3"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label">ราคาหลังส่วนลด</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->pr_subtotal - $cal_payment->discount_total, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                        <td colspan="3"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label">' . $vat_name . '</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->tax, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                    
                        <td colspan="3" style="text-align: left;"><span>(' . $this->Convert($cal_payment->pr_total) . ')</span></td> 
                        
                        
                        <td colspan="2" style="text-align: right;"><span class="label" >จำนวนเงินรวมทั้งสิ้น</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->pr_total, 2, '.', ',') . ' บาท</td>
                    </tr>

                ';
            } else if ($cal_payment->discount_type == "after_tax") {

                $trs_total[] = '
                    <tr>
                        <td colspan="3" style="border-top: 1.5px solid #999;"></td>                            
                        <td colspan="2" style="text-align: right; border-top: 1.5px solid #999;"><span class="label">ราคาก่อนหักส่วนลด</span></td>                            
                        <td style="text-align: right; border-top: 1.5px solid #999;">' . number_format($cal_payment->pr_subtotal, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                        <td colspan="3"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label"><u>หัก</u> ส่วนลด</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->discount_total, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                        <td colspan="3"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label">' . $vat_name . '</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->tax, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                        <td colspan="3"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label">ราคาหลังส่วนลด</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->pr_subtotal - $cal_payment->discount_total, 2, '.', ',') . ' บาท</td>
                    </tr>
                    
                    <tr>
                    
                        <td colspan="3" style="text-align: left;"><span>(' . $this->Convert($cal_payment->pr_total) . ')</span></td> 
                        
                        <td colspan="2" style="text-align: right;"><span class="label" >จำนวนเงินรวมทั้งสิ้น</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->pr_total, 2, '.', ',') . ' บาท</td>
                    </tr>

                ';

            }


            foreach ($keep as $kd => $kv) {
                $i = 1;

                foreach ($kv as $kpage => $vpage) {
                    list($p_page, $p) = explode('_', $kpage);

                    $name = isset($vpage[0]->po_no) ? $vpage[0]->po_no : "PO" . date('Ymd');

                    $dbs = convertObJectToArray($vpage[0]);

                    $dbs = array_merge($dbs, [
                        'page' => $p . '/' . $pages,
                        'supplier_name' => $supplier,
                        'supplier_address' => $vpage[0]->address . ' ' . $po_items[0]->city . ' ' . $po_items[0]->state . ' ' . $po_items[0]->zip . ' ' . $po_items[0]->country . ' ' . $po_items[0]->website . ' ' . $po_items[0]->phone .
                            $po_items[0]->vat_number != '' ? 'หมายเลขภาษี : ' . $po_items[0]->vat_number : "",

                        'supplier_taxno' => '',
                        'pr_id' => $pr_id,
                        'pr_date' => $this->DateConvert($approver->doc_date),
                        'pr_total' => ($p == $pages) ? number_format($cal_payment->pr_total, 2, '.', ',') : '',
                        'pr_total_txt' => ($p == $pages) ? $this->Convert(doubleval($cal_payment->pr_total)) : '',
                        'note' => '',
                        'pr_subtotal' => ($p == $pages) ? number_format(doubleval($cal_payment->pr_subtotal), 2, '.', ',') : '',
                        'discount_total' => ($p == $pages) ? number_format(doubleval($cal_payment->discount_total), 2, '.', ',') : '',
                        'tax_name' => ($p == $pages) ? (isset($cal_payment->tax_name) ? $cal_payment->tax_name : "-") : '',
                        'tax' => ($p == $pages) ? number_format(doubleval($cal_payment->tax), 2, '.', ',') : '',
                        'tax_name2' => ($p == $pages) ? (isset($cal_payment->tax_name2) ? $cal_payment->tax_name2 : "-") : '',
                        'tax2' => ($p == $pages) ? number_format(doubleval($cal_payment->tax2), 2, '.', ',') : '',
                        'amout_name' => '<span class="label">รวมเงิน</span>',
                        'vat_name' => '<span class="label">' . ($p == $pages) ? (isset($cal_payment->tax_name) ? '<span class="label">' . $cal_payment->tax_name . '</span>' : "-") : '-' . '</span>',
                        'total_name' => '<span class="label">รวมเงินทั้งสิ้น</span>',
                        'supplier_name_new' => 'ในนาม ' . $supplier
                    ]);

                    $divs = array();

                    //หัวกระดาษ
                    foreach ($marks[1] as $km => $vm) {
                        $vm['[align]'] = isset($vm['[align]']) ? $vm['[align]'] : 'left';
                        $vm['[val]'] = isset($dbs[$vm['key']]) ? $dbs[$vm['key']] : $vm['[val]'];
                        $head = str_replace(array_keys($vm), $vm, $template);
                        //$head = str_replace('page', $p.'/'.$pages, $head);
                        $divs[] = $head;
                    }
                    $top = 315;
                    $trs = array();

                    foreach ($vpage as $vkpage => $vvpage) {

                        if ($vvpage->title) {
                            $trs[] = '
                                <tr>
                                    <td style="width: 5%; border-bottom: 1px solid #999;">' . $i++ . '</td>
                                    <td style="text-align: left; width: 55%; border-bottom: 1px solid #999;">' . $vvpage->title . '<br/>' . $vvpage->description . '</td>
                                    <td style="border-bottom: 1px solid #999;">' . number_format($vvpage->quantity, 0, '.', ',') . '</td>
                                    <td style="text-align: right; border-bottom: 1px solid #999;">' . $vvpage->unit_type . '</td>                                
                                    <td style="text-align: right; border-bottom: 1px solid #999;">' . number_format($vvpage->rate, 2, '.', ',') . '</td>
                                    <td style="text-align: right; width: 17%; border-bottom: 1px solid #999;">' . number_format($vvpage->total, 2, '.', ',') . '</td>
                                </tr>
                                ';
                        } else {
                            $trs[] = '
                                <tr>
                                    <td style="width: 5%;"></td>
                                    <td style="width: 60%"></td>
                                    <td style=" width: 6%;"></td>
                                    <td style=" width: 6%;"></td>                                
                                    <td></td>
                                    <td style="text-align: right;"></td>
                                </tr>
                                ';
                        }


                        $marks[2] = array(); //สร้าง array แยกแต่ละรายการ
                        // $top += 29;
                        // $left = 40; 
                        // $marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 37, '[top]' => $top, '[val]' => $i++);                    
                        // $left += 42;
                        // $marks[2][] = array('key' => 'title', '[left]' => $left, '[w]' =>450, '[top]' => $top, '[val]' => 'ไม่มีรายการสินค้า', '[align]' => 'left');
                        // $left += 400;
                        // $marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 50, '[top]' => $top, '[val]' => number_format($vvpage->quantity,0,'.',','));
                        // $left += 49;
                        // $marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 50, '[top]' => $top, '[val]' => isset($vvpage->unit_type)? $vvpage->unit_type : '-');
                        // $left += 53;
                        // $marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 85, '[top]' => $top, '[val]' => number_format($vvpage->rate,2,'.',','));
                        // $left += 90;
                        // $marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 80, '[top]' => $top, '[val]' => number_format($vvpage->total,2,'.',','),'[align]' => 'right');

                        $dbs = convertObJectToArray($vvpage);

                        foreach ($marks[2] as $km => $vm) {
                            $vm['[align]'] = isset($vm['[align]']) ? $vm['[align]'] : 'center';
                            $vm['[val]'] = isset($dbs[$vm['key']]) ? $dbs[$vm['key']] : $vm['[val]'];
                            $divs[] = str_replace(array_keys($vm), $vm, $template);
                        }

                    }


                    $trs_note = array();
                    if ($pr_data['pr_info']->note) {
                        $trs_note[] = '
                            <tr>
                                <td colspan="6" style="text-align: left;"><br/><p class="label">หมายเหตุ : </p>' . nl2br($pr_data['pr_info']->note) . '</td>
                            </tr>
                        ';
                    }

                    // arr($po_items); exit;
                    $divTop = 110;
                    $divleft = 27;
                    $vat = $po_items[0]->vat_number != null ? 'เลขประจำตัวผู้เสียภาษี ' . $po_items[0]->vat_number : '';
                    $trs_client = '
                        <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 370px">
                            <table style="width: 100%; ">
                                
                                <tr>
                                    <td style="text-align: left;">' . $company_name . '<br/>' . $company_address . '<br/>หมายเลขประจำตัวผู้เสียภาษี ' . $company_vat_number . '<br/>' . $company_phone . '</td>
                                </tr>
                                <tr>
                                    <td style="text-align: left;"><span class="label"><br/>ผู้จำหน่าย</span></td>
                                </tr>
                                <tr>
                                    <td style="text-align: left;">' . $supplier . '<br/>' . $po_items[0]->address . ' ' . $po_items[0]->city . ' ' . $po_items[0]->state . ' ' . $po_items[0]->zip . ' ' . $po_items[0]->country . '<br/>' . $vat . '</td>
                                </tr>
                            </table>
                        </div>
                        ';

                    $divTop = 990;
                    $divleft = 27;
                    $trs_approve = '<div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 750px; ">
                            <table>
                                <tr>
                                    <td colspan="2">ในนาม ' . $supplier . '</td>
                                    <td></td>
                                    <td colspan="2">ในนาม ' . $company_name . '</td>
                                </tr>
                                <tr>
                                    <td style="height: 100px;">___________________________<p>ผู้ขาย</p></td>
                                    <td>___________________________<p>วันที่</p></td>
                                    <td style="width: 10%;"></td>
                                    <td>___________________________<p>ผู้อนุมัติ</p></td>
                                    <td>___________________________<p>วันที่</p></td>
                                </tr>                            
                            </table>
                        </div>';
                    $su_id = $vpage[0]->supplier_id;
                    $sql_sup = "SELECT *                         
                                    FROM bom_suppliers
                                    LEFT JOIN bom_supplier_contacts on bom_supplier_contacts.supplier_id = bom_suppliers.id
                                    WHERE bom_suppliers.id = $su_id";
                    $a = $this->db->query($sql_sup)->row();
                    $contact_name = isset($a->first_name) ? $a->first_name . ' ' . $a->last_name : "-";
                    $contact_phone = isset($a->phone) ? $a->phone : "-";
                    $contact_email = isset($a->email) ? $a->email : "-";
                    $pro_name = !empty($pr_data['pr_info']->project_name) ? $pr_data['pr_info']->project_name : "-";

                    // var_dump($pr_data['pr_info']->project_name);exit;
                    // var_dump($a);
                    $info_contact = array();

                    $info_contact[] = '
                            <tr>
                                <td style="text-align: left; width: 50px; padding-left: 5px;"><span class="label">ชื่องาน</span></td>
                                <td style="text-align: left;"><span>' . $pro_name . '</span></td>
                            </tr>
                            <tr>
                                <td style="text-align: left; width: 50px; padding-left: 5px;"><span class="label">ผู้ติดต่อ</span></td>
                                <td style="text-align: left;"><span>' . $contact_name . '</span></td>
                            </tr>
                            <tr>
                                <td style="text-align: left; padding-left: 5px;"><span class="label">เบอร์โทร</span></td>
                                <td style="text-align: left;"><span>' . $contact_phone . '</span></td>
                            </tr>
                            <tr>
                                <td style="text-align: left; padding-left: 5px;"><span class="label">อีเมล</span></td>
                                <td style="text-align: left;"><span>' . $contact_email . '</span></td>
                            </tr>';


                    $data = $this->Db_model->creatBy($pr_id, "purchaserequests");
                    $fname = $data->first_name;
                    $lname = $data->last_name;



                    $expired = !empty($data->expired) ? $this->DateConvert($data->expired) : "-";



                    $divTop = 25;
                    $divleft = 410;
                    $info_table = '
                            <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 340px">
                                <table style="width: 100%;">
                                    <tr>
                                        <th colspan="4" style="text-align: center; border-bottom: 1.5px solid #999; font-size: 30px;"><span class="label">ใบสั่งซื้อ</span></th>
                                        
                                    </tr>
                                    <tr>
                                        <td style="text-align: left; padding-left: 5px; width: 100px;"><span class="label">เลขที่</span></td>
                                        <td style="text-align: left;"><span>' . $name . '</span></td>
                                        <td style="text-align: left;"><span class="label">หน้า</span></td>
                                        <td style="text-align: left;"><span>' . $p . '/' . $pages . '</span></td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: left; padding-left: 5px;"><span class="label">วันที่</span></td>
                                        <td style="text-align: left;"><span>' . $this->DateConvert($data->pr_date) . '</span></td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: left; padding-left: 5px;"><span class="label">เครดิต</span></td>
                                        <td style="text-align: left;"><span> ' . $data->credit . ' วัน</span></td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: left; padding-left: 5px;"><span class="label">ครบกำหนด</span></td>
                                        <td style="text-align: left;"><span>' . $expired . '</span></td>
                                    </tr>

                                    <tr>
                                        <td style="text-align: left; padding-left: 5px;"><span class="label">ผู้สั่งซื้อ</span></td>
                                        <td style="text-align: left;"><span>' . $fname . ' ' . $lname . '</span></td>
                                    </tr>
                                    
                                    <tr>
                                        <td style="text-align: left; border-bottom: 1.5px solid #999; padding-left: 5px;"><span class="label">อ้างอิงใบขอซื้อ</span></td>
                                        <td style="text-align: left; border-bottom: 1.5px solid #999;"><span>' . $data->doc_no . '</span></td>
                                        <td style="text-align: left; border-bottom: 1.5px solid #999;"></td>
                                        <td style="text-align: left; border-bottom: 1.5px solid #999;"></td>
                                    </tr>

                                    <tr>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                    </tr>
                                    ' . implode("", $info_contact) . '
                                    
                                    
                                </table>
                            </div>';

                    $divTop = 315;
                    $divleft = 30;
                    $tabletemplate = '
                            <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 750px">
                                <table>
                                    <tr>
                                        <td class="thStyle">#</td>
                                        <td class="thStyle">รายละเอียด</td>
                                        <td class="thStyle">จำนวน</td>
                                        <td class="thStyle"></td>
                                        <td class="thStyle" style="text-align: right;">ราคาต่อหน่วย</td>
                                        <td class="thStyle" style="text-align: right;">ยอดรวม</td>
                                    </tr>
                                    ' . implode("", $trs) . '
                                    ' . implode("", $trs_total) . '
                                    ' . implode("", $trs_note) . '
                                </table>
                            </div>';

                    $html = ' 
                                <style>
                                div{
                                    font-size: 17px;
                                    // border: solid 1px #000;
                                    // font-weight:bold;
                                    text-align: center;
                                }
                                img{
                                    width: 200px;
                                    height: 100px;
                                }
                                .label{
                                    color:  #800000;
                                    // font-weight: bold;
                                }
                                table {
                                    font-size: 17px;
                                    // border: solid 1px #000;
                                    width: 735px; 
                                    border-collapse: collapse;
                                }
                                th,td{
                                    text-align: center;
                                    // border: solid 1px #000;
                                }
                                .thA{
                                    width: 10%;
                                }
        
                                .thStyle{
                                    border-top: 2px solid #999;
                                    border-bottom: 2px solid #999;
                                }

                                </style>
                                ' . $trs_client . '
                                ' . $info_table . '
                                ' . $tabletemplate . '
                                ' . $trs_approve . '
                                ' . implode('', $divs) . '
                            ';
                    $mpdf->SetTitle($data->doc_no);
                    $mpdf->AddPage('P');
                    $pagecount = $mpdf->SetSourceFile('pdf_Template/main_template.pdf');
                    $tplId = $mpdf->importPage(1);
                    $mpdf->useTemplate($tplId);
                    $mpdf->WriteHTML($html);
                }
            }
        }
        //exit;
        // $mpdf->Output();

        $mpdf->Output($name . '.pdf', \Mpdf\Output\Destination::DOWNLOAD);

    }

    function project_materials_pdf($project_id)
    {
        $pdfCtl = &get_instance();
        $view_data['can_read_price'] = true; //$pdfCtl->check_permission('bom_restock_read_price');

        $view_data['label_column'] = "col-md-3";
        $view_data['field_column'] = "col-md-9";

        $view_data["view"] = $pdfCtl->input->post('view');
        $view_data['model_info'] = $pdfCtl->Projects_model->get_one($project_id);


        $view_data['items'] = $pdfCtl->Items_model->get_items([])->result();
        foreach ($view_data['items'] as $item) {
            unset($item->files);
            unset($item->description);
            // var_dump($view_data['items']);
        }
        // exit;
        $view_data['item_mixings'] = $pdfCtl->Bom_item_mixing_groups_model->get_detail_items([
            'for_client_id' => $view_data['model_info']->client_id
        ])->result();

        $view_data['project_items'] = $pdfCtl->Bom_item_mixing_groups_model
            ->get_project_items(['project_id' => $view_data['model_info']->id])->result();

        $view_data['project_materials'] = $pdfCtl->Bom_item_mixing_groups_model
            ->get_project_materials($view_data['project_items']);

        // arr($view_data['project_materials']);exit;

        $trs_materials = array();
        $table_stock = '';
        $keep = array();
        $rangItem = 10;
        $info = $view_data['model_info'];

        $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();

        $fontDirs = $defaultConfig['fontDir'];
        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'fontDir' => array_merge($fontDirs, [
                __DIR__ . '/fonts',
            ]),
            'fontdata' => $fontData + [
                'def' => [
                    'R' => 'THSarabun_Bold.ttf',
                    //'I' => 'THSarabunNew Italic.ttf',
                    //'B' => 'THSarabunNew Bold.ttf',
                ]
            ],
            'default_font' => 'def',
            'tempDir' => '/tmp'
        ]);
        $mpdf->charset_in = 'UTF-8';
        $mpdf->SetTitle('project-' . $project_id);
        $html = '';

        foreach ($view_data['project_materials'] as $parentKey => $child) {

            if (!isset($keep[$child->project_id])) {
                $page = 1;
            }

            $keep[$child->project_id][$page][] = $child;

            if (count($keep[$child->project_id][$page]) == $rangItem) {
                $page += 1;
            }

        }




        if ($keep) {


            foreach ($keep as $kd => $kv) {
                // var_dump($kv);

                foreach ($kv as $kpage => $vpage) {


                    $trs_materials = array();
                    foreach ($vpage as $vkpage => $vvpage) {

                        $mixing_name = isset($vvpage->mixing_name) ? $vvpage->mixing_name : '-';

                        if (isset($vvpage->result)) {

                            $trs_materials[] = '
                            <tr>
                                <td>' . $vvpage->title . '</td>
                                <td>' . $mixing_name . '</td>
                                <td>' . to_decimal_format2($vvpage->quantity) . ' ' . $vvpage->unit_type . '</td> 
                            </tr>
                        ';
                            $trs_materials[] = '
                                    <tr>
                                        <td colspan="3">
                                            <div class="toggle-container">
                                                <table class="sub_mat" style="width: 100%" >
                                                <tr>
                                                    <th class="sub_thStyle" style="text-align: center;">' . lang('stock_material') . '</th>
                                                    <th class="sub_thStyle" style="text-align: center;">' . lang('stock_material_name') . '</th>
                                                    <th class="sub_thStyle" style="text-align: center;">' . lang('stock_restock_name') . '</th>
                                                    <th class="sub_thStyle" style="text-align: center;">' . lang('quantity') . '</th>
                                                    <th class="sub_thStyle" style="text-align: center;">' . lang('stock_calculator_value') . '</th>
                                                </tr>
                                               
                                    ';

                            $total = 0;
                            foreach ($vvpage->result as $rk) {
                                $stock_name = isset($rk->stock_name) ? $rk->stock_name : '-';
                                $rk->ratio = floatval($rk->ratio);
                                $classer = 'red';
                                if ($rk->ratio > 0) {
                                    $classer = 'green';
                                }

                                if ($vvpage->id == $rk->bpim_Pid) {
                                    $trs_materials[] = '                                    
                                        <tr>                                            
                                            <td>' . $rk->material_name . '</td>
                                            <td>' . $rk->material_desc . '</td>
                                            <td>' . $stock_name . '</td>
                                            <td style="text-align: right; color:' . $classer . '">' . to_decimal_format2($rk->ratio) . ' ' . $rk->material_unit . '</td>
                                                               
                                    ';
                                    if ($rk->value != 0) {
                                        $total += $rk->value;
                                        $trs_materials[] = '
                                        <td style="text-align: right;">' . to_currency($total) . '</td>
                                        </tr> 
                                        ';
                                    } else {
                                        $trs_materials[] = '
                                        <td style="text-align: right;">-</td>
                                        </tr> 
                                        ';
                                    }
                                }

                            }

                            $trs_materials[] = '
                                <tr>
                                    <th colspan="3"></th>
                                    <th style="text-align: right;">' . lang('total') . '</th>
                                    <td style="text-align: right;">' . to_currency($total) . '</td>
                                </tr>
                                </table>
                                </div>
                            </td>
                        </tr>
                        ';


                        } else {
                            $trs_materials[] = '
                            <tr>
                                <td >' . $vvpage->title . '</td>
                                <td >' . $mixing_name . '</td>
                                <td >' . to_decimal_format2($vvpage->quantity) . ' ' . $vvpage->unit_type . '</td> 
                            </tr>
                        ';
                        }



                    }

                    $titleLength = strlen($info->title);
                    $divTop = 190;
                    $divleft = 27;
                    if ($titleLength > 100) {
                        $divTop += 20;
                    }
                    if ($titleLength < 45) {
                        $divTop -= 20;
                    }
                    $table_stock = '         
    
                    <div style="position:absolute; top:' . $divTop . 'px; left: ' . $divleft . 'px; width: 100%">
                        <table>
                            <tr>
                                <th class="thStyle" style="text-align: center;">' . lang('item') . '</th>
                                <th class="thStyle" style="text-align: center;">' . lang('item_mixing_name') . '</th>
                                <th class="thStyle" style="text-align: center;">' . lang('quantity') . '</th>
                            </tr>
                           
                            ' . implode(" ", $trs_materials) . '
                            
                        </table>
                    </div>
                    ';

                    $divTop = 40;
                    $divleft = 480;
                    $table_info = '
                        <div style="position:absolute; top:' . $divTop . 'px; left: ' . $divleft . 'px; width: 750px">
                            <table style="width: 270px; border: none;">
                                <tr>
                                        <th colspan="2" style="text-align: center; border: none; border-bottom: 1.5px solid #999; font-size: 30px; "><span class="label" style="font-weight:bold;">โครงการ</span></th>
                                </tr>
                                <tr>
                                    <td style="border: none; vertical-align: top; width: 66px;"> <span class="label">เลขที่</span></td>
                                    <td style="border: none;">' . $info->id . '</td>
                                   
                                </tr>
                                <tr>
                                    <td style="border: none; vertical-align: top;"><span class="label">ชื่อโครงการ</span></td>
                                    <td style="border: none;">' . $info->title . '</td>
                                </tr>
                                <tr>
                                    <td style="border: none; vertical-align: top;"><span class="label">วันที่</span></td>
                                    <td style="border: none;">' . $this->DateConvert($info->created_date) . '</td>
                                </tr>
                            </table>
                        </div>
                    ';

                    $html = '<style>
                    div{
                        font-size: 17px;
                        // border: solid 1px #000;
                        // font-weight:bold;
                        text-align: center;
                    }
                    table {
                        font-size: 18px;
                        border: solid 1px #000;
                        width: 735px; 
                        border-collapse: collapse;
                    }
                    th,td{
                        text-align: left;
                        border: solid 1px #000;
                    }
                    
                    .thStyle{
                        background-color : #ccffcc;
                        border-top: 2px solid #999;
                        border-bottom: 2px solid #999;
                        color: #5900b3;
                    }
                    .sub_thStyle{
                        background-color : #ccffcc;
                        border-top: 2px solid #999;
                        border-bottom: 2px solid #999;
                        color: #5900b3;
                    }
                    .sub_mat{
                        margin: 10px;
                        border: solid 1px #000;
                    }
                    .label{
                        color: #5900b3;
                    }
                    .info_tb{
                        // border: solid 1px #000;
                    }
                    </style>
                    
                    ' . $table_stock . '
                    ' . $table_info . '
                    ';


                    // echo $html;exit;

                    $mpdf->AddPage('P');
                    // var_dump($mpdf->AddPage('P'));exit;
                    $pagecount = $mpdf->SetSourceFile('pdf_Template/main_template.pdf');
                    $tplId = $mpdf->importPage(1);
                    ;
                    $mpdf->useTemplate($tplId);
                    $mpdf->WriteHTML($html);

                }
                $mpdf->Output();
                // $mpdf->Output('project-'.$project_id.'.pdf', \Mpdf\Output\Destination::DOWNLOAD); 
            }
        } else {
            $divTop = 170;
            $divleft = 27;
            $table_stock = '          

                    <div style="position:absolute; top:' . $divTop . 'px; left: ' . $divleft . 'px; width: 750px">
                        <table>
                            <tr>
                                <th class="thStyle" style="text-align: center;">' . lang('item') . '</th>
                                <th class="thStyle" style="text-align: center;">' . lang('item_mixing_name') . '</th>
                                <th class="thStyle" style="text-align: center;">' . lang('quantity') . '</th>
                            </tr>
                            <tr>
                                <td colspan="3" style="text-align: center;">ไม่มีรายการวัตถุดิบ</td>
                            </tr>
                        </table>
                    </div>
                    ';

            $divTop = 40;
            $divleft = 526;
            $table_info = '
                    <div style="position:absolute; top:' . $divTop . 'px; left: ' . $divleft . 'px; width: 750px">
                        <table style="width: 230px; border: none;">
                            <tr>
                                    <th colspan="2" style="text-align: center; border: none; border-bottom: 1.5px solid #999; font-size: 30px; "><span class="label" style="font-weight:bold;">วัตถุดิบโครงการ</span></th>
                                    
                            </tr>
                            <tr>
                                <td style="border: none;"> <span class="label">เลขที่</span></td>
                                <td style="border: none;">' . $info->id . '</td>
                               
                            </tr>
                            <tr>
                                <td style="border: none;"><span class="label">ชื่อโครงการ</span></td>
                                <td style="border: none;">' . $info->title . '</td>
                            </tr>
                            <tr>
                                <td style="border: none;"><span class="label">วันที่</span></td>
                                <td style="border: none;">' . $this->DateConvert($info->created_date) . '</td>
                            </tr>
                        </table>
                    </div>
                ';

            $html = '<style>
                    div{
                        font-size: 17px;
                        // border: solid 1px #000;
                        // font-weight:bold;
                        text-align: center;
                    }
                    table {
                        font-size: 18px;
                        // border: solid 1px #000;
                        width: 735px; 
                        border-collapse: collapse;
                    }
                    th,td{
                        text-align: left;
                        border: solid 1px #000;
                    }
                    
                    .thStyle{
                        background-color : #ccffcc;
                        color: #5900b3;
                    }
                    .sub_thStyle{
                        background-color : #ccffcc;
                        color: #5900b3;
                    }
                    .sub_mat{
                        margin: 10px;
                        border: solid 1px #000;
                    }
                    .label{
                        color: #5900b3;
                    }
                    .info_tb{
                        // border: solid 1px #000;
                    }
                    </style>
                    
                    ' . $table_stock . '
                    ' . $table_info . '
                    ';



            $mpdf->AddPage('P');
            // var_dump($mpdf->AddPage('P'));exit;
            $pagecount = $mpdf->SetSourceFile('pdf_Template/main_template.pdf');
            $tplId = $mpdf->importPage(1);
            // $mpdf->shrink_tables_to_fit = 0.5;
            $mpdf->useTemplate($tplId);
            $mpdf->WriteHTML($html);
            $mpdf->Output();
            // $mpdf->Output('project-'.$project_id.'.pdf', \Mpdf\Output\Destination::DOWNLOAD);
        }


    }

    public function payment_vouchers_pdf($invoice_id = 0)
    {

        //print_r($invoice_id); exit;

        $template = '<div style="text-align: [align]; position:absolute; top:[top]px; left:[left]px; width:[w]px; height:[h]px;"><span>[val]</span></div>';

        $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'fontDir' => array_merge($fontDirs, [
                __DIR__ . '/fonts',
            ]),
            'fontdata' => $fontData + [
                'def' => [
                    'R' => 'THSarabun.ttf',
                    //'I' => 'THSarabunNew Italic.ttf',
                    //'B' => 'THSarabunNew Bold.ttf',
                ]
            ],
            'default_font' => 'def',
            'tempDir' => '/tmp'
        ]);
        $mpdf->charset_in = 'UTF-8';


        $html = '';
        $company_address = nl2br(get_setting("company_address"));
        $company_phone = get_setting("company_phone");
        $company_website = get_setting("company_website");
        $company_vat_number = get_setting("company_vat_number");
        $company_name = get_setting("company_name");

        // $sql = "SELECT *,payment_vouchers.id as pvId, clients.id as clientId, payment_voucher_payments.note as detail
        // FROM `payment_vouchers` 
        // LEFT JOIN payment_voucher_payments ON payment_voucher_payments.payment_vouchers_id = payment_vouchers.id
        // LEFT JOIN orders ON payment_voucher_payments.invoice_id = orders.id
        // LEFT JOIN clients ON orders.client_id = clients.id
        // WHERE payment_vouchers.id = $invoice_id AND payment_voucher_payments.deleted=0";

        $sql = " SELECT *,payment_vouchers.doc_no as pvId, bom_suppliers.id as supplierId, payment_voucher_payments.note as detail
            FROM `payment_vouchers` 
            LEFT JOIN payment_voucher_payments ON payment_voucher_payments.payment_vouchers_id = payment_vouchers.id
            LEFT JOIN orders ON payment_voucher_payments.invoice_id = orders.id
            LEFT JOIN bom_suppliers ON orders.supplier_id = bom_suppliers.id
            LEFT JOIN bank ON payment_voucher_payments.bank_id = bank.id
            WHERE payment_vouchers.id = $invoice_id AND payment_voucher_payments.deleted=0";
        // AND estimate_items.deleted = 0
        //หัวตารากระดาษ------------------------------------------------------------------------------------------------------------
        $top = 107;
        $left = 35;
        $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 420, '[top]' => $top, '[val]' => $company_name, '[align]' => 'left');
        $top += 25;
        $marks[1][] = array('key' => 'company_address', '[left]' => $left, '[w]' => 420, '[top]' => $top, '[val]' => $company_address);
        $top += 25;
        $marks[1][] = array('key' => 'tax', '[left]' => $left, '[w]' => 420, '[top]' => $top, '[val]' => 'หมายเลขประจำตัวผู้เสียภาษี : ' . $company_vat_number);
        $top += 25;
        $marks[1][] = array('key' => 'Tel_company', '[left]' => $left, '[w]' => 420, '[top]' => $top, '[val]' => $company_phone);


        $top = 220;
        $left = 35;
        foreach ($this->Db_model->fetchAll($sql) as $kcus => $vcus) {
            // var_dump($vcus);exit;

            $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 420, '[top]' => $top, '[val]' => $vcus->company_name);

            $top += 30;
            $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 420, '[top]' => $top, '[val]' => $vcus->address . ' ' . $vcus->city . '<br/> ' . $vcus->state . ' ' . $vcus->zip . ' ' . $vcus->country);

            $top += 45;
            $left = 35;
            $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 420, '[top]' => $top, '[val]' => 'เลขประจำตัวผู้เสียภาษี : ' . $vcus->vat_number);
            break;
        }

        $top = 65;
        $left = 630;
        // var_dump($sql);exit;
        foreach ($this->Db_model->fetchAll($sql) as $dd) {
            $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 80, '[top]' => $top, '[val]' => $dd->pvId);
            $top += 25;
            $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 80, '[top]' => $top, '[val]' => $this->DateConvert($dd->bill_date));
            $top += 100;
            $left = 630;
            $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 80, '[top]' => $top, '[val]' => $dd->project_name);
            break;
        }


        $top = 220;
        $left = 545;
        // foreach($this->Db_model->fetchAll($sql) as $method => $id){
        //     if($id->payment_method_id == '1'){
        //         $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 150, '[top]' => $top, '[val]' => 'เงินสด');
        //     }
        //     if($id->payment_method_id == '2'){
        //         $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 150, '[top]' => $top, '[val]' => 'ระบบชำระเงิน Stripe');
        //     }
        //     if($id->payment_method_id == '3'){
        //         $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 150, '[top]' => $top, '[val]' => 'PayPal');
        //     }
        //     if($id->payment_method_id == '4'){
        //         $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 150, '[top]' => $top, '[val]' => 'ระบบชำระเงิน Paytm');
        //     }
        //     if($id->payment_method_id == '5'){
        //         $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 150, '[top]' => $top, '[val]' => 'โอนเงิน');
        //     }
        //     if($id->payment_method_id == '6'){
        //         $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 150, '[top]' => $top, '[val]' => 'บัตรเครดิต/บัตรเดบิต');
        //     }
        //     break;
        // }
        // $marks[1][] = array('key' => 'Work_name', '[left]' => $left, '[w]' => 180, '[top]' => $top, '[val]' => 'ชื่องาน');
        //$marks[1][] = array('key' => 'Work_name', '[left]' => $left, '[w]' => 180, '[top]' => $top, '[val]' => '-');
        //$top += 25;
        // $marks[1][] = array('key' => 'date', '[left]' => $left, '[w]' => 180, '[top]' => $top, '[val]' => 'ผู้ติดต่อ');
        //$marks[1][] = array('key' => 'date', '[left]' => $left, '[w]' => 180, '[top]' => $top, '[val]' => '-');
        //$top += 25;
        // $marks[1][] = array('key' => 'Tel', '[left]' => $left, '[w]' => 180, '[top]' => $top, '[val]' => 'เบอร์โทร');
        //$marks[1][] = array('key' => 'Tel', '[left]' => $left, '[w]' => 180, '[top]' => $top, '[val]' => '-');
        //$top += 25;
        //$left = 600;
        // $marks[1][] = array('key' => 'email', '[left]' => $left, '[w]' => 180, '[top]' => $top, '[val]' => 'E-mail');
        //$marks[1][] = array('key' => 'email', '[left]' => $left, '[w]' => 180, '[top]' => $top, '[val]' => '-');



        $se_id = $this->Db_model->fetchAll($sql);
        $order_id = $se_id[0]->invoice_id;
        // print_r($order_id);exit;

        $cal_payment = $this->Payment_vouchers_model->get_invoice_total_summary($invoice_id, $order_id);

        // var_dump($cal_payment);exit;
        $top = 735;
        $left = 25;
        //$marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 323, '[top]' => $top, '[val]' => '('.$this->Convert($cal_payment->sumvat3).')');
        // $top += 30;
        // $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 50, '[top]' => $top, '[val]' => 'ชำระโดย : ');
        // $left += 50;
        // foreach($this->Db_model->fetchAll($sql) as $method => $id){
        //     if($id->payment_method_id == '1'){
        //         $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 42, '[top]' => $top, '[val]' => 'เงินสด');
        //     }
        //     if($id->payment_method_id == '2'){
        //         $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 42, '[top]' => $top, '[val]' => 'ระบบชำระเงิน Stripe');
        //     }
        //     if($id->payment_method_id == '3'){
        //         $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 42, '[top]' => $top, '[val]' => 'PayPal');
        //     }
        //     if($id->payment_method_id == '4'){
        //         $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 42, '[top]' => $top, '[val]' => 'ระบบชำระเงิน Paytm');
        //     }
        //     if($id->payment_method_id == '5'){
        //         $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 42, '[top]' => $top, '[val]' => 'เงินโอน');
        //         $left += 42;
        //         $marks[1][] = array('key' => 'name_bank', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => '');
        //         $left += 100;
        //         $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 50, '[top]' => $top, '[val]' => 'เลขที่บัญชี');
        //         $left += 50;
        //         $marks[1][] = array('key' => 'number_bank', '[left]' => $left, '[w]' => 80, '[top]' => $top, '[val]' => '');
        //     }
        //     if($id->payment_method_id == '6'){
        //         $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 42, '[top]' => $top, '[val]' => 'บัตรเครดิต/บัตรเดบิต');
        //     }
        //     if($id->payment_method_id == '7'){
        //         $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 42, '[top]' => $top, '[val]' => 'เช็ค');
        //         $left += 42;
        //         $marks[1][] = array('key' => 'name_bank', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => '');
        //         $left += 100;
        //         $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 50, '[top]' => $top, '[val]' => 'เลขที่');
        //         $left += 50;
        //         $marks[1][] = array('key' => 'number_bank', '[left]' => $left, '[w]' => 80, '[top]' => $top, '[val]' => '');
        //         $top += 30;
        //         $left = 25;
        //         $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 30, '[top]' => $top, '[val]' => 'วันที่');
        //         $left += 30;
        //         $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 80, '[top]' => $top, '[val]' => ''.$this->DateConvert($id->due_date).'');
        //     }
        //     break;
        // }
        //$marks[1][] = array('key' => 'payment_method_id', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => '');

        // $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => lang("sub_total"),'[align]' => 'right');
        // $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => 'จำนวนเงินรวมทั้งสิ้น','[align]' => 'left');
        // $top = 735;
        // $left = 530;
        // $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => 'รวมเป็นยอดชำระ','[align]' => 'right');
        // $left = 660;
        // $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' =>  number_format($cal_payment->invoice_total,2,'.',',').' บาท','[align]' => 'right');
        // $top += 20;
        // $left = 530;
        // $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => 'ภาษีมูลค่าเพื่ม 7%','[align]' => 'right');
        // $left = 660;
        // $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' =>  number_format($cal_payment->tolvat7,2,'.',',').' บาท','[align]' => 'right');
        // $top += 20;
        // $left = 530;
        // $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => 'จำนวนเงินรวมทั้งสิ้น','[align]' => 'right');
        // $left = 660;
        // $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' =>  number_format($cal_payment->sumvat7,2,'.',',').' บาท','[align]' => 'right');

        // $top += 20;
        // $left = 530;
        // $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => 'ภาษีหัก ณ ที่จ่าย 3%','[align]' => 'right');
        // $left = 660;
        // $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' =>  number_format($cal_payment->tolvat3,2,'.',',').' บาท','[align]' => 'right');
        // $top += 20;
        // $left = 530;
        // $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => 'ยอดชำระ','[align]' => 'right');
        // $left = 660;
        // $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' =>  number_format($cal_payment->sumvat3,2,'.',',').' บาท','[align]' => 'right');
        // $left = 25;
        $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 323, '[top]' => $top, '[val]' => '(' . $this->Convert($cal_payment->sumvat3) . ')');


        $top += 30;
        $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 735, '[top]' => $top, '[val]' => '<hr>');
        $top += 20;
        $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 50, '[top]' => $top, '[val]' => 'ชำระโดย : ');
        $left += 50;
        foreach ($this->Db_model->fetchAll($sql) as $method => $id) {
            if ($id->payment_method_id == '1') {
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 42, '[top]' => $top, '[val]' => 'เงินสด');
            }
            if ($id->payment_method_id == '2') {
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 42, '[top]' => $top, '[val]' => 'ระบบชำระเงิน Stripe');
            }
            if ($id->payment_method_id == '3') {
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 42, '[top]' => $top, '[val]' => 'PayPal');
            }
            if ($id->payment_method_id == '4') {
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 42, '[top]' => $top, '[val]' => 'ระบบชำระเงิน Paytm');
            }
            if ($id->payment_method_id == '5') {
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 42, '[top]' => $top, '[val]' => 'เงินโอน');
                $left += 42;
                $marks[1][] = array('key' => 'name_bank', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => '');
                $left += 100;
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 50, '[top]' => $top, '[val]' => 'เลขที่บัญชี');
                $left += 50;
                $marks[1][] = array('key' => 'number_bank', '[left]' => $left, '[w]' => 80, '[top]' => $top, '[val]' => '');
            }
            if ($id->payment_method_id == '6') {
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 42, '[top]' => $top, '[val]' => 'บัตรเครดิต/บัตรเดบิต');
            }
            if ($id->payment_method_id == '7') {
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 42, '[top]' => $top, '[val]' => 'เช็ค');
                $left += 42;
                $marks[1][] = array('key' => 'name_bank', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => '');
                $left += 100;
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 50, '[top]' => $top, '[val]' => 'เลขที่');
                $left += 50;
                $marks[1][] = array('key' => 'number_bank', '[left]' => $left, '[w]' => 80, '[top]' => $top, '[val]' => '');
                $top += 20;
                $left = 25;
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 30, '[top]' => $top, '[val]' => 'วันที่');
                $left += 30;
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 80, '[top]' => $top, '[val]' => '' . $this->DateConvert($id->due_date) . '');
            }
            break;
        }






        // $top = 760;
        // $left = 572;
        // $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => '<u>หัก</u> ภาษี ณ ที่จ่าย 3%','[align]' => 'left');
        // $left = 583;
        // $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 180, '[top]' => $top, '[val]' =>  number_format($cal_payment->vat3,2,'.',','),'[align]' => 'right');
        // $top = 788;
        // $left = 572;
        // $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => 'ยอดที่ต้องชำระ','[align]' => 'left');
        // $left = 583;
        // $marks[1][] = array('key' => 'tt_sum', '[left]' => $left, '[w]' => 180, '[top]' => $top, '[val]' =>  number_format($cal_payment->tol,2,'.',','),'[align]' => 'right');
        // ==========================================

        if (true) {
            if ($cal_payment->discount_type == "before_tax") {
                // $top += 20;
                // $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => lang("discount"),'[align]' => 'right');           
                // $marks[1][] = array('key' => 'dis', '[left]' => $left, '[w]' => 180, '[top]' => $top, '[val]' => number_format($cal_payment->discount_total,2,'.',','),'[align]' => 'right');

                // $top += 20;
                // $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => isset($cal_payment->tax_name) ? $cal_payment->tax_name : "-",'[align]' => 'right');           
                // $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 180, '[top]' => $top, '[val]' => number_format($cal_payment->tax,2,'.',','),'[align]' => 'right');

                // $top += 20;
                // $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => isset($cal_payment->tax_name3) ? $cal_payment->tax_name3 : "-",'[align]' => 'right');           
                // $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 180, '[top]' => $top, '[val]' => number_format($cal_payment->tax3,2,'.',','),'[align]' => 'right');
                // ==========================================
                // $top += 20;
                // $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => lang("total"),'[align]' => 'right');           
                // $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 180, '[top]' => $top, '[val]' => number_format($cal_payment->invoice_total,2,'.',','),'[align]' => 'right');
            } else if ($cal_payment->discount_type == "after_tax") {
                $top += 20;
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => isset($cal_payment->tax_name2) ? $cal_payment->tax_name2 : "-", '[align]' => 'right');
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 180, '[top]' => $top, '[val]' => number_format($cal_payment->tax2, 2, '.', ','), '[align]' => 'right');

                $top += 20;
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => isset($cal_payment->tax_name) ? $cal_payment->tax_name : "-", '[align]' => 'right');
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 180, '[top]' => $top, '[val]' => number_format($cal_payment->tax, 2, '.', ','), '[align]' => 'right');

                $top += 20;
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => lang("discount"), '[align]' => 'right');
                $marks[1][] = array('key' => 'dis', '[left]' => $left, '[w]' => 180, '[top]' => $top, '[val]' => number_format($cal_payment->discount_total, 2, '.', ','), '[align]' => 'right');
                // ==========================================
                $top += 20;
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => lang("total"), '[align]' => 'right');
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 180, '[top]' => $top, '[val]' => number_format($cal_payment->invoice_total, 2, '.', ','), '[align]' => 'right');
            }
        } else {
            if ($cal_payment->discount_type == "before_tax") {
                $top += 20;
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => lang("discount"), '[align]' => 'right');
                $marks[1][] = array('key' => 'dis', '[left]' => $left, '[w]' => 180, '[top]' => $top, '[val]' => number_format($cal_payment->discount_total, 2, '.', ','), '[align]' => 'right');

                $top += 20;
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => isset($cal_payment->tax_name) ? $cal_payment->tax_name : "-", '[align]' => 'right');
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 180, '[top]' => $top, '[val]' => number_format($cal_payment->tax, 2, '.', ','), '[align]' => 'right');

                $top += 20;
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => isset($cal_payment->tax_name2) ? $cal_payment->tax_name2 : "-", '[align]' => 'right');
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 180, '[top]' => $top, '[val]' => number_format($cal_payment->tax2, 2, '.', ','), '[align]' => 'right');
                // ==========================================
                $top += 20;
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => lang("total"), '[align]' => 'right');
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 180, '[top]' => $top, '[val]' => number_format($cal_payment->invoice_total, 2, '.', ','), '[align]' => 'right');
            } else if ($cal_payment->discount_type == "after_tax") {
                $top += 20;
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => isset($cal_payment->tax_name2) ? $cal_payment->tax_name2 : "-", '[align]' => 'right');
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 180, '[top]' => $top, '[val]' => number_format($cal_payment->tax2, 2, '.', ','), '[align]' => 'right');

                $top += 20;
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => isset($cal_payment->tax_name) ? $cal_payment->tax_name : "-", '[align]' => 'right');
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 180, '[top]' => $top, '[val]' => number_format($cal_payment->tax, 2, '.', ','), '[align]' => 'right');

                $top += 20;
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => lang("discount"), '[align]' => 'right');
                $marks[1][] = array('key' => 'dis', '[left]' => $left, '[w]' => 180, '[top]' => $top, '[val]' => number_format($cal_payment->discount_total, 2, '.', ','), '[align]' => 'right');
                // ==========================================
                $top += 20;
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 100, '[top]' => $top, '[val]' => lang("total"), '[align]' => 'right');
                $marks[1][] = array('key' => '', '[left]' => $left, '[w]' => 180, '[top]' => $top, '[val]' => number_format($cal_payment->invoice_total, 2, '.', ','), '[align]' => 'right');
            }
        }


        $top = 905;
        $left = 25;
        // $marks[1][] = array('key' => 'note', '[left]' => $left, '[w]' => 323, '[top]' => $top, '[val]' => '','[align]' => 'left');

        //$left = 460;

        //echo $invoice_id;


        //exit;
        $user_signature = $this->Db_model->signature_approve($invoice_id, "payment_vouchers");
        // arr($user_signature);

        // exit;
        // var_dump($user_signature);exit;

        if ($user_signature->tbName != '') {
            if ($user_signature->signature == '') {
                $top = 980;
                $marks[1][] = array('key' => 'signauture', '[left]' => $left, '[w]' => 130, '[top]' => $top, '[val]' => $user_signature->first_name . ' ' . $user_signature->last_name, '[align]' => 'center');
            } else {
                $top = 980;
                $marks[1][] = array('key' => 'signauture', '[left]' => $left, '[w]' => 130, '[top]' => $top, '[val]' => '<img src=' . base_url() . $user_signature->signature . '>', '[align]' => 'center');
            }
            //$top=1060;
            //$left += 175;
            //$marks[1][] = array('key' => 'date_approved', '[left]' => $left, '[w]' => 130, '[top]' => $top, '[val]' => $user_signature->doc_date, '[align]' => 'center');
        }

        //End Item-List------------------------------------------------------------------------------------------------------------
        $keep = array();

        $rangItem = 14;

        foreach ($this->Db_model->fetchAll($sql) as $parentKey => $child) {

            // var_dump($child);
            // exit;
            if (!isset($keep[$child->pvId])) {
                $page = 1;
            }

            $keep[$child->pvId][$page][] = $child;

            if (count($keep[$child->pvId][$page]) == $rangItem) {
                $page += 1;
            }

        }

        foreach ($keep as $kd => $kv) {
            $i = 1;
            //  var_dump($kv);exit;


            foreach ($kv as $kpage => $vpage) {
                $dbs = convertObJectToArray($vpage[0]);

                $divs = array();

                foreach ($marks[1] as $km => $vm) {
                    $vm['[align]'] = isset($vm['[align]']) ? $vm['[align]'] : 'left';
                    $vm['[val]'] = isset($dbs[$vm['key']]) ? $dbs[$vm['key']] : $vm['[val]'];
                    $divs[] = str_replace(array_keys($vm), $vm, $template);
                }
                $top = 324;
                $trs = array();


                foreach ($vpage as $vkpage => $vvpage) {
                    // var_dump($vvpage);exit;

                    if ($vvpage->payment_method_id == '1') {
                        $method = 'เงินสด';
                    } else if ($vvpage->payment_method_id == '2') {
                        $method = 'Stripe';
                    } else if ($vvpage->payment_method_id == '3') {
                        $method = 'PayPal';
                    } else if ($vvpage->payment_method_id == '4') {
                        $method = 'Paytm';
                    } else if ($vvpage->payment_method_id == '5') {
                        $method = 'เงินโอน';
                    } else if ($vvpage->payment_method_id == '6') {
                        $method = 'บัตรเครดิต/บัตรเดบิต';
                    } else if ($vvpage->payment_method_id == '7') {
                        $method = 'เช็ค';
                    }
                    $trs[] = '
                            <tr>
                                <td style="border-bottom: 1px solid #999; text-align: center;">' . $i++ . '</td>
                                <td style="border-bottom: 1px solid #999; text-align: center;">' . $this->DateConvert($vvpage->payment_date) . '</td>
                                <td style="border-bottom: 1px solid #999; text-align: center;">' . $vvpage->invoice1_id . '</td>
                                <td style="border-bottom: 1px solid #999; text-align: center;">' . $this->DateConvert($vvpage->tax_date) . '</td>
                                <td style="border-bottom: 1px solid #999; text-align: center;">' . $vvpage->taxnumber_id . '</td>
                                <td style="border-bottom: 1px solid #999; text-align: left; padding-left: 10px;">' . $vvpage->detail . '</td>
                                <td style="border-bottom: 1px solid #999; text-align: right; padding-right: 10px;">' . number_format($vvpage->amount, 2, '.', ',') . '</td>
                            </tr>
                            ';


                    $marks[2] = array(); //สร้าง array แยกแต่ละรายการ
                    //$top += 28;
                    $left = 20;
                    //$marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 40, '[top]' => $top, '[val]' => $i++);                    

                    //$marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 80, '[top]' => $top, '[val]' => $this->DateConvert($vvpage->payment_date));

                    $left += 110;
                    // if($vvpage->invoice1_id == 0 || $vvpage->invoice1_id == '' || $vvpage->invoice1_id == null){
                    // $marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 20, '[top]' => $top, '[val]' => $vvpage->invoice_id);
                    // }else{
                    // $marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 20, '[top]' => $top, '[val]' => $vvpage->invoice1_id);
                    // }

                    $left += 50;
                    //$marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 390, '[top]' => $top, '[val]' => $vvpage->detail,'[align]' => 'left');

                    $left += 390;
                    $top += 18;
                    // if($vvpage->payment_method_id == '1'){
                    //     $marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 105, '[top]' => $top, '[val]' => 'เงินสด');
                    // }
                    // else if($vvpage->payment_method_id == '2'){
                    //     $marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 105, '[top]' => $top, '[val]' => 'ระบบชำระเงิน Stripe');
                    // }
                    // else if($vvpage->payment_method_id == '3'){
                    //     $marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 105, '[top]' => $top, '[val]' => 'PayPal');
                    // }
                    // else if($vvpage->payment_method_id == '4'){
                    //     $marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 105, '[top]' => $top, '[val]' => 'ระบบชำระเงิน Paytm');
                    // }
                    // else if($vvpage->payment_method_id == '5'){
                    //     $marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 105, '[top]' => $top, '[val]' => 'โอนเงิน');
                    // }
                    // else if($vvpage->payment_method_id == '6'){
                    //     $marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 105, '[top]' => $top, '[val]' => 'บัตรเครดิต/บัตรเดบิต');
                    // }
                    // else if($vvpage->payment_method_id == '7'){
                    //     $marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 105, '[top]' => $top, '[val]' => 'เช็ค');
                    // }
                    //$marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 80, '[top]' => $top, '[val]' => $vvpage->payment_date);
                    // foreach($this->Db_model->fetchAll($sql) as $method => $id){
                    // if($id->payment_method_id == '1'){
                    //     $marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 50, '[top]' => $top, '[val]' => 'เงินสด');
                    // }
                    // else if($id->payment_method_id == '2'){
                    //     $marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 50, '[top]' => $top, '[val]' => 'ระบบชำระเงิน Stripe');
                    // }
                    // else if($id->payment_method_id == '3'){
                    //     $marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 50, '[top]' => $top, '[val]' => 'PayPal');
                    // }
                    // else if($id->payment_method_id == '4'){
                    //     $marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 50, '[top]' => $top, '[val]' => 'ระบบชำระเงิน Paytm');
                    // }
                    // else if($id->payment_method_id == '5'){
                    //     $marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 50, '[top]' => $top, '[val]' => 'โอนเงิน');
                    // }
                    // else if($id->payment_method_id == '6'){
                    //     $marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 50, '[top]' => $top, '[val]' => 'บัตรเครดิต/บัตรเดบิต');
                    // }
                    // else if($id->payment_method_id == '7'){
                    //     $marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 50, '[top]' => $top, '[val]' => 'เช็คเงินสด');
                    // }
                    //     break;
                    // }
                    // $left += 60;
                    // foreach($this->Db_model->fetchAll($sql) as $method => $id){
                    //     $marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 250, '[top]' => $top, '[val]' => $vvpage->company_name, '[align]' => 'left');
                    // }
                    //$marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 250, '[top]' => $top, '[val]' => $vvpage->company_name, '[align]' => 'left');

                    // $left += 20;
                    // $marks[2][] = array('key' => 'note', '[left]' => $left, '[w]' => 300, '[top]' => $top, '[val]' => '', '[align]' => 'left');


                    $left += 100;
                    //$marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 90, '[top]' => $top, '[val]' => number_format($vvpage->amount,2,'.',',') ,'[align]' => 'right');


                    //$left += 70;
                    //$marks[2][] = array('key' => '', '[left]' => $left, '[w]' => 110, '[top]' => $top, '[val]' => number_format($vvpage->amount,2,'.',','));

                    $dbs = convertObJectToArray($vvpage);

                    foreach ($marks[2] as $km => $vm) {
                        $vm['[align]'] = isset($vm['[align]']) ? $vm['[align]'] : 'center';
                        $vm['[val]'] = isset($dbs[$vm['key']]) ? $dbs[$vm['key']] : $vm['[val]'];
                        $divs[] = str_replace(array_keys($vm), $vm, $template);
                    }

                }

                $divTop = 320;
                $divleft = 27;
                $tabletemplate = '
                    <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 1000px">
                        <table>
                            <tr>
                                <th class="thN" style="border-bottom: 1px solid #999; border-top: 1px solid #999;">#</th>
                                <th class="thA" style="border-bottom: 1px solid #999; border-top: 1px solid #999;">วันที่ใบแจ้งหนี้</th>
                                <th class="thA" style="border-bottom: 1px solid #999; border-top: 1px solid #999;">เลขที่ใบแจ้งหนี้</th>
                                <th class="thA" style="border-bottom: 1px solid #999; border-top: 1px solid #999;">วันที่ใบกำกับภาษี</th>
                                <th class="thA" style="border-bottom: 1px solid #999; border-top: 1px solid #999;">เลขที่ใบกำกับภาษี</th>
                                <th class="thB" style="border-bottom: 1px solid #999; border-top: 1px solid #999;">รายละเอียด</th>
                                <th class="thE" style="border-bottom: 1px solid #999; border-top: 1px solid #999; text-align: right; padding-right: 10px;">ยอดชำระ</th>
                            </tr>
                            ' . implode("", $trs) . '
                        </table>
                    </div>';

                $divTop = 640;
                $divleft = 357;
                $tablepaytol = '
                    <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px;">
                        <table style="font-size: 16px; width: 200px;">
                            <tr>
                                <td style="color: #00b050; width: 200px; text-align: right;">รวมเป็นยอดชำระ</td>
                                <td style="text-align: right; width: 200px;">' . number_format($cal_payment->invoice_total, 2, '.', ',') . ' บาท</td>
                            </tr>
                            <tr>
                                <td style="color: #00b050; text-align: right;">ภาษีมูลค่าเพื่ม 7%</td>
                                <td style="text-align: right;">' . number_format($cal_payment->tolvat7, 2, '.', ',') . ' บาท</td>
                            </tr>
                            <tr>
                                <td style="color: #00b050; text-align: right;">จำนวนเงินรวมทั้งสิ้น</td>
                                <td style="text-align: right;">' . number_format($cal_payment->sumvat7, 2, '.', ',') . ' บาท</td>
                            </tr>
                            <tr>
                                <td style="color: #00b050; text-align: right;">ภาษีหัก ณ ที่จ่าย 3%</td>
                                <td style="text-align: right;">' . number_format($cal_payment->tolvat3, 2, '.', ',') . ' บาท</td>
                            </tr>
                            <tr>
                                <td style="color: #00b050; text-align: right;">ยอดชำระ</td>
                                <td style="text-align: right;">' . number_format($cal_payment->sumvat3, 2, '.', ',') . ' บาท</td>
                            </tr>
                        </table>
                    </div>';

                $html = ' 
                            <style>
                            div{
                                font-size: 16px;
                                //border: solid 1px #000;
                                // font-weight:bold;
                                text-align: center;
                            }
                            img{
                                width: 200px;
                                height: 100px;
                            }
                            .label{
                                color: #FF3DA4;
                            }

                            table {
                                // font-size: 90px;
                                //border: solid 1px #000;
                                width: 735px; 
                                border-collapse: collapse;
                            }
                            th,td{
                                //border: solid 1px #000;
                            }
                            .thA{
                                width: 75px;
                            }
                            .thE{
                                width: 87px;
                            }
                            .thD{
                                width: 105px;
                            }
                            .thN{
                                width: 30px;
                            }
                            .testcolor{
                                color: green;
                            }
                           
                            </style>
                            ' . $tabletemplate . '
                            ' . $tablepaytol . '
                            ' . implode('', $divs) . '
                           
                            
            
                            
                        ';

                //exitl
                $mpdf->AddPage('P');
                $pagecount = $mpdf->SetSourceFile('pdf_Template/pv1.pdf');
                $tplId = $mpdf->importPage(1);
                $mpdf->useTemplate($tplId);
                $mpdf->WriteHTML($html);
            }


        }

        //echo $html; exit;
        //   $mpdf->Output();
        $mpdf->Output('ใบสำคัญจ่าย ' . $invoice_id . '.pdf', \Mpdf\Output\Destination::DOWNLOAD);

    }


    public function invoices_pdf($invoices_id = 0)
    {

        $template = '<div style="text-align: [align]; position:absolute; top:[top]px; left:[left]px; width:[w]px; height:[h]px;"><span>[val]</span></div>';

        $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'autoPageBreak' => false,
            'fontDir' => array_merge($fontDirs, [
                __DIR__ . '/fonts',
            ]),
            'fontdata' => $fontData + [
                'def' => [
                    'R' => 'THSarabun_Bold.ttf',
                    //'I' => 'THSarabunNew Italic.ttf',
                    // 'B' => 'THSarabunNewBold.ttf',
                ]
            ],
            'default_font' => 'def',
            'tempDir' => '/tmp'
        ]);
        $mpdf->charset_in = 'UTF-8';


        $keep = array();

        $company_address = nl2br(get_setting("company_address"));
        $company_phone = get_setting("company_phone");
        $company_vat_number = get_setting("company_vat_number");
        $company_name = get_setting("company_name");


        $sql = "SELECT 
            *,invoices.id as InvId,
            invoice_items.id as InvItemId, 
            clients.id as clientId, 
            invoice_items.deleted as InItems_delete, 
            invoices.note as inNote , 
            projects.title as proTitle, 
            invoice_items.title as itTitle,
            invoice_items.description as itDes,
            estimates.doc_no as esDoc_no,
            invoices.doc_no as invDoc_no
            
        FROM `invoices` 
        LEFT JOIN invoice_items ON invoice_items.invoice_id = invoices.id AND invoice_items.deleted = 0
        LEFT JOIN clients ON invoices.client_id = clients.id
        LEFT JOIN estimates ON estimates.id = invoices.es_id 
        LEFT JOIN users ON users.client_id = clients.id AND users.is_primary_contact = 1
        LEFT JOIN projects ON projects.id = invoices.project_id
        WHERE invoices.id = $invoices_id";

        //หัวตารากระดาษ------------------------------------------------------------------------------------------------------------


        $cal_payment = $this->Invoices_model->get_invoice_total_summary($invoices_id);


        $sql_invo_pay = "SELECT pay_spilter FROM invoices WHERE invoices.id = $invoices_id";
        $result = $this->db->query($sql_invo_pay)->row();
        $get_ps = $this->db->query($sql)->row();
        // var_dump($get_ps);exit;
        if ($cal_payment->tax_name) {
            $tax_name = $cal_payment->tax_name;
        } else {
            $tax_name = "-";
        }

        $pay_spliter = "";

        $val1 = ($result->pay_spilter * $cal_payment->tax_percentage) / 100;
        $val2 = ($result->pay_spilter * $cal_payment->tax_percentage2) / 100;

        $trs_deposit = '';
        if ($cal_payment->deposit != 0 && $cal_payment->include_deposit == 1) {
            $trs_deposit = '
            <tr>
                <td colspan="3"></td> 
                <td colspan="2" style="text-align: right;"><span class="label" >วางค่ามัดจำแล้ว</span></td>
                <td style="text-align: right;">' . number_format($cal_payment->deposit, 2, '.', ',') . ' บาท</td>
            </tr>
            ';

        }

        $trs_paid = "";
        $total_paids = 0;
        if (isset($cal_payment->total_paid)) {
            $trs_paid = '
            <tr>
                <td colspan="3"></td> 
                <td colspan="2" style="text-align: right;"><span class="label" >จ่ายแล้ว</span></td>
                <td style="text-align: right;">' . number_format($cal_payment->total_paid, 2, '.', ',') . ' บาท</td>
            </tr>
            ';
            $total_paids = isset($cal_payment->total_paid) ? $cal_payment->total_paid : 0;
        }



        if ($get_ps->pay_type == "percentage") {
            $get_ps_data = $get_ps->pay_sp . ' %';
        } else {
            $get_ps_data = $get_ps->pay_sp . ' งวด';
        }


        $tax_n_pay = '';
        if (!empty($cal_payment->tax_id)) {
            if ($cal_payment->tax_percentage == 7) {
                $ture_val1 = ($result->pay_spilter - $total_paids) + $val1;
            } else {
                $ture_val1 = ($result->pay_spilter - $total_paids) - $val1;
            }

            $tax_n_pay = '
                <tr>
                    <td colspan="3"></td> 
                    <td colspan="2" style="text-align: right;"><span class="label">' . $tax_name . '</span></td>
                    <td style="text-align: right;">' . number_format($val1, 2, '.', ',') . ' บาท</td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: left;"><span>(' . $this->Convert($ture_val1) . ')</span></td> 
                    <td colspan="2" style="text-align: right;"><span class="label" >จำนวนเงินรวมทั้งสิ้น</span></td>
                    <td style="text-align: right;">' . number_format($ture_val1, 2, '.', ',') . ' บาท</td>
                </tr>
                ';
        } else {
            $tax_n_pay = '';
        }
        if (!empty($cal_payment->tax_id2)) {
            $tax_name2 = isset($cal_payment->tax_name2) ? $cal_payment->tax_name2 : "-";
            if ($cal_payment->tax_percentage2 == 3) {
                $ture_val2 = $ture_val1 - $val2;
            } else {
                $ture_val2 = $ture_val1 + $val2;
            }

            $tax2_n_pay = '
                <tr>
                    <td colspan="3"></td> 
                    <td colspan="3" style="border-top: 2px solid #999; height:10px;" ></td>                 
                </tr> 
                            
                <tr>
                   <td colspan="3"></td> 
                   <td colspan="2" style="text-align: right;"><span class="label">' . $tax_name2 . '</span></td>
                   <td style="text-align: right;">' . number_format($val2, 2, '.', ',') . ' บาท</td>
               </tr>
               <tr>
                   <td colspan="3"></td> 
                   <td colspan="2" style="text-align: right;"><span class="label" >ยอดชำระ</span></td>
                   <td style="text-align: right;">' . number_format($ture_val2, 2, '.', ',') . ' บาท</td>
               </tr>
               
                ';
        } else {
            $tax2_n_pay = '';
        }


        // var_dump($get_ps_data);
        if ($get_ps->pay_type == "time") {
            if (!empty(json_decode($get_ps->pay_sps))) {
                $pay_spliter = '
                    <tr><td style="height: 2mm;"></td></tr>
                    <tr>
                        <td colspan="2"></td> 
                        <td colspan="3" style="text-align: right;"><span class="label">แบ่งชำระ</span></td>
                        <td style="text-align: right;">' . number_format($result->pay_spilter, 2, '.', ',') . ' บาท</td>
                    </tr>
                    ' . $trs_paid . '  
                    ' . $tax_n_pay . '
                    ' . $tax2_n_pay . '
                             
                    ';
            } else {
                $pay_spliter = '
                    <tr><td style="height: 2mm;"></td></tr>
                    <tr>
                        <td colspan="2"></td> 
                        <td colspan="3" style="text-align: right;"><span class="label">แบ่งชำระ ' . $get_ps_data . '</span></td>
                        <td style="text-align: right;">' . number_format($result->pay_spilter, 2, '.', ',') . ' บาท</td>
                    </tr>
                    ' . $trs_paid . '  
                    ' . $tax_n_pay . '
                    ' . $tax2_n_pay . '
                       
                    ';
            }

        } else {
            $pay_spliter = '
                    <tr><td style="height: 2mm;"></td></tr>
                    <tr>
                        <td colspan="2"></td> 
                        <td colspan="3" style="text-align: right;"><span class="label">แบ่งชำระ ' . $get_ps_data . '</span></td>
                        <td style="text-align: right;">' . number_format($result->pay_spilter, 2, '.', ',') . ' บาท</td>
                    </tr>
            ' . $trs_paid . '  
            ' . $tax_n_pay . '
            ' . $tax2_n_pay . '
                      
            ';
        }




        $tax_cal1 = $cal_payment->invoice_subtotal + $cal_payment->tax;
        $tax_name2 = isset($cal_payment->tax_name2) ? $cal_payment->tax_name2 : "-";
        if ($cal_payment->tax_id == 1) {

            $tax_val = ($cal_payment->invoice_subtotal - $cal_payment->deposit - $cal_payment->discount_total) + $cal_payment->tax - $cal_payment->tax2;
        } else {
            $tax_val = ($cal_payment->invoice_subtotal - $cal_payment->deposit - $cal_payment->discount_total) - $cal_payment->tax + $cal_payment->tax2;

        }


        $trs_total_tax = "";

        $trs_bar = '
            <tr>
                <td colspan="3"></td> 
                <td colspan="3" style="border-top: 2px solid #999; height:10px;" ></td>                 
            </tr>';





        // if($get_ps->credit != 0){
        $new_tax = $result->pay_spilter;
        // }else{
        // $new_tax = $cal_payment->tax_id == 1 ? $tax_cal1 : $tax_val;                
        // }

        //  var_dump($get_ps);exit;
        // var_dump($cal_payment);exit;
        if ($get_ps->include_deposit == 2) {
            $trs_tax_d = '';
            if ($cal_payment->tax) {
                $trs_tax_d = '
            <tr>
                <td colspan="3"></td> 
                <td colspan="2" style="text-align: right;"><span class="label">' . $tax_name . '</span></td>
                <td style="text-align: right;">' . number_format($cal_payment->tax, 2, '.', ',') . ' บาท</td>
            </tr>
            ';
            }

            $trs_tax_d2 = '';
            if ($cal_payment->tax2) {
                $trs_tax_d2 = '
            <tr>
                <td colspan="3"></td> 
                <td colspan="2" style="text-align: right;"><span class="label">' . $tax_name2 . '</span></td>
                <td style="text-align: right;">' . number_format($cal_payment->tax2, 2, '.', ',') . ' บาท</td>
            </tr>
            ';
            }

            $trs_total[] = '
            <tr>
                <td colspan="3"></td> 
                <td colspan="2" style="text-align: right;"><span class="label">รวมเป็นเงิน</span></td>
                <td style="text-align: right;">' . number_format($cal_payment->invoice_subtotal, 2, '.', ',') . ' บาท</td>
            </tr>
            ' . $trs_tax_d . '
            ' . $trs_tax_d2 . '
            <tr>
                <td colspan="3"></td> 
                <td colspan="2" style="text-align: right;"><span class="label" >จำนวนเงินรวมทั้งสิ้น</span></td>
                <td style="text-align: right;">' . number_format($cal_payment->invoice_total, 2, '.', ',') . ' บาท</td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: left;"><span>(' . $this->Convert($cal_payment->invoice_total) . ')</span></td> 
                <td colspan="3" style="font-size: 20px; background-color:#D8D8D8" ><span class="label" >ยอดคงเหลือชำระ: ' . number_format($cal_payment->total_es - $cal_payment->invoice_total, 2, '.', ',') . ' บาท</span></td> 
            </tr>
        ';
        } else if ($cal_payment->discount_total == '') {

            $a = '';

            if (!empty($cal_payment->tax2)) {

                if (isset($tax_cal1)) {
                    $cal_tax2 = $new_tax - $cal_payment->tax2;
                } else {
                    $cal_tax2 = $tax_val;
                }

                $trs_total_tax = ' 
                    ' . $trs_bar . '               
                     <tr>
                        <td colspan="3"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label">' . $tax_name2 . '</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->tax2, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                        <td colspan="3"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label" >ยอดชำระ</span></td>
                        <td style="text-align: right;">' . number_format($cal_tax2, 2, '.', ',') . ' บาท</td>
                    </tr>
                   
                    
                ';
            }

            if (empty($pay_spliter)) {
                $a = '
                     <tr>
                            <td colspan="2"></td> 
                            <td colspan="3" style="text-align: right;"><span class="label">' . $tax_name . '</span></td>
                            <td style="text-align: right;">' . number_format($cal_payment->tax, 2, '.', ',') . ' บาท</td>
                        </tr>
                        
                        
                        <tr>                            
                            <td colspan="2" style="text-align: left;" ><span>(' . $this->Convert($new_tax) . ')</span></td> 
                            <td colspan="3" style="text-align: right;"><span class="label" >จำนวนเงินรวมทั้งสิ้น</span></td>
                            <td style="text-align: right;">' . number_format($new_tax, 2, '.', ',') . ' บาท</td>
                        </tr>
                        ' . $trs_total_tax . '
            ';
            }

            $trs_total[] = '
                        <tr>
                            <td colspan="2"></td>                            
                            <td colspan="3" style="text-align: right; margin-top: 4%"><span class="label">รวมเป็นเงิน</span></td>                            
                            <td style="text-align: right;">' . number_format($cal_payment->invoice_subtotal, 2, '.', ',') . ' บาท</td>
                        </tr>
                        ' . $trs_deposit . '
                        ' . $a . '
                        ' . $pay_spliter . '

                    ';

        } else if ($cal_payment->discount_type == "before_tax") { //before_tax


            $trs_vat = '';
            if (empty($pay_spliter)) {
                if (isset($cal_payment->tax)) {
                    $cal_tax = ($cal_payment->invoice_subtotal - $cal_payment->deposit - $cal_payment->discount_total) + $cal_payment->tax;
                } else {
                    $cal_tax = ($cal_payment->invoice_subtotal - $cal_payment->deposit - $cal_payment->discount_total) - $cal_payment->tax;
                }

                if ($cal_payment->tax2 != NULL) {

                    if (isset($cal_tax)) {
                        $cal_tax2 = $cal_tax - $cal_payment->tax2;
                    } else {
                        $cal_tax2 = $cal_tax + $cal_payment->tax2;
                    }

                    $trs_total_tax = ' 
                    ' . $trs_bar . '               
                     <tr>
                        <td colspan="3"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label">' . $tax_name2 . '</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->tax2, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                        <td colspan="3"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label" >ยอดชำระ</span></td>
                        <td style="text-align: right;">' . number_format($cal_tax2, 2, '.', ',') . ' บาท</td>
                    </tr>
                   
                    
                ';
                }
                $trs_vat = '
                <tr>
                    <td colspan="3"></td> 
                    <td colspan="2" style="text-align: right;"><span class="label">' . $tax_name . '</span></td>
                    <td style="text-align: right;">' . number_format($cal_payment->tax, 2, '.', ',') . ' บาท</td>
                </tr> 
            
            <tr>
                <td colspan="3" style="text-align: left;"><span>(' . $this->Convert($tax_val) . ')</span></td> 
                <td colspan="2" style="text-align: right;"><span class="label" >จำนวนเงินรวมทั้งสิ้น</span></td>
                <td style="text-align: right;">' . number_format($cal_tax, 2, '.', ',') . ' บาท</td>
            </tr>
            ' . $trs_total_tax . ' 
            ';

            }




            $trs_total[] = '
                    <tr>
                    <td colspan="3" style="border-top: 1.5px solid #999;"></td>                            
                        <td colspan="2" style="text-align: right; margin-top: 4%; border-top: 1.5px solid #999;"><span class="label">รวมเป็นเงิน</span></td>                            
                        <td style="text-align: right; border-top: 1.5px solid #999;">' . number_format($cal_payment->invoice_subtotal, 2, '.', ',') . ' บาท</td>
                    </tr>
                    ' . $trs_deposit . '
                    <tr>
                        <td colspan="3"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label"><u>หัก</u> ส่วนลด</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->discount_total, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                        <td colspan="3"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label">จำนวนเงินหลังหักส่วนลด</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->invoice_subtotal - $cal_payment->deposit - $cal_payment->discount_total, 2, '.', ',') . ' บาท</td>
                    </tr>
                    ' . $trs_vat . '
                    ' . $trs_paid . '  
                                    
                    ' . $pay_spliter . '
                ';
            // <td ><span>'.$this->Convert($cal_payment->estimate_total).'</span></td> 


        } else if ($cal_payment->discount_type == "after_tax") { //after_tax

            $trs_total[] = '
                    <tr>
                        <td colspan="3" style="border-top: 1.5px solid #999;"></td>                            
                        <td colspan="2" style="text-align: right; border-top: 1.5px solid #999;"><span class="label">รวมเป็นเงิน</span></td>                            
                        <td style="text-align: right; border-top: 1.5px solid #999;">' . number_format($cal_payment->invoice_subtotal, 2, '.', ',') . ' บาท</td>
                    </tr>                    
                    <tr>
                        <td colspan="3"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label">' . $tax_name . '</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->tax, 2, '.', ',') . ' บาท</td>
                    </tr>
                    ' . $trs_total_tax . '
                    <tr>
                        <td colspan="3"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label"><u>หัก</u> ส่วนลด</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->discount_total, 2, '.', ',') . ' บาท</td>
                    </tr>                  
                                       
                    <tr>                        
                        <td colspan="3" style="text-align: left;"><span>(' . $this->Convert($tax_val) . ')</span></td>                        
                        <td colspan="2" style="text-align: right;"><span class="label" >จำนวนเงินหลังหักส่วนลด</span></td>
                        <td style="text-align: right;">' . number_format($tax_val, 2, '.', ',') . ' บาท</td>
                    </tr>
                    
                    
                    ' . $pay_spliter . '
                    
                   

                ';

        }


        $left = 460;
        $marks[1] = array();
        $user_signature = $this->Db_model->signature_approve($invoices_id, "invoices");

        // var_dump($user_signature);exit;
        if ($user_signature) {
            if ($user_signature->signature == '') {
                $top = 1040;
                $left = 450;
                $marks[1][] = array('key' => 'signauture', '[left]' => $left, '[w]' => 130, '[top]' => $top, '[val]' => $user_signature->first_name, '[align]' => 'center');
            } else {
                $top = 1000;
                $marks[1][] = array('key' => 'signauture', '[left]' => $left, '[w]' => 130, '[top]' => $top, '[val]' => '<img src=' . base_url() . $user_signature->signature . '>', '[align]' => 'center');
            }
            $top = 1040;
            $left += 155;
            $marks[1][] = array('key' => 'date_approved', '[left]' => $left, '[w]' => 130, '[top]' => $top, '[val]' => $this->DateConvert($user_signature->doc_date), '[align]' => 'center');
        }


        //End Item-List------------------------------------------------------------------------------------------------------------


        $data = $this->Db_model->creatBy($invoices_id, "invoices");
        $fname = $data->first_name;
        $lname = $data->last_name;

        $rangItem = 7;

        foreach ($this->Db_model->fetchAll($sql) as $parentKey => $child) {

            if (!isset($keep[$child->InvId])) {
                $page = 1;
            }

            $keep[$child->InvId][$page][] = $child;

            if (count($keep[$child->InvId][$page]) == $rangItem) {
                $page += 1;
            }

        }



        foreach ($keep as $kd => $kv) {
            // var_dump($kv);exit;
            $i = 1;

            foreach ($kv as $kpage => $vpage) {
                // var_dump($vpage);exit;


                $dbs = convertObJectToArray($vpage[0]);
                $dbs = array_merge($dbs, [
                    'bill_date' => $this->DateConvert($vpage[0]->bill_date),
                    'due_date' => $this->DateConvert($vpage[0]->due_date),
                    'amout_name' => '<span class="label">รวมเงิน</span>',
                    'vat_name' => '<span class="label">' . isset($cal_payment->tax_name) ? '<span class="label">' . $cal_payment->tax_name . '</span>' : "-" . '</span>',
                    'total_name' => '<span class="label">รวมเงินทั้งสิ้น</span>',
                    // 'note' => '<span class="label">หมายเหตุ : </span> '.implode("<br/> -",$str_text)

                ]);


                $divs = array();

                foreach ($marks[1] as $km => $vm) {
                    $vm['[align]'] = isset($vm['[align]']) ? $vm['[align]'] : 'left';
                    $vm['[val]'] = isset($dbs[$vm['key']]) ? $dbs[$vm['key']] : $vm['[val]'];
                    $divs[] = str_replace(array_keys($vm), $vm, $template);
                }

                // exit;
                $top = 326;
                $trs = array();
                foreach ($vpage as $vkpage => $vvpage) {
                    // var_dump($vvpage);exit;

                    if ($vvpage->include_deposit == 2) {
                        $trs[] = '
                        <tr>
                            <td style="width: 5%; border-bottom: 1px solid #999; vertical-align: top;">' . $i++ . '</td>
                            <td style="text-align: left; width: 55%; border-bottom: 1px solid #999;">' . $vvpage->itTitle . '<br/>' . $vvpage->itDes . '</td>
                            <td style=" border-bottom: 1px solid #999; vertical-align: top;"></td>
                            <td style=" text-align: right; border-bottom: 1px solid #999; vertical-align: top;"></td>                                
                            <td style="text-align: right; border-bottom: 1px solid #999; vertical-align: top;"></td>
                            <td style="text-align: right; width:17%; border-bottom: 1px solid #999; vertical-align: top;">' . number_format($vvpage->total, 2, '.', ',') . '</td>
                        </tr>
                    ';
                    } else {
                        $trs[] = '
                        <tr>
                            <td style="width: 5%; border-bottom: 1px solid #999; vertical-align: top;">' . $i++ . '</td>
                            <td style="text-align: left; width: 55%; border-bottom: 1px solid #999;">' . $vvpage->itTitle . '<br/>' . $vvpage->itDes . '</td>
                            <td style=" border-bottom: 1px solid #999; vertical-align: top;">' . number_format($vvpage->quantity, 0, '.', ',') . '</td>
                            <td style=" text-align: right; border-bottom: 1px solid #999; vertical-align: top;">' . $vvpage->unit_type . '</td>                                
                            <td style="text-align: right; border-bottom: 1px solid #999; vertical-align: top;">' . number_format($vvpage->rate, 2, '.', ',') . '</td>
                            <td style="text-align: right; width:17%; border-bottom: 1px solid #999; vertical-align: top;">' . number_format($vvpage->total, 2, '.', ',') . '</td>
                        </tr>
                    ';
                    }


                    $marks[2] = array(); //สร้าง array แยกแต่ละรายการ

                    $dbs = convertObJectToArray($vvpage);

                    foreach ($marks[2] as $km => $vm) {
                        $vm['[align]'] = isset($vm['[align]']) ? $vm['[align]'] : 'center';
                        $vm['[val]'] = isset($dbs[$vm['key']]) ? $dbs[$vm['key']] : $vm['[val]'];
                        $divs[] = str_replace(array_keys($vm), $vm, $template);
                    }

                }
                $trs_note = array();


                if ($vpage[0]->inNote) {
                    $trs_note[] = '
                    <tr>
                        <td colspan="6" style="text-align: left;"><br/><p class="label">หมายเหตุ : </p>' . nl2br($vpage[0]->inNote, false) . '</td>
                    </tr>
                ';
                }

                $divTop = 110;
                $divleft = 27;
                $trs_client = '
                    <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 350px">
                        <table style="width: 100%; ">
                            <tr>
                                <td style="text-align: left;">' . $company_name . '<br/>' . $company_address . '<br/>หมายเลขประจำตัวผู้เสียภาษี ' . $company_vat_number . '<br/>' . $company_phone . '</td>
                            </tr>
                            <tr>
                                <td style="text-align: left;"><span class="label"><br/>ลูกค้า</span></td>
                            </tr>
                            <tr>
                                <td style="text-align: left;">' . $vpage[0]->company_name . '<br/>' . $vpage[0]->address . ' ' . $vpage[0]->city . ' ' . $vpage[0]->state . ' ' . $vpage[0]->zip . ' ' . $vpage[0]->country . '<br/>' . 'เลขประจำตัวผู้เสียภาษี ' . $vpage[0]->vat_number . '</td>
                            </tr>
                        </table>
                    </div>
                    ';

                $pro_title = isset($vpage[0]->proTitle) ? $vpage[0]->proTitle : "-";
                $name = isset($vpage[0]->first_name) ? $vpage[0]->first_name . ' ' . $vpage[0]->last_name : "-";
                $phone = isset($vpage[0]->phone) ? $vpage[0]->phone : "-";
                $email = isset($vpage[0]->email) ? $vpage[0]->email : "-";

                // $info_contact = array();

                $info_contact = '
                        <tr>
                            <td style="text-align: left; padding-left: 5px;"><span class="label">ชื่องาน</span></td>
                            <td style="text-align: left;"><span>' . $pro_title . '</span></td>
                            <td colspan="2"></td>
                        </tr>                        
                        <tr>
                            <td style="text-align: left; padding-left: 5px;"><span class="label">ผู้ติดต่อ</span></td>
                            <td style="text-align: left;"><span>' . $name . '</span></td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td style="text-align: left; padding-left: 5px;"><span class="label">เบอร์โทร</span></td>
                            <td style="text-align: left;"><span>' . $phone . '</span></td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td style="text-align: left; padding-left: 5px;"><span class="label">อีเมล</span></td>
                            <td style="text-align: left;"><span>' . $email . '</span></td>
                            <td colspan="2"></td>
                        </tr>
                        ';

                // width: 150px;

                // var_dump($vpage[0]);exit;

                if ($vpage[0]->credit != 0) {
                    if ($vpage[0]->pay_type == "percentage") {
                        $pay = $vpage[0]->pay_sp . " %";
                    } else {
                        $pay = $vpage[0]->pay_sp . " งวด";
                    }
                    $credits_s = '
                        <td style="text-align: left; "><span class="label">เครดิต</span></td>
                        <td style="text-align: left;">' . $vpage[0]->credit . ' วัน</td>
                        ';
                } else {
                    $credits_s = '
                        <td style="text-align: left;"><span class="label">เครดิต</span></td>
                        <td style="text-align: left;">จ่ายเป็นเงินสด</td>
                        ';
                }

                // var_dump($get_ps);exit;
                if ($get_ps->pay_type == "time") {
                    if (!empty(json_decode($get_ps->pay_sps))) {
                        $pay_detail = '
                            <td style="text-align: left;"><span class="label">การชำระ</span></td>
                            <td style="text-align: left;">' . $pay . '</td>
                        ';
                    } else if ($get_ps->include_deposit == 2) {
                        $pay_detail = '';
                    } else {

                        $pay_detail = '';
                    }

                }

                $divTop = 10;
                $divleft = 400;
                $info_table = '
                    
                        <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 360px">
                            <table style="width: 100%;">
                                <tr>
                                    <th colspan="4" style="text-align: center; border-bottom: 1.5px solid #999; font-size: 30px; "><span class="label">ใบวางบิล/ใบแจ้งหนี้</span><br/><span class="label" style="font-size: 20px;">ต้นฉบับ</span></th>    
                                </tr>
                                <tr>
                                    <td style="text-align: left;  padding-left: 5px; width:120px;"><span class="label">เลขที่</span></td>
                                    <td style="text-align: left; width:100px;"><span>' . $vpage[0]->invDoc_no . '</span></td>
                                    <td colspan="2" ></td>
                                </tr>
                                
                                <tr>
                                    <td style="text-align: left; padding-left: 5px;"><span class="label">วันที่</span></td>
                                    <td style="text-align: left; width:50px;"><span>' . $this->DateConvert($vpage[0]->bill_date) . '</span></td>
                                    ' . $credits_s . '
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding-left: 5px;"><span class="label">ครบกำหนด</span></td>
                                    <td style="text-align: left; width:50px;"><span>' . $this->DateConvert($vpage[0]->due_date) . '</span></td>
                                    ' . $pay_detail . '
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding-left: 5px;"><span class="label">ผู้ขาย</span></td>
                                    <td style="text-align: left;"><span></span>' . $fname . ' ' . $lname . '</td>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding-left: 5px; width: 90px; border-bottom: 1.5px solid #999;"><span class="label">อ้างอิงใบเสนอราคา</span></td>
                                    <td style="text-align: left; border-bottom: 1.5px solid #999;"><span>' . $vpage[0]->esDoc_no . '</span></td>
                                    <td colspan="2" style="border-bottom: 1.5px solid #999;"></td>
                                </tr>
                            
                                <tr>
                                    <td></td>
                                </tr>

                                ' . $info_contact . '
                                
                            </table>
                        </div>
                        ' . $trs_client . '
                        ';



                $divTop = 990;
                $divleft = 27;
                $trs_approve = '
                        <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 740px;">
                                <table style="width:100%;">
                                    <tr>
                                        <td colspan="2">ในนาม ' . $vpage[0]->company_name . '</td>
                                        <td></td>
                                        <td colspan="2">ในนาม ' . $company_name . '</td>
                                    </tr>
                                    <tr>
                                        <td style="height: 100px;">___________________________<p>ผู้รับวางบิล</p></td>
                                        <td>___________________________<p>วันที่</p></td>
                                        <td style="width: 10%;"></td>
                                        <td>___________________________<p>ผู้วางบิล</p></td>
                                        <td>___________________________<p>วันที่</p></td>
                                    </tr>                            
                                </table>
                        </div>
                        
                       ';

                $divTop = 318;
                $divleft = 30;
                if ($vvpage->include_deposit == 2) {
                    $tabletemplate = '
                        <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 730px;">
                            <table style="width:100%; overflow: auto; page-break-inside:avoid">
                                <tr>
                                    <td class="thStyle">#</td>
                                    <td class="thStyle">รายละเอียด</td>
                                    <td class="thStyle"></td>
                                    <td class="thStyle"></td>
                                    <td class="thStyle" style="text-align: right;"></td>
                                    <td class="thStyle" style="text-align: right;">ยอดมัดจำ</td>
                                </tr>
                                ' . implode("", $trs) . '
                                ' . implode("", $trs_total) . '
                                ' . implode("", $trs_note) . '
                            </table>
                            
                        </div>
                    
                            
                        
                        '; //
                } else {
                    $tabletemplate = '
                            <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 730px;">
                                <table style="width:100%; overflow: auto; page-break-inside:avoid">
                                    <tr>
                                        <td class="thStyle">#</td>
                                        <td class="thStyle">รายละเอียด</td>
                                        <td class="thStyle">จำนวน</td>
                                        <td class="thStyle"></td>
                                        <td class="thStyle" style="text-align: right;">ราคาต่อหน่วย</td>
                                        <td class="thStyle" style="text-align: right;">ยอดรวม</td>
                                    </tr>
                                    ' . implode("", $trs) . '
                                    ' . implode("", $trs_total) . '
                                    ' . implode("", $trs_note) . '
                                </table>
                                
                            </div>
                        
                            
                                
                            
                            '; //
                }




                // var_dump($keep);exit;


                $html = ' 
                        <style>
                        div{
                            font-size: 17px;
                            // border: solid 1px #000;
                            // font-weight:bold;
                            text-align: center;
                        }
                        img{
                            width: 200px;
                            height: 100px;
                        }
                        .label{
                            color: #663399;
                            
                        }
                        table {
                            font-size: 17px;
                            // border: solid 1px #000;
                            // font-weight: bold;
                            width: 735px; 
                            border-collapse: collapse;
                        }
                        th,td{
                            text-align: center;
                            // border: solid 1px #000;
                        }
                        .thA{
                            width: 10%;
                        }

                        .thStyle{
                            border-top: 2px solid #999;
                            border-bottom: 2px solid #999;
                        }
                        </style>
                        <body>
                
                                     
                                               
                        ' . $tabletemplate . '
                        </body>       
                        
                    ';
                //' . implode('', $divs) . ' 
                $mpdf->SetHTMLHeader($info_table);

                $Note = nl2br($vpage[0]->inNote, false);
                preg_match_all("/(<br>)/", $Note, $matches);

                for ($j = 0; $j < 8; $j++) {
                    // var_dump($page);exit;
                    if ($j <= 8) {
                        $mpdf->SetHTMLFooter($trs_approve);
                        break;

                    } else {
                        $mpdf->SetHTMLFooter($trs_approve);
                        break;
                    }
                }












                // echo $html;exit; 
                $mpdf->SetTitle($vpage[0]->invDoc_no);
                $mpdf->autoPageBreak = false;
                $mpdf->use_kwt = true;
                $mpdf->shrink_tables_to_fit = 1;
                $mpdf->AddPage('P');
                // var_dump($mpdf->AddPage('P'));exit;
                $pagecount = $mpdf->SetSourceFile('pdf_Template/main_template.pdf');
                $tplId = $mpdf->importPage(1);
                $mpdf->useTemplate($tplId);
                $mpdf->WriteHTML($html);

            }


        }

        // $mpdf->Output();
        $mpdf->Output($vpage[0]->invDoc_no . '.pdf', \Mpdf\Output\Destination::DOWNLOAD);

    }

    public function receipt_pdf($receipt_id = 0)
    {
        $template = '<div style="text-align: [align]; position:absolute; top:[top]px; left:[left]px; width:[w]px; height:[h]px;"><span>[val]</span></div>';

        $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'fontDir' => array_merge($fontDirs, [
                __DIR__ . '/fonts',
            ]),
            'fontdata' => $fontData + [
                'def' => [
                    'R' => 'THSarabun_Bold.ttf',
                    //'I' => 'THSarabunNew Italic.ttf',
                    //'B' => 'THSarabunNew Bold.ttf',
                ]
            ],
            'default_font' => 'def',
            'tempDir' => '/tmp'
        ]);
        $mpdf->charset_in = 'UTF-8';


        $keep = array();
        $html = '';
        $company_address = nl2br(get_setting("company_address"));
        $company_phone = get_setting("company_phone");
        $company_website = get_setting("company_website");
        $company_vat_number = get_setting("company_vat_number");
        $company_name = get_setting("company_name");


        $sql = "SELECT 
                    bom_suppliers.company_name as su_comname, 
                    bom_suppliers.address as su_add,
                    bom_suppliers.city as su_city,
                    bom_suppliers.state as su_state,
                    bom_suppliers.zip as su_zip,
                    bom_suppliers.country as su_country,
                    bom_suppliers.vat_number as su_vat_number,
                    bom_suppliers.currency as su_currency,
                    bom_suppliers.currency_symbol as su_currency_symbol
                    ,receipts.* 
                    ,receipts.id as oID,
                    receipt_items.*,
                    receipt_items.title as orItTitle,
                    receipt_items.description as orItdescription
            FROM receipts
            LEFT JOIN receipt_items on receipt_items.receipt_id = receipts.id AND receipt_items.deleted = 0
            LEFT JOIN bom_suppliers on bom_suppliers.id = receipts.supplier_id
            WHERE receipts.id = $receipt_id";
        // arr($sql);exit;

        $data = $this->Db_model->creatBy($receipt_id, "receipts");
        $fname = $data->first_name;
        $lname = $data->last_name;


        $cal_payment = $this->Receipts_model->get_receipt_total_summary($receipt_id);

        if ($cal_payment->tax_name) {
            $tax_name = $cal_payment->tax_name;
        } else {
            $tax_name = "ภาษีมูลค่าเพิ่ม 0%";
        }
        $value_cal = $cal_payment->receipt_subtotal - $cal_payment->discount_total;
        // var_dump($cal_payment);exit;
        // if($cal_payment->discount_type == "before_tax"){
        $trs_total[] = '
                        <tr>
                            <td colspan="2" style="border-top: 1.5px solid #999;"></td>                            
                            <td colspan="3" style="text-align: right; margin-top: 4%; border-top: 1.5px solid #999;"><span class="label">รวมเป็นเงิน</span></td>                            
                            <td style="text-align: right; border-top: 1.5px solid #999;">' . number_format($cal_payment->receipt_subtotal, 2, '.', ',') . ' บาท</td>
                        </tr>

                        <tr>
                            <td colspan="2"></td> 
                            <td colspan="3" style="text-align: right;"><span class="label">ส่วนลด</span></td>
                            <td style="text-align: right;">' . number_format($cal_payment->discount_total, 2, '.', ',') . ' บาท</td>
                        </tr>
                        <tr>
                            <td colspan="2"></td> 
                            <td colspan="3" style="text-align: right;"><span class="label">จำนวนเงินหลังหักส่วนลด</span></td>
                            <td style="text-align: right;">' . number_format($cal_payment->receipt_subtotal - $cal_payment->discount_total, 2, '.', ',') . ' บาท</td>
                        </tr>

                        <tr>
                            <td colspan="6"><br/></td> 
                            
                        </tr>
                        
                        <tr>
                            <td colspan="2"></td> 
                            <td colspan="3" style="text-align: right;"><span class="label">' . $tax_name . '</span></td>
                            <td style="text-align: right;">' . number_format($cal_payment->tax, 2, '.', ',') . ' บาท</td>
                        </tr>
                        <tr>
                            <td colspan="2"></td> 
                            <td colspan="3" style="text-align: right;"><span class="label">ราคาไม่รวมภาษีมูลค่าเพิ่ม</span></td>
                            <td style="text-align: right;">' . number_format($cal_payment->receipt_total - $cal_payment->tax, 2, '.', ',') . ' บาท</td>
                        </tr>
                        <tr>
                            
                            <td colspan="2" style="text-align: left;"><span>(' . $this->Convert($cal_payment->receipt_total) . ')</span></td> 
                            
                            <td colspan="3" style="text-align: right;"><span class="label" >จำนวนเงินรวมทั้งสิ้น</span></td>
                            <td style="text-align: right;">' . number_format($cal_payment->receipt_total, 2, '.', ',') . ' บาท</td>
                        </tr>
                        <tr>
                            <td colspan="2"></td> 
                            <td colspan="3" style="text-align: right;"><span class="label" >ชำระเงินแล้ว</span></td>
                            <td style="text-align: right;">' . number_format($cal_payment->payment, 2, '.', ',') . ' บาท</td>
                        </tr>

                        <tr>
                            <td colspan="2"></td> 
                            <td colspan="3" style="text-align: right;"><span class="label" >' . $cal_payment->paymentStatus . '</span></td>
                            <td style="text-align: right;">' . number_format($cal_payment->notPaid_n, 2, '.', ',') . ' บาท</td>
                        </tr>

                    ';
        // <td ><span>'.$this->Convert($cal_payment->estimate_total).'</span></td> 


        // }else if($cal_payment->discount_type == "after_tax"){

        //     $trs_total[] = '
        //         <tr>
        //             <td colspan="3" style="border-top: 1.5px solid #999;"></td>                            
        //             <td colspan="2" style="text-align: right; border-top: 1.5px solid #999;"><span class="label">รวมเป็นเงิน</span></td>                            
        //             <td style="text-align: right; border-top: 1.5px solid #999;">'.number_format($cal_payment->order_subtotal,2,'.',',').'</td>
        //         </tr>                       
        //         <tr>
        //             <td colspan="3"></td> 
        //             <td colspan="2" style="text-align: right;"><span class="label">'.$tax_name.'</span></td>
        //             <td style="text-align: right;">'.number_format($cal_payment->tax,2,'.',',').'</td>
        //         </tr>                       

        //         <tr>
        //             <td></td>
        //             <td><span>'.$this->Convert($cal_payment->order_total).'</span></td> 
        //             <td></td>
        //             <td colspan="2" style="text-align: right;"><span class="label" >รวมเงินทั้งสิ้น</span></td>
        //             <td style="text-align: right;">'.number_format($cal_payment->order_total,2,'.',',').'</td>
        //         </tr>
        //         <tr>
        //             <td colspan="3"></td> 
        //             <td colspan="2" style="text-align: right;"><span class="label" >ชำระเงินแล้ว</span></td>
        //             <td style="text-align: right;">'.number_format($cal_payment->payment,2,'.',',').'</td>
        //         </tr>

        //         <tr>
        //             <td colspan="3"></td> 
        //             <td colspan="2" style="text-align: right;"><span class="label" >'.$cal_payment->paymentStatus.'</span></td>
        //             <td style="text-align: right;">'.number_format($cal_payment->notPaid_n,2,'.',',').'</td>
        //         </tr>


        //     ';

        // }
        $divTop = 14;
        $divleft = 27;

        $logos = '<img src="' . get_file_from_setting('order_logo', get_setting('only_file_path')) . '" style="width: 80px; height:80px;"/>';

        $logo_es = '<div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: auto;">
                        ' . $logos . '
                    </div>';

        $marks[1] = array();
        $left = 460;
        $user_signature = $this->Db_model->signature_approve($receipt_id, "receipts");
        // var_dump($user_signature);exit;
        if ($user_signature) {
            if ($user_signature->signature == '') {
                $top = 1010;
                $marks[1][] = array('key' => 'signauture', '[left]' => $left, '[w]' => 130, '[top]' => $top, '[val]' => $user_signature->first_name . ' ' . $user_signature->last_name, '[align]' => 'center');
            } else {
                $top = 1000;
                $marks[1][] = array('key' => 'signauture', '[left]' => $left, '[w]' => 130, '[top]' => $top, '[val]' => '<img src=' . base_url() . $user_signature->signature . '>', '[align]' => 'center');
            }
            $top = 1040;
            $left += 155;
            $marks[1][] = array('key' => 'date_approved', '[left]' => $left, '[w]' => 130, '[top]' => $top, '[val]' => $this->DateConvert($user_signature->doc_date), '[align]' => 'center');
        }

        //End Item-List------------------------------------------------------------------------------------------------------------


        $rangItem = 7;

        foreach ($this->Db_model->fetchAll($sql) as $parentKey => $child) {

            if (!isset($keep[$child->oID])) {
                $page = 1;
            }

            $keep[$child->oID][$page][] = $child;

            if (count($keep[$child->oID][$page]) == $rangItem) {
                $page += 1;
            }

        }

        foreach ($keep as $kd => $kv) {
            $i = 1;
            foreach ($kv as $kpage => $vpage) {
                $str_text = explode("-", $vpage[0]->note);
                $po_id = explode("-", $vpage[0]->po_id);
                // var_dump($vpage);exit;
                $dbs = convertObJectToArray($vpage[0]);

                $divs = array();

                foreach ($marks[1] as $km => $vm) {
                    $vm['[align]'] = isset($vm['[align]']) ? $vm['[align]'] : 'left';
                    $vm['[val]'] = isset($dbs[$vm['key']]) ? $dbs[$vm['key']] : $vm['[val]'];
                    $divs[] = str_replace(array_keys($vm), $vm, $template);
                }
                $top = 326;
                $trs = array();

                foreach ($vpage as $vkpage => $vvpage) {
                    // var_dump($vvpage);exit;

                    if ($vvpage->orItTitle) {
                        $trs[] = '
                            <tr>
                                <td style="width: 3%; vertical-align: top;">' . $i++ . '</td>
                                <td style="text-align: left; width:62%; vertical-align: top;">' . $vvpage->orItTitle . '<br/>' . $vvpage->orItdescription . '</td>
                                <td style="vertical-align: top;">' . number_format($vvpage->quantity, 0, '.', ',') . '</td>
                                <td style="text-align: right; vertical-align: top;">' . $vvpage->unit_type . '</td>                                
                                <td style="text-align: right; vertical-align: top;">' . number_format($vvpage->rate, 2, '.', ',') . '</td>
                                <td style="text-align: right; width: 15%; vertical-align: top;">' . number_format($vvpage->total, 2, '.', ',') . '</td>
                            </tr>
                            ';
                    } else {
                        $trs[] = '
                            <tr>
                                <td style="width: 3%;"></td>
                                <td style="width: 62%;"></td>
                                <td style=" width: 6%;"></td>
                                <td style=" width: 6%;"></td>                                
                                <td ></td>
                                <td ></td>
                            </tr>
                            ';
                    }


                    $marks[2] = array(); //สร้าง array แยกแต่ละรายการ

                    $dbs = convertObJectToArray($vvpage);

                    foreach ($marks[2] as $km => $vm) {
                        $vm['[align]'] = isset($vm['[align]']) ? $vm['[align]'] : 'center';
                        $vm['[val]'] = isset($dbs[$vm['key']]) ? $dbs[$vm['key']] : $vm['[val]'];
                        $divs[] = str_replace(array_keys($vm), $vm, $template);
                    }

                }

                $trs_note = array();
                // var_dump($str_text);exit;
                if ($vpage[0]->note) {
                    $trs_note[] = '
                        <tr>
                            <td colspan="3" style="text-align: left;"><span class="label">หมายเหตุ : </span>' . implode("<br/> -", $str_text) . '</td>
                        </tr>
                    ';
                }

                $divTop = 110;
                $divleft = 27;
                $vat_name = $vpage[0]->su_vat_number != NULL ? 'เลขประจำตัวผู้เสียภาษี ' . $vpage[0]->su_vat_number : "";
                $trs_client = '
                    <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 500px">
                        
                        <table style="width: 250px; ">
                            <tr>
                                <td style="text-align: left;">' . $company_name . '<br/>' . $company_address . '<br/>หมายเลขประจำตัวผู้เสียภาษี ' . $company_vat_number . '<br/>' . $company_phone . '</td>
                            </tr>
                            <tr>
                                <td style="text-align: left;"><span class="label"><br/>ผู้จำหน่าย</span></td>
                            </tr>
                            <tr>
                                <td style="text-align: left;">' . $vpage[0]->su_comname . '<br/>' . $vpage[0]->su_add . ' ' . $vpage[0]->su_city . ' ' . $vpage[0]->su_state . ' ' . $vpage[0]->su_zip . ' ' . $vpage[0]->su_country . '<br/>' . $vat_name . '</td>
                            </tr>
                        </table>
                    </div>
                    ';

                $divTop += 190;
                $divleft = 27;
                $tabletemplate = '
                    <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 1000px">
                        <table>
                            <tr>
                                <td class="thStyle">#</td>
                                <td class="thStyle">รายละเอียด</td>
                                <td class="thStyle">จำนวน</td>
                                <td class="thStyle"></td>
                                <td class="thStyle" style="text-align: right;">ราคาต่อหน่วย</td>
                                <td class="thStyle" style="text-align: right;">ยอดรวม</td>
                            </tr>
                            ' . implode("", $trs) . '
                            ' . implode("", $trs_total) . '
                            ' . implode("", $trs_note) . '
                        </table>
                    </div>';

                $info_contact = array();
                $credit = '';

                if (!empty($po_id[0]) && !empty($po_id[1])) {
                    $sql = "SELECT pri.po_no, pri.supplier_id, purchaserequests.*
                            FROM pr_items pri
                            LEFT JOIN purchaserequests on purchaserequests.id = pri.pr_id                         
                            WHERE pri.pr_id = " . $po_id[0] . " AND pri.supplier_id=" . $po_id[1] . "";

                    $result = $this->db->query($sql)->row();
                    // var_dump($result);exit;
                    $expired_d = isset($result->expired) ? $this->DateConvert($result->expired) : "-";
                    $pro_name = !empty($result->project_name) ? $result->project_name : "-";
                    $info_contact[] = '
                            <tr>
                                <td style="text-align: left; width: 50px; padding-left: 5px;"><span class="label">ชื่องาน</span></td>
                                <td style="text-align: left;"><span>' . $pro_name . '</span></td>
                            </tr>
                        ';

                    $credit = '
                        <tr>
                            <td style="text-align: left; padding-left: 5px;"><span class="label">ครบกำหนด</span></td>
                            <td style="text-align: left;"><span>' . $expired_d . '</span></td>
                        </tr>
                        ';
                }

                // arr($result);exit;
                $tax_ref = $vpage[0]->tax_ref != NULL ? $vpage[0]->tax_ref : "-";


                if (isset($vpage[0]->first_name)) {
                    $info_contact[] = '
                        <tr>
                            <td style="text-align: left; width: 50px; padding-left: 5px;"><span class="label">ผู้ติดต่อ</span></td>
                            <td style="text-align: left;"><span>' . $vpage[0]->first_name . ' ' . $vpage[0]->last_name . '</span></td>
                        </tr>
                        <tr>
                            <td style="text-align: left; padding-left: 5px;"><span class="label">เบอร์โทร</span></td>
                            <td style="text-align: left; padding-left: 5px;"><span>' . $vpage[0]->phone . '</span></td>
                        </tr>
                        <tr>
                            <td style="text-align: left; padding-left: 5px;"><span class="label">Email</span></td>
                            <td style="text-align: left; padding-left: 5px;"><span>' . $vpage[0]->email . '</span></td>
                        </tr>';
                }

                $po = isset($result->po_no) ? $result->po_no : '-';
                // var_dump($vpage[0]->order_date);exit;
                $divTop = 30;
                $divleft = 410;
                $info_table = '
                        <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 340px">
                            <table style="width: 100%;">
                                <tr>
                                    <th colspan="2" style="text-align: center; border-bottom: 1.5px solid #999; font-size:30px;"><span class="label">ใบรับสินค้า</span></th>
                                    
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding-left: 5px; width: 150px;"><span class="label">เลขที่</span></td>
                                    <td style="text-align: left;"><span>' . $vpage[0]->doc_no . '</span></td>
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding-left: 5px;"><span class="label">วันที่</span></td>
                                    <td style="text-align: left;"><span>' . $this->DateConvert($vpage[0]->receipt_date) . '</span></td>
                                </tr>
                                ' . $credit . '
                                <tr>
                                    <td style="text-align: left; padding-left: 5px;"><span class="label">ผู้สั่งซื้อ</span></td>
                                    <td style="text-align: left;"><span>' . $fname . ' ' . $lname . '</span></td>
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding-left: 5px;"><span class="label"> อ้างอิงใบสั่งซื้อ</span></td>
                                    <td style="text-align: left;"><span>' . $po . '</span></td>
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding-left: 5px; border-bottom: 1.5px solid #999;"><span class="label">อ้างอิงใบกำกับภาษี</span></td>
                                    <td style="text-align: left; border-bottom: 1.5px solid #999;"><span>' . $tax_ref . '</span></td>
                                </tr>

                                <tr>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td></td>
                                </tr>
                                ' . implode("", $info_contact) . '
                            </table>
                        </div>';

                $divTop = 990;
                $divleft = 27;
                $trs_approve = '<div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 750px; ">
                        <table>
                            <tr>
                                <td colspan="2">ในนาม ' . $vpage[0]->su_comname . '</td>
                                <td></td>
                                <td colspan="2">ในนาม ' . $company_name . '</td>
                            </tr>
                            <tr>
                                <td style="height: 100px;">___________________________<p>ผู้ขาย</p></td>
                                <td>___________________________<p>วันที่</p></td>
                                <td style="width: 10%;"></td>
                                <td>___________________________<p>ผู้อนุมัติ</p></td>
                                <td>___________________________<p>วันที่</p></td>
                            </tr>                            
                        </table>
                    </div>';


                $html = ' 
                            <style>
                            div{
                                font-size: 17px;
                                // border: solid 1px #000;
                                // font-weight:bold;
                                text-align: center;
                            }
                            img{
                                width: 200px;
                                height: 100px;
                            }
                            .label{
                                color: #A0522D;
                                
                            }

                            table {
                                font-size: 17px;
                                // border: solid 1px #000;
                                width: 735px; 
                                border-collapse: collapse;
                            }
                            th,td{
                                text-align: center;
                                // border: solid 1px #000;
                            }
                            .thA{
                                width: 10%;
                            }

                            .thStyle{
                                border-top: 2px solid #999;
                                border-bottom: 2px solid #999;
                            }
                           
                            </style>
                            ' . $logo_es . '
                            ' . $trs_client . '
                            ' . $info_table . '
                            ' . $tabletemplate . '
                            ' . $trs_approve . '
                            ' . implode('', $divs) . '
                        ';

                //  . implode('', $divs) . 
                $mpdf->AddPage('P');
                $mpdf->SetTitle($vpage[0]->doc_no);
                // var_dump($mpdf->AddPage('P'));exit;
                $pagecount = $mpdf->SetSourceFile('pdf_Template/template.pdf');
                $tplId = $mpdf->importPage(1);
                $mpdf->useTemplate($tplId);
                $mpdf->WriteHTML($html);
            }


        }

        // $mpdf->Output();
        $mpdf->Output($vpage[0]->doc_no . '.pdf', \Mpdf\Output\Destination::DOWNLOAD);

    }


    public function receipt_taxinvoices_pdf($receipt_taxinvoices_id = 0)
    {

        $template = '<div style="text-align: [align]; position:absolute; top:[top]px; left:[left]px; width:[w]px; height:[h]px;"><span>[val]</span></div>';

        $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'autoPageBreak' => false,
            'fontDir' => array_merge($fontDirs, [
                __DIR__ . '/fonts',
            ]),
            'fontdata' => $fontData + [
                'def' => [
                    'R' => 'THSarabun_Bold.ttf',
                    //'I' => 'THSarabunNew Italic.ttf',
                    // 'B' => 'THSarabunNewBold.ttf',
                ]
            ],
            'default_font' => 'def',
            'tempDir' => '/tmp'
        ]);
        $mpdf->charset_in = 'UTF-8';


        $keep = array();

        $company_address = nl2br(get_setting("company_address"));
        $company_phone = get_setting("company_phone");
        $company_vat_number = get_setting("company_vat_number");
        $company_name = get_setting("company_name");


        $sql = "SELECT 
            *,receipt_taxinvoices.id as InvId,
            receipt_taxinvoice_items.id as InvItemId, 
            clients.id as clientId, 
            receipt_taxinvoice_items.deleted as InItems_delete, 
            receipt_taxinvoices.note as inNote , 
            projects.title as proTitle, 
            receipt_taxinvoice_items.title as itTitle,
            receipt_taxinvoice_items.description as itDes,
            invoices.doc_no as esDoc_no,
            receipt_taxinvoices.doc_no as invDoc_no
            
        FROM `receipt_taxinvoices` 
        LEFT JOIN receipt_taxinvoice_items ON receipt_taxinvoice_items.receipt_taxinvoice_id = receipt_taxinvoices.id AND receipt_taxinvoice_items.deleted = 0
        LEFT JOIN clients ON receipt_taxinvoices.client_id = clients.id
        LEFT JOIN invoices ON invoices.id = receipt_taxinvoices.es_id 
        LEFT JOIN users ON users.client_id = clients.id AND users.is_primary_contact = 1
        LEFT JOIN projects ON projects.id = receipt_taxinvoices.project_id
        LEFT JOIN receipt_taxinvoice_payments ON receipt_taxinvoices.id = receipt_taxinvoice_payments.receipt_taxinvoice_id AND receipt_taxinvoice_payments.deleted = 0
        WHERE receipt_taxinvoices.id = $receipt_taxinvoices_id";

        $data = $this->Db_model->creatBy($receipt_taxinvoices_id, "receipt_taxinvoices");
        $fname = $data->first_name;
        $lname = $data->last_name;

        //var_dump($data);exit;


        //หัวตารากระดาษ------------------------------------------------------------------------------------------------------------

        $method = [1 => 'เงินสด', 5 => 'เช็ค', 6 => 'โอนเงิน', 7 => 'เครดิต'];
        $cal_payment = $this->Receipt_taxinvoices_model->get_receipt_taxinvoice_total_summary($receipt_taxinvoices_id);
        // var_dump($cal_payment);exit;

        $sql_invo_pay = "SELECT pay_spilter FROM receipt_taxinvoices WHERE receipt_taxinvoices.id = $receipt_taxinvoices_id";
        $result = $this->db->query($sql_invo_pay)->row();
        $get_ps = $this->db->query($sql)->row();
        // var_dump($get_ps);exit;
        if ($cal_payment->tax_name) {
            $tax_name = $cal_payment->tax_name;
        } else {
            $tax_name = "-";
        }

        $pay_spliter = "";

        $val1 = ($result->pay_spilter * $cal_payment->tax_percentage) / 100;
        $val2 = ($result->pay_spilter * $cal_payment->tax_percentage2) / 100;

        $trs_deposit = '';
        if ($cal_payment->deposit != 0 && $cal_payment->include_deposit == 1) {
            $trs_deposit = '
            <tr>
                <td colspan="3"></td> 
                <td colspan="2" style="text-align: right;"><span class="label" >วางค่ามัดจำแล้ว</span></td>
                <td style="text-align: right;">' . number_format($cal_payment->deposit, 2, '.', ',') . ' บาท</td>
            </tr>
            ';

        }

        $trs_paid = "";
        $total_paids = 0;
        if (isset($cal_payment->total_paid)) {
            $trs_paid = '
            <tr>
                <td colspan="3"></td> 
                <td colspan="2" style="text-align: right;"><span class="label" >จ่ายแล้ว</span></td>
                <td style="text-align: right;">' . number_format($cal_payment->total_paid, 2, '.', ',') . ' บาท</td>
            </tr>
            ';
            $total_paids = isset($cal_payment->total_paid) ? $cal_payment->total_paid : 0;
        }



        if ($get_ps->pay_type == "percentage") {
            $get_ps_data = $get_ps->pay_sp . ' %';
        } else {
            $get_ps_data = $get_ps->pay_sp . ' งวด';
        }


        $tax_n_pay = '';
        if (!empty($cal_payment->tax_id)) {
            if ($cal_payment->tax_percentage == 7) {
                $ture_val1 = ($result->pay_spilter - $total_paids) + $val1;
            } else {
                $ture_val1 = ($result->pay_spilter - $total_paids) - $val1;
            }

            $tax_n_pay = '
                <tr>
                    <td colspan="3"></td> 
                    <td colspan="2" style="text-align: right;"><span class="label">' . $tax_name . '</span></td>
                    <td style="text-align: right;">' . number_format($val1, 2, '.', ',') . ' บาท</td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: left;"><span>(' . $this->Convert($total_paids) . ')</span></td> 
                    <td colspan="2" style="text-align: right;"><span class="label" >จำนวนเงินรวมทั้งสิ้น</span></td>
                    <td style="text-align: right;">' . number_format($total_paids, 2, '.', ',') . ' บาท</td>
                </tr>
                ';
        } else {
            $tax_n_pay = '';
        }
        if (!empty($cal_payment->tax_id2)) {
            $tax_name2 = isset($cal_payment->tax_name2) ? $cal_payment->tax_name2 : "-";
            if ($cal_payment->tax_percentage2 == 3) {
                $ture_val2 = $ture_val1 - $val2;
            } else {
                $ture_val2 = $ture_val1 + $val2;
            }

            $tax2_n_pay = '
                <tr>
                    <td colspan="3"></td> 
                    <td colspan="3" style="border-top: 2px solid #999; height:10px;" ></td>                 
                </tr> 
                            
                <tr>
                   <td colspan="3"></td> 
                   <td colspan="2" style="text-align: right;"><span class="label">' . $tax_name2 . '</span></td>
                   <td style="text-align: right;">' . number_format($val2, 2, '.', ',') . ' บาท</td>
               </tr>
               <tr>
                   <td colspan="3"></td> 
                   <td colspan="2" style="text-align: right;"><span class="label" >ยอดชำระ</span></td>
                   <td style="text-align: right;">' . number_format($ture_val2, 2, '.', ',') . ' บาท</td>
               </tr>
               
                ';
        } else {
            $tax2_n_pay = '';
        }


        // var_dump($get_ps_data);
        if ($get_ps->pay_type == "time") {
            if (!empty(json_decode($get_ps->pay_sps))) {
                $pay_spliter = '
                    <tr><td style="height: 2mm;"></td></tr>
                    <tr>
                        <td colspan="2"></td> 
                        <td colspan="3" style="text-align: right;"><span class="label">แบ่งชำระ</span></td>
                        <td style="text-align: right;">' . number_format($result->pay_spilter, 2, '.', ',') . ' บาท</td>
                    </tr>
                    ' . $trs_paid . '  
                    ' . $tax_n_pay . '
                    ' . $tax2_n_pay . '
                             
                    ';
            } else {
                $pay_spliter = '
                    <tr><td style="height: 2mm;"></td></tr>
                    <tr>
                        <td colspan="2"></td> 
                        <td colspan="3" style="text-align: right;"><span class="label">แบ่งชำระ ' . $get_ps_data . '</span></td>
                        <td style="text-align: right;">' . number_format($result->pay_spilter, 2, '.', ',') . ' บาท</td>
                    </tr>
                    ' . $trs_paid . '  
                    ' . $tax_n_pay . '
                    ' . $tax2_n_pay . '
                       
                    ';
            }

        } else {
            $pay_spliter = '
                    <tr><td style="height: 2mm;"></td></tr>


            ' . $tax_n_pay . '
            ' . $tax2_n_pay . '
                      
            ';
        }




        $tax_cal1 = $cal_payment->receipt_taxinvoice_subtotal + $cal_payment->tax;
        $tax_name2 = isset($cal_payment->tax_name2) ? $cal_payment->tax_name2 : "-";
        if ($cal_payment->tax_id == 1) {

            $tax_val = ($cal_payment->receipt_taxinvoice_subtotal - $cal_payment->deposit - $cal_payment->discount_total) + $cal_payment->tax - $cal_payment->tax2;
        } else {
            $tax_val = ($cal_payment->receipt_taxinvoice_subtotal - $cal_payment->deposit - $cal_payment->discount_total) - $cal_payment->tax + $cal_payment->tax2;

        }


        $trs_total_tax = "";

        $trs_bar = '
            <tr>
                <td colspan="3"></td> 
                <td colspan="3" style="border-top: 2px solid #999; height:10px;" ></td>                 
            </tr>';





        // if($get_ps->credit != 0){
        $new_tax = $result->pay_spilter;
        // }else{
        // $new_tax = $cal_payment->tax_id == 1 ? $tax_cal1 : $tax_val;                
        // }

        //  var_dump($get_ps);exit;
        // var_dump($cal_payment);exit;
        if ($get_ps->include_deposit == 2) {
            $trs_tax_d = '';
            if ($cal_payment->tax) {
                $trs_tax_d = '
            <tr>
                <td colspan="3"></td> 
                <td colspan="2" style="text-align: right;"><span class="label">' . $tax_name . '</span></td>
                <td style="text-align: right;">' . number_format($cal_payment->tax, 2, '.', ',') . ' บาท</td>
            </tr>
            ';
            }

            $trs_tax_d2 = '';
            if ($cal_payment->tax2) {
                $trs_tax_d2 = '
            <tr>
                <td colspan="3"></td> 
                <td colspan="2" style="text-align: right;"><span class="label">' . $tax_name2 . '</span></td>
                <td style="text-align: right;">' . number_format($cal_payment->tax2, 2, '.', ',') . ' บาท</td>
            </tr>
            ';
            }

            $trs_total[] = '
            <tr>
                <td colspan="3"></td> 
                <td colspan="2" style="text-align: right;"><span class="label">รวมเป็นเงิน</span></td>
                <td style="text-align: right;">' . number_format($cal_payment->receipt_taxinvoice_subtotal, 2, '.', ',') . ' บาท</td>
            </tr>
            ' . $trs_tax_d . '
            ' . $trs_tax_d2 . '
            <tr>
                <td colspan="3"></td> 
                <td colspan="2" style="text-align: right;"><span class="label" >จำนวนเงินรวมทั้งสิ้น</span></td>
                <td style="text-align: right;">' . number_format($cal_payment->invoice_total, 2, '.', ',') . ' บาท</td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: left;"><span>(' . $this->Convert($cal_payment->invoice_total) . ')</span></td> 
                <td colspan="3" style="font-size: 20px; background-color:#D8D8D8" ><span class="label" >ยอดคงเหลือชำระ: ' . number_format($cal_payment->total_es - $cal_payment->invoice_total, 2, '.', ',') . ' บาท</span></td> 
            </tr>
        ';
        } else if ($cal_payment->discount_total == '') {

            $a = '';

            if (!empty($cal_payment->tax2)) {

                if (isset($tax_cal1)) {
                    $cal_tax2 = $new_tax - $cal_payment->tax2;
                } else {
                    $cal_tax2 = $tax_val;
                }

                $trs_total_tax = ' 
                    ' . $trs_bar . '               
                     <tr>
                        <td colspan="3"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label">' . $tax_name2 . '</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->tax2, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                        <td colspan="3"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label" >ยอดชำระ</span></td>
                        <td style="text-align: right;">' . number_format($cal_tax2, 2, '.', ',') . ' บาท</td>
                    </tr>
                   
                    
                ';
            }

            if (empty($pay_spliter)) {
                $a = '
                     <tr>
                            <td colspan="2"></td> 
                            <td colspan="3" style="text-align: right;"><span class="label">' . $tax_name . '</span></td>
                            <td style="text-align: right;">' . number_format($cal_payment->tax, 2, '.', ',') . ' บาท</td>
                        </tr>
                        
                        
                        <tr>                            
                            <td colspan="2" style="text-align: left;" ><span>(' . $this->Convert($new_tax) . ')</span></td> 
                            <td colspan="3" style="text-align: right;"><span class="label" >จำนวนเงินรวมทั้งสิ้น</span></td>
                            <td style="text-align: right;">' . number_format($new_tax, 2, '.', ',') . ' บาท</td>
                        </tr>
                        ' . $trs_total_tax . '
            ';
            }

            $trs_total[] = '
                        <tr>
                            <td colspan="2"></td>                            
                            <td colspan="3" style="text-align: right; margin-top: 4%"><span class="label">รวมเป็นเงิน</span></td>                            
                            <td style="text-align: right;">' . number_format($cal_payment->receipt_taxinvoice_subtotal, 2, '.', ',') . ' บาท</td>
                        </tr>
                        ' . $trs_deposit . '
                        ' . $a . '
                        ' . $pay_spliter . '

                    ';

        } else if ($cal_payment->discount_type == "before_tax") { //before_tax


            $trs_vat = '';
            if (empty($pay_spliter)) {
                if (isset($cal_payment->tax)) {
                    $cal_tax = ($cal_payment->receipt_taxinvoice_subtotal - $cal_payment->deposit - $cal_payment->discount_total) + $cal_payment->tax;
                } else {
                    $cal_tax = ($cal_payment->receipt_taxinvoice_subtotal - $cal_payment->deposit - $cal_payment->discount_total) - $cal_payment->tax;
                }

                if ($cal_payment->tax2 != NULL) {

                    if (isset($cal_tax)) {
                        $cal_tax2 = $cal_tax - $cal_payment->tax2;
                    } else {
                        $cal_tax2 = $cal_tax + $cal_payment->tax2;
                    }

                    $trs_total_tax = ' 
                    ' . $trs_bar . '               
                     <tr>
                        <td colspan="3"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label">' . $tax_name2 . '</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->tax2, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                        <td colspan="3"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label" >ยอดชำระ</span></td>
                        <td style="text-align: right;">' . number_format($cal_tax2, 2, '.', ',') . ' บาท</td>
                    </tr>
                   
                    
                ';
                }
                $trs_vat = '
                <tr>
                    <td colspan="3"></td> 
                    <td colspan="2" style="text-align: right;"><span class="label">' . $tax_name . '</span></td>
                    <td style="text-align: right;">' . number_format($cal_payment->tax, 2, '.', ',') . ' บาท</td>
                </tr> 
            
            <tr>
                <td colspan="3" style="text-align: left;"><span>(' . $this->Convert($tax_val) . ')</span></td> 
                <td colspan="2" style="text-align: right;"><span class="label" >จำนวนเงินรวมทั้งสิ้น</span></td>
                <td style="text-align: right;">' . number_format($cal_tax, 2, '.', ',') . ' บาท</td>
            </tr>
            ' . $trs_total_tax . ' 
            ';

            }




            $trs_total[] = '
                    <tr>
                    <td colspan="3" style="border-top: 1.5px solid #999;"></td>                            
                        <td colspan="2" style="text-align: right; margin-top: 4%; border-top: 1.5px solid #999;"><span class="label">รวมเป็นเงิน</span></td>                            
                        <td style="text-align: right; border-top: 1.5px solid #999;">' . number_format($cal_payment->receipt_taxinvoice_subtotal, 2, '.', ',') . ' บาท</td>
                    </tr>
                    ' . $trs_deposit . '

                    ' . $trs_vat . '
                                    
                    ' . $pay_spliter . '
                ';
            // <td ><span>'.$this->Convert($cal_payment->estimate_total).'</span></td> 


        } else if ($cal_payment->discount_type == "after_tax") { //after_tax

            $trs_total[] = '
                    <tr>
                        <td colspan="3" style="border-top: 1.5px solid #999;"></td>                            
                        <td colspan="2" style="text-align: right; border-top: 1.5px solid #999;"><span class="label">รวมเป็นเงิน</span></td>                            
                        <td style="text-align: right; border-top: 1.5px solid #999;">' . number_format($cal_payment->receipt_taxinvoice_subtotal, 2, '.', ',') . ' บาท</td>
                    </tr>                    
                    <tr>
                        <td colspan="3"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label">' . $tax_name . '</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->tax, 2, '.', ',') . ' บาท</td>
                    </tr>
                    ' . $trs_total_tax . '
                    <tr>
                        <td colspan="3"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label"><u>หัก</u> ส่วนลด</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->discount_total, 2, '.', ',') . ' บาท</td>
                    </tr>                  
                                       
                    <tr>                        
                        <td colspan="3" style="text-align: left;"><span>(' . $this->Convert($tax_val) . ')</span></td>                        
                        <td colspan="2" style="text-align: right;"><span class="label" >จำนวนเงินหลังหักส่วนลด</span></td>
                        <td style="text-align: right;">' . number_format($tax_val, 2, '.', ',') . ' บาท</td>
                    </tr>
                    
                    
                    ' . $pay_spliter . '
                    
                   

                ';

        }


        $left = 460;
        $marks[1] = array();
        $user_signature = $this->Db_model->signature_approve($receipt_taxinvoices_id, "receipt_taxinvoices");

        // var_dump($user_signature);exit;
        if ($user_signature) {
            if ($user_signature->signature == '') {
                $top = 500;
                $left = 450;
                $marks[1][] = array('key' => 'signauture', '[left]' => $left, '[w]' => 130, '[top]' => $top, '[val]' => $user_signature->first_name, '[align]' => 'center');
            } else {
                $top = 500;
                $marks[1][] = array('key' => 'signauture', '[left]' => $left, '[w]' => 130, '[top]' => $top, '[val]' => '<img src=' . base_url() . $user_signature->signature . '>', '[align]' => 'center');
            }
            $top = 500;
            $left += 155;
            $marks[1][] = array('key' => 'date_approved', '[left]' => $left, '[w]' => 130, '[top]' => $top, '[val]' => $this->DateConvert($user_signature->doc_date), '[align]' => 'center');
        }


        //End Item-List------------------------------------------------------------------------------------------------------------


        $rangItem = 4;

        foreach ($this->Db_model->fetchAll($sql) as $parentKey => $child) {

            if (!isset($keep[$child->InvId])) {
                $page = 1;
            }

            $keep[$child->InvId][$page][] = $child;

            if (count($keep[$child->InvId][$page]) == $rangItem) {
                $page += 1;
            }

        }



        foreach ($keep as $kd => $kv) {
            // var_dump($kv);exit;
            $i = 1;

            foreach ($kv as $kpage => $vpage) {
                // var_dump($vpage);exit;


                $dbs = convertObJectToArray($vpage[0]);
                $dbs = array_merge($dbs, [
                    'bill_date' => $this->DateConvert($vpage[0]->bill_date),
                    'due_date' => $this->DateConvert($vpage[0]->due_date),
                    'amout_name' => '<span class="label">รวมเงิน</span>',
                    'vat_name' => '<span class="label">' . isset($cal_payment->tax_name) ? '<span class="label">' . $cal_payment->tax_name . '</span>' : "-" . '</span>',
                    'total_name' => '<span class="label">รวมเงินทั้งสิ้น</span>',
                    // 'note' => '<span class="label">หมายเหตุ : </span> '.implode("<br/> -",$str_text)

                ]);


                $divs = array();

                foreach ($marks[1] as $km => $vm) {
                    $vm['[align]'] = isset($vm['[align]']) ? $vm['[align]'] : 'left';
                    $vm['[val]'] = isset($dbs[$vm['key']]) ? $dbs[$vm['key']] : $vm['[val]'];
                    $divs[] = str_replace(array_keys($vm), $vm, $template);
                }

                // exit;
                $top = 326;
                $trs = array();
                foreach ($vpage as $vkpage => $vvpage) {
                    // var_dump($vvpage);exit;

                    if ($vvpage->include_deposit == 2) {
                        $trs[] = '
                        <tr>
                            <td style="width: 5%; border-bottom: 1px solid #999; vertical-align: top;">' . $i++ . '</td>
                            <td style="text-align: left; width: 55%; border-bottom: 1px solid #999;">' . $vvpage->itTitle . '<br/>' . $vvpage->itDes . '</td>
                            <td style=" border-bottom: 1px solid #999; vertical-align: top;"></td>
                            <td style=" text-align: right; border-bottom: 1px solid #999; vertical-align: top;"></td>                                
                            <td style="text-align: right; border-bottom: 1px solid #999; vertical-align: top;"></td>
                            <td style="text-align: right; width:17%; border-bottom: 1px solid #999; vertical-align: top;">' . number_format($vvpage->total, 2, '.', ',') . '</td>
                        </tr>
                    ';
                    } else {
                        $trs[] = '
                        <tr>
                            <td style="width: 5%; border-bottom: 1px solid #999; vertical-align: top;">' . $i++ . '</td>
                            <td style="text-align: left; width: 55%; border-bottom: 1px solid #999;">' . $vvpage->itTitle . '<br/>' . $vvpage->itDes . '</td>
                            <td style=" border-bottom: 1px solid #999; vertical-align: top;">' . number_format($vvpage->quantity, 0, '.', ',') . '</td>
                            <td style=" text-align: right; border-bottom: 1px solid #999; vertical-align: top;">' . $vvpage->unit_type . '</td>                                
                            <td style="text-align: right; border-bottom: 1px solid #999; vertical-align: top;">' . number_format($vvpage->rate, 2, '.', ',') . '</td>
                            <td style="text-align: right; width:17%; border-bottom: 1px solid #999; vertical-align: top;">' . number_format($vvpage->total, 2, '.', ',') . '</td>
                        </tr>
                    ';
                    }


                    $marks[2] = array(); //สร้าง array แยกแต่ละรายการ

                    $dbs = convertObJectToArray($vvpage);

                    foreach ($marks[2] as $km => $vm) {
                        $vm['[align]'] = isset($vm['[align]']) ? $vm['[align]'] : 'center';
                        $vm['[val]'] = isset($dbs[$vm['key']]) ? $dbs[$vm['key']] : $vm['[val]'];
                        $divs[] = str_replace(array_keys($vm), $vm, $template);
                    }

                }
                $trs_note = array();


                if ($vpage[0]->inNote) {
                    $trs_note[] = '
                    <tr>
                        <td colspan="6" style="text-align: left;"><br/><p class="label">หมายเหตุ : </p>' . nl2br($vpage[0]->inNote, false) . '</td>
                    </tr>
                ';
                }

                $divTop = 110;
                $divleft = 27;
                $trs_client = '
                    <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 350px">
                        <table style="width: 100%; ">
                            <tr>
                                <td style="text-align: left;">' . $company_name . '<br/>' . $company_address . '<br/>หมายเลขประจำตัวผู้เสียภาษี ' . $company_vat_number . '<br/>' . $company_phone . '</td>
                            </tr>
                            <tr>
                                <td style="text-align: left;"><span class="label"><br/>ลูกค้า</span></td>
                            </tr>
                            <tr>
                                <td style="text-align: left;">' . $vpage[0]->company_name . '<br/>' . $vpage[0]->address . ' ' . $vpage[0]->city . ' ' . $vpage[0]->state . ' ' . $vpage[0]->zip . ' ' . $vpage[0]->country . '<br/>' . 'เลขประจำตัวผู้เสียภาษี ' . $vpage[0]->vat_number . '</td>
                            </tr>
                        </table>
                    </div>
                    ';

                $pro_title = isset($vpage[0]->proTitle) ? $vpage[0]->proTitle : "-";
                $name = isset($vpage[0]->first_name) ? $vpage[0]->first_name . ' ' . $vpage[0]->last_name : "-";
                $phone = isset($vpage[0]->phone) ? $vpage[0]->phone : "-";
                $email = isset($vpage[0]->email) ? $vpage[0]->email : "-";

                // $info_contact = array();

                $info_contact = '
                        <tr>
                            <td style="text-align: left; padding-left: 5px;"><span class="label">ชื่องาน</span></td>
                            <td style="text-align: left; padding-left: 5px;"><span>' . $pro_title . '</span></td>
                            
                        </tr>                        
                        <tr>
                            <td style="text-align: left; padding-left: 5px;"><span class="label">ผู้ติดต่อ</span></td>
                            <td style="text-align: left; padding-left: 5px;"><span>' . $name . '</span></td>
                            
                        </tr>
                        <tr>
                            <td style="text-align: left; padding-left: 5px;"><span class="label">เบอร์โทร</span></td>
                            <td style="text-align: left; padding-left: 5px;"><span>' . $phone . '</span></td>
                            
                        </tr>
                        <tr>
                            <td style="text-align: left; padding-left: 5px;"><span class="label">อีเมล</span></td>
                            <td style="text-align: left; padding-left: 5px;"><span>' . $email . '</span></td>
                            
                        </tr>
                        ';

                // width: 150px;

                // var_dump($vpage[0]);exit;

                // if($vpage[0]->credit != 0){
                //     if($vpage[0]->pay_type == "percentage"){
                //         $pay = $vpage[0]->pay_sp." %";
                //     }else{
                //         $pay = $vpage[0]->pay_sp." งวด";
                //     }
                //     $credits_s = '
                //     <td style="text-align: left; "><span class="label">เครดิต</span></td>
                //     <td style="text-align: left;">'.$vpage[0]->credit.' วัน</td>
                //     ';
                // }
                // else{                        
                //     $credits_s = '
                //     <td style="text-align: left;"><span class="label">เครดิต</span></td>
                //     <td style="text-align: left;">จ่ายเป็นเงินสด</td>
                //     ';
                // }

                // var_dump($get_ps);exit;
                // if($get_ps->pay_type == "time"){
                //     if(!empty(json_decode($get_ps->pay_sps))){
                //         $pay_detail = '
                //         <td style="text-align: left;"><span class="label">การชำระ</span></td>
                //         <td style="text-align: left;">'.$pay.'</td>
                //     ';
                //     }else if($get_ps->include_deposit == 2){
                //         $pay_detail = '';
                //     }else{

                //     $pay_detail = '';
                //     }

                // }

                $divTop = 10;
                $divleft = 400;
                // var_dump($vpage[0]);exit;
                $info_table = '
                    
                        <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 360px">
                            <table style="width: 100%;">
                                <tr>
                                    <th colspan="4" style="text-align: center; border-bottom: 1.5px solid #999; font-size: 30px; "><span class="label">ใบกำกับภาษี/ใบเสร็จรับเงิน</span><br/><span class="label" style="font-size: 20px;">ต้นฉบับ</span></th>    
                                </tr>
                                <tr>
                                    <td style="text-align: left;  padding-left: 5px; width:120px;"><span class="label">เลขที่</span></td>
                                    <td style="text-align: left; width:100px; padding-left: 5px;"><span>' . $vpage[0]->invDoc_no . '</span></td>
                                    
                                </tr>
                                
                                <tr>
                                    <td style="text-align: left; padding-left: 5px;"><span class="label">วันที่</span></td>
                                    <td style="text-align: left; width:50px; padding-left: 5px;"><span>' . $this->DateConvert($vpage[0]->bill_date) . '</span></td>
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding-left: 5px;"><span class="label">ผู้ขาย</span></td>
                                    <td style="text-align: left; padding-left: 5px;"><span></span>' . $fname . ' ' . $lname . '</td>
                                    
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding-left: 5px; border-bottom: 1.5px solid #999; width: 90px;"><span class="label">อ้างอิง</span></td>
                                    <td style="text-align: left; border-bottom: 1.5px solid #999; padding-left: 5px; width: 240px;"><span>' . $vpage[0]->esDoc_no . '</span></td>                              
                                </tr>
                            
                                <tr>
                                    <td></td>
                                </tr>

                                ' . $info_contact . '
                                
                            </table>
                        </div>
                        ' . $trs_client . '
                        ';

                $info_table2 = '
                    
                        <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 360px">
                            <table style="width: 100%;">
                                <tr>
                                    <th colspan="4" style="text-align: center; border-bottom: 1.5px solid #999; font-size: 30px; "><span class="label">ใบกำกับภาษี/ใบเสร็จรับเงิน</span><br/><span class="label" style="font-size: 20px;">สำเนา</span></th>    
                                </tr>
                                <tr>
                                    <td style="text-align: left;  padding-left: 5px; width:120px;"><span class="label">เลขที่</span></td>
                                    <td style="text-align: left; width:100px; padding-left: 5px;"><span>' . $vpage[0]->invDoc_no . '</span></td>
                                    
                                </tr>
                                
                                <tr>
                                    <td style="text-align: left; padding-left: 5px;"><span class="label">วันที่</span></td>
                                    <td style="text-align: left; width:50px; padding-left: 5px;"><span>' . $this->DateConvert($vpage[0]->bill_date) . '</span></td>
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding-left: 5px;"><span class="label">ผู้ขาย</span></td>
                                    <td style="text-align: left; padding-left: 5px;"><span></span>' . $fname . ' ' . $lname . '</td>
                                    
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding-left: 5px; border-bottom: 1.5px solid #999; width: 90px;"><span class="label">อ้างอิง</span></td>
                                    <td style="text-align: left; border-bottom: 1.5px solid #999; padding-left: 5px; width: 240px;"><span>' . $vpage[0]->esDoc_no . '</span></td>                              
                                </tr>
                            
                                <tr>
                                    <td></td>
                                </tr>

                                ' . $info_contact . '
                                
                            </table>
                        </div>
                        ' . $trs_client . '
                        ';

                $divTop = 590;
                $divleft = 27;
                $paymentmethod = [];
                $checked = '';
                $footer = get_setting("rt_footer");


                $trs_approve = '
                        <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 740px;">
                            <table style="width: 100%;">
                                <tr>
                                    <td style="text-align: left;">' . $footer . '</td>
                                </tr>
                            </table>
                        </div>
                        <br>';

                foreach ($method as $key => $value) {
                    // var_dump($value);
                    if ($key == $vpage[0]->payment_method_id) {
                        $checked = 'checked="checked"';
                    } else {
                        $checked = '';
                    }
                    $paymentmethod[] = '<input type="checkbox" ' . $checked . '> ' . $value . '';
                }
                $ck = implode(' ', $paymentmethod);
                // var_dump($paymentmethod);
                // if(in_array($vpage[0]->payment_method_id,$method)  )
                // {
                //     $checked = 'checked';
                // }
                // else
                // {
                //     $checked = 'wwwww';
                // }
                // $test[] = $checked;
                // var_dump($vpage);exit;
                $divTop = 890;
                $divleft = 27;
                $trs_approve .= '
                        <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 740px;">
                                <table style="width:100%;">
                                    <tr>
                                        <td colspan="6" style="text-align: left; font-size: 20px;">การชำระเงินจะสมบูรณ์เมื่อบริษัทได้รับเงินเรียบร้อยแล้ว : ' . $ck . '</td>
                                    </tr>
                                    <tr>
                                       <td colspan="6" style="text-align: left; font-size: 20px;">ธนาคาร ' . $vpage[0]->namebank . ' เลขที่ ' . $vpage[0]->runnumber . ' วันที่ ' . $this->DateConvert($vpage[0]->payment_date) . ' จำนวนเงิน ' . number_format($vpage[0]->amount, 2, '.', ',') . ' บาท </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" style="text-align: left; font-size: 20px;">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" style="text-align: center; font-size: 20px;">ในนาม ' . $vpage[0]->company_name . '</td>
                                        <td colspan="4" style="text-align: center; font-size: 20px;">ในนาม ' . $company_name . '</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" style="text-align: left; font-size: 20px;">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">_________________</td>
                                        <td colspan="2">_________________</td>
                                        <td colspan="2">' . $user_signature->first_name . ' ' . $user_signature->last_name . '</td>
                                        <td colspan="2">' . $this->DateConvert($user_signature->doc_date) . '</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">ผู้รับสินค้า / บริการ</td>
                                        <td colspan="2">วันที่</td>
                                        <td colspan="2">ผู้อนุมัติ</td>
                                        <td colspan="2">วันที่</td>
                                    </tr>
                                </table>
                        </div>              
                       ';

                //    $trs_approve = '
                //    <div style="position:absolute; top:'.$divTop.'px; left:'.$divleft.'px; width: 740px;">
                //            <table style="width:100%;">
                //    <tr>
                //        <td colspan="2">การชำระเงินจะสมบูรณ์เมื่อบริษัทได้รับเงินเรียบร้อยแล้ว '.$vpage[0]->company_name.'</td>
                //        <td colspan="2">ในนาม '.$company_name.'</td>
                //    </tr>
                //    <tr>
                //        <td style="height: 100px;">___________________________<p>ผู้รับวางบิล</p></td>
                //        <td>___________________________<p>วันที่</p></td>
                //        <td style="width: 10%;"></td>
                //        <td>___________________________<p>ผู้วางบิล</p></td>
                //        <td>___________________________<p>วันที่</p></td>
                //    </tr>                            
                //            </table>
                //    </div>             
                //   ';

                $divTop = 318;
                $divleft = 30;
                if ($vvpage->include_deposit == 2) {
                    $tabletemplate = '
                        <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 730px;">
                            <table style="width:100%; overflow: auto; page-break-inside:avoid">
                                <tr>
                                    <td class="thStyle">#</td>
                                    <td class="thStyle">รายละเอียด</td>
                                    <td class="thStyle"></td>
                                    <td class="thStyle"></td>
                                    <td class="thStyle" style="text-align: right;"></td>
                                    <td class="thStyle" style="text-align: right;">ยอดมัดจำ</td>
                                </tr>
                                ' . implode("", $trs) . '
                                ' . implode("", $trs_total) . '
                                ' . implode("", $trs_note) . '
                            </table>
                            
                        </div>
                    
                            
                        
                        '; //
                } else {
                    $tabletemplate = '
                            <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 730px;">
                                <table style="width:100%; overflow: auto; page-break-inside:avoid">
                                    <tr>
                                        <td class="thStyle">#</td>
                                        <td class="thStyle">รายละเอียด</td>
                                        <td class="thStyle">จำนวน</td>
                                        <td class="thStyle"></td>
                                        <td class="thStyle" style="text-align: right;">ราคาต่อหน่วย</td>
                                        <td class="thStyle" style="text-align: right;">ยอดรวม</td>
                                    </tr>
                                    ' . implode("", $trs) . '
                                    ' . implode("", $trs_total) . '
                                    ' . implode("", $trs_note) . '
                                </table>
                                
                            </div>
                        
                            
                                
                            
                            '; //
                }




                // var_dump($keep);exit;


                $html = ' 
                        <style>
                        div{
                            font-size: 17px;
                            // border: solid 1px #000;
                            // font-weight:bold;
                            text-align: center;
                        }
                        img{
                            width: 200px;
                            height: 100px;
                        }
                        .label{
                            color: #599ebf;
                            
                        }
                        table {
                            font-size: 17px;
                            // border: solid 1px #000;
                            // font-weight: bold;
                            width: 735px; 
                            border-collapse: collapse;
                        }
                        th,td{
                            text-align: center;
                            // border: solid 1px #000;
                        }
                        .thA{
                            width: 10%;
                        }

                        .thStyle{
                            border-top: 2px solid #999;
                            border-bottom: 2px solid #999;
                        }
                        </style>
                        <body>
                
                                     
                                               
                        ' . $tabletemplate . '
                        </body>       
                        
                    ';
                $html2 = ' 
                        <style>
                        div{
                            font-size: 17px;
                            // border: solid 1px #000;
                            // font-weight:bold;
                            text-align: center;
                        }
                        img{
                            width: 200px;
                            height: 100px;
                        }
                        .label{
                            color: #599ebf;
                            
                        }
                        table {
                            font-size: 17px;
                            // border: solid 1px #000;
                            // font-weight: bold;
                            width: 735px; 
                            border-collapse: collapse;
                        }
                        th,td{
                            text-align: center;
                            // border: solid 1px #000;
                        }
                        .thA{
                            width: 10%;
                        }

                        .thStyle{
                            border-top: 2px solid #999;
                            border-bottom: 2px solid #999;
                        }
                        </style>
                        <body>
                
                                     
                                               
                        ' . $tabletemplate . '
                        </body>       
                        
                    ';
                //' . implode('', $divs) . ' 
                $mpdf->SetHTMLHeader($info_table);

                $Note = nl2br($vpage[0]->inNote, false);
                preg_match_all("/(<br>)/", $Note, $matches);

                for ($j = 0; $j < 4; $j++) {
                    // var_dump($page);exit;
                    if ($j <= 4) {
                        $mpdf->SetHTMLFooter($trs_approve);
                        break;

                    } else {
                        $mpdf->SetHTMLFooter($trs_approve);
                        break;
                    }
                }












                // echo $html;exit; 
                $mpdf->SetTitle($vpage[0]->invDoc_no);
                $mpdf->autoPageBreak = false;
                $mpdf->use_kwt = true;
                $mpdf->shrink_tables_to_fit = 1;
                $mpdf->AddPage('P');
                // var_dump($mpdf->AddPage('P'));exit;
                $pagecount = $mpdf->SetSourceFile('pdf_Template/main_template.pdf');
                $tplId = $mpdf->importPage(1);
                $mpdf->useTemplate($tplId);
                $mpdf->WriteHTML($html);

                $mpdf->SetHTMLHeader($info_table2);
                $mpdf->AddPage('P', 'NEXT-EVEN');
                $tplId = $mpdf->importPage(1);
                $mpdf->useTemplate($tplId);
                $mpdf->WriteHTML($html);

            }
            // exit;

        }

        $mpdf->Output();
        // $mpdf->Output($vpage[0]->invDoc_no.'.pdf', \Mpdf\Output\Destination::DOWNLOAD);

    }

    public function materialrequests_pdf($pr_id = 0)
    {
        //$pr_info = $this->Purchaserequests_model->get_details(array("id" => $pr_id))->row();

        $pr_data = get_mr_making_data($pr_id);
        $pr_items = $pr_data['mr_items'];
        $buyer = $pr_data['client_info'];
        $pr_info = $pr_data['mr_info'];
        //$this->dump($pr_data, true);
        // var_dump($pr_data);exit;

        $template = '<div style="text-align: [align]; position:absolute; top:[top]px; left:[left]px; width:[w]px; height:[h]px;"><span>[val]</span></div>';

        $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'fontDir' => array_merge($fontDirs, [
                __DIR__ . '/fonts',
            ]),
            'fontdata' => $fontData + [
                'def' => [
                    'R' => 'THSarabun_Bold.ttf',
                    //'I' => 'THSarabunNew Italic.ttf',
                    //'B' => 'THSarabunNew Bold.ttf',
                ]
            ],
            'default_font' => 'def',
            'tempDir' => '/tmp'
        ]);
        $mpdf->charset_in = 'UTF-8';
        //$mpdf->SetTitle(get_pr_id($pr_id));
        $mpdf->SetTitle($pr_info->doc_no ? $pr_info->doc_no : lang('no_have_doc_no') . ':' . $pr_info->id);

        $keep = array();
        $html = '';
        $company_address = nl2br(get_setting("company_address"));
        $company_phone = get_setting("company_phone");
        $company_website = get_setting("company_website");
        $company_vat_number = get_setting("company_vat_number");
        $company_name = get_setting("company_name");


        /*$sql = "SELECT *,pr.id as pr_id,estimate_items.id as itemId, clients.id as clientId
        FROM `estimates` 
        LEFT JOIN estimate_items ON estimate_items.estimate_id = estimates.id AND estimate_items.deleted = 0
        LEFT JOIN clients ON estimates.client_id = clients.id
        WHERE estimates.id = $pr_id";*/
        //$pr_data = get_pr_making_data($pr_id);
        //$pr_items = $pr_data['pr_items'];
        // AND estimate_items.deleted = 0 $this->session->user_id
        //หัวตารากระดาษ------------------------------------------------------------------------------------------------------------


        $cal_payment = $this->Materialrequests_model->get_mr_total_summary($pr_id);
        $vat_name = $cal_payment->tax_name != null ? $cal_payment->tax_name : "-";

        if ($cal_payment->discount_total == '') {
            $trs_total[] = '                
                    <tr>
                    <td colspan="5" style="border-top: 1.5px solid #999;"></td>                            
                        <td colspan="2" style="text-align: right; margin-top: 4%; border-top: 1.5px solid #999;"><span class="label">รวมเป็นเงิน</span></td>                            
                        <td style="text-align: right; border-top: 1.5px solid #999;">' . number_format($cal_payment->mr_subtotal, 2, '.', ',') . ' บาท</td>
                    </tr>
                    
                    <tr>
                        <td colspan="5"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label">' . $vat_name . '</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->tax, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                        
                        <td colspan="5" style="text-align: left;"><span>(' . $this->Convert($cal_payment->mr_total) . ')</span></td> 
                                              
                        <td colspan="2" style="text-align: right;"><span class="label" >จำนวนเงินรวมทั้งสิ้น</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->mr_total, 2, '.', ',') . ' บาท</td>
                    </tr>

                ';
        } else

            if ($cal_payment->discount_type == "before_tax") {
                $trs_total[] = '
                    <tr>
                        <td colspan="5" style="border-top: 1.5px solid #999;"></td>                            
                        <td colspan="2" style="text-align: right; margin-top: 4%; border-top: 1.5px solid #999;"><span class="label">รวมเป็นเงิน</span></td>                            
                        <td style="text-align: right; border-top: 1.5px solid #999;">' . number_format($cal_payment->mr_subtotal, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                        <td colspan="5"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label"><u>หัก</u> ส่วนลด</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->discount_total, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                        <td colspan="5"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label">ราคาหลังส่วนลด</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->mr_subtotal - $cal_payment->discount_total, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                        <td colspan="5"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label">' . $vat_name . '</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->tax, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                    
                        <td colspan="5" style="text-align: left;"><span>(' . $this->Convert($cal_payment->mr_total) . ')</span></td> 
                        <td colspan="2" style="text-align: right;"><span class="label" >จำนวนเงินรวมทั้งสิ้น</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->mr_total, 2, '.', ',') . ' บาท</td>
                    </tr>

                ';
                // <td ><span>'.$this->Convert($cal_payment->estimate_total).'</span></td> 


            } else if ($cal_payment->discount_type == "after_tax") {

                $trs_total[] = '
                    <tr>
                        <td colspan="3" style="border-top: 1.5px solid #999;"></td>                            
                        <td colspan="2" style="text-align: right; border-top: 1.5px solid #999;"><span class="label">ราคาก่อนหักส่วนลด</span></td>                            
                        <td style="text-align: right; border-top: 1.5px solid #999;">' . number_format($cal_payment->mr_subtotal, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                        <td colspan="3"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label"><u>หัก</u> ส่วนลด</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->discount_total, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                        <td colspan="3"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label">' . $vat_name . '</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->tax, 2, '.', ',') . ' บาท</td>
                    </tr>
                    <tr>
                        <td colspan="3"></td> 
                        <td colspan="2" style="text-align: right;"><span class="label">ราคาหลังส่วนลด</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->mr_subtotal - $cal_payment->discount_total, 2, '.', ',') . ' บาท</td>
                    </tr>
                    
                    <tr>
                   
                        <td colspan="3" style="text-align: left;"><span>(' . $this->Convert($cal_payment->mr_total) . ')</span></td> 
                        
                        <td colspan="2" style="text-align: right;"><span class="label" >จำนวนเงินรวมทั้งสิ้น</span></td>
                        <td style="text-align: right;">' . number_format($cal_payment->mr_total, 2, '.', ',') . ' บาท</td>
                    </tr>

                ';

            }


        $marks[1] = array();
        $user_signature = $this->Db_model->userSignature($pr_info->requester_id);
        $top = 1020;
        $left = 65;
        if ($user_signature) {
            if ($user_signature->signature == '') {
                $top = 990;
                $marks[1][] = array('key' => 'buyer_signauture', '[left]' => $left, '[w]' => 170, '[top]' => $top, '[val]' => $user_signature->first_name . ' ' . $user_signature->last_name, '[align]' => 'center');
            } else {
                $top = 930;
                $marks[1][] = array('key' => 'buyer_signauture', '[left]' => $left, '[w]' => 170, '[top]' => $top, '[val]' => '<img src=' . base_url() . $user_signature->signature . '>', '[align]' => 'center');
            }
            $top = 1035;
            $marks[1][] = array('key' => 'buyer_date_approved', '[left]' => $left, '[w]' => 170, '[top]' => $top, '[val]' => $this->DateConvert($pr_info->mr_date), '[align]' => 'center');
        }


        $left = 555;
        $user_signature = $this->Db_model->signature_approve($pr_id, "purchaserequests");
        // var_dump($user_signature);exit;
        if ($user_signature) {
            if ($user_signature->signature == '') {
                $top = 990;
                $marks[1][] = array('key' => 'signauture', '[left]' => $left, '[w]' => 170, '[top]' => $top, '[val]' => $user_signature->first_name . ' ' . $user_signature->last_name, '[align]' => 'center');
            } else {
                $top = 930;
                $marks[1][] = array('key' => 'signauture', '[left]' => $left, '[w]' => 170, '[top]' => $top, '[val]' => '<img src=' . base_url() . $user_signature->signature . '>', '[align]' => 'center');
            }
            $top = 1035;

            $marks[1][] = array('key' => 'date_approved', '[left]' => $left, '[w]' => 170, '[top]' => $top, '[val]' => $this->DateConvert($user_signature->doc_date), '[align]' => 'center');
        }

        //End Item-List------------------------------------------------------------------------------------------------------------


        $rangItem = 13;

        foreach ($pr_items as $parentKey => $child) {

            if (!isset($keep[$child->mr_id])) {
                $page = 1;
            }

            $keep[$child->mr_id][$page][] = $child;

            if (count($keep[$child->mr_id][$page]) == $rangItem) {
                $page += 1;
            }

        }



        foreach ($keep as $kd => $kv) {
            //  var_dump($kv);exit;
            $i = 1;
            foreach ($kv as $kpage => $vpage) {
                $dbs = convertObJectToArray($vpage[0]);
                $dbs = array_merge($dbs, [
                    'pr_date' => $this->DateConvert($pr_info->mr_date),
                    'price_before_dis' => '<span class="label">ราคาก่อนหักส่วนลด</span>',
                    'discount_name' => '<span class="label"><u>หัก</u> ส่วนลด</span>',
                    'price_after_dis' => '<span class="label">ราคาหลังส่วนลด</span>',
                    'vat_name' => '<span class="label">' . isset($cal_payment->tax_name) ? '<span class="label">' . $cal_payment->tax_name . '</span>' : "-" . '</span>',
                    'total_name' => '<span class="label">รวมเงินทั้งสิ้น</span>'

                    //,'valid_until' => $this->DateConvert($vpage[0]->valid_until)
                ]);

                $divs = array();

                foreach ($marks[1] as $km => $vm) {
                    $vm['[align]'] = isset($vm['[align]']) ? $vm['[align]'] : 'left';
                    $vm['[val]'] = isset($dbs[$vm['key']]) ? $dbs[$vm['key']] : $vm['[val]'];
                    $divs[] = str_replace(array_keys($vm), $vm, $template);
                }
                $top = 315;

                $trs = array();
                foreach ($vpage as $vkpage => $vvpage) {

                    $marks[2] = array(); //สร้าง array แยกแต่ละรายการ
                    if ($vvpage->title) {
                        $trs[] = '
                            <tr>
                                <td style="width: 10%; ">' . $i++ . '</td>
                                <td style="width: 10%; ">' . $vvpage->code . '</td>
                                <td style="text-align: left; width: 70%; ">' . $vvpage->title . '</td>
                                <td style=" width: 20%; ">' . number_format($vvpage->quantity, 0, '.', ',') . '</td>
                            </tr>
                            ';
                    } else {
                        $trs[] = '
                            <tr>
                                <td style="width: 5%;"></td>
                                <td style="width: 20%;"></td>
                                <td style="width: 15%;"></td>
                                <td style="width: 15%;"></td>
                                <td style=" width: 6%;"></td>                                
                                <td style=" width: 6%;"></td>
                                <td ></td>
                                <td ></td>
                            </tr>
                            ';
                    }


                    $dbs = convertObJectToArray($vvpage);

                    foreach ($marks[2] as $km => $vm) {
                        $vm['[align]'] = isset($vm['[align]']) ? $vm['[align]'] : 'center';
                        $vm['[val]'] = isset($dbs[$vm['key']]) ? $dbs[$vm['key']] : $vm['[val]'];
                        $divs[] = str_replace(array_keys($vm), $vm, $template);
                    }

                }

                $trs_note = array();
                $str_text = explode("-", $pr_info->note);
                if ($pr_info->note) {
                    $trs_note[] = '
                        <tr>
                            <td colspan="3" style="text-align: left;"><br/><p class="label">หมายเหตุ : </p>' . nl2br($pr_info->note) . '</td>
                        </tr>
                    ';
                }

                if ($pr_info->created_by_user != '') {
                    $created_by_user = $pr_info->created_by_user;
                } else {
                    $created_by_user = "-";
                }


                $divTop = 110;
                $divleft = 30;
                $trs_client = '
                    <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 370px">
                        <table style="width: 100%;">
                            <tr>
                                <td style="text-align: left;">' . $company_name . '<br/>' . $company_address . '<br/>หมายเลขประจำตัวผู้เสียภาษี ' . $company_vat_number . '<br/>' . $company_phone . '</td>
                            </tr>
                            <tr>
                            
                                <td style="text-align: left;"><span class="label"><br/>ผู้ขอเบิก</span> ' . $created_by_user . '</td>
                            </tr>
                            <tr>
                                <td style="text-align: left;">' . $buyer->first_name . ' ' . $buyer->last_name . '<br/> ตำแหน่ง : ' . $buyer->job_title . '<br/>อีเมล : ' . $buyer->email . '<br/></td>
                            </tr>
                        </table>
                    </div>
                    ';

                $trs_ref = array();
                //var_dump($pr_info);exit;

                if ($pr_info->project_name != '') {
                    $project_ref = $pr_info->project_name;
                } else {
                    $project_ref = "-";
                }



                if ($pr_info->category_name != '') {
                    $category_name_ref = $pr_info->category_name;
                } else {

                    $category_name_ref = "-";
                }
                $trs_ref[] = '                        
                        <tr>
                            <td style="text-align: left; padding-left: 5px;"><span class="label">ชื่อโปรเจค</span></td>
                            <td style="text-align: left;">' . $project_ref . '</td>
                        </tr>
                        <tr>
                            <td style="text-align: left; padding-left: 5px;"><span class="label">ชื่อผู้ลูกค้า</span></td>
                            <td style="text-align: left;">-</td>
                        </tr>
                    ';

                $divTop = 60;
                $divleft = 410;
                if ($pr_info->payment) {
                    $payment = $pr_info->payment;
                } else {
                    $payment = "-";
                }


                $info_table = '
                        <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 340px">
                            <table style="width: 100%;">
                                <tr>
                                    <th colspan="2" style="text-align: center; padding-left: 5px; border-bottom: 1.5px solid #999; font-size: 30px;"><span class="label">ใบขอเบิก</span></th>
                                    
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding-left: 5px;"><span class="label">เลขที่</span></td>
                                    <td style="text-align: left; "><span>' . $pr_info->doc_no . '</span></td>
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding-left: 5px;"><span class="label">วันที่</span></td>
                                    <td style="text-align: left;"><span>' . $this->DateConvert($pr_info->mr_date) . '</span></td>
                                </tr>

                                <tr>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td></td>
                                </tr>
                                ' . implode("", $trs_ref) . '

                            </table>
                        </div>';



                $divTop = 320;
                $divleft = 30;
                $tabletemplate = '
                    <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 720px">
                        <table>
                            <tr>
                                <td class="thStyle">#</td>
                                <td class="thStyle">รหัสวัตถุดิบ</td>
                                <td class="thStyle">รายละเอียด</td>
                                <td class="thStyle">จำนวน</td>
                            </tr>
                            ' . implode("", $trs) . '
                            ' . implode("", $trs_note) . '
                        </table>
                    </div>';

                $divTop = 990;
                $divleft = 27;
                $trs_approve = '<div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 750px; ">
                        <table>
                            <tr>
                                <td>___________________________<br/>ผู้จัดทำ</td>
                                <td>___________________________<br/>ผู้ตวจสอบ</td>
                                <td>___________________________<br/>ผู้อนุมัติ</td>
                            </tr>
                            <tr>
                                <td>___________________________<br/>วันที่</td>
                                <td>___________________________<br/>วันที่</td>
                                <td>___________________________<br/>วันที่</td>
                            </tr>                            
                        </table>
                    </div>';

                $html = ' 
                            <style>
                            div{
                                font-size: 17px;
                                // border: solid 1px #000;
                                // font-weight:bold;
                                text-align: center;
                            }
                            img{
                                width: 200px;
                                height: 100px;
                            }
                            .label{
                                color: blue;
                                
                            }

                            table {
                                font-size: 17px;
                                // border: solid 1px #000;
                                width: 735px; 
                                border-collapse: collapse;
                            }
                            th,td{
                                text-align: center;
                                // border: solid 1px #000;
                            }
                            .thA{
                                width: 10%;
                            }

                            .thStyle{
                                border-top: 2px solid #999;
                                border-bottom: 2px solid #999;
                            }

                            </style>
    
                            
                            ' . $info_table . '
                            ' . $tabletemplate . '
                            ' . $trs_approve . '
                            ' . $trs_client . '
                            ' . implode('', $divs) . '
                            
                        ';
                $mpdf->AddPage('P');
                $pagecount = $mpdf->SetSourceFile('pdf_Template/main_template.pdf');
                $tplId = $mpdf->importPage(1);
                $mpdf->useTemplate($tplId);
                $mpdf->WriteHTML($html);
            }

            // var_dump($buyer);exit;
        }

        $mpdf->Output();
        // $mpdf->Output($pr_info->doc_no.'.pdf', \Mpdf\Output\Destination::DOWNLOAD);
    }



    public function deliverys_pdf($delivery_id = 0)
    {
        $template = '<div style="text-align: [align]; position:absolute; top:[top]px; left:[left]px; width:[w]px; height:[h]px;"><span>[val]</span></div>';

        $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];


        $mpdf = new \Mpdf\Mpdf([
            'fontDir' => array_merge($fontDirs, [
                __DIR__ . '/fonts',
            ]),
            'fontdata' => $fontData + [
                'def' => [
                    'R' => 'THSarabun_Bold.ttf',
                    //'I' => 'THSarabunNew Italic.ttf',
                    //'B' => 'THSarabunNew Bold.ttf',
                ]
            ],
            'default_font' => 'def',
            'tempDir' => '/tmp'
        ]);
        $mpdf->charset_in = 'UTF-8';


        $keep = array();
        $html = '';
        $company_address = nl2br(get_setting("company_address"));
        $company_phone = get_setting("company_phone");
        $company_website = get_setting("company_website");
        $company_vat_number = get_setting("company_vat_number");
        $company_name = get_setting("company_name");


        $sql = "SELECT *,deliverys.id as esId,delivery_items.id as itemId, clients.id as clientId , deliverys.note as es_note, projects.title as protitle, delivery_items.title as esITitle
            FROM `deliverys` 
            LEFT JOIN delivery_items ON delivery_items.delivery_id = deliverys.id AND delivery_items.deleted = 0
            LEFT JOIN clients ON deliverys.client_id = clients.id            
            LEFT JOIN users ON clients.id = users.client_id AND users.is_primary_contact = 1
            LEFT JOIN projects ON projects.id = deliverys.project_id
            WHERE deliverys.id = $delivery_id;";

        //arr($sql);exit;




        $data = $this->Db_model->creatBy($delivery_id, "deliverys");
        $fname = $data->first_name;
        $lname = $data->last_name;
        $deliverys_data = get_deliverys_making_data($delivery_id);
        $buyer = $deliverys_data['client_info'];
        // var_dump($buyer);exit;
        // exit;


        $cal_payment = $this->Deliverys_model->get_delivery_total_summary($delivery_id);
        // var_dump($cal_payment);exit;
        if ($cal_payment->tax_name) {
            $tax_name = $cal_payment->tax_name;
        } else {
            $tax_name = "-";
        }

        $tax_name2 = isset($cal_payment->tax_name2) ? $cal_payment->tax_name2 : "-";
        if ($cal_payment->tax_id == 1) {
            $tax_val = ($cal_payment->delivery_subtotal - $cal_payment->discount_total) + $cal_payment->tax;
        } else {
            $tax_val = ($cal_payment->delivery_subtotal - $cal_payment->discount_total) - $cal_payment->tax;
        }

        //     if($cal_payment->tax2 != NULL){
        //         $trs_tax2 = '

        //             <tr>
        //                 <td colspan="3"></td> 
        //                 <td colspan="3" style="border-top: 2px solid #999; height:10px;" ></td> 

        //             </tr>

        //             <tr>
        //                 <td colspan="2"></td> 
        //                 <td colspan="3" style="text-align: right;"><span class="label">'.$tax_name2.'</span></td>
        //                 <td style="text-align: right;">'.number_format($cal_payment->tax2,2,'.',',').' บาท</td>
        //             </tr>
        //             <tr>
        //                 <td colspan="2"></td> 

        //                 <td colspan="3" style="text-align: right;"><span class="label" >ยอดชำระ</span></td>
        //                 <td style="text-align: right;">'.number_format($cal_payment->delivery_total,2,'.',',').' บาท</td>
        //             </tr>

        //         ';
        //  }

        //     if($cal_payment->discount_total == ''){

        //         $trs_total[] = '
        //                 <tr>
        //                     <td colspan="2"></td>                            
        //                     <td colspan="3" style="text-align: right; margin-top: 4%"><span class="label">รวมเป็นเงิน</span></td>                            
        //                     <td style="text-align: right;">'.number_format($cal_payment->delivery_subtotal,2,'.',',').' บาท</td>
        //                 </tr>                        
        //                 <tr>
        //                     <td colspan="2"></td> 
        //                     <td colspan="3" style="text-align: right;"><span class="label">'.$tax_name.'</span></td>
        //                     <td style="text-align: right;">'.number_format($cal_payment->tax,2,'.',',').' บาท</td>
        //                 </tr>
        //                 <tr>

        //                     <td colspan="2" style="text-align: left;"><span>('.$this->Convert($cal_payment->delivery_total).')</span></td> 

        //                     <td colspan="3" style="text-align: right;"><span class="label" >จำนวนเงินรวมทั้งสิ้น</span></td>
        //                     <td style="text-align: right;">'.number_format($tax_val,2,'.',',').' บาท</td>
        //                 </tr>
        //                 '.$trs_tax2.'

        //             ';            
        //     }else if($cal_payment->discount_type == "before_tax"){



        //         $trs_total[] = '
        //             <tr>
        //                 <td colspan="2"></td>                            
        //                 <td colspan="3" style="text-align: right;"><span class="label">ราคาก่อนหักส่วนลด</span></td>                            
        //                 <td style="text-align: right;">'.number_format($cal_payment->delivery_subtotal,2,'.',',').'</td>
        //             </tr>
        //             <tr>
        //                 <td colspan="2"></td> 
        //                 <td colspan="3" style="text-align: right;"><span class="label"><u>หัก</u> ส่วนลด</span></td>
        //                 <td style="text-align: right;">'.number_format($cal_payment->discount_total,2,'.',',').'</td>
        //             </tr>
        //             <tr>
        //                 <td colspan="2"></td> 
        //                 <td colspan="3" style="text-align: right;"><span class="label">ราคาหลังส่วนลด</span></td>
        //                 <td style="text-align: right;">'.number_format($cal_payment->delivery_subtotal-$cal_payment->discount_total,2,'.',',').'</td>
        //             </tr>
        //             <tr>
        //                 <td colspan="2"></td> 
        //                 <td colspan="3" style="text-align: right;"><span class="label">'.$tax_name.'</span></td>
        //                 <td style="text-align: right;">'.number_format($cal_payment->tax,2,'.',',').'</td>
        //             </tr>

        //             <tr>

        //                 <td colspan="2" style="text-align: left;"><span>'.$this->Convert($cal_payment->delivery_total).'</span></td> 
        //                 <td colspan="3" style="text-align: right;"><span class="label" >จำนวนเงินรวมทั้งสิ้น</span></td>
        //                 <td style="text-align: right;">'.number_format($tax_val,2,'.',',').'</td>
        //             </tr>
        //             '.$trs_tax2.'

        //         ';

        //     }else if($cal_payment->discount_type == "after_tax"){

        //         $trs_total[] = '
        //             <tr>
        //                 <td colspan="2"></td>                            
        //                 <td colspan="3" style="text-align: right;"><span class="label">ราคาก่อนหักส่วนลด</span></td>                            
        //                 <td style="text-align: right;">'.number_format($cal_payment->delivery_subtotal,2,'.',',').'</td>
        //             </tr>
        //             <tr>
        //                 <td colspan="2"></td> 
        //                 <td colspan="3" style="text-align: right;"><span class="label">'.$tax_name.'</span></td>
        //                 <td style="text-align: right;">'.number_format($cal_payment->tax,2,'.',',').'</td>
        //             </tr>
        //             <tr>
        //                 <td colspan="2"></td> 
        //                 <td colspan="3" style="text-align: right;"><span class="label"><u>หัก</u> ส่วนลด</span></td>
        //                 <td style="text-align: right;">'.number_format($cal_payment->discount_total,2,'.',',').'</td>
        //             </tr>

        //             <tr>
        //                 <td colspan="2"></td> 
        //                 <td colspan="3" style="text-align: right;"><span class="label">ราคาหลังส่วนลด</span></td>
        //                 <td style="text-align: right;">'.number_format($cal_payment->delivery_subtotal-$cal_payment->discount_total,2,'.',',').'</td>
        //             </tr>

        //             <tr>                        
        //                 <td colspan="2" style="text-align: left;"><span>'.$this->Convert($cal_payment->delivery_total).'</span></td>                         
        //                 <td colspan="3" style="text-align: right;"><span class="label" >จำนวนเงินรวมทั้งสิ้น</span></td>
        //                 <td style="text-align: right;">'.number_format($tax_val,2,'.',',').'</td>
        //             </tr>
        //             '.$trs_tax2.'

        //         ';

        //     }







        /* <img src="<?php echo get_file_from_setting($delivery_logo, get_setting('only_file_path')); ?>" />*/
        $marks[1] = array();
        $top = 1020;
        $left = 90;
        $user_signature = $this->Db_model->signature_approve($delivery_id, "deliverys");
        // var_dump($user_signature);exit;
        if ($user_signature) {
            if ($user_signature->signature == '') {
                $top = 990;
                $marks[1][] = array('key' => 'signauture', '[left]' => $left, '[w]' => 130, '[top]' => $top, '[val]' => $user_signature->first_name . ' ' . $user_signature->last_name, '[align]' => 'center');
            } else {
                $top = 930;
                $marks[1][] = array('key' => 'signauture', '[left]' => $left, '[w]' => 130, '[top]' => $top, '[val]' => '<img src=' . base_url() . $user_signature->signature . '>', '[align]' => 'center');
            }
            $top = 1035;
            $left = 570;
            $marks[1][] = array('key' => 'date_approved', '[left]' => $left, '[w]' => 130, '[top]' => $top, '[val]' => $this->DateConvert($user_signature->doc_date), '[align]' => 'center');
        }

        if ($user_signature->signature == '') {
            $top = 990;
            $marks[1][] = array('key' => 'signauture', '[left]' => $left, '[w]' => 130, '[top]' => $top, '[val]' => $user_signature->first_name . ' ' . $user_signature->last_name, '[align]' => 'center');
        } else {
            $top = 930;
            $marks[1][] = array('key' => 'signauture', '[left]' => $left, '[w]' => 130, '[top]' => $top, '[val]' => '<img src=' . base_url() . $user_signature->signature . '>', '[align]' => 'center');
        }
        $top = 1035;
        $left = 90;
        $marks[1][] = array('key' => 'date_approved', '[left]' => $left, '[w]' => 130, '[top]' => $top, '[val]' => $this->DateConvert($user_signature->doc_date), '[align]' => 'center');




        //End Item-List------------------------------------------------------------------------------------------------------------


        $rangItem = 9;

        foreach ($this->Db_model->fetchAll($sql) as $parentKey => $child) {

            if (!isset($keep[$child->esId])) {
                $page = 1;
            }

            $keep[$child->esId][$page][] = $child;

            if (count($keep[$child->esId][$page]) == $rangItem) {
                $page += 1;
            }

        }

        foreach ($keep as $kd => $kv) {

            $i = 1;
            foreach ($kv as $kpage => $vpage) {
                $dbs = convertObJectToArray($vpage[0]);
                $dbs = array_merge($dbs, [
                    'delivery_date' => $this->DateConvert($vpage[0]->delivery_date),
                    'valid_until' => $this->DateConvert($vpage[0]->valid_until),
                    'price_before_dis' => '<span class="label">ราคาก่อนหักส่วนลด</span>',
                    'discount_name' => '<span class="label"><u>หัก</u> ส่วนลด</span>',
                    'price_after_dis' => '<span class="label">ราคาหลังส่วนลด</span>',
                    'vat_name' => '<span class="label">' . isset($cal_payment->tax_name) ? '<span class="label">' . $cal_payment->tax_name . '</span>' : "-" . '</span>',
                    'total_name' => '<span class="label">รวมเงินทั้งสิ้น</span>',

                ]);

                $divs = array();

                foreach ($marks[1] as $km => $vm) {
                    $vm['[align]'] = isset($vm['[align]']) ? $vm['[align]'] : 'left';
                    $vm['[val]'] = isset($dbs[$vm['key']]) ? $dbs[$vm['key']] : $vm['[val]'];
                    $divs[] = str_replace(array_keys($vm), $vm, $template);
                }
                $top = 326;
                $trs = array();

                foreach ($vpage as $vkpage => $vvpage) {


                    if ($vvpage->esITitle) {
                        $trs[] = '
                            <tr>
                                <td style="width: 5%; border-bottom: 1px solid #999; vertical-align: top;">' . $i++ . '</td>
                                <td style="text-align: left; width: 55%; border-bottom: 1px solid #999;">' . $vvpage->esITitle . '<br/>' . $vvpage->description . '</td>
                                <td style=" width: 6%; border-bottom: 1px solid #999; text-align: center; vertical-align: top;">' . number_format($vvpage->quantity, 0, '.', ',') . '</td>
                                <td style=" width: 6%; border-bottom: 1px solid #999; text-align: center; vertical-align: top;">' . $vvpage->unit_type . '</td>                                
                            </tr>
                            ';
                    } else {
                        $trs[] = '
                            <tr>
                                <td style="width: 3%;"></td>
                                <td style="width: 63%;"></td>
                                <td style=" width: 6%;"></td>
                                <td style=" width: 6%;"></td>                                

                            </tr>
                            ';
                    }


                    $marks[2] = array(); //สร้าง array แยกแต่ละรายการ

                    $dbs = convertObJectToArray($vvpage);

                    foreach ($marks[2] as $km => $vm) {
                        $vm['[align]'] = isset($vm['[align]']) ? $vm['[align]'] : 'center';
                        $vm['[val]'] = isset($dbs[$vm['key']]) ? $dbs[$vm['key']] : $vm['[val]'];
                        $divs[] = str_replace(array_keys($vm), $vm, $template);
                    }

                }

                $footer = get_setting("rt_footer");
                $trs_note = array();
                // var_dump($vpage[0]);exit;

                $trs_note[] = '
                        <tr>
                            <td colspan="4" style="text-align: left;"><br/>' . $footer . '</td>
                        </tr>
                    ';


                // $divTop =   110;
                // $divleft = 27;
                // $trs_client ='
                // <div style="position:absolute; top:'.$divTop.'px; left:'.$divleft.'px; width: 390px">
                //     <table style="width: 100%; ">
                //         <tr>
                //             <td style="text-align: left;">'.$company_name.'<br/>'.$company_address.'<br/>หมายเลขประจำตัวผู้เสียภาษี '.$company_vat_number.'<br/>'.$company_phone.'</td>
                //         </tr>
                //         <tr>
                //             <td style="text-align: left;"><span class="label"><br/>ลูกค้า</span></td>
                //         </tr>
                //         <tr>
                //             <td style="text-align: left;">'.$vpage[0]->company_name.'<br/>'.$vpage[0]->address.' '.$vpage[0]->city.' '.$vpage[0]->state.' '.$vpage[0]->zip.' '.$vpage[0]->country.'<br/>'.'เลขประจำตัวผู้เสียภาษี '.$vpage[0]->vat_number.'</td>
                //         </tr>
                //     </table>
                // </div>
                // ';

                $divTop = 110;
                $divleft = 30;
                $trs_client = '
                    <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 370px">
                        <table style="width: 100%;">
                            <tr>
                                <td style="text-align: left;">' . $company_name . '<br/>' . $company_address . '<br/>หมายเลขประจำตัวผู้เสียภาษี ' . $company_vat_number . '<br/>' . $company_phone . '</td>
                            </tr>
                            <tr>
                            
                                <td style="text-align: left;"><span class="label"><br/>ผู้ส่งของ</span></td>
                            </tr>
                            <tr>
                                <td style="text-align: left;">' . $buyer->first_name . ' ' . $buyer->last_name . '<br/> ตำแหน่ง : ' . $buyer->job_title . '<br/> อีเมล : ' . $buyer->email . '<br/> เบอร์โทร : ' . $buyer->phone . '</td>
                            </tr>
                        </table>
                    </div>
                    ';

                $divTop += 210;
                $divleft = 27;
                $tabletemplate = '
                    <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 1000px">
                        <table>
                            <tr>
                                <td class="thStyle">#</td>
                                <td class="thStyle">รายละเอียด</td>
                                <td class="thStyle">จำนวน</td>
                                <td class="thStyle">หน่วยนับ</td>
                            </tr>
                            ' . implode("", $trs) . '
                            ' . implode("", $trs_note) . '
                        </table>
                    </div>';
                $info_contact = array();

                $project_title = isset($vpage[0]->protitle) ? $vpage[0]->protitle : "-";

                $info_contact[] = '
                         <tr>
                            <td style="text-align: left; padding-left: 5px;"><span class="label">ชื่องาน</span></td>
                            <td style="text-align: left;"><span>' . $project_title . '</span></td>
                            <td colspan="2"></td>
                        </tr>
                    ';


                if ($vpage[0]->first_name) {
                    $info_contact[] = '
                        
                        <tr>
                            <td style="text-align: left; padding-left: 5px;"><span class="label">ผู้ติดต่อ</span></td>
                            <td style="text-align: left;"><span>' . $vpage[0]->first_name . ' ' . $vpage[0]->last_name . '</span></td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td style="text-align: left; padding-left: 5px;"><span class="label">เบอร์โทร</span></td>
                            <td style="text-align: left;"><span>' . $vpage[0]->phone . '</span></td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td style="text-align: left; padding-left: 5px;"><span class="label">อีเมล</span></td>
                            <td style="text-align: left;"><span>' . $vpage[0]->email . '</span></td>
                            <td colspan="2"></td>
                        </tr>';
                }


                // <tr>
                //                 <td style="text-align: left; width: 50px;"><span class="label">อ้างอิง</span></td>
                //                 <td style="text-align: left;"><span>(free text)</span></td>
                //             </tr>

                if ($vpage[0]->credit != 0) {
                    $pay_c = $vpage[0]->credit . " วัน";
                    if ($vpage[0]->pay_type == "percentage") {
                        $pay = $vpage[0]->pay_sp . " %";
                    } else {
                        $pay = $vpage[0]->pay_sp . " งวด";
                    }
                    $pay_detail = '
                        <td style="text-align: left; width: 50px; border-bottom: 1.5px solid #999;"><span class="label">การชำระ</span></td>
                        <td style="text-align: left; border-bottom: 1.5px solid #999;">' . $pay . '</td>
                        ';
                } else {
                    $pay_c = "จ่ายเป็นเงินสด";
                    $pay_detail = '';
                }


                // var_dump($pay);
                // var_dump($vpage[0]);exit;

                $divTop = 50;
                $divleft = 420;
                $info_table = '
                        <div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 340px">
                            <table style="width: 100%">
                                <tr>
                                    <th colspan="4" style="text-align: center; border-bottom: 1.5px solid #999; font-size: 30px;"><span class="label">ใบส่งของ</span></th>
                                    
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding-left: 5px; width: 70px;"><span class="label" >เลขที่</span></td>
                                    <td style="text-align: left;"><span>' . $vpage[0]->doc_no . '</span></td>
                                    <td colspan="2"></td>
                                </tr>
                                
                                
                                <tr>
                                    <td style="text-align: left; padding-left: 5px;"><span class="label">วันที่</span></td>
                                    <td style="text-align: left;"><span>' . $this->DateConvert($vpage[0]->delivery_date) . '</span></td>
                                    <td colspan="2"></td>
                                </tr>

                                <tr>
                                    <td colspan="4" style="text-align: left; border-bottom: 1.5px solid #999;"></td>
                                </tr>
                                
                                ' . implode("", $info_contact) . '
                            </table>
                        </div>';

                // <tr>
                //             <td style="text-align: left; border-bottom: 1.5px solid #999; padding-left: 5px;"><span class="label">อ้างอิงใบสั่งซื้อ</span></td>
                //             <td style="text-align: left; border-bottom: 1.5px solid #999;"><span> - </span></td>
                //         </tr>

                $divTop = 990;
                $divleft = 27;
                $trs_approve = '<div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; width: 750px; ">
                            <table>
                                <tr>
                                    <td>___________________________<br/>ผู้ส่งของ</td>
                                    <td>___________________________<br/>ผู้ตวจสอบ</td>
                                    <td>___________________________<br/>ผู้รับของ</td>
                                </tr>
                                <tr>
                                    <td>___________________________<br/>วันที่</td>
                                    <td>___________________________<br/>วันที่</td>
                                    <td>___________________________<br/>วันที่</td>
                                </tr>                            
                            </table>
                        </div>';


                // $divTop =   990;
                // $divleft = 27;
                // $trs_approve = '<div style="position:absolute; top:'.$divTop.'px; left:'.$divleft.'px; width: 750px; ">
                //     <table>
                //         <tr>
                //             <td colspan="2">ในนาม '.$vpage[0]->company_name.'</td>
                //             <td></td>
                //             <td colspan="2">ในนาม '.$company_name.'</td>
                //         </tr>
                //         <tr>
                //             <td style="height: 100px;">___________________________<p>ผู้ขอยืม-คืนสินค้า</p></td>
                //             <td>___________________________<p>วันที่</p></td>
                //             <td style="width: 10%;"></td>
                //             <td>___________________________<p>ผู้อนุมัติ</p></td>
                //             <td>___________________________<p>วันที่</p></td>
                //         </tr>                            
                //     </table>
                // </div>';

                $divTop = 5;
                $divleft = -60;
                $delivery_logo = "delivery_logo";
                $logos = '<img src="' . get_file_from_setting($delivery_logo, get_setting('only_file_path')) . '" style="width: 300px"/>';

                $logo_es = '<div style="position:absolute; top:' . $divTop . 'px; left:' . $divleft . 'px; ">
                        ' . $logos . '
                    </div>';


                $html = ' 
                            <style>
                            div{
                                font-size: 17px;
                                // border: solid 1px #000;
                                // font-weight:bold;
                                text-align: center;
                            }
                            img{
                                width: 200px;
                                height: 100px;
                            }
                            .label{
                                color: #CD853F  ;
                                
                            }

                            table {
                                font-size: 17px;
                                // border: solid 1px #000;
                                width: 735px; 
                                border-collapse: collapse;
                            }
                            th,td{
                                text-align: center;
                                // border: solid 1px #000;
                            }
                            .thA{
                                width: 10%;
                            }

                            .thStyle{
                                border-top: 2px solid #999;
                                border-bottom: 2px solid #999;
                            }

                            .text_info{
                                padding-left: 5px; 
                            }
                           
                            </style>
                            ' . $logo_es . '
                            ' . $trs_client . '
                            ' . $info_table . '
                            ' . $tabletemplate . '
                            ' . $trs_approve . '
                            ' . implode('', $divs) . '
                        ';

                //  . implode('', $divs) . 
                $mpdf->SetTitle($vpage[0]->doc_no);
                $mpdf->AddPage('P');
                $pagecount = $mpdf->SetSourceFile('pdf_Template/template.pdf');
                $tplId = $mpdf->importPage(1);
                $mpdf->useTemplate($tplId);
                $mpdf->WriteHTML($html);
            }


        }

        $mpdf->Output();
        // $mpdf->Output($vpage[0]->doc_no.'.pdf', \Mpdf\Output\Destination::DOWNLOAD);       

    }

    public function productio_order_pdf($project_id = 0, $production_id = 0)
    {
        $data["project_info"] = $this->Projects_model->dev2_getProjectInfoByProjectId($project_id);
        $data["production_bom_header"] = $this->Projects_model->dev2_getProductionOrderHeaderById($production_id);
        $data["production_bom_detail"] = $this->Projects_model->dev2_getProductionOrderDetailByProjectHeaderId(
            $project_id,
            $production_id
        );

        // var_dump(arr($data));
        // exit();

        $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'fontDir' => array_merge($fontDirs, [
                __DIR__ . '/fonts',
            ]),
            'fontdata' => $fontData + [
                'def' => [
                    'R' => 'THSarabun_Bold.ttf'
                ]
            ],
            'default_font' => 'def',
            'tempDir' => '/tmp'
        ]);
        $mpdf->charset_in = 'UTF-8';
        $mpdf->SetTitle('Production Order - ' . $project_id);

        $html = '';

        $html .= '
        <div style="position: relative; width: 100%; height: 100%; border: 1px solid #000;">
            <div style="position: absolute; top: 80px;">
                <table>
                    <thead>
                        <tr>
                            <td style="font-size: 120%;">โครงการ</td>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        ';

        $mpdf->AddPage('P');
        $pagecount = $mpdf->SetSourceFile('pdf_Template/main_template.pdf');
        $tplId = $mpdf->importPage(1);
        $mpdf->useTemplate($tplId);
        $mpdf->WriteHTML($html);
        $mpdf->Output();
    }

    function production_pdf($project_id = 0, $production_id = 0)
    {
        $pdfCtl = &get_instance();
        $view_data['can_read_price'] = true;
        $view_data['label_column'] = "col-md-3";
        $view_data['field_column'] = "col-md-9";
        $view_data["view"] = $pdfCtl->input->post('view');
        $view_data['model_info'] = $pdfCtl->Projects_model->get_one($project_id);
        
        $view_data['items'] = $pdfCtl->Items_model->get_items([])->result();
        foreach ($view_data['items'] as $item) {
            unset($item->files);
            unset($item->description);
        }

        $view_data['item_mixings'] = $pdfCtl->Bom_item_mixing_groups_model->get_detail_items(['for_client_id' => $view_data['model_info']->client_id])->result();
        $view_data['project_items'] = $pdfCtl->Bom_item_mixing_groups_model->get_project_items(['project_id' => $view_data['model_info']->id, 'id' => $production_id])->result();
        $view_data['project_materials'] = $pdfCtl->Bom_item_mixing_groups_model->get_project_materials($view_data['project_items']);
        // var_dump(arr($view_data["project_materials"])); exit();

        $trs_materials = array();
        $table_stock = '';
        $keep = array();
        $rangItem = 10;
        $info = $view_data['model_info'];

        $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        
        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'fontDir' => array_merge($fontDirs, [
                __DIR__ . '/fonts',
            ]),
            'fontdata' => $fontData + [
                'def' => [
                    'R' => 'THSarabun_Bold.ttf'
                ]
            ],
            'default_font' => 'def',
            'tempDir' => '/tmp'
        ]);
        $mpdf->charset_in = 'UTF-8';
        $mpdf->SetTitle('project-' . $project_id);
        $html = '';

        foreach ($view_data['project_materials'] as $parentKey => $child) {
            if (!isset($keep[$child->project_id])) {
                $page = 1;
            }

            $keep[$child->project_id][$page][] = $child;
            if (count($keep[$child->project_id][$page]) == $rangItem) {
                $page += 1;
            }
        }
        // var_dump(arr($keep)); exit();

        if ($keep) {
            foreach ($keep as $kd => $kv) {
                foreach ($kv as $kpage => $vpage) {
                    $trs_materials = array();
                    foreach ($vpage as $vkpage => $vvpage) {
                        $mixing_name = isset($vvpage->mixing_name) ? $vvpage->mixing_name : '-';
                        if (isset($vvpage->result)) {
                            $trs_materials[] = '
                            <tr>
                                <td>' . $vvpage->title . '</td>
                                <td>' . $mixing_name . '</td>
                                <td>' . to_decimal_format2($vvpage->quantity) . ' ' . $vvpage->unit_type . '</td> 
                            </tr>
                            ';

                            $trs_materials[] = '
                            <tr>
                                <td colspan="3">
                                    <div class="toggle-container">
                                        <table class="sub_mat" style="width: 100%">
                                            <tr>
                                                <th class="sub_thStyle" style="text-align: center;">' . lang('stock_material') . '</th>
                                                <th class="sub_thStyle" style="text-align: center;">' . lang('stock_material_name') . '</th>
                                                <th class="sub_thStyle" style="text-align: center;">' . lang('stock_restock_name') . '</th>
                                                <th class="sub_thStyle" style="text-align: center;">' . lang('quantity') . '</th>
                                                <th class="sub_thStyle" style="text-align: center;">' . lang('stock_calculator_value') . '</th>
                                            </tr>   
                            ';

                            $total = 0;
                            foreach ($vvpage->result as $rk) {
                                $stock_name = isset($rk->stock_name) ? $rk->stock_name : '-';
                                $rk->ratio = floatval($rk->ratio);
                                $classer = 'red';
                                if ($rk->ratio > 0) {
                                    $classer = 'green';
                                }

                                if ($vvpage->id == $rk->bpim_Pid) {
                                    $trs_materials[] = '                                    
                                        <tr>                                            
                                            <td>' . $rk->material_name . '</td>
                                            <td>' . $rk->material_desc . '</td>
                                            <td>' . $stock_name . '</td>
                                            <td style="text-align: right; color:' . $classer . '">' . to_decimal_format2($rk->ratio) . ' ' . $rk->material_unit . '</td>
                                                               
                                    ';

                                    if ($rk->value != 0) {
                                        $total += $rk->value;
                                        $trs_materials[] = '
                                            <td style="text-align: right;">' . to_currency($total) . '</td>
                                            </tr> 
                                        ';
                                    } else {
                                        $trs_materials[] = '
                                            <td style="text-align: right;">-</td>
                                            </tr> 
                                        ';
                                    }
                                }
                            }

                            $trs_materials[] = '
                                            <tr>
                                                <th colspan="3"></th>
                                                <th style="text-align: right;">' . lang('total') . '</th>
                                                <td style="text-align: right;">' . to_currency($total) . '</td>
                                            </tr>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                            ';
                        } else {
                            $trs_materials[] = '
                                <tr>
                                    <td >' . $vvpage->title . '</td>
                                    <td >' . $mixing_name . '</td>
                                    <td >' . to_decimal_format2($vvpage->quantity) . ' ' . $vvpage->unit_type . '</td> 
                                </tr>
                            ';
                        }
                    }

                    $titleLength = strlen($info->title);
                    $divTop = 190;
                    $divleft = 27;
                    if ($titleLength > 100) {
                        $divTop += 20;
                    }
                    if ($titleLength < 45) {
                        $divTop -= 20;
                    }
                    $table_stock = '
                    <div style="position:absolute; top:' . $divTop . 'px; left: ' . $divleft . 'px; width: 100%">
                        <table>
                            <tr>
                                <th class="thStyle" style="text-align: center;">' . lang('item') . '</th>
                                <th class="thStyle" style="text-align: center;">' . lang('item_mixing_name') . '</th>
                                <th class="thStyle" style="text-align: center;">' . lang('quantity') . '</th>
                            </tr>
                            ' . implode(" ", $trs_materials) . '
                        </table>
                    </div>
                    ';

                    $divTop = 40;
                    $divleft = 420;
                    $table_info = '
                        <div style="position:absolute; top:' . $divTop . 'px; left: ' . $divleft . 'px; width: 800px">
                            <table style="width: 320px; border: none;">
                                <tr>
                                    <th colspan="2" style="text-align: center; border: none; border-bottom: 1.5px solid #999; font-size: 30px; "><span class="label" style="font-weight:bold;">โครงการ</span></th>
                                </tr>
                                <tr>
                                    <td style="border: none; vertical-align: top; width: 66px;"> <span class="label">เลขที่</span></td>
                                    <td style="border: none;">' . $info->id . ' / ' . $vvpage->id . '</td>
                                </tr>
                                <tr>
                                    <td style="border: none; vertical-align: top;"><span class="label">ชื่อโครงการ</span></td>
                                    <td style="border: none;">' . $info->title . '</td>
                                </tr>
                                <tr>
                                    <td style="border: none; vertical-align: top;"><span class="label">วันที่</span></td>
                                    <td style="border: none;">' . $this->DateConvert($info->created_date) . '</td>
                                </tr>
                            </table>
                        </div>
                    ';

                    $html = '<style>
                    div{
                        font-size: 17px;
                        // border: solid 1px #000;
                        // font-weight:bold;
                        text-align: center;
                    }
                    table {
                        font-size: 18px;
                        border: solid 1px #000;
                        width: 735px; 
                        border-collapse: collapse;
                    }
                    th,td{
                        text-align: left;
                        border: solid 1px #000;
                    }
                    .thStyle{
                        background-color : #ccffcc;
                        border-top: 2px solid #999;
                        border-bottom: 2px solid #999;
                        color: #5900b3;
                    }
                    .sub_thStyle{
                        background-color : #ccffcc;
                        border-top: 2px solid #999;
                        border-bottom: 2px solid #999;
                        color: #5900b3;
                    }
                    .sub_mat{
                        margin: 10px;
                        border: solid 1px #000;
                    }
                    .label{
                        color: #5900b3;
                    }
                    .info_tb{
                        // border: solid 1px #000;
                    }
                    </style>
                    
                    ' . $table_stock . '
                    ' . $table_info . '
                    ';

                    $mpdf->AddPage('P');
                    $pagecount = $mpdf->SetSourceFile('pdf_Template/main_template.pdf');
                    $tplId = $mpdf->importPage(1);
                    $mpdf->useTemplate($tplId);
                    $mpdf->WriteHTML($html);

                }
                $mpdf->Output();
            }
        } else {
            $divTop = 170;
            $divleft = 27;
            $table_stock = '
            <div style="position:absolute; top:' . $divTop . 'px; left: ' . $divleft . 'px; width: 750px">
                <table>
                    <tr>
                        <th class="thStyle" style="text-align: center;">' . lang('item') . '</th>
                        <th class="thStyle" style="text-align: center;">' . lang('item_mixing_name') . '</th>
                        <th class="thStyle" style="text-align: center;">' . lang('quantity') . '</th>
                    </tr>
                    <tr>
                        <td colspan="3" style="text-align: center;">ไม่มีรายการวัตถุดิบ</td>
                    </tr>
                </table>
            </div>
            ';

            $divTop = 40;
            $divleft = 526;
            $table_info = '
                <div style="position:absolute; top:' . $divTop . 'px; left: ' . $divleft . 'px; width: 750px">
                    <table style="width: 230px; border: none;">
                        <tr>
                            <th colspan="2" style="text-align: center; border: none; border-bottom: 1.5px solid #999; font-size: 30px; "><span class="label" style="font-weight:bold;">วัตถุดิบโครงการ</span></th>
                        </tr>
                        <tr>
                            <td style="border: none;"> <span class="label">เลขที่</span></td>
                            <td style="border: none;">' . $info->id . '</td>
                        </tr>
                        <tr>
                            <td style="border: none;"><span class="label">ชื่อโครงการ</span></td>
                            <td style="border: none;">' . $info->title . '</td>
                        </tr>
                        <tr>
                            <td style="border: none;"><span class="label">วันที่</span></td>
                            <td style="border: none;">' . $this->DateConvert($info->created_date) . '</td>
                        </tr>
                    </table>
                </div>
            ';

            $html = '<style>
                    div{
                        font-size: 17px;
                        // border: solid 1px #000;
                        // font-weight:bold;
                        text-align: center;
                    }
                    table {
                        font-size: 18px;
                        // border: solid 1px #000;
                        width: 735px; 
                        border-collapse: collapse;
                    }
                    th,td{
                        text-align: left;
                        border: solid 1px #000;
                    }
                    
                    .thStyle{
                        background-color : #ccffcc;
                        color: #5900b3;
                    }
                    .sub_thStyle{
                        background-color : #ccffcc;
                        color: #5900b3;
                    }
                    .sub_mat{
                        margin: 10px;
                        border: solid 1px #000;
                    }
                    .label{
                        color: #5900b3;
                    }
                    .info_tb{
                        // border: solid 1px #000;
                    }
                    </style>
                    
                    ' . $table_stock . '
                    ' . $table_info . '
                    ';

            $mpdf->AddPage('P');
            $pagecount = $mpdf->SetSourceFile('pdf_Template/main_template.pdf');
            $tplId = $mpdf->importPage(1);
            $mpdf->useTemplate($tplId);
            $mpdf->WriteHTML($html);
            $mpdf->Output();
        }
    }

    public function production_bag_pdf($project_id = 0)
    {
        $data["project_info"] = $this->Projects_model->dev2_getProjectInfoByProjectId($project_id);
        $data["client_info"] = $this->Clients_model->get_one($data["project_info"]["client_id"]);
        $data["user_info"] = $this->Users_model->get_one($data["project_info"]["created_by"]);
        $data["production_items"] = $this->Projects_model->dev2_getMixingCategoryListByProjectId($project_id);
        // var_dump(arr($data)); exit();

        $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        
        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'fontDir' => array_merge($fontDirs, [
                __DIR__ . '/fonts',
            ]),
            'fontdata' => $fontData + [
                'def' => [
                    'R' => 'THSarabun_Bold.ttf'
                ]
            ],
            'default_font' => 'def',
            'tempDir' => '/tmp'
        ]);
        $mpdf->charset_in = 'UTF-8';
        $mpdf->SetTitle('Project-' . $project_id);
        $html = '';

        $project_header = '
        <div style="border: 1px solid rgba(0, 0, 0, 1); margin-bottom: 10px;">
            <table style="width: 100%;" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="width: 45%; text-align: center;">
                        <div style="font-size: 180%;">' . lang("production_order_all_of_material_used") . '</div>
                        <span style="font-size: 130%;">(' . lang("group_category") . ')</span>
                    </td>
                    <td style="width: 55%; border-left: 1px solid rgba(0, 0, 0, 1);">
                        <table style="width: 100%; font-size: 130%;" cellspacing="0" cellpadding="0">
                            <tr>
                                <td style="border-bottom: 1px solid rgba(0, 0, 0, 1); padding: 2px 10px;">' . lang("project_name") . ':</td>
                                <td style="border-bottom: 1px solid rgba(0, 0, 0, 1); padding: 2px 10px;">' . $data["project_info"]["title"] . '</td>
                            </tr>
                            <tr>
                                <td style="border-bottom: 1px solid rgba(0, 0, 0, 1); padding: 2px 10px;">' . lang("start_date") . ':</td>
                                <td style="border-bottom: 1px solid rgba(0, 0, 0, 1); padding: 2px 10px;">' . convertDate($data["project_info"]["start_date"], true) . '</td>
                            </tr>
                            <tr>
                                <td style="border-bottom: 1px solid rgba(0, 0, 0, 1); padding: 2px 10px;">' . lang("deadline") . ':</td>
                                <td style="border-bottom: 1px solid rgba(0, 0, 0, 1); padding: 2px 10px;">' . convertDate($data["project_info"]["deadline"], true) . '</td>
                            </tr>
                            <tr>
                                <td style="padding: 2px 10px;">' . lang("client") . ':</td>
                                <td style="padding: 2px 10px;">' . $data["client_info"]->company_name . '</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        ';
        $html .= $project_header;

        $header = '<div style="width: 100%; border: 1px solid rgba(0, 0, 0, 0); text-align: center; font-size: 175%;">' . lang("production_order_all_of_material_used") . '</div>';
        // $html .= $header;

        $table_open_main = '<table style="width: 100%;" cellpadding="0" cellspacing="0">';
        $html .= $table_open_main;
        
        if (isset($data["production_items"]["rm_cate"]) && !empty($data["production_items"]["rm_cate"])) {
            foreach ($data["production_items"]["rm_cate"] as $category) {
                $total_group = 0.000000;
                $category_line = '<tr class="category-line" style="background-color: rgba(0, 83, 156, .8);">
                    <th width="15%" style="color: #f2f2f2; height: 28px; border: 1px solid rgba(0, 0, 0, 1);">' . lang("category") . '</th>
                    <th style="text-align: left; padding-left: 10px; color: #f2f2f2; border: 1px solid rgba(0, 0, 0, 1);">' . $category["item_type"] . ' : ' . $category["title"] . '</th>
                </tr>';
                $html .= $category_line;

                $material_open_line = '<tr class="material-line"><td colspan="2" style="border: 1px solid rgba(0, 0, 0, 1);"><table style="width: 90%; margin: 5px auto;" cellpadding="0" cellspacing="0">';
                $html .= $material_open_line;

                $thead_material_line = '<thead>
                    <tr>
                        <th style="font-size: 90%; border: 1px solid rgba(0, 0, 0, 1); color: #030303; background-color: rgba(0, 83, 156, .25);">' . lang("stock_material") . '</th>
                        <th style="font-size: 90%; border: 1px solid rgba(0, 0, 0, 1); color: #030303; background-color: rgba(0, 83, 156, .25);">' . lang("quantity") . '</th>
                        <th style="font-size: 90%; border: 1px solid rgba(0, 0, 0, 1); color: #030303; background-color: rgba(0, 83, 156, .25);">' . lang("stock_material_unit") . '</th>
                    </tr>
                </thead>';
                $html .= $thead_material_line;

                $tbody_material_line = '<tbody>';
                foreach ($data["production_items"]["rm_list"] as $rm) {
                    if ($category["id"] == $rm->category_in_bom) {
                        $display_name = '';
                        if (!empty($rm->material_info->production_name)) {
                            $display_name = $rm->material_info->name . ' - ' . mb_strimwidth($rm->material_info->production_name, 0, 50, '...');
                        } else {
                            $display_name = $rm->material_info->name;
                        }

                        $display_description = '';
                        if (!empty($rm->material_info->description)) {
                            $display_description = $rm->material_info->description;
                        }

                        $total_group += $rm->quantity;
                        $tbody_material_line .= '<tr class="material-line-items">
                            <td class="rm-name" style="padding-left: 10px; font-size: 90%; max-width: 320px; white-space: nowrap; text-overflow: ellipsis; overflow: hidden; border: 1px solid rgba(0, 0, 0, 1);">
                                <span class="font-bold">' . $display_name . '</span><br>
                                <span class="rm-description">' . mb_strimwidth($display_description, 0, 50, '...') . '</span>
                            </td>
                            <td class="text-right rm-quantity" style="padding-right: 10px; font-size: 110%; width: 200px; text-align: right; border: 1px solid rgba(0, 0, 0, 1);">' . $rm->quantity . '</td>
                            <td class="text-center rm-unit" style="padding-left: 10px; font-size: 110%; width: 90px; border: 1px solid rgba(0, 0, 0, 1);">' . $rm->material_info->unit . '</td>
                        </tr>';
                    }
                }
                $tbody_material_line .= '</tbody>';
                $html .= $tbody_material_line;

                $tfoot_material_line = '<tfoot>
                    <tr>
                        <th class="text-center" style="font-size: 90%; border: 1px solid rgba(0, 0, 0, 1); color: #030303; background-color: rgba(0, 83, 156, .25);">' . lang("gr_total_quantity") . '</th>
                        <th colspan="2" class="text-center" style="font-size: 110%; border: 1px solid rgba(0, 0, 0, 1); color: #030303; background-color: rgba(0, 83, 156, .25);">' . number_format($total_group, 6) . '</th>
                    </tr>
                </tfoot>';
                $html .= $tfoot_material_line;

                $material_close_line = '</table></td></tr>';
                $html .= $material_close_line;
            }
        }

        $table_close_main = '</table>';
        $html .= $table_close_main;

        $mpdf->AddPage('P');
        $mpdf->WriteHTML($html);
        $html = '';

        $project_header = '
        <div style="border: 1px solid rgba(0, 0, 0, 1); margin-bottom: 10px;">
            <table style="width: 100%;" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="width: 45%; text-align: center;">
                        <div style="font-size: 180%;">' . lang("production_order_all_of_semi_used") . '</div>
                        <span style="font-size: 130%;">(' . lang("group_category") . ')</span>
                    </td>
                    <td style="width: 55%; border-left: 1px solid rgba(0, 0, 0, 1);">
                        <table style="width: 100%; font-size: 130%;" cellspacing="0" cellpadding="0">
                            <tr>
                                <td style="border-bottom: 1px solid rgba(0, 0, 0, 1); padding: 2px 10px;">' . lang("project_name") . ':</td>
                                <td style="border-bottom: 1px solid rgba(0, 0, 0, 1); padding: 2px 10px;">' . $data["project_info"]["title"] . '</td>
                            </tr>
                            <tr>
                                <td style="border-bottom: 1px solid rgba(0, 0, 0, 1); padding: 2px 10px;">' . lang("start_date") . ':</td>
                                <td style="border-bottom: 1px solid rgba(0, 0, 0, 1); padding: 2px 10px;">' . convertDate($data["project_info"]["start_date"], true) . '</td>
                            </tr>
                            <tr>
                                <td style="border-bottom: 1px solid rgba(0, 0, 0, 1); padding: 2px 10px;">' . lang("deadline") . ':</td>
                                <td style="border-bottom: 1px solid rgba(0, 0, 0, 1); padding: 2px 10px;">' . convertDate($data["project_info"]["deadline"], true) . '</td>
                            </tr>
                            <tr>
                                <td style="padding: 2px 10px;">' . lang("client") . ':</td>
                                <td style="padding: 2px 10px;">' . $data["client_info"]->company_name . '</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        ';
        $html .= $project_header;

        $header = '<div style="width: 100%; border: 1px solid rgba(0, 0, 0, 0); text-align: center; font-size: 175%;">' . lang("production_order_all_of_semi_used") . '</div>';
        // $html .= $header;

        $table_open_main = '<table style="width: 100%;" cellpadding="0" cellspacing="0">';
        $html .= $table_open_main;
        
        if (isset($data["production_items"]["sfg_cate"]) && !empty($data["production_items"]["sfg_cate"])) {
            foreach ($data["production_items"]["sfg_cate"] as $category) {
                $total_group = 0.000000;
                $category_line = '<tr class="category-line" style="background-color: rgba(255, 165, 0, .8);">
                    <th width="15%" style="color: #f2f2f2; height: 28px; border: 1px solid rgba(0, 0, 0, 1);">' . lang("category") . '</th>
                    <th style="text-align: left; padding-left: 10px; color: #f2f2f2; border: 1px solid rgba(0, 0, 0, 1);">' . $category["item_type"] . ' : ' . $category["title"] . '</th>
                </tr>';
                $html .= $category_line;

                $material_open_line = '<tr class="material-line"><td colspan="2" style="border: 1px solid rgba(0, 0, 0, 1);"><table style="width: 90%; margin: 5px auto;" cellpadding="0" cellspacing="0">';
                $html .= $material_open_line;

                $thead_material_line = '<thead>
                    <tr>
                        <th style="font-size: 90%; border: 1px solid rgba(0, 0, 0, 1); color: #030303; background-color: rgba(255, 165, 0, .25);">' . lang("sfg") . '</th>
                        <th style="font-size: 90%; border: 1px solid rgba(0, 0, 0, 1); color: #030303; background-color: rgba(255, 165, 0, .25);">' . lang("quantity") . '</th>
                        <th style="font-size: 90%; border: 1px solid rgba(0, 0, 0, 1); color: #030303; background-color: rgba(255, 165, 0, .25);">' . lang("stock_material_unit") . '</th>
                    </tr>
                </thead>';
                $html .= $thead_material_line;

                $tbody_material_line = '<tbody>';
                foreach ($data["production_items"]["sfg_list"] as $sfg) {
                    if ($category["id"] == $sfg->category_in_bom) {
                        $display_name = '';
                        if (!empty($sfg->item_info->item_code)) {
                            $display_name = $sfg->item_info->item_code . ' - ' . mb_strimwidth($sfg->item_info->title, 0, 50, '...');
                        } else {
                            $display_name = $sfg->item_info->title;
                        }

                        $display_description = '';
                        if (!empty($sfg->item_info->description)) {
                            $display_description = $sfg->item_info->description;
                        }

                        $total_group += $sfg->quantity;
                        $tbody_material_line .= '<tr class="material-line-items">
                            <td class="rm-name" style="padding-left: 10px; font-size: 90%; max-width: 320px; white-space: nowrap; text-overflow: ellipsis; overflow: hidden; border: 1px solid rgba(0, 0, 0, 1);">
                                <span class="font-bold">' . $display_name . '</span><br>
                                <span class="rm-description">' . mb_strimwidth($display_description, 0, 50, '...') . '</span>
                            </td>
                            <td class="text-right rm-quantity" style="padding-right: 10px; font-size: 110%; width: 200px; text-align: right; border: 1px solid rgba(0, 0, 0, 1);">' . $sfg->quantity . '</td>
                            <td class="text-center rm-unit" style="padding-left: 10px; font-size: 110%; width: 90px; border: 1px solid rgba(0, 0, 0, 1);">' . $sfg->item_info->unit_type . '</td>
                        </tr>';
                    }
                }
                $tbody_material_line .= '</tbody>';
                $html .= $tbody_material_line;

                $tfoot_material_line = '<tfoot>
                    <tr>
                        <th class="text-center" style="font-size: 90%; border: 1px solid rgba(0, 0, 0, 1); color: #030303; background-color: rgba(255, 165, 0, .25);">' . lang("gr_total_quantity") . '</th>
                        <th colspan="2" class="text-center" style="font-size: 110%; border: 1px solid rgba(0, 0, 0, 1); color: #030303; background-color: rgba(255, 165, 0, .25);">' . number_format($total_group, 6) . '</th>
                    </tr>
                </tfoot>';
                $html .= $tfoot_material_line;

                $material_close_line = '</table></td></tr>';
                $html .= $material_close_line;
            }
        }

        $table_close_main = '</table>';
        $html .= $table_close_main;

        $mpdf->AddPage('P');
        $mpdf->WriteHTML($html);
        
        $mpdf->Output();
    }

    public function wht_document($id = 0)
    {
        // Get company info
        $company_vat_number = get_setting("company_vat_number");
        $company_name = get_setting("company_name");
        $company_address = nl2br(get_setting("company_address"));
        
        // Load mpdf default config
        $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig["fontDir"];
        
        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig["fontdata"];

        $mpdf = new \Mpdf\Mpdf([
            'fontDir' => array_merge($fontDirs, [
                __DIR__ . '/fonts',
            ]),
            'fontdata' => $fontData + [
                'def' => [
                    'R' => 'THSarabun_Bold.ttf'
                ]
            ],
            'default_font' => 'def',
            'tempDir' => '/tmp'
        ]);
        $mpdf->charset_in = 'UTF-8';
        $mpdf->SetTitle('WHT');
        $mpdf->SetSourceFile('pdf_Template/wht.pdf');

        // $html = 'DEVELOPMENT_ENV_WHT_BY_DEV2';
        $html = '';
        $html .= '<br>';

        // Ref - 1
        $ref_1 = "REF#1";
        $div_ref_1 = '<div style="position: absolute; width: 150px; font-size: 80%; text-align: right; top: 33; right: 38; border: 1px solid rgba(0, 0, 0, 0);"><i>' . $ref_1 . '</i></div>';
        if (isset($ref_1) && !empty($ref_1)) {
            $html .= $div_ref_1;
        }
        
        // Ref - 2
        $ref_2 = "REF#2";
        $div_ref_2 = '<div style="position: absolute; width: 150px; font-size: 80%; text-align: right; top: 43; right: 38; border: 1px solid rgba(0, 0, 0, 0);"><i>' . $ref_2 . '</i></div>';
        if (isset($ref_2) && !empty($ref_2)) {
            $html .= $div_ref_2;
        }

        // WHT Number
        $wht_doc = "WHT2023120001";
        $div_wht_doc = '<div style="position: absolute; width: 150px; font-size: 76%; text-align: right; top: 87; right: 40; border: 1px solid rgba(0, 0, 0, 0);">' . $wht_doc . '</div>';
        if (isset($wht_doc) && !empty($wht_doc)) {
            $html .= $div_wht_doc;
        }

        // Start - Payer
        $div_payer_name = '<div style="position: absolute; top: 132; left: 90;">' . $company_name . '</div>';
        if (isset($company_name) && !empty($company_name)) {
            $html .= $div_payer_name;
        }

        $div_payer_address = '<div style="position: absolute; top: 164; left: 90;">' . $company_address . '</div>';
        if (isset($company_address) && !empty($company_address)) {
            $html .= $div_payer_address;
        }

        $payer_taxno = trim($company_vat_number);
        $border_check = "0";
        $border_red = "0";
        $payer_taxno_digit = 0;
        $payer_taxno_top = "110";
        
        // 1
        $div_payer_taxno[$payer_taxno_digit] = '<div style="position: absolute; top: ';
        ($payer_taxno[$payer_taxno_digit] == 0 || $payer_taxno[$payer_taxno_digit] == "0") ? $div_payer_taxno[$payer_taxno_digit] .= $payer_taxno_top - 2 : $div_payer_taxno[$payer_taxno_digit] .= $payer_taxno_top;
        $div_payer_taxno[$payer_taxno_digit] .= '; right: 278; width: 14.5px; text-align: center; border: 1px solid rgba(' . $border_red . ', 0, 0, ' . $border_check . ');">' . $payer_taxno[$payer_taxno_digit] . '</div>';
        if (isset($div_payer_taxno[$payer_taxno_digit]) && !empty($div_payer_taxno[$payer_taxno_digit])) {
            $html .= $div_payer_taxno[$payer_taxno_digit];
        }
        $payer_taxno_digit++;

        // 2
        $div_payer_taxno[$payer_taxno_digit] = '<div style="position: absolute; top: ';
        ($payer_taxno[$payer_taxno_digit] == 0 || $payer_taxno[$payer_taxno_digit] == "0") ? $div_payer_taxno[$payer_taxno_digit] .= $payer_taxno_top - 2 : $div_payer_taxno[$payer_taxno_digit] .= $payer_taxno_top;
        $div_payer_taxno[$payer_taxno_digit] .= '; right: 254; width: 14.5px; text-align: center; border: 1px solid rgba(' . $border_red . ', 0, 0, ' . $border_check . ');">' . $payer_taxno[$payer_taxno_digit] . '</div>';
        if (isset($div_payer_taxno[$payer_taxno_digit]) && !empty($div_payer_taxno[$payer_taxno_digit])) {
            $html .= $div_payer_taxno[$payer_taxno_digit];
        }
        $payer_taxno_digit++;

        // 3
        $div_payer_taxno[$payer_taxno_digit] = '<div style="position: absolute; top: ';
        ($payer_taxno[$payer_taxno_digit] == 0 || $payer_taxno[$payer_taxno_digit] == "0") ? $div_payer_taxno[$payer_taxno_digit] .= $payer_taxno_top - 2 : $div_payer_taxno[$payer_taxno_digit] .= $payer_taxno_top;
        $div_payer_taxno[$payer_taxno_digit] .= '; right: 238; width: 14.5px; text-align: center; border: 1px solid rgba(' . $border_red . ', 0, 0, ' . $border_check . ');">' . $payer_taxno[$payer_taxno_digit] . '</div>';
        if (isset($div_payer_taxno[$payer_taxno_digit]) && !empty($div_payer_taxno[$payer_taxno_digit])) {
            $html .= $div_payer_taxno[$payer_taxno_digit];
        }
        $payer_taxno_digit++;

        // 4
        $div_payer_taxno[$payer_taxno_digit] = '<div style="position: absolute; top: ';
        ($payer_taxno[$payer_taxno_digit] == 0 || $payer_taxno[$payer_taxno_digit] == "0") ? $div_payer_taxno[$payer_taxno_digit] .= $payer_taxno_top - 2 : $div_payer_taxno[$payer_taxno_digit] .= $payer_taxno_top;
        $div_payer_taxno[$payer_taxno_digit] .= '; right: 222; width: 14.5px; text-align: center; border: 1px solid rgba(' . $border_red . ', 0, 0, ' . $border_check . ');">' . $payer_taxno[$payer_taxno_digit] . '</div>';
        if (isset($div_payer_taxno[$payer_taxno_digit]) && !empty($div_payer_taxno[$payer_taxno_digit])) {
            $html .= $div_payer_taxno[$payer_taxno_digit];
        }
        $payer_taxno_digit++;

        // 5
        $div_payer_taxno[$payer_taxno_digit] = '<div style="position: absolute; top: ';
        ($payer_taxno[$payer_taxno_digit] == 0 || $payer_taxno[$payer_taxno_digit] == "0") ? $div_payer_taxno[$payer_taxno_digit] .= $payer_taxno_top - 2 : $div_payer_taxno[$payer_taxno_digit] .= $payer_taxno_top;
        $div_payer_taxno[$payer_taxno_digit] .= '; right: 206; width: 14.5px; text-align: center; border: 1px solid rgba(' . $border_red . ', 0, 0, ' . $border_check . ');">' . $payer_taxno[$payer_taxno_digit] . '</div>';
        if (isset($div_payer_taxno[$payer_taxno_digit]) && !empty($div_payer_taxno[$payer_taxno_digit])) {
            $html .= $div_payer_taxno[$payer_taxno_digit];
        }
        $payer_taxno_digit++;

        // 6
        $div_payer_taxno[$payer_taxno_digit] = '<div style="position: absolute; top: ';
        ($payer_taxno[$payer_taxno_digit] == 0 || $payer_taxno[$payer_taxno_digit] == "0") ? $div_payer_taxno[$payer_taxno_digit] .= $payer_taxno_top - 2 : $div_payer_taxno[$payer_taxno_digit] .= $payer_taxno_top;
        $div_payer_taxno[$payer_taxno_digit] .= '; right: 181; width: 14.5px; text-align: center; border: 1px solid rgba(' . $border_red . ', 0, 0, ' . $border_check . ');">' . $payer_taxno[$payer_taxno_digit] . '</div>';
        if (isset($div_payer_taxno[$payer_taxno_digit]) && !empty($div_payer_taxno[$payer_taxno_digit])) {
            $html .= $div_payer_taxno[$payer_taxno_digit];
        }
        $payer_taxno_digit++;

        // 7
        $div_payer_taxno[$payer_taxno_digit] = '<div style="position: absolute; top: ';
        ($payer_taxno[$payer_taxno_digit] == 0 || $payer_taxno[$payer_taxno_digit] == "0") ? $div_payer_taxno[$payer_taxno_digit] .= $payer_taxno_top - 2 : $div_payer_taxno[$payer_taxno_digit] .= $payer_taxno_top;
        $div_payer_taxno[$payer_taxno_digit] .= '; right: 165; width: 14.5px; text-align: center; border: 1px solid rgba(' . $border_red . ', 0, 0, ' . $border_check . ');">' . $payer_taxno[$payer_taxno_digit] . '</div>';
        if (isset($div_payer_taxno[$payer_taxno_digit]) && !empty($div_payer_taxno[$payer_taxno_digit])) {
            $html .= $div_payer_taxno[$payer_taxno_digit];
        }
        $payer_taxno_digit++;

        // 8
        $div_payer_taxno[$payer_taxno_digit] = '<div style="position: absolute; top: ';
        ($payer_taxno[$payer_taxno_digit] == 0 || $payer_taxno[$payer_taxno_digit] == "0") ? $div_payer_taxno[$payer_taxno_digit] .= $payer_taxno_top - 2 : $div_payer_taxno[$payer_taxno_digit] .= $payer_taxno_top;
        $div_payer_taxno[$payer_taxno_digit] .= '; right: 149; width: 14.5px; text-align: center; border: 1px solid rgba(' . $border_red . ', 0, 0, ' . $border_check . ');">' . $payer_taxno[$payer_taxno_digit] . '</div>';
        if (isset($div_payer_taxno[$payer_taxno_digit]) && !empty($div_payer_taxno[$payer_taxno_digit])) {
            $html .= $div_payer_taxno[$payer_taxno_digit];
        }
        $payer_taxno_digit++;

        // 9
        $div_payer_taxno[$payer_taxno_digit] = '<div style="position: absolute; top: ';
        ($payer_taxno[$payer_taxno_digit] == 0 || $payer_taxno[$payer_taxno_digit] == "0") ? $div_payer_taxno[$payer_taxno_digit] .= $payer_taxno_top - 2 : $div_payer_taxno[$payer_taxno_digit] .= $payer_taxno_top;
        $div_payer_taxno[$payer_taxno_digit] .= '; right: 133; width: 14.5px; text-align: center; border: 1px solid rgba(' . $border_red . ', 0, 0, ' . $border_check . ');">' . $payer_taxno[$payer_taxno_digit] . '</div>';
        if (isset($div_payer_taxno[$payer_taxno_digit]) && !empty($div_payer_taxno[$payer_taxno_digit])) {
            $html .= $div_payer_taxno[$payer_taxno_digit];
        }
        $payer_taxno_digit++;

        // 10
        $div_payer_taxno[$payer_taxno_digit] = '<div style="position: absolute; top: ';
        ($payer_taxno[$payer_taxno_digit] == 0 || $payer_taxno[$payer_taxno_digit] == "0") ? $div_payer_taxno[$payer_taxno_digit] .= $payer_taxno_top - 2 : $div_payer_taxno[$payer_taxno_digit] .= $payer_taxno_top;
        $div_payer_taxno[$payer_taxno_digit] .= '; right: 117; width: 14.5px; text-align: center; border: 1px solid rgba(' . $border_red . ', 0, 0, ' . $border_check . ');">' . $payer_taxno[$payer_taxno_digit] . '</div>';
        if (isset($div_payer_taxno[$payer_taxno_digit]) && !empty($div_payer_taxno[$payer_taxno_digit])) {
            $html .= $div_payer_taxno[$payer_taxno_digit];
        }
        $payer_taxno_digit++;

        // 11
        $div_payer_taxno[$payer_taxno_digit] = '<div style="position: absolute; top: ';
        ($payer_taxno[$payer_taxno_digit] == 0 || $payer_taxno[$payer_taxno_digit] == "0") ? $div_payer_taxno[$payer_taxno_digit] .= $payer_taxno_top - 2 : $div_payer_taxno[$payer_taxno_digit] .= $payer_taxno_top;
        $div_payer_taxno[$payer_taxno_digit] .= '; right: 92.5; width: 14.5px; text-align: center; border: 1px solid rgba(' . $border_red . ', 0, 0, ' . $border_check . ');">' . $payer_taxno[$payer_taxno_digit] . '</div>';
        if (isset($div_payer_taxno[$payer_taxno_digit]) && !empty($div_payer_taxno[$payer_taxno_digit])) {
            $html .= $div_payer_taxno[$payer_taxno_digit];
        }
        $payer_taxno_digit++;

        // 12
        $div_payer_taxno[$payer_taxno_digit] = '<div style="position: absolute; top: ';
        ($payer_taxno[$payer_taxno_digit] == 0 || $payer_taxno[$payer_taxno_digit] == "0") ? $div_payer_taxno[$payer_taxno_digit] .= $payer_taxno_top - 2 : $div_payer_taxno[$payer_taxno_digit] .= $payer_taxno_top;
        $div_payer_taxno[$payer_taxno_digit] .= '; right: 77; width: 14.5px; text-align: center; border: 1px solid rgba(' . $border_red . ', 0, 0, ' . $border_check . ');">' . $payer_taxno[$payer_taxno_digit] . '</div>';
        if (isset($div_payer_taxno[$payer_taxno_digit]) && !empty($div_payer_taxno[$payer_taxno_digit])) {
            $html .= $div_payer_taxno[$payer_taxno_digit];
        }
        $payer_taxno_digit++;

        // 13
        $div_payer_taxno[$payer_taxno_digit] = '<div style="position: absolute; top: ';
        ($payer_taxno[$payer_taxno_digit] == 0 || $payer_taxno[$payer_taxno_digit] == "0") ? $div_payer_taxno[$payer_taxno_digit] .= $payer_taxno_top - 2 : $div_payer_taxno[$payer_taxno_digit] .= $payer_taxno_top;
        $div_payer_taxno[$payer_taxno_digit] .= '; right: 52; width: 14.5px; text-align: center; border: 1px solid rgba(' . $border_red . ', 0, 0, ' . $border_check . ');">' . $payer_taxno[$payer_taxno_digit] . '</div>';
        if (isset($div_payer_taxno[$payer_taxno_digit]) && !empty($div_payer_taxno[$payer_taxno_digit])) {
            $html .= $div_payer_taxno[$payer_taxno_digit];
        }
        // End - Payer

        // Start - Payee
        $div_payee_name = '<div style="position: absolute; top: 230; left: 90;">' . $company_name . '</div>';
        if (isset($company_name) && !empty($company_name)) {
            $html .= $div_payee_name;
        }

        $div_payee_address = '<div style="position: absolute; top: 264; left: 90;">' . $company_address . '</div>';
        if (isset($company_address) && !empty($company_address)) {
            $html .= $div_payee_address;
        }

        $payee_taxno = trim($company_vat_number);
        $payee_taxno_digit = 0;
        $payee_taxno_top = "202";

        // 1
        $div_payee_taxno[$payee_taxno_digit] = '<div style="position: absolute; top: ';
        ($payee_taxno[$payer_taxno_digit] == 0 || $payee_taxno[$payee_taxno_digit] == "0") ? $div_payee_taxno[$payee_taxno_digit] .= $payee_taxno_top - 2 : $div_payee_taxno[$payee_taxno_digit] .= $payee_taxno_top;
        $div_payee_taxno[$payee_taxno_digit] .= '; right: 278; width: 14.5px; text-align: center; border: 1px solid rgba(' . $border_red . ', 0, 0, ' . $border_check . ');">' . $payee_taxno[$payee_taxno_digit] . '</div>';
        if (isset($div_payee_taxno[$payee_taxno_digit]) && !empty($div_payee_taxno[$payee_taxno_digit])) {
            $html .= $div_payee_taxno[$payee_taxno_digit];
        }
        $payee_taxno_digit++;

        // 2
        $div_payee_taxno[$payee_taxno_digit] = '<div style="position: absolute; top: ';
        ($payee_taxno[$payer_taxno_digit] == 0 || $payee_taxno[$payee_taxno_digit] == "0") ? $div_payee_taxno[$payee_taxno_digit] .= $payee_taxno_top - 2 : $div_payee_taxno[$payee_taxno_digit] .= $payee_taxno_top;
        $div_payee_taxno[$payee_taxno_digit] .= '; right: 254; width: 14.5px; text-align: center; border: 1px solid rgba(' . $border_red . ', 0, 0, ' . $border_check . ');">' . $payee_taxno[$payee_taxno_digit] . '</div>';
        if (isset($div_payee_taxno[$payee_taxno_digit]) && !empty($div_payee_taxno[$payee_taxno_digit])) {
            $html .= $div_payee_taxno[$payee_taxno_digit];
        }
        $payee_taxno_digit++;

        // 3
        $div_payee_taxno[$payee_taxno_digit] = '<div style="position: absolute; top: ';
        ($payee_taxno[$payer_taxno_digit] == 0 || $payee_taxno[$payee_taxno_digit] == "0") ? $div_payee_taxno[$payee_taxno_digit] .= $payee_taxno_top - 2 : $div_payee_taxno[$payee_taxno_digit] .= $payee_taxno_top;
        $div_payee_taxno[$payee_taxno_digit] .= '; right: 238; width: 14.5px; text-align: center; border: 1px solid rgba(' . $border_red . ', 0, 0, ' . $border_check . ');">' . $payee_taxno[$payee_taxno_digit] . '</div>';
        if (isset($div_payee_taxno[$payee_taxno_digit]) && !empty($div_payee_taxno[$payee_taxno_digit])) {
            $html .= $div_payee_taxno[$payee_taxno_digit];
        }
        $payee_taxno_digit++;

        // 4
        $div_payee_taxno[$payee_taxno_digit] = '<div style="position: absolute; top: ';
        ($payee_taxno[$payer_taxno_digit] == 0 || $payee_taxno[$payee_taxno_digit] == "0") ? $div_payee_taxno[$payee_taxno_digit] .= $payee_taxno_top - 2 : $div_payee_taxno[$payee_taxno_digit] .= $payee_taxno_top;
        $div_payee_taxno[$payee_taxno_digit] .= '; right: 222; width: 14.5px; text-align: center; border: 1px solid rgba(' . $border_red . ', 0, 0, ' . $border_check . ');">' . $payee_taxno[$payee_taxno_digit] . '</div>';
        if (isset($div_payee_taxno[$payee_taxno_digit]) && !empty($div_payee_taxno[$payee_taxno_digit])) {
            $html .= $div_payee_taxno[$payee_taxno_digit];
        }
        $payee_taxno_digit++;

        // 5
        $div_payee_taxno[$payee_taxno_digit] = '<div style="position: absolute; top: ';
        ($payee_taxno[$payer_taxno_digit] == 0 || $payee_taxno[$payee_taxno_digit] == "0") ? $div_payee_taxno[$payee_taxno_digit] .= $payee_taxno_top - 2 : $div_payee_taxno[$payee_taxno_digit] .= $payee_taxno_top;
        $div_payee_taxno[$payee_taxno_digit] .= '; right: 206; width: 14.5px; text-align: center; border: 1px solid rgba(' . $border_red . ', 0, 0, ' . $border_check . ');">' . $payee_taxno[$payee_taxno_digit] . '</div>';
        if (isset($div_payee_taxno[$payee_taxno_digit]) && !empty($div_payee_taxno[$payee_taxno_digit])) {
            $html .= $div_payee_taxno[$payee_taxno_digit];
        }
        $payee_taxno_digit++;

        // 6
        $div_payee_taxno[$payee_taxno_digit] = '<div style="position: absolute; top: ';
        ($payee_taxno[$payer_taxno_digit] == 0 || $payee_taxno[$payee_taxno_digit] == "0") ? $div_payee_taxno[$payee_taxno_digit] .= $payee_taxno_top - 2 : $div_payee_taxno[$payee_taxno_digit] .= $payee_taxno_top;
        $div_payee_taxno[$payee_taxno_digit] .= '; right: 181; width: 14.5px; text-align: center; border: 1px solid rgba(' . $border_red . ', 0, 0, ' . $border_check . ');">' . $payee_taxno[$payee_taxno_digit] . '</div>';
        if (isset($div_payee_taxno[$payee_taxno_digit]) && !empty($div_payee_taxno[$payee_taxno_digit])) {
            $html .= $div_payee_taxno[$payee_taxno_digit];
        }
        $payee_taxno_digit++;

        // 7
        $div_payee_taxno[$payee_taxno_digit] = '<div style="position: absolute; top: ';
        ($payee_taxno[$payer_taxno_digit] == 0 || $payee_taxno[$payee_taxno_digit] == "0") ? $div_payee_taxno[$payee_taxno_digit] .= $payee_taxno_top - 2 : $div_payee_taxno[$payee_taxno_digit] .= $payee_taxno_top;
        $div_payee_taxno[$payee_taxno_digit] .= '; right: 165; width: 14.5px; text-align: center; border: 1px solid rgba(' . $border_red . ', 0, 0, ' . $border_check . ');">' . $payee_taxno[$payee_taxno_digit] . '</div>';
        if (isset($div_payee_taxno[$payee_taxno_digit]) && !empty($div_payee_taxno[$payee_taxno_digit])) {
            $html .= $div_payee_taxno[$payee_taxno_digit];
        }
        $payee_taxno_digit++;

        // 8
        $div_payee_taxno[$payee_taxno_digit] = '<div style="position: absolute; top: ';
        ($payee_taxno[$payer_taxno_digit] == 0 || $payee_taxno[$payee_taxno_digit] == "0") ? $div_payee_taxno[$payee_taxno_digit] .= $payee_taxno_top - 2 : $div_payee_taxno[$payee_taxno_digit] .= $payee_taxno_top;
        $div_payee_taxno[$payee_taxno_digit] .= '; right: 149; width: 14.5px; text-align: center; border: 1px solid rgba(' . $border_red . ', 0, 0, ' . $border_check . ');">' . $payee_taxno[$payee_taxno_digit] . '</div>';
        if (isset($div_payee_taxno[$payee_taxno_digit]) && !empty($div_payee_taxno[$payee_taxno_digit])) {
            $html .= $div_payee_taxno[$payee_taxno_digit];
        }
        $payee_taxno_digit++;

        // 9
        $div_payee_taxno[$payee_taxno_digit] = '<div style="position: absolute; top: ';
        ($payee_taxno[$payer_taxno_digit] == 0 || $payee_taxno[$payee_taxno_digit] == "0") ? $div_payee_taxno[$payee_taxno_digit] .= $payee_taxno_top - 2 : $div_payee_taxno[$payee_taxno_digit] .= $payee_taxno_top;
        $div_payee_taxno[$payee_taxno_digit] .= '; right: 133; width: 14.5px; text-align: center; border: 1px solid rgba(' . $border_red . ', 0, 0, ' . $border_check . ');">' . $payee_taxno[$payee_taxno_digit] . '</div>';
        if (isset($div_payee_taxno[$payee_taxno_digit]) && !empty($div_payee_taxno[$payee_taxno_digit])) {
            $html .= $div_payee_taxno[$payee_taxno_digit];
        }
        $payee_taxno_digit++;

        // 10
        $div_payee_taxno[$payee_taxno_digit] = '<div style="position: absolute; top: ';
        ($payee_taxno[$payer_taxno_digit] == 0 || $payee_taxno[$payee_taxno_digit] == "0") ? $div_payee_taxno[$payee_taxno_digit] .= $payee_taxno_top - 2 : $div_payee_taxno[$payee_taxno_digit] .= $payee_taxno_top;
        $div_payee_taxno[$payee_taxno_digit] .= '; right: 117; width: 14.5px; text-align: center; border: 1px solid rgba(' . $border_red . ', 0, 0, ' . $border_check . ');">' . $payee_taxno[$payee_taxno_digit] . '</div>';
        if (isset($div_payee_taxno[$payee_taxno_digit]) && !empty($div_payee_taxno[$payee_taxno_digit])) {
            $html .= $div_payee_taxno[$payee_taxno_digit];
        }
        $payee_taxno_digit++;

        // 11
        $div_payee_taxno[$payee_taxno_digit] = '<div style="position: absolute; top: ';
        ($payee_taxno[$payer_taxno_digit] == 0 || $payee_taxno[$payee_taxno_digit] == "0") ? $div_payee_taxno[$payee_taxno_digit] .= $payee_taxno_top - 2 : $div_payee_taxno[$payee_taxno_digit] .= $payee_taxno_top;
        $div_payee_taxno[$payee_taxno_digit] .= '; right: 92.5; width: 14.5px; text-align: center; border: 1px solid rgba(' . $border_red . ', 0, 0, ' . $border_check . ');">' . $payee_taxno[$payee_taxno_digit] . '</div>';
        if (isset($div_payee_taxno[$payee_taxno_digit]) && !empty($div_payee_taxno[$payee_taxno_digit])) {
            $html .= $div_payee_taxno[$payee_taxno_digit];
        }
        $payee_taxno_digit++;

        // 12
        $div_payee_taxno[$payee_taxno_digit] = '<div style="position: absolute; top: ';
        ($payee_taxno[$payer_taxno_digit] == 0 || $payee_taxno[$payee_taxno_digit] == "0") ? $div_payee_taxno[$payee_taxno_digit] .= $payee_taxno_top - 2 : $div_payee_taxno[$payee_taxno_digit] .= $payee_taxno_top;
        $div_payee_taxno[$payee_taxno_digit] .= '; right: 76.5; width: 14.5px; text-align: center; border: 1px solid rgba(' . $border_red . ', 0, 0, ' . $border_check . ');">' . $payee_taxno[$payee_taxno_digit] . '</div>';
        if (isset($div_payee_taxno[$payee_taxno_digit]) && !empty($div_payee_taxno[$payee_taxno_digit])) {
            $html .= $div_payee_taxno[$payee_taxno_digit];
        }
        $payee_taxno_digit++;

        // 13
        $div_payee_taxno[$payee_taxno_digit] = '<div style="position: absolute; top: ';
        ($payee_taxno[$payer_taxno_digit] == 0 || $payee_taxno[$payee_taxno_digit] == "0") ? $div_payee_taxno[$payee_taxno_digit] .= $payee_taxno_top - 2 : $div_payee_taxno[$payee_taxno_digit] .= $payee_taxno_top;
        $div_payee_taxno[$payee_taxno_digit] .= '; right: 51; width: 14.5px; text-align: center; border: 1px solid rgba(' . $border_red . ', 0, 0, ' . $border_check . ');">' . $payee_taxno[$payee_taxno_digit] . '</div>';
        if (isset($div_payee_taxno[$payee_taxno_digit]) && !empty($div_payee_taxno[$payee_taxno_digit])) {
            $html .= $div_payee_taxno[$payee_taxno_digit];
        }
        // End - Payee

        // Start - Income detail
        $income_40_1_period = '';
        $div_income_40_1_period = '<div style="position: absolute; width: 98px; top: 394; left: 438; text-align: center; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_1_period . '</div>';
        if (isset($income_40_1_period) && !empty($income_40_1_period)) {
            $html .= $div_income_40_1_period;
        }

        $income_40_1_value = 0;
        $income_40_1_value_number = '0';
        $income_40_1_value_decimal = '00';
        if (isset($income_40_1_value) && !empty($income_40_1_value)) {
            if ($income_40_1_value > 0) {
                list($income_40_1_value_number, $income_40_1_value_decimal) = explode('.', $income_40_1_value);
            }
        }

        $div_income_40_1_value_number = '<div style="position: absolute; width: 88px; top: 394; left: 542; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_1_value_number, 0) . '</div>';
        $div_income_40_1_value_decimal = '<div style="position: absolute; width: 20px; top: 394; left: 629; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_1_value_decimal . '</div>';
        if (isset($income_40_1_value) && !empty($income_40_1_value) && $income_40_1_value > 0) {
            $html .= $div_income_40_1_value_number;
            $html .= $div_income_40_1_value_decimal;
        }

        $income_40_1_tax = 0;
        $income_40_1_tax_number = '0';
        $income_40_1_tax_decimal = '00';
        if (isset($income_40_1_tax) && !empty($income_40_1_tax)) {
            if ($income_40_1_tax > 0) {
                list($income_40_1_tax_number, $income_40_1_tax_decimal) = explode('.', $income_40_1_tax);
            }
        }
        
        $div_income_40_1_tax_number = '<div style="position: absolute; width: 70px; top: 394; left: 655; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_1_tax_number, 0) . '</div>';
        $div_income_40_1_tax_decimal = '<div style="position: absolute; width: 20px; top: 394; left: 723; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_1_tax_decimal . '</div>';
        if (isset($income_40_1_tax) && !empty($income_40_1_tax) && $income_40_1_tax > 0) {
            $html .= $div_income_40_1_tax_number;
            $html .= $div_income_40_1_tax_decimal;
        }
        // 40 (1)

        $income_40_2_period = '';
        $div_income_40_2_period = '<div style="position: absolute; width: 98px; top: 414; left: 438; text-align: center; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_2_period . '</div>';
        if (isset($income_40_2_period) && !empty($income_40_2_period)) {
            $html .= $div_income_40_2_period;
        }

        $income_40_2_value = 0;
        $income_40_2_value_number = '0';
        $income_40_2_value_decimal = '00';
        if (isset($income_40_2_value) && !empty($income_40_2_value)) {
            if ($income_40_2_value > 0) {
                list($income_40_2_value_number, $income_40_2_value_decimal) = explode('.', $income_40_2_value);
            }
        }
        
        $div_income_40_2_value_number = '<div style="position: absolute; width: 88px; top: 414; left: 542; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_2_value_number, 0) . '</div>';
        $div_income_40_2_value_decimal = '<div style="position: absolute; width: 20px; top: 414; left: 629; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_2_value_decimal . '</div>';
        if (isset($income_40_2_value) && !empty($income_40_2_value) && $income_40_2_value > 0) {
            $html .= $div_income_40_2_value_number;
            $html .= $div_income_40_2_value_decimal;
        }

        $income_40_2_tax = 0;
        $income_40_2_tax_number = '0';
        $income_40_2_tax_decimal = '00';
        if (isset($income_40_2_tax) && !empty($income_40_2_tax)) {
            if ($income_40_2_tax > 0) {
                list($income_40_2_tax_number, $income_40_2_tax_decimal) = explode('.', $income_40_2_tax);
            }
        }
        
        $div_income_40_2_tax_number = '<div style="position: absolute; width: 70px; top: 414; left: 655; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_2_tax_number, 0) . '</div>';
        $div_income_40_2_tax_decimal = '<div style="position: absolute; width: 20px; top: 414; left: 723; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_2_tax_decimal . '</div>';
        if (isset($income_40_2_tax) && !empty($income_40_2_tax) && $income_40_2_tax > 0) {
            $html .= $div_income_40_2_tax_number;
            $html .= $div_income_40_2_tax_decimal;
        }
        // 40 (2)

        $income_40_3_period = '';
        $div_income_40_3_period = '<div style="position: absolute; width: 98px; top: 433; left: 438; text-align: center; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_3_period . '</div>';
        if (isset($income_40_3_period) && !empty($income_40_3_period)) {
            $html .= $div_income_40_3_period;
        }

        $income_40_3_value = 0;
        $income_40_3_value_number = '0';
        $income_40_3_value_decimal = '00';
        if (isset($income_40_3_value) && !empty($income_40_3_value)) {
            if ($income_40_3_value > 0) {
                list($income_40_3_value_number, $income_40_3_value_decimal) = explode('.', $income_40_3_value);
            }
        }
        
        $div_income_40_3_value_number = '<div style="position: absolute; width: 88px; top: 433; left: 542; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_3_value_number, 0) . '</div>';
        $div_income_40_3_value_decimal = '<div style="position: absolute; width: 20px; top: 433; left: 629; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_3_value_decimal . '</div>';
        if (isset($income_40_3_value) && !empty($income_40_3_value) && $income_40_3_value > 0) {
            $html .= $div_income_40_3_value_number;
            $html .= $div_income_40_3_value_decimal;
        }

        $income_40_3_tax = 0;
        $income_40_3_tax_number = '0';
        $income_40_3_tax_decimal = '00';
        if (isset($income_40_3_tax) && !empty($income_40_3_tax)) {
            if ($income_40_3_tax > 0) {
                list($income_40_3_tax_number, $income_40_3_tax_decimal) = explode('.', $income_40_3_tax);
            }
        }
        
        $div_income_40_3_tax_number = '<div style="position: absolute; width: 70px; top: 433; left: 655; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_3_tax_number, 0) . '</div>';
        $div_income_40_3_tax_decimal = '<div style="position: absolute; width: 20px; top: 433; left: 723; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_3_tax_decimal . '</div>';
        if (isset($income_40_3_tax) && !empty($income_40_3_tax) && $income_40_3_tax > 0) {
            $html .= $div_income_40_3_tax_number;
            $html .= $div_income_40_3_tax_decimal;
        }
        // 40 (3)

        $income_40_4A_period = '';
        $div_income_40_4A_period = '<div style="position: absolute; width: 98px; top: 452; left: 438; text-align: center; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4A_period . '</div>';
        if (isset($income_40_4A_period) && !empty($income_40_4A_period)) {
            $html .= $div_income_40_4A_period;
        }

        $income_40_4A_value = 0;
        $income_40_4A_value_number = '0';
        $income_40_4A_value_decimal = '00';
        if (isset($income_40_4A_value) && !empty($income_40_4A_value)) {
            if ($income_40_4A_value > 0) {
                list($income_40_4A_value_number, $income_40_4A_value_decimal) = explode('.', $income_40_4A_value);
            }
        }
        
        $div_income_40_4A_value_number = '<div style="position: absolute; width: 88px; top: 452; left: 542; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_4A_value_number, 0) . '</div>';
        $div_income_40_4A_value_decimal = '<div style="position: absolute; width: 20px; top: 452; left: 629; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4A_value_decimal . '</div>';
        if (isset($income_40_4A_value) && !empty($income_40_4A_value) && $income_40_4A_value > 0) {
            $html .= $div_income_40_4A_value_number;
            $html .= $div_income_40_4A_value_decimal;
        }

        $income_40_4A_tax = 0;
        $income_40_4A_tax_number = '0';
        $income_40_4A_tax_decimal = '00';
        if (isset($income_40_4A_tax) && !empty($income_40_4A_tax)) {
            if ($income_40_4A_tax > 0) {
                list($income_40_4A_tax_number, $income_40_4A_tax_decimal) = explode('.', $income_40_4A_tax);
            }
        }
        
        $div_income_40_4A_tax_number = '<div style="position: absolute; width: 70px; top: 452; left: 655; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_4A_tax_number, 0) . '</div>';
        $div_income_40_4A_tax_decimal = '<div style="position: absolute; width: 20px; top: 452; left: 723; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4A_tax_decimal . '</div>';
        if (isset($income_40_4A_tax) && !empty($income_40_4A_tax) && $income_40_4A_tax > 0) {
            $html .= $div_income_40_4A_tax_number;
            $html .= $div_income_40_4A_tax_decimal;
        }
        // 40 (4) (ก)

        $income_40_4B_period["1-1"] = '';
        $div_income_40_4B_period["1-1"] = '<div style="position: absolute; width: 98px; top: 529.5; left: 438; text-align: center; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_period["1-1"] . '</div>';
        if (isset($income_40_4B_period["1-1"]) && !empty($income_40_4B_period["1-1"])) {
            $html .= $div_income_40_4B_period["1-1"];
        }

        $income_40_4B_value["1-1"] = 0;
        $income_40_4B_value_number["1-1"] = '0';
        $income_40_4B_value_decimal["1-1"] = '00';
        if (isset($income_40_4B_value["1-1"]) && !empty($income_40_4B_value["1-1"])) {
            if ($income_40_4B_value["1-1"] > 0) {
                list($income_40_4B_value_number["1-1"], $income_40_4B_value_decimal["1-1"]) = explode('.', $income_40_4B_value["1-1"]);
            }
        }
        
        $div_income_40_4B_value_number["1-1"] = '<div style="position: absolute; width: 88px; top: 529.5; left: 542; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_4B_value_number["1-1"], 0) . '</div>';
        $div_income_40_4B_value_decimal["1-1"] = '<div style="position: absolute; width: 20px; top: 529.5; left: 629; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_value_decimal["1-1"] . '</div>';
        if (isset($income_40_4B_value["1-1"]) && !empty($income_40_4B_value["1-1"]) && $income_40_4B_value["1-1"] > 0) {
            $html .= $div_income_40_4B_value_number["1-1"];
            $html .= $div_income_40_4B_value_decimal["1-1"];
        }

        $income_40_4B_tax["1-1"] = 0;
        $income_40_4B_tax_number["1-1"] = '0';
        $income_40_4B_tax_decimal["1-1"] = '00';
        if (isset($income_40_4B_tax["1-1"]) && !empty($income_40_4B_tax["1-1"])) {
            if ($income_40_4B_tax["1-1"] > 0) {
                list($income_40_4B_tax_number["1-1"], $income_40_4B_tax_decimal["1-1"]) = explode('.', $income_40_4B_tax["1-1"]);
            }
        }

        $div_income_40_4B_tax_number["1-1"] = '<div style="position: absolute; width: 70px; top: 529.5; left: 655; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_4B_tax_number["1-1"], 0) . '</div>';
        $div_income_40_4B_tax_decimal["1-1"] = '<div style="position: absolute; width: 20px; top: 529.5; left: 723; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_tax_decimal["1-1"] . '</div>';
        if (isset($income_40_4B_tax["1-1"]) && !empty($income_40_4B_tax["1-1"]) && $income_40_4B_tax["1-1"] > 0) {
            $html .= $div_income_40_4B_tax_number["1-1"];
            $html .= $div_income_40_4B_tax_decimal["1-1"];
        }
        // 40 (4) (ข) 1.1

        $income_40_4B_period["1-2"] = '';
        $div_income_40_4B_period["1-2"] = '<div style="position: absolute; width: 98px; top: 549; left: 438; text-align: center; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_period["1-2"] . '</div>';
        if (isset($income_40_4B_period["1-2"]) && !empty($income_40_4B_period["1-2"])) {
            $html .= $div_income_40_4B_period["1-2"];
        }

        $income_40_4B_value["1-2"] = 0;
        $income_40_4B_value_number["1-2"] = '0';
        $income_40_4B_value_decimal["1-2"] = '00';
        if (isset($income_40_4B_value["1-2"]) && !empty($income_40_4B_value["1-2"])) {
            if ($income_40_4B_value["1-2"] > 0) {
                list($income_40_4B_value_number["1-2"], $income_40_4B_value_decimal["1-2"]) = explode('.', $income_40_4B_value["1-2"]);
            }
        }
        
        $div_income_40_4B_value_number["1-2"] = '<div style="position: absolute; width: 88px; top: 549; left: 542; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_4B_value_number["1-2"], 0) . '</div>';
        $div_income_40_4B_value_decimal["1-2"] = '<div style="position: absolute; width: 20px; top: 549; left: 629; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_value_decimal["1-2"] . '</div>';
        if (isset($income_40_4B_value["1-2"]) && !empty($income_40_4B_value["1-2"]) && $income_40_4B_value["1-2"] > 0) {
            $html .= $div_income_40_4B_value_number["1-2"];
            $html .= $div_income_40_4B_value_decimal["1-2"];
        }

        $income_40_4B_tax["1-2"] = 0;
        $income_40_4B_tax_number["1-2"] = '0';
        $income_40_4B_tax_decimal["1-2"] = '00';
        if (isset($income_40_4B_tax["1-2"]) && !empty($income_40_4B_tax["1-2"])) {
            if ($income_40_4B_tax["1-2"] > 0) {
                list($income_40_4B_tax_number["1-2"], $income_40_4B_tax_decimal["1-2"]) = explode('.', $income_40_4B_tax["1-2"]);
            }
        }
        
        $div_income_40_4B_tax_number["1-2"] = '<div style="position: absolute; width: 70px; top: 549; left: 655; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_4B_tax_number["1-2"], 0) . '</div>';
        $div_income_40_4B_tax_decimal["1-2"] = '<div style="position: absolute; width: 20px; top: 549; left: 723; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_tax_decimal["1-2"] . '</div>';
        if (isset($income_40_4B_tax["1-2"]) && !empty($income_40_4B_tax["1-2"]) && $income_40_4B_tax["1-2"] > 0) {
            $html .= $div_income_40_4B_tax_number["1-2"];
            $html .= $div_income_40_4B_tax_decimal["1-2"];
        }
        // 40 (4) (ข) 1.2

        $income_40_4B_period["1-3"] = '';
        $div_income_40_4B_period["1-3"] = '<div style="position: absolute; width: 98px; top: 568.5; left: 438; text-align: center; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_period["1-3"] . '</div>';
        if (isset($income_40_4B_period["1-3"]) && !empty($income_40_4B_period["1-3"])) {
            $html .= $div_income_40_4B_period["1-3"];
        }

        $income_40_4B_value["1-3"] = 0;
        $income_40_4B_value_number["1-3"] = '0';
        $income_40_4B_value_decimal["1-3"] = '00';
        if (isset($income_40_4B_value["1-3"]) && !empty($income_40_4B_value["1-3"])) {
            if ($income_40_4B_value["1-3"] > 0) {
                list($income_40_4B_value_number["1-3"], $income_40_4B_value_decimal["1-3"]) = explode('.', $income_40_4B_value["1-3"]);
            }
        }
        
        $div_income_40_4B_value_number["1-3"] = '<div style="position: absolute; width: 88px; top: 568.5; left: 542; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_4B_value_number["1-3"], 0) . '</div>';
        $div_income_40_4B_value_decimal["1-3"] = '<div style="position: absolute; width: 20px; top: 568.5; left: 629; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_value_decimal["1-3"] . '</div>';
        if (isset($income_40_4B_value["1-3"]) && !empty($income_40_4B_value["1-3"]) && $income_40_4B_value["1-3"] > 0) {
            $html .= $div_income_40_4B_value_number["1-3"];
            $html .= $div_income_40_4B_value_decimal["1-3"];
        }

        $income_40_4B_tax["1-3"] = 0;
        $income_40_4B_tax_number["1-3"] = '0';
        $income_40_4B_tax_decimal["1-3"] = '00';
        if (isset($income_40_4B_tax["1-3"]) && !empty($income_40_4B_tax["1-3"])) {
            if ($income_40_4B_tax["1-3"] > 0) {
                list($income_40_4B_tax_number["1-3"], $income_40_4B_tax_decimal["1-3"]) = explode('.', $income_40_4B_tax["1-3"]);
            }
        }
        
        $div_income_40_4B_tax_number["1-3"] = '<div style="position: absolute; width: 70px; top: 568.5; left: 655; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_4B_tax_number["1-3"], 0) . '</div>';
        $div_income_40_4B_tax_decimal["1-3"] = '<div style="position: absolute; width: 20px; top: 568.5; left: 723; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_tax_decimal["1-3"] . '</div>';
        if (isset($income_40_4B_tax["1-3"]) && !empty($income_40_4B_tax["1-3"]) && $income_40_4B_tax["1-3"] > 0) {
            $html .= $div_income_40_4B_tax_number["1-3"];
            $html .= $div_income_40_4B_tax_decimal["1-3"];
        }
        // 40 (4) (ข) 1.3

        $income_40_4B_percentage["1-4"] = 0;
        $div_income_40_4B_percentage["1-4"] = '<div style="position: absolute; width: 40px; top: 589; left: 219; text-align: center; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_percentage["1-4"] . '</div>';
        if (isset($income_40_4B_percentage["1-4"]) && !empty($income_40_4B_percentage["1-4"]) && $income_40_4B_percentage["1-4"] > 0) {
            $html .= $div_income_40_4B_percentage["1-4"];
        }

        $income_40_4B_period["1-4"] = '';
        $div_income_40_4B_period["1-4"] = '<div style="position: absolute; width: 98px; top: 588; left: 438; text-align: center; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_period["1-4"] . '</div>';
        if (isset($income_40_4B_period["1-4"]) && !empty($income_40_4B_period["1-4"])) {
            $html .= $div_income_40_4B_period["1-4"];
        }

        $income_40_4B_value["1-4"] = 0;
        $income_40_4B_value_number["1-4"] = '0';
        $income_40_4B_value_decimal["1-4"] = '00';
        if (isset($income_40_4B_value["1-4"]) && !empty($income_40_4B_value["1-4"])) {
            if ($income_40_4B_value["1-4"] > 0) {
                list($income_40_4B_value_number["1-4"], $income_40_4B_value_decimal["1-4"]) = explode('.', $income_40_4B_value["1-4"]);
            }
        }
        
        $div_income_40_4B_value_number["1-4"] = '<div style="position: absolute; width: 88px; top: 588; left: 542; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_4B_value_number["1-4"], 0) . '</div>';
        $div_income_40_4B_value_decimal["1-4"] = '<div style="position: absolute; width: 20px; top: 588; left: 629; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_value_decimal["1-4"] . '</div>';
        if (isset($income_40_4B_value["1-4"]) && !empty($income_40_4B_value["1-4"]) && $income_40_4B_value["1-4"] > 0) {
            $html .= $div_income_40_4B_value_number["1-4"];
            $html .= $div_income_40_4B_value_decimal["1-4"];
        }

        $income_40_4B_tax["1-4"] = 0;
        $income_40_4B_tax_number["1-4"] = '0';
        $income_40_4B_tax_decimal["1-4"] = '00';
        if (isset($income_40_4B_tax["1-4"]) && !empty($income_40_4B_tax["1-4"])) {
            if ($income_40_4B_tax["1-4"] > 0) {
                list($income_40_4B_tax_number["1-4"], $income_40_4B_tax_decimal["1-4"]) = explode('.', $income_40_4B_tax["1-4"]);
            }
        }
        
        $div_income_40_4B_tax_number["1-4"] = '<div style="position: absolute; width: 70px; top: 588; left: 655; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_4B_tax_number["1-4"], 0) . '</div>';
        $div_income_40_4B_tax_decimal["1-4"] = '<div style="position: absolute; width: 20px; top: 588; left: 723; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_tax_decimal["1-4"] . '</div>';
        if (isset($income_40_4B_tax["1-4"]) && !empty($income_40_4B_tax["1-4"]) && $income_40_4B_tax["1-4"] > 0) {
            $html .= $div_income_40_4B_tax_number["1-4"];
            $html .= $div_income_40_4B_tax_decimal["1-4"];
        }
        // 40 (4) (ข) 1.4

        $income_40_4B_period["2-1"] = '';
        $div_income_40_4B_period["2-1"] = '<div style="position: absolute; width: 98px; top: 626.5; left: 438; text-align: center; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_period["2-1"] . '</div>';
        if (isset($income_40_4B_period["2-1"]) && !empty($income_40_4B_period["2-1"])) {
            $html .= $div_income_40_4B_period["2-1"];
        }

        $income_40_4B_value["2-1"] = 0;
        $income_40_4B_value_number["2-1"] = '0';
        $income_40_4B_value_decimal["2-1"] = '00';
        if (isset($income_40_4B_value["2-1"]) && !empty($income_40_4B_value["2-1"])) {
            if ($income_40_4B_value["2-1"] > 0) {
                list($income_40_4B_value_number["2-1"], $income_40_4B_value_decimal["2-1"]) = explode('.', $income_40_4B_value["2-1"]);
            }
        }
        
        $div_income_40_4B_value_number["2-1"] = '<div style="position: absolute; width: 88px; top: 626.5; left: 542; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_4B_value_number["2-1"], 0) . '</div>';
        $div_income_40_4B_value_decimal["2-1"] = '<div style="position: absolute; width: 20px; top: 626.5; left: 629; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_value_decimal["2-1"] . '</div>';
        if (isset($income_40_4B_value["2-1"]) && !empty($income_40_4B_value["2-1"]) && $income_40_4B_value["2-1"] > 0) {
            $html .= $div_income_40_4B_value_number["2-1"];
            $html .= $div_income_40_4B_value_decimal["2-1"];
        }

        $income_40_4B_tax["2-1"] = 0;
        $income_40_4B_tax_number["2-1"] = '0';
        $income_40_4B_tax_decimal["2-1"] = '00';
        if (isset($income_40_4B_tax["2-1"]) && !empty($income_40_4B_tax["2-1"])) {
            if ($income_40_4B_tax["2-1"] > 0) {
                list($income_40_4B_tax_number["2-1"], $income_40_4B_tax_decimal["2-1"]) = explode('.', $income_40_4B_tax["2-1"]);
            }
        }
        
        $div_income_40_4B_tax_number["2-1"] = '<div style="position: absolute; width: 70px; top: 626.5; left: 655; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_4B_tax_number["2-1"], 0) . '</div>';
        $div_income_40_4B_tax_decimal["2-1"] = '<div style="position: absolute; width: 20px; top: 626.5; left: 723; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_tax_decimal["2-1"] . '</div>';
        if (isset($income_40_4B_tax["2-1"]) && !empty($income_40_4B_tax["2-1"]) && $income_40_4B_tax["2-1"] > 0) {
            $html .= $div_income_40_4B_tax_number["2-1"];
            $html .= $div_income_40_4B_tax_decimal["2-1"];
        }
        // 40 (4) (ข) 2.1

        $income_40_4B_period["2-2"] = '';
        $div_income_40_4B_period["2-2"] = '<div style="position: absolute; width: 98px; top: 665.5; left: 438; text-align: center; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_period["2-2"] . '</div>';
        if (isset($income_40_4B_period["2-2"]) && !empty($income_40_4B_period["2-2"]) && $income_40_4B_period["2-2"] > 0) {
            $html .= $div_income_40_4B_period["2-2"];
        }

        $income_40_4B_value["2-2"] = 0;
        $income_40_4B_value_number["2-2"] = '0';
        $income_40_4B_value_decimal["2-2"] = '00';
        if (isset($income_40_4B_value["2-2"]) && !empty($income_40_4B_value["2-2"])) {
            if ($income_40_4B_value["2-2"] > 0) {
                list($income_40_4B_value_number["2-2"], $income_40_4B_value_decimal["2-2"]) = explode('.', $income_40_4B_value["2-2"]);
            }
        }
        
        $div_income_40_4B_value_number["2-2"] = '<div style="position: absolute; width: 88px; top: 665.5; left: 542; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_4B_value_number["2-2"], 0) . '</div>';
        $div_income_40_4B_value_decimal["2-2"] = '<div style="position: absolute; width: 20px; top: 665.5; left: 629; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_value_decimal["2-2"] . '</div>';
        if (isset($income_40_4B_value["2-2"]) && !empty($income_40_4B_value["2-2"]) && $income_40_4B_value["2-2"] > 0) {
            $html .= $div_income_40_4B_value_number["2-2"];
            $html .= $div_income_40_4B_value_decimal["2-2"];
        }

        $income_40_4B_tax["2-2"] = 0;
        $income_40_4B_tax_number["2-2"] = '0';
        $income_40_4B_tax_decimal["2-2"] = '00';
        if (isset($income_40_4B_tax["2-2"]) && !empty($income_40_4B_tax["2-2"])) {
            if ($income_40_4B_tax["2-2"] > 0) {
                list($income_40_4B_tax_number["2-2"], $income_40_4B_tax_decimal["2-2"]) = explode('.', $income_40_4B_tax["2-2"]);
            }
        }
        
        $div_income_40_4B_tax_number["2-2"] = '<div style="position: absolute; width: 70px; top: 665.5; left: 655; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_4B_tax_number["2-2"], 0) . '</div>';
        $div_income_40_4B_tax_decimal["2-2"] = '<div style="position: absolute; width: 20px; top: 665.5; left: 723; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_tax_decimal["2-2"] . '</div>';
        if (isset($income_40_4B_tax["2-2"]) && !empty($income_40_4B_tax["2-2"]) && $income_40_4B_tax["2-2"] > 0) {
            $html .= $div_income_40_4B_tax_number["2-2"];
            $html .= $div_income_40_4B_tax_decimal["2-2"];
        }
        // 40 (4) (ข) 2.2

        $income_40_4B_period["2-3"] = '';
        $div_income_40_4B_period["2-3"] = '<div style="position: absolute; width: 98px; top: 704; left: 438; text-align: center; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_period["2-3"] . '</div>';
        if (isset($income_40_4B_period["2-3"]) && !empty($income_40_4B_period["2-3"])) {
            $html .= $div_income_40_4B_period["2-3"];
        }

        $income_40_4B_value["2-3"] = 0;
        $income_40_4B_value_number["2-3"] = '0';
        $income_40_4B_value_decimal["2-3"] = '00';
        if (isset($income_40_4B_value["2-3"]) && !empty($income_40_4B_value["2-3"])) {
            if ($income_40_4B_value["2-3"] > 0) {
                list($income_40_4B_value_number["2-3"], $income_40_4B_value_decimal["2-3"]) = explode('.', $income_40_4B_value["2-3"]);
            }
        }
        
        $div_income_40_4B_value_number["2-3"] = '<div style="position: absolute; width: 88px; top: 704; left: 542; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_4B_value_number["2-3"], 0) . '</div>';
        $div_income_40_4B_value_decimal["2-3"] = '<div style="position: absolute; width: 20px; top: 704; left: 629; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_value_decimal["2-3"] . '</div>';
        if (isset($income_40_4B_value["2-3"]) && !empty($income_40_4B_value["2-3"]) && $income_40_4B_value["2-3"] > 0) {
            $html .= $div_income_40_4B_value_number["2-3"];
            $html .= $div_income_40_4B_value_decimal["2-3"];
        }

        $income_40_4B_tax["2-3"] = 0;
        $income_40_4B_tax_number["2-3"] = '0';
        $income_40_4B_tax_decimal["2-3"] = '00';
        if (isset($income_40_4B_tax["2-3"]) && !empty($income_40_4B_tax["2-3"])) {
            if ($income_40_4B_tax["2-3"] > 0) {
                list($income_40_4B_tax_number["2-3"], $income_40_4B_tax_decimal["2-3"]) = explode('.', $income_40_4B_tax["2-3"]);
            }
        }
        
        $div_income_40_4B_tax_number["2-3"] = '<div style="position: absolute; width: 70px; top: 704; left: 655; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_4B_tax_number["2-3"], 0) . '</div>';
        $div_income_40_4B_tax_decimal["2-3"] = '<div style="position: absolute; width: 20px; top: 704; left: 723; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_tax_decimal["2-3"] . '</div>';
        if (isset($income_40_4B_tax["2-3"]) && !empty($income_40_4B_tax["2-3"]) && $income_40_4B_tax["2-3"] > 0) {
            $html .= $div_income_40_4B_tax_number["2-3"];
            $html .= $div_income_40_4B_tax_decimal["2-3"];
        }
        // 40 (4) (ข) 2.3

        $income_40_4B_period["2-4"] = '';
        $div_income_40_4B_period["2-4"] = '<div style="position: absolute; width: 98px; top: 730; left: 438; text-align: center; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_period["2-4"] . '</div>';
        if (isset($income_40_4B_period["2-4"]) && !empty($income_40_4B_period["2-4"])) {
            $html .= $div_income_40_4B_period["2-4"];
        }

        $income_40_4B_value["2-4"] = 0;
        $income_40_4B_value_number["2-4"] = '0';
        $income_40_4B_value_decimal["2-4"] = '00';
        if (isset($income_40_4B_value["2-4"]) && !empty($income_40_4B_value["2-4"])) {
            if ($income_40_4B_value["2-4"] > 0) {
                list($income_40_4B_value_number["2-4"], $income_40_4B_value_decimal["2-4"]) = explode('.', $income_40_4B_value["2-4"]);
            }
        }
        
        $div_income_40_4B_value_number["2-4"] = '<div style="position: absolute; width: 88px; top: 730; left: 542; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_4B_value_number["2-4"], 0) . '</div>';
        $div_income_40_4B_value_decimal["2-4"] = '<div style="position: absolute; width: 20px; top: 730; left: 629; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_value_decimal["2-4"] . '</div>';
        if (isset($income_40_4B_value["2-4"]) && !empty($income_40_4B_value["2-4"]) && $income_40_4B_value["2-4"] > 0) {
            $html .= $div_income_40_4B_value_number["2-4"];
            $html .= $div_income_40_4B_value_decimal["2-4"];
        }

        $income_40_4B_tax["2-4"] = 0;
        $income_40_4B_tax_number["2-4"] = '0';
        $income_40_4B_tax_decimal["2-4"] = '00';
        if (isset($income_40_4B_tax["2-4"]) && !empty($income_40_4B_tax["2-4"])) {
            if ($income_40_4B_tax["2-4"] > 0) {
                list($income_40_4B_tax_number["2-4"], $income_40_4B_tax_decimal["2-4"]) = explode('.', $income_40_4B_tax["2-4"]);
            }
        }
        
        $div_income_40_4B_tax_number["2-4"] = '<div style="position: absolute; width: 70px; top: 730; left: 655; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_4B_tax_number["2-4"], 0) . '</div>';
        $div_income_40_4B_tax_decimal["2-4"] = '<div style="position: absolute; width: 20px; top: 730; left: 723; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_tax_decimal["2-4"] . '</div>';
        if (isset($income_40_4B_tax["2-4"]) && !empty($income_40_4B_tax["2-4"]) && $income_40_4B_tax["2-4"] > 0) {
            $html .= $div_income_40_4B_tax_number["2-4"];
            $html .= $div_income_40_4B_tax_decimal["2-4"];
        }
        // 40 (4) (ข) 2.4

        $income_40_4B_text["2-5"] = '';
        $div_income_40_4B_text["2-5"] = '<div style="position: absolute; width: 200px; top: 743.5; left: 198; text-align: left; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_text["2-5"] . '</div>';
        if (isset($income_40_4B_text["2-5"]) && !empty($income_40_4B_text["2-5"])) {
            $html .= $div_income_40_4B_text["2-5"];
        }

        $income_40_4B_period["2-5"] = '';
        $div_income_40_4B_period["2-5"] = '<div style="position: absolute; width: 98px; top: 742.5; left: 438; text-align: center; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_period["2-5"] . '</div>';
        if (isset($income_40_4B_period["2-5"]) && !empty($income_40_4B_period["2-5"])) {
            $html .= $div_income_40_4B_period["2-5"];
        }

        $income_40_4B_value["2-5"] = 0;
        $income_40_4B_value_number["2-5"] = '0';
        $income_40_4B_value_decimal["2-5"] = '00';
        if (isset($income_40_4B_value["2-5"]) && !empty($income_40_4B_value["2-5"])) {
            if ($income_40_4B_value["2-5"] > 0) {
                list($income_40_4B_value_number["2-5"], $income_40_4B_value_decimal["2-5"]) = explode('.', $income_40_4B_value["2-5"]);
            }
        }
        
        $div_income_40_4B_value_number["2-5"] = '<div style="position: absolute; width: 88px; top: 742.5; left: 542; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_4B_value_number["2-5"], 0) . '</div>';
        $div_income_40_4B_value_decimal["2-5"] = '<div style="position: absolute; width: 20px; top: 742.5; left: 629; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_value_decimal["2-5"] . '</div>';
        if (isset($income_40_4B_value["2-5"]) && !empty($income_40_4B_value["2-5"]) && $income_40_4B_value["2-5"] > 0) {
            $html .= $div_income_40_4B_value_number["2-5"];
            $html .= $div_income_40_4B_value_decimal["2-5"];
        }

        $income_40_4B_tax["2-5"] = 0;
        $income_40_4B_tax_number["2-5"] = '0';
        $income_40_4B_tax_decimal["2-5"] = '00';
        if (isset($income_40_4B_tax["2-5"]) && !empty($income_40_4B_tax["2-5"])) {
            if ($income_40_4B_tax["2-5"] > 0) {
                list($income_40_4B_tax_number["2-5"], $income_40_4B_tax_decimal["2-5"]) = explode('.', $income_40_4B_tax["2-5"]);
            }
        }
        
        $div_income_40_4B_tax_number["2-5"] = '<div style="position: absolute; width: 70px; top: 742.5; left: 655; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_4B_tax_number["2-5"], 0) . '</div>';
        $div_income_40_4B_tax_decimal["2-5"] = '<div style="position: absolute; width: 20px; top: 742.5; left: 723; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_4B_tax_decimal["2-5"] . '</div>';
        if (isset($income_40_4B_tax["2-5"]) && !empty($income_40_4B_tax["2-5"]) && $income_40_4B_tax["2-5"] > 0) {
            $html .= $div_income_40_4B_tax_number["2-5"];
            $html .= $div_income_40_4B_tax_decimal["2-5"];
        }
        // 40 (4) (ข) 2.5

        $income_40_8_period = '';
        $div_income_40_8_period = '<div style="position: absolute; width: 98px; top: 819.5; left: 438; text-align: center; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_8_period . '</div>';
        if (isset($income_40_8_period) && !empty($income_40_8_period)) {
            $html .= $div_income_40_8_period;
        }

        $income_40_8_value = 0;
        $income_40_8_value_number = '0';
        $income_40_8_value_decimal = '00';
        if (isset($income_40_8_value) && !empty($income_40_8_value)) {
            if ($income_40_8_value > 0) {
                list($income_40_8_value_number, $income_40_8_value_decimal) = explode('.', $income_40_8_value);
            }
        }
        
        $div_income_40_8_value_number = '<div style="position: absolute; width: 88px; top: 819.5; left: 542; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_8_value_number, 0) . '</div>';
        $div_income_40_8_value_decimal = '<div style="position: absolute; width: 20px; top: 819.5; left: 629; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_8_value_decimal . '</div>';
        if (isset($income_40_8_value) && !empty($income_40_8_value) && $income_40_8_value > 0) {
            $html .= $div_income_40_8_value_number;
            $html .= $div_income_40_8_value_decimal;
        }

        $income_40_8_tax = 0;
        $income_40_8_tax_number = '0';
        $income_40_8_tax_decimal = '00';
        if (isset($income_40_8_tax) && !empty($income_40_8_tax)) {
            if ($income_40_8_tax > 0) {
                list($income_40_8_tax_number, $income_40_8_tax_decimal) = explode('.', $income_40_8_tax);
            }
        }
        
        $div_income_40_8_tax_number = '<div style="position: absolute; width: 70px; top: 819.5; left: 655; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_8_tax_number, 0) . '</div>';
        $div_income_40_8_tax_decimal = '<div style="position: absolute; width: 20px; top: 819.5; left: 723; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_8_tax_decimal . '</div>';
        if (isset($income_40_8_tax) && !empty($income_40_8_tax)) {
            $html .= $div_income_40_8_tax_number;
            $html .= $div_income_40_8_tax_decimal;
        }
        // 40 (8) (A)

        $income_40_other_text = '';
        $div_income_40_other_text = '<div style="position: absolute; width: 280px; top: 840; left: 128; text-align: left; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_other_text . '</div>';
        if (isset($income_40_other_text) && !empty($income_40_other_text)) {
            $html .= $div_income_40_other_text;
        }

        $income_40_other_period = '';
        $div_income_40_other_period = '<div style="position: absolute; width: 98px; top: 839; left: 438; text-align: center; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_other_period . '</div>';
        if (isset($income_40_other_period) && !empty($income_40_other_period)) {
            $html .= $div_income_40_other_period;
        }

        $income_40_other_value = 0;
        $income_40_other_value_number = '0';
        $income_40_other_value_decimal = '00';
        if (isset($income_40_other_value) && !empty($income_40_other_value)) {
            if ($income_40_other_value > 0) {
                list($income_40_other_value_number, $income_40_other_value_decimal) = explode('.', $income_40_other_value);
            }
        }
        
        $div_income_40_other_value_number = '<div style="position: absolute; width: 88px; top: 839; left: 542; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_other_value_number, 0) . '</div>';
        $div_income_40_other_value_decimal = '<div style="position: absolute; width: 20px; top: 839; left: 629; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_other_value_decimal . '</div>';
        if (isset($income_40_other_value) && !empty($income_40_other_value) && $income_40_other_value > 0) {
            $html .= $div_income_40_other_value_number;
            $html .= $div_income_40_other_value_decimal;
        }

        $income_40_other_tax = 0;
        $income_40_other_tax_number = '0';
        $income_40_other_tax_decimal = '00';
        if (isset($income_40_other_tax) && !empty($income_40_other_tax)) {
            if ($income_40_other_tax > 0) {
                list($income_40_other_tax_number, $income_40_other_tax_decimal) = explode('.', $income_40_other_tax);
            }
        }
        
        $div_income_40_other_tax_number = '<div style="position: absolute; width: 70px; top: 839; left: 655; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($income_40_other_tax_number, 0) . '</div>';
        $div_income_40_other_tax_decimal = '<div style="position: absolute; width: 20px; top: 839; left: 723; text-align: right; border: 1px solid rgba(0, 0, 0, 0);">' . $income_40_other_tax_decimal . '</div>';
        if (isset($income_40_other_tax) && !empty($income_40_other_tax) && $income_40_other_tax > 0) {
            $html .= $div_income_40_other_tax_number;
            $html .= $div_income_40_other_tax_decimal;
        }
        // 40 (OTHER)
        // End - Income detail

        // Start - Total income
        $total_income = 0;
        $total_income_number = '0';
        $total_income_decimal = '00';
        if (isset($total_income) && !empty($total_income)) {
            if ($total_income > 0) {
                list($total_income_number, $total_income_decimal) = explode('.', $total_income);
            }
        }
        
        $div_total_income_number = '<div style="position: absolute; width: 88px; text-align: right; bottom: 233; left: 542; font-size: 112%; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($total_income_number, 0) . '</div>';
        $div_total_income_decimal = '<div style="position: absolute; width: 20px; text-align: right; bottom: 233; left: 629; font-size: 112%; border: 1px solid rgba(0, 0, 0, 0);">' . $total_income_decimal . '</div>';
        $div_total_income_text = '<div style="position: absolute; width: 480px; text-align: left; bottom: 207; left: 250; font-size: 112%; border: 1px solid rgba(0, 0, 0, 0);"><i>' . numberToText($total_income) . '</i></div>';
        if (isset($total_income) && !empty($total_income) && $total_income > 0) {
            $html .= $div_total_income_number;
            $html .= $div_total_income_decimal;
            $html .= $div_total_income_text;
        }
        
        // Start - Total with holding tax
        $total_wht = 0;
        $total_wht_number = '0';
        $total_wht_decimal = '00';
        if (isset($total_wht) && !empty($total_wht)) {
            if ($total_wht > 0) {
                list($total_wht_number, $total_wht_decimal) = explode('.', $total_wht);
            }
        }
        
        $div_total_wht_number = '<div style="position: absolute; width: 70px; text-align: right; bottom: 233; left: 655; font-size: 112%; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($total_wht_number, 0) . '</div>';
        $div_total_wht_decimal = '<div style="position: absolute; width: 20px; text-align: right; bottom: 233; left: 722; font-size: 112%; border: 1px solid rgba(0, 0, 0, 0);">' . $total_wht_decimal . '</div>';
        if (isset($total_wht) && !empty($total_wht) && $total_wht > 0) {
            $html .= $div_total_wht_number;
            $html .= $div_total_wht_decimal;
        }

        // Start - PND Type
        $div_check_type[1] = '<div style="position: absolute; top: 301; left: 283; font-size: 120%;"><i><b>/</b></i></div>';
        $div_check_type[2] = '<div style="position: absolute; top: 301; left: 387; font-size: 120%;"><i><b>/</b></i></div>';
        $div_check_type[3] = '<div style="position: absolute; top: 301; left: 531; font-size: 120%;"><i><b>/</b></i></div>';
        $div_check_type[4] = '<div style="position: absolute; top: 301; left: 633; font-size: 120%;"><i><b>/</b></i></div>';
        $div_check_type[5] = '<div style="position: absolute; top: 325.5; left: 283; font-size: 120%;"><i><b>/</b></i></div>';
        $div_check_type[6] = '<div style="position: absolute; top: 325.5; left: 387; font-size: 120%;"><i><b>/</b></i></div>';
        $div_check_type[7] = '<div style="position: absolute; top: 325.5; left: 531; font-size: 120%;"><i><b>/</b></i></div>';
        
        $pnd_type = 0;
        if (isset($pnd_type) && !empty($pnd_type)) {
            if ($pnd_type > 0) {
                $html .= $div_check_type[$pnd_type];
            }
        }
        // End - PND Type

        // Start - Voluntary Contribution
        $voluntary_value[1] = 0;
        $div_voluntary_cont[1] = '<div style="position: absolute; width: 50px; text-align: right; bottom: 185; left: 318; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($voluntary_value[1], 2) . '</div>';
        if (isset($voluntary_value[1]) && !empty($voluntary_value[1]) && $voluntary_value[1] > 0) {
            $html .= $div_voluntary_cont[1];
        }

        $voluntary_value[2] = 0;
        $div_voluntary_cont[2] = '<div style="position: absolute; width: 50px; text-align: right; bottom: 185; left: 488; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($voluntary_value[2], 2) . '</div>';
        if (isset($voluntary_value[2]) && !empty($voluntary_value[2]) && $voluntary_value[2] > 0) {
            $html .= $div_voluntary_cont[2];
        }

        $voluntary_value[3] = 0;
        $div_voluntary_cont[3] = '<div style="position: absolute; width: 53px; text-align: right; bottom: 185; left: 665; border: 1px solid rgba(0, 0, 0, 0);">' . number_format($voluntary_value[3], 2) . '</div>';
        if (isset($voluntary_value[3]) && !empty($voluntary_value[3]) && $voluntary_value[3] > 0) {
            $html .= $div_voluntary_cont[3];
        }
        // End - Voluntary Contribution

        // Start - Payer Condition
        $payer_condition_specify = '';
        $div_payer_condition[1] = '<div style="position: absolute; bottom: 155; left: 114; font-size: 120%;"><i><b>/</b></i></div>';
        $div_payer_condition[2] = '<div style="position: absolute; bottom: 155; left: 238; font-size: 120%;"><i><b>/</b></i></div>';
        $div_payer_condition[3] = '<div style="position: absolute; bottom: 155; left: 382; font-size: 120%;"><i><b>/</b></i></div>';
        $div_payer_condition[4] = '<div style="position: absolute; bottom: 155; left: 529; font-size: 120%;"><i><b>/</b></i></div>';
        $div_payer_condition_specify = '<div style="position: absolute; bottom: 155; left: 629;">' . $payer_condition_specify . '</div>';
        $div_payer_condition_specify_dash = '<div style="position: absolute; bottom: 155; left: 629;">-</div>';

        $payer_condition = 0;
        if (isset($payer_condition) && !empty($payer_condition)) {
            if ($payer_condition > 0) {
                $html .= $div_payer_condition[$payer_condition];
            }

            if ($payer_condition == 4 || $payer_condition == '4') {
                if (isset($payer_condition_specify) && !empty($payer_condition_specify)) {
                    $html .= $div_payer_condition_specify;
                } else {
                    $html .= $div_payer_condition_specify_dash;
                }
            }
        }
        // End - Payer Condition
        
        $page = $mpdf->importPage(1);
        $mpdf->useTemplate($page);
        $mpdf->WriteHTML($html);
        $mpdf->Output();
    }

}
