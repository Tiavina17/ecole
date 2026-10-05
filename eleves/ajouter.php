<?php

    $page_title = "Inscrire un(e) nouveau(elle) élève";       // ① Définir le titre AVANT l'include
    require '../config.php';                // ② Connexion PDO
    require '../includes/header.php';       // ③ Ouvre : html, head, body, sidebar, main
    require '../includes/auth.php';

  // Options selon la page :
  // requiereConnexion();                           // tous les connectés
  // requiereRole('directeur');                  // directeur seul
   requiereRole(['directeur','secretaire']);    // directeur OU secrétaire

$errors = []; //tableau associatif pour stocker les erreurs
$success = '';
$old = []; // re-remplir les formulaires en cas d'erreur (garde les valeurs saisies)

// Charger toutes les classes pour le menu déroulant
$classes = $pdo->query("SELECT * FROM classes ORDER BY niveau, nom") -> fetchAll();

// Le formulaire a-t-il été soumis ? 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $old = $_POST;

    // ------------------ ETAPE 1 : Recuperer et nettoyer les données ----------------------------

    $nom = trim($_POST['nom'] ?? ''); // trim() enleve les espaces
    $prenom = trim($_POST['prenom'] ?? '');
    $matricule = strtoupper(trim($_POST['matricule'] ?? ''));
    $sexe = $_POST['sexe'] ?? '';
    $adresse = trim($_POST['adresse'] ?? '');
    $classe_id = (int)($_POST['classe_id'] ?? 0); // forcer en entier
    $date_type = $_POST['date_type'] ?? 'complete';

    // Champs facultatifs -> null si vide
    $nom_pere = trim($_POST['nom_pere'] ?? '') ?: null;
    $nom_mere = trim($_POST['nom_mere'] ?? '') ?: null;
    $contact_pere = trim($_POST['contact_pere'] ?? '') ?: null;
    $contact_mere = trim($_POST['contact_mere'] ?? '') ?: null;
    $contact_tuteur = trim($_POST['contact_tuteur'] ?? '') ?: null;
    $email_famille = trim($_POST['email_famille'] ?? '') ?: null;

    // ---------------- ETAPE 2: valider les champs obligatoires -----------------------------

    if (empty($nom))            $errors['nom']      = "Le nom est obligatoire";
    if (empty($prenom))         $errors['npreom']   = "Le prenom est obligatoire";
    if (empty($matricule))      $errors['matricule']= "Le matricule est obligatoire";
    if (empty($sexe))           $errors['sexe']     = "Le sexe est obligatoire";
    if ($classe_id <= 0)        $errors['classe_id']= "La classe est obligatoire";
    if (empty($adresse))        $errors['adresse']  = "L'adresse est obligatoire";

    // Validation date de naissance
    $date_naissance = null;
    $annee_naissance = null;
    if ($date_type === 'complete') {
        $d = $_POST['date_naissance'] ?? '';
        if(empty($d)) $errors['date'] = "La date est obligatoire";
        else $date_naissance = $d;
    } else {
        $a = (int)($_POST['annee_naissance'] ?? 0);
        if ($a < 1980 || $a > (int)date('Y'))
            $errors['date'] = "Année invalide (ex: 2010)";
        else
            $annee_naissance = $a;
    }

    // Verifier le matricule (UNIQUE)
    if(!isset($errors['matricule'])) {
        $chk = $pdo->prepare("SELECT id FROM eleves WHERE matricule = :m");
        $chk->execute([':m' => $matricule]);
        if ($chk->fetch()) $errors['matricule'] = "Ce matricule existe déjà";
    }

    // ------------------- ETAPE 3: INserer si aucune erreur --------------------

    if(empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO eleves (
                matricule, nom, prenom, sexe, adresse, classe_id, date_naissance, annee_naissance,
                nom_pere, nom_mere, contact_pere, contact_mere, contact_tuteur, email_famille
            ) VALUES (
                :matricule, :nom, :prenom, :sexe, :adresse, :classe_id, :date_naissance, :annee_naissance,
                :nom_pere, :nom_mere, :contact_pere, :contact_mere, :contact_tuteur, :email_famille
            )
        ");

    $stmt->execute([
        ':matricule' => $matricule,
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':sexe' => $sexe,
        ':adresse' => $adresse,
        ':classe_id' => $classe_id,
        ':date_naissance' => $date_naissance,
        ':annee_naissance' => $annee_naissance,
        ':nom_pere' => $nom_pere,
        ':nom_mere' => $nom_mere,
        ':contact_pere' => $contact_pere,
        ':contact_mere' => $contact_mere,
        ':contact_tuteur' => $contact_tuteur,
        ':email_famille' => $email_famille,
    ]);

    /// Algorithme : Rénuméroter par ordre alphabétique dans la classe
    $pdo->exec("SET @r = 0");
    $pdo->prepare("
        UPDATE eleves SET numero_classe = (@r := @r +1) WHERE classe_id = :cid ORDER BY nom, prenom
    ") -> execute([':cid' => $classe_id]);

    $success = "Eleve " . $matricule . " ajouté avec succès !";
    $old = [];

    }

}

?>


<!-- Message succès -->
<?php if ($success): ?>
  <div class="alert-success"><?= $success ?></div>
<?php endif; ?>

<form method="POST" action="">

<!-- Fieldset dans _formInscription.php -->

<?php require '_formInscription.php'; ?>


  <button type="submit">➕ Enregistrer l'élève</button>
</form>

<script>
function toggleDate(val) {
  document.getElementById('div_complete').style.display = val==='complete'?'block':'none';
  document.getElementById('div_annee').style.display   = val==='annee'   ?'block':'none';
}
</script>

<?php require '../includes/footer.php'; ?> 