<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $quantity = max(1, intval($_POST['quantity']));

    // Vérifier que le produit existe bien en base
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    if ($stmt->fetchColumn() == 0) {
        // Produit invalide, redirection avec erreur éventuelle
        $_SESSION['message'] = "Produit invalide.";
        header("Location: panier.php");
        exit;
    }

    // Initialiser le panier s’il n’existe pas
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Ajouter ou mettre à jour la quantité
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    $_SESSION['message'] = "Produit ajouté au panier.";

    header("Location: panier.php");
    exit;
}
