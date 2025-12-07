<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die("Klaidingas ID");

$is_own = isset($_GET['own']);
$role_ok = ($is_own && $_SESSION['role'] === 'artist') || $_SESSION['role'] === 'admin';
if (!$role_ok) die("Neleidžiama");

$where = "id = $1" . ($is_own ? " AND artist_id = $2" : "");
$params = $is_own ? [$id, $_SESSION['user_id']] : [$id];

$result = pg_query_params($conn, "SELECT filename, cloud_public_id FROM artwork WHERE $where", $params);
if (!$row = pg_fetch_assoc($result)) {
    header("Location: " . ($is_own ? 'dashboard.php' : 'admin.php') . "?error=notfound");
    exit;
}

// Delete from Cloudinary
if (!empty($row['cloud_public_id'])) {
    $cloud_name = 'dyh3gaj58';
    $api_key    = '897155443339674';
    $api_secret = '1wFe_abn1-1p6KyEyFZak-ovigs';

    $timestamp = time();
    $signature = sha1("public_id={$row['cloud_public_id']}&timestamp=$timestamp$api_secret");

    $ch = curl_init("https://api.cloudinary.com/v1_1/$cloud_name/image/destroy");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'public_id' => $row['cloud_public_id'],
        'api_key'   => $api_key,
        'timestamp' => $timestamp,
        'signature' => $signature
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

pg_query_params($conn, "DELETE FROM artwork WHERE id = $1", [$id]);

header("Location: " . ($is_own ? 'dashboard.php?deleted=1' : 'admin.php?deleted=1'));
exit;
?>