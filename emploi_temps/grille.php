<?php
require '../config.php'; require '../includes/auth.php';
requiereConnexion();

$classe_id = (int)($_GET['classe_id'] ?? 0);

// Charger tous les créneaux de la classe, organisés par jour et heure
$stmt = $pdo->prepare("
    SELECT et.*, m.nom AS matiere, m.code,
           ens.nom AS ens_nom, ens.prenom AS ens_prenom
    FROM   emploi_temps et
    JOIN   matieres    m   ON m.id   = et.matiere_id
    JOIN   enseignants ens ON ens.id = et.enseignant_id
    WHERE  et.classe_id = :cid AND et.annee_scolaire = :a
    ORDER BY et.jour, et.heure_debut
");
$stmt->execute([':cid'=>$classe_id, ':a'=>ANNEE_SCOLAIRE]);
$rows = $stmt->fetchAll();

// Algorithme : transformer la liste en tableau 2D [jour][heure] pour la grille
$grille = [];
foreach ($rows as $r) {
    // Clé = "08:00" → affichage "08h00"
    $cle_heure = $r['heure_debut'] . '-' . $r['heure_fin'];
    $grille[$r['jour']][$cle_heure] = $r;
}
// Résultat : $grille['Lundi']['08:00-09:00'] = [matiere=>'Maths', ...]

$jours  = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
$heures = ['07:00-08:00','08:00-09:00','09:00-10:00',
           '10:00-11:00','11:00-12:00','14:00-15:00','15:00-16:00'];
?>


<!-- CSS Grid : 1 colonne heures + 6 colonnes jours -->
<div class="edt-grid">

  <!-- En-têtes des colonnes -->
  <div class="edt-corner"></div>
  <?php foreach ($jours as $j): ?>
    <div class="edt-head"><?=$j?></div>
  <?php endforeach; ?>

  <!-- Lignes : une par créneau horaire -->
  <?php foreach ($heures as $h): ?>
    <!-- Afficher l'heure (ex: "08h00 - 09h00") -->
    <div class="edt-time">
      <?= str_replace(':','h',substr($h,0,5)) ?>
    </div>

    <?php foreach ($jours as $j): ?>
      <div class="edt-cell">
        <?php if (isset($grille[$j][$h])): $c = $grille[$j][$h]; ?>
          <div class="edt-cours">
            <strong><?=htmlspecialchars($c['matiere'])?></strong>
            <small><?=htmlspecialchars($c['ens_nom'])?></small>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endforeach; ?>
</div>


<?php
// Vérifier qu'un enseignant n'est pas déjà occupé sur ce créneau
$conflit = $pdo->prepare("
    SELECT COUNT(*) FROM emploi_temps
    WHERE enseignant_id = :eid
      AND jour = :jour
      AND annee_scolaire = :a
      AND (
          /* Le créneau chevauche-t-il un créneau existant ? */
          (heure_debut < :fin AND heure_fin > :debut)
      )
");
$conflit->execute([
    ':eid'  => $enseignant_id,
    ':jour' => $jour,
    ':a'    => ANNEE_SCOLAIRE,
    ':debut'=> $heure_debut,
    ':fin'  => $heure_fin,
]);
if ($conflit->fetchColumn() > 0) {
    $errors[] = "Cet enseignant est déjà occupé sur ce créneau !";
}
?>