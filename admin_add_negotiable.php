<?php
include_once 'header.php';
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $image_url = trim($_POST['image_url']);

    if ($name === '' || $description === '' || $price <= 0 || $image_url === '') {
        $errors[] = "Merci de remplir tous les champs obligatoires.";
    }

    // Vérifier que l'image existe dans dossier images/
    if (!file_exists(__DIR__ . '/' . $image_url)) {
        $errors[] = "L'image spécifiée n'existe pas dans le dossier images/. Exemple: images/monproduit.jpg";
    }

    if (empty($errors)) {
        $sql = "INSERT INTO products (name, description, price, image_url, categorie, type_achat, negociable) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        $categorie = 'Négociable';
        $type_achat = 'negociation';
        $negociable = 1;

        if ($stmt->execute([$name, $description, $price, $image_url, $categorie, $type_achat, $negociable])) {
            $message = "Produit négociable ajouté avec succès !";
        } else {
            $errors[] = "Erreur lors de l'ajout du produit.";
        }
    }
}

// Récupérer tous les produits négociables pour affichage
$sql = "SELECT name, description, price, image_url FROM products WHERE negociable = 1 ORDER BY id DESC";
$stmt = $pdo->query($sql);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5 text-white" style="max-width: 700px;">
    <h2>Ajouter un produit négociable</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="mt-3 mb-5">
        <div class="mb-3">
            <label for="name" class="form-label">Nom du produit</label>
            <input type="text" name="name" id="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" />
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>
        <div class="mb-3">
            <label for="price" class="form-label">Prix (€)</label>
            <input type="number" step="0.01" min="0" name="price" id="price" class="form-control" required value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" />
        </div>
        <div class="mb-3">
            <label for="image_url" class="form-label">URL de l'image</label>
            <input type="text" name="image_url" id="image_url" class="form-control" />
        </div>

        <button type="submit" class="btn btn-primary w-100">Ajouter le produit négociable</button>
    </form>

    <h3>Liste des produits négociables</h3>

    <?php if (empty($products)): ?>
        <p>Aucun produit négociable pour le moment.</p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($products as $prod): ?>
                <div class="col-md-4 mb-4">
                    <div class="card bg-dark text-white h-100">
                        <?php if ($prod['image_url'] && file_exists(__DIR__ . '/' . $prod['image_url'])): ?>
                            <img src="<?= htmlspecialchars($prod['image_url']) ?>" alt="<?= htmlspecialchars($prod['name']) ?>" class="card-img-top" style="height: 180px; object-fit: contain;">
                        <?php else: ?>
                            <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 180px;">
                                Image non disponible
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($prod['name']) ?></h5>
                            <p class="card-text"><?= nl2br(htmlspecialchars($prod['description'])) ?></p>
                            <p class="card-text"><strong>Prix :</strong> €<?= number_format($prod['price'], 2) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include_once 'footer.php'; ?>

