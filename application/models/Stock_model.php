<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_model extends CI_Model
{
    public function count_filtered($search, $warehouse_id)
    {
        $this->db
            ->from('products p')
            ->join('warehouses w', '1 = 1', 'inner', FALSE);

        $this->apply_filters($search, $warehouse_id);

        return (int) $this->db->count_all_results();
    }

    public function get_filtered($search, $warehouse_id, $limit, $offset)
    {
        $this->db
            ->select('p.id AS product_id, p.code AS product_code, p.name AS product_name, p.alert_quantity, p.is_active')
            ->select('c.name AS category_name')
            ->select('w.id AS warehouse_id, w.name AS warehouse_name, w.code AS warehouse_code')
            ->select('COALESCE(wp.quantity, 0) AS quantity', FALSE)
            ->from('products p')
            ->join('categories c', 'c.id = p.category_id', 'inner')
            ->join('warehouses w', '1 = 1', 'inner', FALSE)
            ->join('warehouse_products wp', 'wp.product_id = p.id AND wp.warehouse_id = w.id', 'left', FALSE);

        $this->apply_filters($search, $warehouse_id);

        return $this->db
            ->order_by('p.name', 'ASC')
            ->order_by('w.name', 'ASC')
            ->limit((int) $limit, (int) $offset)
            ->get()
            ->result();
    }

    public function get_summary($warehouse_id = 0)
    {
        $this->db
            ->select('COUNT(DISTINCT w.id) AS warehouse_count', FALSE)
            ->select('COUNT(DISTINCT p.id) AS product_count', FALSE)
            ->select('COALESCE(SUM(COALESCE(wp.quantity, 0)), 0) AS total_units', FALSE)
            ->select('COALESCE(SUM(CASE WHEN COALESCE(wp.quantity, 0) <= p.alert_quantity THEN 1 ELSE 0 END), 0) AS low_stock_count', FALSE)
            ->from('products p')
            ->join('warehouses w', '1 = 1', 'inner', FALSE)
            ->join('warehouse_products wp', 'wp.product_id = p.id AND wp.warehouse_id = w.id', 'left', FALSE);

        if ($warehouse_id > 0) {
            $this->db->where('w.id', (int) $warehouse_id);
        }

        $row = $this->db->get()->row();

        return array(
            'warehouse_count' => (int) $row->warehouse_count,
            'product_count' => (int) $row->product_count,
            'total_units' => (int) $row->total_units,
            'low_stock_count' => (int) $row->low_stock_count,
        );
    }

    public function find_inventory($warehouse_id, $product_id)
    {
        return $this->db
            ->select('p.id AS product_id, p.code AS product_code, p.name AS product_name, p.alert_quantity, p.is_active')
            ->select('c.name AS category_name')
            ->select('w.id AS warehouse_id, w.name AS warehouse_name, w.code AS warehouse_code')
            ->select('COALESCE(wp.quantity, 0) AS quantity', FALSE)
            ->from('products p')
            ->join('categories c', 'c.id = p.category_id', 'inner')
            ->join('warehouses w', '1 = 1', 'inner', FALSE)
            ->join('warehouse_products wp', 'wp.product_id = p.id AND wp.warehouse_id = w.id', 'left', FALSE)
            ->where('p.id', (int) $product_id)
            ->where('w.id', (int) $warehouse_id)
            ->limit(1)
            ->get()
            ->row();
    }

    public function set_quantity($warehouse_id, $product_id, $quantity)
    {
        $sql = 'INSERT INTO warehouse_products (warehouse_id, product_id, quantity) '
            .'VALUES (?, ?, ?) '
            .'ON DUPLICATE KEY UPDATE quantity = ?';

        return $this->db->query($sql, array(
            (int) $warehouse_id,
            (int) $product_id,
            (int) $quantity,
            (int) $quantity,
        ));
    }

    private function apply_filters($search, $warehouse_id)
    {
        if ($search !== '') {
            $this->db
                ->group_start()
                ->like('p.name', $search)
                ->or_like('p.code', $search)
                ->group_end();
        }

        if ($warehouse_id > 0) {
            $this->db->where('w.id', (int) $warehouse_id);
        }
    }
}
