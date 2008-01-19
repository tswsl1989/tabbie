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

        $rules['short_name'] = "required|alpha_dash|maximum_length[20]|short_name|callback__shortname_check";
        $rules['name'] = "required|maximum_length[100]";
        $this->validation->set_rules($rules);

        $fields['short_name'] = 'Identifier';
        $fields['name']	= 'Name';
        $this->validation->set_fields($fields);

        if ($this->validation->run() == FALSE) {
            $this->load->view('tournament/create');
        } else {
            $this->_create();
            redirect('tournament/show');
        }
    }

    function show() {
        $this->load->view('tournament/show');
    }

    function _create() {
        $data = array(
            'short_name' => $this->input->post('short_name'),
            'name' => $this->input->post('name'),
        );
        $this->db->insert('tournaments', $data); 
    }

    function _shortname_check($username) {
        $this->validation->set_message('shortname_check', 'This identifier is already used.');
        $this->db->from('tournaments')->where('short_name', $this->input->post('short_name'));
        return $this->db->get()->num_rows() == 0;
    }

}
?>