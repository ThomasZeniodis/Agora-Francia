<?php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérification de l'accès admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name']);
    $product_image = trim($_POST['product_image']);
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $starting_price = floatval($_POST['starting_price']);

    // Validation simple
    if ($product_name === '' || $product_image === '') {
        $error = "Le nom et l'URL de l'image du produit sont obligatoires.";
    } elseif ($starting_price < 0) {
        $error = "Le prix de départ doit être positif.";
    } elseif (strtotime($start_time) === false || strtotime($end_time) === false || $end_time <= $start_time) {
        $error = "Dates invalides ou date de fin avant date de début.";
    } else {
        try {
            $pdo->beginTransaction();

            // Insertion du produit
            $stmt = $pdo->prepare("INSERT INTO products (name, image) VALUES (?, ?)");
            $stmt->execute([$product_name, $product_image]);
            $product_id = $pdo->lastInsertId();

            // Insertion de l'enchère
            $stmt = $pdo->prepare("INSERT INTO auctions (product_id, start_time, end_time, starting_price, highest_bid) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$product_id, $start_time, $end_time, $starting_price, $starting_price]);

            $pdo->commit();

            header('Location: meilleure_offre.php');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Erreur lors de l'ajout : " . $e->getMessage();
        }
    }
}
?>

<?php include 'header.php'; ?>
<div class="container mt-5" style="max-width:600px">
    <h2 class="mb-4">Ajouter un produit et une offre aux enchères (admin)</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Nom du produit</label>
            <input type="text" name="product_name" class="form-control" required value="<?= isset($_POST['product_name']) ? htmlspecialchars($_POST['product_name']) : '' ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">URL de l'image du produit</label>
            <input type="text" name="product_image" class="form-control" placeholder="Exemple : images/produit.jpg" required value="<?= isset($_POST['product_image']) ? htmlspecialchars($_POST['product_image']) : '' ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Date de début</label>
            <input type="datetime-local" name="start_time" class="form-control" required value="<?= isset($_POST['start_time']) ? htmlspecialchars($_POST['start_time']) : '' ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Date de fin</label>
            <input type="datetime-local" name="end_time" class="form-control" required value="<?= isset($_POST['end_time']) ? htmlspecialchars($_POST['end_time']) : '' ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Prix de départ (€)</label>
            <input type="number" step="0.01" name="starting_price" class="form-control" min="0" required value="<?= isset($_POST['starting_price']) ? htmlspecialchars($_POST['starting_price']) : '0' ?>">
        </div>

        <button type="submit" class="btn btn-primary">Ajouter</button>
        <a href="meilleure_offre.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>
<?php include 'footer.php'; ?>
