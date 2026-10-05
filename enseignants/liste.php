<?php
// Un enseignant peut avoir plusieurs matières → GROUP_CONCAT les regroupe
$enseignants = $pdo->query("
    SELECT ens.*,
           GROUP_CONCAT(m.nom ORDER BY m.nom SEPARATOR ', ') AS matieres_noms,
           COUNT(DISTINCT a.classe_id) AS nb_classes
    FROM   enseignants ens
    LEFT JOIN affectations a ON a.enseignant_id = ens.id
                             AND a.annee_scolaire = '2025-2026'
    LEFT JOIN matieres m    ON m.id = a.matiere_id
    GROUP BY ens.id
    ORDER BY ens.nom, ens.prenom
")->fetchAll();
// GROUP_CONCAT : agrège plusieurs lignes en une chaîne
// ex: "Mathématiques, Physique-Chimie" pour un même enseignant
?>

<!-- Dans le tableau -->
<td>
  <?php if($e['matieres_noms']): ?>
    <?= htmlspecialchars($e['matieres_noms']) ?>
  <?php else: ?>
    <span style="color:var(--muted)">Aucune affectation</span>
  <?php endif; ?>
</td>
<td class="muted"><?=$e['nb_classes']?> classe(s)</td>