<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'artist') {
    header("Location: login.php");
    exit;
}

// ——— CLOUDINARY CONFIG ———
define('CLOUDINARY_CLOUD_NAME', 'dyh3gaj58');     // ← change
define('CLOUDINARY_API_KEY',    '897155443339674');        // ← change
define('CLOUDINARY_API_SECRET', '1wFe_abn1-1p6KyEyFZak-ovigs');    // ← change
define('CLOUDINARY_UPLOAD_PRESET', 'artverse_unsigned');     // we'll create this

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');

    if (empty($title) || strlen($title) > 100) {
        $errors[] = "Title required (max 100 chars)";
    }

    $file = $_FILES['art'] ?? null;
    if (!$file || $file['error'] !== UPLOAD_ERR_OK || $file['size'] > 10*1024*1024) {
        $errors[] = "Valid image required (max 10MB)";
    }

    if (empty($errors)) {
        // ——— UPLOAD TO CLOUDINARY ———
        $url = "https://api.cloudinary.com/v1_1/" . CLOUDINARY_CLOUD_NAME . "/upload";
        
        $post_fields = [
            'file'           => new CURLFile($file['tmp_name'], $file['type'], $file['name']),
            'upload_preset'  => CLOUDINARY_UPLOAD_PRESET,
            'folder'         => 'artverse'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['secure_url'])) {
            $cloud_url = $result['secure_url'];
            $public_id = $result['public_id'];

            // Save only the URL in your database (no local file!)
            $stmt = $conn->prepare("INSERT INTO artwork (title, artist_id, filename, cloud_public_id) 
                                    VALUES (?, ?, ?, ?)");
            // We add a new column: cloud_public_id (for deletion later)
            $stmt->bind_param("siss", $title, $_SESSION['user_id'], $cloud_url, $public_id);
            
            if ($stmt->execute()) {
                header("Location: dashboard.php?uploaded=1");
                exit;
            } else {
                $errors[] = "Database error";
            }
            $stmt->close();
        } else {
            $errors[] = "Upload failed: " . ($result['error']['message'] ?? 'Unknown error');
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Upload • ArtVerse</title>
    <link href="tailwind.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
<div class="bg-white p-10 rounded-2xl shadow-2xl max-w-lg w-full">
    <h1 class="text-3xl font-bold text-center mb-8">Įkelti kūrinį</h1>

    <?php foreach($errors as $e): ?>
        <div class="bg-red-100 text-red-700 p-4 rounded mb-4"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Meno kūrinio pavadinimas" required maxlength="100"
               class="w-full p-4 border rounded-lg mb-6 text-lg">

        <input type="file" name="art" accept="image/*" required
               class="w-full p-4 border rounded-lg mb-8">

        <button type="submit"
                class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-black py-5 rounded-xl font-bold text-xl hover:from-purple-700 hover:to-indigo-700 transition">
            Įkelti kūrinį
        </button>
    </form>
    <p class="text-center mt-6"><a href="dashboard.php" class="text-indigo-600">← Grįžti atgal</a></p>
</div>
</body>
</html>