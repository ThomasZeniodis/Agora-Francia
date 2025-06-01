<?php
include 'config.php'; // se connecte à la base via $pdo
if (!isset($_GET['id'])) {
    echo "Produit introuvable.";
    exit();
}

$product_id = intval($_GET['id']);

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "Produit non trouvé.";
    exit();
}
?>

<?php include 'header.php'; ?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="img-fluid">
        </div>
        <div class="col-md-6">
            <h2><?= htmlspecialchars($product['name']) ?></h2>
            <p class="lead"><?= htmlspecialchars($product['description']) ?></p>
            <h4 class="text-success"><?= number_format($product['price'], 2) ?> €</h4>
            
            <form action="ajouter_panier.php" method="post">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <input type="number" name="quantite" value="1" min="1" class="form-control w-25 mb-3">
                <button type="submit" class="btn btn-dark">Ajouter au panier</button>
            </form>
        </div>
    </div>
</div>


