<?php

class Team extends Controller {

    //TODO: lot's of S&R with building => team

    function Team() {
        parent::Controller();	
    }

    function overview($tournament_short_name) {
        $tournament = $this->_get_tournament($tournament_short_name);
        if (! $tournament ) {
            redirect('home/home');
            return;
        }
        $data["tournament"] = $tournament;
        $data["teams"] = $this->_get_all_teams($tournament->id);
        $this->load->view('team/overview', $data);
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

    function _get_all_teams($tournament_id) {
        $sql = "SELECT  clubs.short_name as club_short_name,
                        teams.short_name as team_short_name,
                        teams.id
                FROM clubs, teams
                WHERE teams.tournament_id = ? AND
                teams.club_id = clubs.id";
        $teams = $this->db->query($sql, $tournament_id)->result();
        $result = array();
        foreach ($teams as $team) {
             $sql = "SELECT  persons.name,
                            speakers.id
                    FROM speakers, persons
                    WHERE speakers.team_id = ? AND
                    speakers.person_id = persons.id";
            $team->speakers = $this->db->query($sql, $team->id)->result();
            $result[] = $team;
        }
        return $result;
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