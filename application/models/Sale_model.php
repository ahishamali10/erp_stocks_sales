<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sale_model extends CI_Model
{
    private $maximum_total_cents = 99999999999999;

    public function count_filtered($search, $warehouse_id)
    {
        $this->base_history_query($search, $warehouse_id);

        return (int) $this->db->count_all_results();
    }

    public function get_filtered($search, $warehouse_id, $limit, $offset)
    {
        $this->db
            ->select('s.id, s.invoice_number, s.subtotal, s.discount_percentage, s.discount_amount, s.total, s.created_at')
            ->select('c.name AS customer_name')
            ->select('w.name AS warehouse_name, w.code AS warehouse_code')
            ->select('u.name AS user_name')
            ->select('(SELECT COUNT(*) FROM sale_items si WHERE si.sale_id = s.id) AS line_count', FALSE)
            ->select('(SELECT COALESCE(SUM(si.quantity), 0) FROM sale_items si WHERE si.sale_id = s.id) AS total_quantity', FALSE);

        $this->base_history_query($search, $warehouse_id);

        return $this->db
            ->order_by('s.created_at', 'DESC')
            ->order_by('s.id', 'DESC')
            ->limit((int) $limit, (int) $offset)
            ->get()
            ->result();
    }

    public function get_history_summary($search, $warehouse_id)
    {
        $this->db
            ->select('COUNT(*) AS invoice_count', FALSE)
            ->select('COALESCE(SUM(s.total), 0) AS sales_total', FALSE)
            ->select('COALESCE(SUM(s.discount_amount), 0) AS discount_total', FALSE);
        $this->base_history_query($search, $warehouse_id);
        $summary = $this->db->get()->row();

        $this->db->select('COALESCE(SUM(si.quantity), 0) AS unit_count', FALSE);
        $this->base_history_query($search, $warehouse_id);
        $units = $this->db
            ->join('sale_items si', 'si.sale_id = s.id', 'inner')
            ->get()
            ->row();

        return array(
            'invoice_count' => (int) $summary->invoice_count,
            'sales_total' => $summary->sales_total,
            'discount_total' => $summary->discount_total,
            'unit_count' => (int) $units->unit_count,
        );
    }

    public function find_authorized($sale_id, $warehouse_id)
    {
        $this->db
            ->select('s.id, s.invoice_number, s.subtotal, s.discount_percentage, s.discount_amount, s.total, s.created_at')
            ->select('c.id AS customer_id, c.name AS customer_name, c.phone AS customer_phone, c.email AS customer_email')
            ->select('w.id AS warehouse_id, w.name AS warehouse_name, w.code AS warehouse_code')
            ->select('u.name AS user_name, u.email AS user_email')
            ->from('sales s')
            ->join('customers c', 'c.id = s.customer_id', 'inner')
            ->join('warehouses w', 'w.id = s.warehouse_id', 'inner')
            ->join('users u', 'u.id = s.user_id', 'left')
            ->where('s.id', (int) $sale_id);

        if ((int) $warehouse_id > 0) {
            $this->db->where('s.warehouse_id', (int) $warehouse_id);
        }

        return $this->db->limit(1)->get()->row();
    }

    public function get_items($sale_id)
    {
        return $this->db
            ->select('si.id, si.product_id, si.quantity, si.unit_price, si.subtotal')
            ->select('p.code AS product_code, p.name AS product_name')
            ->from('sale_items si')
            ->join('products p', 'p.id = si.product_id', 'inner')
            ->where('si.sale_id', (int) $sale_id)
            ->order_by('si.id', 'ASC')
            ->get()
            ->result();
    }

    public function create_invoice($customer_id, $warehouse_id, $user_id, $discount_percentage, $discount_basis, array $lines)
    {
        // A shared lock order keeps competing invoices from taking inventory locks in opposite sequences.
        ksort($lines, SORT_NUMERIC);
        $this->db->trans_begin();

        if (!$this->record_exists('customers', $customer_id)) {
            return $this->rollback('The selected customer is no longer available.');
        }

        $warehouse = $this->db
            ->select('id, code, is_active')
            ->from('warehouses')
            ->where('id', (int) $warehouse_id)
            ->limit(1)
            ->get()
            ->row();

        if (!$warehouse || !$warehouse->is_active) {
            return $this->rollback('The selected warehouse is not available for sales.');
        }

        $items = array();
        $subtotal_cents = 0;
        $lock_sql = 'SELECT p.id, p.code, p.name, p.price, p.is_active, wp.quantity '
            .'FROM warehouse_products wp '
            .'INNER JOIN products p ON p.id = wp.product_id '
            .'WHERE wp.warehouse_id = ? AND wp.product_id = ? '
            .'FOR UPDATE';

        foreach ($lines as $product_id => $quantity) {
            $product = $this->db
                ->query($lock_sql, array((int) $warehouse_id, (int) $product_id))
                ->row();

            if (!$product) {
                return $this->rollback('A selected product is not stocked in this warehouse.');
            }

            if (!$product->is_active) {
                return $this->rollback('Product '.$product->code.' is disabled and cannot be sold.');
            }

            if ((int) $quantity > (int) $product->quantity) {
                return $this->rollback(
                    'Insufficient stock for product '.$product->code.'. Available quantity: '.(int) $product->quantity.'.'
                );
            }

            $unit_price_cents = $this->money_to_cents($product->price);

            if ($unit_price_cents === FALSE || ($unit_price_cents > 0 && (int) $quantity > intdiv($this->maximum_total_cents, $unit_price_cents))) {
                return $this->rollback('The invoice total exceeds the supported monetary limit.');
            }

            $line_subtotal_cents = $unit_price_cents * (int) $quantity;

            if ($subtotal_cents > $this->maximum_total_cents - $line_subtotal_cents) {
                return $this->rollback('The invoice total exceeds the supported monetary limit.');
            }

            $subtotal_cents += $line_subtotal_cents;
            $items[] = array(
                'product_id' => (int) $product->id,
                'product_code' => $product->code,
                'quantity' => (int) $quantity,
                'unit_price' => $this->format_cents($unit_price_cents),
                'subtotal' => $this->format_cents($line_subtotal_cents),
            );
        }

        $discount_amount_cents = intdiv(($subtotal_cents * (int) $discount_basis) + 5000, 10000);
        $total_cents = $subtotal_cents - $discount_amount_cents;
        $this->db->set('invoice_number', "CONCAT('TMP-', UUID())", FALSE);
        $this->db->set(array(
            'customer_id' => (int) $customer_id,
            'warehouse_id' => (int) $warehouse_id,
            'user_id' => $user_id === NULL ? NULL : (int) $user_id,
            'subtotal' => $this->format_cents($subtotal_cents),
            'discount_percentage' => $discount_percentage,
            'discount_amount' => $this->format_cents($discount_amount_cents),
            'total' => $this->format_cents($total_cents),
        ));

        if (!$this->db->insert('sales')) {
            return $this->rollback_database_error('inserting the invoice header');
        }

        $sale_id = (int) $this->db->insert_id();
        $invoice_date = $this->db
            ->select("DATE_FORMAT(created_at, '%Y%m%d') AS invoice_date", FALSE)
            ->from('sales')
            ->where('id', $sale_id)
            ->limit(1)
            ->get()
            ->row();

        if (!$invoice_date) {
            return $this->rollback_database_error('reading the invoice timestamp');
        }

        // The inserted ID makes the final number deterministic and unique; its date uses the same database timestamp as created_at.
        $invoice_number = 'INV-'.$invoice_date->invoice_date.'-'.str_pad((string) $sale_id, 6, '0', STR_PAD_LEFT);

        if (!$this->db->where('id', $sale_id)->update('sales', array('invoice_number' => $invoice_number))) {
            return $this->rollback_database_error('finalizing the invoice number');
        }

        foreach ($items as $item) {
            if (!$this->db->insert('sale_items', array(
                'sale_id' => $sale_id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $item['subtotal'],
            ))) {
                return $this->rollback_database_error('inserting invoice item '.$item['product_code']);
            }

            $deduct_sql = 'UPDATE warehouse_products '
                .'SET quantity = quantity - ? '
                .'WHERE warehouse_id = ? AND product_id = ? AND quantity >= ?';

            if (!$this->db->query($deduct_sql, array(
                $item['quantity'],
                (int) $warehouse_id,
                $item['product_id'],
                $item['quantity'],
            )) || $this->db->affected_rows() !== 1) {
                return $this->rollback('Stock changed while the invoice was being saved. Please review the quantities and try again.');
            }
        }

        if ($this->db->trans_status() === FALSE || !$this->db->trans_commit()) {
            return $this->rollback_database_error('committing the invoice');
        }

        return array(
            'success' => TRUE,
            'sale_id' => $sale_id,
            'invoice_number' => $invoice_number,
            'subtotal' => $this->format_cents($subtotal_cents),
            'discount_percentage' => $discount_percentage,
            'discount_amount' => $this->format_cents($discount_amount_cents),
            'total' => $this->format_cents($total_cents),
        );
    }

    private function record_exists($table, $id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->count_all_results($table) === 1;
    }

    private function base_history_query($search, $warehouse_id)
    {
        $this->db
            ->from('sales s')
            ->join('customers c', 'c.id = s.customer_id', 'inner')
            ->join('warehouses w', 'w.id = s.warehouse_id', 'inner')
            ->join('users u', 'u.id = s.user_id', 'left');

        if ($search !== '') {
            $this->db
                ->group_start()
                ->like('s.invoice_number', $search)
                ->or_like('c.name', $search)
                ->group_end();
        }

        if ((int) $warehouse_id > 0) {
            $this->db->where('s.warehouse_id', (int) $warehouse_id);
        }
    }

    private function money_to_cents($value)
    {
        $value = (string) $value;

        if (!preg_match('/^(\d+)(?:\.(\d{1,2}))?$/', $value, $matches)) {
            return FALSE;
        }

        $fraction = isset($matches[2]) ? str_pad($matches[2], 2, '0') : '00';

        return ((int) $matches[1] * 100) + (int) $fraction;
    }

    private function format_cents($cents)
    {
        return intdiv((int) $cents, 100).'.'.str_pad((string) ((int) $cents % 100), 2, '0', STR_PAD_LEFT);
    }

    private function rollback($message)
    {
        $this->db->trans_rollback();

        return array('success' => FALSE, 'message' => $message);
    }

    private function rollback_database_error($context)
    {
        $error = $this->db->error();
        log_message('error', 'Database error while '.$context.': '.$error['code'].' '.$error['message']);

        return $this->rollback('The invoice could not be saved. No stock was changed. Please try again.');
    }
}
