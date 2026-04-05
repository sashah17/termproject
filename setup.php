<?php
require_once __DIR__ . '/includes/db.php';
$users = [
  ['email'=>'admin@voltmarket.com','password'=>'Admin1234!'],
  ['email'=>'seller@voltmarket.com','password'=>'Seller123!'],
  ['email'=>'john@example.com','password'=>'Password1!'],
];
$stmt = $pdo->prepare("UPDATE users SET password=? WHERE email=?");
foreach($users as $u){ $hash=password_hash($u['password'],PASSWORD_DEFAULT); $stmt->execute([$hash,$u['email']]); echo "✓ ".$u['email']."\n"; }
echo "\n✅ Setup complete!\n";
echo "Admin:  admin@voltmarket.com  / Admin1234!\n";
echo "Seller: seller@voltmarket.com / Seller123!\n";
echo "User:   john@example.com      / Password1!\n";
echo "\nDelete this file after setup.\n";
