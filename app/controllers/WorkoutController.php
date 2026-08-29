<?php
/**
 * Workout Controller
 */

require_once __DIR__ . '/../services/WorkoutService.php';

class WorkoutController {
    private WorkoutService $workoutService;

    public function __construct() {
        $this->workoutService = new WorkoutService();
    }
}
