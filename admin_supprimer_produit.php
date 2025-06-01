<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: tout_parcourir.php');
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: admin_dashboard.php?message=produit_supprime");
        exit;
    } catch (Exception $e) {
        echo "Erreur lors de la suppression : " . $e->getMessage();
    }
} else {
    echo "ID produit manquant.";
} ?>
