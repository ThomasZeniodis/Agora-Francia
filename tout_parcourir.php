<?php
include 'header.php';
require_once 'config.php';

$categories = [
    "Parfum Homme Luxe",
    "Parfum Femme Luxe",
    "Parfum Homme",
    "Parfum Femme"
];

$produits_par_categorie = [];

try {
    foreach ($categories as $cat) {
        // Produits classiques depuis `products`
        $stmt1 = $pdo->prepare("
            SELECT id, name, description, image_url AS photo, price
            FROM products
            WHERE categorie = ?
            AND id NOT IN (
                SELECT product_id FROM auctions
                WHERE NOW() BETWEEN start_time AND end_time
            )
            AND type_achat != 'negociation'
        ");
        $stmt1->execute([$cat]);
        $produits1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);

        // Produits ajoutés par vendeurs depuis `items`
        $stmt2 = $pdo->prepare("
            SELECT id, nom AS name, description, photo, prix AS price
            FROM items
            WHERE categorie = ?
            AND mode_vente = 'achat_immediat'
            AND vendu = 0
        ");
        $stmt2->execute([$cat]);
        $produits2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        // Fusion des deux
        $produits_par_categorie[$cat] = array_merge($produits1, $produits2);
    }
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Erreur : " . $e->getMessage() . "</div>";
}
?>

<div class="container py-5 text-white">
    <h1 class="mb-4">Tout Parcourir</h1>
    <p class="lead">Retrouvez nos parfums classés par catégorie.</p>

    <?php foreach ($produits_par_categorie as $categorie => $produits): ?>
        <h2 class="mt-5"><?= htmlspecialchars($categorie) ?></h2>
        <div class="row row-cols-1 row-cols-md-4 g-4 mt-3">
            <?php if (count($produits) === 0): ?>
                <p class="text-muted">Aucun produit disponible pour cette catégorie.</p>
            <?php else: ?>
                <?php foreach ($produits as $produit): ?>
                    <div class="col">
                        <div class="card h-100 bg-dark text-light border-light shadow">
                            <?php if (!empty($produit['photo'])): ?>
                                <img src="<?= htmlspecialchars($produit['photo']) ?>" class="card-img-top" alt="<?= htmlspecialchars($produit['name']) ?>" style="object-fit: cover; height: 200px;">
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($produit['name']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($produit['description']) ?></p>
                                <p class="fw-bold mt-auto"><?= number_format($produit['price'], 2) ?> €</p>
                                <a href="product.php?id=<?= $produit['id'] ?>" class="btn btn-outline-light mt-2">Voir l'article</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php include 'footer.php'; ?>
