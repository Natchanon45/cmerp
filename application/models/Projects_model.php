<?php
use Monolog\Handler\PushoverHandler;

class Projects_model extends Crud_model {

    private $table = null;

    function __construct() {
        $this->table = 'projects';
        parent::__construct($this->table);
    }

    function get_details($options = array(), $getRolePermission = array() ) {
        $projects_table = $this->db->dbprefix('projects');
        $project_members_table = $this->db->dbprefix('project_members');
        $clients_table = $this->db->dbprefix('clients');
        $tasks_table = $this->db->dbprefix('tasks');
        $where = "";

        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND $projects_table.id=$id";
        }

        $project_type = get_array_value($options, "project_type");
        if ($project_type) {
            $where .= " AND $projects_table.project_type_id=$project_type";
        }

        $tasks_join = "";
        if($this->login_user->is_admin != 1){
            if($this->Permission_m->access_project == "assigned_only"){
                $where .= " AND $projects_table.created_by=".$this->login_user->id;
            }elseif($this->Permission_m->access_project == "specific"){
                $where .= " AND (FIND_IN_SET($projects_table.project_type_id, '".$this->Permission_m->access_project_specific."') OR $projects_table.project_type_id IS NULL)";

                /*$where .= " AND ($tasks_table.assigned_to=".$this->login_user->id." OR FIND_IN_SET('".$this->login_user->id."', $tasks_table.collaborators))";

                $tasks_join = "LEFT JOIN $tasks_table ON $projects_table.id = $tasks_table.project_id";*/
            }
        }

        $client_id = get_array_value($options, "client_id");
        if ($client_id) {
            $where .= " AND $projects_table.client_id=$client_id";
        }

        $status = get_array_value($options, "status");
        if ($status) {
            $where .= " AND $projects_table.status='$status'";
        }

        $statuses = get_array_value($options, "statuses");
        if ($statuses) {
            $where .= " AND (FIND_IN_SET($projects_table.status, '$statuses')) ";
        }

        $project_label = get_array_value($options, "project_label");
        if ($project_label) {
            $where .= " AND (FIND_IN_SET('$project_label', $projects_table.labels)) ";
        }

        $deadline = get_array_value($options, "deadline");
        $for_events_table = get_array_value($options, "for_events_table");
        if ($deadline && !$for_events_table) {
            $now = get_my_local_time("Y-m-d");
            if ($deadline === "expired") {
                $where .= " AND ($projects_table.deadline IS NOT NULL AND $projects_table.deadline<'$now')";
            } else {
                $where .= " AND ($projects_table.deadline IS NOT NULL AND $projects_table.deadline<='$deadline')";
            }
        }

        $start_date = get_array_value($options, "start_date");
        $start_date_for_events = get_array_value($options, "start_date_for_events");
        if ($start_date && $deadline) {
            if ($start_date_for_events) {
                $where .= " AND ($projects_table.start_date BETWEEN '$start_date' AND '$deadline') ";
            } else {
                $where .= " AND ($projects_table.deadline BETWEEN '$start_date' AND '$deadline') ";
            }
        }
        


        $extra_join = "";
        $extra_where = "";
        $user_id = get_array_value($options, "user_id");

        $starred_projects = get_array_value($options, "starred_projects");
        if ($starred_projects) {
            $where .= " AND FIND_IN_SET(':$user_id:',$projects_table.starred_by) ";
        }

        if (!$client_id && $user_id && !$starred_projects) {
            $extra_join = " LEFT JOIN (SELECT $project_members_table.user_id, $project_members_table.project_id FROM $project_members_table WHERE $project_members_table.user_id=$user_id AND $project_members_table.deleted=0 GROUP BY $project_members_table.project_id) AS project_members_table ON project_members_table.project_id= $projects_table.id ";
            $extra_where = " AND project_members_table.user_id=$user_id";
        }

        $select_labels_data_query = $this->get_labels_data_query();

        //prepare custom fild binding query
        $custom_fields = get_array_value($options, "custom_fields");
        $custom_field_query_info = $this->prepare_custom_field_query_string("projects", $custom_fields, $projects_table);
        $select_custom_fieds = get_array_value( $custom_field_query_info, "select_string" );
        $join_custom_fieds = get_array_value( $custom_field_query_info, "join_string" );

        $this->db->query('SET SQL_BIG_SELECTS=1');

        $sql = "
			SELECT 
				$projects_table.*, 
				$clients_table.company_name, 
				$clients_table.currency_symbol,  
				total_points_table.total_points, 
				completed_points_table.completed_points, 
				$select_labels_data_query 
				$select_custom_fieds
			FROM $projects_table
			LEFT JOIN $clients_table ON $clients_table.id = $projects_table.client_id
			LEFT JOIN (
				SELECT 
					project_id,
					SUM( points ) AS total_points 
				FROM $tasks_table 
				WHERE deleted = 0 GROUP BY project_id 
			) AS total_points_table ON total_points_table.project_id = $projects_table.id
			LEFT JOIN (
				SELECT 
					project_id, 
					SUM(points) AS completed_points 
				FROM $tasks_table 
				WHERE deleted = 0 
				AND status_id = 3 
				GROUP BY project_id 
			) AS  completed_points_table ON completed_points_table.project_id = $projects_table.id
            $tasks_join
			$extra_join   
			$join_custom_fieds    
			[WHERE]
			ORDER BY $projects_table.start_date DESC
		";
		
//LEFT JOIN $tasks_table ON $projects_table.id = $tasks_table.project_id

		$filters = array();
		/*if( isset( $this->getRolePermission['filters'] ) ) {
			$filters = $this->getRolePermission['filters'];
		}*/

        $where .= " AND $projects_table.deleted=0";
		
		if( !empty( $options['id'] ) ) {
			$filters['WHERE'][] = $projects_table .".id = ". $options['id'] ."";
		}
		
		if($where) {
            $filters['WHERE'][] = " 1 ".$where;
        }
		$sql = gencond_( $sql, $filters );
	
        return $this->db->query($sql);
    }

    function get_list_data_options($q) {
        $sql = "SELECT 
				projects.id as `value`, projects.title as `id`, projects.title as `text`
			FROM projects
            WHERE `title` LIKE '%{$q}%' AND description LIKE '%{$q}%' AND deleted = 0";
        return $this->db->query($sql);
    }

    function get_label_suggestions() {
        $projects_table = $this->db->dbprefix('projects');
        $sql = "SELECT GROUP_CONCAT(labels) as label_groups
        FROM $projects_table
        WHERE $projects_table.deleted=0";
        return $this->db->query($sql)->row()->label_groups;
    }

    function count_project_status($options = array()) {
        $projects_table = $this->db->dbprefix('projects');
        $project_members_table = $this->db->dbprefix('project_members');

        $extra_join = "";
        $extra_where = "";
        $user_id = get_array_value($options, "user_id");
        if ($user_id) {
            $extra_join = " LEFT JOIN (SELECT $project_members_table.user_id, $project_members_table.project_id FROM $project_members_table WHERE $project_members_table.user_id=$user_id AND $project_members_table.deleted=0 GROUP BY $project_members_table.project_id) AS project_members_table ON project_members_table.project_id= $projects_table.id ";
            $extra_where = " AND project_members_table.user_id=$user_id";
        }

        $sql = "SELECT $projects_table.status, COUNT($projects_table.id) as total
        FROM $projects_table
              $extra_join    
        WHERE $projects_table.deleted=0 AND ($projects_table.status='open' OR  $projects_table.status='completed') $extra_where
        GROUP BY $projects_table.status";
        $result = $this->db->query($sql)->result();

        $info = new stdClass();
        $info->open = 0;
        $info->completed = 0;
        foreach ($result as $value) {
            $status = $value->status;
            $info->$status = $value->total;
        }
        return $info;
    }

    function get_gantt_data($options = array()) {
        $tasks_table = $this->db->dbprefix('tasks');
        $milestones_table = $this->db->dbprefix('milestones');
        $users_table = $this->db->dbprefix('users');
        $task_status_table = $this->db->dbprefix('task_status');
        $project_members_table = $this->db->dbprefix('project_members');
        $projects_table = $this->db->dbprefix('projects');

        $where = "";

        $milestone_id = get_array_value($options, "milestone_id");
        if ($milestone_id) {
            $where .= " AND $tasks_table.milestone_id=$milestone_id";
        }

        $project_id = get_array_value($options, "project_id");
        if ($project_id) {
            $where .= " AND $tasks_table.project_id=$project_id";
        } else {
            //show only opened project's tasks on global view
            $where .= " AND $tasks_table.project_id IN(SELECT $projects_table.id FROM $projects_table WHERE $projects_table.deleted=0 AND $projects_table.status='open')";
        }

        $assigned_to = get_array_value($options, "assigned_to");
        if ($assigned_to) {
            $where .= " AND $tasks_table.assigned_to=$assigned_to";
        }

        $status_id = get_array_value($options, "status_id");
        if ($status_id) {
            $where .= " AND $tasks_table.status_id=$status_id";
        }

        $status_ids = get_array_value($options, "status_ids");
        if ($status_ids) {
            $where .= " AND $tasks_table.status_id IN($status_ids)";
        }

        $exclude_status = get_array_value($options, "exclude_status");
        if ($exclude_status) {
            $where .= " AND $tasks_table.status_id!=$exclude_status";
        }


        $extra_join = "";
        $extra_where = "";
        $user_id = get_array_value($options, "user_id");
        if ($user_id) {
            $extra_join = " LEFT JOIN (SELECT $project_members_table.user_id, $project_members_table.project_id FROM $project_members_table WHERE $project_members_table.user_id=$user_id AND $project_members_table.deleted=0 GROUP BY $project_members_table.project_id) AS project_members_table ON project_members_table.project_id= $tasks_table.project_id ";
            $extra_where = " AND project_members_table.user_id=$user_id";
        }

        $show_assigned_tasks_only_user_id = get_array_value($options, "show_assigned_tasks_only_user_id");
        if ($show_assigned_tasks_only_user_id) {
            $where .= " AND ($tasks_table.assigned_to=$show_assigned_tasks_only_user_id OR FIND_IN_SET('$show_assigned_tasks_only_user_id', $tasks_table.collaborators))";
        }

        $sql = "SELECT $tasks_table.id AS task_id, $tasks_table.title AS task_title, $tasks_table.status_id, $tasks_table.start_date, $tasks_table.deadline AS end_date, $tasks_table.parent_task_id,
             $milestones_table.id AS milestone_id, $milestones_table.title AS milestone_title, $milestones_table.due_date AS milestone_due_date, $tasks_table.assigned_to, CONCAT($users_table.first_name, ' ', $users_table.last_name ) AS assigned_to_name, $tasks_table.project_id, CONCAT($projects_table.title) AS project_name,
             $task_status_table.title AS status_title, $task_status_table.color AS status_color
                FROM $tasks_table
                LEFT JOIN $milestones_table ON $milestones_table.id= $tasks_table.milestone_id
                LEFT JOIN $users_table ON $users_table.id= $tasks_table.assigned_to
                LEFT JOIN $task_status_table ON $task_status_table.id =  $tasks_table.status_id
                LEFT JOIN $projects_table ON $projects_table.id= $tasks_table.project_id
                $extra_join
        WHERE $tasks_table.deleted=0 $where $extra_where
        ORDER BY $tasks_table.start_date, $milestones_table.due_date DESC";
        return $this->db->query($sql)->result();
    }

    function add_remove_star($project_id, $user_id, $type = "add") {
        $projects_table = $this->db->dbprefix('projects');

        $action = " CONCAT($projects_table.starred_by,',',':$user_id:') ";
        $where = " AND FIND_IN_SET(':$user_id:',$projects_table.starred_by) = 0"; //don't add duplicate

        if ($type != "add") {
            $action = " REPLACE($projects_table.starred_by, ',:$user_id:', '') ";
            $where = "";
        }

        $sql = "UPDATE $projects_table SET $projects_table.starred_by = $action
        WHERE $projects_table.id=$project_id $where";
        return $this->db->query($sql);
    }

    function get_starred_projects($user_id) {
        $projects_table = $this->db->dbprefix('projects');

        $sql = "SELECT $projects_table.*
        FROM $projects_table
        WHERE $projects_table.deleted=0 AND FIND_IN_SET(':$user_id:',$projects_table.starred_by)
        ORDER BY $projects_table.title ASC";
        return $this->db->query($sql);
    }

    function delete_project_and_sub_items($project_id) {
        $projects_table = $this->db->dbprefix('projects');
        $tasks_table = $this->db->dbprefix('tasks');
        $milestones_table = $this->db->dbprefix('milestones');
        $project_files_table = $this->db->dbprefix('project_files');
        $project_comments_table = $this->db->dbprefix('project_comments');
        $activity_logs_table = $this->db->dbprefix('activity_logs');
        $notifications_table = $this->db->dbprefix('notifications');

        //get project files info to delete the files from directory 
        $project_files_sql = "SELECT * FROM $project_files_table WHERE $project_files_table.deleted=0 AND $project_files_table.project_id=$project_id; ";
        $project_files = $this->db->query($project_files_sql)->result();

        //get project comments info to delete the files from directory 
        $project_comments_sql = "SELECT * FROM $project_comments_table WHERE $project_comments_table.deleted=0 AND $project_comments_table.project_id=$project_id; ";
        $project_comments = $this->db->query($project_comments_sql)->result();

        //delete the project and sub items
        $delete_project_sql = "UPDATE $projects_table SET $projects_table.deleted=1 WHERE $projects_table.id=$project_id; ";
        $this->db->query($delete_project_sql);

        $delete_tasks_sql = "UPDATE $tasks_table SET $tasks_table.deleted=1 WHERE $tasks_table.project_id=$project_id; ";
        $this->db->query($delete_tasks_sql);

        $delete_milestones_sql = "UPDATE $milestones_table SET $milestones_table.deleted=1 WHERE $milestones_table.project_id=$project_id; ";
        $this->db->query($delete_milestones_sql);

        $delete_files_sql = "UPDATE $project_files_table SET $project_files_table.deleted=1 WHERE $project_files_table.project_id=$project_id; ";
        $this->db->query($delete_files_sql);

        $delete_comments_sql = "UPDATE $project_comments_table SET $project_comments_table.deleted=1 WHERE $project_comments_table.project_id=$project_id; ";
        $this->db->query($delete_comments_sql);

        $delete_activity_logs_sql = "UPDATE $activity_logs_table SET $activity_logs_table.deleted=1 WHERE $activity_logs_table.log_for='project' AND $activity_logs_table.log_for_id=$project_id; ";
        $this->db->query($delete_activity_logs_sql);

        $delete_notifications_sql = "UPDATE $notifications_table SET $notifications_table.deleted=1 WHERE $notifications_table.project_id=$project_id; ";
        $this->db->query($delete_notifications_sql);


        //delete the comment files from directory
        $comment_file_path = get_setting("timeline_file_path");
        foreach ($project_comments as $comment_info) {
            if ($comment_info->files && $comment_info->files != "a:0:{}") {
                $files = unserialize($comment_info->files);
                foreach ($files as $file) {
                    delete_app_files($comment_file_path, array($file));
                }
            }
        }



        //delete the project files from directory
        $file_path = get_setting("project_file_path") . $project_id . "/";
        foreach ($project_files as $file) {
            delete_app_files($file_path, array(make_array_of_file($file)));
        }

        return true;
    }

    function get_search_suggestion($search = "", $options = array()) {
        $projects_table = $this->db->dbprefix('projects');
        $project_members_table = $this->db->dbprefix('project_members');

        $where = "";
        $extra_join = "";

        $user_id = get_array_value($options, "user_id");
        if ($user_id) {
            $extra_join = " LEFT JOIN (SELECT $project_members_table.user_id, $project_members_table.project_id FROM $project_members_table WHERE $project_members_table.user_id=$user_id AND $project_members_table.deleted=0 GROUP BY $project_members_table.project_id) AS project_members_table ON project_members_table.project_id= $projects_table.id ";
            $where = " AND project_members_table.user_id=$user_id";
        }

        $search = $this->db->escape_str($search);

        $sql = "SELECT $projects_table.id, $projects_table.title
        FROM $projects_table  
        $extra_join
        WHERE $projects_table.deleted=0 AND $projects_table.title LIKE '%$search%' $where
        ORDER BY $projects_table.title ASC
        LIMIT 0, 10";

        return $this->db->query($sql);
    }

    public function get_project_by_id($id = 0)
    {
        $this->db->select("*")->from("projects")->where("id", $id);
        $query = $this->db->get();

        return $query->row();
    }

    function dev2_getProjectItemIdByProjectId($project_id)
    {
        $query = $this->db->get_where('bom_project_items', array('project_id' => $project_id));
        return $query->row();
    }

    function dev2_countItemByProjectId($project_id)
    {
        $count = 0;
        if (!empty($project_id) && $project_id) {
            $query = $this->db->get_where('bom_project_items', array('project_id' => $project_id));
            $count = $query->num_rows();
        }

        return $count;
    }

    function dev2_countItemCanCreateMrByProjectId($project_id)
    {
        $count = 0;
        $sql = "SELECT bpim.id FROM bom_project_item_materials bpim 
        INNER JOIN bom_project_items bpi ON bpim.project_item_id = bpi.id 
        WHERE bpi.project_id = '" . $project_id . "' AND bpim.ratio > 0 AND bpim.used_status = 0 AND bpim.mr_id IS NULL 
        ORDER BY bpim.material_id ASC";

        if (isset($project_id) && $project_id != 0) {
            $query = $this->db->query($sql);
            $count = $query->num_rows();
        }
        return $count;
    }

    function dev2_countItemRecalculateByProjectId($project_id)
    {
        $count = 0;
        $sql = "SELECT bpim.id FROM bom_project_item_materials bpim 
        INNER JOIN bom_project_items bpi ON bpim.project_item_id = bpi.id 
        WHERE bpi.project_id = '" . $project_id . "' AND bpim.ratio < 0 AND bpim.used_status = 0 AND bpim.pr_id IS NULL 
        ORDER BY bpim.material_id ASC";

        if (isset($project_id) && $project_id != 0) {
            $query = $this->db->query($sql);
            $count = $query->num_rows();
        }
        return $count;
    }

    function getOpenProjectList()
    {
        $result = array();
        $this->db->where('deleted', 0);
        if (!$this->login_user->is_admin) {
            // $this->db->where('created_by', $this->login_user->id);
            // temporary stop to verify is_admin
        }

        $query = $this->db->get('projects')->result();
        if (sizeof($query)) {
            $result = $query;
        }

        return $result;
    }

    function getProjectNameById($id)
    {
        $project_name = "";

        $this->db->select('title');
        $this->db->where('id', $id);

        $query = $this->db->get('projects')->row();
        if ($query) {
            $project_name = $query->title;
        }

        return $project_name;
    }

    function getItemIdByProjectId($project_id)
    {
        $query = $this->db->select('id')
            ->from('bom_project_items')
            ->where('project_id', $project_id)
            ->get()
            ->row();

        if (empty($query->id)) return null;
        return $query->id;
    }

    // dev2:start multiple production orders
    public function dev2_getProjectInfoByProjectId(int $id): array
    {
        $info = array();
        $get = $this->db->get_where("projects", ["id" => $id])->result();

        if (sizeof($get) && !empty($get)) {
            $info = (array) $get[0];
        }
        return $info;
    }

    public function dev2_getProductionOrderListByProjectId(int $id): array
    {
        $info = array();
        $this->db->select("*")->from("bom_project_items");
        $this->db->where("project_id", $id);

        $produce_status = $this->input->post("produce_status");
        if (isset($produce_status) && !empty($produce_status)) {
            $this->db->where("produce_status", $produce_status);
        }

        $mr_status = $this->input->post("mr_status");
        if (isset($mr_status) && !empty($mr_status)) {
            $this->db->where("mr_status", $mr_status);
        }

        $get = $this->db->get()->result();

        if (sizeof($get) && !empty($get)) {
            foreach ($get as $item) {
                $item->item_info = $this->dev2_getProductInfoByProductId($item->item_id);
                $item->mixing_group_info = $this->dev2_getMixingGroupInfoByMixingGroupId($item->mixing_group_id);
            }

            $info = $get;
        }
        return $info;
    }

    public function dev2_getProductInfoByProductId(int $id): stdClass
    {
        $info = new stdClass();
        $get = $this->db->get_where("items", ["id" => $id])->row();

        if (!empty($get)) {
            $info = $get;
        }
        return $info;
    }

    public function dev2_getMixingGroupInfoByMixingGroupId(int $id): stdClass
    {
        $info = new stdClass();
        $get = $this->db->get_where("bom_item_mixing_groups", ["id" => $id])->row();

        if (!empty($get)) {
            $info = $get;
        }
        return $info;
    }

    public function dev2_getMixingListByProjectId(int $project_id) : array
    {
        $info = [];
        $get_rm = $this->db->get_where("bom_project_item_materials", ["project_id" => $project_id])->result();
        $get_sfg = $this->db->get_where("bom_project_item_items", ["project_id" => $project_id])->result();

        if (!empty($get_rm) && sizeof($get_rm)) {
            foreach ($get_rm as $rm) {
                $info[] = $rm;
            }
        }

        if (!empty($get_sfg) && sizeof($get_sfg)) {
            foreach ($get_sfg as $sfg) {
                $info[] = $sfg;
            }
        }

        return $info;
    }

    public function dev2_getMixingCategoryListByProjectId(int $project_id) : array
    {
        $info = [
            "rm_list" => [],
            "rm_cate" => [],
            "temp_rm_cate" => [],
            "sfg_list" => [],
            "sfg_cate" => [],
            "temp_sfg_cate" => []
        ];

        $sql = "
            SELECT `category_in_bom`, `material_id`, `stock_id`, `mr_id`, CASE WHEN `ratio` < 0 THEN `ratio` * -1 ELSE `ratio` END AS `quantity` 
            FROM `bom_project_item_materials` 
            WHERE `project_id` = ? 
            ORDER BY `category_in_bom`, `material_id` 
        ";

        $sql_sfg = "
            SELECT `category_in_bom`, `item_id` AS `material_id`, `mr_id`, `stock_id`, CASE WHEN `ratio` < 0 THEN `ratio` * -1 ELSE `ratio` END AS `quantity` 
            FROM `bom_project_item_items` 
            WHERE `project_id` = ? 
            ORDER BY `category_in_bom`, `item_id`;
        ";

        $query = $this->db->query($sql, $project_id)->result();
        $query_sfg = $this->db->query($sql_sfg, $project_id)->result();

        if (sizeof($query)) {
            foreach ($query as $row) {
                $row->material_info = $this->dev2_getRowInfoByRowId($row->material_id, "bom_materials");
                $row->stock_info = new stdClass();
                $row->mr_info = new stdClass();

                if (isset($row->stock_id) && !empty($row->stock_id)) {
                    $row->stock_info = $this->dev2_getRowInfoByRowId($row->stock_id, "bom_stocks");
                }

                if (isset($row->mr_id) && !empty($row->mr_id)) {
                    $row->mr_info = $this->dev2_getRowInfoByRowId($row->mr_id, "materialrequests");
                }
                
                array_push($info["temp_rm_cate"], $row->category_in_bom);
            }

            $info["rm_list"] = $query;
            $info["temp_rm_cate"] = array_unique($info["temp_rm_cate"]);
        }

        if (sizeof($query_sfg)) {
            foreach ($query_sfg as $row) {
                $row->item_info = $this->dev2_getRowInfoByRowId($row->material_id, "items");
                $row->stock_info = new stdClass();
                $row->mr_info = new stdClass();

                if (isset($row->stock_id) && !empty($row->stock_id)) {
                    $row->stock_info = $this->dev2_getRowInfoByRowId($row->stock_id, "bom_item_stocks");
                }

                if (isset($row->mr_id) && !empty($row->mr_id)) {
                    $row->mr_info = $this->dev2_getRowInfoByRowId($row->mr_id, "materialrequests");
                }
                
                array_push($info["temp_sfg_cate"], $row->category_in_bom);
            }

            $info["sfg_list"] = $query_sfg;
            $info["temp_sfg_cate"] = array_unique($info["temp_sfg_cate"]);
        }

        if (sizeof($info["temp_rm_cate"])) {
            foreach ($info["temp_rm_cate"] as $row) {
                $row_info = $this->dev2_getMaterialCategoryTitleByCategoryId($row);

                $info["rm_cate"][] = [
                    "id" => $row,
                    "title" => $row_info->title,
                    "item_type" => $row_info->item_type
                ];
            }
        }

        if (sizeof($info["temp_sfg_cate"])) {
            foreach ($info["temp_sfg_cate"] as $row) {
                $row_info = $this->dev2_getMaterialCategoryTitleByCategoryId($row);

                $info["sfg_cate"][] = [
                    "id" => $row,
                    "title" => $row_info->title,
                    "item_type" => $row_info->item_type
                ];
            }
        }

        return $info;
    }

    public function dev2_getRawMatCostOfProductionOrderByProductionOrderId(int $id, float $quantity) : float
    {
        $cost = array();
        $getRmUsageList = $this->db->get_where("bom_project_item_materials", ["project_item_id" => $id])->result();

        if (sizeof($getRmUsageList) && !empty($getRmUsageList)) {
            foreach ($getRmUsageList as $item) {
                if (isset($item->stock_id) && !empty($item->stock_id)) {
                    $item->stock_info = $this->dev2_getRowInfoByRowId($item->stock_id, "bom_stocks");
                }
                $item->cost = 0;

                if (isset($item->stock_info->price) && $item->stock_info->price != 0) {
                    $item->cost = ($item->stock_info->price / $item->stock_info->stock) * $item->ratio;
                }
                array_push($cost, $item->cost);
            }
        }

        return array_sum($cost);
    }

    public function dev2_getRawMatCostOfProductionOrderByProductionOrderIdNew(int $production_id) : float
    {
        $cost = array();

        $get_sfg = $this->db->get_where("bom_project_item_items", ["project_item_id" => $production_id])->result();
        $get_rm = $this->db->get_where("bom_project_item_materials", ["project_item_id" => $production_id])->result();

        if (sizeof($get_sfg)) {
            foreach ($get_sfg as $sfg) {
                if (isset($sfg->stock_id) && !empty($sfg->stock_id)) {
                    $sfg->stock_info = $this->dev2_getRowInfoByRowId($sfg->stock_id, "bom_item_stocks");
                }
                $sfg->cost = 0;

                if (isset($sfg->stock_info->price) && $sfg->stock_info->price != 0) {
                    $sfg->cost = ($sfg->stock_info->price / $sfg->stock_info->stock) * $sfg->ratio;
                }
                array_push($cost, $sfg->cost);
            }
        }

        if (sizeof($get_rm)) {
            foreach ($get_rm as $rm) {
                if (isset($rm->stock_id) && !empty($rm->stock_id)) {
                    $rm->stock_info = $this->dev2_getRowInfoByRowId($rm->stock_id, "bom_stocks");
                }
                $rm->cost = 0;

                if (isset($rm->stock_info->price) && $rm->stock_info->price != 0) {
                    $rm->cost = ($rm->stock_info->price / $rm->stock_info->stock) * $rm->ratio;
                }
                array_push($cost, $rm->cost);
            }
        }

        return array_sum($cost);
    }

    public function dev2_postProduceStateById(int $id, $status): array
    {
        $info = $this->dev2_getRowInfoByRowId($id, "bom_project_items");
        $result = array();

        if (!empty($info)) {
            // update to producing
            if ($status == "2") {
                if ($info->produce_status == "1" && $info->mr_status == "3") {
                    $this->db->where("id", $id);
                    $this->db->update("bom_project_items", ["produce_status" => $status]);

                    $result["status"] = "success";
                } else {
                    $result["status"] = "failure";
                }

                $result["info"] = $this->dev2_getRowInfoByRowId($id, "bom_project_items");
                $result["info"]->item_info = $this->dev2_getProductInfoByProductId($result["info"]->item_id);
                $result["info"]->mixing_group_info = $this->dev2_getMixingGroupInfoByMixingGroupId($result["info"]->mixing_group_id);
            }

            // update to produced completed
            if ($status == "3") {
                if ($info->produce_status == "2" && $info->mr_status == "3") {
                    // verify stock group id by project id from bom_item_groups
                    $stock_info = $this->dev2_getStockGroupByProjectId($info->project_id);
                    $item_info = $this->dev2_getRowInfoByRowId($info->item_id, "items");

                    // calc instock price
                    $instcok_price = 0;
                    if (isset($item_info->rate) && !empty($item_info->rate)) {
                        if ($item_info->rate > 0) {
                            $instcok_price = $item_info->rate * $info->quantity;
                        }
                    }

                    // get production cost from rm and sfg used
                    $production_cost = $this->dev2_getRawMatCostOfProductionOrderByProductionOrderIdNew($id);
                    if (!empty($production_cost) && $production_cost > 0) {
                        $instcok_price = $production_cost;
                    }

                    if (isset($stock_info->id) && !empty($stock_info->id)) {
                        // add an item to bom_item_stocks only
                        $item_data = [
                            "group_id" => $stock_info->id,
                            "item_id" => $info->item_id,
                            "production_id" => $info->id,
                            "mixing_group_id" => $info->mixing_group_id,
                            "stock" => $info->quantity,
                            "remaining" => $info->quantity,
                            "price" => $instcok_price
                        ];

                        if ($info->produce_in) {
                            $this->db->insert("bom_item_stocks", $item_data);
                        }
                    } else {
                        $project_info = $this->dev2_getRowInfoByRowId($info->project_id, "projects");

                        // create a fg stock header to bom_item_groups
                        $header_data = [
                            "project_id" => $project_info->id,
                            "name" => $project_info->title,
                            "po_no" => 0,
                            "created_by" => $this->login_user->id,
                            "created_date" => date("Y-m-d")
                        ];
                        
                        $this->db->insert("bom_item_groups", $header_data);
                        $header_id = $this->db->insert_id();

                        // add an item to bom_item_stocks only
                        $item_data = [
                            "group_id" => $header_id,
                            "item_id" => $info->item_id,
                            "production_id" => $info->id,
                            "mixing_group_id" => $info->mixing_group_id,
                            "stock" => $info->quantity,
                            "remaining" => $info->quantity,
                            "price" => $instcok_price
                        ];

                        if ($info->produce_in) {
                            $this->db->insert("bom_item_stocks", $item_data);
                        }
                    }

                    $this->db->where("id", $id);
                    $this->db->update("bom_project_items", ["produce_status" => $status]);

                    $result["status"] = "success";
                } else {
                    $result["status"] = "failure";
                }

                $result["info"] = $this->dev2_getRowInfoByRowId($id, "bom_project_items");
                $result["info"]->item_info = $this->dev2_getProductInfoByProductId($result["info"]->item_id);
                $result["info"]->mixing_group_info = $this->dev2_getMixingGroupInfoByMixingGroupId($result["info"]->mixing_group_id);
            }
        }

        return $result;
    }

    public function dev2_getFinishedGoodsDropdown(string $item_type): array
    {
        $dropdown = [];
        $get = $this->db->get_where("items", ["deleted" => 0, "item_type" => $item_type])->result();

        if (sizeof($get) && !empty($get)) {
            foreach ($get as $item) {
                $text = "";
                if (empty($item->item_code) || $item->item_code == null) {
                    $text = $item->title;
                } else {
                    $text = $item->item_code . ' - ' . $item->title;
                }

                $dropdown[] = [
                    "id" => $item->id,
                    "text" => $text,
                    "description" => $item->description,
                    "unit" => $item->unit_type
                ];
            }
        }
        return $dropdown;
    }

    public function dev2_getMixingGroupDropdown(string $item_type): array
    {
        $dropdown = [];

        // $get = $this->db->get("bom_item_mixing_groups")->result();
        $get = $this->db->select("bom_item_mixing_groups.*, items.item_type")
            ->from("bom_item_mixing_groups")
            ->join("items","bom_item_mixing_groups.item_id = items.id","left")
            ->where("items.item_type", $item_type)
            ->get()->result();

        if (sizeof($get) && !empty($get)) {
            foreach ($get as $item) {
                $dropdown[] = [
                    "id" => $item->id,
                    "item_id" => $item->item_id,
                    "name" => $item->name
                ];
            }
        }
        return $dropdown;
    }

    public function dev2_postProductionBomDataProcessing(array $data): array
    {
        $result = array();

        if (sizeof($data)) {
            foreach ($data as $item) {
                // insert header data to bom_project_items
                $this->db->insert("bom_project_items", [
                    "project_id" => $item["project_id"],
                    "item_type" => $item["item_type"],
                    "item_id" => $item["item_id"],
                    "mixing_group_id" => $item["item_mixing"],
                    "quantity" => $item["quantity"],
                    "produce_in" => $item["produce_in"],
                    "created_by" => $this->login_user->id
                ]);
                $bpi_id = $this->db->insert_id();
                $item["bpi_id"] = $bpi_id;

                // get bom data detail from bom_item_mixings
                $bim_list = array();
                if (!empty($item["item_mixing"]) && $item["item_mixing"] != 0) {
                    $bim_sql = "SELECT * FROM `bom_item_mixings` WHERE `group_id` = ? ORDER BY `material_id`";
                    $bim_list = $this->db->query($bim_sql, $item["item_mixing"])->result();

                    if (sizeof($bim_list)) {
                        foreach ($bim_list as $mixing) {
                            $mixing->total_ratio = $mixing->ratio * floatval($item["quantity"]);
                            $total_ratio = $mixing->total_ratio;

                            // get stock remaining each bom data detail from bom_stocks
                            $stock_sql = "
                                SELECT bs.id, bs.group_id, bs.material_id, bs.stock, bs.remaining, 
                                IFNULL(bpim.used, 0) AS used, bs.stock - IFNULL(bpim.used, 0) AS actual_remain 
                                FROM bom_stocks bs 
                                INNER JOIN bom_stock_groups bsg ON bsg.id = bs.group_id 
                                LEFT JOIN(
                                    SELECT stock_id, SUM(ratio) AS used 
                                    FROM bom_project_item_materials 
                                    WHERE material_id = ? 
                                    GROUP BY stock_id
                                ) AS bpim ON bs.id = bpim.stock_id 
                                WHERE bs.material_id = ? AND bs.remaining > 0 AND bs.stock - IFNULL(bpim.used, 0) > 0 
                                ORDER BY bsg.created_date ASC
                            ";

                            if ($mixing->item_type == "SFG") {
                                $stock_sql = "
                                    SELECT bs.id, bs.group_id, bs.item_id as material_id, bs.stock, bs.remaining, 
                                    IFNULL(bpim.used, 0) AS used, bs.stock - IFNULL(bpim.used, 0) AS actual_remain 
                                    FROM bom_item_stocks bs 
                                    INNER JOIN bom_item_groups bsg ON bsg.id = bs.group_id 
                                    LEFT JOIN(
                                        SELECT stock_id, SUM(ratio) AS used 
                                        FROM bom_project_item_items 
                                        WHERE item_id = ? 
                                        GROUP BY stock_id
                                    ) AS bpim ON bs.id = bpim.stock_id 
                                    WHERE bs.item_id = ? AND bs.remaining > 0 AND bs.stock - IFNULL(bpim.used, 0) > 0 
                                    ORDER BY bsg.created_date ASC
                                ";
                            }

                            $stock_list = $this->db->query($stock_sql, [$mixing->material_id, $mixing->material_id])->result();
                            
                            if (sizeof($stock_list)) {
                                foreach ($stock_list as $stocking) {
                                    if ($total_ratio > 0) {
                                        $remaining = floatval(min($stocking->remaining, $stocking->actual_remain));
                                        $used = min($total_ratio, $remaining);
                                        $total_ratio -= $used;

                                        if ($mixing->item_type == "SFG") {
                                            $this->db->insert("bom_project_item_items", [
                                                "project_id" => $item["project_id"],
                                                "project_item_id" => $bpi_id,
                                                "item_id" => $mixing->material_id,
                                                "stock_id" => $stocking->id,
                                                "from_mixing" => $mixing->group_id,
                                                "category_in_bom" => $mixing->cat_id,
                                                "ratio" => $used,
                                                "created_by" => $this->login_user->id
                                            ]);

                                            $bpim_id = $this->db->insert_id();
                                            $mixing->bpim[] = $this->dev2_getRowInfoByRowId($bpim_id, "bom_project_item_items");
                                        }

                                        if ($mixing->item_type == "RM") {
                                            $this->db->insert("bom_project_item_materials", [
                                                "project_id" => $item["project_id"],
                                                "project_item_id" => $bpi_id,
                                                "material_id" => $mixing->material_id,
                                                "stock_id" => $stocking->id,
                                                "from_mixing" => $mixing->group_id,
                                                "category_in_bom" => $mixing->cat_id,
                                                "ratio" => $used,
                                                "created_by" => $this->login_user->id
                                            ]);

                                            $bpim_id = $this->db->insert_id();
                                            $mixing->bpim[] = $this->dev2_getRowInfoByRowId($bpim_id, "bom_project_item_materials");
                                        }
                                    }
                                }
                            }

                            if ($total_ratio > 0) {
                                if ($mixing->item_type == "SFG") {
                                    $this->db->insert("bom_project_item_items", [
                                        "project_id" => $item["project_id"],
                                        "project_item_id" => $bpi_id,
                                        "item_id" => $mixing->material_id,
                                        "from_mixing" => $mixing->group_id,
                                        "category_in_bom" => $mixing->cat_id,
                                        "ratio" => $total_ratio * -1,
                                        "created_by" => $this->login_user->id
                                    ]);

                                    $bpim_id = $this->db->insert_id();
                                    $mixing->bpim[] = $this->dev2_getRowInfoByRowId($bpim_id, "bom_project_item_items");
                                }

                                if ($mixing->item_type == "RM") {
                                    $this->db->insert("bom_project_item_materials", [
                                        "project_id" => $item["project_id"],
                                        "project_item_id" => $bpi_id,
                                        "material_id" => $mixing->material_id,
                                        "from_mixing" => $mixing->group_id,
                                        "category_in_bom" => $mixing->cat_id,
                                        "ratio" => $total_ratio * -1,
                                        "created_by" => $this->login_user->id
                                    ]);

                                    $bpim_id = $this->db->insert_id();
                                    $mixing->bpim[] = $this->dev2_getRowInfoByRowId($bpim_id, "bom_project_item_materials");
                                }
                            }
                        }
                    } else {
                        $this->db->where("id", $bpi_id);
                        $this->db->update("bom_project_items", ["mr_status" => 3]);
                    }
                }
                $item["bim_list"] = $bim_list;
                $result[] = $item;
            }
        }

        return $result;
    }

    public function dev2_getProductionOrderHeaderById(int $id): stdClass
    {
        $info = new stdClass();
        $get = $this->db->get_where("bom_project_items", ["id" => $id])->row();

        if (!empty($get)) {
            $get->item_info = $this->dev2_getRowInfoByRowId($get->item_id, "items");
            $get->mixing_group = $this->dev2_getRowInfoByRowId($get->mixing_group_id, "bom_item_mixing_groups");
            $info = $get;
        }
        return $info;
    }

    public function dev2_getProductionOrderDetailByProjectHeaderId(int $project_id, int $project_item_id) : stdClass
    {
        $info = new stdClass();
        
        $get_rm = $this->db->select("*")
        ->from("bom_project_item_materials")
        ->where("project_id", $project_id)
        ->where("project_item_id", $project_item_id)
        ->order_by("material_id", "ASC")
        ->order_by("stock_id", "ASC")
        ->get()
        ->result();

        $get_sfg = $this->db->select("*")
        ->from("bom_project_item_items")
        ->where("project_id", $project_id)
        ->where("project_item_id", $project_item_id)
        ->order_by("item_id", "ASC")
        ->order_by("stock_id", "ASC")
        ->get()
        ->result();

        if (sizeof($get_rm) && !empty($get_rm)) {
            foreach ($get_rm as $item) {
                $item->material_info = $this->dev2_getRowInfoByRowId($item->material_id, "bom_materials");
                if ($item->ratio < 0) {
                    $item->required_qty = $item->ratio * -1;
                    $item->actual_total_remain = $this->dev2_getStockActualTotalRemainingByMaterialId($item->material_id);
                }

                if (isset($item->stock_id) && !empty($item->stock_id)) {
                    $item->stock_info = $this->dev2_getRowInfoByRowId($item->stock_id, "bom_stocks");
                    $item->stock_info->group_info = $this->dev2_getRowInfoByRowId($item->stock_info->group_id, "bom_stock_groups");
                }

                if (isset($item->mr_id) && !empty($item->mr_id)) {
                    $item->mr_info = $this->dev2_getRowInfoByRowId($item->mr_id, "materialrequests");
                }
            }
            $info->rm = (object) $get_rm;
        }

        if (sizeof($get_sfg) && !empty($get_sfg)) {
            foreach ($get_sfg as $item) {
                $item->material_info = $this->dev2_getRowInfoByRowId($item->item_id, "items");
                if ($item->ratio < 0) {
                    $item->required_qty = $item->ratio * -1;
                    $item->actual_total_remain = $this->dev2_getStockActualTotalRemainingBySfgId($item->item_id);
                }

                if (isset($item->stock_id) && !empty($item->stock_id)) {
                    $item->stock_info = $this->dev2_getRowInfoByRowId($item->stock_id, "bom_item_stocks");
                    $item->stock_info->group_info = $this->dev2_getRowInfoByRowId($item->stock_info->group_id, "bom_item_groups");
                }

                if (isset($item->mr_id) && !empty($item->mr_id)) {
                    $item->mr_info = $this->dev2_getRowInfoByRowId($item->mr_id, "materialrequests");
                }
            }
            $info->sfg = (object) $get_sfg;
        }

        return $info;
    }

    public function dev2_postProductionSetProducingStateAll(int $project_id): array
    {
        $info = [
            "result" => [],
            "process" => "failure",
            "success" => false
        ];

        $get = $this->db->get_where("bom_project_items", ["project_id" => $project_id, "produce_status" => 1, "mr_status" => 3])->result();
        if (sizeof($get) && !empty($get)) {
            foreach ($get as $item) {
                $this->db->where("id", $item->id);
                $this->db->update("bom_project_items", ["produce_status" => 2]);
            }

            $info["result"] = $get;
            $info["process"] = "success";
            $info["success"] = true;
        }

        return $info;
    }

    public function dev2_postProductionSetCompletedStateAll(int $project_id): array
    {
        $info = [
            "result" => [],
            "process" => "failure",
            "success" => false
        ];

        $project_info = $this->dev2_getRowInfoByRowId($project_id, "projects");

        $get = $this->db->get_where("bom_project_items", ["project_id" => $project_id, "produce_status" => 2, "mr_status" => 3])->result();
        if (sizeof($get) && !empty($get)) {
            // verify stock group id by project id from bom_item_groups
            $stock_info = $this->dev2_getStockGroupByProjectId($project_id);

            $header_id = 0;
            if (isset($stock_info->id) && !empty($stock_info->id)) {
                // get the fg stock header from bom_item_groups
                $header_data = (array) $this->dev2_getRowInfoByRowId($stock_info->id, "bom_item_groups");
                $header_id = $stock_info->id;
            } else {
                // create a fg stock header to bom_item_groups
                $header_data = [
                    "project_id" => $project_info->id,
                    "name" => $project_info->title,
                    "po_no" => 0,
                    "created_by" => $this->login_user->id,
                    "created_date" => date("Y-m-d")
                ];

                $this->db->insert("bom_item_groups", $header_data);
                $header_id = $this->db->insert_id();
            }

            $info["result"]["header_id"] = $header_id;
            $info["result"]["header_data"] = $header_data;

            foreach ($get as $item) {
                $item_info = $this->dev2_getRowInfoByRowId($item->item_id, "items");
                // calc instock price
                $instock_price = 0;
                if (isset($item_info->rate) && !empty($item_info->rate)) {
                    if ($item_info->rate > 0) {
                        $instock_price = $item_info->rate * $item->quantity;
                    }
                }

                // add some production order in stock item to bom_item_stocks
                $item_data = [
                    "group_id" => $header_id,
                    "item_id" => $item->item_id,
                    "production_id" => $item->id,
                    "mixing_group_id" => $item->mixing_group_id,
                    "stock" => $item->quantity,
                    "remaining" => $item->quantity,
                    "price" => $instock_price
                ];

                // if need stock after produced put item data into bom_item_stocks
                if ($item->produce_in) {
                    $this->db->insert("bom_item_stocks", $item_data);
                    $item_id = $this->db->insert_id();
                    $item_data["id"] = $item_id;

                    $info["result"]["items"][] = $item_data;
                }

                $this->db->where("id", $item->id);
                $this->db->update("bom_project_items", ["produce_status" => 3]);
            }

            $info["process"] = "success";
            $info["success"] = true;
        }

        return $info;
    }

    public function dev2_postProductionMaterialRequestCreationPreview(int $project_id, string $project_name, string $item_type) : array
    {
        $info = [
            "project_id" => $project_id,
            "project_name" => $project_name,
            "item_type" => $item_type,
            "production_list" => [],
            "production_ids" => '',
            "rm_list" => [],
            "rm_categories" => [],
            "sfg_list" => [],
            "sfg_categories" => [],
        ];

        // Get production order list
        if ($item_type == 'BOTH') {
            $production_list = $this->db->get_where("bom_project_items", ["project_id" => $project_id])->result();
        } else {
            $production_list = $this->db->get_where("bom_project_items", ["project_id" => $project_id, "item_type" => $item_type])->result();
        }
        
        $production_ids = [];
        if (sizeof($production_list)) {
            $info["production_list"] = $production_list;

            foreach ($production_list as $list) {
                $production_ids[] = $list->id;
            }
        }

        // Get rm item in bom of production order
        if (sizeof($production_ids)) {
            $ids = implode(",", $production_ids);
            $info["production_ids"] = $ids;

            $rm_sql = "SELECT * FROM bom_project_item_materials WHERE stock_id IS NOT NULL AND mr_id IS NULL AND project_item_id IN (" . $ids . ")";
            $rm_list = $this->db->query($rm_sql)->result();
            if (sizeof($rm_list)) {
                foreach ($rm_list as $list) {
                    $rm_categories[] = $list->category_in_bom;
                    $list->material_info = $this->dev2_getRowInfoByRowId($list->material_id, "bom_materials");
                    $list->stock_name = $this->dev2_getMaterialStockNameByStockId($list->stock_id);
                }
                
                $info["rm_categories"] = $this->dev2_getInCategoriesListByListIds($rm_categories);
                $info["rm_list"] = $rm_list;
            }

            $sfg_sql = "SELECT * FROM bom_project_item_items WHERE stock_id IS NOT NULL AND mr_id IS NULL AND project_item_id IN (" . $ids . ")";
            $sfg_list = $this->db->query($sfg_sql)->result();
            if (sizeof($sfg_list)) {
                foreach ($sfg_list as $list) {
                    $sfg_categories[] = $list->category_in_bom;
                    $list->item_info = $this->dev2_getRowInfoByRowId($list->item_id, "items");
                    $list->stock_name = $this->dev2_getItemStockNameByStockId($list->stock_id);
                }

                $info["sfg_categories"] = $this->dev2_getInCategoriesListByListIds($sfg_categories);
                $info["sfg_list"] = $sfg_list;
            }
        }

        return $info;
    }

    public function dev2_getMaterialStockNameByStockId(int $id) : string
    {
        $stock_name = '-';

        $sql = "SELECT `name` FROM `bom_stock_groups` AS `bg` LEFT JOIN `bom_stocks` AS `bs` ON `bg`.`id` = `bs`.`group_id` WHERE `bs`.`id` = ?";
        $query = $this->db->query($sql, $id)->row();

        if (!empty($query->name)) {
            $stock_name = $query->name;
        }
        return $stock_name;
    }

    public function dev2_getItemStockNameByStockId(int $id) : string
    {
        $stock_name = '-';

        $sql = "SELECT `name` FROM `bom_item_groups` AS `bg` LEFT JOIN `bom_item_stocks` AS `bs` ON `bg`.`id` = `bs`.`group_id` WHERE `bs`.`id` = ?";
        $query = $this->db->query($sql, $id)->row();

        if (!empty($query->name)) {
            $stock_name = $query->name;
        }
        return $stock_name;
    }

    private function dev2_getInCategoriesListByListIds(array $list_ids) : array
    {
        $categories_list = [];

        $ids = implode(",", $list_ids);
        $sql = "SELECT * FROM material_categories WHERE id IN (" . $ids . ")";
        $query = $this->db->query($sql)->result();
        if (sizeof($query)) {
            $categories_list = $query;
        }
        return $categories_list;
    }

    public function dev2_postProductionMaterialRequestCreationAll(int $project_id, string $project_name, string $production_ids) : array
    {
        $info = [
            "production_ids" => $production_ids,
            "mr_id" => null,
            "mr_info" => null,
            "mr_items_info" => null,
            "process" => "failure",
            "success" => false,
            "target" => null
        ];

        $sql_sfg = "SELECT * FROM bom_project_item_items WHERE project_id = " . $project_id . " AND project_item_id IN (" . $production_ids . ") AND ratio > 0 AND mr_id IS NULL AND used_status = 0 AND entry_flag = 0 ORDER BY item_id, stock_id";
        $sql_rm = "SELECT * FROM bom_project_item_materials WHERE project_id = " . $project_id . " AND project_item_id IN (" . $production_ids . ") AND ratio > 0 AND mr_id IS NULL AND used_status = 0 AND entry_flag = 0 ORDER BY material_id, stock_id";
        
        $get_sfg = $this->db->query($sql_sfg)->result();
        $get_rm = $this->db->query($sql_rm)->result();

        $param_docno = [
            "prefix" => "MR",
            "LPAD" => 4,
            "column" => "doc_no",
            "table" => "materialrequests"
        ];

        if ((sizeof($get_rm) && !empty($get_rm)) || (sizeof($get_sfg) && !empty($get_sfg))) {
            // create a material request document
            $header_data = [
                "doc_no" => $this->Db_model->genDocNo($param_docno),
                "mr_type" => 3,
                "project_name" => $project_name,
                "project_id" => $project_id,
                "mr_date" => date("Y-m-d"),
                "status_id" => 1,
                "created_by" => $this->login_user->id,
                "requester_id" => $this->login_user->id
            ];

            $this->db->insert("materialrequests", $header_data);
            $header_id = $this->db->insert_id();

            $project_item_ids = array();
            $mr_items_info = array();

            // sfg
            if (sizeof($get_sfg)) {
                foreach ($get_sfg as $item) {
                    // add some material items to material request document
                    $material_info = $this->dev2_getRowInfoByRowId($item->item_id, "items");
                    $item_data = [
                        "mr_id" => $header_id,
                        "project_id" => $project_id,
                        "project_item_id" => $item->project_item_id,
                        "project_name" => $project_name,
                        "code" => $material_info->item_code,
                        "title" => $material_info->title,
                        "description" => $material_info->description,
                        "quantity" => $item->ratio,
                        "unit_type" => $material_info->unit_type,
                        "item_type" => 'SFG',
                        "item_id" => $item->item_id,
                        "bpim_id" => $item->id,
                        "stock_id" => $item->stock_id,
                        "category_in_bom" => $item->category_in_bom
                    ];
                    
                    $this->db->insert("mr_items", $item_data);
                    $item_id = $this->db->insert_id();
                    $mr_items_info[$item_id] = $item_data;

                    // patch mr id to bom project item material
                    $this->dev2_patchMaterialRequestIdForBomItemByItemId($item->id, $header_id, "bom_project_item_items");

                    array_push($project_item_ids, $item->project_item_id);
                }
            }

            // rm
            if (sizeof($get_rm)) {
                foreach ($get_rm as $item) {
                    // add some material items to material request document
                    $material_info = $this->dev2_getRowInfoByRowId($item->material_id, "bom_materials");
                    $item_data = [
                        "mr_id" => $header_id,
                        "project_id" => $project_id,
                        "project_item_id" => $item->project_item_id,
                        "project_name" => $project_name,
                        "code" => $material_info->name,
                        "title" => $material_info->production_name,
                        "description" => $material_info->description,
                        "quantity" => $item->ratio,
                        "unit_type" => $material_info->unit,
                        "item_type" => 'RM',
                        "material_id" => $item->material_id,
                        "bpim_id" => $item->id,
                        "stock_id" => $item->stock_id,
                        "category_in_bom" => $item->category_in_bom
                    ];
                    
                    $this->db->insert("mr_items", $item_data);
                    $item_id = $this->db->insert_id();
                    $mr_items_info[$item_id] = $item_data;

                    // patch mr id to bom project item material
                    $this->dev2_patchMaterialRequestIdForBomItemByItemId($item->id, $header_id, "bom_project_item_materials");

                    array_push($project_item_ids, $item->project_item_id);
                }
            }

            // patch material request status for production order
            $project_item_id = array_unique($project_item_ids);
            foreach ($project_item_id as $id) {
                $this->dev2_patchProductionMaterialRequestStatus($id);
            }

            $info = [
                "mr_id" => $header_id,
                "mr_info" => $header_data,
                "mr_items_info" => $mr_items_info,
                "process" => "success",
                "success" => true,
                "target" => get_uri("materialrequests/view_group/" . $header_id)
            ];
        }

        return $info;
    }

    public function dev2_postProductionBomRecalculation(int $project_id, string $project_name, int $project_item_id, string $item_type): array
    {
        $info = [
            "process" => "failure",
            "success" => false,
            "have_stock_id" => null,
            "pulling_stock" => null,
            "bpim_info" => null,
            "scrap_qty" => null
        ];

        if ($item_type == "SFG") {
            $bomHaveStockString = "SELECT * FROM bom_project_item_items WHERE stock_id IS NOT NULL AND project_id = ? AND `project_item_id` = ? ORDER BY item_id";
            $bomHaveStockQuery = $this->db->query($bomHaveStockString, [$project_id, $project_item_id]);
            $bomHaveStock = $bomHaveStockQuery->result();
            
            $haveStockId = [];
            if (sizeof($bomHaveStock) && isset($bomHaveStock) && !empty($bomHaveStock)) {
                foreach ($bomHaveStock as $bhs) {
                    array_push($haveStockId, $bhs->stock_id);
                }
            }
            $info["have_stock_id"] = implode(",", $haveStockId);

            $bomNoStockString = "SELECT * FROM bom_project_item_items WHERE stock_id IS NULL AND project_id = ? AND `project_item_id` = ? ORDER BY item_id";
            $bomNoStockQuery = $this->db->query($bomNoStockString, [$project_id, $project_item_id]);
            $bomNoStock = $bomNoStockQuery->result();

            if (sizeof($bomNoStock) && isset($bomNoStock) && !empty($bomNoStock)) {
                foreach ($bomNoStock as $bns) {
                    $pullingStockString = "
                        SELECT bs.id, bs.group_id, bs.item_id, bs.stock, bs.remaining, 
                        IFNULL(bpim.used, 0) AS used, bs.stock - IFNULL(bpim.used, 0) AS actual_remain 
                        FROM bom_item_stocks bs 
                        INNER JOIN bom_item_groups bsg ON bsg.id = bs.group_id 
                        LEFT JOIN (
                            SELECT stock_id, SUM(ratio) AS used 
                            FROM bom_project_item_items 
                            WHERE item_id = " . $bns->item_id . " 
                            GROUP BY stock_id
                        ) AS bpim ON bs.id = bpim.stock_id 
                        WHERE bs.item_id = " . $bns->item_id . " AND bs.remaining > 0 AND bs.stock - IFNULL(bpim.used, 0) > 0 
                        AND bs.id NOT IN(" . $info["have_stock_id"] . ") 
                        ORDER BY bsg.created_date ASC
                    ";

                    if ($info["have_stock_id"] == null) {
                        $pullingStockString = "
                            SELECT bs.id, bs.group_id, bs.item_id, bs.stock, bs.remaining, 
                            IFNULL(bpim.used, 0) AS used, bs.stock - IFNULL(bpim.used, 0) AS actual_remain 
                            FROM bom_item_stocks bs 
                            INNER JOIN bom_item_groups bsg ON bsg.id = bs.group_id 
                            LEFT JOIN (
                                SELECT stock_id, SUM(ratio) AS used 
                                FROM bom_project_item_items 
                                WHERE item_id = " . $bns->item_id . " 
                                GROUP BY stock_id
                            ) AS bpim ON bs.id = bpim.stock_id 
                            WHERE bs.item_id = " . $bns->item_id . " AND bs.remaining > 0 AND bs.stock - IFNULL(bpim.used, 0) > 0 
                            ORDER BY bsg.created_date ASC
                        ";
                    }
                    $pullingStockQuery = $this->db->query($pullingStockString);
                    $pullingStock = $pullingStockQuery->result();

                    if (sizeof($pullingStock) && isset($pullingStock) && !empty($pullingStock)) {
                        $info["pulling_stock"][] = $pullingStock;
                        $total_ratio = $bns->ratio * -1;
                        
                        foreach ($pullingStock as $ps) {
                            if ($total_ratio > 0) {
                                $remaining = floatval(min($ps->remaining, $ps->actual_remain));
                                $used = min($total_ratio, $remaining);
                                $total_ratio -= $used;

                                $this->db->insert("bom_project_item_items", [
                                    "project_id" => $project_id,
                                    "project_item_id" => $project_item_id,
                                    "item_id" => $bns->item_id,
                                    "stock_id" => $ps->id,
                                    "from_mixing" => $bns->from_mixing,
                                    "category_in_bom" => $bns->category_in_bom,
                                    "ratio" => $used,
                                    "created_by" => $this->login_user->id
                                ]);
                                $bpim_id = $this->db->insert_id();
                                $info["bpim_info"][] = $this->dev2_getRowInfoByRowId($bpim_id, "bom_project_item_items");
                            }
                        }
                        $info["scrap_qty"][] = $total_ratio;

                        if ($total_ratio > 0) {
                            $total_ratio = $total_ratio * -1;

                            $this->db->where("id", $bns->id);
                            $this->db->update("bom_project_item_items", ["ratio" => $total_ratio]);
                        } else {
                            $this->db->delete("bom_project_item_items", ["id" => $bns->id]);
                        }
                    }
                }

                $info["process"] = "success";
                $info["success"] = true;
            }
        }

        if ($item_type == "RM") {
            $bomHaveStockString = "SELECT * FROM bom_project_item_materials WHERE stock_id IS NOT NULL AND project_id = ? AND `project_item_id` = ? ORDER BY material_id";
            $bomHaveStockQuery = $this->db->query($bomHaveStockString, [$project_id, $project_item_id]);
            $bomHaveStock = $bomHaveStockQuery->result();
            
            $haveStockId = [];
            if (sizeof($bomHaveStock) && isset($bomHaveStock) && !empty($bomHaveStock)) {
                foreach ($bomHaveStock as $bhs) {
                    array_push($haveStockId, $bhs->stock_id);
                }
            }
            $info["have_stock_id"] = implode(",", $haveStockId);

            $bomNoStockString = "SELECT * FROM bom_project_item_materials WHERE stock_id IS NULL AND project_id = ? AND `project_item_id` = ? ORDER BY material_id";
            $bomNoStockQuery = $this->db->query($bomNoStockString, [$project_id, $project_item_id]);
            $bomNoStock = $bomNoStockQuery->result();

            if (sizeof($bomNoStock) && isset($bomNoStock) && !empty($bomNoStock)) {
                foreach ($bomNoStock as $bns) {
                    $pullingStockString = "
                        SELECT bs.id, bs.group_id, bs.material_id, bs.stock, bs.remaining, 
                        IFNULL(bpim.used, 0) AS used, bs.stock - IFNULL(bpim.used, 0) AS actual_remain 
                        FROM bom_stocks bs 
                        INNER JOIN bom_stock_groups bsg ON bsg.id = bs.group_id 
                        LEFT JOIN (
                            SELECT stock_id, SUM(ratio) AS used 
                            FROM bom_project_item_materials 
                            WHERE material_id = " . $bns->material_id . " 
                            GROUP BY stock_id
                        ) AS bpim ON bs.id = bpim.stock_id 
                        WHERE bs.material_id = " . $bns->material_id . " AND bs.remaining > 0 AND bs.stock - IFNULL(bpim.used, 0) > 0 
                        AND bs.id NOT IN(" . $info["have_stock_id"] . ") 
                        ORDER BY bsg.created_date ASC
                    ";

                    if ($info["have_stock_id"] == null) {
                        $pullingStockString = "
                            SELECT bs.id, bs.group_id, bs.material_id, bs.stock, bs.remaining, 
                            IFNULL(bpim.used, 0) AS used, bs.stock - IFNULL(bpim.used, 0) AS actual_remain 
                            FROM bom_stocks bs 
                            INNER JOIN bom_stock_groups bsg ON bsg.id = bs.group_id 
                            LEFT JOIN (
                                SELECT stock_id, SUM(ratio) AS used 
                                FROM bom_project_item_materials 
                                WHERE material_id = " . $bns->material_id . " 
                                GROUP BY stock_id
                            ) AS bpim ON bs.id = bpim.stock_id 
                            WHERE bs.material_id = " . $bns->material_id . " AND bs.remaining > 0 AND bs.stock - IFNULL(bpim.used, 0) > 0 
                            ORDER BY bsg.created_date ASC
                        ";
                    }
                    $pullingStockQuery = $this->db->query($pullingStockString);
                    $pullingStock = $pullingStockQuery->result();

                    if (sizeof($pullingStock) && isset($pullingStock) && !empty($pullingStock)) {
                        $info["pulling_stock"][] = $pullingStock;
                        $total_ratio = $bns->ratio * -1;
                        
                        foreach ($pullingStock as $ps) {
                            if ($total_ratio > 0) {
                                $remaining = floatval(min($ps->remaining, $ps->actual_remain));
                                $used = min($total_ratio, $remaining);
                                $total_ratio -= $used;

                                $this->db->insert("bom_project_item_materials", [
                                    "project_id" => $project_id,
                                    "project_item_id" => $project_item_id,
                                    "material_id" => $bns->material_id,
                                    "stock_id" => $ps->id,
                                    "from_mixing" => $bns->from_mixing,
                                    "category_in_bom" => $bns->category_in_bom,
                                    "ratio" => $used,
                                    "created_by" => $this->login_user->id
                                ]);
                                $bpim_id = $this->db->insert_id();
                                $info["bpim_info"][] = $this->dev2_getRowInfoByRowId($bpim_id, "bom_project_item_materials");
                            }
                        }
                        $info["scrap_qty"][] = $total_ratio;

                        if ($total_ratio > 0) {
                            $total_ratio = $total_ratio * -1;

                            $this->db->where("id", $bns->id);
                            $this->db->update("bom_project_item_materials", ["ratio" => $total_ratio]);
                        } else {
                            $this->db->delete("bom_project_item_materials", ["id" => $bns->id]);
                        }
                    }
                }

                $info["process"] = "success";
                $info["success"] = true;
            }
        }

        return $info;
    }

    public function dev2_postProductionMaterialRequestCreation(int $project_id, string $project_name, int $project_item_id): array
    {
        $info = [
            "mr_id" => null,
            "mr_info" => null,
            "mr_items_info" => null,
            "process" => "failure",
            "success" => false,
            "target" => null
        ];

        $sql_sfg = "SELECT * FROM bom_project_item_items WHERE project_id = ? AND project_item_id = ? AND ratio > 0 AND mr_id IS NULL AND used_status = 0 AND entry_flag = 0 ORDER BY item_id, stock_id";
        $sql_rm = "SELECT * FROM bom_project_item_materials WHERE project_id = ? AND project_item_id = ? AND ratio > 0 AND mr_id IS NULL AND used_status = 0 AND entry_flag = 0 ORDER BY material_id, stock_id";
        
        $get_sfg = $this->db->query($sql_sfg, [$project_id, $project_item_id])->result();
        $get_rm = $this->db->query($sql_rm, [$project_id, $project_item_id])->result();

        $param_docno = [
            "prefix" => "MR",
            "LPAD" => 4,
            "column" => "doc_no",
            "table" => "materialrequests"
        ];

        if ((sizeof($get_rm) && !empty($get_rm)) || (sizeof($get_sfg) && !empty($get_sfg))) {
            // create a material request document
            $header_data = [
                "doc_no" => $this->Db_model->genDocNo($param_docno),
                "mr_type" => 3,
                "project_name" => $project_name,
                "project_id" => $project_id,
                "mr_date" => date("Y-m-d"),
                "status_id" => 1,
                "created_by" => $this->login_user->id,
                "requester_id" => $this->login_user->id
            ];

            $this->db->insert("materialrequests", $header_data);
            $header_id = $this->db->insert_id();
            $mr_items_info = array();

            // sfg
            if (sizeof($get_sfg)) {
                foreach ($get_sfg as $item) {
                    // add some material items to material request document
                    $item_info = $this->dev2_getRowInfoByRowId($item->item_id, "items");
                    $item_data = [
                        "mr_id" => $header_id,
                        "project_id" => $project_id,
                        "project_item_id" => $item->project_item_id,
                        "project_name" => $project_name,
                        "code" => $item_info->item_code,
                        "title" => $item_info->title,
                        "description" => $item_info->description,
                        "quantity" => $item->ratio,
                        "unit_type" => $item_info->unit_type,
                        "item_type" => 'SFG',
                        "item_id" => $item->item_id,
                        "bpim_id" => $item->id,
                        "stock_id" => $item->stock_id,
                        "category_in_bom" => $item->category_in_bom
                    ];
                    
                    $this->db->insert("mr_items", $item_data);
                    $item_id = $this->db->insert_id();
                    $mr_items_info[$item_id] = $item_data;

                    // patch mr id to bom project item items
                    $this->dev2_patchMaterialRequestIdForBomItemByItemId($item->id, $header_id, "bom_project_item_items");
                }
            }

            // rm
            if (sizeof($get_rm)) {
                foreach ($get_rm as $item) {
                    // add some material items to material request document
                    $material_info = $this->dev2_getRowInfoByRowId($item->material_id, "bom_materials");
                    $item_data = [
                        "mr_id" => $header_id,
                        "project_id" => $project_id,
                        "project_item_id" => $item->project_item_id,
                        "project_name" => $project_name,
                        "code" => $material_info->name,
                        "title" => $material_info->production_name,
                        "description" => $material_info->description,
                        "quantity" => $item->ratio,
                        "unit_type" => $material_info->unit,
                        "item_type" => 'RM',
                        "material_id" => $item->material_id,
                        "bpim_id" => $item->id,
                        "stock_id" => $item->stock_id,
                        "category_in_bom" => $item->category_in_bom
                    ];
                    
                    $this->db->insert("mr_items", $item_data);
                    $item_id = $this->db->insert_id();
                    $mr_items_info[$item_id] = $item_data;

                    // patch mr id to bom project item material
                    $this->dev2_patchMaterialRequestIdForBomItemByItemId($item->id, $header_id, "bom_project_item_materials");
                }
            }

            // patch material request status for production order
            $this->dev2_patchProductionMaterialRequestStatus($project_item_id);

            // prepare info to return
            $info = [
                "mr_id" => $header_id,
                "mr_info" => $header_data,
                "mr_items_info" => $mr_items_info,
                "process" => "success",
                "success" => true,
                "target" => get_uri("materialrequests/view_group/" . $header_id)
            ];
        }

        return $info;
    }

    public function dev2_postProductionOrderDeleteByOrderId(int $project_id, int $production_id): array
    {
        $info = [
            "success" => false,
            "header" => null,
            "items" => null,
            "message" => null
        ];

        // get production order info
        $header_data = $this->dev2_getRowInfoByRowId($production_id, "bom_project_items");
        $rm_data = $this->dev2_getItemListByRowHeaderId($production_id, "bom_project_item_materials", "project_item_id");
        $sfg_data = $this->dev2_getItemListByRowHeaderId($production_id, "bom_project_item_items", "project_item_id");

        if (isset($header_data) && !empty($header_data)) {
            if (isset($rm_data) && !empty($rm_data)) {
                $this->db->delete("bom_project_item_materials", ["project_item_id" => $production_id]);
                $info["items"][] = $rm_data;
            }

            if (isset($sfg_data) && !empty($sfg_data)) {
                $this->db->delete("bom_project_item_items", ["project_item_id" => $production_id]);
                $info["items"][] = $sfg_data;
            }

            $this->db->delete("bom_project_items", ["id" => $production_id]);
            $info["header"] = $header_data;
            $info["success"] = true;
            $info["message"] = lang("production_order_delete_success");
        } else {
            $info["message"] = lang("production_order_delete_failure");
        }

        return $info;
    }

    public function dev2_getProductionOrderStatusById(int $id): int
    {
        $status = 0;
        $get = $this->dev2_getRowInfoByRowId($id, "bom_project_items");

        if (isset($get->produce_status) && !empty($get->produce_status)) {
            $status = $get->produce_status;
        }
        return $status;
    }

    public function dev2_getCountMrForProductionOrderById(int $id): int
    {
        $count = 0;
        $sql = "SELECT COUNT(id) AS rows_num FROM bom_project_item_materials WHERE mr_id IS NOT NULL AND project_item_id = ?";
        $get = $this->db->query($sql, $id)->row();

        if (isset($get->rows_num) && !empty($get->rows_num)) {
            $count = $get->rows_num;
        }
        return $count;
    }

    public function dev2_getCountNoMrForProductionOrderById(int $id): int
    {
        $count_rm = 0;
        $sql = "SELECT COUNT(id) AS rows_num FROM bom_project_item_materials WHERE mr_id IS NULL AND project_item_id = ?";
        $get = $this->db->query($sql, $id)->row();
        if (isset($get->rows_num) && !empty($get->rows_num)) $count_rm = $get->rows_num;

        $count_sfg = 0;
        $sql = "SELECT COUNT(id) AS rows_num FROM bom_project_item_items WHERE mr_id IS NULL AND project_item_id = ?";
        $get = $this->db->query($sql, $id)->row();
        if (isset($get->rows_num) && !empty($get->rows_num)) $count_sfg = $get->rows_num;
        
        return $count_rm + $count_sfg;
    }

    public function dev2_getCountNoStockForProductionOrderById(int $id): int
    {
        $count_rm = 0;
        $sql = "SELECT COUNT(id) AS rows_num FROM bom_project_item_materials WHERE stock_id IS NULL AND project_item_id = ?";
        $get = $this->db->query($sql, $id)->row();
        if (isset($get->rows_num) && !empty($get->rows_num))  $count_rm = $get->rows_num;

        $count_sfg = 0;
        $sql = "SELECT COUNT(id) AS rows_num FROM bom_project_item_items WHERE stock_id IS NULL AND project_item_id = ?";
        $get = $this->db->query($sql, $id)->row();
        if (isset($get->rows_num) && !empty($get->rows_num))  $count_sfg = $get->rows_num;

        return $count_rm + $count_sfg;
    }

    private function dev2_getMaterialCategoryTitleByCategoryId(int $id) : stdClass
    {
        $info = '';
        $query = $this->db->get_where("material_categories", ["id" => $id])->row();

        if ($query !== null) {
            $info = $query;
        }

        return $info;
    }

    private function dev2_getStockActualTotalRemainingBySfgId(int $id): float
    {
        $actual_remaining = 0;
        $temp_actual = 0;
        $temp_remaining = 0;
        $temp_awaiting = 0;

        // get beginning stock 1400
        $sqlBeginningString = "SELECT IFNULL(SUM(stock), 0) AS quantity FROM bom_item_stocks WHERE item_id = ? GROUP BY item_id";
        $beginningQuery = $this->db->query($sqlBeginningString, $id);
        $beginning = $beginningQuery->row();

        // get remainning stock 1400
        $sqlRemainingString = "SELECT IFNULL(SUM(remaining), 0) AS quantity FROM bom_item_stocks WHERE item_id = ? GROUP BY item_id";
        $remainingQuery = $this->db->query($sqlRemainingString, $id);
        $remaining = $remainingQuery->row();
        
        // get using stock 0
        $sqlUsingString = "SELECT IFNULL(SUM(ratio), 0) AS quantity FROM bom_project_item_items WHERE stock_id IS NOT NULL AND item_id = ? GROUP BY item_id";
        $usingQuery = $this->db->query($sqlUsingString, $id);
        $using = $usingQuery->row();

        // get awaiting approval stock 0
        $sqlAwaitingString = "SELECT IFNULL(SUM(ratio), 0) AS quantity FROM bom_project_item_items WHERE stock_id IS NOT NULL AND used_status = 0 AND item_id = ? GROUP BY item_id";
        $awaitingQuery = $this->db->query($sqlAwaitingString, $id);
        $awaiting = $awaitingQuery->row();

        // begin 1400 - using 0 = actual remain 1400
        if (isset($beginning->quantity) && !empty($beginning->quantity)) {
            $temp_actual = $beginning->quantity;

            if (isset($using->quantity) && !empty($using->quantity)) {
                $temp_actual = $temp_actual - $using->quantity;
            }
        }

        // current remain 1400
        if (isset($remaining->quantity) && !empty($remaining->quantity)) {
            $temp_remaining = $remaining->quantity;
        }

        // curren awaiting approval 0
        if (isset($awaiting->quantity) && !empty($awaiting->quantity)) {
            $temp_awaiting = $awaiting->quantity;
        }

        // minimun of remain 1400 - awaiting 0 = actually remaining 1400
        $actual_remaining = min($temp_remaining, $temp_actual) - $temp_awaiting;
        return (float) $actual_remaining;
    }

    private function dev2_getStockActualTotalRemainingByMaterialId(int $id): float
    {
        $actual_remaining = 0;
        $temp_actual = 0;
        $temp_remaining = 0;
        $temp_awaiting = 0;

        // get beginning stock 1400
        $sqlBeginningString = "SELECT IFNULL(SUM(stock), 0) AS quantity FROM bom_stocks WHERE material_id = ? GROUP BY material_id";
        $beginningQuery = $this->db->query($sqlBeginningString, $id);
        $beginning = $beginningQuery->row();

        // get remainning stock 1400
        $sqlRemainingString = "SELECT IFNULL(SUM(remaining), 0) AS quantity FROM bom_stocks WHERE material_id = ? GROUP BY material_id";
        $remainingQuery = $this->db->query($sqlRemainingString, $id);
        $remaining = $remainingQuery->row();
        
        // get using stock 0
        $sqlUsingString = "SELECT IFNULL(SUM(ratio), 0) AS quantity FROM bom_project_item_materials WHERE stock_id IS NOT NULL AND material_id = ? GROUP BY material_id";
        $usingQuery = $this->db->query($sqlUsingString, $id);
        $using = $usingQuery->row();

        // get awaiting approval stock 0
        $sqlAwaitingString = "SELECT IFNULL(SUM(ratio), 0) AS quantity FROM bom_project_item_materials WHERE stock_id IS NOT NULL AND used_status = 0 AND material_id = ? GROUP BY material_id";
        $awaitingQuery = $this->db->query($sqlAwaitingString, $id);
        $awaiting = $awaitingQuery->row();

        // begin 1400 - using 0 = actual remain 1400
        if (isset($beginning->quantity) && !empty($beginning->quantity)) {
            $temp_actual = $beginning->quantity;

            if (isset($using->quantity) && !empty($using->quantity)) {
                $temp_actual = $temp_actual - $using->quantity;
            }
        }

        // current remain 1400
        if (isset($remaining->quantity) && !empty($remaining->quantity)) {
            $temp_remaining = $remaining->quantity;
        }

        // curren awaiting approval 0
        if (isset($awaiting->quantity) && !empty($awaiting->quantity)) {
            $temp_awaiting = $awaiting->quantity;
        }

        // minimun of remain 1400 - awaiting 0 = actually remaining 1400
        $actual_remaining = min($temp_remaining, $temp_actual) - $temp_awaiting;
        return (float) $actual_remaining;
    }

    private function dev2_patchMaterialRequestIdForBomItemByItemId(int $id, int $mr_id, string $table_name) : void
    {
        $this->db->where("id", $id);
        $this->db->update($table_name, [
            "mr_id" => $mr_id
        ]);
    }

    public function dev2_patchProductionMaterialRequestStatus(int $production_id) : void
    {
        $data_status = $this->dev2_getProductionMaterialRequestStatusByProductionId($production_id);
        $mr_status = $data_status["production_mr_status"];

        $data_percentage = $this->dev2_getProductionMaterialRequestPercentageByProductionId($production_id);
        $mr_percentage = $data_percentage["production_mr_percentage"];

        $this->db->where("id", $production_id);
        $this->db->update("bom_project_items", [
            "mr_status" => $mr_status,
            "mr_percentage" => $mr_percentage
        ]);
    }

    public function dev2_getProductionMaterialRequestPercentageByProductionId(int $production_id) : array
    {
        $data = [
            "rm" => null,
            "sfg" => null,
            "total_items" => null,
            "mr_rm" => null,
            "mr_sfg" => null,
            "total_mr" => null,
            "production_mr_percentage" => 0
        ];

        // select count all items of production
        $rm = $this->db->where("project_item_id", $production_id)->from("bom_project_item_materials")->count_all_results();
        $sfg = $this->db->where("project_item_id", $production_id)->from("bom_project_item_items")->count_all_results();
        
        $data["rm"] = $rm;
        $data["sfg"] = $sfg;
        $data["total_items"] = $rm + $sfg;

        // select count the created mr items of production
        $mr_rm = $this->db->where("project_item_id", $production_id)->where("mr_id IS NOT NULL", null, false)->from("bom_project_item_materials")->count_all_results();
        $mr_sfg = $this->db->where("project_item_id", $production_id)->where("mr_id IS NOT NULL", null, false)->from("bom_project_item_items")->count_all_results();

        $data["mr_rm"] = $mr_rm;
        $data["mr_sfg"] = $mr_sfg;
        $data["total_mr"] = $mr_rm + $mr_sfg;

        if ($data["total_items"] > 0 && $data["total_mr"] > 0) {
            $data["production_mr_percentage"] = round(($data["total_mr"] / $data["total_items"]) * 100, 2);
        }

        return $data;
    }

    public function dev2_getProductionMaterialRequestStatusByProductionId(int $production_id) : array
    {
        $data = [
            "rm" => [],
            "sfg" => [],
            "mr_id" => [],
            "mr_unique" => [],
            "mr_unique_size" => 0,
            "find_null" => null,
            "mr_null" => 'N',
            "mr_where" => null,
            "mr_where_status" => null,
            "status" => [],
            "find_awaiting" => null,
            "production_mr_status" => 0
        ];
        
        // Get all sfg items
        $get_sfg = $this->db->get_where("bom_project_item_items", ["project_item_id" => $production_id])->result();
        if (sizeof($get_sfg)) {
            $data["sfg"] = $get_sfg;

            foreach ($get_sfg as $item) {
                $data["mr_id"][] = $item->mr_id;
            }
        }

        // Get all sfg items
        $get_rm = $this->db->get_where("bom_project_item_materials", ["project_item_id" => $production_id])->result();
        if (sizeof($get_rm)) {
            $data["rm"] = $get_rm;

            foreach ($get_rm as $item) {
                $data["mr_id"][] = $item->mr_id;
            }
        }

        if (sizeof($data["mr_id"])) {
            $data["mr_unique"] = array_unique($data["mr_id"]);
        }

        $data["mr_unique_size"] = sizeof($data["mr_unique"]);

        if ($data["mr_unique_size"] === 1) {
            // Size of unique material request equals to 1
            $data["find_null"] = array_search('', $data["mr_unique"]);

            if ($data["find_null"] === 0) {
                // Case null in array index 0 : have no material request
                $data["production_mr_status"] = 1;
            } else {
                // Case not null in array index 0 : have only 1 material request in this production
                $data["mr_where"] = implode(",", $data["mr_unique"]);

                $query = $this->db->get_where("materialrequests", ["id" => $data["mr_where"]])->row();
                if (!empty($query)) {
                    $data["mr_where_status"] = $query->status_id;

                    // Material request status is [Pending] :: Production [mr_status] is [Created withdraw]
                    if ($data["mr_where_status"] == 1) {
                        $data["production_mr_status"] = 4;
                    }

                    // Material request status is [Approved] :: Production [mr_status] is [Completed withdrawal]
                    if ($data["mr_where_status"] == 3) {
                        $data["production_mr_status"] = 3;
                    }
                }
            }
        } else {
            // Size of unique material request not equals to 1
            $data["find_null"] = array_search('', $data["mr_unique"]);

            if ($data["find_null"] == null) {
                // Case have no null any index in array : Let get material request list
                $data["mr_where"] = implode(",", $data["mr_unique"]);

                $sql = "SELECT `status_id` FROM `materialrequests` WHERE `id` IN (" . $data["mr_where"] . ")";
                $query = $this->db->query($sql)->result();

                if (sizeof($query)) {
                    foreach ($query as $item) {
                        $data["status"][] = $item->status_id;
                    }

                    $data["find_awaiting"] = array_search(1, $data["status"]);
                    if ($data["find_awaiting"] == null) {
                        // Case have no awaiting material request : Production [mr_status] is [Completed withdrawal]
                        $data["production_mr_status"] = 3;
                    } else {
                        // Case have awaiting material request : Production [mr_status] is [Created withdraw]
                        $data["production_mr_status"] = 4;
                    }
                }
            } else {
                // Case have null any index in array : Production [mr_status] is [Partially withdrawn]
                $data["production_mr_status"] = 2;
            }
        }

        return $data;
    }

    private function dev2_getStockGroupByProjectId(int $project_id): stdClass
    {
        $info = new stdClass();
        $get = $this->db->get_where("bom_item_groups", ["project_id" => $project_id])->row();

        if (!empty($get)) {
            $info = $get;
        }
        return $info;
    }

    private function dev2_getRowInfoByRowId(int $id, string $table): stdClass
    {
        $info = new stdClass();
        $get = $this->db->get_where($table, ["id" => $id])->row();

        if (!empty($get)) {
            $info = $get;
        }
        return $info;
    }

    private function dev2_getItemListByRowHeaderId(int $id, string $table, string $column): array
    {
        $list = array();
        $get = $this->db->get_where($table, [$column => $id])->result();

        if (sizeof($get) && !empty($get)) {
            $list = $get;
        }
        return $list;
    }

    private function dev2_getListByTableName(string $table): array
    {
        $list = array();
        $get = $this->db->get($table)->result();

        if (sizeof($get) || !empty($get)) {
            $list = $get;
        }
        return $list;
    }

}
