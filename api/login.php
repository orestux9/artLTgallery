<?php
require 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = $1";
    $result = pg_query_params($conn, $query, [$username]);

    if ($row = pg_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user']    = $row['username'];
            $_SESSION['role']    = $row['role'];
            header("Location: index.php");
            exit;
        }
    }
    $error = "Neteisingas prisijungimo vardas arba slaptažodis";
}
?>
<!DOCTYPE html>
<html class="h-full">
<head>
  <meta charset="UTF-8">
  <title>Prisijungimas į galeriją</title>
  <link href="tailwind.css" rel="stylesheet">
</head>
<body class="h-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
  <div class="bg-white p-10 rounded-2xl shadow-2xl w-full max-w-md">
    <h2 class="text-3xl font-bold text-center mb-8">Prisijungimas</h2>
    <?php if ($error): ?>
      <p class="text-red-500 text-center mb-4"><?= $error ?></p>
    <?php endif; ?>
    <form method="POST">
      <input type="text" name="username" placeholder="Prisijungimo vardas" required class="w-full p-4 mb-4 border rounded-lg">
      <input type="password" name="password" placeholder="Slaptažodis" required class="w-full p-4 mb-6 border rounded-lg">
      <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-lg hover:bg-indigo-700 text-lg font-semibold">Prisijungti</button>
    </form>
    <p class="text-center mt-6">Neturi paskyros? <a href="/api/register.php" class="text-indigo-600 font-medium">Registruokis</a></p>
  </div>
</body>
</html>