<?php
require '../config.php'; 
require '../includes/auth.php';
requiereRole('directeur');

$enseignants = $pdo->query("SELECT * FROM enseignants WHERE statut='actif' ORDER BY nom")->fetchAll();
$matieres    = $pdo->query("SELECT * FROM matieres ORDER BY nom")->fetchAll();
$classes     = $pdo->query("SELECT * FROM classes WHERE annee_scolaire='".ANNEE_SCOLAIRE."' ORDER BY niveau,nom")->fetchAll();

$errors = []; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enseignant_id = (int)($_POST['enseignant_id'] ?? 0);
    $matiere_id    = (int)($_POST['matiere_id']    ?? 0);
    $classe_id     = (int)($_POST['classe_id']     ?? 0);

    if (!$enseignant_id) $errors[] = "Choisir un enseignant";
    if (!$matiere_id)    $errors[] = "Choisir une matière";
    if (!$classe_id)     $errors[] = "Choisir une classe";

    // Vérifier qu'une matière n'est pas déjà attribuée dans cette classe
    if (empty($errors)) {
        $chk = $pdo->prepare("
            SELECT id FROM affectations
            WHERE matiere_id=:m AND classe_id=:c AND annee_scolaire=:a
        ");
        $chk->execute([':m'=>$matiere_id, ':c'=>$classe_id, ':a'=>ANNEE_SCOLAIRE]);
        if ($chk->fetch()) {
            $errors[] = "Cette matière est déjà attribuée dans cette classe !";
        }
    }

    if (empty($errors)) {
        $pdo->prepare("
            INSERT INTO affectations (enseignant_id, matiere_id, classe_id, annee_scolaire)
            VALUES (:e, :m, :c, :a)
        ")->execute([
            ':e' => $enseignant_id, ':m' => $matiere_id,
            ':c' => $classe_id,     ':a' => ANNEE_SCOLAIRE
        ]);
        $success = "✅ Affectation enregistrée !";
    }
}
?>