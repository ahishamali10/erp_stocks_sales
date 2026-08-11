<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Warehouse_model extends CI_Model
{
    private $table = 'warehouses';

    public function get_all()
    {
        return $this->db
            ->select('id, name, code')
            ->from($this->table)
            ->order_by('name', 'ASC')
            ->get()
            ->result();
    }

    public function find($id)
    {
        return $this->db
            ->select('id, name, code, created_at, updated_at')
            ->from($this->table)
            ->where('id', (int) $id)
            ->limit(1)
            ->get()
            ->row();
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
