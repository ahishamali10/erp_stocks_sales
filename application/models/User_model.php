<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
    private $table = 'users';

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
            ->select('u.id, u.warehouse_id, u.name, u.email, u.role')
            ->select('w.name AS warehouse_name, w.code AS warehouse_code, w.is_active AS warehouse_is_active')
            ->from($this->table.' u')
            ->join('warehouses w', 'w.id = u.warehouse_id', 'left')
            ->where('u.id', (int) $id)
            ->limit(1)
            ->get()
            ->row();
    }
}
