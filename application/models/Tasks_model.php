<?php

class Tasks_model extends Crud_model {

    private $table = null;

    function __construct() {
        $this->table = 'tasks';
        parent::__construct($this->table);
        parent::init_activity_log("task", "title", "project", "project_id");
    }

    function schema() {
        return array(
            "id" => array(
                "label" => lang("id"),
                "type" => "int"
            ),
            "title" => array(
                "label" => lang("title"),
                "type" => "text"
            ),
            "description" => array(
                "label" => lang("description"),
                "type" => "text"
            ),
            "assigned_to" => array(
                "label" => lang("assigned_to"),
                "type" => "foreign_key",
                "linked_model" => $this->Users_model,
                "label_fields" => array("first_name", "last_name"),
            ),
            "collaborators" => array(
                "label" => lang("collaborators"),
                "type" => "foreign_key",
                "link_type" => "user_group_list",
                "linked_model" => $this->Users_model,
                "label_fields" => array("user_group_name"),
            ),
            "milestone_id" => array(
                "label" => lang("milestone"),
                "type" => "foreign_key",
                "linked_model" => $this->Milestones_model,
                "label_fields" => array("title"),
            ),
            "labels" => array(
                "label" => lang("labels"),
                "type" => "foreign_key",
                "link_type" => "label_group_list",
                "linked_model" => $this->Labels_model,
                "label_fields" => array("label_group_name"),
            ),
            "status" => array(
                "label" => lang("status"),
                "type" => "language_key" //we'are not using this field from 1.9 but don't delete it for existing data.
            ),
            "status_id" => array(
                "label" => lang("status"),
                "type" => "foreign_key",
                "linked_model" => $this->Task_status_model,
                "label_fields" => array("title"),
            ),
            "start_date" => array(
                "label" => lang("start_date"),
                "type" => "date"
            ),
            "deadline" => array(
                "label" => lang("deadline"),
                "type" => "date"
            ),
            "project_id" => array(
                "label" => lang("project"),
                "type" => "foreign_key"
            ),
            "points" => array(
                "label" => lang("points"),
                "type" => "int"
            ),
            "deleted" => array(
                "label" => lang("deleted"),
                "type" => "int"
            ),
            "sort" => array(
                "label" => lang("priority"),
                "type" => "int"
            ),
            "ticket_id" => array(
                "label" => lang("ticket"),
                "type" => "foreign_key",
                "linked_model" => $this->Tickets_model,
                "label_fields" => array("title"),
            ),
            "no_of_cycles" => array(
                "label" => lang("cycles"),
                "type" => "int"
            ),
            "recurring" => array(
                "label" => lang("recurring"),
                "type" => "int"
            ),
            "repeat_type" => array(
                "label" => lang("repeat_type"),
                "type" => "text"
            ),
            "repeat_every" => array(
                "label" => lang("repeat_every"),
                "type" => "int"
            ),
        );
    }

    function get_details($options = array()) {
        $tasks_table = $this->db->dbprefix('tasks');
        $users_table = $this->db->dbprefix('users');
        $projects = $this->db->dbprefix('projects');
        $milestones_table = $this->db->dbprefix('milestones');
        $project_members_table = $this->db->dbprefix('project_members');
        $task_status_table = $this->db->dbprefix('task_status');
        $ticket_table = $this->db->dbprefix('tickets');

        $where = "";

        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND $tasks_table.id=$id";
        }

        if($this->Permission_m->access_project == "assigned_only"){
            $where .= " AND $projects.created_by=".$this->login_user->id;
        }elseif($this->Permission_m->access_project == "specific"){
            $where .= " AND (FIND_IN_SET($projects.project_type_id, '".$this->Permission_m->access_project_specific."') OR $projects.project_type_id IS NULL)";
        }

        $project_id = get_array_value($options, "project_id");
        if ($project_id) {
            $where .= " AND $tasks_table.project_id=$project_id AND $tasks_table.parent_task_id='0' ";
        }

        $client_id = get_array_value($options, "client_id");
        if ($client_id) {
            $where .= " AND $projects.client_id=$client_id";
        }

        $parent_task_id = get_array_value($options, "parent_task_id");
        if ($parent_task_id) {
            $where .= " AND $tasks_table.parent_task_id=$parent_task_id";
        }

        $exclude_task_ids = get_array_value($options, "exclude_task_ids");
        if ($exclude_task_ids) {
            $where .= " AND $tasks_table.id NOT IN($exclude_task_ids)";
        }

        $status_ids = get_array_value($options, "status_ids");
        if ($status_ids) {
            $where .= " AND FIND_IN_SET($tasks_table.status_id,'$status_ids')";
        }

        $task_ids = get_array_value($options, "task_ids");
        if ($task_ids) {
            $where .= " AND $tasks_table.ID IN($task_ids)";
        }

        $exclude_status_id = get_array_value($options, "exclude_status_id");
        if ($exclude_status_id) {
            $where .= " AND $tasks_table.status_id!=$exclude_status_id ";
        }

        $assigned_to = get_array_value($options, "assigned_to");
        if ($assigned_to) {
            $where .= " AND $tasks_table.assigned_to=$assigned_to";
        }

        $specific_user_id = get_array_value($options, "specific_user_id");
        if ($specific_user_id) {
            $where .= " AND ($tasks_table.assigned_to=$specific_user_id OR FIND_IN_SET('$specific_user_id', $tasks_table.collaborators))";
        }

        $show_assigned_tasks_only_user_id = get_array_value($options, "show_assigned_tasks_only_user_id");
        if ($show_assigned_tasks_only_user_id) {
            $where .= " AND ($tasks_table.assigned_to=$show_assigned_tasks_only_user_id OR FIND_IN_SET('$show_assigned_tasks_only_user_id', $tasks_table.collaborators))";
        }

        $project_status = get_array_value($options, "project_status");
        if ($project_status) {
            $where .= " AND FIND_IN_SET($projects.status,'$project_status')";
        }

        $milestone_id = get_array_value($options, "milestone_id");
        if ($milestone_id) {
            $where .= " AND $tasks_table.milestone_id=$milestone_id";
        }

        $task_status_id = get_array_value($options, "task_status_id");
        if ($task_status_id) {
            $where .= " AND $tasks_table.status_id=$task_status_id";
        }

        $start_date = get_array_value($options, "start_date");
        $deadline = get_array_value($options, "deadline");
        if ($start_date && $deadline) {
            $for_events = get_array_value($options, "for_events");
            if ($for_events) {
                $deadline_for_events = get_array_value($options, "deadline_for_events");
                $start_date_for_events = get_array_value($options, "start_date_for_events");
                if ($deadline_for_events) {
                    $where .= " AND (($tasks_table.deadline IS NOT NULL AND $tasks_table.deadline BETWEEN '$start_date' AND '$deadline') OR $milestones_table.due_date BETWEEN '$start_date' AND '$deadline')";
                } else if ($start_date_for_events) {
                    $where .= " AND ($tasks_table.start_date IS NOT NULL AND $tasks_table.start_date BETWEEN '$start_date' AND '$deadline')";
                }
            } else {
                $where .= " AND ($tasks_table.deadline BETWEEN '$start_date' AND '$deadline') ";
            }
        } else if ($deadline) {
            $now = get_my_local_time("Y-m-d");
            if ($deadline === "expired") {
                $where .= " AND (($tasks_table.deadline IS NOT NULL AND $tasks_table.deadline<'$now')  OR $milestones_table.due_date<'$now')";
            } else {
                $where .= " AND (($tasks_table.deadline IS NOT NULL AND $tasks_table.deadline<='$deadline') OR $milestones_table.due_date<='$deadline')";
            }
        }

        $exclude_reminder_date = get_array_value($options, "exclude_reminder_date");
        if ($exclude_reminder_date) {
            $where .= " AND ($tasks_table.reminder_date !='$exclude_reminder_date') ";
        }

        $ticket_id = get_array_value($options, "ticket_id");
        if ($ticket_id) {
            $where .= " AND $ticket_table.ticket_id=$ticket_id";
        }

        $sort = "";
        $sort_by_project = get_array_value($options, "sort_by_project");
        if ($sort_by_project) {
            $sort = " ORDER BY $tasks_table.project_id ASC";
        }

        $extra_left_join = "";
        $project_member_id = get_array_value($options, "project_member_id");
        if ($project_member_id) {
            $where .= " AND $project_members_table.user_id=$project_member_id";
            $extra_left_join = " LEFT JOIN $project_members_table ON $tasks_table.project_id= $project_members_table.project_id AND $project_members_table.deleted=0 AND $project_members_table.user_id=$project_member_id";
        }

        $quick_filter = get_array_value($options, "quick_filter");
        if ($quick_filter) {
            $where .= $this->make_quick_filter_query($quick_filter, $tasks_table);
        }

        $extra_select = ", 0 AS unread";
        $this->_set_unread_tasks_query($options, $tasks_table, $extra_select);

        $select_labels_data_query = $this->get_labels_data_query();

        //prepare custom fild binding query
        $custom_fields = get_array_value($options, "custom_fields");
        $custom_field_query_info = $this->prepare_custom_field_query_string("tasks", $custom_fields, $tasks_table);
        $select_custom_fieds = get_array_value($custom_field_query_info, "select_string");
        $join_custom_fieds = get_array_value($custom_field_query_info, "join_string");

        $this->db->query('SET SQL_BIG_SELECTS=1');

        $sql = "SELECT $tasks_table.*, $task_status_table.key_name AS status_key_name, $task_status_table.title AS status_title,  $task_status_table.color AS status_color, CONCAT($users_table.first_name, ' ',$users_table.last_name) AS assigned_to_user, $users_table.image as assigned_to_avatar, $users_table.user_type,
                    $projects.title AS project_title, $milestones_table.title AS milestone_title, IF($tasks_table.deadline IS NULL, $milestones_table.due_date,$tasks_table.deadline) AS deadline,$ticket_table.title AS ticket_title,
                    (SELECT GROUP_CONCAT($users_table.id, '--::--', $users_table.first_name, ' ', $users_table.last_name, '--::--' , IFNULL($users_table.image,''), '--::--', $users_table.user_type) FROM $users_table WHERE $users_table.deleted=0 AND FIND_IN_SET($users_table.id, $tasks_table.collaborators)) AS collaborator_list,
                    IF($tasks_table.deadline IS NULL, $milestones_table.title, '') AS deadline_milestone_title, $select_labels_data_query $select_custom_fieds 
                    $extra_select    
        FROM $tasks_table
        LEFT JOIN $users_table ON $users_table.id= $tasks_table.assigned_to
        LEFT JOIN $projects ON $tasks_table.project_id=$projects.id 
        LEFT JOIN $milestones_table ON $tasks_table.milestone_id=$milestones_table.id 
        LEFT JOIN $task_status_table ON $tasks_table.status_id = $task_status_table.id 
        LEFT JOIN $ticket_table ON $tasks_table.ticket_id = $ticket_table.id
        $extra_left_join
        $join_custom_fieds    
        WHERE $tasks_table.deleted=0 $where $sort";
        // arr($sql);exit;

        return $this->db->query($sql);
    }

    private function make_quick_filter_query($filter, $tasks_table) {
        $project_comments_table = $this->db->dbprefix("project_comments");
        $query = "";

        if ($filter == "recently_meaning") {
            return $query;
        }

        $login_user_id = 0;
        if (isset($this->login_user->id)) {
            $login_user_id = $this->login_user->id;
        }

        $recently_updated_last_time = prepare_last_recently_date_time($login_user_id);

        $project_comments_table_query = "SELECT $project_comments_table.task_id 
                           FROM $project_comments_table 
                           WHERE $project_comments_table.deleted=0 AND $project_comments_table.task_id!=0";
        $project_comments_table_group_by = "GROUP BY $project_comments_table.task_id";

        if ($filter === "recently_updated") {
            $query = " AND ($tasks_table.status_changed_at IS NOT NULL AND $tasks_table.status_changed_at>='$recently_updated_last_time')";
        } else if ($filter === "recently_commented") {
            $query = " AND $tasks_table.id IN($project_comments_table_query AND $project_comments_table.created_at>='$recently_updated_last_time' $project_comments_table_group_by)";
        } else if ($filter === "mentioned_me" && isset($this->login_user->id)) {
            $mention_string = ":" . $this->login_user->id . "]";
            $query = " AND $tasks_table.id IN($project_comments_table_query AND $project_comments_table.description LIKE '%$mention_string%' $project_comments_table_group_by)";
        } else if ($filter === "recently_mentioned_me" && isset($this->login_user->id)) {
            $mention_string = ":" . $this->login_user->id . "]";
            $query = " AND $tasks_table.id IN($project_comments_table_query AND $project_comments_table.description LIKE '%$mention_string%' AND $project_comments_table.created_at>='$recently_updated_last_time' $project_comments_table_group_by)";
        } else {
            $query = " AND ($tasks_table.status_changed_at IS NOT NULL AND $tasks_table.status_changed_at>='$recently_updated_last_time' AND $tasks_table.status_id=$filter)";
        }

        return $query;
    }

    function get_kanban_details($options = array()) {
        $tasks_table = $this->db->dbprefix('tasks');
        $users_table = $this->db->dbprefix('users');
        $projects = $this->db->dbprefix('projects');
        $milestones_table = $this->db->dbprefix('milestones');
        $labels_table = $this->db->dbprefix('labels');
        $project_members_table = $this->db->dbprefix('project_members');

        $where = "";

        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND $tasks_table.id=$id";
        }

        $project_id = get_array_value($options, "project_id");
        if ($project_id) {
            $where .= " AND $tasks_table.project_id=$project_id";
        }

        $client_id = get_array_value($options, "client_id");
        if ($client_id) {
            $where .= " AND $projects.client_id=$client_id";
        }


        $status = get_array_value($options, "status");
        if ($status) {
            $where .= " AND FIND_IN_SET($tasks_table.status,'$status')";
        }

        $project_status = get_array_value($options, "project_status");
        if ($project_status) {
            $where .= " AND FIND_IN_SET($projects.status,'$project_status')";
        }

        $assigned_to = get_array_value($options, "assigned_to");
        if ($assigned_to) {
            $where .= " AND $tasks_table.assigned_to=$assigned_to";
        }

        $specific_user_id = get_array_value($options, "specific_user_id");
        if ($specific_user_id) {
            $where .= " AND ($tasks_table.assigned_to=$specific_user_id OR FIND_IN_SET('$specific_user_id', $tasks_table.collaborators))";
        }

        $show_assigned_tasks_only_user_id = get_array_value($options, "show_assigned_tasks_only_user_id");
        if ($show_assigned_tasks_only_user_id) {
            $where .= " AND ($tasks_table.assigned_to=$show_assigned_tasks_only_user_id OR FIND_IN_SET('$show_assigned_tasks_only_user_id', $tasks_table.collaborators))";
        }

        $milestone_id = get_array_value($options, "milestone_id");
        if ($milestone_id) {
            $where .= " AND $tasks_table.milestone_id=$milestone_id";
        }

        $deadline = get_array_value($options, "deadline");
        if ($deadline) {
            $now = get_my_local_time("Y-m-d");
            if ($deadline === "expired") {
                $where .= " AND (($tasks_table.deadline IS NOT NULL AND $tasks_table.deadline<'$now')  OR $milestones_table.due_date<'$now')";
            } else {
                $where .= " AND (($tasks_table.deadline IS NOT NULL AND $tasks_table.deadline<='$deadline') OR $milestones_table.due_date<='$deadline')";
            }
        }

        $search = get_array_value($options, "search");
        if ($search) {
            $search = $this->db->escape_str($search);
            $where .= " AND ($tasks_table.title LIKE '%$search%' OR FIND_IN_SET((SELECT $labels_table.id FROM $labels_table WHERE $labels_table.deleted=0 AND $labels_table.context='task' AND $labels_table.title LIKE '%$search%' LIMIT 1), $tasks_table.labels) OR $tasks_table.id='$search')";
        }


        $extra_left_join = "";
        $project_member_id = get_array_value($options, "project_member_id");
        if ($project_member_id) {
            $where .= " AND $project_members_table.user_id=$project_member_id";
            $extra_left_join = " LEFT JOIN $project_members_table ON $tasks_table.project_id= $project_members_table.project_id AND $project_members_table.deleted=0 AND $project_members_table.user_id=$project_member_id";
        }

        $quick_filter = get_array_value($options, "quick_filter");
        if ($quick_filter) {
            $where .= $this->make_quick_filter_query($quick_filter, $tasks_table);
        }


        $extra_select = ", 0 AS unread";
        $this->_set_unread_tasks_query($options, $tasks_table, $extra_select);

        $select_labels_data_query = $this->get_labels_data_query();

        $sql = "SELECT $tasks_table.id, $tasks_table.title, $tasks_table.sort, IF($tasks_table.sort!=0, $tasks_table.sort, $tasks_table.id) AS new_sort, $tasks_table.assigned_to, $tasks_table.labels, $tasks_table.status_id, $tasks_table.project_id, CONCAT($users_table.first_name, ' ',$users_table.last_name) AS assigned_to_user, $users_table.image as assigned_to_avatar, $tasks_table.parent_task_id, $select_labels_data_query $extra_select
        FROM $tasks_table
        LEFT JOIN $users_table ON $users_table.id= $tasks_table.assigned_to
        LEFT JOIN $projects ON $tasks_table.project_id=$projects.id 
        LEFT JOIN $milestones_table ON $tasks_table.milestone_id=$milestones_table.id 
        $extra_left_join    
        WHERE $tasks_table.deleted=0 $where 
        ORDER BY new_sort ASC";

        return $this->db->query($sql);
    }

    function _set_unread_tasks_query($options, $tasks_table, &$extra_select) {

        $notifications_table = $this->db->dbprefix("notifications");

        $unread_status_user_id = get_array_value($options, "unread_status_user_id");
        if ($unread_status_user_id) {
            $extra_select = ", (SELECT COUNT($notifications_table.task_id) FROM $notifications_table WHERE $notifications_table.deleted=0 AND $notifications_table.event='project_task_commented' AND $notifications_table.task_id=$tasks_table.id AND !FIND_IN_SET('$unread_status_user_id', $notifications_table.read_by) AND $notifications_table.user_id!=$unread_status_user_id) AS unread";
        }
    }

    function count_my_open_tasks($user_id) {
        $db = $this->db;

        $q = $db->select("COUNT(tasks.id) AS TOTAL_TASKS")
                ->from("projects")
                ->join("tasks", "tasks.project_id = projects.id")
                ->join("project_members", "project_members.project_id = tasks.project_id")
                ->where("projects.deleted", 0)
                ->where("tasks.deleted", 0)
                ->where("project_members.deleted", 0)
                ->where("projects.status", "open")
                ->where_in("tasks.status_id", [1,2]);
                
        if($this->Permission_m->access_project == "assigned_only"){
            $q->where("projects.created_by", $this->login_user->id);
        }elseif($this->Permission_m->access_project == "specific"){
            $q->where_in("(projects.project_type_id", explode(",", $this->Permission_m->access_project_specific));
            $q->or_where("projects.project_type_id IS NULL)");
        }

        $q->where("project_members.user_id", $this->login_user->id);
        $q->where_in("(tasks.collaborators", [$this->login_user->id]);
        $q->or_where("tasks.assigned_to = '".$this->login_user->id."')");

        $total_tasks = $q->get()->row()->TOTAL_TASKS;

        if($total_tasks == null) return 0;
        return $total_tasks;
    }

    function get_label_suggestions($project_id) {
        $tasks_table = $this->db->dbprefix('tasks');
        $sql = "SELECT GROUP_CONCAT(labels) as label_groups
        FROM $tasks_table
        WHERE $tasks_table.deleted=0 AND $tasks_table.project_id=$project_id";
        return $this->db->query($sql)->row()->label_groups;
    }

    function get_my_projects_dropdown_list($user_id = 0) {
        $project_members_table = $this->db->dbprefix('project_members');
        $projects_table = $this->db->dbprefix('projects');

        $where = " AND $project_members_table.user_id=$user_id";

        $sql = "SELECT $project_members_table.project_id, $projects_table.title AS project_title
        FROM $project_members_table
        LEFT JOIN $projects_table ON $projects_table.id= $project_members_table.project_id
        WHERE $project_members_table.deleted=0 AND $projects_table.deleted=0 $where 
        GROUP BY $project_members_table.project_id";
        return $this->db->query($sql);
    }

    function get_projects_dropdown_list($user_id = 0) {
        $project_members_table = $this->db->dbprefix('project_members');
        $projects_table = $this->db->dbprefix('projects');
        $tasks_table = $this->db->dbprefix('tasks');

        $where = " AND $project_members_table.user_id=$user_id AND $projects_table.status='open' AND ($tasks_table.status_id IN (1, 2)) AND (assigned_to='".$this->login_user->id."' OR FIND_IN_SET('".$this->login_user->id."', collaborators)) AND $tasks_table.deleted=0";

        if($this->Permission_m->access_project == "assigned_only"){
            $where .= " AND $projects_table.created_by=".$this->login_user->id;
        }elseif($this->Permission_m->access_project == "specific"){
            $where .= " AND (FIND_IN_SET($projects_table.project_type_id, '".$this->Permission_m->access_project_specific."') OR $projects_table.project_type_id IS NULL)";
        }

        $sql = "SELECT $project_members_table.project_id, $projects_table.title AS project_title
        FROM $project_members_table
        LEFT JOIN $projects_table ON $projects_table.id = $project_members_table.project_id
        LEFT JOIN $tasks_table ON $tasks_table.project_id = $projects_table.id
        WHERE $project_members_table.deleted=0 AND $projects_table.deleted=0 $where 
        GROUP BY $project_members_table.project_id";
        return $this->db->query($sql);
    }

    function get_task_statistics($options = array()) {
        $tasks_table = $this->db->dbprefix('tasks');
        $task_status_table = $this->db->dbprefix('task_status');

        try {
            $this->db->query("SET sql_mode = ''");
        } catch (Exception $e) {
            
        }
        $where = "";

        $project_id = get_array_value($options, "project_id");
        if ($project_id) {
            $where .= " AND $tasks_table.project_id=$project_id";
        }

        $user_id = get_array_value($options, "user_id");
        if ($user_id) {
            $where .= " AND $tasks_table.assigned_to=$user_id";
        }

        $sql = "SELECT COUNT($tasks_table.id) AS total, $tasks_table.status_id, $task_status_table.key_name, $task_status_table.title, $task_status_table.color
        FROM $tasks_table
        LEFT JOIN $task_status_table ON $task_status_table.id = $tasks_table.status_id
        WHERE $tasks_table.deleted=0 $where
        GROUP BY $tasks_table.status_id";
        return $this->db->query($sql)->result();
    }

    function set_task_comments_as_read($task_id, $user_id = 0) {
        $notifications_table = $this->db->dbprefix('notifications');

        $sql = "UPDATE $notifications_table SET $notifications_table.read_by = CONCAT($notifications_table.read_by,',',$user_id)
        WHERE $notifications_table.task_id=$task_id AND FIND_IN_SET($user_id, $notifications_table.read_by) = 0 AND $notifications_table.event='project_task_commented'";
        return $this->db->query($sql);
    }

    function save_reminder_date(&$data = array(), $id = 0) {
        if ($id) {
            $where = array("id" => $id);
            $this->update_where($data, $where);
        }
    }

    //get the recurring tasks which are ready to renew as on a given date
    function get_renewable_tasks($date) {
        $tasks_table = $this->db->dbprefix('tasks');

        $sql = "SELECT * FROM $tasks_table
                        WHERE $tasks_table.deleted=0 AND $tasks_table.recurring=1
                        AND $tasks_table.next_recurring_date IS NOT NULL AND $tasks_table.next_recurring_date='$date'
                        AND ($tasks_table.no_of_cycles < 1 OR ($tasks_table.no_of_cycles_completed < $tasks_table.no_of_cycles ))";

        return $this->db->query($sql);
    }

    function get_all_dependency_for_this_task($task_id, $type) {
        $tasks_table = $this->db->dbprefix('tasks');

        $where = "";
        if ($type == "blocked_by") {
            $where = "AND $tasks_table.blocking LIKE '%$task_id%'";
        } else {
            $where = "AND $tasks_table.blocked_by LIKE '%$task_id%'";
        }

        $sql = "SELECT GROUP_CONCAT($tasks_table.id) AS dependency_task_ids FROM $tasks_table WHERE $tasks_table.deleted=0 AND $tasks_table.id!=$task_id $where";

        return $this->db->query($sql)->row()->dependency_task_ids;
    }

    function update_custom_data(&$data = array(), $id = 0) {
        if ($id) {
            $where = array("id" => $id);
            $this->update_where($data, $where);

            return $id;
        }
    }

    function get_search_suggestion($search = "", $options = array()) {
        $tasks_table = $this->db->dbprefix('tasks');

        $where = "";

        $show_assigned_tasks_only_user_id = get_array_value($options, "show_assigned_tasks_only_user_id");
        if ($show_assigned_tasks_only_user_id) {
            $where .= " AND ($tasks_table.assigned_to=$show_assigned_tasks_only_user_id OR FIND_IN_SET('$show_assigned_tasks_only_user_id', $tasks_table.collaborators))";
        }

        $search = $this->db->escape_str($search);

        $sql = "SELECT $tasks_table.id, $tasks_table.title
        FROM $tasks_table  
        WHERE $tasks_table.deleted=0 AND ($tasks_table.title LIKE '%$search%' OR $tasks_table.id LIKE '%$search%') $where
        ORDER BY $tasks_table.title ASC
        LIMIT 0, 10";

        return $this->db->query($sql);
    }

    function get_all_tasks_where_have_dependency($project_id) {
        $tasks_table = $this->db->dbprefix('tasks');

        $sql = "SELECT $tasks_table.id, $tasks_table.blocked_by, $tasks_table.blocking
        FROM $tasks_table  
        WHERE $tasks_table.deleted=0 AND $tasks_table.project_id=$project_id AND ($tasks_table.blocked_by!='' OR $tasks_table.blocking!='')";

        return $this->db->query($sql);
    }

    function save_gantt_task_date($data, $task_id) {
        parent::disable_log_activity();
        return $this->save($data, $task_id);
    }

}
