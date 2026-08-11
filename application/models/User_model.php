<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
    private $table = 'users';

    public function count_filtered($search, $role)
    {
        $this->db->from($this->table.' u');
        $this->apply_filters($search, $role);

        return (int) $this->db->count_all_results();
    }

    public function get_filtered($search, $role, $limit, $offset)
    {
        $this->db
            ->select('u.id, u.warehouse_id, u.name, u.email, u.role, u.created_at')
            ->select('w.name AS warehouse_name, w.code AS warehouse_code, w.is_active AS warehouse_is_active')
            ->select('(SELECT COUNT(*) FROM sales s WHERE s.user_id = u.id) AS sale_count', FALSE)
            ->from($this->table.' u')
            ->join('warehouses w', 'w.id = u.warehouse_id', 'left');

        $this->apply_filters($search, $role);

        return $this->db
            ->order_by('u.name', 'ASC')
            ->limit((int) $limit, (int) $offset)
            ->get()
            ->result();
    }

    public function get_summary()
    {
        $row = $this->db
            ->select('COUNT(*) AS total_count', FALSE)
            ->select("COALESCE(SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END), 0) AS admin_count", FALSE)
            ->select("COALESCE(SUM(CASE WHEN role = 'user_warehouse' THEN 1 ELSE 0 END), 0) AS warehouse_user_count", FALSE)
            ->select('COUNT(DISTINCT warehouse_id) AS assigned_warehouse_count', FALSE)
            ->from($this->table)
            ->get()
            ->row();

        return array(
            'total_count' => (int) $row->total_count,
            'admin_count' => (int) $row->admin_count,
            'warehouse_user_count' => (int) $row->warehouse_user_count,
            'assigned_warehouse_count' => (int) $row->assigned_warehouse_count,
        );
    }

    public function find_by_email($email)
    {
        return $this->db
            ->select('u.id, u.warehouse_id, u.name, u.email, u.password, u.role')
            ->select('w.name AS warehouse_name, w.code AS warehouse_code, w.is_active AS warehouse_is_active')
            ->from($this->table.' u')
            ->join('warehouses w', 'w.id = u.warehouse_id', 'left')
            ->where('u.email', strtolower(trim($email)))
            ->limit(1)
            ->get()
            ->row();
    }

    public function find($id)
    {
        return $this->db
            ->select('u.id, u.warehouse_id, u.name, u.email, u.role, u.created_at')
            ->select('w.name AS warehouse_name, w.code AS warehouse_code, w.is_active AS warehouse_is_active')
            ->from($this->table.' u')
            ->join('warehouses w', 'w.id = u.warehouse_id', 'left')
            ->where('u.id', (int) $id)
            ->limit(1)
            ->get()
            ->row();
    }

    public function email_exists($email, $exclude_id = NULL)
    {
        $this->db
            ->from($this->table)
            ->where('email', strtolower(trim($email)));

        if ($exclude_id !== NULL) {
            $this->db->where('id !=', (int) $exclude_id);
        }

        return $this->db->count_all_results() > 0;
    }

    public function create($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function update_with_admin_guard($id, $data)
    {
        $this->db->trans_begin();
        $user = $this->db->query(
            'SELECT id, role FROM users WHERE id = ? FOR UPDATE',
            array((int) $id)
        )->row();

        if (!$user) {
            $this->db->trans_rollback();
            return array('success' => FALSE, 'code' => 'not_found');
        }

        if ($user->role === 'admin' && $data['role'] !== 'admin') {
            $admins = $this->db->query(
                "SELECT id FROM users WHERE role = 'admin' ORDER BY id ASC FOR UPDATE"
            )->result();

            if (count($admins) <= 1) {
                $this->db->trans_rollback();
                return array('success' => FALSE, 'code' => 'last_admin');
            }
        }

        if (!$this->db->where('id', (int) $id)->update($this->table, $data)) {
            $this->db->trans_rollback();
            return array('success' => FALSE, 'code' => 'database');
        }

        if ($this->db->trans_status() === FALSE || !$this->db->trans_commit()) {
            $this->db->trans_rollback();
            return array('success' => FALSE, 'code' => 'database');
        }

        return array('success' => TRUE);
    }

    public function delete_with_guards($id, $current_user_id)
    {
        $this->db->trans_begin();
        $user = $this->db->query(
            'SELECT id, role FROM users WHERE id = ? FOR UPDATE',
            array((int) $id)
        )->row();

        if (!$user) {
            $this->db->trans_rollback();
            return array('success' => FALSE, 'code' => 'not_found');
        }

        if ((int) $user->id === (int) $current_user_id) {
            $this->db->trans_rollback();
            return array('success' => FALSE, 'code' => 'self');
        }

        if ($user->role === 'admin') {
            $admins = $this->db->query(
                "SELECT id FROM users WHERE role = 'admin' ORDER BY id ASC FOR UPDATE"
            )->result();

            if (count($admins) <= 1) {
                $this->db->trans_rollback();
                return array('success' => FALSE, 'code' => 'last_admin');
            }
        }

        $sale_count = (int) $this->db
            ->where('user_id', (int) $id)
            ->count_all_results('sales');

        if ($sale_count > 0) {
            $this->db->trans_rollback();
            return array('success' => FALSE, 'code' => 'has_sales');
        }

        if (!$this->db->where('id', (int) $id)->delete($this->table)) {
            $this->db->trans_rollback();
            return array('success' => FALSE, 'code' => 'database');
        }

        if ($this->db->trans_status() === FALSE || !$this->db->trans_commit()) {
            $this->db->trans_rollback();
            return array('success' => FALSE, 'code' => 'database');
        }

        return array('success' => TRUE);
    }

    private function apply_filters($search, $role)
    {
        if ($search !== '') {
            $this->db
                ->group_start()
                ->like('u.name', $search)
                ->or_like('u.email', $search)
                ->group_end();
        }

        if (in_array($role, array('admin', 'user_warehouse'), TRUE)) {
            $this->db->where('u.role', $role);
        }
    }
}
