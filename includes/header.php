<?php
// Ce fichier est inclus en HAUT de chaque page (require)
// La variable $page_title doit être définie AVANT l'include

// basename() extrait le nom du fichier courant sans extension
// ex: /ecole/eleves/liste.php → "liste"
$page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- ?? : si $page_title n'est pas définie → utiliser 'École' -->
  <title><?= $page_title ?? 'École' ?> — SekolyApp</title>
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/style.css">
</head>
<body>
<div class="app-layout">

  <!-- Sidebar -->
  <nav class="sidebar">
    <div class="sidebar-logo">
      <span class="logo-icon">🏫</span>
      <span class="logo-text">SekolyApp</span>
    </div>
    <ul class="nav-links">

      <!-- in_array() vérifie si $page est dans la liste → classe "active" -->
      <li class="<?= $page==='index' ? 'active':'' ?>">
        <a href="<?= SITE_URL ?>/index.php">🏠 Tableau de bord</a>
      </li>

      <li class="<?= in_array($page,['liste','ajouter','modifier']) ? 'active':'' ?>">
        <a href="<?= SITE_URL ?>/eleves/liste.php">👨‍🎓 Élèves</a>
      </li>

      <li><a href="#">🏛️ Classes</a></li>
      <li><a href="#">📝 Notes</a></li>
    </ul>
  </nav>

  <?php if (estDirecteur()): ?>
    <li><a href="/paiements/liste.php">💰 Écolage</a></li>
    <li><a href="/enseignants/salaires.php">💳 Salaires</a></li>
  <?php endif; ?>

  <!-- Info utilisateur + logout en bas de sidebar -->
<div class="sidebar-user">
  <div class="user-name"><?= htmlspecialchars($_SESSION['user_nom']) ?></div>
  <div class="user-role"><?= ucfirst($_SESSION['user_role']) ?></div>
  <a href="/logout.php" class="btn-logout">⬅ Déconnexion</a>
</div>

  <!-- Le contenu de chaque page va ici -->
  <main class="main-content">