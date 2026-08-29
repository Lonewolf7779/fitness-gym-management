<?php
/**
 * Attendance Controller
 */

require_once __DIR__ . '/../services/AttendanceService.php';

class AttendanceController {
    private AttendanceService $attendanceService;

    public function __construct() {
        $this->attendanceService = new AttendanceService();
    }

    public function getTodayStats(): int {
        return $this->attendanceService->getTodayAttendanceCount();
    }
}
