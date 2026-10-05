<?php
require 'config.php';

// Si déjà connecté → rediriger vers le dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php'); exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mdp   = $_POST['mot_de_passe'] ?? '';

    // Chercher l'utilisateur par email (actif uniquement)
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email=:e AND actif=1");
    $stmt->execute([':e' => $email]);
    $user = $stmt->fetch();

    // password_verify() compare le mot de passe saisi avec le hash stocké
    // Ex: password_verify("Admin1234", "$2y$12$...") → true ou false
    if ($user && password_verify($mdp, $user['mot_de_passe'])) {

        // Stocker les infos dans la SESSION (persistantes entre pages)
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['user_role']= $user['role'];
        $_SESSION['user_nom'] = $user['nom'] . ' ' . $user['prenom'];

        header('Location: dashboard.php');
        exit;

    } else {
        // Message volontairement vague (ne pas indiquer si c'est l'email ou le mdp)
        $error = "Email ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html><html lang="fr"><head>
<meta charset="UTF-8"><title>Connexion — EcoleApp</title>
<link rel="stylesheet" href="assets/style.css"></head>
<body style="display:flex;align-items:center;justify-content:center;min-height:100vh">

<div style="width:360px">
  <div style="text-align:center;margin-bottom:32px">
    <div style="font-size:3em">🏫</div>
    <h1 style="color:var(--primary);font-size:1.4em;margin-top:8px">EcoleApp</h1>
    <p style="color:var(--muted);font-size:0.85em">Connectez-vous à votre espace</p>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?=$error?></div>
  <?php endif; ?>

  <form method="POST" class="card">
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email"
             value="<?=htmlspecialchars($_POST['email']??'')?>"
             placeholder="directeur@ecole.mg" autofocus>
    </div>
    <div class="form-group" style="margin-top:14px">
      <label>Mot de passe</label>
      <input type="password" name="mot_de_passe" placeholder="••••••••">
    </div>
    <button type="submit" class="btn btn-primary" style="width:100%;margin-top:20px;justify-content:center">
      🔐 Se connecter
    </button>
  </form>
</div>
</body></html>