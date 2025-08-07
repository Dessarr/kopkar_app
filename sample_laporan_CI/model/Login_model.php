<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login_model extends CI_Model {
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function cek_login($username, $password) {
        // Gunakan escape string untuk keamanan
        $username = $this->db->escape($username);
        
        // Debug: Let's see what password hash we're comparing against
        $hashed_password = md5($password);
        log_message('debug', 'Trying to login with hash: ' . $hashed_password);
        
        $sql = "SELECT * FROM tbl_user WHERE u_name = {$username} AND pass_word = '{$hashed_password}'";
        $query = $this->db->query($sql);
        
        if ($query === FALSE) {
            log_message('error', 'Query Error: ' . $this->db->error()['message']);
            return FALSE;
        }
        
        return $query->row();
    }
}