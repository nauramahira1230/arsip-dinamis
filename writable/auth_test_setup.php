<?php
$text = file_get_contents(__DIR__ . '/../.env');
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
pg_query_params($connection, 'INSERT INTO users (username, password_hash, role) VALUES ($1, $2, $3)', [
    'auth_test',
    password_hash('TestPass123!', PASSWORD_DEFAULT),
    'operator',
]);
echo 'temporary auth user created';
