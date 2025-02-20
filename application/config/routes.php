<?php
defined('BASEPATH') OR exit('No direct script access allowed');



$route['default_controller'] = 'auth';  // Load the login page by default
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['auth/google_login'] = 'auth/google_login';  // Route for Google OAuth
$route['dashboard'] = 'auth/dashboard';  // Route for user dashboard
$route['logout'] = 'auth/logout';  // Route to log out
$route['home'] = 'auth/dashboard';  // Home redirects to dashboard

$route['auth'] = 'auth/index';  // Home redirects to dashboard
$route['auth/logout'] = 'auth/logout';

$route['savephone'] = 'auth/save_phone';

