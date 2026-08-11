<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Warehouses extends MY_Controller
{
    private $current_warehouse_id = NULL;

    public function __construct()
    {
        parent::__construct();

        $this->require_admin();

        $this->load->model('Warehouse_model', 'warehouses');
    }

    public function index()
    {
        $this->render('warehouses/index', array(
            'page_title' => 'Warehouses',
            'page_description' => 'Manage physical stock locations and their operational status.',
            'active_nav' => 'warehouses',
            'warehouses' => $this->warehouses->get_with_counts(),
            'summary' => $this->warehouses->get_summary(),
        ));
    }

    public function create()
    {
        $this->render('warehouses/form', array(
            'page_title' => 'Add warehouse',
            'page_description' => 'Create a stock location and initialize its product inventory.',
            'active_nav' => 'warehouses',
            'warehouse' => NULL,
            'form_action' => 'warehouses/store',
            'submit_label' => 'Create warehouse',
            'save_error' => '',
        ));
    }

    public function store()
    {
        $this->require_post();
        $this->current_warehouse_id = NULL;

        if (!$this->validate_warehouse()) {
            return $this->render_form(NULL, 'warehouses/store', 'Create warehouse');
        }

        if (!$this->warehouses->create($this->warehouse_payload())) {
            $this->log_database_error('creating a warehouse');
            return $this->render_form(NULL, 'warehouses/store', 'Create warehouse', 'The warehouse could not be saved. Please try again.');
        }

        $this->session->set_flashdata('success', 'Warehouse created and inventory initialized successfully.');
        redirect('warehouses');
    }

    public function edit($id)
    {
        $warehouse = $this->find_or_404($id);

        $this->render('warehouses/form', array(
            'page_title' => 'Edit warehouse',
            'page_description' => 'Update the location name or operational code.',
            'active_nav' => 'warehouses',
            'warehouse' => $warehouse,
            'form_action' => 'warehouses/update/'.$warehouse->id,
            'submit_label' => 'Save changes',
            'save_error' => '',
        ));
    }

    public function update($id)
    {
        $this->require_post();
        $warehouse = $this->find_or_404($id);
        $this->current_warehouse_id = (int) $warehouse->id;

        if (!$this->validate_warehouse()) {
            return $this->render_form($warehouse, 'warehouses/update/'.$warehouse->id, 'Save changes');
        }

        if (!$this->warehouses->update($warehouse->id, $this->warehouse_payload())) {
            $this->log_database_error('updating warehouse '.$warehouse->id);
            return $this->render_form($warehouse, 'warehouses/update/'.$warehouse->id, 'Save changes', 'The warehouse could not be updated. Please try again.');
        }

        $this->session->set_flashdata('success', 'Warehouse updated successfully.');
        redirect('warehouses');
    }

    public function toggle_status($id)
    {
        $this->require_post();
        $warehouse = $this->find_or_404($id);

        if ($warehouse->is_active && $this->warehouses->count_assigned_users($warehouse->id) > 0) {
            $this->session->set_flashdata('error', 'Warehouse '.$warehouse->name.' cannot be disabled while users are assigned to it.');
            redirect('warehouses');
        }

        if (!$this->warehouses->toggle_status($warehouse->id)) {
            $this->log_database_error('changing warehouse status for '.$warehouse->id);
            $this->session->set_flashdata('error', 'The warehouse status could not be changed.');
            redirect('warehouses');
        }

        $status = $warehouse->is_active ? 'disabled' : 'enabled';
        $this->session->set_flashdata('success', 'Warehouse '.$warehouse->code.' was '.$status.'.');
        redirect('warehouses');
    }

    private function validate_warehouse()
    {
        $name_input = $this->input->post('name');
        $code_input = $this->input->post('code');
        $warehouse_name_valid = array('warehouse_name_valid', function ($value) use ($name_input) {
            return $this->valid_scalar_text($value, $name_input);
        });
        $warehouse_code_valid = array('warehouse_code_valid', function ($value) use ($code_input) {
            return $this->valid_scalar_text($value, $code_input);
        });
        $warehouse_code_format = array('warehouse_code_format', function ($value) use ($code_input) {
            if ($code_input === NULL || (is_scalar($code_input) && trim((string) $code_input) === '')) {
                return TRUE;
            }

            return is_scalar($code_input)
                && preg_match('/^[A-Za-z0-9_-]+$/', trim((string) $value)) === 1;
        });
        $warehouse_code_unique = array('warehouse_code_unique', function ($code) use ($code_input) {
            if ($code_input === NULL || (is_scalar($code_input) && trim((string) $code_input) === '')) {
                return TRUE;
            }

            return is_scalar($code_input)
                && !$this->warehouses->code_exists((string) $code, $this->current_warehouse_id);
        });

        $this->form_validation->set_message('warehouse_name_valid', 'The warehouse name must be valid text.');
        $this->form_validation->set_message('warehouse_code_valid', 'The warehouse code must be valid text.');
        $this->form_validation->set_message('warehouse_code_format', 'The warehouse code may contain only letters, numbers, hyphens, and underscores.');
        $this->form_validation->set_message('warehouse_code_unique', 'The warehouse code is already in use.');
        $this->form_validation->set_rules('name', 'Warehouse name', array('trim', 'required', 'max_length[150]', $warehouse_name_valid));
        $this->form_validation->set_rules('code', 'Warehouse code', array('trim', 'required', 'max_length[50]', $warehouse_code_valid, $warehouse_code_format, $warehouse_code_unique));

        return $this->form_validation->run();
    }

    private function valid_scalar_text($value, $original_value)
    {
        if ($original_value === NULL || (is_scalar($original_value) && trim((string) $original_value) === '')) {
            return TRUE;
        }

        return is_scalar($original_value) && trim((string) $value) !== '';
    }

    private function warehouse_payload()
    {
        return array(
            'name' => trim((string) $this->input->post('name')),
            'code' => strtoupper(trim((string) $this->input->post('code'))),
        );
    }

    private function render_form($warehouse, $form_action, $submit_label, $save_error = '')
    {
        $this->output->set_status_header(422);
        $this->render('warehouses/form', array(
            'page_title' => $warehouse ? 'Edit warehouse' : 'Add warehouse',
            'page_description' => $warehouse
                ? 'Update the location name or operational code.'
                : 'Create a stock location and initialize its product inventory.',
            'active_nav' => 'warehouses',
            'warehouse' => $warehouse,
            'form_action' => $form_action,
            'submit_label' => $submit_label,
            'save_error' => $save_error,
        ));
    }

    private function render($view, $data)
    {
        $this->load->view('layouts/header', $data);
        $this->load->view($view, $data);
        $this->load->view('layouts/footer', $data);
    }

    private function find_or_404($id)
    {
        if (!is_scalar($id) || !ctype_digit((string) $id) || (int) $id < 1) {
            show_404();
        }

        $warehouse = $this->warehouses->find((int) $id);

        if (!$warehouse) {
            show_404();
        }

        return $warehouse;
    }

    private function require_post()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('The requested method is not allowed.', 405, 'Method Not Allowed');
        }
    }

    private function log_database_error($context)
    {
        $error = $this->db->error();
        log_message('error', 'Database error while '.$context.': '.$error['code'].' '.$error['message']);
    }
}
