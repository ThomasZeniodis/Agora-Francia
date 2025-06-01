<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';
include_once 'header.php';

// Vérification accès admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Récupérer nombre total de vendeurs
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'vendeur'");
    $stmt->execute();
    $totalVendeurs = $stmt->fetchColumn();
} catch (Exception $e) {
    $totalVendeurs = "Erreur";
}

// Récupérer les produits
try {
    $stmtProduits = $pdo->query("SELECT * FROM products");
    $produits = $stmtProduits->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $produits = [];
}

?>

<div class="container mt-5">
    <h1 class="mb-4 text-center" style="color: #bfa37c; font-family: 'Playfair Display', serif;">
        Tableau de bord Administrateur
    </h1>

    <p class="text-light text-center fs-5 mb-4">
        Bonjour <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>, bienvenue dans votre espace administrateur.
    </p>

    <div class="row gy-4 justify-content-center">

        <div class="col-12 col-md-5 col-lg-4">
            <a href="ajouter_vendeur.php" class="btn btn-outline-warning w-100 py-3 fs-5">
                ➕ Ajouter un vendeur
            </a>
        </div>

        <div class="col-12 col-md-5 col-lg-4">
            <a href="liste_vendeurs.php" class="btn btn-outline-info w-100 py-3 fs-5 d-flex justify-content-between align-items-center">
                📋 Liste des vendeurs
                <span class="badge bg-info text-dark fs-6"><?= $totalVendeurs ?></span>
            </a>
        </div>

        <div class="col-12 col-md-5 col-lg-4">
            <a href="ajout_produit.php" class="btn btn-outline-success w-100 py-3 fs-5">
                🛒 Ajouter un produit
            </a>
        </div>

        <div class="col-12 col-md-5 col-lg-4">
            <a href="admin_add_negotiable.php" class="btn btn-outline-warning w-100 py-3 fs-5">
                🤝 Ajouter un produit négociable
            </a>
        </div>

    </div>

    <hr class="text-light mt-5" />

    <h2 class="text-light text-center mb-4">Produits existants</h2>

    <div class="table-responsive">
        <table class="table table-dark table-hover text-center">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prix</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produits as $produit): ?>
                    <tr>
                        <td><?= htmlspecialchars($produit['id']) ?></td>
                        <td><?= htmlspecialchars($produit['name']) ?></td>
                        <td><?= htmlspecialchars($produit['price']) ?> €</td>
                        <td>
                            <a href="admin_modifier_produit.php?id=<?= $produit['id'] ?>" class="btn btn-sm btn-primary">Modifier</a>
                            <a href="admin_supprimer_produit.php?id=<?= $produit['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include_once 'footer.php'; ?>
