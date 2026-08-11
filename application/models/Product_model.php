<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model
{
    private $table = 'products';

    public function count_filtered($search, $category_id)
    {
        $this->db->from($this->table.' p');
        $this->apply_filters($search, $category_id);

        return (int) $this->db->count_all_results();
    }

    public function get_filtered($search, $category_id, $limit, $offset)
    {
        $this->db
            ->select('p.id, p.category_id, p.code, p.name, p.price, p.alert_quantity, p.is_active, p.created_at, p.updated_at, c.name AS category_name')
            ->from($this->table.' p')
            ->join('categories c', 'c.id = p.category_id', 'inner');

        $this->apply_filters($search, $category_id);

        return $this->db
            ->order_by('p.id', 'DESC')
            ->limit((int) $limit, (int) $offset)
            ->get()
            ->result();
    }

    public function find($id)
    {
        return $this->db
            ->select('id, category_id, code, name, price, alert_quantity, is_active, created_at, updated_at')
            ->from($this->table)
            ->where('id', (int) $id)
            ->limit(1)
            ->get()
            ->row();
    }

    public function code_exists($code, $exclude_id)
    {
        $this->db
            ->from($this->table)
            ->where('code', trim($code));

        if ($exclude_id !== NULL) {
            $this->db->where('id !=', (int) $exclude_id);
        }

        return $this->db->count_all_results() > 0;
    }

    public function create($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update($this->table, $data);
    }

    public function toggle_status($id)
    {
        $product = $this->find($id);

        if (!$product) {
            return FALSE;
        }

        return $this->update($id, array(
            'is_active' => $product->is_active ? 0 : 1,
        ));
    }

    public function get_dashboard_summary()
    {
        $row = $this->db
            ->select('COUNT(*) AS total_products', FALSE)
            ->select('COALESCE(SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END), 0) AS active_products', FALSE)
            ->select('COALESCE(SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END), 0) AS inactive_products', FALSE)
            ->from($this->table)
            ->get()
            ->row();

        return array(
            'total_products' => (int) $row->total_products,
            'active_products' => (int) $row->active_products,
            'inactive_products' => (int) $row->inactive_products,
        );
    }

    public function get_recent($limit)
    {
        return $this->db
            ->select('p.id, p.code, p.name, p.price, p.is_active, c.name AS category_name')
            ->from($this->table.' p')
            ->join('categories c', 'c.id = p.category_id', 'inner')
            ->order_by('p.id', 'DESC')
            ->limit((int) $limit)
            ->get()
            ->result();
    }

    private function apply_filters($search, $category_id)
    {
        if ($search !== '') {
            $this->db
                ->group_start()
                ->like('p.name', $search)
                ->or_like('p.code', $search)
                ->group_end();
        }

        if ($category_id > 0) {
            $this->db->where('p.category_id', (int) $category_id);
        }
    }
}
