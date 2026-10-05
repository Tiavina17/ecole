<?php
$page_title = "Modifier un élève";
require '../config.php';
require '../includes/auth.php';

  // Options selon la page :
  // requiereConnexion();                           // tous les connectés
  // requiereRole('directeur');                  // directeur seul
   requiereRole(['directeur','secretaire']);    // directeur OU secrétaire

// ─── Charger l'élève existant ─────────────────────────────────────
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: liste.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM eleves WHERE id = :id");
$stmt->execute([':id' => $id]);
$eleve = $stmt->fetch();
if (!$eleve) { header('Location: liste.php'); exit; }

$classes = $pdo->query("SELECT * FROM classes ORDER BY niveau, nom")->fetchAll();
$errors  = []; $success = '';

// $old = POST si formulaire soumis, sinon données BD (pré-remplissage)
$old = !empty($_POST) ? $_POST : $eleve;

// Détecter le type de date existant (pour pré-cocher le bon radio)
if (empty($_POST) && !empty($eleve['annee_naissance']) && empty($eleve['date_naissance'])) {
    $old['date_type'] = 'annee';   // pré-cocher "année seulement"
} else {
    $old['date_type'] = $old['date_type'] ?? 'complete';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom       = trim($_POST['nom']       ?? '');
    $prenom    = trim($_POST['prenom']    ?? '');
    $matricule = strtoupper(trim($_POST['matricule'] ?? ''));
    $sexe      = $_POST['sexe']      ?? '';
    $adresse   = trim($_POST['adresse']   ?? '');
    $classe_id = (int)($_POST['classe_id'] ?? 0);
    $date_type = $_POST['date_type'] ?? 'complete';

    $nom_pere       = trim($_POST['nom_pere']       ?? '') ?: null;
    $nom_mere       = trim($_POST['nom_mere']       ?? '') ?: null;
    $contact_pere   = trim($_POST['contact_pere']   ?? '') ?: null;
    $contact_mere   = trim($_POST['contact_mere']   ?? '') ?: null;
    $contact_tuteur = trim($_POST['contact_tuteur'] ?? '') ?: null;
    $email_famille  = trim($_POST['email_famille']  ?? '') ?: null;

    if (empty($nom))       $errors['nom']       = "Le nom est obligatoire";
    if (empty($prenom))    $errors['prenom']    = "Le prénom est obligatoire";
    if (empty($matricule)) $errors['matricule'] = "Le matricule est obligatoire";
    if (empty($sexe))      $errors['sexe']      = "Le sexe est obligatoire";
    if ($classe_id <= 0)   $errors['classe_id'] = "La classe est obligatoire";
    if (empty($adresse))   $errors['adresse']   = "L'adresse est obligatoire";

    $date_naissance = null; $annee_naissance = null;
    if ($date_type === 'complete') {
        $d = $_POST['date_naissance'] ?? '';
        if (empty($d)) $errors['date'] = "La date est obligatoire";
        else $date_naissance = $d;
    } else {
        $a = (int)($_POST['annee_naissance'] ?? 0);
        if ($a < 1990 || $a > (int)date('Y')) $errors['date'] = "Année invalide";
        else $annee_naissance = $a;
    }

    // Unicité matricule : exclure l'élève actuel (AND id != :id)
    if (!isset($errors['matricule'])) {
        $chk = $pdo->prepare("SELECT id FROM eleves WHERE matricule=:m AND id!=:id");
        $chk->execute([':m'=>$matricule, ':id'=>$id]);
        if ($chk->fetch()) $errors['matricule'] = "Ce matricule existe déjà";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE eleves SET
              matricule=:matricule, nom=:nom, prenom=:prenom,
              sexe=:sexe, adresse=:adresse, classe_id=:classe_id,
              date_naissance=:date_naissance, annee_naissance=:annee_naissance,
              nom_pere=:nom_pere, nom_mere=:nom_mere,
              contact_pere=:contact_pere, contact_mere=:contact_mere,
              contact_tuteur=:contact_tuteur, email_famille=:email_famille
            WHERE id = :id
        ");
        $stmt->execute([
            ':matricule'=>($matricule), ':nom'=>($nom), ':prenom'=>($prenom),
            ':sexe'=>($sexe), ':adresse'=>($adresse), ':classe_id'=>($classe_id),
            ':date_naissance'=>($date_naissance), ':annee_naissance'=>($annee_naissance),
            ':nom_pere'=>($nom_pere), ':nom_mere'=>($nom_mere),
            ':contact_pere'=>($contact_pere), ':contact_mere'=>($contact_mere),
            ':contact_tuteur'=>($contact_tuteur), ':email_famille'=>($email_famille),
            ':id'=>($id),
        ]);

        $pdo->exec("SET @r = 0");
        $pdo->prepare("UPDATE eleves SET numero_classe=(@r:=@r+1) WHERE classe_id=:cid ORDER BY nom,prenom")
             ->execute([':cid'=>$classe_id]);

        $success = "✅ Modifications enregistrées !";
        $eleve   = $pdo->prepare("SELECT * FROM eleves WHERE id=:id");
        $eleve->execute([':id'=>$id]); $eleve = $eleve->fetch();
        $old = $eleve; // re-synchroniser avec la BD
    }
}
require '../includes/header.php';
?>


<!--
HTML ajouter.php
-->


<?php if ($success): ?>
  <div class="alert alert-success"><?=$success?></div>
<?php endif; ?>

<div class="page-header">
  <div>
    <!-- DIFFÉRENCE 1 : titre dynamique avec le nom de l'élève -->
    <h1 class="page-title">
      ✏️ Modifier : <?= htmlspecialchars($eleve['nom'].' '.$eleve['prenom']) ?>
    </h1>
    <p class="page-subtitle">Matricule : <?=$eleve['matricule']?></p>
  </div>
  <!-- DIFFÉRENCE 2 : lien Annuler → retour à la liste -->
  <a href="liste.php" class="btn btn-ghost">← Annuler</a>
</div>

<form method="POST" action="">
  <!-- ... tous les fieldsets identiques à ajouter.php ... -->
  <!-- Le formulaire est pré-rempli grâce à $old = $eleve -->

  <?php require '_formInscription.php'; ?>

  <!-- DIFFÉRENCE 3 : texte du bouton -->
  <button type="submit" class="btn btn-primary">
    💾 Enregistrer les modifications
  </button>
  <a href="liste.php" class="btn btn-ghost">Annuler</a>
</form>

<?php require '../includes/footer.php'; ?>