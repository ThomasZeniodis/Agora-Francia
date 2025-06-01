<?php
session_start();
require_once 'config.php';
include_once 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT e.*, p.name AS nom_produit, p.image_url, p.price AS prix_initial,
           (SELECT MAX(montant_actuel) FROM encheres WHERE id_produit = p.id) AS meilleur_prix
    FROM encheres e
    JOIN products p ON e.id_produit = p.id
    WHERE e.id_utilisateur = ?
    ORDER BY e.date_enchere DESC
");
$stmt->execute([$user_id]);
$encheres = $stmt->fetchAll();
?>

<div class="container mt-5" style="color: #f5f5f5;">
  <h2 class="mb-4" style="font-family: 'Playfair Display', serif;">Mes enchères</h2>

  <?php if (count($encheres) > 0): ?>
    <div class="row row-cols-1 row-cols-md-2 g-4">
      <?php foreach ($encheres as $enchere): ?>
        <div class="col">
          <div class="card h-100" style="background-color: #1e1e1e; border: 1px solid #444; border-radius: 16px;">
            <div class="row g-0">
              <div class="col-md-4">
                <img src="<?= htmlspecialchars($enchere['image_url']) ?: 'https://via.placeholder.com/200x150?text=Pas+d\'image' ?>" 
                     class="img-fluid rounded-start" alt="<?= htmlspecialchars($enchere['nom_produit']) ?>" />
              </div>
              <div class="col-md-8">
                <div class="card-body">
                  <h5 class="card-title" style="color: #bfa37c;"><?= htmlspecialchars($enchere['nom_produit']) ?></h5>
                  <p class="card-text text-white mb-1">Votre enchère max : <strong><?= number_format($enchere['montant_max'], 2) ?> €</strong></p>
                  <p class="card-text text-white mb-1">Mise actuelle : <strong><?= number_format($enchere['montant_actuel'], 2) ?> €</strong></p>
                  <p class="card-text text-white mb-2">Meilleure enchère actuelle : <strong><?= number_format($enchere['meilleur_prix'], 2) ?> €</strong></p>
                  <?php if ($enchere['montant_actuel'] == $enchere['meilleur_prix']): ?>
                    <span class="badge bg-success">Vous êtes le meilleur enchérisseur</span>
                  <?php else: ?>
                    <span class="badge bg-danger">Surpassé</span>
                  <?php endif; ?>
                  <p class="card-text"><small class="text-muted">Placé le : <?= date('d/m/Y H:i', strtotime($enchere['date_enchere'])) ?></small></p>
                  <a href="product.php?id=<?= $enchere['id_produit'] ?>" class="btn btn-outline-light btn-sm mt-2">Voir le produit</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p class="text-muted">Vous n'avez encore participé à aucune enchère.</p>
  <?php endif; ?>
</div>

<?php include_once 'footer.php'; ?>
