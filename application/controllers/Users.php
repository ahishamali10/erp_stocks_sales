<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->require_admin();
        $this->load->model('User_model', 'users');
        $this->load->model('Warehouse_model', 'warehouses');
    }

    public function index()
    {
        $search_input = $this->input->get('q');
        $role_input = $this->input->get('role');
        $search = is_scalar($search_input) ? substr(trim((string) $search_input), 0, 200) : '';
        $role = is_scalar($role_input) && in_array((string) $role_input, array('admin', 'user_warehouse'), TRUE)
            ? (string) $role_input
            : '';
        $per_page = 10;
        $total_rows = $this->users->count_filtered($search, $role);
        $total_pages = max(1, (int) ceil($total_rows / $per_page));
        $page = $this->positive_integer($this->input->get('page'));
        $page = $page > 0 ? min($page, $total_pages) : 1;
        $offset = ($page - 1) * $per_page;

        $this->render('users/index', array(
            'page_title' => 'Users',
            'page_description' => 'Manage ERP access, roles, and warehouse assignments.',
            'active_nav' => 'users',
            'users' => $this->users->get_filtered($search, $role, $per_page, $offset),
            'summary' => $this->users->get_summary(),
            'search' => $search,
            'role' => $role,
            'pagination' => $this->build_pagination($page, $total_pages, $search, $role),
            'total_rows' => $total_rows,
            'result_from' => $total_rows > 0 ? $offset + 1 : 0,
            'result_to' => min($offset + $per_page, $total_rows),
        ));
    }

    public function create()
    {
        $this->render_form(NULL, 'users/store', 'Create user');
    }

    public function store()
    {
        $this->require_post();

        if (!$this->validate_user(NULL)) {
            return $this->render_form(NULL, 'users/store', 'Create user', '', 422);
        }

        $payload = $this->user_payload(TRUE);

        if ($payload === FALSE || !$this->users->create($payload)) {
            $this->log_database_error('creating a user');
            return $this->render_form(NULL, 'users/store', 'Create user', 'The user could not be saved. The email address may now be in use.', 422);
        }

        $this->session->set_flashdata('success', 'User created successfully.');
        redirect('users');
    }

    public function edit($id)
    {
        $user = $this->find_or_404($id);
        $this->render_form($user, 'users/update/'.$user->id, 'Save changes');
    }

    public function update($id)
    {
        $this->require_post();
        $user = $this->find_or_404($id);

        if (!$this->validate_user($user)) {
            return $this->render_form($user, 'users/update/'.$user->id, 'Save changes', '', 422);
        }

        $payload = $this->user_payload(FALSE);

        if ($payload === FALSE) {
            return $this->render_form($user, 'users/update/'.$user->id, 'Save changes', 'The password could not be secured. Please try again.', 422);
        }

        $result = $this->users->update_with_admin_guard($user->id, $payload);

        if (!$result['success']) {
            if ($result['code'] === 'last_admin') {
                return $this->render_form($user, 'users/update/'.$user->id, 'Save changes', 'The final administrator cannot be changed to a warehouse user.', 422);
            }

            $this->log_database_error('updating user '.$user->id);
            return $this->render_form($user, 'users/update/'.$user->id, 'Save changes', 'The user could not be updated. The email address may now be in use.', 422);
        }

        $self_demoted = (int) $user->id === (int) $this->current_user['id'] && $payload['role'] !== 'admin';
        $this->session->set_flashdata('success', 'User updated successfully.');
        redirect($self_demoted ? 'home' : 'users');
    }

    public function delete($id)
    {
        $this->require_post();
        $user = $this->find_or_404($id);
        $result = $this->users->delete_with_guards($user->id, $this->current_user['id']);

        if (!$result['success']) {
            $messages = array(
                'self' => 'You cannot delete your own signed-in account.',
                'last_admin' => 'The final administrator account cannot be deleted.',
                'has_sales' => 'User '.$user->name.' cannot be deleted because invoices are attributed to this account.',
            );
            $this->session->set_flashdata(
                'error',
                isset($messages[$result['code']]) ? $messages[$result['code']] : 'The user could not be deleted. Please try again.'
            );
            redirect('users');
        }

        $this->session->set_flashdata('success', 'User '.$user->name.' was deleted.');
        redirect('users');
    }

    private function validate_user($user)
    {
        $user_id = $user ? (int) $user->id : NULL;
        $name_rule = array('user_name_valid', function () {
            $name = $this->input->post('name');
            return is_scalar($name) && trim((string) $name) !== '' && strlen(trim((string) $name)) <= 150;
        });
        $email_rule = array('user_email_valid', function () use ($user_id) {
            $email = $this->input->post('email');

            return is_scalar($email)
                && strlen(trim((string) $email)) <= 150
                && filter_var(trim((string) $email), FILTER_VALIDATE_EMAIL) !== FALSE
                && !$this->users->email_exists((string) $email, $user_id);
        });
        $role_rule = array('user_role_valid', function () {
            $role = $this->input->post('role');
            return is_scalar($role) && in_array((string) $role, array('admin', 'user_warehouse'), TRUE);
        });
        $warehouse_rule = array('user_warehouse_valid', function () {
            $role = $this->input->post('role');
            $warehouse_id = $this->input->post('warehouse_id');

            if ($role === 'admin') {
                return $warehouse_id === NULL || (is_scalar($warehouse_id) && in_array(trim((string) $warehouse_id), array('', '0'), TRUE));
            }

            if ($role !== 'user_warehouse' || !$this->is_positive_integer($warehouse_id)) {
                return FALSE;
            }

            $warehouse = $this->warehouses->find((int) $warehouse_id);
            return $warehouse && (int) $warehouse->is_active === 1;
        });
        $password_rule = array('user_password_valid', function () use ($user) {
            $password = $this->input->post('password');

            if ($user && ($password === NULL || (is_scalar($password) && $password === ''))) {
                return TRUE;
            }

            return is_scalar($password) && strlen((string) $password) >= 8 && strlen((string) $password) <= 72;
        });
        $confirmation_rule = array('user_password_confirmation_valid', function () use ($user) {
            $password = $this->input->post('password');
            $confirmation = $this->input->post('password_confirmation');

            if ($user && ($password === NULL || (is_scalar($password) && $password === ''))) {
                return $confirmation === NULL || (is_scalar($confirmation) && $confirmation === '');
            }

            return is_scalar($password) && is_scalar($confirmation) && (string) $password === (string) $confirmation;
        });

        $this->form_validation->set_message('user_name_valid', 'The user name is required and cannot exceed 150 characters.');
        $this->form_validation->set_message('user_email_valid', 'Enter a unique, valid email address no longer than 150 characters.');
        $this->form_validation->set_message('user_role_valid', 'Select a valid user role.');
        $this->form_validation->set_message('user_warehouse_valid', 'Warehouse users require an active warehouse; administrators cannot have a warehouse assignment.');
        $this->form_validation->set_message('user_password_valid', 'The password must contain between 8 and 72 characters.');
        $this->form_validation->set_message('user_password_confirmation_valid', 'The password confirmation must match.');
        $this->form_validation->set_rules('name', 'User name', array($name_rule));
        $this->form_validation->set_rules('email', 'Email', array($email_rule));
        $this->form_validation->set_rules('role', 'Role', array($role_rule));
        $this->form_validation->set_rules('warehouse_id', 'Warehouse', array($warehouse_rule));
        $this->form_validation->set_rules('password', 'Password', array($password_rule));
        $this->form_validation->set_rules('password_confirmation', 'Password confirmation', array($confirmation_rule));

        return $this->form_validation->run();
    }

    private function user_payload($password_required)
    {
        $role = (string) $this->input->post('role');
        $payload = array(
            'warehouse_id' => $role === 'admin' ? NULL : (int) $this->input->post('warehouse_id'),
            'name' => trim((string) $this->input->post('name')),
            'email' => strtolower(trim((string) $this->input->post('email'))),
            'role' => $role,
        );
        $password = $this->input->post('password');

        if ($password_required || (is_scalar($password) && $password !== '')) {
            $hash = password_hash((string) $password, PASSWORD_BCRYPT);

            if ($hash === FALSE) {
                return FALSE;
            }

            $payload['password'] = $hash;
        }

        return $payload;
    }

    private function render_form($user, $form_action, $submit_label, $save_error = '', $status = 200)
    {
        $this->output->set_status_header((int) $status);
        $this->render('users/form', array(
            'page_title' => $user ? 'Edit user' : 'Add user',
            'page_description' => $user ? 'Update access, role, warehouse assignment, or password.' : 'Create a secure ERP account and define its warehouse scope.',
            'active_nav' => 'users',
            'user' => $user,
            'warehouses' => $this->warehouses->get_all(),
            'form_action' => $form_action,
            'submit_label' => $submit_label,
            'save_error' => $save_error,
        ));
    }

    private function build_pagination($current_page, $total_pages, $search, $role)
    {
        if ($total_pages <= 1) {
            return array();
        }

        $items = array();

        if ($current_page > 1) {
            $items[] = $this->page_link('Previous', $current_page - 1, $search, $role, FALSE);
        }

        $pages = array(1, $total_pages);
        for ($page = max(1, $current_page - 2); $page <= min($total_pages, $current_page + 2); $page++) {
            $pages[] = $page;
        }

        $pages = array_values(array_unique($pages));
        sort($pages);
        $previous_page = 0;

        foreach ($pages as $page) {
            if ($previous_page > 0 && $page > $previous_page + 1) {
                $items[] = array('type' => 'ellipsis');
            }

            $items[] = $this->page_link((string) $page, $page, $search, $role, $page === $current_page);
            $previous_page = $page;
        }

        if ($current_page < $total_pages) {
            $items[] = $this->page_link('Next', $current_page + 1, $search, $role, FALSE);
        }

        return $items;
    }

    private function page_link($label, $page, $search, $role, $current)
    {
        $query = array();

        if ($search !== '') {
            $query['q'] = $search;
        }

        if ($role !== '') {
            $query['role'] = $role;
        }

        if ($page > 1) {
            $query['page'] = (int) $page;
        }

        $url = site_url('users');

        return array(
            'type' => 'link',
            'label' => $label,
            'url' => empty($query) ? $url : $url.'?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986),
            'current' => $current,
        );
    }

    private function find_or_404($id)
    {
        if (!$this->is_positive_integer($id)) {
            show_404();
        }

        $user = $this->users->find((int) $id);

        if (!$user) {
            show_404();
        }

        return $user;
    }

    private function render($view, $data)
    {
        $this->load->view('layouts/header', $data);
        $this->load->view($view, $data);
        $this->load->view('layouts/footer', $data);
    }

    private function require_post()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('The requested method is not allowed.', 405, 'Method Not Allowed');
        }
    }

    private function positive_integer($value)
    {
        return $this->is_positive_integer($value) ? (int) $value : 0;
    }

    private function is_positive_integer($value)
    {
        return is_scalar($value) && ctype_digit((string) $value) && (int) $value > 0;
    }

    private function log_database_error($context)
    {
        $error = $this->db->error();
        log_message('error', 'Database error while '.$context.': '.$error['code'].' '.$error['message']);
    }
}
