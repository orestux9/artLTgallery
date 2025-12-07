<?php 
session_start();
require 'db.php';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    if (strlen($username) < 3 || strlen($username) > 30) {
        $error = "Username must be 3–30 characters";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = "Username already taken";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'artist')");
            $stmt->bind_param("ss", $username, $hash);
            if ($stmt->execute()) {
                $success = "Account created! You can now log in as an artist.";
            } else {
                $error = "Something went wrong. Try again.";
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
    <title>Register - ArtVerse Gallery</title>
    <link href="tailwind.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="h-full bg-gradient-to-br from-purple-600 via-indigo-600 to-blue-700 flex items-center justify-center px-4">
    
    <div class="bg-white/95 backdrop-blur-lg p-10 rounded-3xl shadow-2xl w-full max-w-md border border-white/20">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-indigo-700 mb-2">ArtVerse</h1>
            <p class="text-gray-600">Join as an Artist</p>
        </div>

        <?php if($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-lg mb-6 text-center font-medium">
                <?= $success ?>
                <a href="login.php" class="block mt-3 text-indigo-600 underline">Go to Login →</a>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg mb-6 text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if(!$success): ?>
        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                <input type="text" name="username" required minlength="3" maxlength="30"
                       class="w-full px-5 py-4 border border-gray-300 rounded-xl focus:ring-4 focus:ring-indigo-300 focus:border-indigo-500 outline-none transition"
                       placeholder="picasso2025" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                <input type="password" name="password" required minlength="6"
                       class="w-full px-5 py-4 border border-gray-300 rounded-xl focus:ring-4 focus:ring-indigo-300 focus:border-indigo-500 outline-none transition"
                       placeholder="••••••••">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Confirm Password</label>
                <input type="password" name="confirm" required
                       class="w-full px-5 py-4 border border-gray-300 rounded-xl focus:ring-4 focus:ring-indigo-300 focus:border-indigo-500 outline-none transition"
                       placeholder="••••••••">
            </div>

            <button type="submit" 
                    class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold py-5 rounded-xl hover:from-indigo-700 hover:to-purple-700 transform hover:scale-105 transition duration-200 shadow-lg">
                Create Artist Account
            </button>
        </form>
        <?php endif; ?>

        <p class="text-center mt-8 text-gray-600">
            Already have an account? 
            <a href="login.php" class="text-indigo-600 font-bold hover:underline">Log in here</a>
        </p>
    </div>
</body>
</html>