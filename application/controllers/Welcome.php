<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	public function index()
	{
		$data = array(
			'page_title' => 'Dashboard',
		);

		$this->load->view('layouts/header', $data);
		$this->load->view('home/index');
		$this->load->view('layouts/footer');
	}
}
