<?php
require_once 'config.php'; // Assure-toi que $pdo est bien configuré
include_once 'header.php';  // Contient <html><head><body>, navbar et styles

// Données de l'admin
$username = 'Lyam';
$email = 'lyamlapous@gmail.com';
$password = 'lyamboss';
$is_admin = 1;

// Hash du mot de passe
$password_hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $sql = "INSERT INTO users (username, email, password_hash, is_admin) VALUES (:username, :email, :password_hash, :is_admin)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':username' => $username,
        ':email' => $email,
        ':password_hash' => $password_hash,
        ':is_admin' => $is_admin,
    ]);
    echo "Utilisateur admin inséré avec succès.";
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
