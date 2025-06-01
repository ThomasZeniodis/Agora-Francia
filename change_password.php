<?php
include_once 'header.php';
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Récupérer hash du mot de passe actuel
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($current_password, $user['password_hash'])) {
        $message = "Le mot de passe actuel est incorrect.";
    } elseif (strlen($new_password) < 6) {
        $message = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
    } elseif ($new_password !== $confirm_password) {
        $message = "Les nouveaux mots de passe ne correspondent pas.";
    } else {
        // Hasher le nouveau mot de passe
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$new_hash, $user_id]);
        $message = "Mot de passe mis à jour avec succès.";
    }
}
?>

<div class="container mt-5" style="max-width: 500px;">
    <h2>Changer mon mot de passe</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label for="current_password" class="form-label text-light">Mot de passe actuel</label>
            <input type="password" id="current_password" name="current_password" class="form-control bg-dark text-light border-0" required />
        </div>

        <div class="mb-3">
            <label for="new_password" class="form-label text-light">Nouveau mot de passe</label>
            <input type="password" id="new_password" name="new_password" class="form-control bg-dark text-light border-0" required />
        </div>

        <div class="mb-3">
            <label for="confirm_password" class="form-label text-light">Confirmer le nouveau mot de passe</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control bg-dark text-light border-0" required />
        </div>

        <button type="submit" class="btn btn-warning w-100">Changer le mot de passe</button>
        <a href="profile.php" class="btn btn-link text-light mt-3 d-block text-center">Retour au profil</a>
    </form>
</div>

<?php include_once 'footer.php'; ?>
