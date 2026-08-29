<?php
/**
 * Trainer Controller
 */

require_once __DIR__ . '/../models/Trainer.php';
require_once __DIR__ . '/../middleware/TrainerMiddleware.php';

class TrainerController {
    private Trainer $trainerModel;

    public function __construct() {
        $this->trainerModel = new Trainer();
    }

    public function dashboard(): void {
        TrainerMiddleware::handle();
        require_once __DIR__ . '/../views/trainer/index.php';
    }
}
