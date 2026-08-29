<?php
/**
 * Workout Service
 */

require_once __DIR__ . '/../models/Workout.php';

class WorkoutService {
    private Workout $workoutModel;

    public function __construct() {
        $this->workoutModel = new Workout();
    }

    public function getMemberWorkoutPlans(int $memberId): array {
        return $this->workoutModel->getPlansByMember($memberId);
    }
}
