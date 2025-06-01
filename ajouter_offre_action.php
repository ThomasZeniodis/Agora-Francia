<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $starting_price = $_POST['starting_price'];

    $stmt = $pdo->prepare("INSERT INTO auctions (product_id, start_time, end_time, highest_bid) VALUES (?, ?, ?, ?)");
    $stmt->execute([$product_id, $start_time, $end_time, $starting_price]);

    header('Location: meilleure_offre.php?success=1');
    exit;
} else {
    echo "Méthode non autorisée.";
}
