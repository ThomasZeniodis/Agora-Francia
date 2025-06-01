<?php
include_once 'header.php';
require_once 'config.php';

try {
    // Récupérer les 8 derniers produits qui NE SONT PAS en enchère active ET pas en négociation
    $stmt = $pdo->prepare("
        SELECT * FROM products p
        WHERE p.id NOT IN (
            SELECT product_id FROM auctions
            WHERE NOW() BETWEEN start_time AND end_time
        )
        AND p.type_achat != 'negociation'
        ORDER BY p.id DESC
        LIMIT 8
    ");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Erreur : " . $e->getMessage() . "</div>";
}
?>

<div class="container mt-5">
    <h1 class="mb-1" style="font-family: 'Playfair Display', serif; color: #bfa37c;">Bienvenue chez Agora Francia</h1>
    <h3 class="mb-4" style="font-family: 'Playfair Display', serif; color: #bfa37c;">Ventes Flash :</h3>

    <!-- Carrousel dynamique -->
    <?php if (count($products) > 0): ?>
        <div id="flashCarousel" class="carousel slide mb-5" data-bs-ride="carousel" style="border-radius: 15px; overflow: hidden;">
            <div class="carousel-inner">
                <?php foreach ($products as $index => $product): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <div class="d-flex justify-content-center align-items-center" style="height: 400px; background-color: #000;">
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                 style="max-height: 100%; max-width: 100%; object-fit: contain;">
                        </div>
                        <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-3">
                            <h5 style="font-family: 'Playfair Display', serif; color: #bfa37c;"><?= htmlspecialchars($product['name']) ?></h5>
                            <p><?= htmlspecialchars($product['description']) ?></p>
                            <p class="fw-bold" style="color: #ffffff;"><?= number_format($product['price'], 2) ?> €</p>
                            <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-warning">Voir le produit</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#flashCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
                <span class="visually-hidden">Précédent</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#flashCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
                <span class="visually-hidden">Suivant</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Section cartes produit classique -->
    <div class="row g-4">
        <?php foreach ($products as $product): ?>
            <div class="col-md-3">
                <div class="card bg-dark text-light h-100 shadow border-0" style="border-radius: 15px;">
                    <?php if ($product['image_url']): ?>
                        <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                             class="card-img-top" 
                             alt="<?= htmlspecialchars($product['name']) ?>" 
                             style="border-radius: 15px 15px 0 0; object-fit: cover; height: 200px;">
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title" style="font-family: 'Playfair Display', serif;"><?= htmlspecialchars($product['name']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars($product['description']) ?></p>
                        <p class="mt-auto fw-bold" style="font-size: 1.1rem; color: #bfa37c;"><?= number_format($product['price'], 2) ?> €</p>
                        <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-warning mt-2">Voir le produit</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include_once 'footer.php'; ?>
