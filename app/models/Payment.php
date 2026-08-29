<?php
/**
 * Payment Model
 */

require_once __DIR__ . '/../config/database.php';

class Payment {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getMonthlyRevenue(): float {
        $startOfMonth = date('Y-m-01 00:00:00');
        $stmt = $this->db->prepare("SELECT SUM(amount) FROM payments WHERE status = 'paid' AND payment_date >= :start_date");
        $stmt->execute(['start_date' => $startOfMonth]);
        return (float) ($stmt->fetchColumn() ?: 0.00);
    }
}
