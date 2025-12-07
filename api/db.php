<?php
// db.php – Fixed Neon PostgreSQL Connection (no channel_binding for pooled)
$host     = getenv('PGHOST')     ?: 'ep-spring-waterfall-ab3olxgi-pooler.eu-west-2.aws.neon.tech';
$database = getenv('PGDATABASE') ?: 'neondb';
$user     = getenv('PGUSER')     ?: 'neondb_owner';
$password = getenv('PGPASSWORD') ?: 'npg_MB5WLnbIf1EK';

$conn_string = "host=$host dbname=$database user=$user password=$password sslmode=require";
// Removed: options=--channel_binding=require (unsupported in pooled mode)

$conn = pg_connect($conn_string);

if (!$conn || pg_connection_status($conn) !== PGSQL_CONNECTION_OK) {
    die("Database connection failed: " . pg_last_error($conn ?? null));
}

?>