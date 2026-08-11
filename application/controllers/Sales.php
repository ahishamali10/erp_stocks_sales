<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sales extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Customer_model', 'customers');
        $this->load->model('Warehouse_model', 'warehouses');
        $this->load->model('Stock_model', 'stock');
        $this->load->model('Sale_model', 'sales');
    }

    public function index()
    {
        redirect('sales/create');
    }

    public function create()
    {
        $this->render('sales/create', array(
            'page_title' => 'New sales invoice',
            'page_description' => 'Build an invoice from trusted product prices and live warehouse stock.',
            'active_nav' => 'sales',
            'customers' => $this->customers->get_all(),
            'warehouses' => $this->warehouses->get_active(),
        ));
    }

    public function search_products()
    {
        if ($this->input->method(TRUE) !== 'GET') {
            return $this->json_response(FALSE, 'The requested method is not allowed.', array(), 405);
        }

        $warehouse_id = $this->positive_integer($this->input->get('warehouse_id'));
        $warehouse = $warehouse_id > 0 ? $this->warehouses->find($warehouse_id) : NULL;

        if (!$warehouse || !$warehouse->is_active) {
            return $this->json_response(FALSE, 'Select an active warehouse before searching products.', array(), 422);
        }

        $search_input = $this->input->get('q');
        $search = is_scalar($search_input) ? substr(trim((string) $search_input), 0, 100) : '';
        $products = array();

        foreach ($this->stock->search_sale_products($warehouse_id, $search, 20) as $product) {
            $products[] = array(
                'id' => (int) $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'price' => $product->price,
                'available_quantity' => (int) $product->available_quantity,
            );
        }

        return $this->json_response(TRUE, '', array('data' => $products));
    }

    public function store()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            if ($this->input->is_ajax_request()) {
                return $this->json_response(FALSE, 'The requested method is not allowed.', array(), 405);
            }

            show_error('The requested method is not allowed.', 405, 'Method Not Allowed');
        }

        $validation = $this->validate_invoice_request();

        if (!$validation['success']) {
            return $this->invoice_error($validation['message'], 422);
        }

        $result = $this->sales->create_invoice(
            $validation['customer_id'],
            $validation['warehouse_id'],
            NULL,
            $validation['discount_percentage'],
            $validation['discount_basis'],
            $validation['lines']
        );

        if (!$result['success']) {
            return $this->invoice_error($result['message'], 409);
        }

        $message = 'Invoice '.$result['invoice_number'].' saved successfully.';

        if ($this->input->is_ajax_request()) {
            return $this->json_response(TRUE, $message, array('data' => array(
                'sale_id' => $result['sale_id'],
                'invoice_number' => $result['invoice_number'],
                'subtotal' => $result['subtotal'],
                'discount_percentage' => $result['discount_percentage'],
                'discount_amount' => $result['discount_amount'],
                'total' => $result['total'],
            )), 201);
        }

        $this->session->set_flashdata('success', $message);
        redirect('sales/create');
    }

    private function validate_invoice_request()
    {
        $customer_id = $this->positive_integer($this->input->post('customer_id'));

        if ($customer_id < 1 || !$this->customers->exists($customer_id)) {
            return $this->validation_failure('Please select a valid customer.');
        }

        $warehouse_id = $this->positive_integer($this->input->post('warehouse_id'));
        $warehouse = $warehouse_id > 0 ? $this->warehouses->find($warehouse_id) : NULL;

        if (!$warehouse || !$warehouse->is_active) {
            return $this->validation_failure('Please select an active warehouse.');
        }

        $discount = $this->parse_percentage($this->input->post('discount_percentage'));

        if (!$discount['success']) {
            return $this->validation_failure('Discount percentage must be between 0 and 100 with at most two decimal places.');
        }

        $product_ids = $this->input->post('product_id');
        $quantities = $this->input->post('quantity');

        if (!is_array($product_ids) || !is_array($quantities) || count($product_ids) < 1 || count($product_ids) !== count($quantities)) {
            return $this->validation_failure('Add at least one valid product line.');
        }

        if (count($product_ids) > 100) {
            return $this->validation_failure('An invoice cannot contain more than 100 submitted lines.');
        }

        $lines = array();

        foreach ($product_ids as $index => $product_id) {
            $quantity = isset($quantities[$index]) ? $quantities[$index] : NULL;

            if (!$this->is_positive_integer($product_id) || !$this->is_positive_integer($quantity)) {
                return $this->validation_failure('Every invoice line must contain a valid product and positive whole-number quantity.');
            }

            $product_id = (int) $product_id;
            $quantity = (int) $quantity;

            if ($quantity > 2147483647) {
                return $this->validation_failure('An invoice line quantity is too large.');
            }

            if (isset($lines[$product_id])) {
                if ($lines[$product_id] > 2147483647 - $quantity) {
                    return $this->validation_failure('The combined quantity for a product is too large.');
                }

                $lines[$product_id] += $quantity;
            } else {
                $lines[$product_id] = $quantity;
            }
        }

        return array(
            'success' => TRUE,
            'customer_id' => $customer_id,
            'warehouse_id' => $warehouse_id,
            'discount_percentage' => $discount['formatted'],
            'discount_basis' => $discount['basis'],
            'lines' => $lines,
        );
    }

    private function parse_percentage($value)
    {
        if ($value === NULL || (is_scalar($value) && trim((string) $value) === '')) {
            return array('success' => TRUE, 'basis' => 0, 'formatted' => '0.00');
        }

        if (!is_scalar($value)) {
            return array('success' => FALSE);
        }

        $value = trim((string) $value);

        if (!preg_match('/^(\d{1,3})(?:\.(\d{1,2}))?$/', $value, $matches)) {
            return array('success' => FALSE);
        }

        $whole = (int) $matches[1];
        $fraction = isset($matches[2]) ? (int) str_pad($matches[2], 2, '0') : 0;
        $basis = ($whole * 100) + $fraction;

        if ($basis > 10000) {
            return array('success' => FALSE);
        }

        return array(
            'success' => TRUE,
            'basis' => $basis,
            'formatted' => intdiv($basis, 100).'.'.str_pad((string) ($basis % 100), 2, '0', STR_PAD_LEFT),
        );
    }

    private function invoice_error($message, $status)
    {
        if ($this->input->is_ajax_request()) {
            return $this->json_response(FALSE, $message, array(), $status);
        }

        $this->session->set_flashdata('error', $message);
        redirect('sales/create');
    }

    private function json_response($success, $message, array $extra = array(), $status = 200)
    {
        $payload = array_merge(array(
            'success' => (bool) $success,
            'message' => $message,
            'csrf' => array(
                'name' => $this->security->get_csrf_token_name(),
                'hash' => $this->security->get_csrf_hash(),
            ),
        ), $extra);

        return $this->output
            ->set_status_header((int) $status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($payload));
    }

    private function validation_failure($message)
    {
        return array('success' => FALSE, 'message' => $message);
    }

    private function positive_integer($value)
    {
        return $this->is_positive_integer($value) ? (int) $value : 0;
    }

    private function is_positive_integer($value)
    {
        return is_scalar($value) && ctype_digit((string) $value) && (int) $value > 0;
    }

    private function render($view, $data)
    {
        $this->load->view('layouts/header', $data);
        $this->load->view($view, $data);
        $this->load->view('layouts/footer', $data);
    }
}
