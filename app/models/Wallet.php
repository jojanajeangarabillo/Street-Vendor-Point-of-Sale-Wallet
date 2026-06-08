<?php

class Wallet extends Model {
    /**
     * Get vendor's wallet details
     */
    public function getWalletByVendor($vendorId) {
        $this->db->query('SELECT * FROM wallets WHERE vendor_id = :vendor_id LIMIT 1');
        $this->db->bind(':vendor_id', $vendorId);
        return $this->db->single();
    }

    /**
     * Store new wallet information (public key only)
     */
    public function saveWallet($data) {
        $this->db->query('INSERT INTO wallets (vendor_id, stellar_public_key, wallet_type, network) VALUES (:vendor_id, :public_key, :wallet_type, :network)');
        
        $this->db->bind(':vendor_id', $data['vendor_id']);
        $this->db->bind(':public_key', $data['public_key']);
        $this->db->bind(':wallet_type', $data['wallet_type']);
        $this->db->bind(':network', $data['network']);

        return $this->db->execute();
    }
}
