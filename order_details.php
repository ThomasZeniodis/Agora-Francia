<?php
include_once 'header.php';
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: profile.php');
    exit;
}

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Vérifier que la commande appartient bien à l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Commande introuvable ou accès refusé.</div></div>";
    include_once 'footer.php';
    exit;
}

// Récupérer les articles de la commande
$stmt_items = $pdo->prepare("
    SELECT oi.quantity, oi.price, p.name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container mt-5" style="max-width: 700px;">
    <h2>Détails de la commande #<?= $order_id ?></h2>
    <p><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
    <p><strong>Montant total :</strong> <?= number_format($order['total_price'], 2) ?> €</p>

    <table class="table table-dark table-striped">
        <thead>
            <tr>
                <th>Produit</th>
                <th>Quantité</th>
                <th>Prix unitaire</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $total = 0;
            foreach ($items as $item):
                $subtotal = $item['quantity'] * $item['price'];
                $total += $subtotal;
            ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td><?= number_format($item['price'], 2) ?> €</td>
                <td><?= number_format($subtotal, 2) ?> €</td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3" class="text-end"><strong>Total :</strong></td>
                <td><strong><?= number_format($total, 2) ?> €</strong></td>
            </tr>
        </tbody>
    </table>

    <a href="profile.php" class="btn btn-secondary">Retour à mon profil</a>
</div>

<?php include_once 'footer.php'; ?>
