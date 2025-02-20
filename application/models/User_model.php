<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

 
    public function createUser($data) {
        return $this->db->insert('users', $data);
    }

    public function getUser($email) {
        return $this->db->where('email',  $email)->get('users')->row();
    }
	public function updatePhoneNumber($email, $phone) {
        return $this->db->where('email', $email)->update('users', ['phone' => $phone]);
    }
	public function get_all_users() {
        return $this->db->get('users')->result();
    }

}
       
