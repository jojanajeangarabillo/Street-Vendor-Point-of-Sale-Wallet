<?php

class PaymentRequest extends Model {
    /**
     * Create a new payment request
     */
    public function create($data) {
        $this->db->query('INSERT INTO payment_requests (vendor_id, amount, description, payment_reference, status, expires_at, qr_data) VALUES (:vendor_id, :amount, :description, :payment_reference, :status, :expires_at, :qr_data)');
        
        $this->db->bind(':vendor_id', $data['vendor_id']);
        $this->db->bind(':amount', $data['amount']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':payment_reference', $data['payment_reference']);
        $this->db->bind(':status', 'pending');
        $this->db->bind(':expires_at', $data['expires_at']);
        $this->db->bind(':qr_data', $data['destination_address']); // Using qr_data to store destination address

        return $this->db->execute();
    }

    /**
     * Get request by reference
     */
    public function getByReference($reference) {
        $this->db->query('SELECT * FROM payment_requests WHERE payment_reference = :reference');
        $this->db->bind(':reference', $reference);
        return $this->db->single();
    }

    /**
     * Get all pending requests for a vendor
     */
    public function getPendingByVendor($vendorId) {
        $this->db->query('SELECT * FROM payment_requests WHERE vendor_id = :vendor_id AND status = "pending" ORDER BY created_at DESC');
        $this->db->bind(':vendor_id', $vendorId);
        return $this->db->resultSet();
    }

    /**
     * Update status to completed
     */
    public function markAsPaid($id) {
        $this->db->query('UPDATE payment_requests SET status = "completed" WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Update status to expired
     */
    public function markAsExpired($id) {
        $this->db->query('UPDATE payment_requests SET status = "expired" WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
