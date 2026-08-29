<?php
/**
 * Membership Controller
 */

require_once __DIR__ . '/../services/MembershipService.php';

class MembershipController {
    private MembershipService $membershipService;

    public function __construct() {
        $this->membershipService = new MembershipService();
    }

    public function listPlans(): array {
        return $this->membershipService->getLandingPlans();
    }
}
