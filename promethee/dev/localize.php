<?php
// Executed only after first import into the disposable local database.
$db = new PDO('mysql:host=db;dbname=promethee', 'promethee', 'local-promethee-only', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
foreach (['sessions','jobs','failed_jobs','notifications','user_oauth_tokens','password_resets'] as $table) {
    $db->exec("DELETE FROM phpvms7_$table");
}
$db->exec("UPDATE phpvms7_users SET api_key=SHA1(CONCAT(UUID(),id)), password=CONCAT('disabled-',UUID()), email=CONCAT('pilot-',id,'@example.invalid')");
$db->exec("UPDATE phpvms7_modules SET enabled=0");
$stmt = $db->prepare("UPDATE phpvms7_settings SET value='seven', updated_at=UTC_TIMESTAMP() WHERE id='general_theme' OR `key`='general.theme'");
$stmt->execute();
