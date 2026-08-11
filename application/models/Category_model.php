<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Category_model extends CI_Model
{
    private $table = 'categories';

    public function get_all()
    {
        return $this->db
            ->select('id, name')
            ->from($this->table)
            ->order_by('name', 'ASC')
            ->get()
            ->result();
    }

    public function get_with_product_counts()
    {
        return $this->db
            ->select('c.id, c.name, c.created_at, c.updated_at')
            ->select('COUNT(p.id) AS product_count', FALSE)
            ->from($this->table.' c')
            ->join('products p', 'p.category_id = c.id', 'left')
            ->group_by(array('c.id', 'c.name', 'c.created_at', 'c.updated_at'))
            ->order_by('c.name', 'ASC')
            ->get()
            ->result();
    }

    public function find($id)
    {
        return $this->db
            ->select('id, name, created_at, updated_at')
            ->from($this->table)
            ->where('id', (int) $id)
            ->limit(1)
            ->get()
            ->row();
    }

    public function name_exists($name, $exclude_id = NULL)
    {
        $this->db
            ->from($this->table)
            ->where('name', trim($name));

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

    public function count_products($id)
    {
        return (int) $this->db
            ->where('category_id', (int) $id)
            ->count_all_results('products');
    }

    public function delete($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->delete($this->table);
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
