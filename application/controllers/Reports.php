<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Report_model', 'reports');
        $this->load->model('Warehouse_model', 'warehouses');
    }

    public function low_stock()
    {
        $filters = $this->resolve_filters();
        $search = $filters['search'];
        $warehouse_id = $filters['warehouse_id'];

        $per_page = 15;
        $total_rows = $this->reports->count_low_stock($search, $warehouse_id);
        $total_pages = max(1, (int) ceil($total_rows / $per_page));
        $page = $this->positive_integer($this->input->get('page'));
        $page = $page > 0 ? min($page, $total_pages) : 1;
        $offset = ($page - 1) * $per_page;

        $data = array(
            'page_title' => 'Low-stock report',
            'page_description' => 'Identify inventory positions at or below their configured alert quantity.',
            'active_nav' => 'reports',
            'rows' => $this->reports->get_low_stock($search, $warehouse_id, $per_page, $offset),
            'summary' => $this->reports->get_low_stock_summary($search, $warehouse_id),
            'warehouses' => $this->authorized_warehouses(),
            'search' => $search,
            'warehouse_id' => $warehouse_id,
            'is_admin' => $this->is_admin(),
            'pagination' => $this->build_pagination($page, $total_pages, $search, $warehouse_id),
            'total_rows' => $total_rows,
            'result_from' => $total_rows > 0 ? $offset + 1 : 0,
            'result_to' => min($offset + $per_page, $total_rows),
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('reports/low_stock', $data);
        $this->load->view('layouts/footer', $data);
    }

    public function low_stock_csv()
    {
        if ($this->input->method(TRUE) !== 'GET') {
            show_error('The requested method is not allowed.', 405, 'Method Not Allowed');
        }

        $filters = $this->resolve_filters();
        $rows = $this->reports->get_low_stock_export($filters['search'], $filters['warehouse_id']);
        $stream = fopen('php://temp', 'w+');

        if ($stream === FALSE) {
            log_message('error', 'Unable to open a temporary stream for the low-stock CSV export.');
            show_error('The report could not be exported. Please try again.', 500, 'Export Error');
        }

        fputcsv($stream, array('Product Code', 'Product Name', 'Warehouse', 'Quantity', 'Alert Quantity', 'Shortage'));

        foreach ($rows as $row) {
            fputcsv($stream, array(
                $this->safe_csv_text($row->product_code),
                $this->safe_csv_text($row->product_name),
                $this->safe_csv_text($row->warehouse_name.' ('.$row->warehouse_code.')'),
                (int) $row->quantity,
                (int) $row->alert_quantity,
                (int) $row->shortage,
            ));
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $this->output
            ->set_content_type('text/csv', 'utf-8')
            ->set_header('Content-Disposition: attachment; filename="low-stock-'.date('Ymd-His').'.csv"')
            ->set_header('Cache-Control: no-store, no-cache, must-revalidate')
            ->set_output($csv);
    }

    private function resolve_filters()
    {
        $search_input = $this->input->get('q');
        $search = is_scalar($search_input) ? substr(trim((string) $search_input), 0, 200) : '';
        $requested_warehouse_id = $this->positive_integer($this->input->get('warehouse_id'));

        if (!$this->is_admin()) {
            if ($requested_warehouse_id > 0 && !$this->can_access_warehouse($requested_warehouse_id)) {
                $this->deny_access('You do not have permission to report on that warehouse.');
            }

            $warehouse_id = (int) $this->current_user['warehouse_id'];
        } else {
            $warehouse_id = $requested_warehouse_id;

            if ($warehouse_id > 0 && !$this->warehouses->exists($warehouse_id)) {
                $warehouse_id = 0;
            }
        }

        return array('search' => $search, 'warehouse_id' => $warehouse_id);
    }

    private function safe_csv_text($value)
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@]/', $value) ? "'".$value : $value;
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

        $url = site_url('reports/low-stock');

        return array(
            'type' => 'link',
            'label' => $label,
            'url' => empty($query) ? $url : $url.'?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986),
            'current' => $current,
        );
    }

    private function positive_integer($value)
    {
        return is_scalar($value) && ctype_digit((string) $value) && (int) $value > 0 ? (int) $value : 0;
    }
}
