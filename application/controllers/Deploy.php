<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Deploy extends CI_Controller {

    function __construct() {
        parent::__construct();
    }

    function pull() {
        $branch = $this->input->get("branch");
        if($branch != "stage" && $branch != "devteam" && $branch != "test") return;

        if($branch == "devteam"){
            echo shell_exec("git reset --hard HEAD");
            echo shell_exec("git pull git@github.com:cosmatch/cmerp.git devteam");
        }

        if($branch == "stage"){
            echo shell_exec("git reset --hard HEAD");
            echo shell_exec("git pull git@github.com:cosmatch/cmerp.git stage");
        }

        if($branch == "test"){
            echo "Hello, I'm Ready";
        }
    }

}