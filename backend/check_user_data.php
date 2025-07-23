<?php

require_once 'src/Core/Database.php';
use App\Core\Database;

$db = Database::getInstance();
$stmt = $db->query('SELECT id, username, email, full_name, position, extension FROM users WHERE id = 10');
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Current user data:\n";
echo "ID: " . $user['id'] . "\n";
echo "Username: " . $user['username'] . "\n";
echo "Email: " . $user['email'] . "\n";
echo "Full Name: " . $user['full_name'] . "\n";
echo "Position: " . $user['position'] . "\n";
echo "Extension: " . $user['extension'] . "\n";