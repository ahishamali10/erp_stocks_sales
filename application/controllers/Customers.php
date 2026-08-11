<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customers extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Customer_model', 'customers');
    }

    public function index()
    {
        $search_input = $this->input->get('q');
        $search = is_scalar($search_input) ? substr(trim((string) $search_input), 0, 200) : '';
        $per_page = 10;
        $total_rows = $this->customers->count_filtered($search);
        $total_pages = max(1, (int) ceil($total_rows / $per_page));
        $page = $this->positive_integer($this->input->get('page'));
        $page = $page > 0 ? min($page, $total_pages) : 1;
        $offset = ($page - 1) * $per_page;

        $this->render('customers/index', array(
            'page_title' => 'Customers',
            'page_description' => 'Maintain customer contacts for sales invoices and account history.',
            'active_nav' => 'customers',
            'customers' => $this->customers->get_filtered($search, $per_page, $offset),
            'search' => $search,
            'pagination' => $this->build_pagination($page, $total_pages, $search),
            'total_rows' => $total_rows,
            'result_from' => $total_rows > 0 ? $offset + 1 : 0,
            'result_to' => min($offset + $per_page, $total_rows),
        ));
    }

    public function create()
    {
        $this->render('customers/form', array(
            'page_title' => 'Add customer',
            'page_description' => 'Create a customer record for invoice selection.',
            'active_nav' => 'customers',
            'customer' => NULL,
            'form_action' => 'customers/store',
            'submit_label' => 'Create customer',
            'save_error' => '',
        ));
    }

    public function store()
    {
        $this->require_post();

        if (!$this->validate_customer()) {
            return $this->render_form(NULL, 'customers/store', 'Create customer');
        }

        if (!$this->customers->create($this->customer_payload())) {
            $this->log_database_error('creating a customer');
            return $this->render_form(NULL, 'customers/store', 'Create customer', 'The customer could not be saved. Please try again.');
        }

        $this->session->set_flashdata('success', 'Customer created successfully.');
        redirect('customers');
    }

    public function edit($id)
    {
        $customer = $this->find_or_404($id);

        $this->render('customers/form', array(
            'page_title' => 'Edit customer',
            'page_description' => 'Update contact details without changing invoice history.',
            'active_nav' => 'customers',
            'customer' => $customer,
            'form_action' => 'customers/update/'.$customer->id,
            'submit_label' => 'Save changes',
            'save_error' => '',
        ));
    }

    public function update($id)
    {
        $this->require_post();
        $customer = $this->find_or_404($id);

        if (!$this->validate_customer()) {
            return $this->render_form($customer, 'customers/update/'.$customer->id, 'Save changes');
        }

        if (!$this->customers->update($customer->id, $this->customer_payload())) {
            $this->log_database_error('updating customer '.$customer->id);
            return $this->render_form($customer, 'customers/update/'.$customer->id, 'Save changes', 'The customer could not be updated. Please try again.');
        }

        $this->session->set_flashdata('success', 'Customer updated successfully.');
        redirect('customers');
    }

    public function delete($id)
    {
        $this->require_post();
        $customer = $this->find_or_404($id);

        if ($this->customers->count_sales($customer->id) > 0) {
            $this->session->set_flashdata('error', 'Customer '.$customer->name.' cannot be deleted because invoices are assigned to it.');
            redirect('customers');
        }

        if (!$this->customers->delete($customer->id)) {
            $error = $this->db->error();

            if ((int) $error['code'] !== 1451) {
                $this->log_database_error('deleting customer '.$customer->id);
            }

            $this->session->set_flashdata('error', 'The customer could not be deleted. It may now be in use.');
            redirect('customers');
        }

        $this->session->set_flashdata('success', 'Customer '.$customer->name.' was deleted.');
        redirect('customers');
    }

    private function validate_customer()
    {
        $name_rule = array('customer_name_valid', function () {
            $name = $this->input->post('name');
            return is_scalar($name) && trim((string) $name) !== '' && strlen(trim((string) $name)) <= 200;
        });
        $phone_rule = array('customer_phone_valid', function () {
            $phone = $this->input->post('phone');
            return $phone === NULL || (is_scalar($phone) && strlen(trim((string) $phone)) <= 50);
        });
        $email_rule = array('customer_email_valid', function () {
            $email = $this->input->post('email');

            if ($email === NULL || (is_scalar($email) && trim((string) $email) === '')) {
                return TRUE;
            }

            return is_scalar($email)
                && strlen(trim((string) $email)) <= 150
                && filter_var(trim((string) $email), FILTER_VALIDATE_EMAIL) !== FALSE;
        });

        $this->form_validation->set_message('customer_name_valid', 'The customer name is required and cannot exceed 200 characters.');
        $this->form_validation->set_message('customer_phone_valid', 'The phone number cannot exceed 50 characters.');
        $this->form_validation->set_message('customer_email_valid', 'Enter a valid email address no longer than 150 characters.');
        $this->form_validation->set_rules('name', 'Customer name', array($name_rule));
        $this->form_validation->set_rules('phone', 'Phone', array($phone_rule));
        $this->form_validation->set_rules('email', 'Email', array($email_rule));

        return $this->form_validation->run();
    }

    private function customer_payload()
    {
        $phone = trim((string) $this->input->post('phone'));
        $email = trim((string) $this->input->post('email'));

        return array(
            'name' => trim((string) $this->input->post('name')),
            'phone' => $phone === '' ? NULL : $phone,
            'email' => $email === '' ? NULL : strtolower($email),
        );
    }

    private function render_form($customer, $form_action, $submit_label, $save_error = '')
    {
        $this->output->set_status_header(422);
        $this->render('customers/form', array(
            'page_title' => $customer ? 'Edit customer' : 'Add customer',
            'page_description' => $customer
                ? 'Update contact details without changing invoice history.'
                : 'Create a customer record for invoice selection.',
            'active_nav' => 'customers',
            'customer' => $customer,
            'form_action' => $form_action,
            'submit_label' => $submit_label,
            'save_error' => $save_error,
        ));
    }

    private function build_pagination($current_page, $total_pages, $search)
    {
        if ($total_pages <= 1) {
            return array();
        }

        $items = array();

        if ($current_page > 1) {
            $items[] = array('type' => 'link', 'label' => 'Previous', 'url' => $this->customer_page_url($current_page - 1, $search), 'current' => FALSE);
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

            $items[] = array(
                'type' => 'link',
                'label' => (string) $page,
                'url' => $this->customer_page_url($page, $search),
                'current' => $page === $current_page,
            );
            $previous_page = $page;
        }

        if ($current_page < $total_pages) {
            $items[] = array('type' => 'link', 'label' => 'Next', 'url' => $this->customer_page_url($current_page + 1, $search), 'current' => FALSE);
        }

        return $items;
    }

    private function customer_page_url($page, $search)
    {
        $query = array();

        if ($search !== '') {
            $query['q'] = $search;
        }

        if ($page > 1) {
            $query['page'] = (int) $page;
        }

        $url = site_url('customers');

        return empty($query) ? $url : $url.'?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }

    private function render($view, $data)
    {
        $this->load->view('layouts/header', $data);
        $this->load->view($view, $data);
        $this->load->view('layouts/footer', $data);
    }

    private function find_or_404($id)
    {
        if (!$this->is_positive_integer($id)) {
            show_404();
        }

        $customer = $this->customers->find((int) $id);

        if (!$customer) {
            show_404();
        }

        return $customer;
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
