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

        $rules['short_name'] = "required|alpha_dash|maximum_length[20]|callback__shortname_check";
        $rules['name'] = "required|maximum_length[100]";
        $this->validation->set_rules($rules);

        $fields['short_name'] = 'Identifier';
        $fields['name']	= 'Name';
        $this->validation->set_fields($fields);

        if ($this->validation->run() == FALSE) {
            $this->load->view('tournament/create');
        } else {
            $short_name = $this->input->post('short_name');
            $this->_create($short_name);
            redirect("tournament/admin/$short_name");
        }
    }

    function admin($short_name) {
        $this->db->from('tournaments')->where('short_name', $short_name);
        
        $data["tournament"] = $this->db->get()->row();
        if ($data["tournament"])
            $this->load->view('tournament/admin', $data);
        else
            redirect('home/home');
    }

    function _create($short_name) {
        $data = array(
            'short_name' => $short_name,
            'name' => $this->input->post('name'),
        );
        $this->db->insert('tournaments', $data); 
    }

    function _shortname_check($short_name) {
        $this->validation->set_message('_shortname_check', 'This identifier is already used.');
        $this->db->from('tournaments')->where('short_name', $short_name);
        return $this->db->get()->num_rows() == 0;
    }

}
?>