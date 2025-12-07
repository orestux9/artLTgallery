<?php
session_start();
require 'db.php';

// Destroy all session data
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Redirect to home with a nice fade-in message (optional)
header("Location: index.php?loggedout=1");
exit;
?>