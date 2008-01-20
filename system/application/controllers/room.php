<?php

class Room extends Controller {

    function Room() {
        parent::Controller();	
    }

    function overview($tournament_short_name) {
        $tournament = $this->_get_tournament($tournament_short_name);
        if (! $tournament ) {
            redirect('home/home');
            return;
        }
        $data["tournament"] = $tournament;
        $data["rooms"] = $this->_get_all_rooms($tournament->id);
        $this->load->view('room/overview', $data);
    }

    function add($tournament_short_name) {
        $tournament = $this->_get_tournament($tournament_short_name);
        $data["tournament"] = $tournament;

        $this->load->helper('form');
        $this->load->library('validation');

        $rules['name'] = "required|maximum_length[100]|callback__is_room_unique";
        $this->validation->set_rules($rules);

        $fields['name']	= 'Name';
        $this->validation->set_fields($fields);

        if ($this->validation->run() == FALSE) {
            $this->load->view('room/add');
        } else {
            //$this->_create($short_name);
            redirect("room/overview/$tournament_short_name");
        }

        $this->load->view('room/add', $data);
    }

    function _get_all_rooms($tournament_id) {
        $sql = "SELECT buildings.name as building_name, rooms.name, rooms.id
                FROM rooms, buildings
                WHERE buildings.tournament_id = ? AND
                rooms.building_id = buildings.id";
        return $this->db->query($sql, $tournament_id)->result(); 
        return $this->db->result();
    }

    function _get_tournament($short_name) {
        $this->db->from('tournaments')->where('short_name', $short_name);
        return $this->db->get()->row();
    }

    function _is_room_unique($short_name) {
        $this->validation->set_message('_shortname_check', 'This identifier is already used.');
        $this->db->from('tournaments')->where('short_name', $short_name);
        return $this->db->get()->num_rows() == 0;
    }

}
?>