<?php
class Purchaserequest_m extends CI_Model {

    function __construct() {
        $this->load->model("Material_m");
    }

    function row($prid){
        $prrow = $this->db->select("*")
                            ->from("purchaserequests")
                            ->where("id", $prid)
                            ->get()->row();

        if(empty($prrow)) return null;

        return $prrow;
    }


    function updateStatus($prid, $update_to_status){
        $prrow = $this->row($prid);

        if($this->Permission_m->approve_purchase_request != true){
            return ["process"=>"fail", "Don't have permission"];
        }

        if(empty($prrow)){
            return ["process"=>"fail", "Not found MR"];
        }

        if($update_to_status == 3){
            if($prrow->status_id != 1){
                return ["process"=>"fail", "message"=>"Approval fail"];
            }

            $this->db->where('id', $prid);
            $this->db->update('purchaserequests', ["status_id"=>3]);

            return ["process"=>"success", "message"=>"Successfully Approved"];
        }

        if($update_to_status == 4){
            if($prrow->status_id != 1){
                return ["process"=>"fail", "message"=>"Disapproval fail"];
            }

            $this->db->where('id', $prid);
            $this->db->update('purchaserequests', ["status_id"=>4]);

            return ["process"=>"success", "message"=>"Successfully Disapproved"];
        }
    }

    function jLackedMaterial(){
        $db = $this->db;
        /*$sql = "SELECT bpim.*,p.title as projecttitle,bm.name as material_name, bm.unit `material_unit`,bpi.project_id,prj.title as project_name,
            IF(bmp.supplier_id IS NULL, 0, bmp.supplier_id) as supplier_id,
            IF(bmp.price IS NULL, 0, bmp.price) as price,
            IF(bs.company_name IS NULL, '', bs.company_name) as supplier_name,bs.currency,bs.currency_symbol
            FROM bom_project_item_materials as bpim
            INNER JOIN bom_project_items bpi ON bpi.id = bpim.project_item_id
            INNER JOIN projects as prj ON prj.id = bpi.project_id
            LEFT JOIN projects as p ON bpi.project_id=p.id
            LEFT JOIN bom_materials as bm ON bpim.material_id=bm.id
            LEFT JOIN (SELECT material_id,supplier_id,price/ratio as price FROM bom_material_pricings ORDER BY price ASC LIMIT 0,1) as bmp
                ON bmp.material_id=bpim.material_id
            LEFT JOIN bom_suppliers as bs ON bmp.supplier_id=bs.id
            WHERE bpim.ratio<0
        ";

        $rows = $db->query($sql)->result();


        $projects = [];
        foreach($rows as $row) {
            $row->ratio = abs($row->ratio);
            $span = '<div class="project'.$row->project_item_id.'_lacked_material lacked_material prj_'.$row->project_item_id.'" data-project-id="'.$row->project_id.'" data-project-name="'.$row->project_name.'" data-material-id="'.$row->material_id.'" data-lacked-amount="'.$row->ratio.'" data-unit="'.$row->material_unit.'" data-supplier-name="'.$row->supplier_name.'" data-supplier-id="'.@$row->supplier_id.'" data-price="'.$row->price.'" data-currency="'.$row->currency.'" data-currency_symbol="'.$row->currency_symbol.'" style="display:none;">'.$row->material_name.' '.$row->ratio.$row->material_unit.'</div>';
            $button = '<button type="button" class="btn btn-danger pull-right btn-pr" id="btn-pr1" onclick="purchaseRequest(\'#btn-pr1\',\'project'.$row->project_item_id.'_\')"><i class="fa fa-shopping-cart"></i> '.lang('request_purchasing_materials').'</button>';
            if(!isset($projects[$row->project_item_id])) {
                $project = [];
                $project[] = $row->project_item_id;
                $project[] = '<a href="javascript:;" onclick="javascript:jQuery(\'.prj_'.$row->project_item_id.'\').toggle();">'.$row->projecttitle.'</a>'.$span;
                $project[] = 1;
                $project[] = $button;
                $projects[$row->project_item_id] = $project;
            }else{
                $project = $projects[$row->project_item_id];
                $project[1] = $project[1].$span;
                $project[2]++;
                $project[3] = $button;
                $projects[$row->project_item_id] = $project;
            }
        }



        return json_encode(["data"=>array_values($projects), "success"=>1, "message"=>"Success"]);*/

        /*$bpimrows = $db->select("project_item_id, material_id, SUM(ratio) as TOTAL_LACKED")
                        ->from("bom_project_item_materials")
                        ->where("stock_id IS NULL")
                        ->get()->result();

        if(empty($bpimrows)){
            return;
        }

        $materials = [];

        foreach($bpimrows as $bpimrow){
            $total_lacked = abs($bpimrow->TOTAL_LACKED);

            $material_id = $bpimrow->material_id;

            if(!array_key_exists($material_id, $materials)){
                $m = [];
                $m[0] = $material_id;
                $m[1] = '<a href="javascript:;" onclick="javascript:jQuery(\'.prj_'.$bpimrow->project_item_id.'\').toggle();">'.$this->Material_m->getCode($material_id).'</a>';
                $m[2] = 12;
                $m[3] = 1;
                $m[4] = '<button type="button" class="btn btn-danger pull-right btn-pr" id="btn-pr1" onclick="purchaseRequest(\'#btn-pr1\',\'project'.$row->project_item_id.'_\')"><i class="fa fa-shopping-cart"></i> '.lang('request_purchasing_materials').'</button>';

                $materials[$material_id] = $m;
            }else{

            }
        }*/


        $bpimrows = $db->select("material_id, SUM(ratio) AS TOTAL_LACKED")
                        ->from("bom_project_item_materials")
                        ->where("stock_id IS NULL")
                        ->group_by("material_id")
                        ->get()->result();

        $materials = [];

        foreach($bpimrows as $bpimrow){
            $material_id = $bpimrow->material_id;
            $material_code = $this->Material_m->getCode($material_id);
            $total_lacked = abs($bpimrow->TOTAL_LACKED);

            $bpimrows = $db->select("project_item_id")
                            ->from("bom_project_item_materials")
                            ->where("material_id", $material_id)
                            ->group_by("project_item_id")
                            ->get()->result();


            $lacked_from_project_ids = [];
            $lacked_from_project_html = "";

            if(!empty($bpimrows)){
                foreach($bpimrows as $bpimrow){
                    $project_item_id = $bpimrow->project_item_id;

                    $bpirow = $db->select("project_id")
                                        ->from("bom_project_items")
                                        ->where("id", $project_item_id)
                                        ->get()->row();

                    if(empty($bpirow)) continue;
                    if(in_array($bpirow->project_id, $lacked_from_project_ids)) continue;

                    array_push($lacked_from_project_ids, $bpirow->project_id);
                    $lacked_from_project_html .= "<span class='lacked_from_project'>".$this->Projects_m->getName($bpirow->project_id)."</span>";
                }
            }


            $m = [];
            $m[0] = $material_id;
            $m[1] = '<a href="javascript:;" onclick="javascript:jQuery(\'.prj_'.$bpimrow->project_item_id.'\').toggle();">'.$this->Material_m->getCode($material_id).'</a>';
            $m[2] = $lacked_from_project_html;
            $m[3] = $total_lacked;
            $m[4] = '<button type="button" class="btn btn-danger pull-right btn-pr" id="btn-pr1"><i class="fa fa-shopping-cart"></i> '.lang('request_purchasing_materials').'</button>';

            $materials[] = $m;
        }


        return json_encode(["data"=>array_values($materials), "success"=>1, "message"=>"Success"]);
    }

    
}
