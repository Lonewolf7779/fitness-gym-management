<?php
/**
 * Progress Controller
 */

require_once __DIR__ . '/../models/Progress.php';

class ProgressController {
    private Progress $progressModel;

    public function __construct() {
        $this->progressModel = new Progress();
    }
}
