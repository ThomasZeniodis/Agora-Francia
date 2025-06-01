<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_destroy();

include_once 'header.php'; 
?>

<div style="min-height: 80vh; display: flex; justify-content: center; align-items: center; color: #f5f5f5; font-family: 'Playfair Display', serif; font-size: 1.8rem; text-align: center;">
  <div>
    <h1>Vous êtes déconnecté</h1>
    <p>Redirection vers la page d'accueil...</p>
    <p><a href="index.php" style="color:#bfa37c; font-weight: bold; text-decoration: none;">Cliquez ici si vous n'êtes pas redirigé</a></p>
  </div>
</div>

<?php
// Redirection automatique après 3 secondes
header("refresh:3;url=index.php");
include_once 'footer.php'; 
?>
