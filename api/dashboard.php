<?php 
session_start();
require 'db.php';
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'artist') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mano galerija - ArtVerse</title>
    <link href="tailwind.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3 { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 min-h-screen">

    <div class="max-w-6xl mx-auto px-6 py-12">
        <div class="text-center mb-12">
            <h1 class="text-5xl md:text-6xl font-bold text-indigo-800 mb-4">
                Sveiki, <span class="text-purple-600"><?= htmlspecialchars($_SESSION['user']) ?></span>
            </h1>
            <p class="text-xl text-gray-600">Valdykite jūsų kūrinių galeriją</p>
        </div>

        <?php if (isset($_GET['uploaded'])): ?>
            <div class="max-w-2xl mx-auto bg-green-100 border-2 border-green-500 text-green-800 px-8 py-6 rounded-2xl mb-10 text-center font-bold text-lg shadow-lg animate-pulse">
                Kūrinys sėkmingai įkeltas!
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
            <div class="max-w-2xl mx-auto bg-red-100 border-2 border-red-500 text-red-800 px-8 py-6 rounded-2xl mb-10 text-center font-bold text-lg shadow-lg animate-pulse">
                Kūrinys ištrintas!
            </div>
        <?php endif; ?>

        <div class="text-center mb-16">
            <a href="/api/upload.php" class="inline-block bg-gradient-to-r from-indigo-600 to-purple-700 text-white px-12 py-6 rounded-3xl text-2xl font-bold shadow-2xl hover:from-indigo-700 hover:to-purple-800 transform hover:scale-105 transition duration-300">
                + Įkelti naują kūrinį
            </a>
        </div>

        <h2 class="text-4xl font-bold text-center text-gray-800 mb-10">Jūsų galerija</h2>

        <?php
        $res = pg_query_params($conn, "SELECT * FROM artwork WHERE artist_id = $1 ORDER BY uploaded_at DESC", [$_SESSION['user_id']]);
        if (pg_num_rows($res) === 0): ?>
            <div class="text-center py-20">
                <p class="text-2xl text-gray-500 mb-8">Dar nėra kūrinių</p>
                <a href="/api/upload.php" class="text-indigo-600 text-xl underline hover:text-purple-600">Įkelkite pirmąjį!</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                <?php while($art = pg_fetch_assoc($res)): ?>
                    <div class="group relative bg-white rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2">
                        <div class="aspect-w-1 aspect-h-1 bg-gray-100">
                            <img src="<?= htmlspecialchars($art['filename']) ?>" class="w-full h-full object-cover">
                        </div>
                        <a href="/api/delete.php?id=<?= $art['id'] ?>&own=1" 
                           class="absolute top-4 right-4 z-10 bg-red-600 hover:bg-red-700 text-white px-5 py-3 rounded-xl font-bold text-sm shadow-2xl transition transform hover:scale-110">
                           Ištrinti
                        </a>
                        <div class="p-5 bg-white border-t-4 border-purple-500">
                            <h3 class="text-lg font-semibold text-center text-gray-800"><?= htmlspecialchars($art['title']) ?></h3>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

        <div class="text-center mt-16 space-x-8 text-lg">
            <a href="/api/index.php" class="text-indigo-600 font-semibold hover:underline">Grįžti į viešą galeriją</a>
            <span class="text-gray-400">•</span>
            <a href="/api/logout.php" class="text-red-600 font-semibold hover:underline">Atsijungti</a>
        </div>
    </div>
</body>
</html>