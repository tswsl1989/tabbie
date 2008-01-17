<?php

class Home extends Controller {

    function Home() {
        parent::Controller();	
        $this->load->helper('url');
    }
    
    function index() {
        $this->load->view('home/home');
    }
}
?>