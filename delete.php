<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    $redirect = isset($_GET['own']) ? 'dashboard.php' : 'admin.php';
    header("Location: $redirect?error=Invalid ID");
    exit;
}

// Determine if own delete or admin
$is_own = isset($_GET['own']);
if ($is_own && $_SESSION['role'] !== 'artist') {
    header("Location: dashboard.php?error=Unauthorized");
    exit;
} elseif (!$is_own && $_SESSION['role'] !== 'admin') {
    header("Location: admin.php?error=Unauthorized");
    exit;
}

// Fetch artwork with ownership check
$where = "id = ?";
$params = "i";
$bind_values = [&$id];

if ($is_own) {
    $where .= " AND artist_id = ?";
    $params .= "i";
    $bind_values[] = &$_SESSION['user_id'];
}

$stmt = $conn->prepare("SELECT filename, cloud_public_id FROM artwork WHERE $where");
$stmt->bind_param($params, ...$bind_values);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $redirect = $is_own ? 'dashboard.php' : 'admin.php';
    header("Location: $redirect?error=Not found or unauthorized");
    exit;
}

$row = $result->fetch_assoc();
$stmt->close();

// Delete from cloud if public_id exists
if (!empty($row['cloud_public_id'])) {
    $cloud_name = 'dyh3gaj58'; // REPLACE
    $api_key = '897155443339674';       // REPLACE
    $api_secret = '1wFe_abn1-1p6KyEyFZak-ovigs'; // REPLACE

    $timestamp = time();
    $signature = sha1("public_id={$row['cloud_public_id']}&timestamp=$timestamp$api_secret");

    $ch = curl_init("https://api.cloudinary.com/v1_1/$cloud_name/image/destroy");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'public_id' => $row['cloud_public_id'],
        'api_key' => $api_key,
        'timestamp' => $timestamp,
        'signature' => $signature
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $res = json_decode($response, true);
    if ($res['result'] !== 'ok') {
        // Log error (optional)
        error_log("Cloudinary delete failed: " . print_r($res, true));
    }
} elseif (!empty($row['filename']) && strpos($row['filename'], 'http') !== 0) {
    // Local file delete fallback
    $file = __DIR__ . '/uploads/' . basename($row['filename']);
    if (file_exists($file)) unlink($file);
}

// Delete from DB
$del = $conn->prepare("DELETE FROM artwork WHERE id = ?");
$del->bind_param("i", $id);
$del->execute();
$del->close();

$redirect = $is_own ? 'dashboard.php?deleted=1' : 'admin.php?deleted=1';
header("Location: $redirect");
exit;
?>