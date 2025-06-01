<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';
include_once 'header.php';

$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et sécuriser les données
    $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
    $prenom = isset($_POST['prenom']) ? trim($_POST['prenom']) : '';
    $adresse1 = isset($_POST['adresse1']) ? trim($_POST['adresse1']) : '';
    $adresse2 = isset($_POST['adresse2']) ? trim($_POST['adresse2']) : '';
    $ville = isset($_POST['ville']) ? trim($_POST['ville']) : '';
    $code_postal = isset($_POST['code_postal']) ? trim($_POST['code_postal']) : '';
    $pays = isset($_POST['pays']) ? trim($_POST['pays']) : '';
    $telephone = isset($_POST['telephone']) ? trim($_POST['telephone']) : '';
    $type_carte = isset($_POST['type_carte']) ? trim($_POST['type_carte']) : '';
    $numero_carte = isset($_POST['numero_carte']) ? trim($_POST['numero_carte']) : '';
    $nom_carte = isset($_POST['nom_carte']) ? trim($_POST['nom_carte']) : '';
    $expiration = isset($_POST['expiration']) ? trim($_POST['expiration']) : '';
    $code_securite = isset($_POST['code_securite']) ? trim($_POST['code_securite']) : '';

    // Validation simple
    if (!$nom) $errors[] = "Le nom est requis.";
    if (!$prenom) $errors[] = "Le prénom est requis.";
    if (!$adresse1) $errors[] = "L'adresse ligne 1 est requise.";
    if (!$ville) $errors[] = "La ville est requise.";
    if (!$code_postal) $errors[] = "Le code postal est requis.";
    if (!$pays) $errors[] = "Le pays est requis.";
    if (!$telephone) $errors[] = "Le téléphone est requis.";
    if (!$type_carte) $errors[] = "Le type de carte est requis.";
    if (!$numero_carte) $errors[] = "Le numéro de carte est requis.";
    if (!$nom_carte) $errors[] = "Le nom sur la carte est requis.";
    if (!$expiration) $errors[] = "La date d'expiration est requise.";
    if (!$code_securite) $errors[] = "Le code de sécurité est requis.";

    // Si pas d'erreurs, insertion
    if (empty($errors)) {
        $user_id = $_SESSION['user_id'] ?? null;
        $cart = $_SESSION['cart'] ?? [];

        if (!$user_id || empty($cart)) {
            $message = "Vous devez être connecté et avoir un panier non vide.";
        } else {
            // Calcul du total
            $placeholders = implode(',', array_fill(0, count($cart), '?'));
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
            $stmt->execute(array_keys($cart));
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $total = 0;
            foreach ($products as $p) {
                $total += $p['price'] * $cart[$p['id']];
            }

            // Insertion dans orders
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, nom, prenom, adresse1, adresse2, ville, code_postal, pays, telephone, type_carte, numero_carte, nom_carte, expiration, code_securite, total, date_commande) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $success = $stmt->execute([
                $user_id, $nom, $prenom, $adresse1, $adresse2, $ville, $code_postal, $pays, $telephone,
                $type_carte, $numero_carte, $nom_carte, $expiration, $code_securite, $total
            ]);

            if ($success) {
                $order_id = $pdo->lastInsertId();

                // Insertion des items dans order_items
                $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                foreach ($products as $p) {
                    $qty = $cart[$p['id']];
                    $stmtItem->execute([$order_id, $p['id'], $qty, $p['price']]);
                }

                // Vider le panier
                unset($_SESSION['cart']);

                $message = "Merci pour votre commande ! Votre numéro de commande est #$order_id.";
            } else {
                $message = "Une erreur est survenue lors de la commande.";
            }
        }
    }
}
?>

<div class="container mt-5 text-white">
  <h2>Passer la commande</h2>

  <?php if ($message): ?>
    <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <?php if (!isset($order_id)): ?>

    <?php
    // Affichage des articles du panier
    $cart = $_SESSION['cart'] ?? [];
    if (!empty($cart)) {
        $placeholders = implode(',', array_fill(0, count($cart), '?'));
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
        $stmt->execute(array_keys($cart));
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo '<h3>Votre panier</h3>';
        echo '<table class="table table-bordered text-white">';
        echo '<thead><tr><th>Produit</th><th>Quantité</th><th>Prix unitaire</th><th>Total</th></tr></thead><tbody>';

        $total_panier = 0;
        foreach ($products as $product) {
            $qty = $cart[$product['id']];
            $total_produit = $product['price'] * $qty;
            $total_panier += $total_produit;
            echo '<tr>';
            echo '<td>' . htmlspecialchars($product['name']) . '</td>';
            echo '<td>' . $qty . '</td>';
            echo '<td>' . number_format($product['price'], 2) . ' €</td>';
            echo '<td>' . number_format($total_produit, 2) . ' €</td>';
            echo '</tr>';
        }

        echo '<tr><td colspan="3" class="text-end"><strong>Total panier</strong></td><td><strong>' . number_format($total_panier, 2) . ' €</strong></td></tr>';
        echo '</tbody></table>';
    } else {
        echo '<p>Votre panier est vide.</p>';
    }
    ?>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <ul>
          <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST" action="checkout.php" style="max-width: 600px; margin:auto;">
      <h4>Informations de livraison</h4>
      <div class="mb-3">
        <label for="nom" class="form-label">Nom</label>
        <input type="text" id="nom" name="nom" class="form-control" value="<?= htmlspecialchars($nom ?? '') ?>" required>
      </div>
      <div class="mb-3">
        <label for="prenom" class="form-label">Prénom</label>
        <input type="text" id="prenom" name="prenom" class="form-control" value="<?= htmlspecialchars($prenom ?? '') ?>" required>
      </div>
      <div class="mb-3">
        <label for="adresse1" class="form-label">Adresse ligne 1</label>
        <input type="text" id="adresse1" name="adresse1" class="form-control" value="<?= htmlspecialchars($adresse1 ?? '') ?>" required>
      </div>
      <div class="mb-3">
        <label for="adresse2" class="form-label">Adresse ligne 2 (optionnel)</label>
        <input type="text" id="adresse2" name="adresse2" class="form-control" value="<?= htmlspecialchars($adresse2 ?? '') ?>">
      </div>
      <div class="mb-3">
        <label for="ville" class="form-label">Ville</label>
        <input type="text" id="ville" name="ville" class="form-control" value="<?= htmlspecialchars($ville ?? '') ?>" required>
      </div>
      <div class="mb-3">
        <label for="code_postal" class="form-label">Code postal</label>
        <input type="text" id="code_postal" name="code_postal" class="form-control" value="<?= htmlspecialchars($code_postal ?? '') ?>" required>
      </div>
      <div class="mb-3">
        <label for="pays" class="form-label">Pays</label>
        <input type="text" id="pays" name="pays" class="form-control" value="<?= htmlspecialchars($pays ?? '') ?>" required>
      </div>
      <div class="mb-3">
        <label for="telephone" class="form-label">Téléphone</label>
        <input type="text" id="telephone" name="telephone" class="form-control" value="<?= htmlspecialchars($telephone ?? '') ?>" required>
      </div>

      <h4>Informations de paiement</h4>
      <div class="mb-3">
        <label for="type_carte" class="form-label">Type de carte</label>
        <select id="type_carte" name="type_carte" class="form-select" required>
          <option value="">-- Choisir --</option>
          <option value="Visa" <?= (isset($type_carte) && $type_carte == 'Visa') ? 'selected' : '' ?>>Visa</option>
          <option value="Mastercard" <?= (isset($type_carte) && $type_carte == 'Mastercard') ? 'selected' : '' ?>>Mastercard</option>
          <option value="American Express" <?= (isset($type_carte) && $type_carte == 'American Express') ? 'selected' : '' ?>>American Express</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="numero_carte" class="form-label">Numéro de carte</label>
        <input type="text" id="numero_carte" name="numero_carte" class="form-control" value="<?= htmlspecialchars($numero_carte ?? '') ?>" required>
      </div>
      <div class="mb-3">
        <label for="nom_carte" class="form-label">Nom sur la carte</label>
        <input type="text" id="nom_carte" name="nom_carte" class="form-control" value="<?= htmlspecialchars($nom_carte ?? '') ?>" required>
      </div>
      <div class="mb-3">
        <label for="expiration" class="form-label">Date d'expiration (MM/AA)</label>
        <input type="text" id="expiration" name="expiration" class="form-control" value="<?= htmlspecialchars($expiration ?? '') ?>" required>
      </div>
      <div class="mb-3">
        <label for="code_securite" class="form-label">Code de sécurité</label>
        <input type="text" id="code_securite" name="code_securite" class="form-control" value="<?= htmlspecialchars($code_securite ?? '') ?>" required>
      </div>

      <button type="submit" class="btn btn-primary w-100">Valider la commande</button>
    </form>

  <?php endif; ?>
</div>

<?php include_once 'footer.php'; ?>
