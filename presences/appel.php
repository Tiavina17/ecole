<?php
requiereRole(['directeur','enseignant']);

$classe_id  = (int)($_GET['classe_id'] ?? 0);
$date_cours = $_GET['date'] ?? date('Y-m-d'); // aujourd'hui par défaut

// Charger les élèves + leur statut de présence pour ce jour
$stmt = $pdo->prepare("
    SELECT e.id, e.nom, e.prenom, e.numero_classe,
           p.present,   -- NULL si pas encore fait l'appel
           p.motif,
           p.justifie
    FROM   eleves e
    LEFT JOIN presences p ON p.eleve_id   = e.id
                          AND p.date_cours = :d
    WHERE  e.classe_id = :cid
    ORDER BY e.numero_classe, e.nom
");
$stmt->execute([':d'=>$date_cours, ':cid'=>$classe_id]);
$eleves = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // $_POST['presents'] = tableau des IDs des présents (cochés)
    $presents = $_POST['presents'] ?? [];

    foreach ($eleves as $e) {
        $est_present = in_array($e['id'], $presents) ? 1 : 0;

        // INSERT ou UPDATE si déjà fait
        $pdo->prepare("
            INSERT INTO presences (eleve_id, classe_id, date_cours, present)
            VALUES (:eid, :cid, :d, :p)
            ON DUPLICATE KEY UPDATE present = :p
        ")->execute([
            ':eid'=>$e['id'], ':cid'=>$classe_id,
            ':d'=>$date_cours, ':p'=>$est_present
        ]);
    }
    $success = "✅ Appel enregistré !";
}
?>

<!-- Formulaire : checkbox pour chaque élève -->
<form method="POST">
  <?php foreach($eleves as $e): ?>
  <tr>
    <td><?=$e['nom']?></td>
    <td>
      <!-- Coché si présent est NULL (pas encore fait) ou = 1 -->
      <input type="checkbox"
             name="presents[]"
             value="<?=$e['id']?>"
             <?= ($e['present'] === null || $e['present']) ? 'checked':'' ?>>
    </td>
  </tr>
  <?php endforeach; ?>
  <button type="submit" class="btn btn-primary">✅ Enregistrer l'appel</button>
</form>