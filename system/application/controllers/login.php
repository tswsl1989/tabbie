<?php

class Login extends Controller {

    function Login() {
        parent::Controller();	
    }
    
    function index() {
        $this->load->view('login/login');
    }

    function go() {
        if ($this->input->post('username') == 'admin') {
            $this->session->set_userdata('username', 'admin');
            redirect('home');
        } else {
            $this->load->view('login/login');
        }
    }

}
?>