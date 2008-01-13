<?php

class Database extends Controller {

    function Database() {
        parent::Controller();	
    }
    
    function addTournament() {

        print $this->post->asdf;
        $this->load->view('home/home');
    }
}
?>