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

// Récupérer tous les vendeurs
$stmt = $pdo->query("SELECT id, email, username FROM users WHERE role = 'vendeur' ORDER BY username ASC");
$vendeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
    <h1 class="mb-4 text-center" style="font-family: 'Playfair Display', serif; color: #bfa37c;">Liste des vendeurs</h1>

    <?php if (empty($vendeurs)): ?>
        <p class="text-center text-light">Aucun vendeur trouvé.</p>
    <?php else: ?>
        <table class="table table-dark table-striped table-hover">
            <thead>
                <tr>
                    <th>Pseudo</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vendeurs as $v): ?>
                    <tr>
                        <td><?= htmlspecialchars($v['username']) ?></td>
                        <td><?= htmlspecialchars($v['email']) ?></td>
                        <td>
                            <a href="supprimer_vendeur.php?id=<?= $v['id'] ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Voulez-vous vraiment supprimer le vendeur <?= htmlspecialchars($v['username']) ?> ?');">
                               Supprimer
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="admin_dashboard.php" class="btn btn-warning">Retour au tableau de bord</a>
    </div>
</div>

<?php include_once 'footer.php'; ?>
