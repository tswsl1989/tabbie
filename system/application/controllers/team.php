<?php

class Building extends Controller {

    function Building() {
        parent::Controller();	
    }

    function overview($tournament_short_name) {
        $tournament = $this->_get_tournament($tournament_short_name);
        if (! $tournament ) {
            redirect('home/home');
            return;
        }
        $data["tournament"] = $tournament;
        $data["buildings"] = $this->_get_all_buildings($tournament->id);
        $this->load->view('building/overview', $data);
    }

    function add($tournament_short_name) {
        $this->tournament = $this->_get_tournament($tournament_short_name);
        $data["tournament"] = $this->tournament;

        $this->load->helper('form');
        $this->load->library('validation');

        $rules['name'] = "required|maximum_length[100]|callback__is_building_unique";
        $this->validation->set_rules($rules);

        $fields['name']	= 'Name';
        $this->validation->set_fields($fields);

        if ($this->validation->run() == FALSE) {
            $this->load->view('building/add', $data);
        } else {
            $this->_add();
            redirect("building/overview/$tournament_short_name");
        }
    }

    function _get_all_buildings($tournament_id) {
        $this->db->from('buildings')->where('tournament_id', $tournament_id);
        return $this->db->get()->result(); 
    }

    function _get_tournament($short_name) {
        $this->db->from('tournaments')->where('short_name', $short_name);
        return $this->db->get()->row();
    }

    function _is_building_unique($name) {
        $this->validation->set_message('_is_building_unique', 'This building name is already used.');
        $this->db->from('buildings')->where('name', $name)->where('tournament_id', $this->tournament->id);
        return $this->db->get()->num_rows() == 0;
    }

    function _add() {
        $data = array(
            'tournament_id' => $this->tournament->id,
            'name' => $this->input->post('name'),
        );
        $this->db->insert('buildings', $data); 
    }

}
?>