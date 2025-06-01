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

$error = '';

if ($product_id === 0) {
    // Liste des produits négociables
    $stmt = $pdo->prepare("SELECT * FROM products WHERE negociable = 1 ORDER BY id DESC");
    if (!$stmt->execute()) {
        echo "<div class='container mt-5 text-white'>Erreur lors de la récupération des produits.</div>";
        include 'footer.php';
        exit;
    }
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="container mt-5 text-white">
        <h2>Produits négociables</h2>
        <?php if (empty($products)): ?>
            <p>Aucun produit négociable disponible.</p>
        <?php else: ?>
            <div class="row">
                <?php foreach ($products as $product): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card bg-dark text-light h-100 shadow border-0" style="border-radius: 15px;">
                            <?php if ($product['image_url']): ?>
                                <img src="<?= htmlspecialchars($product['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>" style="border-radius: 15px 15px 0 0; object-fit: cover; height: 200px;">
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                                <p class="card-text">Prix : <?= number_format($product['price'], 2, ',', ' ') ?> €</p>
                                <a href="negociation.php?product_id=<?= $product['id'] ?>" class="btn btn-primary mt-auto">Négocier</a>
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

// Page négociation pour un produit donné
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND negociable = 1");
if (!$stmt->execute([$product_id])) {
    echo "<div class='container mt-5 text-white'>Erreur lors de la récupération du produit.</div>";
    include 'footer.php';
    exit;
}
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "<div class='container mt-5 text-white'>Produit introuvable ou non négociable.</div>";
    include 'footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['offer_price'])) {
    $offer_price = floatval($_POST['offer_price']);

    // Validation basique de l'offre
    if ($offer_price <= 0) {
        $error = "Veuillez saisir un prix d'offre valide.";
    } elseif ($offer_price > $product['price']) {
        $error = "Votre offre ne peut pas être supérieure au prix initial.";
    }

    if (empty($error)) {
        // Limite des 5 offres max par utilisateur et produit
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM negociations WHERE product_id = ? AND user_id = ?");
        $stmt->execute([$product_id, $user_id]);
        $count = $stmt->fetchColumn();

        if ($count < 5) {
            // Insérer la négociation sans image_url
            $stmt = $pdo->prepare("INSERT INTO negociations (product_id, user_id, offer_price) VALUES (?, ?, ?)");
            $stmt->execute([$product_id, $user_id, $offer_price]);
            header("Location: negociation.php?product_id=$product_id");
            exit;
        } else {
            $error = "Vous avez atteint la limite de 5 tentatives de négociation.";
        }
    }
}

// Récupération des négociations pour affichage
$stmt = $pdo->prepare("SELECT * FROM negociations WHERE product_id = ? AND user_id = ? ORDER BY created_at ASC");
$stmt->execute([$product_id, $user_id]);
$negociations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérification si une offre a été acceptée
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
    <div class="card bg-dark text-light h-100 shadow border-0" style="border-radius: 15px;">
        <?php if ($product['image_url']): ?>
            <img src="<?= htmlspecialchars($product['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>" style="border-radius: 15px 15px 0 0; object-fit: cover; height: 200px;">
        <?php endif; ?>
    </div>
    
    <p><strong>Prix initial :</strong> <?= number_format($product['price'], 2, ',', ' ') ?> €</p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($accepted): ?>
        <div class="alert alert-success">
            Votre offre de <?= number_format($final_price, 2, ',', ' ') ?> € a été acceptée !<br>
            Vous êtes désormais engagé à acheter ce produit.
        </div>
    <?php elseif (count($negociations) >= 5): ?>
        <div class="alert alert-warning">
            Négociation terminée : vous avez utilisé vos 5 tentatives.
        </div>
    <?php else: ?>
        <form method="POST" class="mb-4" style="max-width: 400px;">
            <label class="form-label" for="offer_price">Votre offre (€)</label>
            <input type="number" step="0.01" name="offer_price" id="offer_price" class="form-control mb-2" required min="0.01" max="<?= htmlspecialchars($product['price']) ?>">
            <button type="submit" class="btn btn-primary">Envoyer l'offre</button>
        </form>
    <?php endif; ?>

    <h4>Historique des offres</h4>
    <?php if (empty($negociations)): ?>
        <p>Aucune offre pour ce produit.</p>
    <?php else: ?>
        <table class="table table-dark table-striped" style="max-width: 700px;">
            <thead>
                <tr>
                    <th>Offre (€)</th>
                    <th>Réponse du vendeur</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($negociations as $nego): ?>
                    <tr>
                        <td><?= number_format($nego['offer_price'], 2, ',', ' ') ?></td>
                        <td>
                            <?php
                            switch ($nego['seller_response']) {
                                case 'accepted': echo '<span class="text-success">Acceptée</span>'; break;
                                case 'rejected': echo '<span class="text-danger">Rejetée</span>'; break;
                                default: echo '<span class="text-warning">En attente</span>';
                            }
                            ?>
                        </td>
                        <td><?= htmlspecialchars($nego['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
