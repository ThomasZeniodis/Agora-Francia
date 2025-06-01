<?php
include_once 'header.php';
require_once 'config.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}

// Suppression d'une enchère si demandé et si admin
if (isset($_GET['delete']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $auction_id = (int)$_GET['delete'];

    try {
        // Supprimer l'enchère
        $stmt = $pdo->prepare("DELETE FROM auctions WHERE id = ?");
        $stmt->execute([$auction_id]);

        // Optionnel : supprimer le produit associé si tu veux (attention, peut être utilisé ailleurs)
        // $stmt = $pdo->prepare("DELETE FROM products WHERE id = (SELECT product_id FROM auctions WHERE id = ?)");
        // $stmt->execute([$auction_id]);

        header('Location: meilleure_offre.php');
        exit;
    } catch (PDOException $e) {
        echo "<div class='container mt-5'><div class='alert alert-danger'>Erreur lors de la suppression : " . $e->getMessage() . "</div></div>";
    }
}

try {
    $stmt = $pdo->prepare("SELECT a.*, p.name, p.image, p.price AS base_price
                           FROM auctions a 
                           JOIN products p ON a.product_id = p.id");
    $stmt->execute();
    $auctions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Erreur : " . $e->getMessage() . "</div></div>";
    include_once 'footer.php';
    exit;
}

$current_time = new DateTime();
?>

<div class="container mt-5">
    <h2 class="mb-4">Meilleure Offre</h2>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a href="ajouter_offre_admin.php" class="btn btn-success mb-3">Ajouter une offre</a>
    <?php endif; ?>

    <?php if (empty($auctions)): ?>
        <div class="alert alert-info">Aucune offre disponible actuellement.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($auctions as $auction): ?>
                <?php
                    $end_time = new DateTime($auction['end_time']);
                    $start_time = new DateTime($auction['start_time']);
                    $is_active = ($current_time >= $start_time && $current_time <= $end_time);

                    // Afficher uniquement les enchères actives pour acheteurs/vendeurs,
                    // les anciennes uniquement pour admin
                    if (!$is_active && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin')) {
                        continue;
                    }
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 bg-dark text-white border-light">
                        <?php if (!empty($auction['image'])): ?>
                            <img src="<?= htmlspecialchars($auction['image']) ?>" class="card-img-top" alt="Produit" style="object-fit: cover; height: 200px;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($auction['name']) ?></h5>
                            <p class="card-text"><strong>Prix de départ :</strong> <?= number_format($auction['starting_price'], 2) ?> €</p>
                            <p class="card-text"><strong>Meilleure offre actuelle :</strong>
                                <?= $auction['highest_bid'] > 0 ? number_format($auction['highest_bid'], 2) . " €" : "Aucune offre pour le moment" ?>
                            </p>
                            <p class="card-text"><strong>Date début :</strong> <?= date('d/m/Y H:i', strtotime($auction['start_time'])) ?></p>
                            <p class="card-text"><strong>Date fin :</strong> <?= date('d/m/Y H:i', strtotime($auction['end_time'])) ?></p>

                            <?php if ($is_active): ?>
                                <a href="encherir.php?id=<?= $auction['id'] ?>" class="btn btn-primary">Enchérir</a>
                            <?php else: ?>
                                <span class="badge bg-secondary">Enchère terminée</span>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <!-- Bouton suppression avec confirmation -->
                                <a href="meilleure_offre.php?delete=<?= $auction['id'] ?>" class="btn btn-danger mt-2" onclick="return confirm('Confirmer la suppression de cette enchère ?');">
                                    Supprimer
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include_once 'footer.php'; ?>
