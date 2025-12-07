<?php
require 'db.php';

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $pass1    = $_POST['password'];
    $pass2    = $_POST['confirm'];

    if (strlen($username) < 3 || strlen($username) > 30) $error = "Vardas turi būti 3–30 simbolių";
    elseif ($pass1 !== $pass2) $error = "Slaptažodžiai nesutampa";
    elseif (strlen($pass1) < 6) $error = "Slaptažodis per trumpas";
    else {
        $check = pg_query_params($conn, "SELECT id FROM users WHERE username = $1", [$username]);
        if (pg_num_rows($check) > 0) {
            $error = "Toks vartotojas jau egzistuoja";
        } else {
            $hash = password_hash($pass1, PASSWORD_DEFAULT);
            $result = pg_query_params($conn, "INSERT INTO users (username, password, role) VALUES ($1, $2, 'artist')", [$username, $hash]);
            if ($result) {
                $success = "Paskyra sukurta! Galite prisijungti.";
            } else {
                $error = "Įvyko klaida";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registracija</title>
    <link href="tailwind.css" rel="stylesheet">
</head>
<body class="h-full bg-gradient-to-br from-purple-600 to-blue-700 flex items-center justify-center px-4">
    <div class="bg-white/95 backdrop-blur-lg p-10 rounded-3xl shadow-2xl w-full max-w-md border border-white/20">
        <h1 class="text-4xl font-bold text-center text-indigo-700 mb-8">ArtVerse</h1>
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-lg mb-6 text-center font-medium">
                <?= $success ?><br><a href="/api/login.php" class="underline text-indigo-600">Prisijungti</a>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg mb-6 text-center">
                <?= $error ?>
            </div>
        <?php endif; ?>
        <?php if (!$success): ?>
        <form method="POST" class="space-y-6">
            <input type="text" name="username" placeholder="Prisijungimo vardas" required minlength="3" maxlength="30" class="w-full px-5 py-4 border border-gray-300 rounded-xl focus:ring-4 focus:ring-indigo-300 outline-none" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            <input type="password" name="password" placeholder="Slaptažodis" required minlength="6" class="w-full px-5 py-4 border border-gray-300 rounded-xl focus:ring-4 focus:ring-indigo-300 outline-none">
            <input type="password" name="confirm" placeholder="Pakartokite slaptažodį" required class="w-full px-5 py-4 border border-gray-300 rounded-xl focus:ring-4 focus:ring-indigo-300 outline-none">
            <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold py-5 rounded-xl hover:from-indigo-700 hover:to-purple-700 transform hover:scale-105 transition shadow-lg">
                Sukurti paskyrą
            </button>
        </form>
        <?php endif; ?>
        <p class="text-center mt-8 text-gray-600">Jau turi paskyrą? <a href="/api/login.php" class="text-indigo-600 font-bold hover:underline">Prisijungti</a></p>
    </div>
</body>
</html>