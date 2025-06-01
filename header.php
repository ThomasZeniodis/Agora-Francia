<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Agora Francia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display&family=Montserrat&display=swap" rel="stylesheet" />
  <style>
    body {
      background-color: #1a1a1a;
      color: #e0d9c8;
      font-family: 'Montserrat', sans-serif;
    }
    h1, h2, h3, h4, h5 {
      font-family: 'Playfair Display', serif;
      color: #bfa37c;
    }
    .navbar {
      background-color: #121212;
    }
    .navbar-brand {
      font-family: 'Playfair Display', serif;
      font-weight: 700;
      color: #bfa37c !important;
    }
    .nav-link {
      color: #e0d9c8 !important;
      transition: color 0.3s ease;
    }
    .nav-link:hover {
      color: #bfa37c !important;
    }
    .btn-logout {
      color: #bfa37c;
      border: 1px solid #bfa37c;
      padding: 5px 12px;
      border-radius: 5px;
      transition: all 0.3s ease;
      text-decoration: none;
    }
    .btn-logout:hover {
      background-color: #bfa37c;
      color: #121212;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
  <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="#bfa37c" class="bi bi-gem" viewBox="0 0 16 16">
    <path d="M3.1.5a.5.5 0 0 0-.4.2l-2.5 3a.5.5 0 0 0 .02.65l7.5 8.5a.5.5 0 0 0 .76 0l7.5-8.5a.5.5 0 0 0 .02-.65l-2.5-3a.5.5 0 0 0-.4-.2H3.1zm0 1h9.8l2.07 2.5H1.03L3.1 1.5zM1.5 4.5h13l-6.5 7.33L1.5 4.5z"/>
  </svg>
  <span>Agora Francia</span>
</a>

    <button class="navbar-toggler btn btn-outline-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      ☰
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">

        <!-- Accueil -->
        <li class="nav-item">
          <a class="nav-link" href="index.php">Accueil</a>
        </li>

        <!-- Tout Parcourir -->
        <li class="nav-item">
          <a class="nav-link" href="tout_parcourir.php">Tout Parcourir</a>
        </li>

        <!-- Notifications -->
        <li class="nav-item">
          <a class="nav-link" href="notifications.php">Notifications</a>
        </li>

        <!-- Panier -->
        <li class="nav-item">
          <a class="nav-link" href="panier.php">Panier
            <?php
            $count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
            if ($count > 0) {
                echo " (<span>$count</span>)";
            }
            ?>
          </a>
        </li>

        <!-- Votre Compte -->
        <li class="nav-item">
          <a class="nav-link" href="profile.php">Votre Compte</a>
        </li>


        <!-- Meilleure offre -->
        
        <li class="nav-item">
        <a class="nav-link" href="meilleure_offre.php">Meilleure offre</a>
        </li>
        <!-- Négociation -->
        <li class="nav-item">
        <a class="nav-link" href="negociation.php">Négociation</a>
       </li>
       <!-- Dashboard -->
       <li class="nav-item">
        <a class="nav-link" href="admin_dashboard.php">Dashboard</a>
       </li>

        <!-- Admin -->
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
        <li class="nav-item">
          <a class="nav-link" href="admin.php">Espace Admin</a>
        </li>
        <?php endif; ?>

        <!-- Connexion / Déconnexion -->
        <?php if (isset($_SESSION['user_id'])): ?>
        <li class="nav-item">
          <a class="nav-link btn-logout ms-2" href="logout.php">Déconnexion</a>
        </li>
        <?php else: ?>
        <li class="nav-item">
          <a class="nav-link" href="login.php">Connexion</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="register.php">Inscription</a>
        </li>
        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
