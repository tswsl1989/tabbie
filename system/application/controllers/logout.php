<?php

class Logout extends Controller {

    function Logout() {
        parent::Controller();	
    }
    
    function index() {
        $this->session->set_userdata('username', '');
        $this->session->sess_destroy();
        $this->load->view('home/home');
    }

}
?>