<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Report_model extends CI_Model
{
    public function count_low_stock($search, $warehouse_id)
    {
        $this->base_low_stock_query($search, $warehouse_id);

        return (int) $this->db->count_all_results();
    }

    public function get_low_stock($search, $warehouse_id, $limit, $offset)
    {
        $this->db
            ->select('p.id AS product_id, p.code AS product_code, p.name AS product_name, p.alert_quantity, p.is_active')
            ->select('w.id AS warehouse_id, w.name AS warehouse_name, w.code AS warehouse_code')
            ->select('COALESCE(wp.quantity, 0) AS quantity', FALSE)
            ->select('GREATEST(p.alert_quantity - COALESCE(wp.quantity, 0), 0) AS shortage', FALSE);

        $this->base_low_stock_query($search, $warehouse_id);

        return $this->db
            ->order_by('shortage', 'DESC')
            ->order_by('p.name', 'ASC')
            ->order_by('w.name', 'ASC')
            ->limit((int) $limit, (int) $offset)
            ->get()
            ->result();
    }

    public function get_low_stock_export($search, $warehouse_id)
    {
        $this->db
            ->select('p.code AS product_code, p.name AS product_name, p.alert_quantity')
            ->select('w.name AS warehouse_name, w.code AS warehouse_code')
            ->select('COALESCE(wp.quantity, 0) AS quantity', FALSE)
            ->select('GREATEST(p.alert_quantity - COALESCE(wp.quantity, 0), 0) AS shortage', FALSE);

        $this->base_low_stock_query($search, $warehouse_id);

        return $this->db
            ->order_by('shortage', 'DESC')
            ->order_by('p.name', 'ASC')
            ->order_by('w.name', 'ASC')
            ->get()
            ->result();
    }

    public function get_low_stock_summary($search, $warehouse_id)
    {
        $this->db
            ->select('COUNT(*) AS position_count', FALSE)
            ->select('COUNT(DISTINCT w.id) AS warehouse_count', FALSE)
            ->select('COALESCE(SUM(GREATEST(p.alert_quantity - COALESCE(wp.quantity, 0), 0)), 0) AS total_shortage', FALSE);

        $this->base_low_stock_query($search, $warehouse_id);
        $row = $this->db->get()->row();

        return array(
            'position_count' => (int) $row->position_count,
            'warehouse_count' => (int) $row->warehouse_count,
            'total_shortage' => (int) $row->total_shortage,
        );
    }

    private function base_low_stock_query($search, $warehouse_id)
    {
        $this->db
            ->from('products p')
            ->join('warehouses w', '1 = 1', 'inner', FALSE)
            ->join('warehouse_products wp', 'wp.product_id = p.id AND wp.warehouse_id = w.id', 'left', FALSE)
            ->where('COALESCE(wp.quantity, 0) <= p.alert_quantity', NULL, FALSE);

        if ($search !== '') {
            $this->db
                ->group_start()
                ->like('p.name', $search)
                ->or_like('p.code', $search)
                ->group_end();
        }

        if ((int) $warehouse_id > 0) {
            $this->db->where('w.id', (int) $warehouse_id);
        }
    }
}
