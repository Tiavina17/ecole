<?php
// Calcul de la situation de paiement d'un élève sur l'année
$eleve_id = (int)($_GET['eleve_id'] ?? 0);

// Tous les paiements de cet élève avec le mois formaté
$stmt = $pdo->prepare("
    SELECT *,
        DATE_FORMAT(STR_TO_DATE(mois_concerne, '%Y-%m'), '%M %Y') AS mois_affiche
    FROM  paiements
    WHERE eleve_id = :id
    ORDER BY mois_concerne DESC
");
// DATE_FORMAT(...,'%M %Y') → convertit "2026-10" en "Octobre 2026"
// STR_TO_DATE(...,'%Y-%m') → convertit la chaîne "2026-10" en vraie date MySQL
$stmt->execute([':id' => $eleve_id]);
$paiements = $stmt->fetchAll();

// Total payé sur l'année scolaire
$total_paye = array_sum(array_column($paiements, 'montant'));
// array_column() extrait une colonne du tableau → [500000, 500000, 500000]
// array_sum() additionne → 1500000

$mensualite  = 50000;   // montant dû par mois (à mettre dans config)
$mois_ecoules= 3;       // calcul dynamique des mois écoulés dans l'année
$total_du    = $mensualite * $mois_ecoules;
$solde       = $total_paye - $total_du;
// Positif = avance, négatif = dette
?>

<!-- Affichage -->
<p>Total payé  : <strong><?= number_format($total_paye,0,',',' ') ?> Ar</strong></p>
<p>Total dû    : <strong><?= number_format($total_du,0,',',' ') ?> Ar</strong></p>
<p style="color:<?=$solde>=0?'var(--success)':'var(--danger)'?>">
  Solde : <?=$solde>=0?'+':''?><?= number_format($solde,0,',',' ') ?> Ar
</p>