<?php

class Club extends Controller {

    function Club() {
        parent::Controller();	
    }

    function overview() {
        $data["clubs"] = $this->_get_all_clubs();
        $this->load->view('club/overview', $data);
    }

    function add() {
        $this->load->helper('form');
        $this->load->library('validation');

        $rules['short_name'] = "required|alpha_dash|maximum_length[10]|callback__is_short_name_unique";
        $rules['name'] = "required|maximum_length[100]|callback__is_name_unique";
        $this->validation->set_rules($rules);

        $fields['name'] = 'Name';
        $fields['short_name'] = 'Abbreviation';
        $this->validation->set_fields($fields);

        if ($this->validation->run() == FALSE) {
            $this->load->view('club/add');
        } else {
            $this->_add();
            redirect("club/overview/");
        }
    }

    function _get_all_clubs() {
        $this->db->from('clubs')->orderby("short_name");
        return $this->db->get()->result(); 
    }

    function _is_short_name_unique($value) {
        $this->validation->set_message('_is_short_name_unique', 'This abbreviation is already used.');
        $this->db->from('clubs')->where('short_name', $value);
        return $this->db->get()->num_rows() == 0;
    }

    function _is_name_unique($value) {
        $this->validation->set_message('_is_name_unique', 'This name is already used.');
        $this->db->from('clubs')->where('name', $value);
        return $this->db->get()->num_rows() == 0;
    }

    function _add() {
        $data = array(
            'short_name' => strtolower($this->input->post('short_name')),
            'name' => $this->input->post('name'),
        );
        $this->db->insert('clubs', $data); 
    }

}
?>