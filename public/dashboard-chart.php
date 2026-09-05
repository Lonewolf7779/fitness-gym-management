<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') { http_response_code(403); echo json_encode(['success'=>false]); exit; }
try {
  $db=Database::getInstance();
  $revenue=$db->query("SELECT DATE_FORMAT(payment_date,'%b') AS label, COALESCE(SUM(amount),0) AS value FROM payments WHERE status='paid' AND payment_date >= DATE_SUB(DATE_FORMAT(CURDATE(),'%Y-%m-01'),INTERVAL 5 MONTH) GROUP BY YEAR(payment_date),MONTH(payment_date) ORDER BY YEAR(payment_date),MONTH(payment_date)")->fetchAll();
  $attendance=$db->query("SELECT DATE_FORMAT(date,'%a') AS label, COUNT(*) AS value FROM attendance WHERE date BETWEEN DATE_SUB(CURDATE(),INTERVAL 6 DAY) AND CURDATE() GROUP BY date ORDER BY date")->fetchAll();
  echo json_encode(['success'=>true,'revenue'=>array_map(fn($r)=>['label'=>$r['label'],'value'=>(float)$r['value']],$revenue),'attendance'=>array_map(fn($r)=>['label'=>$r['label'],'value'=>(int)$r['value']],$attendance)]);
} catch(Throwable $e) { http_response_code(500); echo json_encode(['success'=>false,'message'=>APP_DEBUG?$e->getMessage():'Unable to load chart data.']); }
