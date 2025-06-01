<?php
require_once 'config.php';
include 'header.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

// --- Si aucun produit sélectionné, afficher la liste des produits négociables ---
if ($product_id === 0) {
    try {
        $stmt = $pdo->query("SELECT * FROM products WHERE negotiable = 1");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        echo "<div class='container mt-5 text-white'>Erreur lors de la récupération des produits.</div>";
        include 'footer.php';
        exit;
    }
    ?>

    <div class="container mt-5 text-white">
        <h2>Produits négociables</h2>

        <?php if (empty($products)): ?>
            <p>Aucun produit négociable pour le moment.</p>
        <?php else: ?>
            <div class="row">
                <?php foreach ($products as $product): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card bg-dark text-white">
                            <?php if (!empty($product['image_url'])): ?>
                                <img src="images/<?= htmlspecialchars($product['image_url']) ?>" style="max-width: 300px;" class="img-fluid my-3" alt="<?= htmlspecialchars($product['name']) ?>">

                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                                <p class="card-text">Prix : <?= number_format($product['price'], 2) ?> €</p>
                                <a href="negociation.php?product_id=<?= $product['id'] ?>" class="btn btn-primary">Négocier</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php
    include 'footer.php';
    exit;
}

// --- Sinon, on est sur la page de négociation d’un produit ---

// Récupérer le produit
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product || $product['negotiable'] != 1) {
    echo "<div class='container mt-5 text-white'>Produit introuvable ou non négociable.</div>";
    include 'footer.php';
    exit;
}

// Gestion POST pour insertion d’une nouvelle offre
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['offer_price'])) {
    $offer_price = floatval($_POST['offer_price']);

    // Vérification du nombre d'offres déjà faites par l'utilisateur sur ce produit
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM negociations WHERE product_id = ? AND user_id = ?");
    $stmt->execute([$product_id, $user_id]);
    $count = $stmt->fetchColumn();

    if ($count < 5) {
        $stmt = $pdo->prepare("INSERT INTO negociations (product_id, user_id, offer_price) VALUES (?, ?, ?)");
        $stmt->execute([$product_id, $user_id, $offer_price]);
        header("Location: negociation.php?product_id=$product_id");
        exit;
    } else {
        $error = "Vous avez atteint la limite de 5 tentatives de négociation.";
    }
}

// Récupérer l’historique des négociations de l’utilisateur pour ce produit
$stmt = $pdo->prepare("SELECT * FROM negociations WHERE product_id = ? AND user_id = ? ORDER BY created_at ASC");
$stmt->execute([$product_id, $user_id]);
$negociations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si une offre a été acceptée
$accepted = false;
$final_price = 0;
foreach ($negociations as $nego) {
    if ($nego['seller_response'] === 'accepted') {
        $accepted = true;
        $final_price = $nego['offer_price'];
        break;
    }
}
?>

<div class="container mt-5 text-white">
    <h2>Négociation pour : <?= htmlspecialchars($product['name']) ?></h2>
    <?php if (!empty($product['image_url'])): ?>
        <img src="<?= htmlspecialchars($product['image_url']) ?>" style="max-width: 300px;" class="img-fluid my-3" alt="<?= htmlspecialchars($product['name']) ?>">
    <?php endif; ?>
    <p><strong>Prix initial :</strong> <?= number_format($product['price'], 2) ?> €</p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($accepted): ?>
        <div class="alert alert-success">
            Votre offre de <?= number_format($final_price, 2) ?> € a été acceptée !<br>
            Vous êtes désormais engagé à acheter ce produit.
        </div>
    <?php elseif (count($negociations) >= 5): ?>
        <div class="alert alert-warning">
            Négociation terminée : vous avez utilisé vos 5 tentatives.
        </div>
    <?php else: ?>
        <form method="POST" class="mb-4" style="max-width: 400px;">
            <label class="form-label">Votre offre (€)</label>
            <input type="number" step="0.01" name="offer_price" class="form-control mb-2" required min="0.01" max="<?= htmlspecialchars($product['price']) ?>">
            <button type="submit" class="btn btn-primary">Envoyer l'offre</button>
        </form>
    <?php endif; ?>

    <h4>Historique des offres</h4>
    <?php if (empty($negociations)): ?>
        <p>Aucune offre pour le moment.</p>
    <?php else: ?>
        <ul class="list-group mb-5" style="max-width: 600px;">
            <?php foreach ($negociations as $nego): ?>
                <li class="list-group-item bg-dark text-light">
                    <strong>Offre :</strong> <?= number_format($nego['offer_price'], 2) ?> €
                    <?php if ($nego['seller_response'] === 'accepted'): ?>
                        <span class="badge bg-success ms-2">Acceptée</span>
                    <?php elseif ($nego['seller_response'] === 'counter'): ?>
                        <span class="badge bg-warning text-dark ms-2">Contre-offre : <?= number_format($nego['counter_offer_price'], 2) ?> €</span>
                    <?php else: ?>
                        <span class="badge bg-secondary ms-2">En attente de réponse</span>
                    <?php endif; ?>
                    <br><small class="text-muted"><?= $nego['created_at'] ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <a href="negociation.php" class="btn btn-secondary">Retour à la liste des produits négociables</a>
</div>

<?php include 'footer.php'; ?>
