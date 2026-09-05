<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/security.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'member') { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Member access required.']); exit; }
try {
  $db=Database::getInstance(); $stmt=$db->prepare('SELECT id FROM members WHERE user_id=? LIMIT 1'); $stmt->execute([(int)$_SESSION['user_id']]); $memberId=(int)($stmt->fetchColumn()?:0);
  if(!$memberId){http_response_code(404);echo json_encode(['success'=>false,'message'=>'Member profile not found.']);exit;}
  if($_SERVER['REQUEST_METHOD']==='POST'){
    $body=json_decode(file_get_contents('php://input'),true) ?: $_POST;
    if(!validateCsrfToken($body['csrf_token']??null)){http_response_code(419);echo json_encode(['success'=>false,'message'=>'Security token expired. Refresh and try again.']);exit;}
    $action=$body['action']??'';
    if($action==='save_progress'){
      $date=trim((string)($body['log_date']??date('Y-m-d'))); $d=DateTimeImmutable::createFromFormat('Y-m-d',$date);
      if(!$d||$d->format('Y-m-d')!==$date||$date>date('Y-m-d')) throw new RuntimeException('Progress date must be a valid date up to today.');
      $numeric=['weight_kg'=>[20,500],'body_fat_pct'=>[1,80],'chest_cm'=>[30,250],'waist_cm'=>[30,250],'biceps_cm'=>[10,100]]; $values=[];
      foreach($numeric as $field=>$range){$v=($body[$field]??'')===''?null:(float)$body[$field];if($v!==null&&($v<$range[0]||$v>$range[1]))throw new RuntimeException('Invalid '.$field.' value.');$values[$field]=$v;}
      $stmt=$db->prepare('INSERT INTO progress_logs(member_id,log_date,weight_kg,body_fat_pct,chest_cm,waist_cm,biceps_cm,notes) VALUES(?,?,?,?,?,?,?,?)');$stmt->execute([$memberId,$date,$values['weight_kg'],$values['body_fat_pct'],$values['chest_cm'],$values['waist_cm'],$values['biceps_cm'],trim((string)($body['notes']??''))?:null]);
      echo json_encode(['success'=>true,'message'=>'Progress log saved.']);exit;
    }
    if($action==='check_in'){
      $stmt=$db->prepare('SELECT id FROM attendance WHERE member_id=? AND date=CURDATE() LIMIT 1');$stmt->execute([$memberId]);if($stmt->fetchColumn())throw new RuntimeException('You are already checked in today.');
      $stmt=$db->prepare("INSERT INTO attendance(member_id,date,check_in_time,status) VALUES(?,CURDATE(),CURTIME(),'present')");$stmt->execute([$memberId]);echo json_encode(['success'=>true,'message'=>'Check-in recorded.']);exit;
    }
    if($action==='check_out'){
      $stmt=$db->prepare('SELECT id,check_out_time FROM attendance WHERE member_id=? AND date=CURDATE() LIMIT 1');$stmt->execute([$memberId]);$row=$stmt->fetch();if(!$row)throw new RuntimeException('No check-in found for today.');if($row['check_out_time'])throw new RuntimeException('You are already checked out today.');$stmt=$db->prepare('UPDATE attendance SET check_out_time=CURTIME() WHERE id=?');$stmt->execute([(int)$row['id']]);echo json_encode(['success'=>true,'message'=>'Check-out recorded.']);exit;
    }
    throw new RuntimeException('Unknown member action.');
  }
  $stmt=$db->prepare('SELECT * FROM progress_logs WHERE member_id=? ORDER BY log_date DESC,id DESC LIMIT 12');$stmt->execute([$memberId]);$progress=$stmt->fetchAll();
  $stmt=$db->prepare('SELECT date,check_in_time,check_out_time,status FROM attendance WHERE member_id=? ORDER BY date DESC LIMIT 30');$stmt->execute([$memberId]);$attendance=$stmt->fetchAll();
  $stmt=$db->prepare('SELECT id,date,check_in_time,check_out_time FROM attendance WHERE member_id=? AND date=CURDATE() LIMIT 1');$stmt->execute([$memberId]);$today=$stmt->fetch()?:null;
  echo json_encode(['success'=>true,'csrf_token'=>generateCsrfToken(),'progress'=>$progress,'attendance'=>$attendance,'today'=>$today]);
} catch(Throwable $e){http_response_code(400);if(defined('STORAGE_PATH'))error_log('['.date('Y-m-d H:i:s').'] Member API: '.$e->getMessage().PHP_EOL,3,STORAGE_PATH.'/logs/app.log');echo json_encode(['success'=>false,'message'=>APP_DEBUG?$e->getMessage():'Request could not be completed.']);}
