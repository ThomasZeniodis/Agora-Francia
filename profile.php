<?php
include_once 'header.php';
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer infos utilisateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<p>Utilisateur introuvable.</p>";
    exit;
}

$user_type = $user['user_type'] ?? 'client';

// Récupérer les commandes si client
$orders = [];
if ($user_type === 'client') {
    $stmt_orders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt_orders->execute([$user_id]);
    $orders = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);
}

// Récupérer produits / ventes si vendeur
$sales = [];
if ($user_type === 'vendeur') {
    $stmt_products = $pdo->prepare("SELECT * FROM products WHERE seller_id = ?");
    $stmt_products->execute([$user_id]);
    $products = $stmt_products->fetchAll(PDO::FETCH_ASSOC);

    $stmt_sales = $pdo->prepare("
        SELECT o.* FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE p.seller_id = ?
        ORDER BY o.created_at DESC
    ");
    $stmt_sales->execute([$user_id]);
    $sales = $stmt_sales->fetchAll(PDO::FETCH_ASSOC);
}

// Message changement de mdp
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    
    if ($new_password && $new_password === $confirm) {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$hash, $user_id]);
        $message = "Mot de passe mis à jour avec succès.";
    } else {
        $message = "Les mots de passe ne correspondent pas.";
    }
}
?>

<div class="container mt-5" style="max-width: 900px;">
    <h2 class="mb-4 text-center" style="font-family: 'Playfair Display', serif; color: #bfa37c;">Mon profil</h2>
    
    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Informations utilisateur -->
    <div class="card bg-dark text-light border-light mb-4">
        <div class="card-body">
            <h5 class="card-title text-light">Informations du compte</h5>
            <p><strong>Nom :</strong> <?= htmlspecialchars($user['last_name'] ?? '') ?></p>
            <p><strong>Prénom :</strong> <?= htmlspecialchars($user['first_name'] ?? '') ?></p>
            <p><strong>Adresse :</strong> <?= htmlspecialchars($user['address'] ?? 'Non renseignée') ?></p>
            <p><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Type de compte :</strong> <?= ucfirst(htmlspecialchars($user_type)) ?></p>
            <p><strong>Informations de paiement :</strong> 
                <?= $user['payment_info'] ? str_repeat('•', 12) . ' (sécurisé)' : 'Non renseigné' ?>
            </p>
        </div>
    </div>

    <!-- Clause légale -->
    <div class="card bg-dark text-light border-light mb-4">
        <div class="card-body">
            <h5 class="card-title">Clause légale</h5>
            <p class="card-text">
                En utilisant Agora Francia, vous acceptez que toute offre que vous faites sur un article constitue un engagement légal. 
                Si le vendeur accepte votre offre, vous êtes juridiquement tenu(e) d'acheter le produit concerné.
            </p>
        </div>
    </div>

    <!-- Formulaire changement mot de passe -->
    <div class="card bg-dark text-light border-light mb-4">
        <div class="card-body">
            <h5 class="card-title">Changer le mot de passe</h5>
            <form method="POST" action="profile.php">
                <div class="mb-3">
                    <label for="new_password" class="form-label">Nouveau mot de passe</label>
                    <input type="password" name="new_password" id="new_password" required class="form-control bg-dark text-light border-secondary">
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                    <input type="password" name="confirm_password" id="confirm_password" required class="form-control bg-dark text-light border-secondary">
                </div>
                <button type="submit" class="btn btn-outline-light">Mettre à jour</button>
            </form>
        </div>
    </div>

    <?php if ($user_type === 'client'): ?>
        <!-- Commandes client -->
        <div class="card bg-dark text-light border-light mb-4">
            <div class="card-body">
                <h5 class="card-title">Historique des commandes</h5>
                <?php if ($orders): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($orders as $order): ?>
                            <li class="list-group-item bg-dark text-light border-secondary">
                                Commande #<?= $order['id'] ?> - <?= number_format($order['total_price'], 2) ?> € - <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Aucune commande passée.</p>
                <?php endif; ?>
            </div>
        </div>

    <?php elseif ($user_type === 'vendeur'): ?>
        <!-- Produits et ventes vendeur -->
        <div class="card bg-dark text-light border-light mb-4">
            <div class="card-body">
                <h5 class="card-title">Mes produits</h5>
                <?php if (!empty($products)): ?>
                    <ul class="list-group list-group-flush mb-3">
                        <?php foreach ($products as $prod): ?>
                            <li class="list-group-item bg-dark text-light border-secondary">
                                <?= htmlspecialchars($prod['name']) ?> - <?= number_format($prod['price'], 2) ?> €
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Vous n'avez pas encore ajouté de produits.</p>
                <?php endif; ?>
                <a href="ajout_produit.php" class="btn btn-outline-light">Ajouter un nouveau produit</a>
            </div>
        </div>

        <div class="card bg-dark text-light border-light">
            <div class="card-body">
                <h5 class="card-title">Mes ventes récentes</h5>
                <?php if (!empty($sales)): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($sales as $sale): ?>
                            <li class="list-group-item bg-dark text-light border-secondary">
                                Vente #<?= $sale['id'] ?> - <?= number_format($sale['total_price'], 2) ?> € - <?= date('d/m/Y H:i', strtotime($sale['created_at'])) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Aucune vente enregistrée.</p>
                <?php endif; ?>
            </div>
        </div>

    <?php elseif ($user_type === 'admin'): ?>
        <!-- Admin -->
        <div class="card bg-dark text-light border-light">
            <div class="card-body">
                <h5 class="card-title">Administration</h5>
                <ul>
                    <li><a href="admin_dashboard.php">Tableau de bord admin</a></li>
                    <li><a href="gestion_utilisateurs.php">Gérer les utilisateurs</a></li>
                    <li><a href="gestion_produits.php">Gérer les produits</a></li>
                    <li><a href="rapports.php">Rapports et statistiques</a></li>
                </ul>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include_once 'footer.php'; ?>

