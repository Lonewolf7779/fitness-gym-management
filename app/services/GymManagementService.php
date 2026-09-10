<?php
/**
 * IRONCORE Gym Management Service: Centralized, Secure Database Business Operations.
 */

require_once __DIR__ . '/../config/database.php';

class GymManagementService {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // =========================================================================
    // 1. ADMIN DASHBOARD & CORE METRICS
    // =========================================================================

    public function dashboardStats(): array {
        $totalMembers = (int) $this->db->query("SELECT COUNT(*) FROM members")->fetchColumn();
        $activeMembers = (int) $this->db->query("
            SELECT COUNT(*) FROM members m 
            JOIN users u ON u.id = m.user_id 
            WHERE u.status = 'active'
        ")->fetchColumn();

        $todayAttendance = (int) $this->db->query("
            SELECT COUNT(*) FROM attendance 
            WHERE date = CURDATE()
        ")->fetchColumn();

        $rawRevenue = (float) $this->db->query("
            SELECT COALESCE(SUM(amount), 0) FROM payments 
            WHERE status = 'paid' 
            AND MONTH(payment_date) = MONTH(CURDATE()) 
            AND YEAR(payment_date) = YEAR(CURDATE())
        ")->fetchColumn();

        // Format revenue nicely (in Lakhs or standard currency)
        $formattedRevenue = ($rawRevenue >= 100000) 
            ? number_format($rawRevenue / 100000, 2) . 'L' 
            : number_format($rawRevenue, 2);

        $activePct = ($totalMembers > 0) ? round(($activeMembers / $totalMembers) * 100, 1) : 0;

        return [
            'total_members'     => $totalMembers,
            'active_members'    => $activeMembers,
            'today_attendance'  => $todayAttendance,
            'monthly_revenue'   => $formattedRevenue,
            'monthly_revenue_raw' => $rawRevenue,
            'active_pct'        => $activePct
        ];
    }

    public function recentMembers(int $limit = 5): array {
        $stmt = $this->db->prepare("
            SELECT m.id, m.user_id, m.phone, m.join_date, u.full_name, u.email, u.status,
                   p.title as plan_title, s.status as subscription_status
            FROM members m
            JOIN users u ON u.id = m.user_id
            LEFT JOIN subscriptions s ON s.member_id = m.id AND s.id = (
                SELECT MAX(s2.id) FROM subscriptions s2 WHERE s2.member_id = m.id
            )
            LEFT JOIN membership_plans p ON p.id = s.plan_id
            ORDER BY m.id DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function approachingExpiries(int $days = 7): array {
        $stmt = $this->db->prepare("
            SELECT s.id as subscription_id, s.start_date, s.end_date, s.status,
                   u.full_name, p.title as plan_title,
                   DATEDIFF(s.end_date, CURDATE()) as days_left
            FROM subscriptions s
            JOIN members m ON m.id = s.member_id
            JOIN users u ON u.id = m.user_id
            JOIN membership_plans p ON p.id = s.plan_id
            WHERE s.status = 'active'
            AND s.end_date >= CURDATE()
            AND s.end_date <= DATE_ADD(CURDATE(), INTERVAL :days DAY)
            ORDER BY s.end_date ASC
            LIMIT 6
        ");
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function monthlyRevenueChart(): array {
        $labels = [];
        $values = [];
        for ($i = 7; $i >= 0; $i--) {
            $monthTime = strtotime("-$i months");
            $m = date('m', $monthTime);
            $y = date('Y', $monthTime);
            $labels[] = date('M', $monthTime);

            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(amount), 0) FROM payments 
                WHERE status = 'paid' AND MONTH(payment_date) = :m AND YEAR(payment_date) = :y
            ");
            $stmt->execute(['m' => $m, 'y' => $y]);
            $sum = (float) $stmt->fetchColumn();
            // Convert to Lakhs for chart scale if values are large
            $valInLakhs = ($sum > 0) ? round($sum / 100000, 2) : 0;
            $values[] = ($valInLakhs > 0) ? $valInLakhs : round($sum, 2);
        }

        $max = !empty($values) ? max(max($values) * 1.25, 1.0) : 1.0;
        return [
            'labels' => $labels,
            'values' => $values,
            'max'    => $max
        ];
    }

    public function weeklyAttendanceChart(): array {
        $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $values = [];
        $monday = strtotime('monday this week');

        for ($i = 0; $i < 7; $i++) {
            $dayTime = strtotime("+$i days", $monday);
            $date = date('Y-m-d', $dayTime);
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM attendance WHERE date = :d");
            $stmt->execute(['d' => $date]);
            $values[] = (int) $stmt->fetchColumn();
        }

        $max = !empty($values) ? max(max($values) * 1.2, 10) : 10;
        return [
            'labels' => $labels,
            'values' => $values,
            'max'    => $max
        ];
    }

    // =========================================================================
    // 2. MEMBER MANAGEMENT
    // =========================================================================

    public function members(string $search = '', string $status = ''): array {
        $sql = "
            SELECT m.id, m.user_id, m.phone, m.emergency_contact, m.gender, m.dob, m.address, m.join_date,
                   u.full_name, u.email, u.status,
                   s.id as subscription_id, s.start_date, s.end_date, s.status as subscription_status,
                   p.id as plan_id, p.title as plan_title, p.price as plan_price
            FROM members m
            JOIN users u ON u.id = m.user_id
            LEFT JOIN subscriptions s ON s.member_id = m.id AND s.id = (
                SELECT MAX(s2.id) FROM subscriptions s2 WHERE s2.member_id = m.id
            )
            LEFT JOIN membership_plans p ON p.id = s.plan_id
            WHERE 1=1
        ";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (u.full_name LIKE :q1 OR u.email LIKE :q2 OR m.phone LIKE :q3)";
            $params['q1'] = "%$search%";
            $params['q2'] = "%$search%";
            $params['q3'] = "%$search%";
        }
        if ($status !== '' && $status !== 'all') {
            $sql .= " AND u.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY m.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getMemberById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT m.*, u.full_name, u.email, u.status,
                   s.id as subscription_id, s.start_date, s.end_date, s.status as subscription_status,
                   p.title as plan_title, p.price as plan_price
            FROM members m
            JOIN users u ON u.id = m.user_id
            LEFT JOIN subscriptions s ON s.member_id = m.id AND s.id = (
                SELECT MAX(s2.id) FROM subscriptions s2 WHERE s2.member_id = m.id
            )
            LEFT JOIN membership_plans p ON p.id = s.plan_id
            WHERE m.id = :id LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function getMemberByUserId(int $userId): ?array {
        $stmt = $this->db->prepare("
            SELECT m.*, u.full_name, u.email, u.status
            FROM members m
            JOIN users u ON u.id = m.user_id
            WHERE m.user_id = :uid LIMIT 1
        ");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetch() ?: null;
    }

    public function createMember(array $d): int {
        $email = strtolower(trim($d['email'] ?? ''));
        if (empty($email) || empty($d['name'])) {
            throw new InvalidArgumentException('Member name and valid email are required.');
        }

        // Check duplicate email
        $check = $this->db->prepare("SELECT id FROM users WHERE email = :e");
        $check->execute(['e' => $email]);
        if ($check->fetchColumn()) {
            throw new RuntimeException("A user with email '{$email}' already exists.");
        }

        $this->db->beginTransaction();
        try {
            $passHash = password_hash($d['password'] ?? 'Member@123', PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("
                INSERT INTO users (full_name, email, password_hash, role, status)
                VALUES (:name, :email, :pass, 'member', :status)
            ");
            $stmt->execute([
                'name'   => trim($d['name']),
                'email'  => $email,
                'pass'   => $passHash,
                'status' => $d['status'] ?? 'active'
            ]);
            $uid = (int) $this->db->lastInsertId();

            $stmt = $this->db->prepare("
                INSERT INTO members (user_id, phone, emergency_contact, gender, dob, address, join_date)
                VALUES (:uid, :phone, :emergency, :gender, :dob, :address, :join_date)
            ");
            $stmt->execute([
                'uid'       => $uid,
                'phone'     => $d['phone'] ?? null,
                'emergency' => $d['emergency_contact'] ?? null,
                'gender'    => $d['gender'] ?? null,
                'dob'       => !empty($d['dob']) ? $d['dob'] : null,
                'address'   => $d['address'] ?? null,
                'join_date' => !empty($d['join_date']) ? $d['join_date'] : date('Y-m-d')
            ]);
            $mid = (int) $this->db->lastInsertId();

            // Optional subscription assignment
            if (!empty($d['plan_id'])) {
                $plan = $this->getPlan((int) $d['plan_id']);
                if ($plan) {
                    $start = !empty($d['start_date']) ? $d['start_date'] : date('Y-m-d');
                    $days = max(1, (int) ($plan['duration_days'] ?? 30));
                    $end = date('Y-m-d', strtotime("{$start} + {$days} days"));

                    $stmt = $this->db->prepare("
                        INSERT INTO subscriptions (member_id, plan_id, start_date, end_date, auto_renew, status)
                        VALUES (:member, :plan, :start, :end, 1, 'active')
                    ");
                    $stmt->execute([
                        'member' => $mid,
                        'plan'   => $plan['id'],
                        'start'  => $start,
                        'end'    => $end
                    ]);
                    $subId = (int) $this->db->lastInsertId();

                    // Automatically record initial payment
                    $this->createPayment([
                        'subscription_id' => $subId,
                        'member_id'       => $mid,
                        'amount'          => $plan['price'],
                        'payment_method'  => $d['payment_method'] ?? 'UPI',
                        'status'          => 'paid'
                    ]);
                }
            }

            $this->db->commit();
            return $mid;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateMember(int $id, array $d): void {
        $stmt = $this->db->prepare("SELECT user_id FROM members WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $uid = (int) $stmt->fetchColumn();
        if (!$uid) {
            throw new RuntimeException('Member not found.');
        }

        $email = strtolower(trim($d['email'] ?? ''));
        if (empty($email) || empty($d['name'])) {
            throw new InvalidArgumentException('Name and email cannot be empty.');
        }

        // Check email uniqueness excluding current user
        $check = $this->db->prepare("SELECT id FROM users WHERE email = :e AND id != :uid");
        $check->execute(['e' => $email, 'uid' => $uid]);
        if ($check->fetchColumn()) {
            throw new RuntimeException("The email '{$email}' is already in use by another user.");
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                UPDATE users SET full_name = :name, email = :email, status = :status 
                WHERE id = :uid
            ");
            $stmt->execute([
                'name'   => trim($d['name']),
                'email'  => $email,
                'status' => $d['status'] ?? 'active',
                'uid'    => $uid
            ]);

            $stmt = $this->db->prepare("
                UPDATE members 
                SET phone = :phone, emergency_contact = :emergency, gender = :gender, 
                    dob = :dob, address = :address 
                WHERE id = :id
            ");
            $stmt->execute([
                'phone'     => $d['phone'] ?? null,
                'emergency' => $d['emergency_contact'] ?? null,
                'gender'    => $d['gender'] ?? null,
                'dob'       => !empty($d['dob']) ? $d['dob'] : null,
                'address'   => $d['address'] ?? null,
                'id'        => $id
            ]);

            // If a new plan is assigned or updated
            if (!empty($d['plan_id'])) {
                $plan = $this->getPlan((int) $d['plan_id']);
                if ($plan) {
                    $start = !empty($d['start_date']) ? $d['start_date'] : date('Y-m-d');
                    $days = max(1, (int) ($plan['duration_days'] ?? 30));
                    $end = date('Y-m-d', strtotime("{$start} + {$days} days"));

                    $stmt = $this->db->prepare("
                        INSERT INTO subscriptions (member_id, plan_id, start_date, end_date, auto_renew, status)
                        VALUES (:member, :plan, :start, :end, 1, 'active')
                    ");
                    $stmt->execute([
                        'member' => $id,
                        'plan'   => $plan['id'],
                        'start'  => $start,
                        'end'    => $end
                    ]);
                }
            }

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function resetPassword(int $userId, string $newPassword): void {
        if (strlen($newPassword) < 6) {
            throw new InvalidArgumentException('Password must be at least 6 characters.');
        }
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE users SET password_hash = :p WHERE id = :id");
        $stmt->execute(['p' => $hash, 'id' => $userId]);
    }

    public function setUserStatus(int $userId, string $status): void {
        $allowed = ['active', 'inactive', 'suspended'];
        if (!in_array($status, $allowed, true)) {
            throw new InvalidArgumentException('Invalid user status.');
        }
        $stmt = $this->db->prepare("UPDATE users SET status = :s WHERE id = :id");
        $stmt->execute(['s' => $status, 'id' => $userId]);
    }

    public function deleteMember(int $memberId): void {
        $stmt = $this->db->prepare("SELECT user_id FROM members WHERE id = :id");
        $stmt->execute(['id' => $memberId]);
        $userId = (int) $stmt->fetchColumn();

        $this->db->beginTransaction();
        try {
            // Delete dependent records
            $this->db->prepare("DELETE FROM progress_logs WHERE member_id = :id")->execute(['id' => $memberId]);
            $this->db->prepare("DELETE FROM attendance WHERE member_id = :id")->execute(['id' => $memberId]);
            $this->db->prepare("DELETE FROM payments WHERE member_id = :id")->execute(['id' => $memberId]);
            $this->db->prepare("DELETE FROM subscriptions WHERE member_id = :id")->execute(['id' => $memberId]);
            $this->db->prepare("DELETE FROM workout_plan_exercises WHERE plan_id IN (SELECT id FROM workout_plans WHERE member_id = :id)")->execute(['id' => $memberId]);
            $this->db->prepare("DELETE FROM workout_plans WHERE member_id = :id")->execute(['id' => $memberId]);
            $this->db->prepare("DELETE FROM members WHERE id = :id")->execute(['id' => $memberId]);
            if ($userId) {
                $this->db->prepare("DELETE FROM users WHERE id = :id")->execute(['id' => $userId]);
            }
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteTrainer(int $trainerId): void {
        $stmt = $this->db->prepare("SELECT user_id FROM trainers WHERE id = :id");
        $stmt->execute(['id' => $trainerId]);
        $userId = (int) $stmt->fetchColumn();

        $this->db->beginTransaction();
        try {
            $this->db->prepare("UPDATE workout_plans SET trainer_id = NULL WHERE trainer_id = :id")->execute(['id' => $trainerId]);
            $this->db->prepare("DELETE FROM trainers WHERE id = :id")->execute(['id' => $trainerId]);
            if ($userId) {
                $this->db->prepare("DELETE FROM users WHERE id = :id")->execute(['id' => $userId]);
            }
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteWorkout(int $workoutId): void {
        $this->db->beginTransaction();
        try {
            $this->db->prepare("DELETE FROM workout_plan_exercises WHERE plan_id = :id")->execute(['id' => $workoutId]);
            $this->db->prepare("DELETE FROM workout_plans WHERE id = :id")->execute(['id' => $workoutId]);
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // =========================================================================
    // 3. MEMBERSHIP PLANS
    // =========================================================================

    public function plans(bool $activeOnly = false): array {
        $sql = "SELECT * FROM membership_plans" . ($activeOnly ? " WHERE status = 'active'" : "") . " ORDER BY price ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getPlan(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM membership_plans WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function savePlan(array $d, ?int $id = null): int {
        $title = trim($d['title'] ?? '');
        if (empty($title)) {
            throw new InvalidArgumentException('Plan title is required.');
        }

        $data = [
            'title'       => $title,
            'tag'         => $d['tag'] ?? null,
            'price'       => max(0, (float) ($d['price'] ?? 0)),
            'cycle'       => $d['billing_cycle'] ?? 'monthly',
            'days'        => max(1, (int) ($d['duration_days'] ?? 30)),
            'description' => $d['description'] ?? null,
            'recommended' => !empty($d['is_recommended']) ? 1 : 0,
            'status'      => $d['status'] ?? 'active'
        ];

        if ($id) {
            $data['id'] = $id;
            $stmt = $this->db->prepare("
                UPDATE membership_plans 
                SET title = :title, tag = :tag, price = :price, billing_cycle = :cycle, 
                    duration_days = :days, description = :description, is_recommended = :recommended, 
                    status = :status 
                WHERE id = :id
            ");
            $stmt->execute($data);
            return $id;
        }

        $stmt = $this->db->prepare("
            INSERT INTO membership_plans (title, tag, price, billing_cycle, duration_days, description, is_recommended, status)
            VALUES (:title, :tag, :price, :cycle, :days, :description, :recommended, :status)
        ");
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function togglePlan(int $id): void {
        $stmt = $this->db->prepare("UPDATE membership_plans SET status = IF(status = 'active', 'inactive', 'active') WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    // =========================================================================
    // 4. TRAINERS & TRAINER PORTAL
    // =========================================================================

    public function trainers(): array {
        return $this->db->query("
            SELECT t.*, u.full_name, u.email, u.status 
            FROM trainers t 
            JOIN users u ON u.id = t.user_id 
            ORDER BY t.id DESC
        ")->fetchAll();
    }

    public function getTrainerByUserId(int $userId): ?array {
        $stmt = $this->db->prepare("
            SELECT t.*, u.full_name, u.email, u.status 
            FROM trainers t 
            JOIN users u ON u.id = t.user_id 
            WHERE t.user_id = :uid LIMIT 1
        ");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetch() ?: null;
    }

    public function createTrainer(array $d): int {
        $email = strtolower(trim($d['email'] ?? ''));
        if (empty($email) || empty($d['name'])) {
            throw new InvalidArgumentException('Trainer name and email are required.');
        }

        $check = $this->db->prepare("SELECT id FROM users WHERE email = :e");
        $check->execute(['e' => $email]);
        if ($check->fetchColumn()) {
            throw new RuntimeException("A user with email '{$email}' already exists.");
        }

        $this->db->beginTransaction();
        try {
            $passHash = password_hash($d['password'] ?? 'Trainer@123', PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("
                INSERT INTO users (full_name, email, password_hash, role, status)
                VALUES (:name, :email, :pass, 'trainer', :status)
            ");
            $stmt->execute([
                'name'   => trim($d['name']),
                'email'  => $email,
                'pass'   => $passHash,
                'status' => $d['status'] ?? 'active'
            ]);
            $uid = (int) $this->db->lastInsertId();

            $stmt = $this->db->prepare("
                INSERT INTO trainers (user_id, specialization, experience_years, bio, hourly_rate)
                VALUES (:uid, :spec, :exp, :bio, :rate)
            ");
            $stmt->execute([
                'uid'  => $uid,
                'spec' => $d['specialization'] ?? 'General Fitness & Strength',
                'exp'  => (int) ($d['experience_years'] ?? 0),
                'bio'  => $d['bio'] ?? null,
                'rate' => (float) ($d['hourly_rate'] ?? 0)
            ]);
            $tid = (int) $this->db->lastInsertId();

            $this->db->commit();
            return $tid;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateTrainer(int $id, array $d): void {
        $stmt = $this->db->prepare("SELECT user_id FROM trainers WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $uid = (int) $stmt->fetchColumn();
        if (!$uid) {
            throw new RuntimeException('Trainer not found.');
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("UPDATE users SET full_name = :name, email = :email, status = :status WHERE id = :uid");
            $stmt->execute([
                'name'   => trim($d['name'] ?? ''),
                'email'  => strtolower(trim($d['email'] ?? '')),
                'status' => $d['status'] ?? 'active',
                'uid'    => $uid
            ]);

            $stmt = $this->db->prepare("
                UPDATE trainers 
                SET specialization = :spec, experience_years = :exp, bio = :bio, hourly_rate = :rate 
                WHERE id = :id
            ");
            $stmt->execute([
                'spec' => $d['specialization'] ?? 'General Fitness',
                'exp'  => (int) ($d['experience_years'] ?? 0),
                'bio'  => $d['bio'] ?? null,
                'rate' => (float) ($d['hourly_rate'] ?? 0),
                'id'   => $id
            ]);

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function trainerDashboard(int $userId): array {
        $trainer = $this->getTrainerByUserId($userId);
        $trainerId = $trainer ? (int) $trainer['id'] : null;

        // 1. Assigned Clients & Client Roster
        $clientsQuery = "
            SELECT m.id, m.phone, m.join_date, u.full_name, u.email, u.status,
                   p.title as plan_title, s.end_date, s.status as subscription_status,
                   wp.title as workout_title, wp.goal as workout_goal,
                   (SELECT COUNT(*) FROM attendance a WHERE a.member_id = m.id) as total_checkins,
                   (SELECT MAX(CONCAT(a2.date, ' ', a2.check_in_time)) FROM attendance a2 WHERE a2.member_id = m.id) as last_seen
            FROM members m
            JOIN users u ON u.id = m.user_id
            LEFT JOIN workout_plans wp ON wp.member_id = m.id " . ($trainerId ? "AND wp.trainer_id = :tid" : "") . "
            LEFT JOIN subscriptions s ON s.member_id = m.id AND s.id = (
                SELECT MAX(s2.id) FROM subscriptions s2 WHERE s2.member_id = m.id
            )
            LEFT JOIN membership_plans p ON p.id = s.plan_id
            WHERE u.status = 'active'
            ORDER BY m.id DESC
        ";
        $stmt = $this->db->prepare($clientsQuery);
        if ($trainerId) {
            $stmt->execute(['tid' => $trainerId]);
        } else {
            $stmt->execute();
        }
        $clients = $stmt->fetchAll();

        $assignedCount = count($clients);
        $activePrograms = 0;
        $needingAttention = 0;

        foreach ($clients as $c) {
            if (!empty($c['workout_title'])) {
                $activePrograms++;
            }
            if ($c['subscription_status'] === 'expired' || empty($c['subscription_status']) || (int) $c['total_checkins'] === 0) {
                $needingAttention++;
            }
        }

        // 2. Real Weekly Sessions (attendance this calendar week)
        $sessionsThisWeek = (int) $this->db->query("
            SELECT COUNT(*) FROM attendance 
            WHERE YEARWEEK(date, 1) = YEARWEEK(CURDATE(), 1)
        ")->fetchColumn();

        // 3. Today's Attendance / Sessions
        $todaySchedule = $this->db->query("
            SELECT a.*, u.full_name, u.email 
            FROM attendance a
            JOIN members m ON m.id = a.member_id
            JOIN users u ON u.id = m.user_id
            WHERE a.date = CURDATE()
            ORDER BY a.check_in_time DESC
        ")->fetchAll();

        return [
            'trainer'                          => $trainer,
            'assigned_clients_count'           => $assignedCount,
            'active_programs_count'            => $activePrograms,
            'sessions_this_week'               => $sessionsThisWeek,
            'clients_needing_attention'        => $needingAttention,
            'clients_needing_attention_count'  => $needingAttention,
            'clients'                          => $clients,
            'today_schedule'                   => $todaySchedule
        ];
    }

    // =========================================================================
    // 5. MEMBER PORTAL & SELF-SERVICE
    // =========================================================================

    public function memberDashboard(int $userId): array {
        $member = $this->getMemberByUserId($userId);
        if (!$member) {
            return [
                'member'            => null,
                'subscription'      => null,
                'days_remaining'    => 0,
                'attendance_streak' => 0,
                'attendance_rate'   => 0,
                'total_checkins'    => 0,
                'weekly_stats'      => ['workouts_completed' => '0 / 0', 'training_time' => '0h', 'weekly_attendance' => '0 Days'],
                'workout_plan'      => null,
                'workout_exercises' => [],
                'progress_logs'     => [],
                'recent_activity'   => []
            ];
        }

        $memberId = (int) $member['id'];

        // 1. Latest Subscription
        $stmt = $this->db->prepare("
            SELECT s.*, p.title as plan_title, p.price as plan_price, p.billing_cycle,
                   DATEDIFF(s.end_date, CURDATE()) as days_left
            FROM subscriptions s
            JOIN membership_plans p ON p.id = s.plan_id
            WHERE s.member_id = :mid
            ORDER BY s.id DESC LIMIT 1
        ");
        $stmt->execute(['mid' => $memberId]);
        $subscription = $stmt->fetch() ?: null;
        $daysRemaining = $subscription ? max(0, (int) $subscription['days_left']) : 0;

        // 2. Attendance & Streak Calculation
        $stmt = $this->db->prepare("
            SELECT date, check_in_time, check_out_time, status 
            FROM attendance 
            WHERE member_id = :mid 
            ORDER BY date DESC, check_in_time DESC
        ");
        $stmt->execute(['mid' => $memberId]);
        $attendanceLogs = $stmt->fetchAll();
        $totalCheckins = count($attendanceLogs);

        // Calculate consecutive attendance streak
        $streak = 0;
        $checkDate = new DateTime();
        $attendedDates = array_column($attendanceLogs, 'date');
        
        // Check if checked in today or yesterday
        $todayStr = $checkDate->format('Y-m-d');
        $yesterdayStr = (clone $checkDate)->modify('-1 day')->format('Y-m-d');

        if (in_array($todayStr, $attendedDates, true) || in_array($yesterdayStr, $attendedDates, true)) {
            $cursor = in_array($todayStr, $attendedDates, true) ? clone $checkDate : (clone $checkDate)->modify('-1 day');
            while (in_array($cursor->format('Y-m-d'), $attendedDates, true)) {
                $streak++;
                $cursor->modify('-1 day');
            }
        }

        // Attendance this week
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM attendance 
            WHERE member_id = :mid AND YEARWEEK(date, 1) = YEARWEEK(CURDATE(), 1)
        ");
        $stmt->execute(['mid' => $memberId]);
        $weeklyCount = (int) $stmt->fetchColumn();

        // 3. Assigned Workout Plan & Exercises
        $stmt = $this->db->prepare("
            SELECT wp.*, tu.full_name as trainer_name 
            FROM workout_plans wp
            LEFT JOIN trainers t ON t.id = wp.trainer_id
            LEFT JOIN users tu ON tu.id = t.user_id
            WHERE wp.member_id = :mid
            ORDER BY wp.id DESC LIMIT 1
        ");
        $stmt->execute(['mid' => $memberId]);
        $workoutPlan = $stmt->fetch() ?: null;

        $workoutExercises = [];
        if ($workoutPlan) {
            $stmt = $this->db->prepare("
                SELECT wpe.*, ec.name as exercise_name, ec.category, ec.muscle_group, ec.equipment, ec.instructions
                FROM workout_plan_exercises wpe
                JOIN exercise_catalog ec ON ec.id = wpe.exercise_id
                WHERE wpe.plan_id = :pid
                ORDER BY FIELD(wpe.day_of_week, 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'), wpe.id ASC
            ");
            $stmt->execute(['pid' => $workoutPlan['id']]);
            $workoutExercises = $stmt->fetchAll();
        }

        // 4. Progress Logs
        $stmt = $this->db->prepare("
            SELECT * FROM progress_logs 
            WHERE member_id = :mid 
            ORDER BY log_date DESC LIMIT 10
        ");
        $stmt->execute(['mid' => $memberId]);
        $progressLogs = $stmt->fetchAll();

        // 5. Recent Activity Feed
        $recentActivity = [];
        foreach (array_slice($attendanceLogs, 0, 4) as $att) {
            $recentActivity[] = [
                'title' => 'Gym Check-in (' . ucfirst($att['status']) . ')',
                'desc'  => 'Checked in at ' . date('h:i A', strtotime($att['check_in_time'])),
                'time'  => date('M d, Y', strtotime($att['date']))
            ];
        }
        foreach (array_slice($progressLogs, 0, 2) as $prog) {
            $recentActivity[] = [
                'title' => 'Progress Updated',
                'desc'  => "Weight: {$prog['weight_kg']} kg" . ($prog['body_fat_pct'] ? " · Body Fat: {$prog['body_fat_pct']}%" : ""),
                'time'  => date('M d, Y', strtotime($prog['log_date']))
            ];
        }

        return [
            'member'            => $member,
            'subscription'      => $subscription,
            'days_remaining'    => $daysRemaining,
            'attendance_streak' => max($streak, $totalCheckins > 0 ? 1 : 0),
            'attendance_rate'   => ($totalCheckins > 0) ? min(100, round(($totalCheckins / 30) * 100)) : 0,
            'total_checkins'    => $totalCheckins,
            'weekly_stats'      => [
                'workouts_completed' => count($workoutExercises) . ' Exercises Assigned',
                'training_time'      => ($weeklyCount * 1.2) . 'h',
                'weekly_attendance'  => $weeklyCount . ' Days'
            ],
            'workout_plan'      => $workoutPlan,
            'workout_exercises' => $workoutExercises,
            'progress_logs'     => $progressLogs,
            'recent_activity'   => $recentActivity
        ];
    }

    public function memberSelfCheckIn(int $userId): int {
        $member = $this->getMemberByUserId($userId);
        if (!$member) {
            throw new RuntimeException('Member profile not found.');
        }

        return $this->checkIn((int) $member['id'], 'present');
    }

    public function memberAddProgress(int $userId, array $d): int {
        $member = $this->getMemberByUserId($userId);
        if (!$member) {
            throw new RuntimeException('Member profile not found.');
        }

        $stmt = $this->db->prepare("
            INSERT INTO progress_logs (member_id, log_date, weight_kg, body_fat_pct, chest_cm, waist_cm, biceps_cm, notes)
            VALUES (:mid, :date, :weight, :bf, :chest, :waist, :biceps, :notes)
        ");
        $stmt->execute([
            'mid'    => (int) $member['id'],
            'date'   => $d['log_date'] ?? date('Y-m-d'),
            'weight' => !empty($d['weight_kg']) ? (float) $d['weight_kg'] : null,
            'bf'     => !empty($d['body_fat_pct']) ? (float) $d['body_fat_pct'] : null,
            'chest'  => !empty($d['chest_cm']) ? (float) $d['chest_cm'] : null,
            'waist'  => !empty($d['waist_cm']) ? (float) $d['waist_cm'] : null,
            'biceps' => !empty($d['biceps_cm']) ? (float) $d['biceps_cm'] : null,
            'notes'  => $d['notes'] ?? null
        ]);
        return (int) $this->db->lastInsertId();
    }

    // =========================================================================
    // 6. ATTENDANCE OPERATIONS
    // =========================================================================

    public function attendance(string $date = ''): array {
        $date = $date ?: date('Y-m-d');
        $stmt = $this->db->prepare("
            SELECT a.*, u.full_name, u.email 
            FROM attendance a 
            JOIN members m ON m.id = a.member_id 
            JOIN users u ON u.id = m.user_id 
            WHERE a.date = :date 
            ORDER BY a.check_in_time DESC
        ");
        $stmt->execute(['date' => $date]);
        return $stmt->fetchAll();
    }

    public function checkIn(int $memberId, string $status = 'present'): int {
        $allowed = ['present', 'late', 'excused'];
        if (!in_array($status, $allowed, true)) {
            $status = 'present';
        }

        // Duplicate check-in protection for the same calendar date
        $check = $this->db->prepare("SELECT id FROM attendance WHERE member_id = :m AND date = :d");
        $check->execute(['m' => $memberId, 'd' => date('Y-m-d')]);
        if ($check->fetchColumn()) {
            throw new RuntimeException('Member has already been checked in today.');
        }

        $stmt = $this->db->prepare("
            INSERT INTO attendance (member_id, date, check_in_time, status) 
            VALUES (:m, :d, :t, :s)
        ");
        $stmt->execute([
            'm' => $memberId,
            'd' => date('Y-m-d'),
            't' => date('H:i:s'),
            's' => $status
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function checkOut(int $id): void {
        $stmt = $this->db->prepare("
            UPDATE attendance 
            SET check_out_time = :t 
            WHERE id = :id AND check_out_time IS NULL
        ");
        $stmt->execute([
            't'  => date('H:i:s'),
            'id' => $id
        ]);
        if ($stmt->rowCount() === 0) {
            throw new RuntimeException('Attendance record was not found or is already checked out.');
        }
    }

    // =========================================================================
    // 7. PAYMENTS & FINANCIALS
    // =========================================================================

    public function payments(): array {
        return $this->db->query("
            SELECT p.*, u.full_name, p.payment_date 
            FROM payments p 
            JOIN members m ON m.id = p.member_id 
            JOIN users u ON u.id = m.user_id 
            ORDER BY p.payment_date DESC
        ")->fetchAll();
    }

    public function createPayment(array $d): int {
        $amount = (float) ($d['amount'] ?? 0);
        if ($amount <= 0) {
            throw new InvalidArgumentException('Payment amount must be greater than zero.');
        }

        $txn = trim($d['transaction_id'] ?? '');
        if ($txn === '') {
            $txn = 'TXN_IRN_' . strtoupper(bin2hex(random_bytes(4)));
        }

        // Check duplicate transaction ID
        $check = $this->db->prepare("SELECT id FROM payments WHERE transaction_id = :t");
        $check->execute(['t' => $txn]);
        if ($check->fetchColumn()) {
            throw new RuntimeException("Transaction ID '{$txn}' already exists.");
        }

        $stmt = $this->db->prepare("
            INSERT INTO payments (subscription_id, member_id, amount, payment_method, transaction_id, status, payment_date)
            VALUES (:subscription, :member, :amount, :method, :txn, :status, NOW())
        ");
        $stmt->execute([
            'subscription' => !empty($d['subscription_id']) ? (int) $d['subscription_id'] : null,
            'member'       => (int) $d['member_id'],
            'amount'       => $amount,
            'method'       => $d['payment_method'] ?? 'UPI',
            'txn'          => $txn,
            'status'       => $d['status'] ?? 'paid'
        ]);
        return (int) $this->db->lastInsertId();
    }

    // =========================================================================
    // 8. WORKOUTS & EXERCISE CATALOG
    // =========================================================================

    public function exercises(): array {
        return $this->db->query("SELECT * FROM exercise_catalog ORDER BY category, name")->fetchAll();
    }

    public function workouts(): array {
        return $this->db->query("
            SELECT wp.*, u.full_name as member_name, tu.full_name as trainer_name 
            FROM workout_plans wp 
            JOIN members m ON m.id = wp.member_id 
            JOIN users u ON u.id = m.user_id 
            LEFT JOIN trainers t ON t.id = wp.trainer_id 
            LEFT JOIN users tu ON tu.id = t.user_id 
            ORDER BY wp.id DESC
        ")->fetchAll();
    }

    public function createWorkout(array $d): int {
        $title = trim($d['title'] ?? '');
        if (empty($title)) {
            throw new InvalidArgumentException('Workout plan title is required.');
        }

        $stmt = $this->db->prepare("
            INSERT INTO workout_plans (member_id, trainer_id, title, goal, start_date, end_date)
            VALUES (:member, :trainer, :title, :goal, :start, :end)
        ");
        $stmt->execute([
            'member'  => (int) $d['member_id'],
            'trainer' => !empty($d['trainer_id']) ? (int) $d['trainer_id'] : null,
            'title'   => $title,
            'goal'    => $d['goal'] ?? null,
            'start'   => !empty($d['start_date']) ? $d['start_date'] : date('Y-m-d'),
            'end'     => !empty($d['end_date']) ? $d['end_date'] : null
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function addWorkoutExercise(array $d): int {
        $day = $d['day_of_week'] ?? 'Mon';
        if (!in_array($day, ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], true)) {
            $day = 'Mon';
        }

        $stmt = $this->db->prepare("
            INSERT INTO workout_plan_exercises (plan_id, exercise_id, sets, reps, rest_seconds, day_of_week)
            VALUES (:plan, :exercise, :sets, :reps, :rest, :day)
        ");
        $stmt->execute([
            'plan'     => (int) $d['plan_id'],
            'exercise' => (int) $d['exercise_id'],
            'sets'     => max(1, (int) ($d['sets'] ?? 3)),
            'reps'     => $d['reps'] ?? '10-12',
            'rest'     => max(0, (int) ($d['rest_seconds'] ?? 60)),
            'day'      => $day
        ]);
        return (int) $this->db->lastInsertId();
    }

    // =========================================================================
    // 9. REPORTS & ANALYTICS
    // =========================================================================

    public function reportStats(): array {
        return [
            'members'          => (int) $this->db->query("SELECT COUNT(*) FROM members")->fetchColumn(),
            'active_members'   => (int) $this->db->query("SELECT COUNT(*) FROM members m JOIN users u ON u.id = m.user_id WHERE u.status = 'active'")->fetchColumn(),
            'trainers'         => (int) $this->db->query("SELECT COUNT(*) FROM trainers t JOIN users u ON u.id = t.user_id WHERE u.status = 'active'")->fetchColumn(),
            'plans'            => (int) $this->db->query("SELECT COUNT(*) FROM membership_plans WHERE status = 'active'")->fetchColumn(),
            'attendance_today' => (int) $this->db->query("SELECT COUNT(*) FROM attendance WHERE date = CURDATE()")->fetchColumn(),
            'revenue'          => (float) $this->db->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'paid' AND MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE())")->fetchColumn()
        ];
    }
}
