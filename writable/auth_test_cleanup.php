<?php
$text = file_get_contents(__DIR__ . '/../.env');
preg_match('/^database\.default\.(hostname|port|database|username|password)\s*=\s*(.*)$/m', $text, $unused);
$values = [];
foreach (['hostname', 'port', 'database', 'username', 'password'] as $key) {
    preg_match('/^database\.default\.' . $key . '\s*=\s*(.*)$/m', $text, $match);
    $values[$key] = trim($match[1] ?? '');
}
$connection = pg_connect(
    'host=' . $values['hostname']
    . ' port=' . $values['port']
    . ' dbname=' . $values['database']
    . ' user=' . $values['username']
    . ' password=' . $values['password']
    . ' sslmode=require'
);
if (!$connection) {
    exit(1);
}
pg_query_params($connection, 'DELETE FROM users WHERE username = $1', ['auth_test']);
echo 'temporary auth user removed';
