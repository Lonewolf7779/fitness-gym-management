<?php
require_once __DIR__ . '/../config/database.php';

class SettingsService {
    private PDO $db;
    public function __construct(){ $this->db=Database::getInstance(); $this->db->exec("CREATE TABLE IF NOT EXISTS system_settings (setting_key VARCHAR(80) PRIMARY KEY, setting_value TEXT NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); }
    public function all(): array { $rows=$this->db->query('SELECT setting_key,setting_value FROM system_settings')->fetchAll(); $out=[]; foreach($rows as $r)$out[$r['setting_key']]=$r['setting_value']; return $out; }
    public function save(array $values): void { $stmt=$this->db->prepare('INSERT INTO system_settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)'); foreach($values as $k=>$v){if(!preg_match('/^[a-z0-9_]{1,80}$/',$k))continue;$stmt->execute([$k,trim((string)$v)]);} }
}
