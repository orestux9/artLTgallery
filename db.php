<?php
$conn = new mysqli("localhost", "root", "", "art_gallery");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
?>