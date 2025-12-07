<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// === DELETE WITH CLOUDINARY ===
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $res = pg_query_params($conn, "SELECT filename, cloud_public_id FROM artwork WHERE id = $1", [$id]);
        if ($row = pg_fetch_assoc($res)) {
            if (!empty($row['cloud_public_id'])) {
                $cloud_name = 'dyh3gaj58';
                $api_key    = '897155443339674';
                $api_secret = '1wFe_abn1-1p6KyEyFZak-ovigs';

                $timestamp  = time();
                $signature  = sha1("public_id={$row['cloud_public_id']}&timestamp=$timestamp$api_secret");

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
        }
        pg_query_params($conn, "DELETE FROM artwork WHERE id = $1", [$id]);
    }
    header("Location: admin.php?deleted=1");
    exit;
}

// Fetch all artworks
$res = pg_query($conn, "SELECT a.id, a.title, a.filename, a.uploaded_at, u.username FROM artwork a JOIN users u ON a.artist_id = u.id ORDER BY a.uploaded_at DESC");
$artworks = pg_fetch_all($res) ?: [];
?>

<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel • ArtVerse</title>
    <link href="tailwind.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-indigo-900 to-purple-900 min-h-screen text-white">
    <?php if (isset($_GET['deleted'])): ?>
        <div class="fixed top-4 left-1/2 -translate-x-1/2 bg-green-600 px-8 py-4 rounded-xl shadow-2xl z-50 animate-pulse font-bold">
            Kūrinys ištrintas!
        </div>
    <?php endif; ?>

    <nav class="bg-black/30 backdrop-blur-lg sticky top-0 z-40 p-6 shadow-2xl">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-3xl font-bold">ArtVerse <span class="text-red-500">Admin</span></h1>
            <div class="space-x-6">
                <a href="/api/index.php" class="bg-indigo-600 hover:bg-indigo-700 px-6 py-3 rounded-xl font-bold">Grįžti į galeriją</a>
                <a href="/api/logout.php" class="bg-red-600 hover:bg-red-700 px-6 py-3 rounded-xl font-bold">Atsijungti</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 py-12">
        <h2 class="text-5xl font-bold text-center mb-12">Administratoriaus panelė</h2>

        <?php if (empty($artworks)): ?>
            <p class="text-center text-2xl text-gray-400 py-20">Nėra kūrinių</p>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                <?php foreach($artworks as $art): ?>
                    <div class="bg-white/10 backdrop-blur-lg rounded-3xl overflow-hidden shadow-2xl relative group">
                        <img src="<?= htmlspecialchars($art['filename']) ?>" class="w-full h-64 object-cover">
                        <a href="/api/admin.php?delete=<?= $art['id'] ?>" 
                           class="absolute top-4 right-4 z-20 bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-xl font-bold shadow-2xl transition transform hover:scale-110">
                           Ištrinti
                        </a>
                        <div class="p-6">
                            <h3 class="text-xl font-bold"><?= htmlspecialchars($art['title']) ?></h3>
                            <p class="text-sm opacity-80">by <?= htmlspecialchars($art['username']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>