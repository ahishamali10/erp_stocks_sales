<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    public $current_user = NULL;

    public function __construct()
    {
        parent::__construct();

        $this->load->model('User_model', 'auth_users');
        $session_user = $this->session->userdata('auth_user');
        $user_id = is_array($session_user) && isset($session_user['id']) ? (int) $session_user['id'] : 0;
        $user = $user_id > 0 ? $this->auth_users->find($user_id) : NULL;

        if (!$user || !in_array($user->role, array('admin', 'user_warehouse'), TRUE)) {
            $this->session->unset_userdata('auth_user');
            $this->require_login();
        }

        if ($user->role === 'user_warehouse' && (int) $user->warehouse_id < 1) {
            $this->session->unset_userdata('auth_user');
            $this->require_login('Your account does not have a warehouse assignment.');
        }

        $this->current_user = array(
            'id' => (int) $user->id,
            'warehouse_id' => $user->warehouse_id === NULL ? NULL : (int) $user->warehouse_id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'warehouse_name' => $user->warehouse_name,
            'warehouse_code' => $user->warehouse_code,
            'warehouse_is_active' => $user->warehouse_is_active === NULL ? NULL : (int) $user->warehouse_is_active,
        );
        $this->session->set_userdata('auth_user', $this->current_user);
    }

    protected function is_admin()
    {
        return $this->current_user['role'] === 'admin';
    }

    protected function require_admin()
    {
        if (!$this->is_admin()) {
            $this->deny_access('Administrator access is required for this page.');
        }
    }

    protected function can_access_warehouse($warehouse_id)
    {
        if ($this->is_admin()) {
            return TRUE;
        }

        return (int) $this->current_user['warehouse_id'] === (int) $warehouse_id;
    }

    protected function require_warehouse_access($warehouse_id)
    {
        if (!$this->can_access_warehouse($warehouse_id)) {
            $this->deny_access('You do not have permission to access that warehouse.');
        }
    }

    protected function authorized_warehouses($active_only = FALSE)
    {
        $this->db
            ->select('id, name, code, is_active')
            ->from('warehouses');

        if (!$this->is_admin()) {
            $this->db->where('id', (int) $this->current_user['warehouse_id']);
        }

        if ($active_only) {
            $this->db->where('is_active', 1);
        }

        return $this->db
            ->order_by('name', 'ASC')
            ->get()
            ->result();
    }

    protected function deny_access($message)
    {
        if ($this->expects_json()) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode(array(
                    'success' => FALSE,
                    'message' => $message,
                    'csrf' => array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash(),
                    ),
                )))
                ->_display();
            exit;
        }

        show_error($message, 403, 'Forbidden');
    }

    private function require_login($message = 'Please sign in to continue.')
    {
        if ($this->expects_json()) {
            $this->output
                ->set_status_header(401)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode(array('success' => FALSE, 'message' => $message)))
                ->_display();
            exit;
        }

        if ($this->input->method(TRUE) === 'GET') {
            $uri = uri_string();
            $query = $this->input->server('QUERY_STRING');
            $this->session->set_userdata('auth_redirect', $uri.($query ? '?'.$query : ''));
        }

        $this->session->set_flashdata('error', $message);
        redirect('login');
        exit;
    }

    private function expects_json()
    {
        $accept = (string) $this->input->server('HTTP_ACCEPT');

        return $this->input->is_ajax_request() || stripos($accept, 'application/json') !== FALSE;
    }
}
