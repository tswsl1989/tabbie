<?php

class Tournament extends Controller {

    function Tournament() {
        parent::Controller();	
    }
    
    function index() {
        $data['tournaments'] = $this->_get_all_tournaments();
        $this->load->view('tournament/index', $data);
    }

    function add() {
        $this->load->helper('form');
        $this->load->library('validation');

        $rules['short_name'] = "required|alpha_dash|maximum_length[20]|callback__shortname_check";
        $rules['name'] = "required|maximum_length[100]";
        $this->validation->set_rules($rules);

        $fields['short_name'] = 'Identifier';
        $fields['name']	= 'Name';
        $this->validation->set_fields($fields);

        if ($this->validation->run() == FALSE) {
            $this->load->view('tournament/add');
        } else {
            $short_name = strtolower($this->input->post('short_name'));
            $this->_add($short_name);
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

    function _add($short_name) {
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

    function _get_all_tournaments() {
        $this->db->from('tournaments')->orderby("short_name");
        return $this->db->get()->result(); 
    }



}
?>