<?php
requiereRole(['directeur','enseignant']);

// Charger les élèves de la classe pour afficher le formulaire de saisie
$eleves = $pdo->prepare("
    SELECT e.id, e.nom, e.prenom, e.numero_classe,
           n.note   -- note existante s'il y en a une
    FROM   eleves e
    LEFT JOIN notes n ON n.eleve_id    = e.id
                      AND n.matiere_id = :mid
                      AND n.trimestre  = :t
                      AND n.type_eval  = :type
    WHERE  e.classe_id = :cid
    ORDER BY e.numero_classe, e.nom
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // $notes est un tableau : notes[eleve_id] = valeur_note
    $notes = $_POST['notes'] ?? [];

    foreach ($notes as $eleve_id => $valeur) {
        $valeur = trim($valeur);
        if ($valeur === '') continue; // skip si vide

        $note = (float)$valeur;
        if ($note < 0 || $note > 20) continue; // valider 0-20

        // INSERT OR UPDATE : si note existe → la modifier, sinon la créer
        // ON DUPLICATE KEY UPDATE → évite les doublons (nécessite UNIQUE KEY)
        $pdo->prepare("
            INSERT INTO notes (eleve_id, matiere_id, classe_id, note, type_eval, trimestre, annee_scolaire, date_eval)
            VALUES (:eid, :mid, :cid, :note, :type, :t, :a, CURDATE())
            ON DUPLICATE KEY UPDATE note = :note
        ")->execute([
            ':eid'=>$eleve_id, ':mid'=>$matiere_id, ':cid'=>$classe_id,
            ':note'=>$note, ':type'=>$type_eval,
            ':t'=>$trimestre, ':a'=>ANNEE_SCOLAIRE
        ]);
    }
    $success = "✅ Notes enregistrées !";
}
?>

<!-- Formulaire avec inputs dynamiques -->
<form method="POST">
  <?php foreach($eleves as $e): ?>
  <tr>
    <td><?=$e['nom']?></td>
    <td>
      <!-- Le name est notes[ID_ELEVE] → PHP reçoit un tableau -->
      <input type="number" name="notes[<?=$e['id']?>]"
             min="0" max="20" step="0.25"
             value="<?=$e['note']??''?>">
    </td>
  </tr>
  <?php endforeach; ?>
</form>