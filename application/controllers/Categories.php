<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Categories extends MY_Controller
{
    private $current_category_id = NULL;

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Category_model', 'categories');
    }

    public function index()
    {
        $data = array(
            'page_title' => 'Categories',
            'page_description' => 'Organize products into reusable catalog groups.',
            'active_nav' => 'categories',
            'categories' => $this->categories->get_with_product_counts(),
        );

        $this->render('categories/index', $data);
    }

    public function create()
    {
        $this->render('categories/form', array(
            'page_title' => 'Add category',
            'page_description' => 'Create a category for product organization and filtering.',
            'active_nav' => 'categories',
            'category' => NULL,
            'form_action' => 'categories/store',
            'submit_label' => 'Create category',
            'save_error' => '',
        ));
    }

    public function store()
    {
        $this->require_post();
        $this->current_category_id = NULL;

        if (!$this->validate_category()) {
            return $this->render_form(NULL, 'categories/store', 'Create category');
        }

        if (!$this->categories->create($this->category_payload())) {
            $this->log_database_error('creating a category');
            return $this->render_form(NULL, 'categories/store', 'Create category', 'The category could not be saved. Please try again.');
        }

        $this->session->set_flashdata('success', 'Category created successfully.');
        redirect('categories');
    }

    public function edit($id)
    {
        $category = $this->find_or_404($id);

        $this->render('categories/form', array(
            'page_title' => 'Edit category',
            'page_description' => 'Rename this catalog group everywhere it is used.',
            'active_nav' => 'categories',
            'category' => $category,
            'form_action' => 'categories/update/'.$category->id,
            'submit_label' => 'Save changes',
            'save_error' => '',
        ));
    }

    public function update($id)
    {
        $this->require_post();
        $category = $this->find_or_404($id);
        $this->current_category_id = (int) $category->id;

        if (!$this->validate_category()) {
            return $this->render_form($category, 'categories/update/'.$category->id, 'Save changes');
        }

        if (!$this->categories->update($category->id, $this->category_payload())) {
            $this->log_database_error('updating category '.$category->id);
            return $this->render_form($category, 'categories/update/'.$category->id, 'Save changes', 'The category could not be updated. Please try again.');
        }

        $this->session->set_flashdata('success', 'Category updated successfully.');
        redirect('categories');
    }

    public function delete($id)
    {
        $this->require_post();
        $category = $this->find_or_404($id);

        if ($this->categories->count_products($category->id) > 0) {
            $this->session->set_flashdata('error', 'Category '.$category->name.' cannot be deleted because products are assigned to it.');
            redirect('categories');
        }

        if (!$this->categories->delete($category->id)) {
            $error = $this->db->error();

            if ((int) $error['code'] !== 1451) {
                $this->log_database_error('deleting category '.$category->id);
            }

            $this->session->set_flashdata('error', 'The category could not be deleted. It may now be in use.');
            redirect('categories');
        }

        $this->session->set_flashdata('success', 'Category '.$category->name.' was deleted.');
        redirect('categories');
    }

    private function validate_category()
    {
        $category_name_unique = array('category_name_unique', function ($name) {
            return !$this->categories->name_exists(trim($name), $this->current_category_id);
        });

        $this->form_validation->set_message('category_name_unique', 'The category name is already in use.');
        $this->form_validation->set_rules('name', 'Category name', array('trim', 'required', 'max_length[150]', $category_name_unique));

        return $this->form_validation->run();
    }

    private function category_payload()
    {
        return array(
            'name' => trim((string) $this->input->post('name')),
        );
    }

    private function render_form($category, $form_action, $submit_label, $save_error = '')
    {
        $data = array(
            'page_title' => $category ? 'Edit category' : 'Add category',
            'page_description' => $category
                ? 'Rename this catalog group everywhere it is used.'
                : 'Create a category for product organization and filtering.',
            'active_nav' => 'categories',
            'category' => $category,
            'form_action' => $form_action,
            'submit_label' => $submit_label,
            'save_error' => $save_error,
        );

        $this->output->set_status_header(422);
        $this->render('categories/form', $data);
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

        $category = $this->categories->find((int) $id);

        if (!$category) {
            show_404();
        }

        return $category;
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
