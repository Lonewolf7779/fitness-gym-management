<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/security.php';
require_once __DIR__ . '/../app/services/GymManagementService.php';
require_once __DIR__ . '/../app/middleware/AuthMiddleware.php';
header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) session_start();
try {
  AuthMiddleware::handle();
  $svc=new GymManagementService(); $action=$_GET['action']??'';
  if ($_SERVER['REQUEST_METHOD']==='GET') {
    switch($action){
      case 'members': echo json_encode(['success'=>true,'data'=>$svc->members($_GET['q']??'',$_GET['status']??'')]); break;
      case 'plans': echo json_encode(['success'=>true,'data'=>$svc->plans(!empty($_GET['active_only']))]); break;
      case 'trainers': echo json_encode(['success'=>true,'data'=>$svc->trainers()]); break;
      case 'attendance': echo json_encode(['success'=>true,'data'=>$svc->attendance($_GET['date']??'')]); break;
      case 'payments': echo json_encode(['success'=>true,'data'=>$svc->payments()]); break;
      case 'exercises': echo json_encode(['success'=>true,'data'=>$svc->exercises()]); break;
      case 'workouts': echo json_encode(['success'=>true,'data'=>$svc->workouts()]); break;
      case 'reports': echo json_encode(['success'=>true,'data'=>$svc->reportStats()]); break;
      default: http_response_code(404); echo json_encode(['success'=>false,'message'=>'Unknown action']);
    } exit;
  }
  if ($_SERVER['REQUEST_METHOD']!=='POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit; }
  if (!verifyCsrfToken($_POST['csrf_token']??'')) { http_response_code(419); echo json_encode(['success'=>false,'message'=>'Invalid CSRF token']); exit; }
  switch($action){
    case 'create_member': $id=$svc->createMember($_POST); break;
    case 'update_member': $svc->updateMember((int)$_POST['id'],$_POST); $id=(int)$_POST['id']; break;
    case 'create_plan': $id=$svc->savePlan($_POST); break;
    case 'update_plan': $id=$svc->savePlan($_POST,(int)$_POST['id']); break;
    case 'toggle_plan': $svc->togglePlan((int)$_POST['id']); $id=(int)$_POST['id']; break;
    case 'create_trainer': $id=$svc->createTrainer($_POST); break;
    case 'update_trainer': $svc->updateTrainer((int)$_POST['id'],$_POST); $id=(int)$_POST['id']; break;
    case 'check_in': $svc->checkIn((int)$_POST['member_id'],$_POST['status']??'present'); $id=1; break;
    case 'check_out': $svc->checkOut((int)$_POST['attendance_id']); $id=(int)$_POST['attendance_id']; break;
    case 'create_payment': $id=$svc->createPayment($_POST); break;
    case 'create_workout': $id=$svc->createWorkout($_POST); break;
    case 'add_workout_exercise': $id=$svc->addWorkoutExercise($_POST); break;
    default: http_response_code(404); echo json_encode(['success'=>false,'message'=>'Unknown action']); exit;
  }
  echo json_encode(['success'=>true,'id'=>$id,'message'=>'Operation completed successfully']);
} catch (Throwable $e) { http_response_code(400); echo json_encode(['success'=>false,'message'=>$e->getMessage()]); }
