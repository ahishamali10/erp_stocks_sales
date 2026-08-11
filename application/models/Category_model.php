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
