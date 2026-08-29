<?php
/**
 * Payment Controller
 */

require_once __DIR__ . '/../services/PaymentService.php';

class PaymentController {
    private PaymentService $paymentService;

    public function __construct() {
        $this->paymentService = new PaymentService();
    }
}
