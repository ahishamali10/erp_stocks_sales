<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_model extends CI_Model
{
    private $table = 'customers';

    public function count_filtered($search)
    {
        $this->db->from($this->table.' c');
        $this->apply_search($search);

        return (int) $this->db->count_all_results();
    }

    public function get_filtered($search, $limit, $offset)
    {
        $this->db
            ->select('c.id, c.name, c.phone, c.email, c.created_at')
            ->select('(SELECT COUNT(*) FROM sales s WHERE s.customer_id = c.id) AS sale_count', FALSE)
            ->select('(SELECT COALESCE(SUM(s.total), 0) FROM sales s WHERE s.customer_id = c.id) AS total_spent', FALSE)
            ->from($this->table.' c');

        $this->apply_search($search);

        return $this->db
            ->order_by('c.id', 'DESC')
            ->limit((int) $limit, (int) $offset)
            ->get()
            ->result();
    }

    public function get_all()
    {
        return $this->db
            ->select('id, name, phone, email')
            ->from($this->table)
            ->order_by('name', 'ASC')
            ->get()
            ->result();
    }

    public function find($id)
    {
        return $this->db
            ->select('id, name, phone, email, created_at')
            ->from($this->table)
            ->where('id', (int) $id)
            ->limit(1)
            ->get()
            ->row();
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

    public function delete($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->delete($this->table);
    }

    public function count_sales($id)
    {
        return (int) $this->db
            ->where('customer_id', (int) $id)
            ->count_all_results('sales');
    }

    public function count_all()
    {
        return (int) $this->db->count_all($this->table);
    }

    private function apply_search($search)
    {
        if ($search === '') {
            return;
        }

        $this->db
            ->group_start()
            ->like('c.name', $search)
            ->or_like('c.phone', $search)
            ->or_like('c.email', $search)
            ->group_end();
    }
}
