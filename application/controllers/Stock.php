<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stock extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Warehouse_model', 'warehouses');
        $this->load->model('Stock_model', 'stock');
    }

    public function index()
    {
        $search_input = $this->input->get('q');
        $search = is_scalar($search_input) ? trim((string) $search_input) : '';
        $search = substr($search, 0, 200);
        $warehouse_id = $this->positive_integer($this->input->get('warehouse_id'));

        if ($warehouse_id > 0 && !$this->warehouses->exists($warehouse_id)) {
            $warehouse_id = 0;
        }

        $per_page = 10;
        $total_rows = $this->stock->count_filtered($search, $warehouse_id);
        $total_pages = max(1, (int) ceil($total_rows / $per_page));
        $page = $this->positive_integer($this->input->get('page'));
        $page = $page > 0 ? min($page, $total_pages) : 1;
        $offset = ($page - 1) * $per_page;

        $data = array(
            'page_title' => 'Inventory',
            'page_description' => 'Monitor and adjust product quantities across warehouses.',
            'active_nav' => 'stock',
            'inventory_rows' => $this->stock->get_filtered($search, $warehouse_id, $per_page, $offset),
            'warehouses' => $this->warehouses->get_all(),
            'summary' => $this->stock->get_summary($warehouse_id),
            'search' => $search,
            'warehouse_id' => $warehouse_id,
            'pagination' => $this->build_pagination($page, $total_pages, $search, $warehouse_id),
            'total_rows' => $total_rows,
            'result_from' => $total_rows > 0 ? $offset + 1 : 0,
            'result_to' => min($offset + $per_page, $total_rows),
        );

        $this->render('stock/index', $data);
    }

    public function edit($warehouse_id, $product_id)
    {
        $inventory = $this->find_or_404($warehouse_id, $product_id);

        $this->render('stock/form', array(
            'page_title' => 'Adjust inventory',
            'page_description' => 'Set the trusted on-hand quantity for this product and warehouse.',
            'active_nav' => 'stock',
            'inventory' => $inventory,
            'save_error' => '',
        ));
    }

    public function update($warehouse_id, $product_id)
    {
        $this->require_post();
        $inventory = $this->find_or_404($warehouse_id, $product_id);

        $quantity_input = $this->input->post('quantity');
        $valid_stock_quantity = array('valid_stock_quantity', function ($value) use ($quantity_input) {
            if ($quantity_input === NULL || (is_scalar($quantity_input) && trim((string) $quantity_input) === '')) {
                return TRUE;
            }

            if (!is_scalar($quantity_input)) {
                return FALSE;
            }

            $quantity = trim((string) $value);

            return ctype_digit($quantity)
                && strlen($quantity) <= 10
                && (float) $quantity <= 2147483647;
        });

        $this->form_validation->set_message(
            'valid_stock_quantity',
            'The quantity must be a whole number between 0 and 2147483647.'
        );
        $this->form_validation->set_rules('quantity', 'Quantity', array('trim', 'required', $valid_stock_quantity));

        if (!$this->form_validation->run()) {
            return $this->render_form($inventory);
        }

        $quantity = (int) trim((string) $quantity_input);

        if (!$this->stock->set_quantity($inventory->warehouse_id, $inventory->product_id, $quantity)) {
            $this->log_database_error('updating inventory for warehouse '.$inventory->warehouse_id.' and product '.$inventory->product_id);
            return $this->render_form($inventory, 'The inventory quantity could not be saved. Please try again.');
        }

        $this->session->set_flashdata(
            'success',
            'Inventory updated for '.$inventory->product_code.' at '.$inventory->warehouse_name.'.'
        );
        redirect('stock?warehouse_id='.$inventory->warehouse_id);
    }

    private function render_form($inventory, $save_error = '')
    {
        $this->output->set_status_header(422);
        $this->render('stock/form', array(
            'page_title' => 'Adjust inventory',
            'page_description' => 'Set the trusted on-hand quantity for this product and warehouse.',
            'active_nav' => 'stock',
            'inventory' => $inventory,
            'save_error' => $save_error,
        ));
    }

    private function build_pagination($current_page, $total_pages, $search, $warehouse_id)
    {
        if ($total_pages <= 1) {
            return array();
        }

        $items = array();

        if ($current_page > 1) {
            $items[] = $this->page_link('Previous', $current_page - 1, $search, $warehouse_id, FALSE);
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

            $items[] = $this->page_link((string) $page, $page, $search, $warehouse_id, $page === $current_page);
            $previous_page = $page;
        }

        if ($current_page < $total_pages) {
            $items[] = $this->page_link('Next', $current_page + 1, $search, $warehouse_id, FALSE);
        }

        return $items;
    }

    private function page_link($label, $page, $search, $warehouse_id, $current)
    {
        $query = array();

        if ($search !== '') {
            $query['q'] = $search;
        }

        if ($warehouse_id > 0) {
            $query['warehouse_id'] = (int) $warehouse_id;
        }

        if ($page > 1) {
            $query['page'] = (int) $page;
        }

        $url = site_url('stock');

        return array(
            'type' => 'link',
            'label' => $label,
            'url' => empty($query) ? $url : $url.'?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986),
            'current' => $current,
        );
    }

    private function find_or_404($warehouse_id, $product_id)
    {
        if (!$this->is_positive_integer($warehouse_id) || !$this->is_positive_integer($product_id)) {
            show_404();
        }

        $inventory = $this->stock->find_inventory((int) $warehouse_id, (int) $product_id);

        if (!$inventory) {
            show_404();
        }

        return $inventory;
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
