<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Warehouse_model extends CI_Model
{
    private $table = 'warehouses';

    public function get_all()
    {
        return $this->db
            ->select('id, name, code, is_active')
            ->from($this->table)
            ->order_by('name', 'ASC')
            ->get()
            ->result();
    }

    public function get_active()
    {
        return $this->db
            ->select('id, name, code')
            ->from($this->table)
            ->where('is_active', 1)
            ->order_by('name', 'ASC')
            ->get()
            ->result();
    }

    public function get_with_counts()
    {
        return $this->db
            ->select('w.id, w.name, w.code, w.is_active, w.created_at, w.updated_at')
            ->select('(SELECT COUNT(*) FROM warehouse_products wp WHERE wp.warehouse_id = w.id) AS inventory_count', FALSE)
            ->select('(SELECT COALESCE(SUM(wp.quantity), 0) FROM warehouse_products wp WHERE wp.warehouse_id = w.id) AS total_units', FALSE)
            ->select('(SELECT COUNT(*) FROM users u WHERE u.warehouse_id = w.id) AS user_count', FALSE)
            ->select('(SELECT COUNT(*) FROM sales s WHERE s.warehouse_id = w.id) AS sale_count', FALSE)
            ->from($this->table.' w')
            ->order_by('w.name', 'ASC')
            ->get()
            ->result();
    }

    public function get_summary($warehouse_id = 0)
    {
        $warehouse_query = $this->db
            ->select('COUNT(*) AS total_count', FALSE)
            ->select('COALESCE(SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END), 0) AS active_count', FALSE)
            ->select('COALESCE(SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END), 0) AS inactive_count', FALSE)
            ->from($this->table);

        if ((int) $warehouse_id > 0) {
            $warehouse_query->where('id', (int) $warehouse_id);
        }

        $row = $warehouse_query
            ->get()
            ->row();

        $inventory_query = $this->db
            ->select('COALESCE(SUM(quantity), 0) AS total_units', FALSE)
            ->from('warehouse_products');

        if ((int) $warehouse_id > 0) {
            $inventory_query->where('warehouse_id', (int) $warehouse_id);
        }

        $total_units = $inventory_query
            ->get()
            ->row();

        return array(
            'total_count' => (int) $row->total_count,
            'active_count' => (int) $row->active_count,
            'inactive_count' => (int) $row->inactive_count,
            'total_units' => (int) $total_units->total_units,
        );
    }

    public function find($id)
    {
        return $this->db
            ->select('id, name, code, is_active, created_at, updated_at')
            ->from($this->table)
            ->where('id', (int) $id)
            ->limit(1)
            ->get()
            ->row();
    }

    public function code_exists($code, $exclude_id = NULL)
    {
        $this->db
            ->from($this->table)
            ->where('code', strtoupper(trim($code)));

        if ($exclude_id !== NULL) {
            $this->db->where('id !=', (int) $exclude_id);
        }

        return $this->db->count_all_results() > 0;
    }

    public function create($data)
    {
        $this->db->trans_begin();

        if (!$this->db->insert($this->table, $data)) {
            $this->db->trans_rollback();
            return FALSE;
        }

        $warehouse_id = (int) $this->db->insert_id();
        $sql = 'INSERT INTO warehouse_products (warehouse_id, product_id, quantity) '
            .'SELECT ?, id, 0 FROM products';

        if (!$this->db->query($sql, array($warehouse_id))) {
            $this->db->trans_rollback();
            return FALSE;
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return FALSE;
        }

        return $this->db->trans_commit();
    }

    public function update($id, $data)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update($this->table, $data);
    }

    public function toggle_status($id)
    {
        $warehouse = $this->find($id);

        if (!$warehouse) {
            return FALSE;
        }

        return $this->update($id, array(
            'is_active' => $warehouse->is_active ? 0 : 1,
        ));
    }

    public function count_assigned_users($id)
    {
        return (int) $this->db
            ->where('warehouse_id', (int) $id)
            ->count_all_results('users');
    }

    public function exists($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->count_all_results($this->table) === 1;
    }

    public function count_all()
    {
        return (int) $this->db->count_all($this->table);
    }
}
