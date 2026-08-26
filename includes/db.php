<?php

function db_connect() {
    static $pdo = null;
    if ($pdo !== null) return $pdo;
    $config = config('database.connections.mysql');
    $dsn = "{$config['driver']}:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
    $options = $config['options'] ?? [];
    $sslCa = env('DB_SSL_CA');
    if ($sslCa && file_exists($sslCa)) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = $sslCa;
    }
    $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
    return $pdo;
}

function investigation_db_connect() {
    static $pdo = null;
    if ($pdo !== null) return $pdo;
    $config = config('database.connections.investigation');
    $dsn = "{$config['driver']}:host={$config['host']};port={$config['port']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options'] ?? []);
    return $pdo;
}

function investigation_db_for(string $databaseName) {
    $conn = investigation_db_connect();
    $conn->exec("USE `" . str_replace('`', '', $databaseName) . "`");
    return $conn;
}

function db() { return db_connect(); }
function investigationDb() { return investigation_db_connect(); }
function investigationDbFor(string $name) { return investigation_db_for($name); }
