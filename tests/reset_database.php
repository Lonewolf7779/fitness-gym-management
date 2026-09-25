<?php
/**
 * Test-only database reset helper.
 * Restores the deterministic baseline from database/seed.sql.
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';

$seedPath = __DIR__ . '/../database/seed.sql';
if (!is_file($seedPath)) {
    fwrite(STDERR, "Seed file not found: {$seedPath}\n");
    exit(1);
}

$sql = file_get_contents($seedPath);
if ($sql === false) {
    fwrite(STDERR, "Unable to read seed file.\n");
    exit(1);
}

// The seed is maintained as a deterministic test fixture. Execute statements
// individually so this helper does not depend on MySQL CLI availability.
$sql = preg_replace('/^\s*--.*$/m', '', $sql);
$statements = preg_split('/;\s*(?=(?:[^\']*\'[^\']*\')*[^\']*$)/', $sql);

try {
    $db = Database::getInstance();
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if ($statement === '') {
            continue;
        }
        $db->exec($statement);
    }

    echo "Database reset to pristine seed successfully.\n";
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "Database reset failed: " . $e->getMessage() . "\n");
    exit(1);
}
