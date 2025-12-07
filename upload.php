<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'artist') {
    header("Location: login.php");
    exit;
}

define('CLOUDINARY_CLOUD_NAME', 'dyh3gaj58');
define('CLOUDINARY_API_KEY',    '897155443339674');
define('CLOUDINARY_API_SECRET', '1wFe_abn1-1p6KyEyFZak-ovigs');
define('CLOUDINARY_UPLOAD_PRESET', 'artverse_unsigned');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    if (empty($title) || strlen($title) > 100) {
        $errors[] = "Pavadinimas privalomas (max 100 ženklų)";
    }

    $file = $_FILES['art'] ?? null;
    if (!$file || $file['error'] !== UPLOAD_ERR_OK || $file['size'] > 10*1024*1024) {
        $errors[] = "Pasirinkite galiojantį paveikslėlį (max 10MB)";
    }

    if (empty($errors)) {
        $url = "https://api.cloudinary.com/v1_1/" . CLOUDINARY_CLOUD_NAME . "/upload";
        $post_fields = [
            'file'          => new CURLFile($file['tmp_name'], $file['type'], $file['name']),
            'upload_preset' => CLOUDINARY_UPLOAD_PRESET,
            'folder'        => 'artverse'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['secure_url'])) {
            $cloud_url = $result['secure_url'];
            $public_id = $result['public_id'] ?? null;

            $query = "INSERT INTO artwork (title, artist_id, filename, cloud_public_id) VALUES ($1, $2, $3, $4)";
            pg_query_params($conn, $query, [$title, $_SESSION['user_id'], $cloud_url, $public_id]);

            header("Location: dashboard.php?uploaded=1");
            exit;
        } else {
            $errors[] = "Įkėlimo klaida: " . ($result['error']['message'] ?? 'Nežinoma klaida');
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Įkelti kūrinį</title>
    <link href="tailwind.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 min-h-screen flex items-center justify-center">
<div class="bg-white p-10 rounded-3xl shadow-2xl max-w-lg w-full">
    <h1 class="text-4xl font-bold text-center mb-8 text-indigo-700">Įkelti kūrinį</h1>
    <?php foreach($errors as $e): ?>
        <div class="bg-red-100 text-red-700 p-4 rounded mb-4"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Kūrinio pavadinimas" required maxlength="100" class="w-full p-4 border-2 border-indigo-200 rounded-xl mb-6 text-lg">
        <input type="file" name="art" accept="image/*" required class="w-full p-4 border-2 border-dashed border-indigo-300 rounded-xl mb-8">
        <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-5 rounded-xl font-bold text-xl hover:from-indigo-700 hover:to-purple-700 transition shadow-xl">
            Įkelti į galeriją
        </button>
    </form>
    <p class="text-center mt-6"><a href="dashboard.php" class="text-indigo-600 hover:underline">← Grįžti</a></p>
</div>
</body>
</html>