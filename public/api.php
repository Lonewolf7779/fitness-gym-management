<?php
/**
 * IRONCORE Unified Secure JSON API Gateway
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/security.php';
require_once __DIR__ . '/../app/services/GymManagementService.php';
require_once __DIR__ . '/../app/middleware/AuthMiddleware.php';

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // 1. Authentication Check
    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentication required.']);
        exit;
    }

    $role = strtolower($_SESSION['role'] ?? '');
    $userId = (int) ($_SESSION['user_id'] ?? 0);

    $svc = new GymManagementService();
    $action = $_GET['action'] ?? '';

    // =========================================================================
    // GET ENDPOINTS (Data Fetching)
    // =========================================================================
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        switch ($action) {
            // Admin only actions
            case 'members':
                if ($role !== 'admin' && $role !== 'trainer') {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
                    exit;
                }
                echo json_encode(['success' => true, 'data' => $svc->members($_GET['q'] ?? '', $_GET['status'] ?? '')]);
                break;

            case 'plans':
                echo json_encode(['success' => true, 'data' => $svc->plans(!empty($_GET['active_only']))]);
                break;

            case 'trainers':
                echo json_encode(['success' => true, 'data' => $svc->trainers($_GET['q'] ?? '', $_GET['status'] ?? '')]);
                break;

            case 'attendance':
                if ($role !== 'admin' && $role !== 'trainer') {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
                    exit;
                }
                echo json_encode(['success' => true, 'data' => $svc->attendance($_GET['filter'] ?? $_GET['date'] ?? '')]);
                break;

            case 'attendance_stats':
                if ($role !== 'admin' && $role !== 'trainer') {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
                    exit;
                }
                echo json_encode(['success' => true, 'data' => $svc->attendanceModuleStats()]);
                break;

            case 'payments':
                if ($role !== 'admin') {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Administrator privileges required.']);
                    exit;
                }
                echo json_encode(['success' => true, 'data' => $svc->payments($_GET['q'] ?? '', $_GET['status'] ?? '')]);
                break;

            case 'payment_stats':
                if ($role !== 'admin') {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Administrator privileges required.']);
                    exit;
                }
                echo json_encode(['success' => true, 'data' => $svc->paymentModuleStats()]);
                break;

            case 'member_stats':
                if ($role !== 'admin') {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Administrator privileges required.']);
                    exit;
                }
                echo json_encode(['success' => true, 'data' => $svc->memberModuleStats()]);
                break;

            case 'trainer_stats':
                if ($role !== 'admin') {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Administrator privileges required.']);
                    exit;
                }
                echo json_encode(['success' => true, 'data' => $svc->trainerModuleStats()]);
                break;

            case 'membership_stats':
                if ($role !== 'admin') {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Administrator privileges required.']);
                    exit;
                }
                echo json_encode(['success' => true, 'data' => $svc->membershipModuleStats()]);
                break;

            case 'trainer_exercises':
                if ($role !== 'admin' && $role !== 'trainer') {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
                    exit;
                }
                if ($role === 'trainer') {
                    $tr = $svc->getTrainerByUserId($userId);
                    echo json_encode(['success' => true, 'data' => $tr ? $svc->trainerExercises((int)$tr['id']) : []]);
                } else {
                    $trainerId = !empty($_GET['trainer_id']) ? (int)$_GET['trainer_id'] : null;
                    echo json_encode(['success' => true, 'data' => $svc->trainerExercises($trainerId)]);
                }
                break;

            case 'exercises':
                echo json_encode(['success' => true, 'data' => $svc->exercises()]);
                break;

            case 'workouts':
                if ($role !== 'admin' && $role !== 'trainer') {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
                    exit;
                }
                $trainerFilterId = null;
                if ($role === 'trainer') {
                    $tr = $svc->getTrainerByUserId($userId);
                    $trainerFilterId = $tr ? (int) $tr['id'] : -1;
                }
                echo json_encode(['success' => true, 'data' => $svc->workouts($trainerFilterId)]);
                break;

            case 'workout_stats':
                if ($role !== 'admin' && $role !== 'trainer') {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
                    exit;
                }
                $trainerFilterId = null;
                if ($role === 'trainer') {
                    $tr = $svc->getTrainerByUserId($userId);
                    $trainerFilterId = $tr ? (int) $tr['id'] : -1;
                }
                echo json_encode(['success' => true, 'data' => $svc->workoutModuleStats($trainerFilterId)]);
                break;

            case 'workout_details':
                if ($role !== 'admin' && $role !== 'trainer' && $role !== 'member') {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
                    exit;
                }
                $wid = (int) ($_GET['id'] ?? 0);
                $details = $svc->getWorkoutDetails($wid);
                if (!$details) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Workout program not found.']);
                    exit;
                }
                if ($role === 'member') {
                    $mem = $svc->getMemberByUserId($userId);
                    if (!$mem || (int) $details['member_id'] !== (int) $mem['id']) {
                        http_response_code(403);
                        echo json_encode(['success' => false, 'message' => 'Forbidden. You cannot view another athlete\'s workout protocol.']);
                        exit;
                    }
                } elseif ($role === 'trainer') {
                    $tr = $svc->getTrainerByUserId($userId);
                    if (!$tr || ((int) $details['trainer_id'] !== (int) $tr['id'] && !$svc->isTrainerAuthorizedForMember($userId, (int) $details['member_id']))) {
                        http_response_code(403);
                        echo json_encode(['success' => false, 'message' => 'Forbidden. You are not authorized to view this workout program.']);
                        exit;
                    }
                }
                echo json_encode(['success' => true, 'data' => $details]);
                break;

            case 'reports':
                if ($role !== 'admin') {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Administrator privileges required.']);
                    exit;
                }
                echo json_encode(['success' => true, 'data' => $svc->reportStats()]);
                break;

            case 'dashboard_stats':
                if ($role !== 'admin') {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Administrator privileges required.']);
                    exit;
                }
                echo json_encode(['success' => true, 'data' => $svc->dashboardStats()]);
                break;

            case 'chart_revenue':
                if ($role !== 'admin') {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Administrator privileges required.']);
                    exit;
                }
                echo json_encode(['success' => true, 'data' => $svc->monthlyRevenueChart()]);
                break;

            case 'chart_attendance':
                if ($role !== 'admin') {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Administrator privileges required.']);
                    exit;
                }
                echo json_encode(['success' => true, 'data' => $svc->weeklyAttendanceChart()]);
                break;

            case 'chart_trends':
            case 'performance_trends':
                if ($role !== 'admin') {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Administrator privileges required.']);
                    exit;
                }
                $range = isset($_GET['range']) ? (int)$_GET['range'] : 12;
                echo json_encode(['success' => true, 'data' => $svc->performanceTrends($range)]);
                break;

            default:
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Unknown action.']);
                break;
        }
        exit;
    }

    // =========================================================================
    // POST ENDPOINTS (Data Mutation)
    // =========================================================================
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
        exit;
    }

    // CSRF Protection Validation
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        http_response_code(419);
        echo json_encode(['success' => false, 'message' => 'Invalid or expired security token.']);
        exit;
    }

    switch ($action) {
        // Admin Only CRUD
        case 'create_member':
            if ($role !== 'admin') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $id = $svc->createMember($_POST);
            break;

        case 'update_member':
            if ($role !== 'admin') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $svc->updateMember((int) $_POST['id'], $_POST);
            $id = (int) $_POST['id'];
            break;

        case 'create_plan':
            if ($role !== 'admin') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $id = $svc->savePlan($_POST);
            break;

        case 'update_plan':
            if ($role !== 'admin') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $id = $svc->savePlan($_POST, (int) $_POST['id']);
            break;

        case 'toggle_plan':
            if ($role !== 'admin') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $svc->togglePlan((int) $_POST['id']);
            $id = (int) $_POST['id'];
            break;

        case 'delete_plan':
            if ($role !== 'admin') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $svc->deletePlan((int) $_POST['id']);
            $id = (int) $_POST['id'];
            break;

        case 'create_trainer':
            if ($role !== 'admin') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $id = $svc->createTrainer($_POST);
            break;

        case 'update_trainer':
            if ($role !== 'admin') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $svc->updateTrainer((int) $_POST['id'], $_POST);
            $id = (int) $_POST['id'];
            break;

        case 'check_in':
            if ($role !== 'admin' && $role !== 'trainer') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $memberId = (int) ($_POST['member_id'] ?? 0);
            if ($role === 'trainer' && !$svc->isTrainerAuthorizedForMember($userId, $memberId)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Forbidden. You are not authorized to check in this athlete.']);
                exit;
            }
            $id = $svc->checkIn($memberId, $_POST['status'] ?? 'present');
            break;

        case 'check_out':
            if ($role !== 'admin' && $role !== 'trainer') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $attendanceId = (int) ($_POST['attendance_id'] ?? 0);
            if ($role === 'trainer' && !$svc->isTrainerAuthorizedForAttendance($userId, $attendanceId)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Forbidden. You are not authorized to check out this attendance record.']);
                exit;
            }
            $svc->checkOut($attendanceId);
            $id = (int) $_POST['attendance_id'];
            break;

        case 'check_out_member':
            if ($role !== 'admin' && $role !== 'trainer') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $memberId = (int) ($_POST['member_id'] ?? 0);
            if ($role === 'trainer' && !$svc->isTrainerAuthorizedForMember($userId, $memberId)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Forbidden. You are not authorized to check out this athlete.']);
                exit;
            }
            $svc->checkOutByMemberId($memberId);
            $id = $memberId;
            break;

        case 'create_payment':
            if ($role !== 'admin') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $id = $svc->createPayment($_POST);
            break;

        // Admin & Trainer Workout Management
        case 'assign_exercise_to_trainer':
            if ($role !== 'admin') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Administrator privileges required.']); exit; }
            $trainerId = (int) ($_POST['trainer_id'] ?? 0);
            $exerciseId = (int) ($_POST['exercise_id'] ?? 0);
            $id = $svc->assignExerciseToTrainer($trainerId, $exerciseId, $userId);
            break;

        case 'remove_exercise_from_trainer':
            if ($role !== 'admin') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Administrator privileges required.']); exit; }
            $svc->removeExerciseFromTrainer((int) ($_POST['trainer_id'] ?? 0), (int) ($_POST['exercise_id'] ?? 0));
            $id = (int) ($_POST['trainer_id'] ?? 0);
            break;

        case 'create_workout':
            if ($role !== 'admin' && $role !== 'trainer') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $targetMemberId = (int) ($_POST['member_id'] ?? 0);
            if ($role === 'trainer' && !$svc->isTrainerAuthorizedForMember($userId, $targetMemberId)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Forbidden. You are not authorized to assign workouts to this athlete.']);
                exit;
            }
            $id = $svc->createWorkout($_POST);
            break;

        case 'create_workout_plan':
            if ($role !== 'admin' && $role !== 'trainer') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $workoutData = $_POST;
            $targetMemberId = (int) ($workoutData['member_id'] ?? 0);
            if ($role === 'trainer') {
                if (!$svc->isTrainerAuthorizedForMember($userId, $targetMemberId)) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Forbidden. You are not authorized to assign workouts to this athlete.']);
                    exit;
                }
                if (empty($workoutData['trainer_id'])) {
                    $tr = $svc->getTrainerByUserId($userId);
                    if ($tr) {
                        $workoutData['trainer_id'] = (int) $tr['id'];
                    }
                }
            }
            if ($role === 'trainer' && !empty($_POST['exercises']) && is_array($_POST['exercises'])) {
                foreach ($_POST['exercises'] as $ex) {
                    if (!empty($ex['exercise_id']) && !$svc->isExerciseAssignedToTrainer($userId, (int)$ex['exercise_id'])) {
                        http_response_code(403);
                        echo json_encode(['success' => false, 'message' => 'One or more selected exercises have not been assigned to you by an administrator.']);
                        exit;
                    }
                }
            }
            $planId = $svc->createWorkout($workoutData);
            if (!empty($_POST['exercises']) && is_array($_POST['exercises'])) {
                foreach ($_POST['exercises'] as $ex) {
                    if (!empty($ex['exercise_id'])) {
                        $exerciseId = (int) $ex['exercise_id'];
                        $svc->addWorkoutExercise([
                            'plan_id'      => $planId,
                            'exercise_id'  => $exerciseId,
                            'sets'         => (int) ($ex['sets'] ?? 3),
                            'reps'         => (string) ($ex['reps'] ?? '10-12'),
                            'rest_seconds' => (int) ($ex['rest_seconds'] ?? 60),
                            'day_of_week'  => (string) ($ex['day_of_week'] ?? 'Mon')
                        ]);
                    }
                }
            }
            $id = $planId;
            break;

        case 'add_workout_exercise':
            if ($role !== 'admin' && $role !== 'trainer') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $planId = (int) ($_POST['plan_id'] ?? 0);
            $exerciseId = (int) ($_POST['exercise_id'] ?? 0);
            if ($role === 'trainer' && !$svc->isExerciseAssignedToTrainer($userId, $exerciseId)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'This exercise has not been assigned to you by an administrator.']);
                exit;
            }
            if ($role === 'trainer' && !$svc->isTrainerAuthorizedForWorkout($userId, $planId)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Forbidden. You are not authorized to modify this workout program.']);
                exit;
            }
            $id = $svc->addWorkoutExercise($_POST);
            break;

        // User Self-Service Profile & Password
        case 'update_profile':
            $svc->updateUserProfile($userId, $_POST);
            if (!empty($_POST['full_name'])) $_SESSION['full_name'] = trim($_POST['full_name']);
            if (!empty($_POST['email'])) $_SESSION['email'] = trim($_POST['email']);
            if (!empty($_POST['username'])) $_SESSION['username'] = trim($_POST['username']);
            $id = $userId;
            break;

        case 'change_password':
            $svc->changeUserPassword($userId, $_POST['current_password'] ?? '', $_POST['new_password'] ?? '');
            $id = $userId;
            break;

        // Member Self-Service
        case 'member_self_checkin':
            if ($role !== 'member') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $id = $svc->memberSelfCheckIn($userId);
            break;

        case 'member_add_progress':
            if ($role !== 'member') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $id = $svc->memberAddProgress($userId, $_POST);
            break;

        // Admin Credential & Entity Management
        case 'reset_password':
            if ($role !== 'admin') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $targetUserId = (int) ($_POST['user_id'] ?? 0);
            $newPassword = $_POST['new_password'] ?? '';
            $svc->resetPassword($targetUserId, $newPassword);
            $id = $targetUserId;
            break;

        case 'set_user_status':
            if ($role !== 'admin') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $targetUserId = (int) ($_POST['user_id'] ?? 0);
            $newStatus = $_POST['status'] ?? 'active';
            $svc->setUserStatus($targetUserId, $newStatus);
            $id = $targetUserId;
            break;

        case 'delete_member':
            if ($role !== 'admin') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $svc->deleteMember((int) $_POST['id']);
            $id = (int) $_POST['id'];
            break;

        case 'delete_trainer':
            if ($role !== 'admin') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $svc->deleteTrainer((int) $_POST['id']);
            $id = (int) $_POST['id'];
            break;

        case 'delete_workout':
            if ($role !== 'admin' && $role !== 'trainer') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $workoutId = (int) $_POST['id'];
            if ($role === 'trainer' && !$svc->isTrainerAuthorizedForWorkout($userId, $workoutId)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Forbidden. You are not authorized to delete this workout program.']);
                exit;
            }
            $svc->deleteWorkout($workoutId);
            $id = $workoutId;
            break;

        case 'delete_attendance':
            if ($role !== 'admin' && $role !== 'trainer') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $attendanceId = (int) ($_POST['id'] ?? 0);
            if ($role === 'trainer' && !$svc->isTrainerAuthorizedForAttendance($userId, $attendanceId)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Forbidden. You are not authorized to delete this attendance record.']);
                exit;
            }
            $svc->deleteAttendance($attendanceId);
            $id = (int) $_POST['id'];
            break;

        case 'delete_payment':
            if ($role !== 'admin') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $svc->deletePayment((int) $_POST['id']);
            $id = (int) $_POST['id'];
            break;

        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Unknown action.']);
            exit;
    }

    echo json_encode(['success' => true, 'id' => $id, 'message' => 'Operation completed successfully.']);

} catch (Throwable $e) {
    if (defined('STORAGE_PATH')) {
        error_log("[" . date('Y-m-d H:i:s') . "] API Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n", 3, STORAGE_PATH . '/logs/app.log');
    }
    $status = 400;
    $msg = $e->getMessage();
    if (!APP_DEBUG && ($e instanceof PDOException || strpos($msg, 'SQLSTATE') !== false)) {
        $msg = 'A database error occurred. Please contact system administrator.';
        $status = 500;
    }
    http_response_code($status);
    echo json_encode(['success' => false, 'message' => $msg]);
}
