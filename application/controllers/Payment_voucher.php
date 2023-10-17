<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Payment_voucher extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        if ($this->Permission_m->access_purchase_request != true) {
            $this->session->set_flashdata('notice_error', lang('no_permissions'));
            redirect(get_uri("accounting/buy"));
        }

        $this->load->model('Purchase_request_m');
        $this->load->model('Purchase_order_m');
        $this->load->model('Payment_voucher_m');
        $this->load->model('Goods_receipt_m');
        $this->load->model('Bom_materials_model');
    }

    function index()
    {
        if ($this->input->post("datatable") == true) {
            jout(["data" => $this->Payment_voucher_m->indexDataSet()]);
            return;
        } elseif (isset($this->json->taskName)) {
            if ($this->json->taskName == "update_doc_status") jout($this->Payment_voucher_m->updateStatus());
            if ($this->json->taskName == "item_deleted") jout($this->Payment_voucher_m->deleteRecordPaymentReceipt($this->json->id));
            if ($this->json->taskName == "got_a_receipt") jout($this->Payment_voucher_m->gotConfirmedPaymentReceipt($this->json->id));
            return;
        }

        redirect("accounting/buy/payment_voucher");
    }

    function test()
    {
        jout(["data" => $this->Payment_voucher_m->indexDataSet()]);
        return;
    }

    function addedit()
    {
        if (isset($this->json->task)) {
            if ($this->json->task == "save_doc")
                jout($this->Payment_voucher_m->saveDoc());
            return;
        }

        $data = $this->Payment_voucher_m->getDoc($this->input->post("id"));

        $this->load->view('payment_voucher/addedit', $data);
    }

    function partial_payment_type()
    {
        if (isset($this->json->task)) {
            if ($this->json->task == "update_doc_status")
                jout($this->Payment_voucher_m->updateStatus());
            return;
        }

        $data = $this->Payment_voucher_m->getDoc($this->uri->segment(3));
        if ($data["status"] != "success")
            return;

        $this->load->view('payment_voucher/partial_payment_type', $data);
    }

    function view($id = 0)
    {
        if ($id == 0 || empty($id)) {
            redirect(get_uri("accounting/buy/payment_voucher"));
            return;
        }

        $data["active_module"] = "payment_voucher";
        
        $data["pv_id"] = $id;
        $data["pv_info"] = $this->Payment_voucher_m->dev2_getPaymentVoucherHeaderById($data["pv_id"]);
        $data["pv_info"]->total_in_text = "(" . numberToText($data["pv_info"]->total) . ")";
        $data["pv_detail"] = $this->Payment_voucher_m->dev2_getPaymentVoucherDetailByHeaderId($data["pv_id"]);
        $data["pv_method"] = $this->Payment_voucher_m->dev2_getPaymentMethodItemsById($data["pv_id"]);
        $data["created_by"] = $this->Users_m->getInfo($data["pv_info"]->created_by);
        $data["approved_by"] = $this->Users_m->getInfo($data["pv_info"]->approved_by);
        $data["supplier"] = $this->Suppliers_m->getInfo($data["pv_info"]->supplier_id);
        $data["supplier_contact"] = $this->Suppliers_m->getContactInfo($data["pv_info"]->supplier_id);
        $data["print_url"] = get_uri("payment_voucher/print/" . str_replace("=", "", base64_encode($data["pv_info"]->id . ':' . $data["pv_info"]->doc_number)));

        // var_dump(arr($data)); exit();
        $this->template->rander("payment_voucher/view", $data);
    }

    function print()
    {
        $this->data["doc"] = $doc = $this->Payment_voucher_m->getEdoc($this->uri->segment(3), null);
        if ($doc['status'] != 'success') redirect('forbidden');

        $this->data["additional_style"] = 'style="width: 30%;"';
        $this->data["docmode"] = "private_print";

        // var_dump(arr($this->data)); exit();
        $this->load->view('edocs/payment_voucher', $this->data);
    }

    function delete_doc()
    {
        if ($this->input->post('undo') == true) {
            jout($this->Payment_voucher_m->undoDoc());
            return;
        }

        jout($this->Payment_voucher_m->deleteDoc());
    }

    function items()
    {
        jout($this->Payment_voucher_m->items());
    }

    function item()
    {
        if (isset($this->json->task)) {
            if ($this->json->task == "save")
                jout($this->Payment_voucher_m->saveItem());
            return;
        }

        $suggestion = [];

        if ($this->input->get("task") != null) {
            if ($this->input->get("task") == "suggest_products") {
                $sprows = $this->Products_m->getRows();
                if (!empty($sprows)) {
                    foreach ($sprows as $sprow) {
                        $suggestion[] = ["id" => $sprow->id, "text" => $sprow->title, "description" => $sprow->description, "unit" => $sprow->unit_type, "price" => $sprow->rate];
                    }
                }
                $suggestion[] = array("id" => "+", "text" => "+ " . lang("create_new_item"));
                jout($suggestion);
            }
            return;
        }

        $data = $this->Payment_voucher_m->item();

        $this->load->view('payment_voucher/item', $data);
    }

    function share()
    {
        if (isset($this->json->task)) {
            if ($this->json->task == "gen_sharekey") jout($this->Payment_voucher_m->genShareKey());
            return;
        }

        $data = $this->Payment_voucher_m->getDoc($this->input->post("doc_id"));

        // var_dump(arr($data)); exit();
        $this->load->view("payment_voucher/share", $data);
    }

    function record_payment()
    {
        $post = $this->input->post();
        
        $data["header_info"] = $this->Payment_voucher_m->dev2_getPaymentVoucherHeaderById($post["doc_id"]);
        $data["header_info"]->remain_amount = $data["header_info"]->payment_amount - $data["header_info"]->pay_amount;
        $data["payments_dropdown"] = $this->Goods_receipt_m->payments_method();

        // var_dump(arr($data)); exit();
        $this->load->view("payment_voucher/addedit_payment", $data);
    }

    function payments_save()
    {
        $json = $this->json;

        $data = [
            "pv_id" => $json->documentId,
            "date" => $this->DateCaseConvert($json->paymentDate),
            "amount" => $json->paymentAmount,
            "payment_id" => $json->paymentMethodId,
            "type_name" => $json->paymentMethodName,
            "type_description" => $json->paymentMethodDescription,
            "created_by" => $this->login_user->id
        ];

        $insert_id = $this->Payment_voucher_m->postPaymentForPaymentVoucher($data);
        if (isset($insert_id) && !empty($insert_id)) {
            $this->Payment_voucher_m->postPayAmountForPaymentVoucherHeader($json->documentId, $json->paymentAmount);
        }

        $result = [
            "success" => true,
            "status" => "success",
            "post" => $json,
            "data" => $data
        ];
        
        echo json_encode($result);
    }

    private function DateCaseConvert(string $dateInput): string
    {
        // Convert "DD/MM/YYYY" to "YYYY-MM-DD" 
        $dateOutput = '';

        $dateOutput = explode('/', $dateInput);
        $dateOutput = array_reverse($dateOutput);
        $dateOutput = implode('-', $dateOutput);

        return $dateOutput;
    }

}