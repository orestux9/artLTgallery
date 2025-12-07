<?php
session_start();
require 'db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $user = $conn->real_escape_string($_POST['username']);
  $pass = $_POST['password'];
  $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
  $stmt->bind_param("s", $user);
  $stmt->execute();
  $res = $stmt->get_result();

  if ($res->num_rows > 0) {
    $row = $res->fetch_assoc();
    if (password_verify($pass, $row['password'])) {
      $_SESSION['user'] = $row['username'];
      $_SESSION['user_id'] = $row['id'];
      $_SESSION['role'] = $row['role'];
      header("Location: index.php");
      exit;
    }
  }
  $error = "Invalid credentials";
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
    <?php if (isset($error))
      echo "<p class='text-red-500 text-center mb-4'>$error</p>"; ?>
    <form method="POST">
      <input type="text" name="username" placeholder="Prisijungimo vardas" required class="w-full p-4 mb-4 border rounded-lg">
      <input type="password" name="password" placeholder="Slaptažodis" required class="w-full p-4 mb-6 border rounded-lg">
      <button type="submit"
        class="w-full bg-indigo-600 text-black py-4 rounded-lg hover:bg-indigo-700 text-lg font-semibold">Prisijungti</button>
    </form>
    <p class="text-center mt-6">Neturi paskyros? <a href="register.php" class="text-indigo-600 font-medium">Užsiregistruokite</a></p>
  </div>
</body>

</html>