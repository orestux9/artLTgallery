<?php
// db.php – Neon PostgreSQL Connection (your exact credentials)
$host     = 'ep-spring-waterfall-ab3olxgi-pooler.eu-west-2.aws.neon.tech';
$database = 'neondb';
$user     = 'neondb_owner';
$password = 'npg_MB5WLnbIf1EK';

$conn_string = "host=$host dbname=$database user=$user password=$password sslmode=require options=--channel_binding=require";
$conn = pg_connect($conn_string);

if (!$conn) {
    die("Database connection failed: " . pg_last_error());
}

session_start();
?>