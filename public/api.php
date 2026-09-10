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

            case 'exercises':
                echo json_encode(['success' => true, 'data' => $svc->exercises()]);
                break;

            case 'workouts':
                if ($role !== 'admin' && $role !== 'trainer') {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
                    exit;
                }
                echo json_encode(['success' => true, 'data' => $svc->workouts()]);
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
            $id = $svc->checkIn((int) $_POST['member_id'], $_POST['status'] ?? 'present');
            break;

        case 'check_out':
            if ($role !== 'admin' && $role !== 'trainer') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $svc->checkOut((int) $_POST['attendance_id']);
            $id = (int) $_POST['attendance_id'];
            break;

        case 'create_payment':
            if ($role !== 'admin') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $id = $svc->createPayment($_POST);
            break;

        // Admin & Trainer Workout Management
        case 'create_workout':
            if ($role !== 'admin' && $role !== 'trainer') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $id = $svc->createWorkout($_POST);
            break;

        case 'add_workout_exercise':
            if ($role !== 'admin' && $role !== 'trainer') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $id = $svc->addWorkoutExercise($_POST);
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
            $svc->deleteWorkout((int) $_POST['id']);
            $id = (int) $_POST['id'];
            break;

        case 'delete_attendance':
            if ($role !== 'admin' && $role !== 'trainer') { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden.']); exit; }
            $svc->deleteAttendance((int) $_POST['id']);
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
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
