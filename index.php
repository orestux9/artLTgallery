<?php
session_start();
require 'db.php';
$result = pg_query($conn, "SELECT a.*, u.username FROM artwork a JOIN users u ON a.artist_id = u.id ORDER BY a.uploaded_at DESC");
?>
<!DOCTYPE html>
<html lang="lt" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Skaitmeninė Lietuvos meno galerija</title>
  <link href="tailwind.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; }
    h1, h2, h3 { font-family: 'Playfair Display', serif; }
  </style>
</head>
<body class="bg-gray-50 text-gray-800">
  <?php if(isset($_GET['loggedout'])): ?>
  <div id="logout-msg" class="fixed top-4 right-4 bg-green-600 text-white px-8 py-4 rounded-xl shadow-2xl z-50 animate-pulse text-lg font-bold">
    Sėkmingai atsijungėte!
  </div>
  <script>setTimeout(() => document.getElementById('logout-msg')?.remove(), 4000);</script>
  <?php endif; ?>

  <!-- Navbar -->
  <nav class="bg-white shadow-xl sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-5 flex justify-between items-center">
      <h1 class="text-4xl font-bold text-indigo-600">ArtVerse</h1>
      <div class="space-x-6">
        <?php if(isset($_SESSION['user'])): ?>
          <?php if($_SESSION['role'] === 'artist'): ?>
            <a href="dashboard.php" class="text-indigo-600 font-semibold hover:underline">Mano galerija</a>
          <?php endif; ?>
          <?php if($_SESSION['role'] === 'admin'): ?>
            <a href="admin.php" class="text-red-600 font-bold hover:underline">Admin</a>
          <?php endif; ?>
          <a href="logout.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl font-medium transition">Atsijungti</a>
        <?php else: ?>
          <a href="login.php" class="text-indigo-600 font-semibold hover:underline">Prisijungti</a>
          <a href="register.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl font-medium transition">Registruotis</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <section class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 text-white py-32">
    <div class="max-w-7xl mx-auto px-6 text-center">
      <h2 class="text-5xl md:text-7xl font-bold mb-6 leading-tight">Lietuvos Skaitmeninė Meno Galerija</h2>
      <p class="text-xl md:text-2xl opacity-90">Kuriami ir dalinamasi kūriniais</p>
    </div>
  </section>

  <!-- Gallery -->
  <section class="max-w-7xl mx-auto px-6 py-20">
    <?php if (pg_num_rows($result) === 0): ?>
      <p class="text-center text-3xl text-gray-500 py-32">Kol kas nėra kūrinių. Būk pirmas!</p>
    <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-10">
        <?php while($row = pg_fetch_assoc($result)): ?>
          <div class="group bg-white rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-500 overflow-hidden flex flex-col">
            <div class="relative overflow-hidden">
              <img src="<?= htmlspecialchars($row['filename']) ?>" alt="<?= htmlspecialchars($row['title']) ?>" class="w-full h-72 object-cover group-hover:scale-110 transition-transform duration-700">
            </div>
            <div class="p-6 bg-white border-t-4 border-purple-500">
              <h3 class="text-xl font-bold text-gray-800 mb-1"><?= htmlspecialchars($row['title']) ?></h3>
              <p class="text-sm text-gray-600">by <span class="font-medium text-indigo-600"><?= htmlspecialchars($row['username']) ?></span></p>
              <p class="text-xs text-gray-500 mt-3"><?= date('Y-m-d', strtotime($row['uploaded_at'])) ?></p>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php endif; ?>
  </section>
</body>
</html>