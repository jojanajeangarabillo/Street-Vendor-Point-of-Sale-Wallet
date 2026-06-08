<?php
class Vendor extends Model {
    /**
     * Find vendor by email
     */
    public function findVendorByEmail($email) {
        $this->db->query('SELECT * FROM vendors WHERE email = :email');
        $this->db->bind(':email', $email);

        $row = $this->db->single();

        // Check row
        if ($this->db->rowCount() > 0) {
            return $row;
        } else {
            return false;
        }
    }

    /**
     * Register vendor
     */
    public function register($data) {
        $this->db->query('INSERT INTO vendors (full_name, email, password_hash, phone) VALUES (:full_name, :email, :password_hash, :phone)');
        
        // Bind values
        $this->db->bind(':full_name', $data['full_name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':password_hash', $data['password_hash']);
        $this->db->bind(':phone', $data['phone']);

        // Execute
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Login vendor
     */
    public function login($email, $password) {
        $this->db->query('SELECT * FROM vendors WHERE email = :email');
        $this->db->bind(':email', $email);

        $row = $this->db->single();

        if ($row) {
            $hashed_password = $row->password_hash;
            if (password_verify($password, $hashed_password)) {
                return $row;
            }
        }
        return false;
    }

    /**
     * Get vendor by ID
     */
    public function getVendorById($id) {
        $this->db->query('SELECT * FROM vendors WHERE id = :id');
        $this->db->bind(':id', $id);

        $row = $this->db->single();
        return $row;
    }
}
