<?php

class Tournament extends Controller {

    function Tournament() {
        parent::Controller();	
    }
    
    function index() {
        //$this->load->view('home/home');
    }

    function create() {
        $this->load->helper('form');
        $this->load->library('validation');

        $rules['short_name'] = "required|alpha_dash|maximum_length[20]|short_name|callback_shortname_check";
        $rules['name'] = "required|maximum_length[100]";
        $this->validation->set_rules($rules);

        $fields['short_name']	= 'Identifier';
        $fields['name']	= 'Name';
        $this->validation->set_fields($fields);

        if ($this->validation->run() == FALSE) {
            $this->load->view('tournament/create');
        } else {
            //create tournament
            $this->load->view('tournament/show');
        }
    }

    function shortname_check($username) {
        $this->validation->set_message('shortname_check', 'This identifier is already in use.');
        return false;
        //return ($username == $this->session->userdata('username'));
    }


}
?>