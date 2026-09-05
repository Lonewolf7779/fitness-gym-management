<?php
/**
 * IRONCORE live dashboard data endpoint.
 * Read-only dashboard aggregation for the authenticated user.
 */
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

try {
    $db = Database::getInstance();
    $role = strtolower((string) $_SESSION['role']);
    $userId = (int) $_SESSION['user_id'];

    if ($role === 'admin') {
        $stats = [
            'total_members' => (int) $db->query("SELECT COUNT(*) FROM members")->fetchColumn(),
            'active_members' => (int) $db->query("SELECT COUNT(*) FROM members m INNER JOIN users u ON u.id = m.user_id WHERE u.status = 'active'")->fetchColumn(),
            'today_attendance' => (int) $db->query("SELECT COUNT(*) FROM attendance WHERE date = CURDATE()")->fetchColumn(),
            'monthly_revenue' => (float) $db->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status = 'paid' AND payment_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND payment_date < DATE_ADD(LAST_DAY(CURDATE()), INTERVAL 1 DAY)")->fetchColumn()
        ];
        $recent = $db->query("SELECT m.id, u.full_name, u.email, m.join_date, u.status, COALESCE(mp.title, 'No Plan') AS plan_title
            FROM members m INNER JOIN users u ON u.id = m.user_id
            LEFT JOIN subscriptions s ON s.member_id = m.id AND s.id = (SELECT MAX(s2.id) FROM subscriptions s2 WHERE s2.member_id = m.id)
            LEFT JOIN membership_plans mp ON mp.id = s.plan_id
            ORDER BY m.join_date DESC, m.id DESC LIMIT 5")->fetchAll();
        $expiry = $db->query("SELECT u.full_name, mp.title AS plan_title, s.end_date, DATEDIFF(s.end_date, CURDATE()) AS days_left
            FROM subscriptions s INNER JOIN members m ON m.id = s.member_id INNER JOIN users u ON u.id = m.user_id
            INNER JOIN membership_plans mp ON mp.id = s.plan_id
            WHERE s.status = 'active' AND s.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            ORDER BY s.end_date ASC LIMIT 6")->fetchAll();
        echo json_encode(['success' => true, 'role' => $role, 'stats' => $stats, 'recent_members' => $recent, 'expiry' => $expiry]);
        exit;
    }

    if ($role === 'trainer') {
        $stmt = $db->prepare("SELECT id FROM trainers WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $trainerId = (int) ($stmt->fetchColumn() ?: 0);
        if (!$trainerId) {
            echo json_encode(['success' => true, 'role' => $role, 'stats' => ['assigned_clients'=>0,'active_programs'=>0,'sessions'=>0,'attention'=>0], 'clients'=>[]]);
            exit;
        }
        $stmt = $db->prepare("SELECT COUNT(DISTINCT member_id) FROM workout_plans WHERE trainer_id = ?"); $stmt->execute([$trainerId]); $clients = (int)$stmt->fetchColumn();
        $stmt = $db->prepare("SELECT COUNT(*) FROM workout_plans WHERE trainer_id = ? AND (end_date IS NULL OR end_date >= CURDATE())"); $stmt->execute([$trainerId]); $programs = (int)$stmt->fetchColumn();
        $stmt = $db->prepare("SELECT COUNT(DISTINCT CONCAT(a.member_id, '-', a.date)) FROM attendance a INNER JOIN workout_plans w ON w.member_id = a.member_id WHERE w.trainer_id = ? AND a.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"); $stmt->execute([$trainerId]); $sessions = (int)$stmt->fetchColumn();
        $stmt = $db->prepare("SELECT COUNT(*) FROM (SELECT w.member_id, MAX(a.date) AS last_attendance FROM workout_plans w LEFT JOIN attendance a ON a.member_id=w.member_id WHERE w.trainer_id=? GROUP BY w.member_id HAVING last_attendance IS NULL OR last_attendance < DATE_SUB(CURDATE(), INTERVAL 14 DAY)) x"); $stmt->execute([$trainerId]); $attention = (int)$stmt->fetchColumn();
        $stmt = $db->prepare("SELECT u.full_name, u.email, COALESCE(mp.title, 'No Plan') AS plan_title, MAX(wp.end_date) AS plan_end, MAX(a.date) AS last_attendance
            FROM workout_plans wp INNER JOIN members m ON m.id=wp.member_id INNER JOIN users u ON u.id=m.user_id
            LEFT JOIN attendance a ON a.member_id=m.id LEFT JOIN subscriptions s ON s.member_id=m.id AND s.status='active'
            LEFT JOIN membership_plans mp ON mp.id=s.plan_id WHERE wp.trainer_id=?
            GROUP BY m.id,u.full_name,u.email,mp.title ORDER BY last_attendance IS NULL DESC,last_attendance ASC LIMIT 8"); $stmt->execute([$trainerId]); $clientRows=$stmt->fetchAll();
        echo json_encode(['success'=>true,'role'=>$role,'stats'=>['assigned_clients'=>$clients,'active_programs'=>$programs,'sessions'=>$sessions,'attention'=>$attention],'clients'=>$clientRows]);
        exit;
    }

    if ($role === 'member') {
        $stmt = $db->prepare("SELECT id FROM members WHERE user_id = ? LIMIT 1"); $stmt->execute([$userId]); $member=$stmt->fetch();
        if (!$member) { echo json_encode(['success'=>true,'role'=>$role,'summary'=>['current_plan'=>'NO PLAN','days_remaining'=>0,'workout_streak'=>0,'attendance_rate'=>0],'progress'=>null,'workouts'=>[]]); exit; }
        $memberId=(int)$member['id'];
        $stmt=$db->prepare("SELECT s.id,mp.title,s.start_date,s.end_date,s.status FROM subscriptions s INNER JOIN membership_plans mp ON mp.id=s.plan_id WHERE s.member_id=? AND s.status IN ('active','pending') ORDER BY s.end_date DESC LIMIT 1"); $stmt->execute([$memberId]); $subscription=$stmt->fetch() ?: null;
        $daysRemaining=0;
        if ($subscription) { $daysRemaining=max(0,(new DateTimeImmutable('today'))->diff(new DateTimeImmutable($subscription['end_date']))->invert ? 0 : (int)(new DateTimeImmutable('today'))->diff(new DateTimeImmutable($subscription['end_date']))->days); }
        $stmt=$db->prepare("SELECT COUNT(*) FROM attendance WHERE member_id=? AND date BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND CURDATE()"); $stmt->execute([$memberId]); $attended=(int)$stmt->fetchColumn();
        $attendanceRate=min(100,(int)round(($attended/max(1,(int)date('j')))*100));
        $streak=0; $stmt=$db->prepare("SELECT DISTINCT date FROM attendance WHERE member_id=? ORDER BY date DESC LIMIT 60"); $stmt->execute([$memberId]); $dateRows=$stmt->fetchAll(); $cursor=new DateTimeImmutable('today');
        foreach($dateRows as $row){$d=new DateTimeImmutable($row['date']); if($d->format('Y-m-d')===$cursor->format('Y-m-d')){$streak++;$cursor=$cursor->modify('-1 day');}elseif($streak===0&&$d->format('Y-m-d')===$cursor->modify('-1 day')->format('Y-m-d')){$streak++;$cursor=$d->modify('-1 day');}elseif($d<$cursor){break;}}
        $stmt=$db->prepare("SELECT * FROM progress_logs WHERE member_id=? ORDER BY log_date DESC,id DESC LIMIT 1"); $stmt->execute([$memberId]); $progress=$stmt->fetch() ?: null;
        $stmt=$db->prepare("SELECT wp.id,wp.title,wp.goal,wp.start_date,wp.end_date,u.full_name AS trainer_name FROM workout_plans wp LEFT JOIN trainers t ON t.id=wp.trainer_id LEFT JOIN users u ON u.id=t.user_id WHERE wp.member_id=? ORDER BY wp.start_date DESC LIMIT 5"); $stmt->execute([$memberId]); $workouts=$stmt->fetchAll();
        echo json_encode(['success'=>true,'role'=>$role,'summary'=>['current_plan'=>$subscription?strtoupper($subscription['title']):'NO PLAN','days_remaining'=>$daysRemaining,'workout_streak'=>$streak,'attendance_rate'=>$attendanceRate],'subscription'=>$subscription,'progress'=>$progress,'workouts'=>$workouts]); exit;
    }
    http_response_code(403); echo json_encode(['success'=>false,'message'=>'Unsupported role.']);
} catch(Throwable $e) {
    http_response_code(500);
    if(defined('STORAGE_PATH')) error_log('['.date('Y-m-d H:i:s').'] Dashboard API: '.$e->getMessage().PHP_EOL,3,STORAGE_PATH.'/logs/app.log');
    echo json_encode(['success'=>false,'message'=>APP_DEBUG?$e->getMessage():'Unable to load dashboard data.']);
}
