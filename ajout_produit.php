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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $image_url = trim($_POST['image_url']);
    $categorie = trim($_POST['categorie']);
    $type_achat = trim($_POST['type_achat']);

    if ($name === '' || $description === '' || $price <= 0 || $categorie === '') {
        $message = "Merci de remplir tous les champs obligatoires correctement.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image_url, categorie, type_achat) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $description, $price, $image_url, $categorie, $type_achat])) {
            $message = "Produit ajouté avec succès ! <a href='index.php' class='link-light'>Retour à l'accueil</a>";
        } else {
            $message = "Erreur lors de l'ajout du produit.";
        }
    }
}
?>

<div class="container mt-5 text-white">
    <h2>Ajouter un nouveau produit</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <form method="post" class="mt-3">
        <div class="mb-3">
            <label for="name" class="form-label">Nom du produit</label>
            <input type="text" name="name" id="name" class="form-control" required />
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control" required></textarea>
        </div>
        <div class="mb-3">
            <label for="price" class="form-label">Prix (€)</label>
            <input type="number" step="0.01" name="price" id="price" class="form-control" required />
        </div>
        <div class="mb-3">
            <label for="image_url" class="form-label">URL de l'image</label>
            <input type="text" name="image_url" id="image_url" class="form-control" />
        </div>
        <div class="mb-3">
            <label for="categorie" class="form-label">Catégorie</label>
            <select name="categorie" id="categorie" class="form-select" required>
                <option value="Parfum Homme Luxe">Parfum Homme Luxe</option>
                <option value="Parfum Femme Luxe">Parfum Femme Luxe</option>
                <option value="Parfum Homme">Parfum Homme</option>
                <option value="Parfum Femme">Parfum Femme</option>
            </select>
        </div>

        <!-- Champ caché pour fixer à "achat immédiat" -->
        <input type="hidden" name="type_achat" value="achat immédiat" />

        <button type="submit" class="btn btn-success">Ajouter le produit</button>
    </form>
</div>

<?php include_once 'footer.php'; ?>
