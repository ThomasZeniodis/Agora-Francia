<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendeur') {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: dashboard_vendeur.php');
    exit;
}

$item_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Vérifier que l'item appartient bien au vendeur
$stmt = $pdo->prepare("SELECT id FROM items WHERE id = ? AND vendeur_id = ?");
$stmt->execute([$item_id, $user_id]);
$item = $stmt->fetch();

if (!$item) {
    header('Location: dashboard_vendeur.php');
    exit;
}

// Supprimer l'item
$stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
$stmt->execute([$item_id]);

header('Location: dashboard_vendeur.php');
exit;
