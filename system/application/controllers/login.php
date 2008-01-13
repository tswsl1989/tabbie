<?php

class Login extends Controller {

    function Login() {
        parent::Controller();	
    }
    
    function index() {
        $this->load->view('login/login');
    }

    function go() {
        if ($this->input->post("username") == "klaas") {
            $this->load->view('home/home');
        } else {
            $this->load->view('login/login');
        }
    }

}
?>