<?php
/**
 * Attendance Service
 */

require_once __DIR__ . '/../models/Attendance.php';

class AttendanceService {
    private Attendance $attendanceModel;

    public function __construct() {
        $this->attendanceModel = new Attendance();
    }

    public function getTodayAttendanceCount(): int {
        return $this->attendanceModel->getTodayCount();
    }
}
