<?php
require_once 'config.php';
include_once 'header.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Suppression d'un article du panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_product_id'])) {
    $removeId = (int) $_POST['remove_product_id'];
    if (isset($_SESSION['cart'][$removeId])) {
        unset($_SESSION['cart'][$removeId]);
    }
    // Redirection pour éviter resoumission du formulaire
    header('Location: panier.php');
    exit;
}

$cart = $_SESSION['cart'] ?? [];

$total = 0;
$products = [];

if ($cart) {
    $placeholders = implode(',', array_fill(0, count($cart), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute(array_keys($cart));
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="container mt-5 text-white">
  <h2>🛒 Mon panier</h2>

  <?php if (empty($products)): ?>
    <p>Votre panier est vide.</p>
  <?php else: ?>
    <div class="row">
      <?php foreach ($products as $product):
        $qty = $cart[$product['id']];
        $subtotal = $product['price'] * $qty;
        $total += $subtotal;
        $transaction = $product['transaction_type'] ?? 'achat';
        $status = $product['transaction_status'] ?? 'en_cours';
      ?>
      <div class="col-md-6 mb-4">
        <div class="card bg-dark text-white">
          <div class="row g-0">
            <div class="col-md-4">
              <img src="<?= htmlspecialchars($product['image_url'] ?? 'placeholder.jpg') ?>" class="img-fluid rounded-start" alt="Produit">
            </div>
            <div class="col-md-8">
              <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                <p class="card-text"><?= htmlspecialchars($product['description'] ?? 'Pas de description') ?></p>
                <p class="card-text">Quantité : <?= $qty ?></p>
                <p class="card-text">Prix unitaire : <?= number_format($product['price'], 2) ?> €</p>
                <p class="card-text"><strong>Total : <?= number_format($subtotal, 2) ?> €</strong></p>
                <p class="card-text">
                  <span class="badge bg-secondary">
                    <?= ucfirst($transaction) ?>
                  </span>
                </p>

                <?php if (($transaction === 'enchere' || $transaction === 'negociation') && $status === 'valide'): ?>
                  <div class="alert alert-success p-2">✅ Paiement automatique effectué.</div>
                <?php elseif ($transaction === 'achat'): ?>
                  <form method="POST" action="checkout.php" style="display:inline-block;">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <button type="submit" class="btn btn-primary">Passer à la commande</button>
                  </form>
                <?php else: ?>
                  <div class="alert alert-warning p-2">⏳ En attente de validation.</div>
                <?php endif; ?>

                <!-- Formulaire suppression -->
                <form method="POST" action="panier.php" style="display:inline-block; margin-left:10px;">
                  <input type="hidden" name="remove_product_id" value="<?= $product['id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                </form>

              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="text-end mt-4">
      <h4>Total du panier : <strong><?= number_format($total, 2) ?> €</strong></h4>
    </div>
  <?php endif; ?>
</div>

<?php include_once 'footer.php'; ?>

