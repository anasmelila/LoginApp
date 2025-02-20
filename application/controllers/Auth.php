<?php

defined('BASEPATH') OR exit('No direct script access allowed');

use Google\Client as GoogleClient;

class Auth extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('user_model');
        $this->load->library('session');
    }

    public function index() {
        $this->load->view('login');
    }

    public function dashboard() {
        if ($this->session->has_userdata('user')) {
            $user = (array) $this->session->userdata('user'); // Convert object to array
            $this->load->view('home', ['user' => $user]);
        } else {
            $this->load->view('login');
        }
    }

    public function google_login() {
        $client = new GoogleClient();
        $client->setApplicationName('your-app-name');
		$client->setClientId('your-client-id');
		$client->setClientSecret('your-client-secret');
        $client->setRedirectUri(base_url('auth/google_login'));
        $client->addScope([
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile',
            'https://www.googleapis.com/auth/calendar.readonly',
        ]);

        if ($code = $this->input->get('code')) {
            $token = $client->fetchAccessTokenWithAuthCode($code);
            
            if (isset($token['error'])) {
                die('Error fetching access token');
            }

            $client->setAccessToken($token);
            $oauth = new Google\Service\Oauth2($client);
            $user_info = $oauth->userinfo->get();

            $data = [
                'name' => $user_info->name,
                'email' => $user_info->email,
                'google_access_token' => json_encode($token) // Save token as JSON
            ];

            // Check if user exists
            if ($user = $this->user_model->getUser($user_info->email)) {
                // Update access token
                $this->user_model->updateUser($user_info->email, $data);
                $this->session->set_userdata('user', (array)$user);
            } else {
                // Create user and store access token
                $this->user_model->createUser($data);
                $this->session->set_userdata('user', $data);
            }

            redirect('home'); // Redirect to dashboard or home page
        } else {
            // Generate login URL and redirect
            $url = $client->createAuthUrl();
            header('Location: ' . filter_var($url, FILTER_SANITIZE_URL));
            exit();
        }
    }

    public function save_phone() {
        $phone = $this->input->post('phone');
        $user = $this->session->userdata('user');

        if ($this->user_model->updatePhoneNumber($user['email'], $phone)) {
            $this->session->set_flashdata('success', 'Phone number updated successfully.');
        } else {
            $this->session->set_flashdata('error', 'Failed to update phone number.');
        }

        redirect('dashboard');
    }

    public function logout() {
        // Destroy session
        $this->session->unset_userdata('user');
        $this->session->sess_destroy();
        
        // Redirect to login page
        redirect('auth');
    }
}
