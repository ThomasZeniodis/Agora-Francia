<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: tout_parcourir.php');
    exit;
}

if (!isset($_GET['id'])) {
    echo "ID produit manquant.";
    exit;
}

$id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;

    try {
        $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ? WHERE id = ?");
        $stmt->execute([$name, $description, $price, $id]);
        header("Location: admin_dashboard.php?message=produit_modifie");
        exit;
    } catch (Exception $e) {
        echo "Erreur lors de la modification : " . $e->getMessage();
    }
} else {
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $produit = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        echo "Erreur : " . $e->getMessage();
        exit;
    }
    if (!$produit) {
        echo "Produit non trouvé.";
        exit;
    }
}
?>

<?php include_once 'header.php'; ?>
<div class="container mt-5">
    <h1 class="text-center text-light mb-4">Modifier le produit</h1>
    <form method="post" class="text-light">
        <div class="mb-3">
            <label class="form-label">Nom</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($produit['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" required><?= htmlspecialchars($produit['description']) ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Prix</label>
            <input type="number" name="price" class="form-control" step="0.01" value="<?= htmlspecialchars($produit['price']) ?>" required>
        </div>
        <button type="submit" class="btn btn-warning">Enregistrer les modifications</button>
    </form>
</div>
<?php include_once 'footer.php'; ?>
