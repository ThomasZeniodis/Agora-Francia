<?php
session_start();

// Simulons une base de données d'alertes en session (en vrai, ça serait dans une base SQL)
if (!isset($_SESSION['alerts'])) {
    $_SESSION['alerts'] = [];
}

// Traitement formulaire ajout d'une alerte
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_alert'])) {
    $categorie = trim($_POST['categorie'] ?? '');
    $type_achat = trim($_POST['type_achat'] ?? '');
    $prix_max = floatval($_POST['prix_max'] ?? 0);
    $mots_cles = trim($_POST['mots_cles'] ?? '');

    if ($categorie && $type_achat) {
        $_SESSION['alerts'][] = [
            'categorie' => $categorie,
            'type_achat' => $type_achat,
            'prix_max' => $prix_max,
            'mots_cles' => $mots_cles,
            'active' => true,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
}

// Activation/Désactivation d'une alerte
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $index = (int)$_GET['toggle'];
    if (isset($_SESSION['alerts'][$index])) {
        $_SESSION['alerts'][$index]['active'] = !$_SESSION['alerts'][$index]['active'];
    }
}

// Suppression d'une alerte
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $index = (int)$_GET['delete'];
    if (isset($_SESSION['alerts'][$index])) {
        array_splice($_SESSION['alerts'], $index, 1);
    }
}

// ----------------------------------------------------
// --- Partie Notification : Détection des articles ---
// ----------------------------------------------------

// Simulons une liste d'articles disponibles (à remplacer par ta vraie source de données)
$articles = [
    [
        'id' => 1,
        'categorie' => 'Parfum Homme Luxe',
        'type_achat' => 'Achat immédiat',
        'prix' => 450,
        'description' => 'Parfum homme luxe de grande marque'
    ],
    [
        'id' => 2,
        'categorie' => 'Parfum Femme',
        'type_achat' => 'Meilleure offre',
        'prix' => 120,
        'description' => 'Parfum floral femme'
    ],
    [
        'id' => 3,
        'categorie' => 'Parfum Femme Luxe',
        'type_achat' => 'Achat immédiat',
        'prix' => 600,
        'description' => 'Parfum femme luxe édition limitée'
    ],
    [
        'id' => 4,
        'categorie' => 'Parfum Homme',
        'type_achat' => 'Transaction vendeur-client',
        'prix' => 200,
        'description' => 'Parfum homme classique'
    ],
    // Tu peux rajouter d'autres articles ici...
];

// Fonction qui retourne un tableau d'articles correspondant aux alertes actives
function verifier_alertes($articles, $alerts) {
    $resultats = [];
    foreach ($alerts as $index => $alert) {
        if (!$alert['active']) continue;
        foreach ($articles as $article) {
            if ($article['categorie'] === $alert['categorie'] &&
                $article['type_achat'] === $alert['type_achat']) {

                if ($alert['prix_max'] > 0 && $article['prix'] > $alert['prix_max']) {
                    continue;
                }

                if ($alert['mots_cles']) {
                    $mots = explode(',', $alert['mots_cles']);
                    $match = false;
                    foreach ($mots as $mot) {
                        $mot = trim($mot);
                        if (stripos($article['description'], $mot) !== false) {
                            $match = true;
                            break;
                        }
                    }
                    if (!$match) continue;
                }

                $resultats[$index][] = $article;
            }
        }
    }
    return $resultats;
}

// On récupère les articles qui correspondent aux alertes actives
$alertes_correspondantes = verifier_alertes($articles, $_SESSION['alerts'] ?? []);

// Calcul du nombre total d'articles correspondants à toutes alertes actives
$total_correspondants = 0;
foreach ($alertes_correspondantes as $articles_alert) {
    $total_correspondants += count($articles_alert);
}

?>

<?php include 'header.php'; ?>

<div class="container py-5">

  <?php if ($total_correspondants > 0): ?>
    <div class="alert alert-warning text-dark" style="max-width: 600px; margin: 20px auto;">
      ⚠️ Vous avez <strong><?= $total_correspondants ?></strong> article<?= $total_correspondants > 1 ? 's' : '' ?> correspondant<?= $total_correspondants > 1 ? 's' : '' ?> à vos alertes actives !
    </div>
  <?php endif; ?>

  <h1>Alertes Notifications</h1>
  <p>Activez une alerte pour être prévenu quand un article correspondant à vos critères est disponible.</p>

  <h3>Ajouter une nouvelle alerte</h3>
  <form method="POST" class="mb-4" style="max-width:600px;">
    <div class="mb-3">
      <label for="categorie" class="form-label">Catégorie d'article</label>
      <select name="categorie" id="categorie" class="form-select" required>
        <option value="">-- Choisir une catégorie --</option>
        <option value="Parfum Homme Luxe">Parfum Homme Luxe</option>
        <option value="Parfum Femme Luxe">Parfum Femme Luxe</option>
        <option value="Parfum Homme">Parfum Homme</option>
        <option value="Parfum Femme">Parfum Femme</option>
      </select>
    </div>

    <div class="mb-3">
      <label for="type_achat" class="form-label">Mode d'achat</label>
      <select name="type_achat" id="type_achat" class="form-select" required>
        <option value="">-- Choisir un mode d'achat --</option>
        <option value="Meilleure offre">Meilleure offre</option>
        <option value="Transaction vendeur-client">Transaction vendeur-client</option>
        <option value="Achat immédiat">Achat immédiat</option>
      </select>
    </div>

    <div class="mb-3">
      <label for="prix_max" class="form-label">Prix maximum (optionnel)</label>
      <input type="number" name="prix_max" id="prix_max" class="form-control" placeholder="Ex: 500" min="0" step="0.01" />
    </div>

    <div class="mb-3">
      <label for="mots_cles" class="form-label">Mots-clés (optionnel)</label>
      <input type="text" name="mots_cles" id="mots_cles" class="form-control" placeholder="Ex: bague, Cartier, or" />
    </div>

    <button type="submit" name="add_alert" class="btn btn-warning text-dark">Ajouter l'alerte</button>
  </form>

  <h3>Vos alertes actives</h3>
  <?php if (empty($_SESSION['alerts'])): ?>
    <p>Aucune alerte définie pour le moment.</p>
  <?php else: ?>
    <table class="table table-dark table-striped" style="max-width:800px;">
      <thead>
        <tr>
          <th>Catégorie</th>
          <th>Mode d'achat</th>
          <th>Prix max</th>
          <th>Mots-clés</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($_SESSION['alerts'] as $i => $alert): ?>
          <tr>
            <td><?= htmlspecialchars($alert['categorie']) ?></td>
            <td><?= htmlspecialchars($alert['type_achat']) ?></td>
            <td><?= $alert['prix_max'] > 0 ? htmlspecialchars($alert['prix_max']) . ' €' : '-' ?></td>
            <td><?= htmlspecialchars($alert['mots_cles'] ?: '-') ?></td>
            <td>
              <form method="GET" style="display:inline;">
                <input type="hidden" name="toggle" value="<?= $i ?>">
                <button class="btn-toggle <?= $alert['active'] ? '' : 'off' ?>" type="submit">
                  <?= $alert['active'] ? 'Activée' : 'Désactivée' ?>
                </button>
              </form>
            </td>
            <td>
              <form method="GET" style="display:inline;" onsubmit="return confirm('Voulez-vous vraiment supprimer cette alerte ?');">
                <input type="hidden" name="delete" value="<?= $i ?>">
                <button class="btn-delete" type="submit">Supprimer</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
