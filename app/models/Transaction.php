<?php

class Transaction extends Model {
    /**
     * Record a new confirmed transaction
     */
    public function record($data) {
        $this->db->query('INSERT INTO transactions (vendor_id, payment_request_id, stellar_transaction_hash, sender_address, receiver_address, amount, asset_code, status, confirmed_at) 
                          VALUES (:vendor_id, :payment_request_id, :hash, :sender, :receiver, :amount, :asset, "confirmed", :confirmed_at)');
        
        $this->db->bind(':vendor_id', $data['vendor_id']);
        $this->db->bind(':payment_request_id', $data['payment_request_id']);
        $this->db->bind(':hash', $data['hash']);
        $this->db->bind(':sender', $data['sender']);
        $this->db->bind(':receiver', $data['receiver']);
        $this->db->bind(':amount', $data['amount']);
        $this->db->bind(':asset', $data['asset'] ?? 'XLM');
        $this->db->bind(':confirmed_at', $data['confirmed_at']);

        return $this->db->execute();
    }

    /**
     * Get transactions by vendor
     */
    public function getByVendor($vendorId) {
        $this->db->query('SELECT t.*, pr.payment_reference FROM transactions t 
                          LEFT JOIN payment_requests pr ON t.payment_request_id = pr.id 
                          WHERE t.vendor_id = :vendor_id 
                          ORDER BY t.created_at DESC');
        $this->db->bind(':vendor_id', $vendorId);
        return $this->db->resultSet();
    }
}
