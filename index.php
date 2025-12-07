<?php
session_start();
require 'db.php'; 
$result = $conn->query("SELECT a.*, u.username FROM artwork a JOIN users u ON a.artist_id = u.id ORDER BY a.uploaded_at DESC");
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
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

  <!-- Logout Message -->
  <?php if(isset($_GET['loggedout'])): ?>
  <div id="logout-msg" class="fixed top-4 right-4 bg-green-600 text-white px-8 py-4 rounded-xl shadow-2xl z-50 animate-pulse text-lg font-bold">
    Sėkmingai atsijungėte!
  </div>
  <script>setTimeout(() => document.getElementById('logout-msg')?.remove(), 4000);</script>
  <?php endif; ?>

  <!-- Navbar -->
  <nav class="bg-white shadow-xl sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-5 flex justify-between items-center">
      <h1 class="text-4xl font-bold text-indigo-600">Galerija</h1>
      <div class="space-x-6">
        <?php if(isset($_SESSION['user'])): ?>
          <?php if($_SESSION['role'] === 'artist'): ?>
            <a href="dashboard.php" class="text-indigo-600 font-semibold hover:underline">Valdymas</a>
          <?php endif; ?>
          <?php if($_SESSION['role'] === 'admin'): ?>
            <a href="admin.php" class="text-red-600 font-bold hover:underline">Admin panelė</a>
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
      <h2 class="text-5xl md:text-7xl font-bold mb-6 leading-tight">Skaitmeninė Lietuvos meno galerija</h2>
      <p class="text-xl md:text-2xl opacity-90">Lietuvos menininkų meno kūriniai</p>
    </div>
  </section>

  <!-- Gallery Grid - FIXED: Title & Artist always visible below -->
  <section class="max-w-7xl mx-auto px-6 py-20">
    <?php if ($result->num_rows === 0): ?>
      <div class="text-center py-32">
        <p class="text-3xl text-gray-500">Dar kol kas nėra meno kūrinių. Įkelkite pirmas!</p>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-10">
        <?php while($row = $result->fetch_assoc()): ?>
          <div class="group bg-white rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-500 overflow-hidden flex flex-col">
            
            <!-- Image with hover zoom -->
            <div class="relative overflow-hidden">
              <img src="<?= htmlspecialchars($row['filename']) ?>" 
                   alt="<?= htmlspecialchars($row['title']) ?>"
                   class="w-full h-72 object-cover group-hover:scale-110 transition-transform duration-700">
              
              <!-- Optional: Hover overlay (you can remove if you want clean look) -->
              <!-- <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex items-end p-6">
                <div class="text-white">
                  <h3 class="text-2xl font-bold"><?= htmlspecialchars($row['title']) ?></h3>
                  <p class="text-sm opacity-90">by <?= htmlspecialchars($row['username']) ?></p>
                </div>
              </div> -->
            </div>

            <!-- Title & Artist - Always visible below image -->
            <div class="p-6 bg-white border-t-4 border-purple-500 flex-1 flex flex-col justify-between">
              <div>
                <h3 class="text-xl font-bold text-gray-800 mb-1"><?= htmlspecialchars($row['title']) ?></h3>
                <p class="text-sm text-gray-600">by <span class="font-medium text-indigo-600"><?= htmlspecialchars($row['username']) ?></span></p>
              </div>
              <p class="text-xs text-gray-500 mt-3"><?= date('M j, Y', strtotime($row['uploaded_at'])) ?></p>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php endif; ?>
  </section>


</body>
</html>