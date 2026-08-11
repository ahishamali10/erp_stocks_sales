<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends MY_Controller
{
    private $current_product_id = NULL;

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Product_model', 'products');
        $this->load->model('Category_model', 'categories');
    }

    public function index()
    {
        $search = trim((string) $this->input->get('q'));
        $search = substr($search, 0, 200);
        $category_id = $this->positive_integer($this->input->get('category_id'));

        if ($category_id > 0 && !$this->categories->exists($category_id)) {
            $category_id = 0;
        }

        $per_page = 5;
        $total_rows = $this->products->count_filtered($search, $category_id);
        $total_pages = max(1, (int) ceil($total_rows / $per_page));
        $page = $this->positive_integer($this->input->get('page'));
        $page = $page > 0 ? min($page, $total_pages) : 1;
        $offset = ($page - 1) * $per_page;

        $data = array(
            'page_title' => 'Products',
            'page_description' => 'Manage the product catalog, pricing, reorder alerts, and availability.',
            'active_nav' => 'products',
            'products' => $this->products->get_filtered($search, $category_id, $per_page, $offset),
            'categories' => $this->categories->get_all(),
            'search' => $search,
            'category_id' => $category_id,
            'pagination' => $this->build_pagination($page, $total_pages, $search, $category_id),
            'total_rows' => $total_rows,
            'result_from' => $total_rows > 0 ? $offset + 1 : 0,
            'result_to' => min($offset + $per_page, $total_rows),
        );

        $this->render('products/index', $data);
    }

    public function create()
    {
        $data = array(
            'page_title' => 'Add product',
            'page_description' => 'Create a product record for use across warehouses and sales.',
            'active_nav' => 'products',
            'product' => NULL,
            'categories' => $this->categories->get_all(),
            'form_action' => 'products/store',
            'submit_label' => 'Create product',
            'save_error' => '',
        );

        $this->render('products/form', $data);
    }

    public function store()
    {
        $this->require_post();
        $this->current_product_id = NULL;

        if (!$this->validate_product()) {
            return $this->render_form(NULL, 'products/store', 'Create product');
        }

        if (!$this->products->create($this->product_payload())) {
            $this->log_database_error('creating a product');
            return $this->render_form(NULL, 'products/store', 'Create product', 'The product could not be saved. Please try again.');
        }

        $this->session->set_flashdata('success', 'Product created successfully.');
        redirect('products');
    }

    public function edit($id)
    {
        $product = $this->find_or_404($id);

        $data = array(
            'page_title' => 'Edit product',
            'page_description' => 'Update catalog information without changing historical invoice prices.',
            'active_nav' => 'products',
            'product' => $product,
            'categories' => $this->categories->get_all(),
            'form_action' => 'products/update/'.$product->id,
            'submit_label' => 'Save changes',
            'save_error' => '',
        );

        $this->render('products/form', $data);
    }

    public function update($id)
    {
        $this->require_post();
        $product = $this->find_or_404($id);
        $this->current_product_id = (int) $product->id;

        if (!$this->validate_product()) {
            return $this->render_form($product, 'products/update/'.$product->id, 'Save changes');
        }

        if (!$this->products->update($product->id, $this->product_payload())) {
            $this->log_database_error('updating product '.$product->id);
            return $this->render_form($product, 'products/update/'.$product->id, 'Save changes', 'The product could not be updated. Please try again.');
        }

        $this->session->set_flashdata('success', 'Product updated successfully.');
        redirect('products');
    }

    public function toggle_status($id)
    {
        $this->require_post();
        $product = $this->find_or_404($id);

        if (!$this->products->toggle_status($product->id)) {
            $this->log_database_error('changing product status for '.$product->id);
            $this->session->set_flashdata('error', 'The product status could not be changed.');
            redirect('products');
        }

        $status = $product->is_active ? 'disabled' : 'enabled';
        $this->session->set_flashdata('success', 'Product '.$product->code.' was '.$status.'.');
        redirect('products');
    }

    private function validate_product()
    {
        $category_exists = array('category_exists', function ($category_id) {
            return $this->is_positive_integer($category_id)
                && $this->categories->exists((int) $category_id);
        });
        $product_code_unique = array('product_code_unique', function ($code) {
            return !$this->products->code_exists(trim($code), $this->current_product_id);
        });

        $this->form_validation->set_message('category_exists', 'Please select a valid category.');
        $this->form_validation->set_message('product_code_unique', 'The product code is already in use.');
        $this->form_validation->set_rules('category_id', 'Category', array('trim', 'required', 'integer', $category_exists));
        $this->form_validation->set_rules('code', 'Product code', array('trim', 'required', 'max_length[100]', $product_code_unique));
        $this->form_validation->set_rules('name', 'Product name', 'trim|required|max_length[200]');
        $this->form_validation->set_rules('price', 'Price', 'trim|required|numeric|greater_than_equal_to[0]|less_than_equal_to[9999999999.99]');
        $this->form_validation->set_rules('alert_quantity', 'Alert quantity', 'trim|required|integer|greater_than_equal_to[0]|less_than_equal_to[2147483647]');

        return $this->form_validation->run();
    }

    private function product_payload()
    {
        return array(
            'category_id' => (int) $this->input->post('category_id'),
            'code' => strtoupper(trim((string) $this->input->post('code'))),
            'name' => trim((string) $this->input->post('name')),
            'price' => number_format((float) $this->input->post('price'), 2, '.', ''),
            'alert_quantity' => (int) $this->input->post('alert_quantity'),
        );
    }

    private function render_form($product, $form_action, $submit_label, $save_error = '')
    {
        $data = array(
            'page_title' => $product ? 'Edit product' : 'Add product',
            'page_description' => $product
                ? 'Update catalog information without changing historical invoice prices.'
                : 'Create a product record for use across warehouses and sales.',
            'active_nav' => 'products',
            'product' => $product,
            'categories' => $this->categories->get_all(),
            'form_action' => $form_action,
            'submit_label' => $submit_label,
            'save_error' => $save_error,
        );

        $this->output->set_status_header(422);
        $this->render('products/form', $data);
    }

    private function build_pagination($current_page, $total_pages, $search, $category_id)
    {
        if ($total_pages <= 1) {
            return array();
        }

        $items = array();

        if ($current_page > 1) {
            $items[] = array(
                'type' => 'link',
                'label' => 'Previous',
                'url' => $this->product_page_url($current_page - 1, $search, $category_id),
                'current' => FALSE,
            );
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
                'url' => $this->product_page_url($page, $search, $category_id),
                'current' => $page === $current_page,
            );
            $previous_page = $page;
        }

        if ($current_page < $total_pages) {
            $items[] = array(
                'type' => 'link',
                'label' => 'Next',
                'url' => $this->product_page_url($current_page + 1, $search, $category_id),
                'current' => FALSE,
            );
        }

        return $items;
    }

    private function product_page_url($page, $search, $category_id)
    {
        $query = array();

        if ($search !== '') {
            $query['q'] = $search;
        }

        if ($category_id > 0) {
            $query['category_id'] = (int) $category_id;
        }

        if ($page > 1) {
            $query['page'] = (int) $page;
        }

        $url = site_url('products');

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

        $product = $this->products->find((int) $id);

        if (!$product) {
            show_404();
        }

        return $product;
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
