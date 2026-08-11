<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	public function index()
	{
		$this->load->model('Product_model', 'products');
		$this->load->model('Category_model', 'categories');
		$this->load->model('Stock_model', 'stock');
		$this->load->model('Warehouse_model', 'warehouses');
		$this->load->model('Customer_model', 'customers');

		$data = array(
			'page_title' => 'Dashboard',
			'page_description' => 'A concise operational view of the product catalog and upcoming ERP modules.',
			'active_nav' => 'dashboard',
			'product_summary' => $this->products->get_dashboard_summary(),
			'category_count' => $this->categories->count_all(),
			'inventory_summary' => $this->stock->get_summary(),
			'warehouse_summary' => $this->warehouses->get_summary(),
			'customer_count' => $this->customers->count_all(),
			'recent_products' => $this->products->get_recent(5),
		);

		$this->load->view('layouts/header', $data);
		$this->load->view('home/index');
		$this->load->view('layouts/footer');
	}
}
