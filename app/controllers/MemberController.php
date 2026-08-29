<?php
/**
 * Member Controller
 */

require_once __DIR__ . '/../models/Member.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class MemberController {
    private Member $memberModel;

    public function __construct() {
        $this->memberModel = new Member();
    }

    public function dashboard(): void {
        AuthMiddleware::handle();
        $userId = $_SESSION['user_id'];
        $member = $this->memberModel->findByUserId($userId);
        
        // Pass to view template
        require_once __DIR__ . '/../views/member/index.php';
    }
}
