<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('User_model', 'users');
    }

    public function login()
    {
        if ($this->session->userdata('auth_user')) {
            redirect('home');
        }

        $error = '';
        $status = 200;

        if ($this->input->method(TRUE) === 'POST') {
            $email_input = $this->input->post('email');
            $password_input = $this->input->post('password');
            $email = is_scalar($email_input) ? strtolower(trim((string) $email_input)) : '';
            $password = is_scalar($password_input) ? (string) $password_input : '';

            if ($email === '' || strlen($email) > 150 || filter_var($email, FILTER_VALIDATE_EMAIL) === FALSE || $password === '') {
                $error = 'Enter a valid email address and password.';
                $status = 422;
            } else {
                $user = $this->users->find_by_email($email);

                if (!$user || !password_verify($password, $user->password)) {
                    $error = 'The email address or password is incorrect.';
                    $status = 422;
                } elseif ($user->role === 'user_warehouse' && (int) $user->warehouse_id < 1) {
                    $error = 'This account does not have a warehouse assignment.';
                    $status = 403;
                } else {
                    $this->session->sess_regenerate(TRUE);
                    $this->session->set_userdata('auth_user', array(
                        'id' => (int) $user->id,
                        'warehouse_id' => $user->warehouse_id === NULL ? NULL : (int) $user->warehouse_id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'warehouse_name' => $user->warehouse_name,
                        'warehouse_code' => $user->warehouse_code,
                        'warehouse_is_active' => $user->warehouse_is_active === NULL ? NULL : (int) $user->warehouse_is_active,
                    ));

                    $redirect = $this->session->userdata('auth_redirect');
                    $this->session->unset_userdata('auth_redirect');
                    redirect(is_string($redirect) && $redirect !== '' ? $redirect : 'home');
                }
            }
        } elseif ($this->input->method(TRUE) !== 'GET') {
            show_error('The requested method is not allowed.', 405, 'Method Not Allowed');
        }

        $this->output->set_status_header($status);
        $this->load->view('auth/login', array(
            'page_title' => 'Sign in',
            'error' => $error,
        ));
    }

    public function logout()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('The requested method is not allowed.', 405, 'Method Not Allowed');
        }

        $this->session->sess_destroy();
        redirect('login');
    }
}
