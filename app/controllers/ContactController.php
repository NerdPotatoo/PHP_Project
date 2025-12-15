<?php

namespace App\Controllers;

use App\configs\Database;
use PDO;

class ContactController {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
    }

    /**
     * Get all contacts
     */
    public function getAllContacts($limit = null) {
        try {
            $query = "SELECT * FROM contacts ORDER BY created_at DESC";
            if ($limit) {
                $query .= " LIMIT " . intval($limit);
            }
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching contacts: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get contact by ID
     */
    public function getContactById($id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM contacts WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error fetching contact: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new contact submission
     */
    public function createContact($data) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO contacts (name, email, phone, subject, message, subscribe) 
                                          VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['name'],
                $data['email'],
                $data['phone'] ?? null,
                $data['subject'],
                $data['message'],
                $data['subscribe'] ?? 0
            ]);

            return [
                'success' => true,
                'message' => 'Thank you for contacting us! We will get back to you soon.',
                'id' => $this->conn->lastInsertId()
            ];
        } catch (\PDOException $e) {
            error_log("Error creating contact: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to submit your message. Please try again later.'
            ];
        }
    }

    /**
     * Update contact status
     */
    public function updateContactStatus($id, $status) {
        try {
            $stmt = $this->conn->prepare("UPDATE contacts SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);

            return [
                'success' => true,
                'message' => 'Contact status updated successfully'
            ];
        } catch (\PDOException $e) {
            error_log("Error updating contact status: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete contact
     */
    public function deleteContact($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM contacts WHERE id = ?");
            $stmt->execute([$id]);

            return [
                'success' => true,
                'message' => 'Contact deleted successfully'
            ];
        } catch (\PDOException $e) {
            error_log("Error deleting contact: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to delete contact: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get contact count by status
     */
    public function getContactCountByStatus($status = 'New') {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM contacts WHERE status = ?");
            $stmt->execute([$status]);
            return $stmt->fetch()['count'] ?? 0;
        } catch (\PDOException $e) {
            error_log("Error counting contacts: " . $e->getMessage());
            return 0;
        }
    }
}
