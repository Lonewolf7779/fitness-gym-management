<?php
/**
 * Payment Service
 */

require_once __DIR__ . '/../models/Payment.php';

class PaymentService {
    private Payment $paymentModel;

    public function __construct() {
        $this->paymentModel = new Payment();
    }

    public function getMonthlyRevenue(): float {
        return $this->paymentModel->getMonthlyRevenue();
    }
}
