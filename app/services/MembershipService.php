<?php
/**
 * Membership Service
 */

require_once __DIR__ . '/../models/Membership.php';

class MembershipService {
    private Membership $membershipModel;

    public function __construct() {
        $this->membershipModel = new Membership();
    }

    public function getLandingPlans(): array {
        return $this->membershipModel->getActivePlans();
    }
}
