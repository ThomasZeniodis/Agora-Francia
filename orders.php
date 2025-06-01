<?php
include_once 'header.php';
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer commandes de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
  <h2>Mes commandes</h2>

  <?php if (empty($orders)): ?>
    <p class="text-light">Vous n'avez pas encore passé de commande.</p>
  <?php else: ?>
    <?php foreach ($orders as $order): ?>
      <div class="card mb-4 bg-dark text-light border border-warning">
        <div class="card-header">
          <strong>Commande #<?= htmlspecialchars($order['id']) ?></strong> - Le <?= date('d/m/Y à H:i', strtotime($order['created_at'])) ?>
          <span class="float-end">Total : <strong><?= number_format($order['total_price'], 2) ?> €</strong></span>
        </div>
        <div class="card-body">
          <?php
          // Récupérer les items de la commande
          $stmt_items = $pdo->prepare("SELECT oi.quantity, oi.price, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
          $stmt_items->execute([$order['id']]);
          $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
          ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($items as $item): ?>
              <li class="list-group-item bg-dark text-light border-warning">
                <?= htmlspecialchars($item['name']) ?> x <?= $item['quantity'] ?> - <?= number_format($item['price'] * $item['quantity'], 2) ?> €
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php include_once 'footer.php'; ?>
