<?php
require_once 'config.php';

// Récupérer les enchères terminées mais non clôturées
$now = date('Y-m-d H:i:s');
$stmt = $pdo->prepare("SELECT id FROM auctions WHERE status = 'active' AND end_time <= ?");
$stmt->execute([$now]);
$ended_auctions = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($ended_auctions as $auction_id) {
    // Trouver la meilleure offre
    $stmt = $pdo->prepare("SELECT user_id, bid_amount FROM bids WHERE auction_id = ? ORDER BY bid_amount DESC LIMIT 1");
    $stmt->execute([$auction_id]);
    $best_bid = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($best_bid) {
        // Mettre à jour l'enchère avec le gagnant et fermer
        $update = $pdo->prepare("UPDATE auctions SET winner_user_id = ?, status = 'closed', current_price = ? WHERE id = ?");
        $update->execute([$best_bid['user_id'], $best_bid['bid_amount'], $auction_id]);

        // Ici tu peux ajouter une notification mail au gagnant si tu veux
    } else {
        // Pas d'offre, on ferme simplement l'enchère sans gagnant
        $update = $pdo->prepare("UPDATE auctions SET status = 'closed' WHERE id = ?");
        $update->execute([$auction_id]);
    }
}
echo "Mise à jour des enchères terminées effectuée.\n";
?>
