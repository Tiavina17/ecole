<?php
require '../config.php'; 
require '../includes/auth.php';
requiereRole(['directeur', 'secretaire']);

// Matières + nombre de classes où elles sont enseignées
$matieres = $pdo->query("
    SELECT m.*,
           COUNT(DISTINCT a.classe_id) AS nb_classes,
           COUNT(DISTINCT a.enseignant_id) AS nb_enseignants
    FROM   matieres m
    LEFT JOIN affectations a ON a.matiere_id = m.id
                             AND a.annee_scolaire = '".ANNEE_SCOLAIRE."'
    GROUP BY m.id
    ORDER BY m.nom
")->fetchAll();
?>
<!-- Tableau avec : Nom | Code | Coefficient | Classes | Enseignants | Actions -->