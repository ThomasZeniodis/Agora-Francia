<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';
include_once 'header.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; // admin, vendeur, client

        // Redirection en fonction du rôle
        if ($user['role'] === 'admin') {
            header('Location: admin_dashboard.php');
        } elseif ($user['role'] === 'vendeur') {
            header('Location: vendeur.php');
        } else {
            header('Location: index.php');
        }
        exit;
    } else {
        $message = "Email ou mot de passe incorrect.";
    }
}
?>

<div class="container mt-5" style="max-width: 400px;">
    <h1 class="mb-4 text-center" style="font-family: 'Playfair Display', serif; color: #bfa37c;">Connexion</h1>

    <?php if ($message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="mb-3">
            <label for="email" class="form-label text-light">Email</label>
            <input type="email" id="email" name="email" required class="form-control bg-dark text-light border-0" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />
        </div>
        <div class="mb-3">
            <label for="password" class="form-label text-light">Mot de passe</label>
            <input type="password" id="password" name="password" required class="form-control bg-dark text-light border-0" />
        </div>
        <button type="submit" class="btn btn-primary w-100">Se connecter</button>
        <a href="register.php" class="btn btn-link text-light d-block text-center mt-3">Pas encore inscrit ?</a>
    </form>
</div>

<?php include_once 'footer.php'; ?>

