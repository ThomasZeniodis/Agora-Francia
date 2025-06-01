<?php 
include_once 'header.php';
require_once 'config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $address = trim($_POST['address']);

    if ($password !== $confirm) {
        $message = "Les mots de passe ne correspondent pas.";
    } else {
        // Vérifier si l'utilisateur existe déjà
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $message = "Un compte avec cet email existe déjà.";
        } else {
            // Insérer l'utilisateur
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('
                INSERT INTO users (username, email, password_hash, first_name, last_name, address)
                VALUES (?, ?, ?, ?, ?, ?)
            ');
            if ($stmt->execute([$username, $email, $hash, $first_name, $last_name, $address])) {
                $message = "Inscription réussie, vous pouvez maintenant vous connecter.";
            } else {
                $message = "Erreur lors de l'inscription.";
            }
        }
    }
}
?>

<div class="container mt-4" style="color: #f5f5f5; font-family: 'Open Sans', sans-serif;">
    <h1 style="font-family: 'Playfair Display', serif; color: #fff; text-align: center; margin-bottom: 2rem;">Inscription</h1>
    
    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <form method="POST" action="register.php" style="max-width: 480px; margin: auto;">
        <div class="mb-3">
            <label for="username" class="form-label">Nom d'utilisateur</label>
            <input type="text" id="username" name="username" required class="form-control" />
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" required class="form-control" />
        </div>
        <div class="mb-3">
            <label for="first_name" class="form-label">Prénom</label>
            <input type="text" id="first_name" name="first_name" required class="form-control" />
        </div>
        <div class="mb-3">
            <label for="last_name" class="form-label">Nom</label>
            <input type="text" id="last_name" name="last_name" required class="form-control" />
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Adresse</label>
            <textarea id="address" name="address" required class="form-control" rows="2"></textarea>
        </div>
        
        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="password" id="password" name="password" required class="form-control" />
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirmer mot de passe</label>
            <input type="password" id="confirm_password" name="confirm_password" required class="form-control" />
        </div>
        <button type="submit" class="btn btn-primary w-100">S'inscrire</button>
        <div class="text-center mt-3">
            <a href="login.php" class="btn btn-link" style="color: #bfa37c;">Déjà un compte ? Connexion</a>
        </div>
    </form>
</div>

<?php include_once 'footer.php'; ?>
