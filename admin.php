<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// === HANDLE DELETION (WITH CLOUDINARY SUPPORT) ===
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        // Fetch artwork + cloud public_id
        $stmt = $conn->prepare("SELECT filename, cloud_public_id FROM artwork WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // === DELETE FROM CLOUDINARY IF EXISTS ===
            if (!empty($row['cloud_public_id'])) {
                // REPLACE THESE WITH YOUR CLOUDINARY CREDENTIALS
                $cloud_name = 'dyh3gaj58';     // ← CHANGE THIS
                $api_key    = '897155443339674';        // ← CHANGE THIS
                $api_secret = '1wFe_abn1-1p6KyEyFZak-ovigs';    // ← CHANGE THIS

                $timestamp  = time();
                $signature  = sha1("public_id={$row['cloud_public_id']}&timestamp=$timestamp$api_secret");

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.cloudinary.com/v1_1/$cloud_name/image/destroy");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, [
                    'public_id'  => $row['cloud_public_id'],
                    'api_key'    => $api_key,
                    'timestamp'  => $timestamp,
                    'signature'  => $signature
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                curl_close($ch);

                $res = json_decode($response, true);
                if ($res['result'] !== 'ok') {
                    error_log("Cloudinary delete failed for ID $id: " . print_r($res, true));
                }
            }
            // Fallback: delete local file if not cloud
            elseif (!empty($row['filename']) && strpos($row['filename'], 'http') !== 0) {
                $file = __DIR__ . '/uploads/' . basename($row['filename']);
                if (file_exists($file)) unlink($file);
            }
        }
        $stmt->close();

        // Delete from database
        $del = $conn->prepare("DELETE FROM artwork WHERE id = ?");
        $del->bind_param("i", $id);
        $del->execute();
        $del->close();
    }
    header("Location: admin.php?deleted=1");
    exit;
}

// Fetch all artworks
$stmt = $conn->prepare("
    SELECT a.id, a.title, a.filename, a.uploaded_at, u.username 
    FROM artwork a 
    JOIN users u ON a.artist_id = u.id 
    ORDER BY a.uploaded_at DESC
");
$stmt->execute();
$res = $stmt->get_result();
$artworks = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panelė • Galerija</title>
  <link href="tailwind.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; background: #0a0e1a; color: #e2e8f0; }
    h1, h2, h3 { font-family: 'Playfair Display', serif; }
    .glass { background: rgba(255,255,255,0.05); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.1); }
  </style>
</head>
<body class="min-h-screen">

  <?php if (isset($_GET['deleted'])): ?>
    <div class="fixed top-4 left-1/2 -translate-x-1/2 bg-green-600 text-white px-8 py-4 rounded-lg shadow-2xl z-50 animate-pulse font-bold">
      Meno kūrinys sėkmingai ištrintas!
    </div>
  <?php endif; ?>

  <nav class="glass sticky top-0 z-40 p-6 shadow-lg">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
      <h1 class="text-3xl font-bold text-white">Skaitmeninė Lietuvos meno galerija <span class="text-red-400 text-sm">Admin</span></h1>
      <div class="space-x-4">
        <a href="index.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold transition">Grįžti į galeriją</a>
        <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition">Atsijungti</a>
      </div>
    </div>
  </nav>

  <div class="max-w-7xl mx-auto px-6 py-12">
    <h2 class="text-5xl font-bold text-center mb-4 text-white">Admin Panelė</h2>
    <p class="text-center text-gray-400 mb-12">Ištrinkite norimus meno kūrinius</p>

    <?php if (empty($artworks)): ?>
      <p class="text-center text-2xl text-gray-500 py-20">Nėra meno kūrinių.</p>
    <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
        <?php foreach($artworks as $art): ?>
          <div class="glass rounded-2xl overflow-hidden shadow-2xl relative group">
            <div class="aspect-w-1 aspect-h-1 bg-gray-900">
              <img src="<?= htmlspecialchars($art['filename']) ?>" 
                   alt="<?= htmlspecialchars($art['title']) ?>"
                   class="w-full h-full object-cover">
            </div>

            <!-- Delete Button - Always visible -->
            <a href="admin.php?delete=<?= $art['id'] ?>" 
               class="absolute top-4 right-4 z-20 bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-xl font-bold shadow-2xl transition transform hover:scale-110">
               Ištrinti
            </a>

            <div class="p-6 bg-gradient-to-t from-black/80 to-transparent">
              <h3 class="text-xl font-bold text-white"><?= htmlspecialchars($art['title']) ?></h3>
              <p class="text-sm text-gray-300">by <?= htmlspecialchars($art['username']) ?></p>
              <p class="text-xs text-gray-400 mt-1"><?= date('M j, Y - H:i', strtotime($art['uploaded_at'])) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>